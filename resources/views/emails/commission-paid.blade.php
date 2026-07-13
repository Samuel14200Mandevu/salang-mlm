<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Paid</title>
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
            background: #5ab638;
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
        .commission-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px 20px;
            border-left: 4px solid #5ab638;
            margin: 16px 0;
        }
        .commission-box .amount {
            font-size: 28px;
            font-weight: 800;
            color: #5ab638;
        }
        .commission-box .label {
            font-size: 13px;
            color: #6b7a94;
        }
        .commission-details {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 16px 0;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .commission-details .detail {
            flex: 1;
            min-width: 100px;
        }
        .commission-details .detail .label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: #8a9bb5;
            letter-spacing: 0.03em;
        }
        .commission-details .detail .value {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }
        .btn {
            display: inline-block;
            padding: 10px 24px;
            background: #5ab638;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .btn:hover {
            background: #4a9e2e;
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
            color: #5ab638;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            .email-body { padding: 20px; }
            .commission-box .amount { font-size: 24px; }
            .commission-details { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Salang MLM</h1>
            <p>Commission Payment</p>
        </div>

        <div class="email-body">
            <h2>Hello {{ $user->name }},</h2>
            <p>
                Congratulations! A commission has been credited to your wallet.
            </p>

            <div class="commission-box">
                <div class="label">Amount Credited</div>
                <div class="amount">${{ number_format($amount, 2) }}</div>
            </div>

            <div class="commission-details">
                <div class="detail">
                    <div class="label">Type</div>
                    <div class="value">{{ $type === 'consumer' ? 'Consumer Bonus' : ucfirst($type) }}</div>
                </div>
                <div class="detail">
                    <div class="label">Commission ID</div>
                    <div class="value">#{{ $commissionId }}</div>
                </div>
                <div class="detail">
                    <div class="label">Date</div>
                    <div class="value">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <p>
                You can view all your commissions in the commissions section of your dashboard.
            </p>

            <a href="{{ route('commissions.index') }}" class="btn">
                View My Commissions
            </a>
        </div>

        <div class="email-footer">
            <p>
                &copy; {{ date('Y') }} Salang Group. All rights reserved.
                <br>
                <a href="{{ route('home') }}">Visit our website</a> &bull; 
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
            </p>
        </div>
    </div>
</body>
</html>