<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $proposal->title }} - Proposal #{{ $proposal->proposal_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-size: 14px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin: -20px -20px 30px -20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .header .proposal-number {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-draft { background: #fef3c7; color: #92400e; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-section h3 {
            color: #374151;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .info-item {
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #111827;
            font-size: 14px;
            margin-top: 2px;
        }

        .amount {
            font-size: 18px;
            font-weight: 700;
            color: #059669;
        }

        .content-section {
            border-top: 1px solid #e5e7eb;
            padding-top: 30px;
            margin-top: 30px;
        }

        .content-section h3 {
            color: #374151;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .proposal-content {
            line-height: 1.8;
        }

        .proposal-content h1 {
            color: #1f2937;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            margin-top: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }

        .proposal-content h2 {
            color: #374151;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
            margin-top: 18px;
        }

        .proposal-content h3 {
            color: #4b5563;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            margin-top: 15px;
        }

        .proposal-content p {
            color: #4b5563;
            margin-bottom: 12px;
            line-height: 1.75;
        }

        .proposal-content ul,
        .proposal-content ol {
            margin-bottom: 12px;
            padding-left: 20px;
        }

        .proposal-content li {
            color: #4b5563;
            margin-bottom: 6px;
            line-height: 1.6;
        }

        .proposal-content strong {
            color: #1f2937;
            font-weight: 600;
        }

        .proposal-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .proposal-content th {
            background-color: #f9fafb;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #d1d5db;
        }

        .proposal-content td {
            padding: 10px;
            border: 1px solid #e5e7eb;
        }

        .proposal-content blockquote {
            border-left: 4px solid #3b82f6;
            background-color: #f8fafc;
            padding: 15px;
            margin: 20px 0;
            font-style: italic;
        }

        .proposal-content hr {
            border: none;
            border-top: 1px solid #d1d5db;
            margin: 25px 0;
        }

        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
        }

        .accepted-notice {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .accepted-notice h4 {
            color: #065f46;
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .accepted-notice p {
            color: #047857;
            margin: 0;
        }

        @media print {
            body { margin: 0; padding: 15px; }
            .header { margin: -15px -15px 20px -15px; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $proposal->title }}</h1>
        <p class="proposal-number">Proposal #{{ $proposal->proposal_number }}</p>
        <span class="status-badge status-{{ $proposal->status }}">{{ ucfirst($proposal->status) }}</span>
    </div>

    <!-- Proposal Information Grid -->
    <div class="info-grid">
        <div class="info-section">
            <h3>Proposal Details</h3>
            <div class="info-item">
                <div class="info-label">Created</div>
                <div class="info-value">{{ $proposal->created_at->format('F j, Y') }}</div>
            </div>
            @if($proposal->valid_until)
            <div class="info-item">
                <div class="info-label">Valid Until</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($proposal->valid_until)->format('F j, Y') }}</div>
            </div>
            @endif
            @if($proposal->amount)
            <div class="info-item">
                <div class="info-label">Proposal Amount</div>
                <div class="info-value amount">£{{ number_format($proposal->amount, 2) }}</div>
            </div>
            @endif
            @if($proposal->template)
            <div class="info-item">
                <div class="info-label">Template</div>
                <div class="info-value">{{ $proposal->template->name }}</div>
            </div>
            @endif
        </div>

        <div class="info-section">
            <h3>Client Information</h3>
            @if($proposal->lead)
                <div class="info-item">
                    <div class="info-label">Lead Contact</div>
                    <div class="info-value">{{ $proposal->lead->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $proposal->lead->email }}</div>
                </div>
                @if($proposal->lead->company)
                <div class="info-item">
                    <div class="info-label">Company</div>
                    <div class="info-value">{{ $proposal->lead->company }}</div>
                </div>
                @endif
                @if($proposal->lead->phone)
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $proposal->lead->phone }}</div>
                </div>
                @endif
                @if($proposal->lead->address)
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $proposal->lead->address }}</div>
                </div>
                @endif
            @elseif($proposal->customer)
                <div class="info-item">
                    <div class="info-label">Customer</div>
                    <div class="info-value">{{ $proposal->customer->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $proposal->customer->email }}</div>
                </div>
                @if($proposal->customer->company)
                <div class="info-item">
                    <div class="info-label">Company</div>
                    <div class="info-value">{{ $proposal->customer->company }}</div>
                </div>
                @endif
                @if($proposal->customer->phone)
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $proposal->customer->phone }}</div>
                </div>
                @endif
                @if($proposal->customer->address)
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $proposal->customer->address }}</div>
                </div>
                @endif
            @elseif($proposal->client_name || $proposal->client_email)
                @if($proposal->client_name)
                <div class="info-item">
                    <div class="info-label">Client Name</div>
                    <div class="info-value">{{ $proposal->client_name }}</div>
                </div>
                @endif
                @if($proposal->client_email)
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $proposal->client_email }}</div>
                </div>
                @endif
            @else
                <p style="color: #6b7280; font-style: italic;">No client information specified</p>
            @endif
        </div>
    </div>

    <!-- Proposal Content -->
    @if($proposal->content)
    <div class="content-section">
        <h3>Proposal Content</h3>
        <div class="proposal-content">
            @php
                // Process template variables in the content for PDF
                $processedContent = $proposal->content;

                // Get client data for replacements
                $clientData = [];
                if ($proposal->lead) {
                    $clientData = [
                        'client_name' => $proposal->lead->name ?? '',
                        'client_email' => $proposal->lead->email ?? '',
                        'client_address' => $proposal->lead->address ?? '',
                        'client_company_number' => $proposal->lead->company_number ?? '',
                        'company_name' => $proposal->lead->company ?? '',
                        'company_number' => $proposal->lead->company_number ?? '',
                        'company_address' => $proposal->lead->address ?? '',
                    ];
                } elseif ($proposal->customer) {
                    $clientData = [
                        'client_name' => $proposal->customer->name ?? '',
                        'client_email' => $proposal->customer->email ?? '',
                        'client_address' => $proposal->customer->address ?? '',
                        'client_company_number' => $proposal->customer->company_number ?? '',
                        'company_name' => $proposal->customer->company ?? $proposal->customer->name ?? '',
                        'company_number' => $proposal->customer->company_number ?? '',
                        'company_address' => $proposal->customer->address ?? '',
                    ];
                } elseif ($proposal->client_name || $proposal->client_email) {
                    $clientData = [
                        'client_name' => $proposal->client_name ?? '',
                        'client_email' => $proposal->client_email ?? '',
                        'client_address' => '',
                        'client_company_number' => '',
                        'company_name' => '',
                        'company_number' => '',
                        'company_address' => '',
                    ];
                }

                // Create comprehensive replacements array
                $replacements = [
                    'client_name' => $clientData['client_name'] ?: '[Client Name]',
                    'client_email' => $clientData['client_email'] ?: '[Client Email]',
                    'client_address' => $clientData['client_address'] ?: '[Client Address]',
                    'client_company_number' => $clientData['client_company_number'] ?: '[Company Number]',
                    'company_name' => $clientData['company_name'] ?: '[Company Name]',
                    'company_number' => $clientData['company_number'] ?: '[Company Number]',
                    'company_address' => $clientData['company_address'] ?: '[Company Address]',
                    'proposal_title' => $proposal->title ?: '[Proposal Title]',
                    'amount' => $proposal->amount ? '£' . number_format($proposal->amount, 2) : '[Amount]',
                    'date' => now()->format('jS F Y'),
                    'valid_until' => $proposal->valid_until ? \Carbon\Carbon::parse($proposal->valid_until)->format('jS F Y') : '[Valid Until Date]',
                    'first_name' => $clientData['client_name'] ? explode(' ', $clientData['client_name'])[0] : '[First Name]',
                    'last_name' => $clientData['client_name'] ? implode(' ', array_slice(explode(' ', $clientData['client_name']), 1)) : '[Last Name]'
                ];

                // Replace template variables with case-insensitive matching
                foreach ($replacements as $key => $value) {
                    // Replace with double curly braces
                    $processedContent = preg_replace('/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/i', $value, $processedContent);
                    // Also try single curly braces (in case editor uses different format)
                    $processedContent = preg_replace('/\{\s*' . preg_quote($key, '/') . '\s*\}/i', $value, $processedContent);
                }
            @endphp

            {!! $processedContent !!}
        </div>
    </div>
    @endif

    <!-- Acceptance Information -->
    @if($proposal->status === 'accepted' && $proposal->accepted_at)
    <div class="accepted-notice">
        <h4>✓ Proposal Accepted</h4>
        <p>This proposal was accepted on {{ $proposal->accepted_at->format('F j, Y \a\t g:i A') }}.</p>
        @if($proposal->signature_data)
            <p>Digital signature recorded and verified.</p>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        @if($proposal->template)
            <p>Template: {{ $proposal->template->name }}</p>
        @endif
    </div>
</body>
</html>
