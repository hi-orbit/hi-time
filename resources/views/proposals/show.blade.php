@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
<style>
/* Custom styles for proposal content display */
.proposal-content {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #374151;
}

.proposal-content h1 {
    font-size: 2rem;
    font-weight: bold;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #111827;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

.proposal-content h2 {
    font-size: 1.5rem;
    font-weight: bold;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: #111827;
}

.proposal-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
    color: #111827;
}

.proposal-content h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    color: #111827;
}

.proposal-content h5,
.proposal-content h6 {
    font-size: 1rem;
    font-weight: 600;
    margin-top: 0.75rem;
    margin-bottom: 0.5rem;
    color: #111827;
}

.proposal-content p {
    margin-bottom: 1rem;
    text-align: justify;
}

.proposal-content ul,
.proposal-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.proposal-content ul {
    list-style-type: disc;
}

.proposal-content ol {
    list-style-type: decimal;
}

.proposal-content li {
    margin-bottom: 0.25rem;
}

.proposal-content blockquote {
    border-left: 4px solid #3b82f6;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    background-color: #f8fafc;
    padding: 1rem;
    border-radius: 0.375rem;
}

.proposal-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
    border: 1px solid #d1d5db;
}

.proposal-content th,
.proposal-content td {
    border: 1px solid #d1d5db;
    padding: 0.75rem;
    text-align: left;
}

.proposal-content th {
    background-color: #f9fafb;
    font-weight: 600;
}

.proposal-content img {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
    border-radius: 0.375rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.proposal-content strong {
    font-weight: 600;
    color: #111827;
}

.proposal-content em {
    font-style: italic;
}

.proposal-content u {
    text-decoration: underline;
}

.proposal-content code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}

.proposal-content pre {
    background-color: #f3f4f6;
    padding: 1rem;
    border-radius: 0.375rem;
    overflow-x: auto;
    margin: 1rem 0;
}

.proposal-content pre code {
    background-color: transparent;
    padding: 0;
}

.proposal-content a {
    color: #3b82f6;
    text-decoration: underline;
}

.proposal-content a:hover {
    color: #1d4ed8;
}

.proposal-content hr {
    border: none;
    border-top: 1px solid #d1d5db;
    margin: 2rem 0;
}

/* Ensure proper spacing for nested elements */
.proposal-content div {
    margin-bottom: 0.5rem;
}

.proposal-content div:last-child {
    margin-bottom: 0;
}

/* Fix for any inline styles that might override */
.proposal-content * {
    max-width: 100% !important;
}

/* Print styles */
@media print {
    .proposal-content {
        font-size: 12pt;
        line-height: 1.4;
    }
}
</style>
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
                        Proposal #{{ $proposal->proposal_number }}
                    </h1>
                    <div class="ml-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($proposal->status === 'draft') bg-yellow-100 text-yellow-800
                            @elseif($proposal->status === 'sent') bg-blue-100 text-blue-800
                            @elseif($proposal->status === 'accepted') bg-green-100 text-green-800
                            @elseif($proposal->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($proposal->status) }}
                        </span>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $proposal->title }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('proposals.edit', $proposal) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    Edit Proposal
                </a>
                @if($proposal->status === 'draft')
                    <form action="{{ route('proposals.send', $proposal) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            Send Proposal
                        </button>
                    </form>
                @endif
                <a href="{{ route('proposals.preview', $proposal) }}" target="_blank"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    Preview
                </a>
                <a href="{{ route('proposals.pdf', $proposal) }}"
                   class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    Download PDF
                </a>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Proposal Details -->
                <div class="bg-white shadow rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Proposal Details</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Proposal Number</dt>
                            <dd class="text-sm text-gray-900">{{ $proposal->proposal_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Amount</dt>
                            <dd class="text-sm text-gray-900">
                                @if($proposal->amount)
                                    £{{ number_format($proposal->amount, 2) }}
                                @else
                                    <span class="text-gray-400">Not specified</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                            <dd class="text-sm text-gray-900">
                                @if($proposal->valid_until)
                                    {{ $proposal->valid_until->format('d F Y') }}
                                @else
                                    <span class="text-gray-400">Not specified</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-900">{{ $proposal->created_at->format('d F Y \a\t H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Recipient</dt>
                            <dd class="text-sm text-gray-900">
                                @if($proposal->lead)
                                    {{ $proposal->lead->name }} ({{ $proposal->lead->company }})
                                    <br><span class="text-gray-500">{{ $proposal->lead->email }}</span>
                                @elseif($proposal->customer)
                                    {{ $proposal->customer->name }}
                                    <br><span class="text-gray-500">{{ $proposal->customer->email }}</span>
                                @elseif($proposal->client_name)
                                    {{ $proposal->client_name }}
                                    @if($proposal->client_email)
                                        <br><span class="text-gray-500">{{ $proposal->client_email }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">No recipient specified</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="text-sm text-gray-900">
                                @if($proposal->creator)
                                    {{ $proposal->creator->name }}
                                @else
                                    <span class="text-gray-400">Unknown</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Proposal Content -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Proposal Content</h2>
                    <div class="proposal-content bg-white p-6 border border-gray-200 rounded-lg">
                        @php
                            // Process template variables in the content
                            $processedContent = $proposal->content;

                            // Get client data for replacements
                            $clientData = [];
                            if ($proposal->lead) {
                                $clientData = [
                                    'client_name' => $proposal->lead->name ?? '',
                                    'client_email' => $proposal->lead->email ?? '',
                                    'client_address' => $proposal->lead->address ?? '',
                                    'client_company_number' => $proposal->lead->company_number ?? '',
                                    'company_name' => $proposal->lead->company ?? '',
                                    'company_number' => $proposal->lead->company_number ?? '',
                                    'company_address' => $proposal->lead->address ?? '',
                                ];
                            } elseif ($proposal->customer) {
                                $clientData = [
                                    'client_name' => $proposal->customer->name ?? '',
                                    'client_email' => $proposal->customer->email ?? '',
                                    'client_address' => $proposal->customer->address ?? '',
                                    'client_company_number' => $proposal->customer->company_number ?? '',
                                    'company_name' => $proposal->customer->company ?? $proposal->customer->name ?? '',
                                    'company_number' => $proposal->customer->company_number ?? '',
                                    'company_address' => $proposal->customer->address ?? '',
                                ];
                            } elseif ($proposal->client_name) {
                                $clientData = [
                                    'client_name' => $proposal->client_name ?? '',
                                    'client_email' => $proposal->client_email ?? '',
                                    'client_address' => '',
                                    'client_company_number' => '',
                                    'company_name' => $proposal->client_name ?? '',
                                    'company_number' => '',
                                    'company_address' => '',
                                ];
                            }

                            // Add proposal data
                            $proposalData = [
                                'proposal_title' => $proposal->title ?? '',
                                'amount' => $proposal->amount ? '£' . number_format($proposal->amount, 2) : '',
                                'date' => now()->format('F j, Y'),
                                'valid_until' => $proposal->valid_until ? $proposal->valid_until->format('F j, Y') : '',
                            ];

                            // Split names for first/last name variables
                            if (isset($clientData['client_name'])) {
                                $nameParts = explode(' ', $clientData['client_name'], 2);
                                $clientData['first_name'] = $nameParts[0] ?? '';
                                $clientData['last_name'] = $nameParts[1] ?? '';
                            }

                            // Merge all data
                            $allData = array_merge($clientData, $proposalData);

                            // Replace variables in content using robust pattern matching
                            foreach ($allData as $key => $value) {
                                // Replace with double curly braces (with optional whitespace)
                                $processedContent = preg_replace('/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/i', $value, $processedContent);
                                // Also try single curly braces (in case editor uses different format)
                                $processedContent = preg_replace('/\{\s*' . preg_quote($key, '/') . '\s*\}/i', $value, $processedContent);
                            }
                        @endphp
                        {!! $processedContent !!}
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($proposal->status === 'draft')
                                    <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
                                @elseif($proposal->status === 'sent')
                                    <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                @elseif($proposal->status === 'accepted')
                                    <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                @elseif($proposal->status === 'rejected')
                                    <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                                @else
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                @endif
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ ucfirst($proposal->status) }}</p>
                                <p class="text-sm text-gray-500">Current status</p>
                            </div>
                        </div>

                        @if($proposal->sent_at)
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Sent</p>
                                    <p class="text-sm text-gray-500">{{ $proposal->sent_at->format('d F Y \a\t H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($proposal->viewed_at)
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Viewed</p>
                                    <p class="text-sm text-gray-500">{{ $proposal->viewed_at->format('d F Y \a\t H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($proposal->responded_at)
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 bg-purple-400 rounded-full"></div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Responded</p>
                                    <p class="text-sm text-gray-500">{{ $proposal->responded_at->format('d F Y \a\t H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('proposals.edit', $proposal) }}"
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-center block">
                            Edit Proposal
                        </a>
                        @if($proposal->status === 'draft')
                            <form action="{{ route('proposals.send', $proposal) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                    Send to Client
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('proposals.preview', $proposal) }}" target="_blank"
                           class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-center block">
                            Preview
                        </a>
                        <a href="{{ route('proposals.pdf', $proposal) }}"
                           class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-center block">
                            Download PDF
                        </a>
                        <form action="{{ route('proposals.destroy', $proposal) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this proposal?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                Delete Proposal
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Template Info -->
                @if($proposal->template)
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Template Used</h3>
                        <p class="text-sm font-medium text-gray-900">{{ $proposal->template->name }}</p>
                        <p class="text-sm text-gray-500">{{ $proposal->template->type }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
