@extends('layouts.app')

@section('title', 'Proposal Template Details')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-{{ $proposalTemplate->is_active ? 'green' : 'gray' }}-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-{{ $proposalTemplate->is_active ? 'green' : 'gray' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $proposalTemplate->name }}</h1>
                        <div class="mt-1 flex items-center space-x-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $proposalTemplate->is_active ? 'green' : 'gray' }}-100 text-{{ $proposalTemplate->is_active ? 'green' : 'gray' }}-800">
                                {{ $proposalTemplate->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-sm text-gray-500">{{ $proposalTemplate->type }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('proposal-templates.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Templates
                    </a>
                    <a href="{{ route('proposal-templates.edit', $proposalTemplate) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Template
                    </a>
                    <a href="{{ route('proposals.create', ['template_id' => $proposalTemplate->id]) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Use Template
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Template Details -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Template Details</h2>

                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Template Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $proposalTemplate->name }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $proposalTemplate->type }}</dd>
                            </div>

                            @if($proposalTemplate->description)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $proposalTemplate->description }}</dd>
                            </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $proposalTemplate->is_active ? 'green' : 'gray' }}-100 text-{{ $proposalTemplate->is_active ? 'green' : 'gray' }}-800">
                                        {{ $proposalTemplate->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Usage</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    Used in {{ $proposalTemplate->proposals()->count() }} proposal{{ $proposalTemplate->proposals()->count() !== 1 ? 's' : '' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $proposalTemplate->created_at->format('M j, Y g:i A') }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $proposalTemplate->updated_at->format('M j, Y g:i A') }}</dd>
                            </div>
                        </dl>

                        <!-- Variables Section -->
                        @if($proposalTemplate->variables && count($proposalTemplate->variables) > 0)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Template Variables</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($proposalTemplate->variables as $variable)
                                    <span class="inline-flex px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        @{{ $variable }}
                                    </span>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                These variables can be filled when creating proposals from this template.
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Template Content -->
                <div class="lg:col-span-2">
                    <div class="bg-white border border-gray-200 rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Template Content</h2>
                        </div>
                        <div class="p-6">
                            <div class="bg-gray-50 rounded-lg p-4 font-mono text-sm text-gray-800 whitespace-pre-wrap">{{ $proposalTemplate->content }}</div>
                        </div>
                    </div>

                    <!-- Recent Proposals Using This Template -->
                    @if($proposalTemplate->proposals()->count() > 0)
                    <div class="mt-6 bg-white border border-gray-200 rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Recent Proposals Using This Template</h2>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($proposalTemplate->proposals()->latest()->take(5)->get() as $proposal)
                            <div class="px-6 py-4 flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h4 class="text-sm font-medium text-gray-900">
                                            {{ $proposal->title ?? 'Untitled Proposal' }}
                                        </h4>
                                        @if($proposal->status)
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($proposal->status === 'sent') bg-blue-100 text-blue-800
                                            @elseif($proposal->status === 'accepted') bg-green-100 text-green-800
                                            @elseif($proposal->status === 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($proposal->status) }}
                                        </span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Created {{ $proposal->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <a href="{{ route('proposals.show', $proposal) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                    View →
                                </a>
                            </div>
                            @endforeach
                        </div>
                        @if($proposalTemplate->proposals()->count() > 5)
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
                            <a href="{{ route('proposals.index') }}?template={{ $proposalTemplate->id }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                View all {{ $proposalTemplate->proposals()->count() }} proposals using this template
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions Section -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex space-x-3">
                        @if($proposalTemplate->proposals()->count() === 0)
                            <form method="POST" action="{{ route('proposal-templates.destroy', $proposalTemplate) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete this template? This action cannot be undone.')"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Template
                                </button>
                            </form>
                        @else
                            <div class="text-sm text-gray-500">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Cannot delete template that is being used by proposals.
                            </div>
                        @endif
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('proposal-templates.edit', $proposalTemplate) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit Template
                        </a>
                        <a href="{{ route('proposals.create', ['template_id' => $proposalTemplate->id]) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Use This Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
