<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Termination Notice – REMIS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f0f4f3; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }

        .email-wrapper { width: 100%; background-color: #f0f4f3; padding: 40px 16px; }
        .email-card    { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.10); }

        .header { background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 60%, #dc2626 100%); padding: 40px 32px 36px; text-align: center; }
        .header-logo-wrap { display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.12); border-radius: 14px; padding: 10px 18px; margin-bottom: 20px; }
        .header-logo-wrap img { height: 42px; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -.3px; margin-bottom: 6px; }
        .header p  { color: #fca5a5; font-size: 14px; }

        .notice-strip { background: #fef2f2; border-left: 4px solid #dc2626; margin: 28px 32px 0; border-radius: 10px; padding: 14px 18px; }
        .notice-strip p { font-size: 15px; color: #7f1d1d; font-weight: 600; }
        .notice-strip span { font-weight: 400; color: #991b1b; }

        .body { padding: 28px 32px 0; color: #374151; font-size: 15px; line-height: 1.7; }
        .body p { margin-bottom: 16px; }

        .details-box { background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 14px; margin: 24px 32px; padding: 24px; }
        .detail-row { display: flex; gap: 12px; margin-bottom: 14px; align-items: flex-start; }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-label { font-size: 11px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; min-width: 90px; padding-top: 2px; flex-shrink: 0; }
        .detail-value { font-size: 14px; font-weight: 600; color: #1f2937; }

        .reason-box { background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 12px; margin: 0 32px 24px; padding: 16px 18px; }
        .reason-box .reason-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #9ca3af; margin-bottom: 6px; }
        .reason-box .reason-text { font-size: 14px; color: #991b1b; font-weight: 600; }
        .reason-box .notes-text { font-size: 13px; color: #6b7280; margin-top: 6px; line-height: 1.5; }

        .btn-wrap { text-align: center; padding: 8px 32px 28px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #1b4332, #2d6a4f); color: #ffffff !important; font-size: 15px; font-weight: 700; padding: 14px 44px; border-radius: 50px; letter-spacing: .3px; box-shadow: 0 4px 14px rgba(27,67,50,.3); }

        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 0 32px 20px; }
        .url-fallback { padding: 0 32px 24px; }
        .url-fallback p { font-size: 12px; color: #9ca3af; margin-bottom: 6px; }
        .url-fallback a { font-size: 12px; color: #40916c; word-break: break-all; text-decoration: underline; }

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
        <h1>Lease Termination Notice</h1>
        <p>REMIS — Rental Management Information System</p>
    </div>

    {{-- Notice strip --}}
    <div class="notice-strip">
        <p>Dear <span>{{ $lease->tenant->name }}</span>,</p>
    </div>

    {{-- Intro --}}
    <div class="body">
        <p>We are writing to formally inform you that your lease agreement has been <strong>terminated</strong> by your landlord. The details of this termination are outlined below.</p>
    </div>

    {{-- Lease details --}}
    <div class="details-box">
        <div class="detail-row">
            <span class="detail-label">Property</span>
            <span class="detail-value">{{ $lease->property->name ?? 'N/A' }}</span>
        </div>
        @if($lease->unit)
        <div class="detail-row">
            <span class="detail-label">Unit</span>
            <span class="detail-value">{{ $lease->unit->unit_number }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span class="detail-label">Lease Period</span>
            <span class="detail-value">
                {{ $lease->start_date->format('d M Y') }} – {{ $lease->end_date->format('d M Y') }}
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Terminated On</span>
            <span class="detail-value">{{ $lease->terminated_at->format('d M Y') }}</span>
        </div>
    </div>

    {{-- Termination reason --}}
    <div class="reason-box">
        <div class="reason-label">Reason for Termination</div>
        <div class="reason-text">{{ ucfirst(str_replace('_', ' ', $lease->termination_reason)) }}</div>
        @if($lease->termination_notes)
        <div class="notes-text">{{ $lease->termination_notes }}</div>
        @endif
    </div>

    {{-- Body --}}
    <div class="body">
        <p>Please log in to your REMIS tenant portal to review your full lease history and any outstanding records.</p>
    </div>

    {{-- CTA --}}
    <div class="btn-wrap">
        <a href="{{ $loginUrl }}" class="btn">View My Lease Details &rarr;</a>
    </div>

    <hr class="divider">

    {{-- URL fallback --}}
    <div class="url-fallback">
        <p>If the button above does not work, copy and paste this link into your browser:</p>
        <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} <strong>REMIS Estates</strong> &mdash; Rental Management Information System</p>
        <p>This notice was sent to {{ $lease->tenant->email }} regarding your tenancy at {{ $lease->property->name ?? 'the property' }}.</p>
        <p class="no-reply">This is an automated message — please do not reply to this email.</p>
    </div>

</div>
</div>
</body>
</html>
