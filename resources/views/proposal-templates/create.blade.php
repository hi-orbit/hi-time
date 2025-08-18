@extends('layouts.app')

@section('title', 'Create Proposal Template')

@push('styles')
<!-- Sun Editor CSS -->
<link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
<style>
    /* Custom styles for Sun Editor */
    .sun-editor {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    }
    .sun-editor .se-wrapper-inner {
        min-height: 400px;
    }
    .sun-editor-editable {
        padding: 1rem;
        font-family: inherit;
        line-height: 1.6;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Proposal Template</h1>
                    <p class="text-gray-600 mt-2">Create a new reusable proposal template with variables.</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('proposal-templates.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Templates
                    </a>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('proposal-templates.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Template Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror"
                                placeholder="Enter template name...">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Template Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Template Type</label>
                            <select id="type" name="type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 @enderror">
                                <option value="">Select Type</option>
                                <option value="Service Proposal" {{ old('type') === 'Service Proposal' ? 'selected' : '' }}>Service Proposal</option>
                                <option value="Project Proposal" {{ old('type') === 'Project Proposal' ? 'selected' : '' }}>Project Proposal</option>
                                <option value="Quote" {{ old('type') === 'Quote' ? 'selected' : '' }}>Quote</option>
                                <option value="Estimate" {{ old('type') === 'Estimate' ? 'selected' : '' }}>Estimate</option>
                                <option value="Contract" {{ old('type') === 'Contract' ? 'selected' : '' }}>Contract</option>
                                <option value="Other" {{ old('type') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-300 @enderror"
                                placeholder="Brief description of what this template is for...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <div class="flex items-center">
                                <!-- Hidden input to ensure is_active is always sent -->
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Active template
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Inactive templates won't be available for creating new proposals.</p>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Variable Instructions -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Using Variables</h4>
                            <div class="text-sm text-blue-800 space-y-1">
                                <p>• Use double curly braces to create variables: <code class="bg-blue-100 px-1 rounded">@{{variable_name}}</code></p>
                                <p>• Variables will be automatically detected and can be filled when creating proposals</p>
                                <p>• Common variables: client_name, project_name, amount, date, description</p>
                                <p>• Variables are case-sensitive and should use underscore naming</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Template Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Template Content</label>

                            <!-- CSRF Token Refresh Indicator -->
                            <div class="csrf-refresh-indicator mb-2 text-xs text-green-600" id="csrf-refresh-status" style="opacity: 0; transition: opacity 0.3s ease;">
                                CSRF token refreshed successfully
                            </div>

                            <!-- Hidden textarea for form submission -->
                            <textarea id="content" name="content" style="display: none;" required>{{ old('content') }}</textarea>

                            <!-- Sun Editor Container -->
                            <div id="suneditor-container" class="@error('content') border-red-300 @enderror">
                                <!-- Fallback content while Sun Editor loads -->
                                <div id="editor-loading" style="min-height: 400px; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 1rem; background-color: #f9fafb; display: flex; align-items: center; justify-content: center; color: #6b7280;">
                                    <div class="text-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                                        <p>Loading editor...</p>
                                    </div>
                                </div>
                            </div>

                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-2 text-xs text-gray-500">
                                💡 Tip: Use variables like @{{client_name}}, @{{project_name}}, @{{amount}}, @{{date}}, @{{client_address}}, @{{client_company_number}}, @{{valid_until}}, @{{client_email}} in your content
                            </div>
                        </div>

                        <!-- Template Preview -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Template Guidelines</h4>
                            <div class="text-sm text-gray-600 space-y-2">
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Include your company branding and contact information</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Define project scope, timeline, and deliverables</span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Use variables for dynamic content that changes per proposal</span>
                                </div>
                            </div>
                        </div>

                        <!-- Example Variables -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-yellow-900 mb-2">Available Variables</h4>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="space-y-1">
                                    <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{client_name}}</code>
                                    <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{client_email}}</code>
                                    <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{client_address}}</code>
                                    <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{client_company_number}}</code>
                                </div>
                                <div class="space-y-1">
                                    <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{project_name}}</code>
                                    <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{amount}}</code>
                                    <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{date}}</code>
                                    <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{valid_until}}</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 mt-8">
                    <div class="flex space-x-3">
                        <a href="{{ route('proposal-templates.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Create Template
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/en.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');

    // Initialize Sun Editor with working configuration from proposals
    const sunEditor = SUNEDITOR.create('suneditor-container', {
        lang: SUNEDITOR_LANG['en'],
        width: '100%',
        height: '400px',
        placeholder: `Enter your proposal template content here...

Use variables like:
• @{{client_name}} for client name
• @{{client_email}} for client email
• @{{client_address}} for client address
• @{{client_company_number}} for company number
• @{{project_name}} for project name
• @{{amount}} for project amount
• @{{date}} for current date
• @{{valid_until}} for validity date

Example:
Dear @{{client_name}},

We are pleased to submit this proposal for @{{project_name}}.

Company: @{{client_name}}
Address: @{{client_address}}
Company Number: @{{client_company_number}}
Email: @{{client_email}}

The total investment for this project is @{{amount}}.

This proposal is valid until @{{valid_until}}.

Thank you for considering our services.

Best regards,
Your Company Name`,
        buttonList: [
            ['undo', 'redo'],
            ['fontSize', 'formatBlock'],
            ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
            ['fontColor', 'hiliteColor'],
            ['align', 'list', 'lineHeight'],
            ['outdent', 'indent'],
            ['table', 'link', 'image'],
            ['removeFormat'],
            ['preview', 'print'],
            ['fullScreen', 'showBlocks', 'codeView']
        ],
        formats: ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        colorList: [
            '#333333', '#666666', '#999999', '#cccccc',
            '#000000', '#ffffff', '#ff0000', '#00ff00', '#0000ff'
        ],
        // Image upload configuration - completely disable built-in upload
        imageUploadUrl: '', // Empty string to disable
        imageUploadSizeLimit: 5 * 1024 * 1024, // 5MB limit
        imageUploadHeader: null, // Not needed for custom handler
        // Configure file input name - SunEditor uses 'file-0' by default
        imageMultipleFile: true, // Multiple file upload
        imageFileInput: true, // Enable file upload tab
        imageUrlInput: true, // Enable URL input tab
        imageAccept: '.jpg,.jpeg,.png,.gif,.webp', // Accepted file types
        onImageUpload: function(targetElement, index, state, info, remainingFilesCount, core) {
            console.log('Image upload started', info);
        },
        onImageUploadError: function(errorMessage, result, core) {
            console.error('Image upload error:', errorMessage, result);
            alert('Failed to upload image: ' + errorMessage);
        },
        onImageUploadBefore: function(files, info, core) {
            console.log('Custom upload handler - About to upload files:', files);

            // Handle each file upload with proper authentication
            Array.from(files).forEach((file, index) => {
                const formData = new FormData();
                formData.append('file-' + index, file);

                // Use fetch with proper credentials and CSRF
                fetch('/proposal-templates/upload-image', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin' // Include cookies for authentication
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');

                    if (!response.ok) {
                        let errorMessage = `HTTP ${response.status}`;

                        if (contentType && contentType.includes('application/json')) {
                            const errorData = await response.json();
                            errorMessage = errorData.message || errorMessage;
                        } else {
                            const errorText = await response.text();
                            errorMessage = errorText || errorMessage;
                        }

                        throw new Error(errorMessage);
                    }

                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        throw new Error('Server returned non-JSON response');
                    }
                })
                .then(data => {
                    console.log('Upload response:', data);

                    if (data.errorMessage) {
                        throw new Error(data.errorMessage);
                    }

                    if (data.result && data.result.length > 0) {
                        // Insert the uploaded image into the editor
                        const imageUrl = data.result[0].url;
                        const imageName = data.result[0].name;

                        // Create image element and insert it
                        const img = document.createElement('img');
                        img.src = imageUrl;
                        img.alt = imageName;
                        img.style.maxWidth = '100%';
                        img.style.height = 'auto';

                        // Insert the image at the current cursor position
                        sunEditor.insertHTML(img.outerHTML);
                        console.log('Image uploaded and inserted successfully:', imageUrl);

                        // Show success message
                        alert('Image uploaded successfully!');
                    } else {
                        throw new Error('No image data received from server');
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('Failed to upload image: ' + error.message);
                });
            });

            return false; // Prevent SunEditor's default upload behavior
        },
        callBackSave: function (contents) {
            console.log('Content saved');
        }
    });

    // Debug: Check the actual configuration
    console.log('SunEditor configuration:', {
        imageUploadUrl: sunEditor.options.imageUploadUrl,
        imageUploadHeader: sunEditor.options.imageUploadHeader
    });

    // Set initial content if available
    if (contentTextarea.value) {
        sunEditor.setContents(contentTextarea.value);
    }

    // Update hidden textarea when editor content changes
    sunEditor.onChange = function(contents) {
        contentTextarea.value = contents;
    };

    // Handle submit button clicks - sync editor content to hidden field
    const form = document.querySelector('form');
    const submitButtons = form.querySelectorAll('button[type="submit"]');

    submitButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Sync editor content to hidden textarea before form submission
            const editorContent = sunEditor.getContents();
            contentTextarea.value = editorContent;

            // Simple validation - only prevent if content is truly empty
            if (!editorContent || editorContent.trim() === '') {
                e.preventDefault();
                alert('Please enter template content before submitting.');
                return false;
            }

            // Show loading state
            button.disabled = true;
            button.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Creating Template...';
        });
    });
});
</script>
@endpush
@endsection
