@extends('layouts.app')

@section('title', 'Screen Sharing Settings')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="mb-6">
                <div class="flex items-center mb-4">
                    <a href="{{ route('settings.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Screen Sharing Settings</h1>
                        <p class="text-gray-600 mt-2">Configure privacy settings for screen sharing during client calls.</p>
                    </div>
                </div>
            </div>

            <div class="max-w-2xl">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Privacy Mode</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>When enabled, all time tracking elements will be hidden to protect sensitive information during screen sharing with clients.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center justify-between p-6 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Hide Time Tracking Elements</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Hide all time inputs, controls, and time-related navigation when screen sharing
                            </p>
                            <div class="mt-3 text-xs text-gray-500">
                                <p><strong>Hidden elements include:</strong></p>
                                <ul class="list-disc list-inside mt-1 space-y-1">
                                    <li>Time Tracking navigation menu</li>
                                    <li>Reports navigation menu</li>
                                    <li>Time input fields and controls</li>
                                    <li>Duration displays</li>
                                    <li>Time entry forms</li>
                                </ul>
                            </div>
                        </div>
                        <div class="ml-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="hideTimeTracking" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Enable</span>
                            </label>
                        </div>
                    </div>

                    <div id="statusMessage" class="hidden p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg id="statusIcon" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <!-- Icon will be set by JavaScript -->
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p id="statusText" class="text-sm font-medium"></p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Quick Actions</h4>
                                <p class="text-xs text-gray-500 mt-1">Quickly toggle the setting</p>
                            </div>
                            <div class="space-x-3">
                                <button id="quickEnable" class="px-3 py-2 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors duration-200">
                                    Quick Enable
                                </button>
                                <button id="quickDisable" class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors duration-200">
                                    Quick Disable
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('hideTimeTracking');
    const statusMessage = document.getElementById('statusMessage');
    const statusIcon = document.getElementById('statusIcon');
    const statusText = document.getElementById('statusText');
    const quickEnable = document.getElementById('quickEnable');
    const quickDisable = document.getElementById('quickDisable');

    // Load current setting from localStorage
    const currentSetting = localStorage.getItem('hideTimeTracking') === 'true';
    checkbox.checked = currentSetting;
    updateStatus(currentSetting);

    // Update status display
    function updateStatus(isEnabled) {
        statusMessage.className = isEnabled
            ? 'p-4 rounded-lg bg-green-50 border border-green-200'
            : 'p-4 rounded-lg bg-gray-50 border border-gray-200';

        statusIcon.className = isEnabled
            ? 'h-5 w-5 text-green-400'
            : 'h-5 w-5 text-gray-400';

        statusIcon.innerHTML = isEnabled
            ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>'
            : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';

        statusText.className = isEnabled
            ? 'text-sm font-medium text-green-800'
            : 'text-sm font-medium text-gray-800';

        statusText.textContent = isEnabled
            ? 'Privacy mode is active - Time tracking elements are hidden'
            : 'Privacy mode is disabled - All elements are visible';

        statusMessage.classList.remove('hidden');
    }

    // Apply or remove time hiding
    function applyTimeHiding(hide) {
        if (hide) {
            document.body.classList.add('hide-time-tracking');
        } else {
            document.body.classList.remove('hide-time-tracking');
        }
    }

    // Save setting and apply immediately
    function saveSetting(value) {
        localStorage.setItem('hideTimeTracking', value);
        applyTimeHiding(value);
        updateStatus(value);

        // Trigger a custom event for other parts of the app to listen to
        window.dispatchEvent(new CustomEvent('timeTrackingVisibilityChanged', {
            detail: { hidden: value }
        }));
    }

    // Checkbox change event
    checkbox.addEventListener('change', function() {
        saveSetting(this.checked);
    });

    // Quick action buttons
    quickEnable.addEventListener('click', function() {
        checkbox.checked = true;
        saveSetting(true);
    });

    quickDisable.addEventListener('click', function() {
        checkbox.checked = false;
        saveSetting(false);
    });

    // Apply the current setting immediately
    applyTimeHiding(currentSetting);
});
</script>
@endsection
