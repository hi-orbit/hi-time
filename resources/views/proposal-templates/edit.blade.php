@extends('layouts.app')

@section('title', 'Edit Proposal Template')

@push('styles')
<!-- Sun Editor CSS -->
<link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
<style>
    /* Custom styles for Sun E        placeholder: \`Enter your proposal template content here...

Use variables like:
• @{{client_name}} for client name
• @{{project_name}} for project name
• @{{amount}} for project amount
• @{{date}} for current date

Example:
Dear @{{client_name}},

We are pleased to submit this proposal for @{{project_name}}...\`, .sun-editor {
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
                    <h1 class="text-2xl font-bold text-gray-900">Edit Proposal Template</h1>
                    <p class="text-gray-600 mt-2">Update the proposal template details and content.</p>
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
            <form method="POST" action="{{ route('proposal-templates.update.post', $proposalTemplate) }}" id="template-form">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Template Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $proposalTemplate->name) }}" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror">
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
                                <option value="Service Proposal" {{ old('type', $proposalTemplate->type) === 'Service Proposal' ? 'selected' : '' }}>Service Proposal</option>
                                <option value="Project Proposal" {{ old('type', $proposalTemplate->type) === 'Project Proposal' ? 'selected' : '' }}>Project Proposal</option>
                                <option value="Quote" {{ old('type', $proposalTemplate->type) === 'Quote' ? 'selected' : '' }}>Quote</option>
                                <option value="Estimate" {{ old('type', $proposalTemplate->type) === 'Estimate' ? 'selected' : '' }}>Estimate</option>
                                <option value="Contract" {{ old('type', $proposalTemplate->type) === 'Contract' ? 'selected' : '' }}>Contract</option>
                                <option value="Other" {{ old('type', $proposalTemplate->type) === 'Other' ? 'selected' : '' }}>Other</option>
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
                                placeholder="Brief description of what this template is for...">{{ old('description', $proposalTemplate->description) }}</textarea>
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
                                    {{ old('is_active', $proposalTemplate->is_active) ? 'checked' : '' }}
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

                        <!-- Current Variables -->
                        @if($proposalTemplate->variables && count($proposalTemplate->variables) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Variables</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($proposalTemplate->variables as $variable)
                                        <span class="inline-flex px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">
                                            @{{ $variable }}
                                        </span>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-sm text-gray-500">These variables are automatically detected from your template content.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Template Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Template Content</label>

                            <!-- Hidden textarea for form submission -->
                            <textarea id="content" name="content" style="display: none;" required>{{ old('content', $proposalTemplate->content) }}</textarea>

                            <!-- Sun Editor Container -->
                            <div id="suneditor-container" class="@error('content') border-red-300 @enderror">
                                <!-- Sun Editor will be initialized here -->
                            </div>

                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-2 text-xs text-gray-500">
                                💡 Tip: Use variables like @{{client_name}}, @{{project_name}}, @{{amount}}, @{{date}}, @{{client_address}}, @{{client_company_number}}, @{{valid_until}}, @{{client_email}} in your content
                            </div>
                        </div>

                        <!-- Variable Instructions -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Using Variables</h4>
                            <div class="text-sm text-blue-800 space-y-1">
                                <p>• Use double curly braces to create variables: <code class="bg-blue-100 px-1 rounded">@{{variable_name}}</code></p>
                                <p>• Variables will be automatically detected and can be filled when creating proposals</p>
                                <p>• Available client variables: client_name, client_email, client_address, client_company_number</p>
                                <p>• Other variables: project_name, amount, date, valid_until, proposal_title</p>
                                <p>• Variables are case-sensitive and should use underscore naming</p>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Template Preview</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>Used in <strong>{{ $proposalTemplate->proposals()->count() }}</strong> proposal{{ $proposalTemplate->proposals()->count() !== 1 ? 's' : '' }}</p>
                                <p>Created: {{ $proposalTemplate->created_at->format('M j, Y') }}</p>
                                <p>Last updated: {{ $proposalTemplate->updated_at->format('M j, Y g:i A') }}</p>
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
                            Update Template
                        </button>
                    </div>
                </div>
            </form>

            <!-- Delete Form (separate from main form) -->
            @if($proposalTemplate->proposals()->count() === 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <form method="POST" action="{{ route('proposal-templates.destroy', $proposalTemplate) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('Are you sure you want to delete this template? This action cannot be undone.')"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Template
                    </button>
                </form>
            </div>
            @else
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Cannot delete template that is being used by proposals.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<!-- Sun Editor JS -->
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/en.js"></script>

@verbatim
@push('scripts')
<!-- Sun Editor JS -->
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/en.js"></script>

@push('scripts')
<!-- Sun Editor JS -->
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/en.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof SUNEDITOR !== 'undefined') {
        const editor = SUNEDITOR.create('suneditor-container', {
            plugins: [
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
                'image'
            ],
            buttonList: [
                ['undo', 'redo'],
                ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                ['fontColor', 'hiliteColor'],
                ['removeFormat'],
                ['outdent', 'indent'],
                ['align', 'horizontalRule', 'list', 'lineHeight'],
                ['table', 'link', 'image'],
                ['fullScreen', 'showBlocks', 'codeView'],
                ['preview', 'print']
            ],
            imageUploadUrl: '',
            imageUploadSizeLimit: 5 * 1024 * 1024, // 5MB
            imageUploadHeader: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            imageUploadBefore: function(files, info, uploadHandler) {
                // Custom upload handler with proper authentication
                if (!files || files.length === 0) return false;

                const formData = new FormData();
                formData.append('file-0', files[0]);

                fetch('/proposal-templates/upload-image', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.errorMessage) {
                        throw new Error(data.errorMessage);
                    }

                    // Call the upload handler with successful result
                    if (data.result && data.result.length > 0) {
                        uploadHandler(data.result[0].url, data.result[0].name, null);
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    uploadHandler(null, null, error.message || 'Upload failed');
                });

                // Return false to prevent default upload
                return false;
            },
            imageMultipleFile: true,
            imageAccept: '.jpg,.jpeg,.png,.gif,.webp',
            height: '400px',
            minHeight: '200px',
            placeholder: `Enter your proposal template content here...

Use variables like:
• {{client_name}} for client name
• {{client_email}} for client email
• {{client_address}} for client address
• {{client_company_number}} for company number
• {{project_name}} for project name
• {{amount}} for project amount
• {{date}} for current date
• {{valid_until}} for validity date

Example:
Dear {{client_name}},

We are pleased to submit this proposal for {{project_name}}.

Company: {{client_name}}
Address: {{client_address}}
Company Number: {{client_company_number}}
Email: {{client_email}}

The total investment for this project is {{amount}}.

This proposal is valid until {{valid_until}}.

Thank you for considering our services.

Best regards,
Your Company Name`,
            resizingBar: true,
            showPathLabel: false,
            charCounter: true,
            maxCharCount: 50000
        });

        // Store editor instance globally for access
        window.sunEditor = editor;

        // Get initial content and set it in the editor
        const hiddenTextarea = document.getElementById('content');
        const initialContent = hiddenTextarea.value || `{!! addslashes(old('content', $proposalTemplate->content ?? '')) !!}`;
        if (initialContent) {
            editor.setContents(initialContent);
        }

        // Combined editor change handler for content syncing and auto-save
        let autoSaveTimeout;
        editor.onChange = function(contents) {
            // Update hidden textarea for form submission
            hiddenTextarea.value = contents;

            // Clear existing auto-save timeout
            clearTimeout(autoSaveTimeout);

            // Set new timeout for auto-save (optional feature)
            autoSaveTimeout = setTimeout(function() {
                console.log('Template content auto-saved locally');
            }, 2000);
        };

        // Handle image upload success
        editor.onImageUpload = function(targetElement, index, state, imageInfo, remainingFilesCount) {
            console.log('Image uploaded:', imageInfo);
        };

        // Handle image upload error
        editor.onImageUploadError = function(errorMessage, result) {
            console.error('Image upload error:', errorMessage, result);
            alert('Image upload failed: ' + errorMessage);
        };

        // Custom form submission for edit template
        const form = document.getElementById('template-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Ensure the hidden textarea has the latest content
                hiddenTextarea.value = editor.getContents();

                // Show loading state
                const submitButton = form.querySelector('button[type="submit"]');
                const originalButtonContent = submitButton ? submitButton.innerHTML : '';
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Updating Template...';
                }

                // Prepare form data
                const formData = new FormData(form);

                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Submit via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.ok) {
                        // Success - redirect to index page
                        window.location.href = '/proposal-templates';
                    } else if (response.status === 419) {
                        throw new Error('CSRF token mismatch. Please refresh the page and try again.');
                    } else if (response.status === 422) {
                        // Validation errors
                        return response.json().then(data => {
                            throw new Error('Validation failed: ' + JSON.stringify(data.errors));
                        });
                    } else {
                        throw new Error('Server error: ' + response.status);
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    alert('Error: ' + error.message);

                    // Re-enable the submit button
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonContent;
                    }
                });
            });
        }

    } else {
        console.error('SunEditor is not loaded. Please include the SunEditor library.');
    }
});
</script>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Additional functionality for template editing
    if (window.sunEditor) {
        // Get initial content and set it in the editor
        const hiddenTextarea = document.getElementById('content');
        const initialContent = hiddenTextarea.value || `{!! addslashes(old('content', $proposalTemplate->content ?? '')) !!}`;
        if (initialContent) {
            window.sunEditor.setContents(initialContent);
        }

        // Combined editor change handler for content syncing and auto-save
        let autoSaveTimeout;
        window.sunEditor.onChange = function(contents) {
            // Update hidden textarea for form submission
            hiddenTextarea.value = contents;

            // Clear existing auto-save timeout
            clearTimeout(autoSaveTimeout);

            // Set new timeout for auto-save (optional feature)
            autoSaveTimeout = setTimeout(function() {
                console.log('Template content auto-saved locally');
            }, 2000);
        };

        // Custom form submission for edit template
        const form = document.getElementById('template-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Ensure the hidden textarea has the latest content
                hiddenTextarea.value = window.sunEditor.getContents();

                // Show loading state
                const submitButton = form.querySelector('button[type="submit"]');
                const originalButtonContent = submitButton ? submitButton.innerHTML : '';
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Updating Template...';
                }

                // Prepare form data
                const formData = new FormData(form);

                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Submit via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.ok) {
                        // Success - redirect to index page
                        window.location.href = '/proposal-templates';
                    } else if (response.status === 419) {
                        throw new Error('CSRF token mismatch. Please refresh the page and try again.');
                    } else if (response.status === 422) {
                        // Validation errors
                        return response.json().then(data => {
                            throw new Error('Validation failed: ' + JSON.stringify(data.errors));
                        });
                    } else {
                        throw new Error('Server error: ' + response.status);
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    alert('Error: ' + error.message);

                    // Re-enable the submit button
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonContent;
                    }
                });
            });
        }
    }
});
</script>
@endpush
@endverbatim
@endpush
@endsection
