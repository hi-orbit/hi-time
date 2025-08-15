@extends('layouts.app')

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
                        Proposal Preview
                    </h1>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Preview how this proposal will appear to the client
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('proposals.pdf', $proposal) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>

        <!-- Proposal Preview -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Header Section -->
            <div class="bg-blue-600 text-white p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-3xl font-bold">{{ $proposal->title }}</h2>
                        <p class="mt-2 text-blue-100">Proposal #{{ $proposal->proposal_number }}</p>
                    </div>
                    <div class="text-right">
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($proposal->status === 'draft') bg-yellow-200 text-yellow-800
                            @elseif($proposal->status === 'sent') bg-blue-200 text-blue-800
                            @elseif($proposal->status === 'accepted') bg-green-200 text-green-800
                            @elseif($proposal->status === 'rejected') bg-red-200 text-red-800
                            @else bg-gray-200 text-gray-800 @endif">
                            {{ ucfirst($proposal->status) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="p-6">
                <!-- Proposal Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Proposal Information</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="text-sm text-gray-900">{{ $proposal->created_at->format('F j, Y') }}</dd>
                            </div>
                            @if($proposal->valid_until)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($proposal->valid_until)->format('F j, Y') }}</dd>
                            </div>
                            @endif
                            @if($proposal->amount)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                <dd class="text-lg font-semibold text-green-600">${{ number_format($proposal->amount, 2) }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Client Information</h3>
                        @if($proposal->lead)
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Lead</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->lead->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->lead->email }}</dd>
                                </div>
                                @if($proposal->lead->company)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Company</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->lead->company }}</dd>
                                </div>
                                @endif
                                @if($proposal->lead->phone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->lead->phone }}</dd>
                                </div>
                                @endif
                            </dl>
                        @elseif($proposal->customer)
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Customer</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->customer->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->customer->email }}</dd>
                                </div>
                                @if($proposal->customer->company)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Company</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->customer->company }}</dd>
                                </div>
                                @endif
                                @if($proposal->customer->phone)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->customer->phone }}</dd>
                                </div>
                                @endif
                            </dl>
                        @elseif($proposal->client_name || $proposal->client_email)
                            <dl class="space-y-2">
                                @if($proposal->client_name)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->client_name }}</dd>
                                </div>
                                @endif
                                @if($proposal->client_email)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900">{{ $proposal->client_email }}</dd>
                                </div>
                                @endif
                            </dl>
                        @else
                            <p class="text-sm text-gray-500 italic">No client information specified</p>
                        @endif
                    </div>
                </div>

                <!-- Proposal Content -->
                @if($proposal->content)
                <div class="border-t border-gray-200 pt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Proposal Content</h3>
                    <div class="proposal-content">
                        @php
                            // Process template variables in the content
                            $processedContent = $proposal->content;

                            // Get client data for replacements
                            $clientData = [];
                            if ($proposal->lead) {
                                $clientData = [
                                    'client_name' => $proposal->lead->name,
                                    'client_email' => $proposal->lead->email,
                                    'client_address' => $proposal->lead->address,
                                    'client_company_number' => $proposal->lead->company_number,
                                    'company_name' => $proposal->lead->company,
                                    'company_number' => $proposal->lead->company_number,
                                    'company_address' => $proposal->lead->address,
                                ];
                            } elseif ($proposal->customer) {
                                $clientData = [
                                    'client_name' => $proposal->customer->name,
                                    'client_email' => $proposal->customer->email,
                                    'client_address' => $proposal->customer->address,
                                    'client_company_number' => $proposal->customer->company_number,
                                    'company_name' => $proposal->customer->company,
                                    'company_number' => $proposal->customer->company_number,
                                    'company_address' => $proposal->customer->address,
                                ];
                            } elseif ($proposal->client_name || $proposal->client_email) {
                                $clientData = [
                                    'client_name' => $proposal->client_name,
                                    'client_email' => $proposal->client_email,
                                    'client_address' => '',
                                    'client_company_number' => '',
                                    'company_name' => '',
                                    'company_number' => '',
                                    'company_address' => '',
                                ];
                            }

                            // Create replacements array
                            $replacements = [
                                'client_name' => $clientData['client_name'] ?? '[Client Name]',
                                'client_email' => $clientData['client_email'] ?? '[Client Email]',
                                'client_address' => $clientData['client_address'] ?? '[Client Address]',
                                'client_company_number' => $clientData['client_company_number'] ?? '[Company Number]',
                                'company_name' => $clientData['company_name'] ?? '[Company Name]',
                                'company_number' => $clientData['client_company_number'] ?? '[Company Number]',
                                'company_address' => $clientData['client_address'] ?? '[Client Address]',
                                'proposal_title' => $proposal->title,
                                'amount' => $proposal->amount ? '$' . number_format($proposal->amount, 2) : '[Amount]',
                                'date' => now()->format('j F Y'),
                                'valid_until' => $proposal->valid_until ? \Carbon\Carbon::parse($proposal->valid_until)->format('j F Y') : '[Valid Until Date]',
                                'first_name' => $clientData['client_name'] ? explode(' ', $clientData['client_name'])[0] : '[First Name]',
                                'last_name' => $clientData['client_name'] ? implode(' ', array_slice(explode(' ', $clientData['client_name']), 1)) : '[Last Name]'
                            ];

                            // Replace template variables
                            foreach ($replacements as $key => $value) {
                                $processedContent = preg_replace('/\{\{' . preg_quote($key) . '\}\}/', $value, $processedContent);
                            }
                        @endphp

                        <div class="prose prose-lg max-w-none
                                    prose-headings:text-gray-900
                                    prose-h1:text-3xl prose-h1:font-bold prose-h1:mb-6 prose-h1:mt-8
                                    prose-h2:text-2xl prose-h2:font-semibold prose-h2:mb-4 prose-h2:mt-6
                                    prose-h3:text-xl prose-h3:font-semibold prose-h3:mb-3 prose-h3:mt-5
                                    prose-p:text-gray-700 prose-p:leading-relaxed prose-p:mb-4
                                    prose-ul:mb-4 prose-ol:mb-4
                                    prose-li:text-gray-700 prose-li:mb-2
                                    prose-strong:text-gray-900 prose-strong:font-semibold
                                    prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline
                                    prose-blockquote:border-blue-200 prose-blockquote:bg-blue-50 prose-blockquote:p-4
                                    prose-table:table-auto prose-table:w-full
                                    prose-th:bg-gray-50 prose-th:p-3 prose-th:text-left prose-th:font-semibold
                                    prose-td:p-3 prose-td:border-t prose-td:border-gray-200">
                            {!! $processedContent !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Template Information -->
                @if($proposal->template)
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <p class="text-sm text-gray-500">
                        <span class="font-medium">Template:</span> {{ $proposal->template->name }}
                    </p>
                </div>
                @endif

                <!-- Acceptance Information -->
                @if($proposal->status === 'accepted' && $proposal->accepted_at)
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <div class="bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Proposal Accepted</h3>
                                <div class="mt-1 text-sm text-green-700">
                                    <p>This proposal was accepted on {{ $proposal->accepted_at->format('F j, Y \a\t g:i A') }}.</p>
                                    @if($proposal->signature_data)
                                        <p class="mt-1">Digital signature recorded.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Additional custom styles for proposal content */
.proposal-content {
    line-height: 1.8;
}

.proposal-content h1 {
    color: #1f2937;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    margin-top: 2rem;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

.proposal-content h2 {
    color: #374151;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    margin-top: 1.5rem;
}

.proposal-content h3 {
    color: #4b5563;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    margin-top: 1.25rem;
}

.proposal-content p {
    color: #4b5563;
    margin-bottom: 1rem;
    line-height: 1.75;
}

.proposal-content ul,
.proposal-content ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.proposal-content li {
    color: #4b5563;
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.proposal-content strong {
    color: #1f2937;
    font-weight: 600;
}

.proposal-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.proposal-content th {
    background-color: #f9fafb;
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid #d1d5db;
}

.proposal-content td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.proposal-content blockquote {
    border-left: 4px solid #3b82f6;
    background-color: #eff6ff;
    padding: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
}

.proposal-content hr {
    border: none;
    border-top: 1px solid #d1d5db;
    margin: 2rem 0;
}
</style>
@endsection
