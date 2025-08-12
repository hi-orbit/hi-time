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
                                    ${{ number_format($proposal->amount, 2) }}
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
                    <div class="prose max-w-none">
                        {!! $proposal->content !!}
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
