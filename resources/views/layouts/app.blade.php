<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Styles Stack -->
    @stack('styles')

    <!-- Simple Browser Notifications -->
    <script>
        // Wrap in IIFE to avoid conflicts with browser extensions
        (function() {
            'use strict';

            // Request notification permission on page load
            document.addEventListener('DOMContentLoaded', function() {
                console.log('=== Hi-Time Notification System Loading ===');

                // Check notification support and permission
                if ('Notification' in window) {
                    console.log('Current notification permission:', Notification.permission);

                    // Show setup banner if permission not granted
                    if (Notification.permission === 'default') {
                        showNotificationSetupBanner();
                    }
                } else {
                    console.log('Browser notifications not supported');
                }

                console.log('=== Notification System Ready ===');
            });

            // Show banner to request notification permission
            function showNotificationSetupBanner() {
                const banner = document.createElement('div');
                banner.id = 'notification-setup-banner';
                banner.className = 'fixed top-0 left-0 right-0 bg-blue-600 text-white p-3 z-50 text-center';
                banner.innerHTML = `
                    <div class="flex items-center justify-between max-w-6xl mx-auto">
                        <div class="flex items-center">
                            <span class="mr-3">🔔</span>
                            <span>Enable notifications to receive task updates instantly!</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button id="enable-notifications" class="bg-white text-blue-600 px-4 py-1 rounded text-sm font-medium hover:bg-gray-100">
                                Enable Notifications
                            </button>
                            <button id="dismiss-banner" class="text-blue-200 hover:text-white">
                                ×
                            </button>
                        </div>
                    </div>
                `;

                document.body.insertBefore(banner, document.body.firstChild);

                // Add event listeners
                document.getElementById('enable-notifications').addEventListener('click', function() {
                    requestNotificationPermission();
                });

                document.getElementById('dismiss-banner').addEventListener('click', function() {
                    banner.remove();
                });

                // Adjust page content to account for banner
                document.body.style.paddingTop = '60px';
            }

            // Request notification permission (must be user-triggered)
            function requestNotificationPermission() {
                if ('Notification' in window) {
                    Notification.requestPermission().then(function(permission) {
                        console.log('Notification permission result:', permission);

                        const banner = document.getElementById('notification-setup-banner');
                        if (banner) {
                            if (permission === 'granted') {
                                banner.innerHTML = `
                                    <div class="text-center">
                                        <span class="mr-2">✅</span>
                                        <span>Notifications enabled! You'll now receive task updates.</span>
                                    </div>
                                `;
                                setTimeout(() => {
                                    banner.remove();
                                    document.body.style.paddingTop = '0';
                                }, 3000);
                            } else {
                                banner.innerHTML = `
                                    <div class="text-center bg-yellow-600">
                                        <span class="mr-2">⚠️</span>
                                        <span>Notifications blocked. You can enable them in your browser settings.</span>
                                    </div>
                                `;
                                setTimeout(() => {
                                    banner.remove();
                                    document.body.style.paddingTop = '0';
                                }, 5000);
                            }
                        }
                    });
                }
            }

            // Function to show browser notification
            function showNotification(title, message, url = null) {
                console.log('showNotification called:', title, message, url);
                const isChrome = navigator.userAgent.includes('Chrome');
                console.log('Browser:', isChrome ? 'Chrome' : 'Safari/Other');

                // Always show in-page notification for Chrome (more reliable)
                if (isChrome) {
                    console.log('Chrome detected: using reliable in-page notifications');
                    showInPageNotification(title, message, url);
                    return;
                }

                if ('Notification' in window) {
                    console.log('Notification permission:', Notification.permission);

                    if (Notification.permission === 'granted') {
                        console.log('Creating browser notification...');
                        try {
                            const notification = new Notification(title, {
                                body: message,
                                icon: '/favicon.ico',
                                tag: 'hi-time-notification',
                                requireInteraction: false, // Chrome-specific: don't require user interaction to dismiss
                                silent: false
                            });

                            // Handle notification click
                            notification.onclick = function() {
                                console.log('Notification clicked');
                                window.focus();
                                if (url) {
                                    window.location.href = url;
                                }
                                notification.close();
                            };

                            // Handle notification errors
                            notification.onerror = function(error) {
                                console.error('Notification error:', error);
                                showInPageNotification(title, message, url);
                            };

                            // Auto close after 8 seconds (longer for Chrome)
                            setTimeout(() => {
                                notification.close();
                            }, 8000);

                            console.log('Browser notification created successfully');
                        } catch (error) {
                            console.error('Error creating browser notification:', error);
                            showInPageNotification(title, message, url);
                        }
                    } else {
                        console.log('Browser notifications not permitted, using in-page fallback');
                        console.log('Try clicking the notification setup banner or check browser settings');
                        showInPageNotification(title, message, url);
                    }
                } else {
                    console.log('Notifications not supported, using in-page fallback');
                    showInPageNotification(title, message, url);
                }
            }            // Fallback in-page notification
            function showInPageNotification(title, message, url = null) {
            console.log('Showing in-page notification fallback');

            // Create notification element
            const notificationEl = document.createElement('div');
            notificationEl.className = 'fixed top-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50 max-w-sm';
            notificationEl.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-semibold">${title}</h4>
                        <p class="text-sm mt-1">${message}</p>
                    </div>
                    <button class="ml-2 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                        ×
                    </button>
                </div>
            `;

            // Add click handler for URL
            if (url) {
                notificationEl.style.cursor = 'pointer';
                notificationEl.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'BUTTON') {
                        window.location.href = url;
                    }
                });
            }

            // Add to page
            document.body.appendChild(notificationEl);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notificationEl.parentNode) {
                    notificationEl.remove();
                }
            }, 5000);
        }

            // Check for pending notifications
            @auth
            function checkPendingNotifications() {
                console.log('=== CHECKING FOR NOTIFICATIONS ===');
                console.log('Current timestamp:', new Date().toISOString());

                fetch('/api/notifications/pending', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
            .then(response => {
                console.log('Notification API response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Notification API data:', data);
                if (data.notifications && data.notifications.length > 0) {
                    console.log('Found', data.notifications.length, 'notifications');
                    data.notifications.forEach(notification => {
                        console.log('Showing notification:', notification.title);
                        showNotification(notification.title, notification.message, notification.url);
                    });

                    // Clear notifications after showing
                    fetch('/api/notifications/clear', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }).then(() => {
                        console.log('Notifications cleared');
                    });
                } else {
                    console.log('No notifications found');
                }
            })
            .catch(error => {
                console.log('Notification check failed:', error);
            });
            }

            // Check for notifications every 10 seconds
            console.log('Setting up notification polling...');
            setInterval(checkPendingNotifications, 10000);

            // Check immediately on page load
            console.log('Starting immediate notification check...');
            setTimeout(checkPendingNotifications, 1000);

            // Make functions available globally for testing
            window.testNotifications = checkPendingNotifications;
            window.testNotificationShow = function() {
                showNotification('Test Title', 'This is a test notification', '/dashboard');
            };
            console.log('Test functions available: testNotifications() and testNotificationShow()');
            @endauth

        })(); // End IIFE
    </script>

    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @auth
            <!-- Navigation -->
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                                    Hi-Time
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Dashboard
                                </a>
                                <a href="{{ route('projects.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('projects.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Projects
                                </a>
                                <a href="{{ route('customers.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('customers.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Customers
                                </a>
                                <a href="{{ route('proposals.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('proposals.*') || request()->routeIs('leads.*') || request()->routeIs('proposal-templates.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Proposals
                                </a>
                                <a href="{{ route('time-tracking.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('time-tracking.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Time Tracking
                                </a>
                                <a href="{{ route('reports.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('reports.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Reports
                                </a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('settings.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('settings.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Settings
                                </a>
                                @endif
                            </div>
                        </div>

                        <!-- User menu -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative">
                                <div class="flex items-center space-x-4">
                                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                                    <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded">{{ ucfirst(auth()->user()->role) }}</span>
                                    <form method="POST" action="{{ route('logout') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-gray-500 hover:text-gray-700">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        @endauth

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    @livewireScripts

    <!-- Custom Scripts Stack -->
    @stack('scripts')
</body>
</html>
