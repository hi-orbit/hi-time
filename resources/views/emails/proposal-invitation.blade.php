<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $proposal->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .proposal-number {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .proposal-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .proposal-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .cta-button:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="proposal-number">{{ $proposal->proposal_number }}</div>
        <h1 class="proposal-title">{{ $proposal->title }}</h1>
    </div>

    <div class="content">
        <p>Dear {{ $recipientName }},</p>

        @if($proposal->email_body)
            {!! nl2br(e($proposal->email_body)) !!}
        @else
            <p>We're pleased to share a new proposal with you. Please review the details below and let us know if you have any questions.</p>
        @endif

        <div class="proposal-details">
            @if($proposal->amount)
            <div class="detail-row">
                <span class="detail-label">Amount:</span>
                <span>${{ number_format($proposal->amount, 2) }}</span>
            </div>
            @endif

            @if($proposal->valid_until)
            <div class="detail-row">
                <span class="detail-label">Valid Until:</span>
                <span>{{ $proposal->valid_until->format('F j, Y') }}</span>
            </div>
            @endif

            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span>{{ ucfirst($proposal->status) }}</span>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="{{ $viewUrl }}" class="cta-button">
                View & Accept Proposal
            </a>
        </div>

        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Click the button above to review the full proposal</li>
            <li>Contact us with any questions or clarifications</li>
            <li>Accept the proposal online when you're ready to proceed</li>
        </ul>
    </div>

    <div class="footer">
        <p>
            This proposal is valid until {{ $proposal->valid_until ? $proposal->valid_until->format('F j, Y') : 'further notice' }}.
        </p>
        <p>
            If you're unable to click the button above, copy and paste this link into your browser:<br>
            <a href="{{ $viewUrl }}">{{ $viewUrl }}</a>
        </p>
        <p>
            Questions? Contact us at {{ config('mail.from.address') }} or reply to this email.
        </p>
    </div>
</body>
</html>
