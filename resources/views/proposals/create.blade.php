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
                    <a href="{{ route('proposals.index') }}" class="text-gray-500 hover:text-gray-700 mr-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Create New Proposal
                    </h1>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Create a professional proposal for a lead or existing customer
                </p>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('proposals.store') }}" method="POST" class="space-y-6">
            @csrf

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
                                   value="{{ old('title') }}" required>
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
                                                {{ old('lead_id', $selectedLead?->id) == $lead->id ? 'selected' : '' }}>
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
                                                {{ old('customer_id', $selectedCustomer?->id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Manual Client Information (for when no lead/customer selected) -->
                        <div id="manual-client-info" class="border border-gray-200 rounded-md p-4 mb-4" style="display: none;">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Manual Client Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name</label>
                                    <input type="text" name="client_name" id="client_name"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ old('client_name') }}">
                                </div>
                                <div>
                                    <label for="client_email" class="block text-sm font-medium text-gray-700">Client Email</label>
                                    <input type="email" name="client_email" id="client_email"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ old('client_email') }}">
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
                                            {{ old('template_id') == $template->id ? 'selected' : '' }}>
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
                                           value="{{ old('amount') }}">
                                </div>
                                @error('amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="valid_until" class="block text-sm font-medium text-gray-700">Valid Until</label>
                                <input type="date" name="valid_until" id="valid_until"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('valid_until') border-red-300 @enderror"
                                       value="{{ old('valid_until') }}">
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
                            <textarea id="content" name="content" style="display: none;" required>{{ old('content') }}</textarea>

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
                            <a href="{{ route('proposals.index') }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200">
                                Cancel
                            </a>
                            <div class="flex space-x-3">
                                <button type="submit" name="status" value="draft"
                                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                    Save as Draft
                                </button>
                                <button type="submit" name="status" value="sent"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                    Create & Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Preview -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Preview</h3>
                        <div id="content-preview" class="bg-white p-4 rounded border min-h-[200px] sun-editor-editable" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; font-size: 14px;">
                            <p class="text-gray-500 italic">Select a template or start typing to see preview...</p>
                        </div>
                    </div>

                    <!-- Template Variables -->
                    <div id="template-variables" class="bg-white shadow rounded-lg p-6" style="display: none;">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Template Variables</h3>
                        <div id="variables-list" class="space-y-2">
                            <!-- Variables will be populated by JavaScript -->
                        </div>
                    </div>

                    <!-- Help -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Need Help?</h3>
                        <div class="text-sm text-gray-600 space-y-2">
                            <p>• Select either a lead or existing customer</p>
                            <p>• Choose a template to pre-fill content</p>
                            <p>• Customize the content as needed</p>
                            <p>• Save as draft or send immediately</p>
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
    const templateVariables = document.getElementById('template-variables');

    // Initialize Sun Editor
    const sunEditor = SUNEDITOR.create('suneditor-container', {
        lang: SUNEDITOR_LANG['en'],
        width: '100%',
        height: '400px',
        placeholder: `Enter your proposal content here...

Use variables like:
• @{{client_name}} for client name
• @{{client_email}} for client email
• @{{client_address}} for client address
• @{{client_company_number}} for company number
• @{{amount}} for proposal amount
• @{{date}} for current date
• @{{valid_until}} for validity date
• @{{proposal_title}} for proposal title

Example:
Dear @{{client_name}},

We are pleased to submit this proposal for your consideration.

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
            ['table', 'link'],
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

    // Set initial content if available
    if (contentTextarea.value) {
        sunEditor.setContents(contentTextarea.value);
    } else {
        // Set comprehensive test content with all placeholders
        const placeholders = {
            start: '{' + '{',
            end: '}' + '}'
        };

        const testContent = '<h1>Service Proposal</h1>' +
        '<h2>' + placeholders.start + 'proposal_title' + placeholders.end + '</h2>' +
        '<p><strong>Date:</strong> ' + placeholders.start + 'date' + placeholders.end + '</p>' +
        '<p><strong>Valid Until:</strong> ' + placeholders.start + 'valid_until' + placeholders.end + '</p>' +
        '<hr>' +
        '<h3>Client Information</h3>' +
        '<p>Dear ' + placeholders.start + 'client_name' + placeholders.end + ',</p>' +
        '<p>We are pleased to submit this proposal for your consideration.</p>' +
        '<p><strong>Client Details:</strong></p>' +
        '<ul>' +
        '<li><strong>Name:</strong> ' + placeholders.start + 'client_name' + placeholders.end + '</li>' +
        '<li><strong>Email:</strong> ' + placeholders.start + 'client_email' + placeholders.end + '</li>' +
        '<li><strong>Address:</strong> ' + placeholders.start + 'client_address' + placeholders.end + '</li>' +
        '<li><strong>Company:</strong> ' + placeholders.start + 'company_name' + placeholders.end + '</li>' +
        '<li><strong>Company Number:</strong> ' + placeholders.start + 'client_company_number' + placeholders.end + '</li>' +
        '</ul>' +
        '<h3>Proposal Details</h3>' +
        '<p>The total investment for this project is <strong>' + placeholders.start + 'amount' + placeholders.end + '</strong>.</p>' +
        '<p>This proposal is valid until <strong>' + placeholders.start + 'valid_until' + placeholders.end + '</strong>.</p>' +
        '<h3>Additional Information</h3>' +
        '<p>First Name: ' + placeholders.start + 'first_name' + placeholders.end + '</p>' +
        '<p>Last Name: ' + placeholders.start + 'last_name' + placeholders.end + '</p>' +
        '<p>Current Date: ' + placeholders.start + 'date' + placeholders.end + '</p>' +
        '<p>Company Address: ' + placeholders.start + 'company_address' + placeholders.end + '</p>' +
        '<br>' +
        '<p>Thank you for considering our services.</p>' +
        '<p>Best regards,<br>Your Company Name</p>';

        sunEditor.setContents(testContent);
        contentTextarea.value = testContent;

        // Set some sample form data to demonstrate replacements
        document.getElementById('title').value = 'Website Development Proposal';
        document.getElementById('amount').value = '5000.00';
        document.getElementById('valid_until').value = '2025-08-31';
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
        updatePreview(); // Update preview when lead changes
    });
    customerSelect.addEventListener('change', function() {
        updateClientSelection();
        updatePreview(); // Update preview when customer changes
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
    const PREVIEW_DEBOUNCE_DELAY = 1000; // Wait 1 second after user stops typing

    // Update content preview using server-side processing (simplified)
    function updatePreview(content = null) {
        // Clear existing timeout
        if (previewTimeout) {
            clearTimeout(previewTimeout);
        }

        // Debounce the preview update
        previewTimeout = setTimeout(() => {
            performPreviewUpdate(content);
        }, PREVIEW_DEBOUNCE_DELAY);
    }

    function performPreviewUpdate(content = null) {
        let editorContent = content || sunEditor.getContents();

        if (!editorContent || !editorContent.trim()) {
            contentPreview.innerHTML = '<p class="text-gray-500 italic">Select a template or start typing to see preview...</p>';
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

        // Debug: Log what we're sending
        console.log('Preview requestData:', {
            lead_id: requestData.lead_id,
            customer_id: requestData.customer_id,
            title: requestData.title,
            amount: requestData.amount,
            content_length: requestData.content.length
        });

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
                alert('Please enter proposal content before submitting.');
                return false;
            }

            // Let the form submit naturally - no interference with form submission
        });
    });

    // Initialize
    updateClientSelection();
    updatePreview();
});
</script>
@endsection
