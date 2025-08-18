<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $proposal->title }} - {{ $proposal->proposal_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .signature-pad {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            cursor: crosshair;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $proposal->title }}</h1>
                    <p class="text-gray-600 mt-1">{{ $proposal->proposal_number }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($proposal->status === 'sent') bg-blue-100 text-blue-800
                        @elseif($proposal->status === 'viewed') bg-yellow-100 text-yellow-800
                        @elseif($proposal->status === 'signed') bg-green-100 text-green-800
                        @elseif($proposal->status === 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($proposal->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-6">
                {{ session('info') }}
            </div>
        @endif

        <!-- Proposal Details -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Proposal Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($proposal->amount)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                    <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($proposal->amount, 2) }}</dd>
                </div>
                @endif

                @if($proposal->valid_until)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $proposal->valid_until->format('F j, Y') }}</dd>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $proposal->created_at->format('F j, Y') }}</dd>
                </div>

                @if($proposal->creator)
                <div>
                    <dt class="text-sm font-medium text-gray-500">From</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $proposal->creator->name }}</dd>
                </div>
                @endif
            </div>
        </div>

        <!-- Proposal Content -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Proposal Content</h2>
            <div class="prose max-w-none">
                {!! $proposal->content !!}
            </div>
        </div>

        <!-- Action Buttons -->
        @if(in_array($proposal->status, ['sent', 'viewed']))
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Take Action</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Accept/Sign Section -->
                <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                    <h3 class="font-semibold text-green-900 mb-3">Accept Proposal</h3>
                    <form method="POST" action="{{ route('proposals.public.sign', $proposal->signature_token) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="signature_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" name="signature_name" id="signature_name" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>

                            <div>
                                <label for="signature_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="signature_email" id="signature_email" required
                                    value="{{ $proposal->recipient_email }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>

                            <div>
                                <label for="signature_date" class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="signature_date" id="signature_date" required
                                    value="{{ now()->format('Y-m-d') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="agreed_terms" id="agreed_terms" required
                                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <label for="agreed_terms" class="ml-2 block text-sm text-gray-900">
                                    I agree to the terms and conditions outlined in this proposal
                                </label>
                            </div>

                            <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Accept & Sign Proposal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Reject Section -->
                <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                    <h3 class="font-semibold text-red-900 mb-3">Decline Proposal</h3>
                    <form method="POST" action="{{ route('proposals.public.reject', $proposal->signature_token) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason (Optional)</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                    placeholder="Please let us know why you're declining this proposal..."></textarea>
                            </div>

                            <button type="submit"
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                onclick="return confirm('Are you sure you want to decline this proposal?')">
                                Decline Proposal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if($proposal->status === 'signed')
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-green-800">Proposal Signed</h3>
                    <p class="text-green-700">
                        This proposal was signed on {{ $proposal->responded_at->format('F j, Y \a\t g:i A') }}.
                        @if($proposal->signature_data && isset($proposal->signature_data['name']))
                            Signed by {{ $proposal->signature_data['name'] }}.
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

        @if($proposal->status === 'rejected')
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-red-800">Proposal Declined</h3>
                    <p class="text-red-700">
                        This proposal was declined on {{ $proposal->responded_at->format('F j, Y \a\t g:i A') }}.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Questions about this proposal? Contact us at {{ config('mail.from.address') }}</p>
        </div>
    </div>
</body>
</html>
