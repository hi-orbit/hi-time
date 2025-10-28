@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $project->name }}</h1>

    @if(!auth()->user()->isCustomer())
        <!-- Total time tracked section -->
        <div class="time-tracked-section duration-display time-tracking-section" data-time-tracking="true">
            <h2>Total Time Tracked</h2>
            <p>{{ $project->total_time_tracked }}</p>
        </div>

        <!-- Recent time entries section -->
        <div class="recent-entries-section time-tracking-section" data-time-tracking="true">
            <h2>Recent Time Entries</h2>
            <ul>
                @foreach($project->recentTimeEntries as $entry)
                    <li>{{ $entry->description }} - {{ $entry->time }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ...existing project details code... -->
</div>
@endsection
