<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rank Upgraded</title>
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
            background: #8b5cf6;
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
        .rank-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #8b5cf6;
            margin: 16px 0;
            text-align: center;
        }
        .rank-box .old-rank {
            font-size: 18px;
            color: #6b7a94;
            text-decoration: line-through;
        }
        .rank-box .arrow {
            font-size: 24px;
            color: #8b5cf6;
            margin: 0 12px;
        }
        .rank-box .new-rank {
            font-size: 28px;
            font-weight: 800;
            color: #8b5cf6;
        }
        .rank-box .pv-info {
            font-size: 13px;
            color: #6b7a94;
            margin-top: 8px;
        }
        .btn {
            display: inline-block;
            padding: 10px 24px;
            background: #8b5cf6;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .btn:hover {
            background: #7c3aed;
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
            color: #8b5cf6;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            .email-body { padding: 20px; }
            .rank-box .new-rank { font-size: 22px; }
            .rank-box .arrow { margin: 0 8px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Salang MLM</h1>
            <p>Rank Promotion</p>
        </div>

        <div class="email-body">
            <h2>Congratulations {{ $user->name }}!</h2>
            <p>
                You have been promoted to a new rank! This is a significant achievement
                in your journey with Salang MLM.
            </p>

            <div class="rank-box">
                <span class="old-rank">{{ $oldRank }}</span>
                <span class="arrow">→</span>
                <span class="new-rank">{{ $newRank }}</span>
                <div class="pv-info">{{ number_format($pvBalance) }} PV achieved</div>
            </div>

            <p>
                As a {{ $newRank }}, you now qualify for higher commission rates and
                additional benefits. Keep up the great work!
            </p>

            <a href="{{ route('rank.index') }}" class="btn">
                View My Rank
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