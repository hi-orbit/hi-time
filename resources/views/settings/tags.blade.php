@extends('layouts.app')

@section('title', 'Tag Management')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        @livewire('settings.tag-management')
    </div>
</div>
@endsection
