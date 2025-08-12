@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center">
                    <a href="{{ route('settings.users.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
                        <p class="text-gray-600 mt-2">Reset password for {{ $user->name }} ({{ $user->email }}).</p>
                    </div>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                        <div class="h-12 w-12 rounded-full bg-{{ $user->role === 'admin' ? 'purple' : ($user->role === 'contractor' ? 'green' : 'blue') }}-100 flex items-center justify-center">
                            <span class="text-{{ $user->role === 'admin' ? 'purple' : ($user->role === 'contractor' ? 'green' : 'blue') }}-600 font-medium text-lg">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-lg font-medium text-gray-900">{{ $user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' :
                               ($user->role === 'contractor' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Warning Notice -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Password Reset Warning</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>This will immediately change the user's password. The user should be notified of their new password through a secure channel.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('settings.users.reset-password.store', $user) }}" method="POST" class="space-y-6">
                @csrf

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Password must be at least 8 characters long.</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('settings.users.edit', $user) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            onclick="return confirm('Are you sure you want to reset this user\'s password?')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
