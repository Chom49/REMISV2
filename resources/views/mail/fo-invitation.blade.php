<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Officer Invitation – REMIS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #eef2ff; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        img { border: 0; display: block; }

        .email-wrapper { width: 100%; background-color: #eef2ff; padding: 40px 16px; }
        .email-card    { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }

        .header { background: linear-gradient(135deg, #312e81 0%, #4338ca 60%, #6366f1 100%); padding: 40px 32px 36px; text-align: center; }
        .header-logo-wrap { display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.12); border-radius: 14px; padding: 10px 18px; margin-bottom: 20px; }
        .header-logo-wrap img { height: 42px; }
        .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: -.3px; margin-bottom: 6px; }
        .header p  { color: #c7d2fe; font-size: 14px; }

        .role-badge { text-align: center; padding: 20px 32px 0; }
        .role-badge span { display: inline-block; background: #eef2ff; border: 1.5px solid #a5b4fc; color: #4338ca; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; padding: 6px 18px; border-radius: 999px; }

        .welcome-strip { background: #e0e7ff; border-left: 4px solid #6366f1; margin: 20px 32px 0; border-radius: 10px; padding: 14px 18px; }
        .welcome-strip p { font-size: 15px; color: #1e1b4b; font-weight: 600; }
        .welcome-strip span { font-weight: 400; color: #4338ca; }

        .body { padding: 24px 32px 0; color: #374151; font-size: 15px; line-height: 1.7; }
        .body p { margin-bottom: 16px; }

        .creds-box { background: #f5f3ff; border: 1.5px solid #a5b4fc; border-radius: 14px; margin: 24px 32px; padding: 24px; }
        .creds-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #6b7280; margin-bottom: 16px; }
        .cred-row { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .cred-row:last-child { margin-bottom: 0; }
        .cred-icon { width: 36px; height: 36px; border-radius: 8px; background: #e0e7ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .cred-icon svg { width: 18px; height: 18px; }
        .cred-info .cred-key { font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
        .cred-info .cred-val { font-size: 15px; font-weight: 700; color: #1e1b4b; font-family: 'Courier New', monospace; word-break: break-all; }
        .password-val { font-size: 20px !important; letter-spacing: 2px; color: #4338ca !important; }

        .steps-section { margin: 0 32px 24px; }
        .steps-title { font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 14px; }
        .step { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
        .step-num { width: 24px; height: 24px; border-radius: 50%; background: #6366f1; color: #fff; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
        .step-text { font-size: 14px; color: #4b5563; line-height: 1.5; }
        .step-text strong { color: #1e1b4b; }

        .btn-wrap { text-align: center; padding: 8px 32px 28px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #4338ca, #6366f1); color: #ffffff !important; font-size: 16px; font-weight: 700; padding: 16px 48px; border-radius: 50px; letter-spacing: .3px; box-shadow: 0 4px 14px rgba(99,102,241,.40); }

        .security-notice { margin: 0 32px 28px; background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 12px; padding: 16px 18px; display: flex; gap: 12px; align-items: flex-start; }
        .security-notice svg { flex-shrink: 0; margin-top: 2px; }
        .security-notice p { font-size: 13px; color: #92400e; line-height: 1.5; }
        .security-notice strong { color: #7c2d12; }

        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 0 32px 20px; }

        .url-fallback { padding: 0 32px 24px; }
        .url-fallback p { font-size: 12px; color: #9ca3af; margin-bottom: 6px; }
        .url-fallback a { font-size: 12px; color: #6366f1; word-break: break-all; text-decoration: underline; }

        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 24px 32px; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; margin-bottom: 4px; }
        .footer .no-reply { font-size: 11px; color: #d1d5db; margin-top: 8px; }
    </style>
</head>
<body>
<div class="email-wrapper">
<div class="email-card">

    <div class="header">
        <div class="header-logo-wrap">
            <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS">
        </div>
        <h1>You've Been Appointed!</h1>
        <p>REMIS — Rental Management Information System</p>
    </div>

    <div class="role-badge">
        <span>Financial Officer</span>
    </div>

    <div class="welcome-strip">
        <p>Hello, <span>{{ $fo->name }}</span>!</p>
    </div>

    <div class="body">
        <p>You have been appointed as a <strong>Financial Officer</strong> on REMIS. Your role is to manage all payment operations for the property portfolio assigned to you, including:</p>
        <p style="padding-left:16px; color:#4338ca;">
            ✓ &nbsp;Generate NMB payment control numbers for tenants<br>
            ✓ &nbsp;Send payment instructions to tenants via email or SMS<br>
            ✓ &nbsp;Verify and confirm received payments<br>
            ✓ &nbsp;Access financial reports and payment history
        </p>
        <p>Your login credentials are below. You will be required to set a new password on your first login.</p>
    </div>

    <div class="creds-box">
        <div class="creds-label">🔐 &nbsp;Your Login Credentials</div>
        <div class="cred-row">
            <div class="cred-icon">
                <svg fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="cred-info">
                <div class="cred-key">Email Address</div>
                <div class="cred-val">{{ $fo->email }}</div>
            </div>
        </div>
        <div class="cred-row">
            <div class="cred-icon">
                <svg fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="cred-info">
                <div class="cred-key">Temporary Password (must change on first login)</div>
                <div class="cred-val password-val">{{ $plainPassword }}</div>
            </div>
        </div>
    </div>

    <div class="steps-section">
        <div class="steps-title">Getting Started</div>
        <div class="step">
            <div class="step-num">1</div>
            <div class="step-text">Click <strong>Login to REMIS</strong> below to open the login page.</div>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <div class="step-text">Enter <strong>{{ $fo->email }}</strong> and the temporary password above.</div>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <div class="step-text"><strong>Set a new password</strong> immediately — this is required before you can access the system.</div>
        </div>
        <div class="step">
            <div class="step-num">4</div>
            <div class="step-text">You will land on your Financial Officer dashboard, ready to manage payments.</div>
        </div>
    </div>

    <div class="btn-wrap">
        <a href="{{ $loginUrl }}" class="btn">Login to REMIS &rarr;</a>
    </div>

    <div class="security-notice">
        <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <p><strong>Security Notice:</strong> Your temporary password must be changed immediately after first login. Do not share your credentials with anyone. REMIS will never ask for your password via email or phone.</p>
    </div>

    <hr class="divider">

    <div class="url-fallback">
        <p>If the button above does not work, copy and paste this link into your browser:</p>
        <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} <strong>REMIS Estates</strong> &mdash; Rental Management Information System</p>
        <p>This email was sent to {{ $fo->email }} because you were appointed as a Financial Officer on REMIS.</p>
        <p class="no-reply">This is an automated message — please do not reply to this email.</p>
    </div>

</div>
</div>
</body>
</html>
