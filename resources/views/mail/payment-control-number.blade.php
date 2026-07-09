<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Your Rent Payment Control Number</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f0f4f3; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        img { border: 0; display: block; }

        .email-wrapper { width: 100%; background-color: #f0f4f3; padding: 40px 16px; }
        .email-card    { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }

        .header { background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 60%, #40916c 100%); padding: 40px 32px 36px; text-align: center; }
        .header-logo-wrap { display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.12); border-radius: 14px; padding: 10px 18px; margin-bottom: 20px; }
        .header-logo-wrap img { height: 42px; }
        .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: -.3px; margin-bottom: 6px; }
        .header p  { color: #b7e4c7; font-size: 14px; }

        .welcome-strip { background: #d8f3dc; border-left: 4px solid #40916c; margin: 28px 32px 0; border-radius: 10px; padding: 14px 18px; }
        .welcome-strip p { font-size: 15px; color: #1b4332; font-weight: 600; }
        .welcome-strip span { font-weight: 400; color: #2d6a4f; }

        .body { padding: 24px 32px 0; color: #374151; font-size: 15px; line-height: 1.7; }
        .body p { margin-bottom: 14px; }

        .ctrl-box { background: #f0fdf4; border: 2px solid #40916c; border-radius: 16px; margin: 24px 32px; padding: 28px; text-align: center; }
        .ctrl-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 10px; }
        .ctrl-number { font-size: 32px; font-weight: 800; color: #1b4332; letter-spacing: 3px; font-family: 'Courier New', monospace; margin-bottom: 8px; }
        .ctrl-hint { font-size: 13px; color: #6b7280; }

        .details-box { margin: 0 32px 24px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .details-row { display: flex; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .details-row:last-child { border-bottom: none; }
        .details-row .dk { color: #9ca3af; font-weight: 500; }
        .details-row .dv { color: #1f2937; font-weight: 600; }

        .steps-section { margin: 0 32px 24px; }
        .steps-title { font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 14px; }
        .step { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
        .step-num { width: 26px; height: 26px; border-radius: 50%; background: #40916c; color: #fff; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
        .step-text { font-size: 14px; color: #4b5563; line-height: 1.55; }
        .step-text strong { color: #1b4332; }

        .alert-box { margin: 0 32px 28px; background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 12px; padding: 16px 18px; }
        .alert-box p { font-size: 13px; color: #92400e; line-height: 1.5; }
        .alert-box strong { color: #7c2d12; }

        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 0 32px 24px; }

        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 24px 32px; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; margin-bottom: 4px; }
        .footer .no-reply { font-size: 11px; color: #d1d5db; margin-top: 8px; }
    </style>
</head>
<body>
<div class="email-wrapper">
<div class="email-card">

    {{-- Header --}}
    <div class="header">
        <div class="header-logo-wrap">
            <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS">
        </div>
        <h1>Rent Payment Due</h1>
        <p>REMIS — Rental Management Information System</p>
    </div>

    {{-- Welcome strip --}}
    <div class="welcome-strip">
        <p>Hi, <span>{{ $tenant->name }}</span> — your rent payment is due soon.</p>
    </div>

    {{-- Body --}}
    <div class="body">
        <p>Your landlord has generated a <strong>NMB Bank control number</strong> for your upcoming rent payment. Use the number below to pay via any NMB channel.</p>
    </div>

    {{-- Control number --}}
    <div class="ctrl-box">
        <div class="ctrl-label">Your NMB Control Number</div>
        <div class="ctrl-number">{{ $payment->control_number }}</div>
        <div class="ctrl-hint">Use this number to pay at any NMB branch, ATM, or via internet/mobile banking</div>
    </div>

    {{-- Payment details --}}
    <div class="details-box">
        <div class="details-row">
            <span class="dk">Property</span>
            <span class="dv">{{ optional(optional($payment->lease)->property)->name ?? '—' }}</span>
        </div>
        @if(optional(optional($payment->lease)->unit)->unit_number)
        <div class="details-row">
            <span class="dk">Unit</span>
            <span class="dv">{{ optional(optional($payment->lease)->unit)->unit_number }}</span>
        </div>
        @endif
        <div class="details-row">
            <span class="dk">Amount Due</span>
            <span class="dv" style="color:#1b4332;">TZS {{ number_format($payment->amount, 0) }}</span>
        </div>
        <div class="details-row">
            <span class="dk">Due Date</span>
            <span class="dv">{{ $payment->due_date->format('d M Y') }}</span>
        </div>
        <div class="details-row">
            <span class="dk">Currency</span>
            <span class="dv">Tanzanian Shillings (TZS)</span>
        </div>
    </div>

    {{-- Steps --}}
    <div class="steps-section">
        <div class="steps-title">How to pay</div>
        <div class="step">
            <div class="step-num">1</div>
            <div class="step-text">Visit any <strong>NMB Bank branch</strong> or use <strong>NMB internet banking / mobile app</strong>.</div>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <div class="step-text">Select <strong>"Pay Bill"</strong> or <strong>"Control Number Payment"</strong>.</div>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <div class="step-text">Enter the control number <strong>{{ $payment->control_number }}</strong> when prompted.</div>
        </div>
        <div class="step">
            <div class="step-num">4</div>
            <div class="step-text">Confirm the amount <strong>TZS {{ number_format($payment->amount, 0) }}</strong> and complete the payment. Keep your receipt for reference.</div>
        </div>
    </div>

    {{-- Alert --}}
    <div class="alert-box">
        <p><strong>Important:</strong> This control number is valid until your payment due date of <strong>{{ $payment->due_date->format('d M Y') }}</strong>. Late payments may incur additional charges as per your lease agreement. Contact your landlord immediately if you have any concerns.</p>
    </div>

    <hr class="divider">

    {{-- Footer --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} <strong>REMIS Estates</strong> &mdash; Rental Management Information System</p>
        <p>This email was sent to {{ $tenant->email }} regarding your tenancy.</p>
        <p class="no-reply">This is an automated message — please do not reply to this email.</p>
    </div>

</div>
</div>
</body>
</html>
