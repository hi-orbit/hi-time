@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-{{ $customer->status === 'active' ? 'green' : 'gray' }}-100 flex items-center justify-center">
                            <span class="text-{{ $customer->status === 'active' ? 'green' : 'gray' }}-600 font-medium text-lg">
                                {{ substr($customer->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            {{ $customer->name }}
                        </h1>
                        <div class="mt-1 flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $customer->status === 'active' ? 'green' : 'gray' }}-100 text-{{ $customer->status === 'active' ? 'green' : 'gray' }}-800">
                                {{ ucfirst($customer->status) }}
                            </span>
                            @if($customer->contact_person)
                                <span class="ml-2 text-sm text-gray-500">{{ $customer->contact_person }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Edit Customer
                </a>
                <a href="{{ route('projects.create') }}?customer_id={{ $customer->id }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    New Project
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Customer Details -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Details</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            @if($customer->contact_person)
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->contact_person }}</dd>
                                </div>
                            @endif
                            @if($customer->email)
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <a href="mailto:{{ $customer->email }}" class="text-indigo-600 hover:text-indigo-500">{{ $customer->email }}</a>
                                    </dd>
                                </div>
                            @endif
                            @if($customer->phone)
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <a href="tel:{{ $customer->phone }}" class="text-indigo-600 hover:text-indigo-500">{{ $customer->phone }}</a>
                                    </dd>
                                </div>
                            @endif
                            @if($customer->address)
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->address }}</dd>
                                </div>
                            @endif
                            @if($customer->notes)
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->notes }}</dd>
                                </div>
                            @endif
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->created_at->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Projects -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Projects ({{ $customer->projects->count() }})
                            </h3>
                            <a href="{{ route('projects.create') }}?customer_id={{ $customer->id }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Project
                            </a>
                        </div>
                    </div>
                    @if($customer->projects->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($customer->projects as $project)
                                <li class="px-4 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full bg-{{ $project->status === 'active' ? 'green' : ($project->status === 'completed' ? 'blue' : 'gray') }}-100 flex items-center justify-center">
                                                    <span class="text-{{ $project->status === 'active' ? 'green' : ($project->status === 'completed' ? 'blue' : 'gray') }}-600 font-medium text-xs">
                                                        {{ substr($project->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="flex items-center">
                                                    <p class="text-sm font-medium text-indigo-600 truncate">
                                                        <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                                                    </p>
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $project->status === 'active' ? 'green' : ($project->status === 'completed' ? 'blue' : 'gray') }}-100 text-{{ $project->status === 'active' ? 'green' : ($project->status === 'completed' ? 'blue' : 'gray') }}-800">
                                                        {{ ucfirst($project->status) }}
                                                    </span>
                                                </div>
                                                @if($project->description)
                                                    <p class="mt-1 text-sm text-gray-500">{{ Str::limit($project->description, 100) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right text-sm text-gray-500">
                                            @if($project->due_date)
                                                <p>Due: {{ $project->due_date->format('M d, Y') }}</p>
                                            @endif
                                            <p>{{ $project->tasks()->count() }} tasks</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No projects yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new project for this customer.</p>
                            <div class="mt-6">
                                <a href="{{ route('projects.create') }}?customer_id={{ $customer->id }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Create Project
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
