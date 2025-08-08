<div class="py-12" x-data="{
    showMessage: false,
    message: '',
    messageType: 'success'
}"
x-on:success.window="showMessage = true; message = $event.detail; messageType = 'success'; setTimeout(() => showMessage = false, 3000)"
x-on:error.window="showMessage = true; message = $event.detail; messageType = 'error'; setTimeout(() => showMessage = false, 5000)">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
