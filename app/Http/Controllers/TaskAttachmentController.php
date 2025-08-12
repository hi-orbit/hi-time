<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskAttachmentController extends Controller
{
    /**
     * Upload a task attachment via form upload.
     * Supports: Images (jpg, jpeg, png, gif), Documents (pdf, doc, docx, txt, xlsx, xls),
     * Archives (zip, rar), and Videos (mp4, avi, mov, wmv, flv, webm, mkv, m4v, 3gp)
     * Max file size: 10MB for documents/images, 20MB for videos
     */
    public function upload(Request $request, Task $task)
    {
        // Video file extensions
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v', '3gp'];

        // Check if uploaded file is a video to determine size limit
        $file = $request->file('attachment');
        $isVideo = $file && in_array(strtolower($file->getClientOriginalExtension()), $videoExtensions);
        $maxSize = $isVideo ? 20480 : 10240; // 20MB for videos, 10MB for others

        $request->validate([
            'attachment' => "required|file|max:{$maxSize}|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,xlsx,xls,zip,rar,mp4,avi,mov,wmv,flv,webm,mkv,m4v,3gp",
        ], [
            'attachment.max' => $isVideo
                ? 'Video files cannot be larger than 20MB.'
                : 'Files cannot be larger than 10MB.',
        ]);

        try {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('task-attachments', $fileName, 'public');

            TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => Auth::id(),
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return back()->with('success', "File '{$originalName}' uploaded successfully!");

        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload a task attachment via dropzone (AJAX).
     * Supports: Images (jpg, jpeg, png, gif), Documents (pdf, doc, docx, txt, xlsx, xls),
     * Archives (zip, rar), and Videos (mp4, avi, mov, wmv, flv, webm, mkv, m4v, 3gp)
     * Max file size: 10MB for documents/images, 20MB for videos
     */
    public function dropzoneUpload(Request $request, Task $task)
    {
        try {
            // First, basic validation with the maximum size (20MB)
            $request->validate([
                'file' => 'required|file|max:20480|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,xlsx,xls,zip,rar,mp4,avi,mov,wmv,flv,webm,mkv,m4v,3gp',
            ]);

            $file = $request->file('file');

            // Now check if it's a video and apply stricter validation for non-videos
            $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v', '3gp'];
            $isVideo = in_array(strtolower($file->getClientOriginalExtension()), $videoExtensions);

            // Additional validation for non-video files (10MB limit)
            if (!$isVideo && $file->getSize() > 10240 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non-video files cannot be larger than 10MB. Videos can be up to 20MB.'
                ], 422);
            }

            $originalName = $file->getClientOriginalName();
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('task-attachments', $fileName, 'public');

            $attachment = TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => Auth::id(),
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "File '{$originalName}' uploaded successfully!",
                'attachment' => $attachment
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 422);
        }
    }
}
