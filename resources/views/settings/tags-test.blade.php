@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Tag Management Test</h1>

    <div class="bg-white shadow rounded-lg p-4">
        <h2 class="text-lg font-semibold mb-4">Tags in Database:</h2>
        @if($tags->count() > 0)
            <ul class="space-y-2">
                @foreach($tags as $tag)
                    <li class="flex items-center space-x-2">
                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $tag->color }}"></div>
                        <span>{{ $tag->name }}</span>
                        <span class="text-sm text-gray-500">({{ $tag->tasks_count }} tasks)</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">No tags found in database.</p>
        @endif
    </div>

    <div class="mt-6">
        <a href="{{ route('settings.tags') }}" class="text-indigo-600 hover:text-indigo-800">
            Back to Livewire Tag Management
        </a>
    </div>
</div>
@endsection
