@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Edit Project</h2>

                <form method="POST" action="{{ route('projects.update', $project) }}">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Project Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $project->name) }}"
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
                                <option value="{{ $customer->id }}" {{ old('customer_id', $project->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if($customers->count() === 0)
                            <p class="mt-1 text-sm text-gray-500">
                                No customers available. <a href="{{ route('customers.create') }}" class="text-indigo-600 hover:text-indigo-500">Create one first</a>
                            </p>
                        @endif
                    </div>

                    <!-- Assigned Customer Users -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Assigned Customer Users
                        </label>
                        @if($project->assignedUsers && $project->assignedUsers->count() > 0)
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($project->assignedUsers as $assignedUser)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $assignedUser->name }}
                                        <button type="button" onclick="removeCustomerUser({{ $assignedUser->id }})"
                                                class="ml-2 text-blue-600 hover:text-blue-800">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                        <input type="hidden" name="assigned_users[]" value="{{ $assignedUser->id }}">
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if($customerUsers->count() > 0)
                            <select id="customerSelect" onchange="addCustomerUser(this)"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">+ Assign Customer</option>
                                @foreach($customerUsers as $customerUser)
                                    <option value="{{ $customerUser->id }}" data-name="{{ $customerUser->name }}" data-email="{{ $customerUser->email }}">
                                        {{ $customerUser->name }} ({{ $customerUser->email }})
                                    </option>
                                @endforeach
                            </select>
                        @endif

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
                        >{{ old('description', $project->description) }}</textarea>
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
                            <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="archived" {{ old('status', $project->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('projects.show', $project) }}" class="bg-gray-200 py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function addCustomerUser(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (!selectedOption.value) return;

    const userId = selectedOption.value;
    const userName = selectedOption.dataset.name;
    const userEmail = selectedOption.dataset.email;

    // Check if user is already assigned
    const existingInput = document.querySelector(`input[name="assigned_users[]"][value="${userId}"]`);
    if (existingInput) {
        select.value = "";
        return;
    }

    // Create the badge element
    const badge = document.createElement('span');
    badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800';
    badge.innerHTML = `
        ${userName}
        <button type="button" onclick="removeCustomerUser(${userId})" class="ml-2 text-blue-600 hover:text-blue-800">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
        <input type="hidden" name="assigned_users[]" value="${userId}">
    `;

    // Add to the container
    const container = document.querySelector('.flex.flex-wrap.gap-2.mb-3') || createBadgeContainer();
    container.appendChild(badge);

    // Hide the option in the select
    selectedOption.style.display = 'none';
    select.value = "";
}

function removeCustomerUser(userId) {
    // Remove the badge
    const badge = document.querySelector(`input[name="assigned_users[]"][value="${userId}"]`).closest('span');
    badge.remove();

    // Show the option in the select again
    const option = document.querySelector(`#customerSelect option[value="${userId}"]`);
    if (option) {
        option.style.display = 'block';
    }

    // Remove the container if no more badges
    const container = document.querySelector('.flex.flex-wrap.gap-2.mb-3');
    if (container && container.children.length === 0) {
        container.remove();
    }
}

function createBadgeContainer() {
    const container = document.createElement('div');
    container.className = 'flex flex-wrap gap-2 mb-3';

    const select = document.getElementById('customerSelect');
    select.parentNode.insertBefore(container, select);

    return container;
}

// Hide already assigned users from the select on page load
document.addEventListener('DOMContentLoaded', function() {
    const assignedUserIds = Array.from(document.querySelectorAll('input[name="assigned_users[]"]')).map(input => input.value);
    assignedUserIds.forEach(userId => {
        const option = document.querySelector(`#customerSelect option[value="${userId}"]`);
        if (option) {
            option.style.display = 'none';
        }
    });
});
</script>
@endsection
