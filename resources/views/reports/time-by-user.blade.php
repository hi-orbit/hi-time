@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                        Time by User
                    </h1>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <div class="bg-purple-100 px-4 py-2 rounded-lg">
                    <span class="text-purple-800 font-medium">Total: {{ number_format($userTimeData['total_hours'], 2) }} hours</span>
                </div>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" action="{{ route('reports.time-by-user') }}" class="flex items-center space-x-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $selectedStartDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $selectedEndDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium">
                            Update Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(count($userTimeData['users']) > 0)
            <div class="space-y-6">
                @foreach($userTimeData['users'] as $userData)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <!-- User Header -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-medium text-sm">
                                            {{ substr($userData['user_name'], 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $userData['user_name'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ count($userData['customers']) }} customer{{ count($userData['customers']) !== 1 ? 's' : '' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900">{{ number_format($userData['total_hours'], 2) }}h</div>
                                    <div class="text-sm text-gray-500">
                                        @if($userTimeData['total_hours'] > 0)
                                            {{ number_format(($userData['total_hours'] / $userTimeData['total_hours']) * 100, 1) }}% of total
                                        @else
                                            0% of total
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customers -->
                        <div class="divide-y divide-gray-200">
                            @foreach($userData['customers'] as $customerData)
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-md font-medium text-gray-900">{{ $customerData['customer_name'] }}</h4>
                                        <span class="text-lg font-semibold text-gray-700">{{ number_format($customerData['hours'], 2) }}h</span>
                                    </div>

                                    <!-- Projects -->
                                    <div class="ml-4 space-y-3">
                                        @foreach($customerData['projects'] as $projectData)
                                            <div class="border-l-2 border-gray-200 pl-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h5 class="text-sm font-medium text-gray-700">{{ $projectData['project_name'] }}</h5>
                                                    <span class="text-sm font-semibold text-gray-600">{{ number_format($projectData['hours'], 2) }}h</span>
                                                </div>

                                                <!-- Time Entries -->
                                                <div class="space-y-1">
                                                    @foreach($projectData['entries'] as $entry)
                                                        <div class="flex items-center justify-between text-sm bg-gray-50 rounded px-3 py-2">
                                                            <div class="flex-1">
                                                                <span class="font-medium text-gray-900">{{ $entry->task_title }}</span>
                                                                @if($entry->description)
                                                                    <span class="text-gray-600 ml-2">- {{ $entry->description }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center space-x-3">
                                                                <span class="font-medium text-gray-900">{{ number_format($entry->hours + ($entry->minutes / 60), 2) }}h</span>
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
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No time entries found</h3>
                <p class="mt-1 text-sm text-gray-500">No time has been tracked by any users in this period.</p>
            </div>
        @endif
    </div>
</div>
@endsection
