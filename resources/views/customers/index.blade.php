@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Customers
                </h1>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('customers.create') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Customer
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Customers Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            @if($customers->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($customers as $customer)
                        <li>
                            <div class="px-4 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-{{ $customer->status === 'active' ? 'green' : 'gray' }}-100 flex items-center justify-center">
                                            <span class="text-{{ $customer->status === 'active' ? 'green' : 'gray' }}-600 font-medium text-sm">
                                                {{ substr($customer->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <p class="text-lg font-medium text-indigo-600 truncate">
                                                <a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a>
                                            </p>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $customer->status === 'active' ? 'green' : 'gray' }}-100 text-{{ $customer->status === 'active' ? 'green' : 'gray' }}-800">
                                                {{ ucfirst($customer->status) }}
                                            </span>
                                        </div>
                                        <div class="mt-1">
                                            @if($customer->contact_person)
                                                <p class="text-sm text-gray-600">{{ $customer->contact_person }}</p>
                                            @endif
                                            @if($customer->email)
                                                <p class="text-sm text-gray-500">{{ $customer->email }}</p>
                                            @endif
                                            @if($customer->phone)
                                                <p class="text-sm text-gray-500">{{ $customer->phone }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">{{ $customer->total_projects }} Projects</p>
                                        <p class="text-sm text-gray-500">{{ $customer->active_projects }} Active</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Edit
                                        </a>
                                        @if($customer->projects()->count() === 0)
                                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this customer?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $customers->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.196M17 20v-2a3 3 0 00-5.196-2.196M17 20H7m10 0v-2M7 20H2v-2a3 3 0 015.196-2.196M7 20v2M7 18a3 3 0 005.196 2.196M13 6a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No customers</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new customer.</p>
                    <div class="mt-6">
                        <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add Customer
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
