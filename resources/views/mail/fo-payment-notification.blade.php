<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received – REMIS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f0f4f3; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }

        .email-wrapper { width: 100%; background-color: #f0f4f3; padding: 40px 16px; }
        .email-card    { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }

        .header { background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 60%, #40916c 100%); padding: 36px 32px 32px; text-align: center; }
        .header-logo-wrap { display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.12); border-radius: 14px; padding: 10px 18px; margin-bottom: 18px; }
        .header-logo-wrap img { height: 40px; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .header p  { color: #b7e4c7; font-size: 13px; }

        .paid-badge { text-align: center; padding: 24px 32px 0; }
        .paid-badge-inner { display: inline-flex; align-items: center; gap: 8px; background: #d1fae5; border: 1.5px solid #6ee7b7; color: #065f46; font-size: 14px; font-weight: 700; padding: 8px 20px; border-radius: 999px; }
        .paid-badge-inner svg { width: 18px; height: 18px; }

        .body { padding: 24px 32px 0; color: #374151; font-size: 15px; line-height: 1.7; }
        .body p { margin-bottom: 12px; }

        .detail-box { background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 14px; margin: 20px 32px; padding: 6px 0; }
        .detail-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #dcfce7; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
        .detail-value { font-size: 14px; color: #1b4332; font-weight: 700; text-align: right; max-width: 60%; word-break: break-word; }
        .detail-value.amount { font-size: 18px; color: #166534; }

        .fo-note { margin: 0 32px 24px; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 12px; padding: 14px 18px; font-size: 13px; color: #1e40af; line-height: 1.5; }
        .fo-note strong { color: #1e3a8a; }

        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 0 32px 20px; }

        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 22px 32px; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; margin-bottom: 3px; }
    </style>
</head>
<body>
<div class="email-wrapper">
<div class="email-card">

    <div class="header">
        <div class="header-logo-wrap">
            <img src="{{ asset('images/signIn/logo_transparent.png') }}" alt="REMIS">
        </div>
        <h1>Payment Confirmed</h1>
        <p>REMIS — Rental Management Information System</p>
    </div>

    <div class="paid-badge">
        <div class="paid-badge-inner">
            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Payment Marked as Paid
        </div>
    </div>

    <div class="body">
        <p>Hi <strong>{{ $landlord->name }}</strong>,</p>
        <p>Your Financial Officer <strong>{{ $foName }}</strong> has confirmed a rent payment on your behalf. Details are below.</p>
    </div>

    <div class="detail-box">
        <div class="detail-row">
            <span class="detail-label">Tenant</span>
            <span class="detail-value">{{ optional($payment->tenant)->name ?? 'N/A' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Property</span>
            <span class="detail-value">{{ optional(optional($payment->lease)->property)->name ?? 'N/A' }}</span>
        </div>
        @if(optional(optional($payment->lease)->unit)->unit_number)
        <div class="detail-row">
            <span class="detail-label">Unit</span>
            <span class="detail-value">{{ $payment->lease->unit->unit_number }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span class="detail-label">Amount</span>
            <span class="detail-value amount">TZS {{ number_format($payment->amount, 0) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Payment Date</span>
            <span class="detail-value">{{ optional($payment->paid_date)->format('d M Y') ?? now()->format('d M Y') }}</span>
        </div>
        @if($payment->reference || $payment->nmb_receipt_number)
        <div class="detail-row">
            <span class="detail-label">Reference / Receipt</span>
            <span class="detail-value">{{ $payment->reference ?? $payment->nmb_receipt_number }}</span>
        </div>
        @endif
        @if($payment->notes)
        <div class="detail-row">
            <span class="detail-label">Notes</span>
            <span class="detail-value">{{ $payment->notes }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span class="detail-label">Verified By</span>
            <span class="detail-value">{{ $foName }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Confirmed At</span>
            <span class="detail-value">{{ now()->format('d M Y, H:i') }}</span>
        </div>
    </div>

    <div class="fo-note">
        <strong>Note:</strong> This payment was verified and confirmed by your Financial Officer. You can view the full payment history in your REMIS landlord dashboard under Reports.
    </div>

    <hr class="divider">

    <div class="footer">
        <p>&copy; {{ date('Y') }} <strong>REMIS Estates</strong> &mdash; Rental Management Information System</p>
        <p>This notification was sent to {{ $landlord->email }} because a Financial Officer confirmed a payment on your account.</p>
        <p>This is an automated message — please do not reply to this email.</p>
    </div>

</div>
</div>
</body>
</html>
