@extends('layouts.app')

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
                                                {{ old('lead_id', $selectedLead?->id) == $lead->id ? 'selected' : '' }}>
                                            {{ $lead->company_name }} - {{ $lead->contact_name }}
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
                            <textarea name="content" id="content" rows="15"
                                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('content') border-red-300 @enderror"
                                      placeholder="Enter your proposal content here...">{{ old('content') }}</textarea>
                            @error('content')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Content Help -->
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Content Tips</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>You can use variables in your content that will be replaced with actual values:</p>
                                        <ul class="list-disc list-inside mt-1 space-y-1">
                                            <li><code>@{client_name}</code> - Client's name</li>
                                            <li><code>@{client_email}</code> - Client's email</li>
                                            <li><code>@{proposal_title}</code> - Proposal title</li>
                                            <li><code>@{amount}</code> - Proposal amount</li>
                                            <li><code>@{date}</code> - Current date</li>
                                        </ul>
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
                        <div id="content-preview" class="prose prose-sm max-w-none bg-gray-50 p-3 rounded border min-h-[200px]">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const leadSelect = document.getElementById('lead_id');
    const customerSelect = document.getElementById('customer_id');
    const manualClientInfo = document.getElementById('manual-client-info');
    const templateSelect = document.getElementById('template_id');
    const contentTextarea = document.getElementById('content');
    const contentPreview = document.getElementById('content-preview');
    const templateVariables = document.getElementById('template-variables');

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

    leadSelect.addEventListener('change', updateClientSelection);
    customerSelect.addEventListener('change', updateClientSelection);

    // Handle template selection
    templateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const templateContent = selectedOption.getAttribute('data-content');
            if (templateContent) {
                contentTextarea.value = templateContent;
                updatePreview();
            }
        }
    });

    // Update content preview
    function updatePreview() {
        const content = contentTextarea.value;
        if (content.trim()) {
            // Simple preview - replace line breaks with <br> tags
            const preview = content.replace(/\n/g, '<br>');
            contentPreview.innerHTML = preview;
        } else {
            contentPreview.innerHTML = '<p class="text-gray-500 italic">Select a template or start typing to see preview...</p>';
        }
    }

    contentTextarea.addEventListener('input', updatePreview);

    // Initialize
    updateClientSelection();
    updatePreview();
});
</script>
@endsection
