<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Official Warning Notice</title>
<style>
    body {
        font-family: 'Helvetica Neue', Arial, sans-serif;
        background: #f4f6f8;
        margin: 0;
        padding: 32px 16px;
        color: #1a1a2e;
        font-size: 15px;
        line-height: 1.7;
    }
    .wrapper {
        max-width: 600px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .header {
        background: #1b4332;
        padding: 28px 36px;
        text-align: center;
    }
    .header img {
        height: 52px;
        width: auto;
    }
    .header-title {
        color: #ffffff;
        font-size: 11px;
        letter-spacing: 4px;
        text-transform: uppercase;
        margin-top: 10px;
        opacity: 0.75;
    }
    .notice-banner {
        background: #fff3cd;
        border-top: 4px solid #e6a817;
        padding: 14px 36px;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        color: #856404;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .body {
        padding: 36px 36px 28px;
    }
    .greeting {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 16px;
    }
    p {
        margin: 0 0 16px;
        color: #444;
    }
    .info-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #1b4332;
        border-radius: 8px;
        padding: 18px 20px;
        margin: 22px 0;
    }
    .info-row {
        display: flex;
        gap: 12px;
        margin-bottom: 10px;
        font-size: 14px;
    }
    .info-row:last-child { margin-bottom: 0; }
    .info-label {
        font-weight: 700;
        color: #1b4332;
        min-width: 130px;
        flex-shrink: 0;
    }
    .info-value {
        color: #333;
    }
    .warning-text {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 14px 18px;
        font-size: 14px;
        color: #7f1d1d;
        margin: 22px 0;
    }
    .footer {
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        padding: 24px 36px;
        font-size: 13px;
        color: #6b7280;
    }
    .signature {
        margin-bottom: 18px;
    }
    .signature strong {
        display: block;
        color: #1a1a2e;
        font-size: 14px;
        margin-bottom: 2px;
    }
    .footer-note {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
        text-align: center;
    }
</style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        @php $logoPath = public_path('images/signIn/logo_transparent.png'); @endphp
        @if(file_exists($logoPath))
            <div style="background:#ffffff; border-radius:10px; display:inline-block; padding:10px 20px; margin-bottom:10px;">
                <img src="{{ $message->embed($logoPath) }}" alt="REMIS" style="height:52px;width:auto;display:block;">
            </div>
        @else
            <div style="font-size:22px;font-weight:900;letter-spacing:6px;color:#fff;">REMIS</div>
        @endif
        <div class="header-title">Real Estate Management Information System</div>
    </div>

    {{-- Warning banner --}}
    <div class="notice-banner">
        ⚠ Official Warning Notice
    </div>

    {{-- Body --}}
    <div class="body">
        <p class="greeting">Dear {{ $lease->tenant->name ?? 'Tenant' }},</p>

        <p>We hope you are well. This email serves as an <strong>official warning</strong> regarding your tenancy at
        <strong>{{ $lease->property->name ?? 'the property' }}{{ $lease->unit ? ', Unit ' . $lease->unit->unit_number : '' }}</strong>.</p>

        <p>Our records indicate that there has been an issue requiring your <strong>immediate attention</strong>:</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Property:</span>
                <span class="info-value">{{ $lease->property->name ?? '—' }}{{ $lease->unit ? ', Unit ' . $lease->unit->unit_number : '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Reason for Notice:</span>
                <span class="info-value"><strong>{{ $reason }}</strong></span>
            </div>
            @if($comments)
            <div class="info-row">
                <span class="info-label">Additional Details:</span>
                <span class="info-value">{{ $comments }}</span>
            </div>
            @endif
        </div>

        <p>We kindly request that you address this matter within the required timeframe and ensure compliance with
        the terms and conditions of your lease agreement.</p>

        <div class="warning-text">
            Failure to resolve this issue may result in further action, including <strong>lease termination</strong>
            or other measures permitted under the lease agreement.
        </div>

        <p>If you believe this notice has been issued in error or would like to discuss the matter, please contact
        the landlord or property management office as soon as possible.</p>

        <p>Thank you for your cooperation.</p>
    </div>

    {{-- Footer / Signature --}}
    <div class="footer">
        <div class="signature">
            <span>Kind Regards,</span><br><br>
            <strong>{{ $lease->landlord->name ?? 'Your Landlord' }}</strong>
            <span>REMIS Property Management System</span>
        </div>

        <div class="footer-note">
            This is an official notice generated by REMIS. Please do not reply to this email directly.
            Contact your landlord for any queries.
        </div>
    </div>

</div>
</body>
</html>
