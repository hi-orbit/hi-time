<div class="py-12" x-data="{
    showMessage: false,
    message: '',
    messageType: 'success'
}"
x-on:success.window="showMessage = true; message = $event.detail; messageType = 'success'; setTimeout(() => showMessage = false, 3000)"
x-on:error.window="showMessage = true; message = $event.detail; messageType = 'error'; setTimeout(() => showMessage = false, 5000)">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Customer Reports
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">My Time Today</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Date Picker -->
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date:</label>
                            <input type="date"
                                   wire:model.live="selectedDate"
                                   id="date"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div x-show="showMessage" x-transition class="mb-4">
                    <div x-bind:class="messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'"
                         class="px-4 py-3 rounded">
                        <span x-text="message"></span>
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Summary -->
                <div class="bg-indigo-50 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-indigo-900">Total Time</h3>
                            <p class="text-sm text-indigo-600">{{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-indigo-900">
                                {{ $totalHours }}h {{ $totalMinutes }}m
                            </div>
                            <p class="text-sm text-indigo-600">{{ count($timeEntries) }} {{ Str::plural('entry', count($timeEntries)) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Daily Hours Chart -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Hours Logged on {{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}</h3>

                    @php
                        $totalMinutes = collect($chartData)->sum('duration_minutes');
                        $totalHours = floor($totalMinutes / 60);
                        $totalMins = $totalMinutes % 60;
                    @endphp

                    <div class="bg-gray-50 rounded-lg p-6">
                        <!-- Timeline Header -->
                        <div class="mb-4">
                            <p class="text-lg font-semibold text-gray-700">
                                Total: {{ $totalHours }}h {{ $totalMins }}m
                            </p>
                            <p class="text-sm text-gray-500">Hover over a task to see details</p>
                            <p class="text-xs text-gray-400 mt-1">Manual entries are shown with dashed borders and estimated times</p>
                        </div>

                        <!-- Timeline Chart -->
                        @php
                            $maxLayer = count($chartData) > 0 ? max(array_column($chartData, 'layer')) : 0;
                            $rowHeight = 50; // Height of each timeline row
                            $chartHeight = max(100, ($maxLayer + 1) * $rowHeight + 50); // Proper padding for bottom entries
                        @endphp

                        <div class="relative" style="height: {{ $chartHeight }}px;">
                            <!-- Hour markers -->
                            <div class="absolute inset-0 flex text-xs text-gray-400 mb-2">
                                @for($hour = 0; $hour < 24; $hour++)
                                    <div class="flex flex-col items-start" style="width: {{ 100/24 }}%; position: relative;">
                                        <span class="text-xs font-medium">{{ sprintf('%02d', $hour) }}</span>
                                        <div class="absolute left-0 top-4 w-px bg-gray-300" style="height: {{ $chartHeight - 20 }}px;"></div>
                                        <!-- 30-minute marker -->
                                        <div class="absolute left-1/2 top-4 w-px bg-gray-200" style="height: {{ $chartHeight - 30 }}px;"></div>
                                        <!-- Time labels for clarity -->
                                        <div class="absolute left-1/2 top-0 text-xs text-gray-300 transform -translate-x-1/2">30</div>
                                    </div>
                                @endfor
                            </div>

                            <!-- Timeline bars -->
                            <div class="absolute inset-0 mt-8">
                                @if(count($chartData) > 0)
                                    @foreach($chartData as $entry)
                                        @php
                                            // Calculate exact decimal hours for positioning
                                            $startHour = $entry['start_time']->hour + ($entry['start_time']->minute / 60);
                                            $endHour = $entry['end_time']->hour + ($entry['end_time']->minute / 60);

                                            // Calculate position and width as percentage of 24-hour day
                                            $left = ($startHour / 24) * 100;
                                            $width = (($endHour - $startHour) / 24) * 100;

                                            // Calculate vertical position based on layer
                                            $topPosition = 15 + ($entry['layer'] * $rowHeight);

                                            $durationHours = floor($entry['duration_minutes'] / 60);
                                            $durationMins = $entry['duration_minutes'] % 60;
                                        @endphp
                                        <div class="absolute group"
                                             style="left: {{ number_format($left, 2) }}%; width: {{ number_format($width, 2) }}%; top: {{ $topPosition }}px; height: 40px;">
                                            <div class="w-full h-full rounded shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer border-2 border-white {{ isset($entry['is_manual']) && $entry['is_manual'] ? 'border-dashed' : '' }}"
                                                 style="background-color: {{ $entry['color'] }}; {{ isset($entry['is_manual']) && $entry['is_manual'] ? 'opacity: 0.8;' : '' }}">

                                                <!-- Tooltip -->
                                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-10">
                                                    <div class="bg-gray-800 text-white text-xs rounded-lg py-2 px-3 whitespace-nowrap shadow-lg">
                                                        <div class="font-semibold">{{ $entry['task'] }}</div>
                                                        <div class="text-gray-300">{{ $entry['project'] }}</div>
                                                        <div class="mt-1">
                                                            {{ $entry['start_time']->format('H:i') }} - {{ $entry['end_time']->format('H:i') }}
                                                            @if(isset($entry['is_manual']) && $entry['is_manual'])
                                                                <span class="text-yellow-300 ml-1">(Manual Entry)</span>
                                                            @endif
                                                        </div>
                                                        <div>Duration: {{ $durationHours }}h {{ $durationMins }}m</div>
                                                        @if(!empty($entry['description']))
                                                            <div class="text-gray-300 mt-1">{{ $entry['description'] }}</div>
                                                        @endif
                                                        <!-- Arrow -->
                                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex items-center justify-center h-full">
                                        <p class="text-gray-500 text-sm">No time logged for this date</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Legend -->
                        @if(count($chartData) > 0)
                            <div class="mt-1 pt-2 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Tasks:</h4>
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        $uniqueTasks = collect($chartData)->unique('task');
                                    @endphp
                                    @foreach($uniqueTasks as $entry)
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-sm mr-2" style="background-color: {{ $entry['color'] }};"></div>
                                            <span class="text-sm text-gray-600">{{ $entry['task'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Time Entries -->
                @if(count($timeEntries) > 0)
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Time Entries</h3>

                        @foreach($timeEntries as $entry)
                            @livewire('components.time-entry-editor', ['timeEntry' => $entry], key($entry->id))
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No time entries</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            You haven't logged any time for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('time-tracking.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Log Time
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
