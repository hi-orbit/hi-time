@extends('layouts.app')

@push('styles')
<style>
/* Content editable styles */
#content-area[contenteditable="true"] {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Formatting toolbar */
#format-toolbar {
    position: sticky;
    top: 0;
    z-index: 50;
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    padding: 8px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-bottom: none;
    border-radius: 0.375rem 0.375rem 0 0;
}
#format-toolbar .tb-group {
    display: flex;
    align-items: center;
    gap: 2px;
}
#format-toolbar .tb-divider {
    width: 1px;
    height: 24px;
    background: #d1d5db;
    margin: 0 4px;
}
#format-toolbar button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid transparent;
    border-radius: 4px;
    background: transparent;
    color: #374151;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.15s;
}
#format-toolbar button:hover {
    background: #e5e7eb;
    border-color: #d1d5db;
}
#format-toolbar button.active {
    background: #3b82f6;
    color: #fff;
}
#format-toolbar select {
    height: 32px;
    padding: 0 6px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: #fff;
    font-size: 12px;
    color: #374151;
    cursor: pointer;
}
#format-toolbar select:hover {
    border-color: #9ca3af;
}
#format-toolbar input[type="color"] {
    width: 32px;
    height: 32px;
    padding: 2px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    cursor: pointer;
}
</style>
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
                <button onclick="copyProposalLink('{{ $proposal->public_url }}')"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Copy Customer Link
                </button>
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
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Proposal Content</h2>
                        @if($proposal->canBeEdited())
                            <div id="edit-controls">
                                <button type="button" id="edit-content-btn" onclick="toggleInlineEdit()"
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit Content
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Formatting Toolbar (hidden by default) -->
                    <div id="format-toolbar" class="hidden">
                        <div class="tb-group">
                            <select id="tb-formatblock" onchange="execFormat('formatBlock', this.value); this.value='';">
                                <option value="">Style</option>
                                <option value="p">Paragraph</option>
                                <option value="h1">Heading 1</option>
                                <option value="h2">Heading 2</option>
                                <option value="h3">Heading 3</option>
                                <option value="h4">Heading 4</option>
                                <option value="h5">Heading 5</option>
                                <option value="h6">Heading 6</option>
                                <option value="pre">Preformatted</option>
                            </select>
                            <select id="tb-fontsize" onchange="execFormat('fontSize', this.value); this.value='';">
                                <option value="">Size</option>
                                <option value="1">Small</option>
                                <option value="3">Normal</option>
                                <option value="5">Large</option>
                                <option value="7">Huge</option>
                            </select>
                        </div>
                        <div class="tb-divider"></div>
                        <div class="tb-group">
                            <button onclick="execFormat('bold')" title="Bold"><b>B</b></button>
                            <button onclick="execFormat('italic')" title="Italic"><i>I</i></button>
                            <button onclick="execFormat('underline')" title="Underline"><u>U</u></button>
                            <button onclick="execFormat('strikeThrough')" title="Strikethrough"><s>S</s></button>
                        </div>
                        <div class="tb-divider"></div>
                        <div class="tb-group">
                            <input type="color" id="tb-fontcolor" value="#000000" onchange="execFormat('foreColor', this.value)" title="Text Color">
                            <input type="color" id="tb-bgcolor" value="#ffffff" onchange="execFormat('hiliteColor', this.value)" title="Highlight Color">
                        </div>
                        <div class="tb-divider"></div>
                        <div class="tb-group">
                            <button onclick="execFormat('justifyLeft')" title="Align Left">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v1.5H2V4zm0 4.5h10v1.5H2V8.5zm0 4.5h16v1.5H2v-1.5zm0 4.5h10v1.5H2v-1.5z"/></svg>
                            </button>
                            <button onclick="execFormat('justifyCenter')" title="Center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v1.5H2V4zm3.5 4.5h9v1.5h-9V8.5zm3.5 4.5h2v1.5h-2v-1.5zm-3.5 4.5h9v1.5h-9v-1.5z"/></svg>
                            </button>
                            <button onclick="execFormat('justifyRight')" title="Align Right">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v1.5H2V4zm6 4.5h10v1.5H8V8.5zm6 4.5h4v1.5h-4v-1.5zm-6 4.5h10v1.5H8v-1.5z"/></svg>
                            </button>
                            <button onclick="execFormat('justifyFull')" title="Justify">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v1.5H2V4zm0 4.5h16v1.5H2V8.5zm0 4.5h16v1.5H2v-1.5zm0 4.5h16v1.5H2v-1.5z"/></svg>
                            </button>
                        </div>
                        <div class="tb-divider"></div>
                        <div class="tb-group">
                            <button onclick="execFormat('insertUnorderedList')" title="Bullet List">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="3" cy="5" r="1.5"/><circle cx="3" cy="10" r="1.5"/><circle cx="3" cy="15" r="1.5"/><path d="M7 4.25h12v1.5H7V4.25zm0 5.75h12v1.5H7V10zm0 5.75h12v1.5H7V15.75z"/></svg>
                            </button>
                            <button onclick="execFormat('insertOrderedList')" title="Numbered List">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><text x="0.5" y="6.5" font-size="5" font-weight="bold">1.</text><text x="0.5" y="11.5" font-size="5" font-weight="bold">2.</text><text x="0.5" y="16.5" font-size="5" font-weight="bold">3.</text><path d="M7 4.25h12v1.5H7V4.25zm0 5.75h12v1.5H7V10zm0 5.75h12v1.5H7V15.75z"/></svg>
                            </button>
                            <button onclick="execFormat('indent')" title="Indent">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v1.5H2V4zm8 3.5h8v1.5h-8V7.5zm-4 3.5h12v1.5H6V11zm-4 3.5h16v1.5H2V14.5z"/></svg>
                            </button>
                            <button onclick="execFormat('outdent')" title="Outdent">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4h16v1.5H2V4zm4 3.5h8v1.5H6V7.5zm0 3.5h12v1.5H6V11zm0 3.5h12v1.5H6V14.5z"/></svg>
                            </button>
                        </div>
                        <div class="tb-divider"></div>
                        <div class="tb-group">
                            <button onclick="execFormat('insertHorizontalRule')" title="Horizontal Line">―</button>
                            <button onclick="insertTableDialog()" title="Insert Table">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><rect x="1" y="2" width="18" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="1.5"/><line x1="1" y1="7" x2="19" y2="7" stroke="currentColor" stroke-width="1"/><line x1="1" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="1"/><line x1="7" y1="2" x2="7" y2="18" stroke="currentColor" stroke-width="1"/><line x1="13" y1="2" x2="13" y2="18" stroke="currentColor" stroke-width="1"/></svg>
                            </button>
                        </div>
                        <div class="tb-divider"></div>
                        <div class="tb-group">
                            <button onclick="execFormat('undo')" title="Undo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h11a4 4 0 014 4 4 4 0 01-4 4H8m-4-4l4-4m-4 4l4 4"/></svg>
                            </button>
                            <button onclick="execFormat('redo')" title="Redo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10H10a4 4 0 00-4 4 4 4 0 004 4h5m4-4l-4-4m4 4l-4 4"/></svg>
                            </button>
                            <button onclick="execFormat('removeFormat')" title="Clear Formatting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M7 7l5-5 5 5M9 17l-3 3M15 17l3 3"/></svg>
                            </button>
                            <button onclick="toggleSourceView()" id="tb-source-btn" title="View Source">&lt;&gt;</button>
                        </div>
                    </div>

                    <!-- Proposal Content Area (becomes editable when Edit is clicked) -->
                    <div id="content-area"
                         class="proposal-content bg-white p-6 border border-gray-200 rounded-lg min-h-[200px]"
                         contenteditable="false">
                        @php
                            $processedContent = $proposal->content;

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

                            $proposalData = [
                                'proposal_title' => $proposal->title ?? '',
                                'amount' => $proposal->amount ? '£' . number_format($proposal->amount, 2) : '',
                                'date' => now()->format('F j, Y'),
                                'valid_until' => $proposal->valid_until ? $proposal->valid_until->format('F j, Y') : '',
                            ];

                            if (isset($clientData['client_name'])) {
                                $nameParts = explode(' ', $clientData['client_name'], 2);
                                $clientData['first_name'] = $nameParts[0] ?? '';
                                $clientData['last_name'] = $nameParts[1] ?? '';
                            }

                            $allData = array_merge($clientData, $proposalData);

                            foreach ($allData as $key => $value) {
                                $processedContent = preg_replace('/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/i', $value, $processedContent);
                                $processedContent = preg_replace('/\{\s*' . preg_quote($key, '/') . '\s*\}/i', $value, $processedContent);
                            }
                        @endphp
                        {!! $processedContent !!}
                    </div>

                    <!-- Save/Cancel bar (hidden by default) -->
                    <div id="edit-bar" class="hidden mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-500" id="save-status"></div>
                        <div class="flex space-x-3">
                            <button type="button" onclick="cancelInlineEdit()"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200">
                                Cancel
                            </button>
                            <button type="button" onclick="saveInlineEdit()"
                                    id="save-content-btn"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                Save Changes
                            </button>
                        </div>
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

<!-- Success notification for copy link -->
<div id="copy-success" class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50 hidden">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        Customer link copied to clipboard!
    </div>
</div>

<script>
function copyProposalLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        // Show success notification
        const notification = document.getElementById('copy-success');
        notification.classList.remove('hidden');

        // Hide notification after 3 seconds
        setTimeout(function() {
            notification.classList.add('hidden');
        }, 3000);
    }).catch(function(err) {
        console.error('Failed to copy text: ', err);
        alert('Failed to copy link. Please try again.');
    });
}
</script>

<script>
function copyProposalLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        const notification = document.getElementById('copy-success');
        notification.classList.remove('hidden');
        setTimeout(function() {
            notification.classList.add('hidden');
        }, 3000);
    }).catch(function(err) {
        console.error('Failed to copy text: ', err);
        alert('Failed to copy link. Please try again.');
    });
}
</script>

<script>
const proposalUrl = "{{ route('proposals.update', $proposal) }}";
const csrfToken = "{{ csrf_token() }}";
const originalContent = {!! json_encode($proposal->content) !!};
let isEditing = false;
let previousContent = null;
let isSourceView = false;

/** Execute a formatting command on the contenteditable area */
function execFormat(command, value) {
    document.getElementById('content-area').focus();
    document.execCommand(command, false, value || null);
}

/** Toggle raw source code view */
function toggleSourceView() {
    const contentArea = document.getElementById('content-area');
    const btn = document.getElementById('tb-source-btn');

    if (!isSourceView) {
        // Switch to source view
        contentArea.setAttribute('data-html', contentArea.innerHTML);
        contentArea.innerHTML = formatHTML(contentArea.innerHTML);
        contentArea.style.fontFamily = 'monospace';
        contentArea.style.fontSize = '13px';
        isSourceView = true;
        btn.classList.add('active');
    } else {
        // Switch back to visual view
        const html = contentArea.getAttribute('data-html') || contentArea.innerHTML;
        contentArea.innerHTML = html;
        contentArea.style.fontFamily = '';
        contentArea.style.fontSize = '';
        isSourceView = false;
        btn.classList.remove('active');
    }
}

/** Pretty-print HTML for source view */
function formatHTML(html) {
    let formatted = '';
    let indent = '';
    const tab = '  ';
    const nodes = html.split(/>\s*</);
    nodes.forEach(function(node, i) {
        if (node.match(/^\/\w/)) {
            indent = indent.substring(tab.length);
        }
        formatted += indent + '<' + node + '>\n';
        if (node.match(/^<?\w[^>]*[^\/]$/) && !node.startsWith('!') && !node.startsWith('br') && !node.startsWith('hr') && !node.startsWith('img') && !node.startsWith('input')) {
            indent += tab;
        }
    });
    return formatted.substring(1, formatted.length - 2);
}

/** Insert table dialog */
function insertTableDialog() {
    const rows = prompt('Number of rows:', '3');
    if (!rows) return;
    const cols = prompt('Number of columns:', '3');
    if (!cols) return;

    let table = '<table style="width:100%;border-collapse:collapse;margin:1rem 0">';
    for (let r = 0; r < parseInt(rows); r++) {
        table += '<tr>';
        for (let c = 0; c < parseInt(cols); c++) {
            if (r === 0) {
                table += '<th style="border:1px solid #d1d5db;padding:0.5rem;background:#f9fafb;font-weight:600">Header</th>';
            } else {
                table += '<td style="border:1px solid #d1d5db;padding:0.5rem">Cell</td>';
            }
        }
        table += '</tr>';
    }
    table += '</table>';

    document.getElementById('content-area').focus();
    document.execCommand('insertHTML', false, table);
}

function toggleInlineEdit() {
    if (isEditing) return;
    isEditing = true;

    const contentArea = document.getElementById('content-area');
    previousContent = contentArea.innerHTML;

    // Make content editable
    contentArea.contentEditable = 'true';
    contentArea.style.cursor = 'text';

    // Show toolbar and save/cancel bar
    document.getElementById('format-toolbar').classList.remove('hidden');
    document.getElementById('edit-bar').classList.remove('hidden');
    document.getElementById('edit-controls').classList.add('hidden');

    // Focus the content area
    contentArea.focus();
}

function cancelInlineEdit() {
    const contentArea = document.getElementById('content-area');

    // Restore original content
    contentArea.innerHTML = previousContent;
    contentArea.contentEditable = 'false';
    contentArea.style.cursor = '';
    if (isSourceView) {
        isSourceView = false;
        document.getElementById('tb-source-btn').classList.remove('active');
    }

    isEditing = false;
    document.getElementById('format-toolbar').classList.add('hidden');
    document.getElementById('edit-bar').classList.add('hidden');
    document.getElementById('edit-controls').classList.remove('hidden');
    document.getElementById('save-status').textContent = '';
}

function saveInlineEdit() {
    const contentArea = document.getElementById('content-area');
    const saveBtn = document.getElementById('save-content-btn');
    const statusEl = document.getElementById('save-status');

    // If in source view, get the raw HTML
    const updatedContent = isSourceView
        ? contentArea.innerHTML.replace(/\n/g, '').replace(/\s+/g, ' ')
        : contentArea.innerHTML;

    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    statusEl.textContent = 'Saving changes...';
    statusEl.className = 'text-sm text-gray-500';

    fetch(proposalUrl, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content: updatedContent })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            contentArea.contentEditable = 'false';
            contentArea.style.cursor = '';

            isEditing = false;
            location.reload();
        } else {
            statusEl.textContent = data.message || 'Failed to save changes.';
            statusEl.className = 'text-sm text-red-600';
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Changes';
        }
    })
    .catch(function(error) {
        console.error('Error saving:', error);
        statusEl.textContent = 'Error saving changes. Please try again.';
        statusEl.className = 'text-sm text-red-600';
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Changes';
    });
}

/** Keyboard shortcuts (Ctrl+B, Ctrl+I, etc.) */
document.addEventListener('keydown', function(e) {
    if (!isEditing) return;
    if (e.ctrlKey || e.metaKey) {
        switch(e.key.toLowerCase()) {
            case 'b': e.preventDefault(); execFormat('bold'); break;
            case 'i': e.preventDefault(); execFormat('italic'); break;
            case 'u': e.preventDefault(); execFormat('underline'); break;
            case 'z': if (e.shiftKey) { e.preventDefault(); execFormat('redo'); } break;
        }
    }
});
</script>
@endsection
