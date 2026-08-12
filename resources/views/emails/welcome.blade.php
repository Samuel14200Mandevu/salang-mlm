<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Salang MLM</title>
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
        .welcome-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px 20px;
            border-left: 4px solid #5ab638;
            margin: 16px 0;
        }
        .welcome-box p {
            margin: 0;
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
        .btn-outline {
            background: transparent;
            color: #5ab638;
            border: 2px solid #5ab638;
        }
        .btn-outline:hover {
            background: #5ab638;
            color: white;
        }
        .quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 16px 0;
        }
        .quick-links a {
            display: inline-block;
            padding: 6px 14px;
            background: #f8fafc;
            border-radius: 6px;
            font-size: 13px;
            color: #4a5a72;
            text-decoration: none;
            border: 1px solid #e5e7eb;
        }
        .quick-links a:hover {
            border-color: #5ab638;
            color: #5ab638;
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
            .quick-links { flex-direction: column; }
            .quick-links a { text-align: center; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Salang MLM</h1>
            <p>Welcome to the Community!</p>
        </div>

        <div class="email-body">
            <h2>Welcome {{ $user->name }}!</h2>
            <p>
                We are excited to have you on board. You have taken the first step
                towards building your financial freedom.
            </p>

            <div class="welcome-box">
                <p>
                    <strong>Your journey starts now!</strong>
                    @if(isset($sponsorName) && $sponsorName)
                        You were sponsored by <strong>{{ $sponsorName }}</strong>.
                    @endif
                </p>
            </div>

            <h3 style="color: #111827; font-size: 16px; margin: 16px 0 8px;">What to do next</h3>

            <div class="quick-links">
                <a href="{{ route('subscriptions.index') }}">Choose a Package</a>
                <a href="{{ route('products.index') }}">Browse Products</a>
                <a href="{{ route('network.index') }}">Build Your Network</a>
                <a href="{{ route('wallet.index') }}">Check Your Wallet</a>
            </div>

            <p>
                Start earning commissions by purchasing a package and building your network.
                Your sponsor is here to help you get started.
            </p>

            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px;">
                <a href="{{ route('dashboard') }}" class="btn">Go to Dashboard</a>
                <a href="{{ route('subscriptions.index') }}" class="btn btn-outline">View Packages</a>
            </div>
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