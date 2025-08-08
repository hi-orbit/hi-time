@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <div class="flex items-center">
                    <a href="{{ route('leads.index') }}" class="text-gray-500 hover:text-gray-700 mr-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        {{ $lead->company ?: $lead->name }}
                    </h1>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Lead Details
                </p>
            </div>
            <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
                @if(!$lead->convertedCustomer)
                    <form action="{{ route('leads.convert', $lead) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            Convert to Customer
                        </button>
                    </form>
                @endif
                <a href="{{ route('leads.edit', $lead) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    Edit Lead
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Lead Information -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Lead Information</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Company Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $lead->company ?: 'Not provided' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $lead->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Company Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $lead->company_number ?: 'Not provided' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    <a href="mailto:{{ $lead->email }}" class="text-blue-600 hover:text-blue-500">
                                        {{ $lead->email }}
                                    </a>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($lead->phone)
                                        <a href="tel:{{ $lead->phone }}" class="text-blue-600 hover:text-blue-500">
                                            {{ $lead->phone }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">Not provided</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <p class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                        @switch($lead->status)
                                            @case('new')
                                                bg-blue-100 text-blue-800
                                                @break
                                            @case('contacted')
                                                bg-yellow-100 text-yellow-800
                                                @break
                                            @case('qualified')
                                                bg-purple-100 text-purple-800
                                                @break
                                            @case('proposal_sent')
                                                bg-indigo-100 text-indigo-800
                                                @break
                                            @case('closed_won')
                                                bg-green-100 text-green-800
                                                @break
                                            @case('closed_lost')
                                                bg-red-100 text-red-800
                                                @break
                                            @default
                                                bg-gray-100 text-gray-800
                                        @endswitch
                                    ">
                                        {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Source</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    @if($lead->source)
                                        {{ ucfirst(str_replace('_', ' ', $lead->source)) }}
                                    @else
                                        <span class="text-gray-400">Not specified</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($lead->address)
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $lead->address }}</p>
                            </div>
                        @endif

                        @if($lead->notes)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <div class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                                    {{ $lead->notes }}
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $lead->created_at->format('M j, Y \a\t g:i A') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $lead->updated_at->format('M j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proposals -->
                @if($lead->proposals && $lead->proposals->count() > 0)
                    <div class="bg-white shadow rounded-lg mt-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Proposals</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($lead->proposals as $proposal)
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ $proposal->title }}</h4>
                                            <p class="text-sm text-gray-500">Created {{ $proposal->created_at->format('M j, Y') }}</p>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                                @switch($proposal->status)
                                                    @case('draft')
                                                        bg-gray-100 text-gray-800
                                                        @break
                                                    @case('sent')
                                                        bg-blue-100 text-blue-800
                                                        @break
                                                    @case('accepted')
                                                        bg-green-100 text-green-800
                                                        @break
                                                    @case('rejected')
                                                        bg-red-100 text-red-800
                                                        @break
                                                    @default
                                                        bg-gray-100 text-gray-800
                                                @endswitch
                                            ">
                                                {{ ucfirst($proposal->status) }}
                                            </span>
                                            <a href="{{ route('proposals.show', $proposal) }}"
                                               class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <a href="{{ route('proposals.create', ['lead_id' => $lead->id]) }}"
                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-medium py-2 px-4 rounded-lg transition duration-200">
                            Create Proposal
                        </a>
                        @if(!$lead->convertedCustomer)
                            <form action="{{ route('leads.convert', $lead) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="block w-full bg-green-600 hover:bg-green-700 text-white text-center font-medium py-2 px-4 rounded-lg transition duration-200"
                                        onclick="return confirm('Are you sure you want to convert this lead to a customer?')">
                                    Convert to Customer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Conversion Status -->
                @if($lead->convertedCustomer)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Conversion Status</h3>
                        </div>
                        <div class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium text-green-800">Converted to Customer</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">
                                This lead has been converted to customer:
                                <a href="{{ route('customers.show', $lead->convertedCustomer) }}" class="text-blue-600 hover:text-blue-500 font-medium">
                                    {{ $lead->convertedCustomer->name }}
                                </a>
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Converted on {{ $lead->converted_at->format('M j, Y') }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
