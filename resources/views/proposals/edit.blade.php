@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <div class="flex items-center">
                    <a href="{{ route('proposals.show', $proposal) }}" class="text-gray-500 hover:text-gray-700 mr-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Edit Proposal: {{ $proposal->title }}
                    </h1>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Update your proposal details and content
                </p>
            </div>
        </div>

        <!-- Status Alert -->
        @if($proposal->status !== 'draft')
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Proposal Status: {{ ucfirst($proposal->status) }}
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>This proposal has already been sent. Changes will be saved but may require resending to notify the client.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('proposals.update', $proposal) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Proposal Title</label>
                            <input type="text" name="title" id="title"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('title') border-red-300 @enderror"
                                   value="{{ old('title', $proposal->title) }}" required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Client Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Lead Selection -->
                            <div>
                                <label for="lead_id" class="block text-sm font-medium text-gray-700">Select Lead</label>
                                <select name="lead_id" id="lead_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('lead_id') border-red-300 @enderror">
                                    <option value="">Choose a lead...</option>
                                    @foreach($leads as $lead)
                                        <option value="{{ $lead->id }}"
                                                data-name="{{ $lead->name }}"
                                                data-email="{{ $lead->email }}"
                                                data-company="{{ $lead->company }}"
                                                data-address="{{ $lead->address }}"
                                                data-company-number="{{ $lead->company_number }}"
                                                {{ old('lead_id', $proposal->lead_id) == $lead->id ? 'selected' : '' }}>
                                            {{ $lead->company }} - {{ $lead->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lead_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Customer Selection -->
                            <div>
                                <label for="customer_id" class="block text-sm font-medium text-gray-700">Or Select Existing Customer</label>
                                <select name="customer_id" id="customer_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('customer_id') border-red-300 @enderror">
                                    <option value="">Choose a customer...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                                data-name="{{ $customer->name }}"
                                                data-email="{{ $customer->email }}"
                                                data-company="{{ $customer->name }}"
                                                data-address="{{ $customer->address }}"
                                                data-company-number="{{ $customer->company_number }}"
                                                {{ old('customer_id', $proposal->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Manual Client Information -->
                        <div id="manual-client-info" class="border border-gray-200 rounded-md p-4 mb-4" style="display: {{ (!$proposal->lead_id && !$proposal->customer_id) ? 'block' : 'none' }};">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Manual Client Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name</label>
                                    <input type="text" name="client_name" id="client_name"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ old('client_name', $proposal->client_name) }}">
                                </div>
                                <div>
                                    <label for="client_email" class="block text-sm font-medium text-gray-700">Client Email</label>
                                    <input type="email" name="client_email" id="client_email"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ old('client_email', $proposal->client_email) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Template Selection -->
                        <div class="mb-4">
                            <label for="template_id" class="block text-sm font-medium text-gray-700">Select Template</label>
                            <select name="template_id" id="template_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('template_id') border-red-300 @enderror">
                                <option value="">Choose a template...</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}"
                                            data-content="{{ $template->content }}"
                                            {{ old('template_id', $proposal->template_id) == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} ({{ $template->type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Amount -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Proposal Amount</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" step="0.01" min="0"
                                           class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('amount') border-red-300 @enderror"
                                           value="{{ old('amount', $proposal->amount) }}">
                                </div>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="valid_until" class="block text-sm font-medium text-gray-700">Valid Until</label>
                                <input type="date" name="valid_until" id="valid_until"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('valid_until') border-red-300 @enderror"
                                       value="{{ old('valid_until', $proposal->valid_until ? $proposal->valid_until->format('Y-m-d') : '') }}">
                                @error('valid_until')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Content Editor -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Proposal Content</h3>

                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>

                            <!-- Hidden textarea for form submission -->
                            <textarea id="content" name="content" style="display: none;" required>{{ old('content', $proposal->content) }}</textarea>

                            <!-- Sun Editor Container -->
                            <div id="suneditor-container" class="@error('content') border-red-300 @enderror">
                                <!-- Sun Editor will be initialized here -->
                            </div>

                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-2 text-xs text-gray-500">
                                💡 Tip: Use variables like @{{client_name}}, @{{project_name}}, @{{amount}}, @{{date}} in your content
                            </div>
                        </div>

                        <!-- Variable Instructions -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Available Variables</h4>
                            <div class="text-sm text-blue-800 space-y-1">
                                <p>• Use double curly braces to create variables: <code class="bg-blue-100 px-1 rounded">@{{variable_name}}</code></p>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div class="space-y-1">
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{client_name}}</code>
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{client_email}}</code>
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{client_address}}</code>
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{client_company_number}}</code>
                                    </div>
                                    <div class="space-y-1">
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{amount}}</code>
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{date}}</code>
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{valid_until}}</code>
                                        <code class="bg-yellow-100 px-2 py-1 rounded text-yellow-800 block">@{{proposal_title}}</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('proposals.show', $proposal) }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200">
                                Cancel
                            </a>
                            <div class="flex space-x-3">
                                @if($proposal->status !== 'sent')
                                <button type="submit" name="status" value="draft"
                                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                    Save as Draft
                                </button>
                                @endif
                                <button type="submit" name="status" value="{{ $proposal->status === 'sent' ? 'sent' : 'sent' }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                    @if($proposal->status === 'sent')
                                        Update & Resend
                                    @else
                                        Save & Send
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Proposal Status -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Proposal Status</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Status:</span>
                                <span class="text-sm font-medium text-gray-900 capitalize">{{ $proposal->status }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Created:</span>
                                <span class="text-sm text-gray-900">{{ $proposal->created_at->format('M j, Y') }}</span>
                            </div>
                            @if($proposal->sent_at)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Sent:</span>
                                <span class="text-sm text-gray-900">{{ $proposal->sent_at->format('M j, Y') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Updated:</span>
                                <span class="text-sm text-gray-900">{{ $proposal->updated_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Preview</h3>
                        <div id="content-preview" class="bg-white p-4 rounded border min-h-[200px] sun-editor-editable" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; font-size: 14px;">
                            <p class="text-gray-500 italic">Loading preview...</p>
                        </div>
                    </div>

                    <!-- Help -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Need Help?</h3>
                        <div class="text-sm text-gray-600 space-y-2">
                            <p>• Update client information as needed</p>
                            <p>• Modify the content using the rich editor</p>
                            <p>• Use variables for dynamic content</p>
                            <p>• Save changes or resend to client</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/en.js"></script>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function() {
    const leadSelect = document.getElementById('lead_id');
    const customerSelect = document.getElementById('customer_id');
    const manualClientInfo = document.getElementById('manual-client-info');
    const templateSelect = document.getElementById('template_id');
    const contentTextarea = document.getElementById('content');
    const contentPreview = document.getElementById('content-preview');

    // Initialize Sun Editor
    const sunEditor = SUNEDITOR.create('suneditor-container', {
        lang: SUNEDITOR_LANG['en'],
        width: '100%',
        height: '400px',
        placeholder: 'Enter your proposal content here...',
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
        ]
    });

    // Set initial content
    if (contentTextarea.value) {
        sunEditor.setContents(contentTextarea.value);
    }

    // Update hidden textarea when editor content changes
    sunEditor.onChange = function(contents) {
        contentTextarea.value = contents;
        updatePreview(contents);
    };

    // Handle lead/customer selection
    function updateClientSelection() {
        const hasLead = leadSelect.value !== '';
        const hasCustomer = customerSelect.value !== '';

        if (hasLead) {
            customerSelect.value = '';
            manualClientInfo.style.display = 'none';
        } else if (hasCustomer) {
            leadSelect.value = '';
            manualClientInfo.style.display = 'none';
        } else {
            manualClientInfo.style.display = 'block';
        }
    }

    leadSelect.addEventListener('change', function() {
        updateClientSelection();
        updatePreview();
    });
    customerSelect.addEventListener('change', function() {
        updateClientSelection();
        updatePreview();
    });

    // Add event listeners for form fields that affect preview
    document.getElementById('title').addEventListener('input', updatePreview);
    document.getElementById('amount').addEventListener('input', updatePreview);
    document.getElementById('valid_until').addEventListener('change', updatePreview);
    document.getElementById('client_name').addEventListener('input', updatePreview);
    document.getElementById('client_email').addEventListener('input', updatePreview);

    // Handle template selection
    templateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const templateContent = selectedOption.getAttribute('data-content');
            if (templateContent) {
                sunEditor.setContents(templateContent);
                contentTextarea.value = templateContent;
                updatePreview(templateContent);
            }
        }
    });

    // Debounce function to limit preview requests
    let previewTimeout = null;
    const PREVIEW_DEBOUNCE_DELAY = 1000;

    // Update content preview
    function updatePreview(content = null) {
        if (previewTimeout) {
            clearTimeout(previewTimeout);
        }

        previewTimeout = setTimeout(() => {
            performPreviewUpdate(content);
        }, PREVIEW_DEBOUNCE_DELAY);
    }

    function performPreviewUpdate(content = null) {
        let editorContent = content || sunEditor.getContents();

        if (!editorContent || !editorContent.trim()) {
            contentPreview.innerHTML = '<p class="text-gray-500 italic">Start typing to see preview...</p>';
            return;
        }

        // Show loading state
        contentPreview.innerHTML = '<p class="text-blue-500 italic">Loading preview...</p>';

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            contentPreview.innerHTML = '<p class="text-red-500 italic">CSRF token not found. Please refresh the page.</p>';
            return;
        }

        // Prepare form data
        const requestData = {
            content: editorContent,
            lead_id: document.getElementById('lead_id').value || '',
            customer_id: document.getElementById('customer_id').value || '',
            title: document.getElementById('title').value || '',
            amount: document.getElementById('amount').value || '',
            valid_until: document.getElementById('valid_until').value || '',
            client_name: document.getElementById('client_name').value || '',
            client_email: document.getElementById('client_email').value || '',
            _token: csrfToken.getAttribute('content')
        };

        // Make preview request
        fetch('{{ route("proposals.live-preview") }}', {
            method: 'POST',
            body: JSON.stringify(requestData),
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': requestData._token,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                contentPreview.innerHTML = data.preview;
            } else {
                contentPreview.innerHTML = '<p class="text-red-500 italic">Error loading preview: ' + (data.message || 'Unknown error') + '</p>';
            }
        })
        .catch(error => {
            contentPreview.innerHTML = '<p class="text-red-500 italic">Error loading preview: ' + error.message + '</p>';
        });
    }

    // Handle submit button clicks
    const form = document.querySelector('form');
    const submitButtons = form.querySelectorAll('button[type="submit"]');

    submitButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const editorContent = sunEditor.getContents();
            contentTextarea.value = editorContent;

            if (!editorContent || editorContent.trim() === '') {
                e.preventDefault();
                alert('Please enter proposal content before submitting.');
                return false;
            }
        });
    });

    // Initialize
    updateClientSelection();
    updatePreview();
});
</script>
@endsection
