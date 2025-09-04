@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Reports Navigation -->
        <div class="mb-6">
            <nav class="bg-white shadow rounded-lg">
                <div class="px-6 py-3">
                    <div class="flex space-x-8">
                        <a href="{{ route('reports.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.index') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            Reports Dashboard
                        </a>
                        <a href="{{ route('reports.my-time-today') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.my-time-today') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            My Time Today
                        </a>
                        <a href="{{ route('reports.time-by-user') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.time-by-user*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0"></path>
                            </svg>
                            Time by User
                        </a>
                        <a href="{{ route('reports.time-by-customer-this-month') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.time-by-customer*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Customer Reports
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <div class="flex items-center">
                    <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-gray-700 mr-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Time by Customer - {{ $periodName }}
                    </h1>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <div class="bg-blue-100 px-4 py-2 rounded-lg">
                    <span class="text-blue-800 font-medium">Total: {{ number_format($customerTimeData['total_hours'], 2) }} hours</span>
                </div>
            </div>
        </div>

        @if(count($customerTimeData['customers']) > 0)
            <div class="space-y-6">
                @foreach($customerTimeData['customers'] as $customerData)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <!-- Customer Header -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-medium text-sm">
                                            {{ substr($customerData['customer_name'], 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $customerData['customer_name'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ count($customerData['projects']) }} project{{ count($customerData['projects']) !== 1 ? 's' : '' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900">{{ number_format($customerData['total_hours'], 2) }}h</div>
                                    <div class="text-sm text-gray-500">
                                        @if(isset($customerData['total_task_hours']) && isset($customerData['total_general_hours']))
                                            Task: {{ number_format($customerData['total_task_hours'], 1) }}h |
                                            General: {{ number_format($customerData['total_general_hours'], 1) }}h
                                        @else
                                            @if($customerTimeData['total_hours'] > 0)
                                                {{ number_format(($customerData['total_hours'] / $customerTimeData['total_hours']) * 100, 1) }}% of total
                                            @else
                                                0% of total
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Projects -->
                        <div class="divide-y divide-gray-200">
                            @foreach($customerData['projects'] as $projectData)
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <h4 class="text-md font-medium text-gray-900">{{ $projectData['project_name'] }}</h4>
                                            @if(isset($projectData['total_task_hours']) && isset($projectData['total_general_hours']))
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Task Work: {{ number_format($projectData['total_task_hours'], 1) }}h |
                                                    General Activities: {{ number_format($projectData['total_general_hours'], 1) }}h
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-lg font-semibold text-gray-700">{{ number_format($projectData['total_hours'], 2) }}h</span>
                                    </div>

                                    <!-- Time Entries -->
                                    <div class="space-y-2">
                                        @foreach($projectData['entries'] as $entry)
                                            <div class="flex items-center justify-between text-sm bg-gray-50 rounded px-3 py-2">
                                                <div class="flex-1">
                                                    <div class="flex items-center">
                                                        <span class="font-medium text-gray-900">{{ $entry->activity_description ?? $entry->task_title ?? 'Unknown Activity' }}</span>
                                                        @if(isset($entry->entry_type))
                                                            <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full {{ $entry->entry_type === 'Task Work' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                                {{ $entry->entry_type }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($entry->description)
                                                        <div class="text-gray-600 mt-1">{{ $entry->description }}</div>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-3">
                                                    <span class="text-gray-500">{{ $entry->user_name }}</span>
                                                    <span class="font-medium text-gray-900">
                                                        {{ number_format($entry->calculated_hours ?? (($entry->duration ?? $entry->duration_minutes ?? 0) / 60), 2) }}h
                                                    </span>
                                                    <span class="text-gray-500">{{ \Carbon\Carbon::parse($entry->created_at)->format('M j') }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No time entries found</h3>
                <p class="mt-1 text-sm text-gray-500">No time has been tracked for any customers in this period.</p>
            </div>
        @endif
    </div>
</div>
@endsection
