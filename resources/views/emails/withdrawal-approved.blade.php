<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Approved</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .email-header {
            background: #22c55e;
            padding: 24px 30px;
            text-align: center;
        }
        .email-header h1 {
            color: white;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .email-header p {
            color: rgba(255,255,255,0.9);
            margin: 4px 0 0;
            font-size: 14px;
        }
        .email-body {
            padding: 30px;
        }
        .email-body h2 {
            color: #111827;
            font-size: 20px;
            margin: 0 0 8px;
        }
        .email-body p {
            color: #4a5a72;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 16px;
        }
        .withdrawal-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px 20px;
            border-left: 4px solid #22c55e;
            margin: 16px 0;
        }
        .withdrawal-box .amount {
            font-size: 28px;
            font-weight: 800;
            color: #22c55e;
        }
        .withdrawal-box .label {
            font-size: 13px;
            color: #6b7a94;
        }
        .withdrawal-box .fee-info {
            font-size: 12px;
            color: #6b7a94;
            margin-top: 4px;
        }
        .withdrawal-details {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 16px 0;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .withdrawal-details .detail {
            flex: 1;
            min-width: 100px;
        }
        .withdrawal-details .detail .label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: #8a9bb5;
            letter-spacing: 0.03em;
        }
        .withdrawal-details .detail .value {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }
        .btn {
            display: inline-block;
            padding: 10px 24px;
            background: #22c55e;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .btn:hover {
            background: #16a34a;
        }
        .email-footer {
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .email-footer p {
            color: #8a9bb5;
            font-size: 12px;
            margin: 0;
        }
        .email-footer a {
            color: #22c55e;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            .email-body { padding: 20px; }
            .withdrawal-box .amount { font-size: 24px; }
            .withdrawal-details { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Salang MLM</h1>
            <p>Withdrawal Approved</p>
        </div>

        <div class="email-body">
            <h2>Hello {{ $user->name }},</h2>
            <p>
                Your withdrawal request has been <strong>approved</strong> and processed successfully.
            </p>

            <div class="withdrawal-box">
                <div class="label">Amount Withdrawn</div>
                <div class="amount">${{ number_format($amount, 2) }}</div>
                <div class="fee-info">
                    Fee: ${{ number_format($fee, 2) }} • Net: ${{ number_format($netAmount, 2) }}
                </div>
            </div>

            <div class="withdrawal-details">
                <div class="detail">
                    <div class="label">Method</div>
                    <div class="value">{{ ucfirst($method) }}</div>
                </div>
                <div class="detail">
                    <div class="label">Withdrawal ID</div>
                    <div class="value">#{{ $withdrawalId }}</div>
                </div>
                <div class="detail">
                    <div class="label">Date</div>
                    <div class="value">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <p>
                The funds have been sent to your {{ $method }} account.
            </p>

            <a href="{{ route('withdrawal.index') }}" class="btn">
                View Withdrawals
            </a>
        </div>

        <div class="email-footer">
            <p>
                &copy; {{ date('Y') }} Salang Group. All rights reserved.
                <br>
                <a href="{{ route('home') }}">Visit our website</a> • 
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
            </p>
        </div>
    </div>
</body>
</html>