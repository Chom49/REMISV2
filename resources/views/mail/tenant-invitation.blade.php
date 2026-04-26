<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're invited to REMIS</title>
    <style>
        body { margin: 0; padding: 0; background: #f3f4f6; font-family: 'Inter', Arial, sans-serif; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { background: #e8f5f0; text-align: center; padding: 36px 32px 28px; }
        .header img { height: 52px; }
        .header h1 { margin: 16px 0 0; font-size: 22px; font-weight: 700; color: #1b4332; }
        .body { padding: 32px; color: #374151; font-size: 15px; line-height: 1.6; }
        .body p { margin: 0 0 16px; }
        .btn-wrap { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background: #40916c; color: #ffffff !important; text-decoration: none; font-weight: 600; font-size: 15px; padding: 14px 36px; border-radius: 50px; }
        .note { font-size: 13px; color: #9ca3af; text-align: center; margin-top: 24px; }
        .footer { background: #f9fafb; text-align: center; padding: 20px 32px; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS">
        <h1>Welcome to REMIS</h1>
    </div>
    <div class="body">
        <p>Hi <strong>{{ $tenant->name }}</strong>,</p>
        <p>Your landlord has created an account for you on <strong>REMIS</strong> — a rental management platform that lets you view your lease, track payments, and submit maintenance requests all in one place.</p>
        <p>Click the button below to create your password and activate your account:</p>
        <div class="btn-wrap">
            <a href="{{ $setupUrl }}" class="btn">Create My Password</a>
        </div>
        <p>Or copy and paste this link into your browser:</p>
        <p style="word-break:break-all; font-size:13px; color:#6b7280;">{{ $setupUrl }}</p>
        <p class="note">This link expires in 7 days. If you did not expect this email, you can safely ignore it.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} REMIS &mdash; Rental Management Information System
    </div>
</div>
</body>
</html>
