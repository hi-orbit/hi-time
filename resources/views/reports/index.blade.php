@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Reports Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Generate time tracking and productivity reports
                </p>
            </div>
        </div>

        <!-- Report Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- My Time Today -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    My Time Today
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ now()->format('F j, Y') }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('reports.my-time-today') }}" class="w-full bg-indigo-600 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            View Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Time by Customer - This Month -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Customer Time - This Month
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ now()->format('F Y') }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('reports.time-by-customer-this-month') }}" class="w-full bg-blue-600 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            View Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Time by Customer - Last Month -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Customer Time - Last Month
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ now()->subMonth()->format('F Y') }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('reports.time-by-customer-last-month') }}" class="w-full bg-green-600 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            View Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Time by User -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Time by User
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    Custom Date Range
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-2">
                        <a href="{{ route('reports.time-by-user') }}" class="w-full bg-purple-600 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Standard View
                        </a>
                        <a href="{{ route('reports.time-by-user-enhanced') }}" class="w-full bg-purple-100 border border-purple-300 rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-purple-700 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Enhanced (Editable)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Stats</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ \App\Models\Customer::count() }}</div>
                            <div class="text-sm text-gray-500">Total Customers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ \App\Models\Project::active()->count() }}</div>
                            <div class="text-sm text-gray-500">Active Projects</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ \App\Models\User::count() }}</div>
                            <div class="text-sm text-gray-500">Total Users</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-indigo-600">{{ \App\Models\TaskNote::whereNotNull('total_minutes')->where('total_minutes', '>', 0)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count() }}</div>
                            <div class="text-sm text-gray-500">Time Entries This Month</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
