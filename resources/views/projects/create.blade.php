@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Create New Project</h2>

                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Project Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Customer -->
                    <div class="mb-4">
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">
                            Customer
                        </label>
                        <select
                            name="customer_id"
                            id="customer_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('customer_id') border-red-500 @enderror"
                        >
                            <option value="">Select a customer (optional)</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', request('customer_id')) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assigned Customer Users -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Assigned Customer Users
                        </label>
                        <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-300 rounded-md p-3">
                            @foreach($customerUsers as $customerUser)
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        name="assigned_users[]"
                                        value="{{ $customerUser->id }}"
                                        {{ in_array($customerUser->id, old('assigned_users', [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">{{ $customerUser->name }} ({{ $customerUser->email }})</span>
                                </label>
                            @endforeach
                        </div>
                        @if($customerUsers->count() === 0)
                            <p class="mt-1 text-sm text-gray-500">
                                No customer users available. Customer users can only view projects they're assigned to.
                            </p>
                        @endif
                        @error('assigned_users')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                            placeholder="Project description..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Status
                        </label>
                        <select
                            name="status"
                            id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-500 @enderror"
                        >
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div class="mb-6">
                        <label for="due_date" class="block text-sm font-medium text-gray-700">
                            Due Date
                        </label>
                        <input
                            type="date"
                            name="due_date"
                            id="due_date"
                            value="{{ old('due_date') }}"
                            min="{{ date('Y-m-d') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('due_date') border-red-500 @enderror"
                        >
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('projects.index') }}" class="bg-gray-200 py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
