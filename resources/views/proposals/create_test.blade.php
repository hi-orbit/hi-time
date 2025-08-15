@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold mb-6">Simple Proposal Test Form</h1>

        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('proposals.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" id="title" value="Test Proposal"
                           class="w-full border-gray-300 rounded-md shadow-sm" required>
                </div>

                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                    <textarea name="content" id="content" rows="10"
                              class="w-full border-gray-300 rounded-md shadow-sm"
                              required>This is a simple test content.</textarea>
                </div>

                <div class="mb-6">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <input type="number" name="amount" id="amount" value="1000" step="0.01"
                           class="w-full border-gray-300 rounded-md shadow-sm">
                </div>

                <div class="mb-6">
                    <label for="lead_id" class="block text-sm font-medium text-gray-700 mb-2">Lead (Optional)</label>
                    <select name="lead_id" id="lead_id" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Select a lead</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }} - {{ $lead->company }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">Customer (Optional)</label>
                    <select name="customer_id" id="customer_id" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Select a customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">
                    Test Submit
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
