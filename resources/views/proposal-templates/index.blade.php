@extends('layouts.app')

@section('title', 'Proposal Templates')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Proposal Templates</h1>
                    <p class="text-gray-600 mt-2">Create and manage reusable proposal templates with variables.</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('proposals.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Proposals
                    </a>
                    <a href="{{ route('proposal-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Template
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

            <!-- Templates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($templates as $template)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $template->name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $template->type }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($template->is_active)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Description -->
                            @if($template->description)
                                <p class="text-sm text-gray-500 mb-4">{{ Str::limit($template->description, 120) }}</p>
                            @endif

                            <!-- Variables -->
                            @if($template->variables && count($template->variables) > 0)
                                <div class="mb-4">
                                    <p class="text-xs font-medium text-gray-700 mb-2">Variables:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($template->variables, 0, 3) as $variable)
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                @{{ $variable }}
                                            </span>
                                        @endforeach
                                        @if(count($template->variables) > 3)
                                            <span class="inline-flex px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                                +{{ count($template->variables) - 3 }} more
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Usage Stats -->
                            <div class="text-xs text-gray-500 mb-4">
                                Used in {{ $template->proposals()->count() }} proposal{{ $template->proposals()->count() !== 1 ? 's' : '' }}
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex space-x-2">
                                    <a href="{{ route('proposal-templates.show', $template) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        View
                                    </a>
                                    <a href="{{ route('proposal-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                        Edit
                                    </a>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('proposals.create', ['template_id' => $template->id]) }}" class="inline-flex items-center px-3 py-1 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Use Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No templates yet</h3>
                            <p class="text-gray-500 mb-4">Create your first proposal template to get started.</p>
                            <a href="{{ route('proposal-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create Template
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($templates->hasPages())
                <div class="mt-6">
                    {{ $templates->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
