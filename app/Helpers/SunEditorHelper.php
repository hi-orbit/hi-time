<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SunEditorHelper
{
    /**
     * Handle image uploads for SunEditor
     *
     * @param Request $request
     * @param string $folder The folder name to store images (e.g., 'proposal-images', 'template-images')
     * @return JsonResponse
     */
    public static function uploadImage(Request $request, string $folder = 'editor-images'): JsonResponse
    {
        try {
            // Log PHP upload configuration
            Log::info('SunEditor Image Upload - PHP Configuration', [
                'upload_tmp_dir' => ini_get('upload_tmp_dir'),
                'file_uploads' => ini_get('file_uploads'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'temp_dir_writable' => is_writable(ini_get('upload_tmp_dir')),
                'temp_dir_exists' => is_dir(ini_get('upload_tmp_dir'))
            ]);

            Log::info('SunEditor Image Upload Request', [
                'files' => $request->allFiles(),
                'raw_files' => $_FILES ?? [],
                'user_id' => Auth::id(),
                'content_type' => $request->header('content-type'),
                'content_length' => $request->header('content-length'),
                'folder' => $folder
            ]);

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            // SunEditor sends file as 'file-0', try both 'file' and 'file-0'
            $file = $request->file('file-0') ?? $request->file('file');

            if (!$file) {
                return response()->json([
                    'errorMessage' => 'No file uploaded. Available fields: ' . implode(', ', array_keys($request->allFiles()))
                ], 400);
            }

            // Check if file is valid and log detailed error info
            if (!$file->isValid()) {
                $uploadError = $file->getError();
                $errorMessages = [
                    UPLOAD_ERR_OK => 'No error',
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary directory',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
                ];

                $errorMessage = $errorMessages[$uploadError] ?? 'Unknown upload error: ' . $uploadError;

                Log::error('SunEditor File Upload Validation Failed', [
                    'error_code' => $uploadError,
                    'error_message' => $errorMessage,
                    'file_size' => $file->getSize(),
                    'file_name' => $file->getClientOriginalName(),
                    'folder' => $folder
                ]);

                return response()->json([
                    'errorMessage' => 'File upload failed: ' . $errorMessage
                ], 400);
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'errorMessage' => 'Invalid file extension. Allowed: ' . implode(', ', $allowedExtensions)
                ], 400);
            }

            // Generate safe filename
            $filename = uniqid() . '.' . $extension;

            // Detect if we're in Docker environment or production
            $isDocker = file_exists('/.dockerenv') || getenv('CONTAINER_ENV') === 'docker';

            if ($isDocker) {
                // Docker environment: save directly to public directory to avoid volume sharing issues
                $publicDir = public_path($folder);
                if (!is_dir($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }

                $destinationPath = $publicDir . '/' . $filename;
                $file->move($publicDir, $filename);
                $url = '/' . $folder . '/' . $filename;

                Log::info('SunEditor Image Uploaded (Docker mode)', [
                    'filename' => $filename,
                    'url' => $url,
                    'destination_path' => $destinationPath,
                    'environment' => 'docker',
                    'folder' => $folder
                ]);
            } else {
                // Production/standard environment: use Laravel storage with symlink
                $path = $file->storeAs($folder, $filename, 'public');
                $url = Storage::disk('public')->url($path);

                // Ensure storage symlink exists
                if (!file_exists(public_path('storage'))) {
                    \Artisan::call('storage:link');
                }

                Log::info('SunEditor Image Uploaded (Production mode)', [
                    'filename' => $filename,
                    'url' => $url,
                    'path' => $path,
                    'storage_path' => Storage::disk('public')->path($path),
                    'environment' => 'production',
                    'folder' => $folder
                ]);
            }

            // Log storage details for debugging
            Log::info('SunEditor Image Upload Successful', [
                'filename' => $filename,
                'url' => $url,
                'user_id' => Auth::id(),
                'is_docker' => $isDocker,
                'folder' => $folder
            ]);

            // SunEditor response format
            return response()->json([
                'errorMessage' => null,
                'result' => [
                    [
                        'url' => $url,
                        'name' => $filename
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('SunEditor Image Upload Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'folder' => $folder
            ]);

            return response()->json([
                'errorMessage' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SunEditor configuration with image upload enabled
     *
     * @param string $uploadRoute The route for image uploads
     * @param string $csrfToken CSRF token for requests
     * @return array
     */
    public static function getConfig(string $uploadRoute, string $csrfToken): array
    {
        return [
            'plugins' => [
                'align',
                'font',
                'fontSize',
                'fontColor',
                'hiliteColor',
                'horizontalRule',
                'list',
                'lineHeight',
                'table',
                'link',
                'image',
                'video'
            ],
            'buttonList' => [
                ['undo', 'redo'],
                ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                ['fontColor', 'hiliteColor'],
                ['removeFormat'],
                ['outdent', 'indent'],
                ['align', 'horizontalRule', 'list', 'lineHeight'],
                ['table', 'link', 'image', 'video'],
                ['fullScreen', 'showBlocks', 'codeView'],
                ['preview', 'print']
            ],
            'imageUploadUrl' => $uploadRoute,
            'imageUploadSizeLimit' => 5 * 1024 * 1024, // 5MB
            'imageUploadHeader' => [
                'X-CSRF-TOKEN' => $csrfToken,
                'X-Requested-With' => 'XMLHttpRequest'
            ],
            'imageMultipleFile' => true,
            'imageAccept' => '.jpg,.jpeg,.png,.gif,.webp',
            'height' => '400px',
            'minHeight' => '200px',
            'placeholder' => 'Enter your content here...',
            'resizingBar' => true,
            'showPathLabel' => false,
            'charCounter' => true,
            'maxCharCount' => 50000
        ];
    }

    /**
     * Generate JavaScript initialization code for SunEditor
     *
     * @param string $elementSelector CSS selector for the textarea element
     * @param string $uploadRoute The route for image uploads
     * @param string $csrfToken CSRF token for requests
     * @param array $additionalOptions Additional SunEditor options
     * @return string
     */
    public static function generateInitScript(
        string $elementSelector,
        string $uploadRoute,
        string $csrfToken,
        array $additionalOptions = []
    ): string {
        $config = array_merge(
            self::getConfig($uploadRoute, $csrfToken),
            $additionalOptions
        );

        $configJson = json_encode($config, JSON_PRETTY_PRINT);

        return "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof SUNEDITOR !== 'undefined') {
                const editor = SUNEDITOR.create('{$elementSelector}', {$configJson});

                // Store editor instance globally for access
                window.sunEditor = editor;

                // Handle image upload success
                editor.onImageUpload = function(targetElement, index, state, imageInfo, remainingFilesCount) {
                    console.log('Image uploaded:', imageInfo);
                };

                // Handle image upload error
                editor.onImageUploadError = function(errorMessage, result) {
                    console.error('Image upload error:', errorMessage, result);
                    alert('Image upload failed: ' + errorMessage);
                };
            } else {
                console.error('SunEditor is not loaded. Please include the SunEditor library.');
            }
        });
        </script>";
    }
}
