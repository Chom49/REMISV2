<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Lease Contract – {{ $lease->property->name ?? 'REMIS' }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9.5pt;
    color: #111827;
    background: #ffffff;
    line-height: 1.5;
}

/* ─── HEADER ─── */
.page-header { padding: 18px 32px 14px; }

.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.brand { display: flex; align-items: center; gap: 10px; }

.brand-icon {
    width: 42px; height: 42px;
    background: #d8f3dc;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
}

.brand-name {
    font-size: 24pt; font-weight: 700;
    color: #1b4332; letter-spacing: 2px; line-height: 1;
}

.header-info { display: flex; gap: 28px; align-items: flex-start; }

.header-info-block { display: flex; align-items: flex-start; gap: 7px; }

.header-info-icon { width: 22px; height: 22px; margin-top: 2px; flex-shrink: 0; }

.header-info-label {
    font-size: 7pt; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1px;
    color: #6b7280;
}
.header-info-value { font-size: 11pt; font-weight: 700; color: #111827; }

.header-divider { height: 2.5px; background: #40916c; }

/* ─── BODY ─── */
.body-wrap { padding: 16px 32px 16px; }

/* ─── PARTY CARDS ─── */
.parties-row { display: flex; gap: 14px; margin-bottom: 16px; }

.party-card {
    flex: 1;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
}

.party-head {
    background: #40916c;
    padding: 7px 12px;
    display: flex; align-items: center; gap: 7px;
}
.party-head-label {
    font-size: 8pt; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.8px;
    color: #ffffff;
}

.party-table { width: 100%; border-collapse: collapse; }
.party-table td {
    padding: 6px 12px; font-size: 8.5pt;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}
.party-table tr:last-child td { border-bottom: none; }
.td-label { color: #6b7280; width: 60px; }
.td-value { font-weight: 600; color: #111827; }

/* ─── SECTION HEADER ─── */
.sec-head {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 8px; margin-top: 14px;
}
.sec-icon {
    width: 26px; height: 26px;
    background: #40916c;
    border-radius: 5px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sec-title {
    font-size: 9.5pt; font-weight: 700;
    text-transform: uppercase;
    color: #1b4332; letter-spacing: 0.3px;
}

/* ─── SECTION CARD ─── */
.sec-card {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 14px;
}

/* Full-width row (property address) */
.card-full-row {
    padding: 8px 14px;
    border-bottom: 1px solid #e5e7eb;
    display: flex; align-items: baseline; gap: 8px;
}
.cfr-label { font-size: 8.5pt; font-weight: 700; color: #374151; min-width: 110px; flex-shrink: 0; }
.cfr-value { font-size: 8.5pt; font-weight: 600; color: #111827; }

/* 4-column table */
.col4 { width: 100%; border-collapse: collapse; }
.col4 td {
    width: 25%;
    padding: 10px 14px;
    vertical-align: top;
    border-right: 1px solid #e5e7eb;
}
.col4 td:last-child { border-right: none; }
.col-head { font-size: 7.5pt; font-weight: 700; color: #374151; margin-bottom: 4px; }
.col-val  { font-size: 9pt; font-weight: 600; color: #111827; line-height: 1.4; }
.col-sub  { font-size: 7.5pt; color: #6b7280; margin-top: 2px; }

/* ─── TERMS ─── */
.terms-card {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 14px;
}
.terms-2col { display: flex; gap: 22px; }
.terms-col { flex: 1; }
.term-row {
    display: flex; gap: 7px;
    margin-bottom: 7px;
    font-size: 8.5pt; color: #374151;
    line-height: 1.45;
    align-items: flex-start;
}
.term-num { font-weight: 700; color: #1b4332; min-width: 14px; flex-shrink: 0; }

/* ─── SIGNATURES ─── */
.sig-card {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 16px 18px;
    margin-bottom: 6px;
}
.sig-intro { font-size: 8.5pt; color: #374151; margin-bottom: 18px; }
.sig-row { display: flex; gap: 50px; }
.sig-block { flex: 1; }
.sig-role-label {
    font-size: 7.5pt; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    color: #40916c; margin-bottom: 8px;
}
.sig-cursive {
    font-size: 16pt; font-style: italic;
    color: #111827; height: 30px; line-height: 1;
}
.sig-underline {
    border-bottom: 1.5px solid #374151;
    margin-top: 4px; margin-bottom: 5px;
}
.sig-printed-name { font-size: 8.5pt; font-weight: 600; color: #111827; margin-bottom: 4px; }
.sig-date-field { font-size: 8pt; color: #6b7280; }

/* ─── FOOTER ─── */
.page-footer {
    background: #1b4332;
    padding: 10px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 18px;
}
.footer-item {
    display: flex; align-items: center; gap: 6px;
    font-size: 8.5pt; color: #ffffff;
}
</style>
</head>
<body>

@php
    $today      = now();
    $daysLeft   = $today->copy()->startOfDay()->diffInDays($lease->end_date, false);
    $isOverdue  = $lease->status === 'active' && $daysLeft < 0;
    $months     = $lease->start_date->diffInMonths($lease->end_date);
    $contractNo = 'REMIS-' . $lease->start_date->format('Y') . '-' . str_pad($lease->id, 5, '0', STR_PAD_LEFT);

    // Payment due day ordinal
    $pd = $lease->payment_day ?? 1;
    $pdSuffix = ($pd % 100 >= 11 && $pd % 100 <= 13) ? 'th'
        : match($pd % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
    $pdStr = $pd . $pdSuffix;

    // Property full address
    $addrParts = array_filter([
        $lease->unit?->unit_number ? 'Unit ' . $lease->unit->unit_number : null,
        $lease->property->address ?? null,
        $lease->property->city    ?? null,
        $lease->property->county  ?? null,
    ]);
    $fullAddress = implode(', ', $addrParts) ?: '—';

    // Floor and lot (graceful fallback if fields don't exist)
    $unitFloor   = $lease->unit?->floor    ?? '—';
    $propertyLot = $lease->property?->lot_block ?? '—';

    // Default terms
    $defaultTerms = [
        'The Tenant shall pay the rent on or before the due date stated above.',
        'The Tenant shall use the premises solely for residential purposes.',
        'The Tenant shall maintain the property in good condition and shall be responsible for any damages caused by negligence or misuse.',
        'Subletting is not allowed without the written consent of the Landlord.',
        'The Tenant shall not make any alterations or improvements without prior written approval.',
        'Utility bills (electricity, water, internet, etc.) shall be paid by the Tenant unless otherwise agreed.',
        'The Landlord may inspect the property with reasonable notice.',
        'This agreement may be terminated in accordance with the terms set forth by law.',
    ];
@endphp

{{-- ════════════════ HEADER ════════════════ --}}
<div class="page-header">
    <div class="header-row">

        {{-- Brand --}}
        <div class="brand">
            <div class="brand-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                    <path d="M3 10.5L12 3L21 10.5V20C21 20.55 20.55 21 20 21H15V15H9V21H4C3.45 21 3 20.55 3 20V10.5Z" fill="#40916c"/>
                    <rect x="9" y="15" width="6" height="6" fill="#1b4332"/>
                </svg>
            </div>
            <div class="brand-name">REMIS</div>
        </div>

        {{-- Date + Contract --}}
        <div class="header-info">
            <div class="header-info-block">
                <div class="header-info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#40916c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                        <line x1="3"  y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <div class="header-info-label">Date of Agreement</div>
                    <div class="header-info-value">{{ $today->format('M d, Y') }}</div>
                </div>
            </div>
            <div class="header-info-block">
                <div class="header-info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#40916c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </div>
                <div>
                    <div class="header-info-label">Contract No.</div>
                    <div class="header-info-value">{{ $contractNo }}</div>
                </div>
            </div>
        </div>

    </div>
    <div class="header-divider"></div>
</div>

{{-- ════════════════ BODY ════════════════ --}}
<div class="body-wrap">

    {{-- ── LANDLORD / TENANT CARDS ── --}}
    <div class="parties-row">

        <div class="party-card">
            <div class="party-head">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span class="party-head-label">Landlord Information</span>
            </div>
            <table class="party-table">
                <tr>
                    <td class="td-label">Name</td>
                    <td class="td-value">{{ $lease->landlord->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Phone</td>
                    <td class="td-value">{{ $lease->landlord->phone ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Email</td>
                    <td class="td-value">{{ $lease->landlord->email ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="party-card">
            <div class="party-head">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span class="party-head-label">Tenant Information</span>
            </div>
            @if($lease->tenant)
            <table class="party-table">
                <tr>
                    <td class="td-label">Name</td>
                    <td class="td-value">{{ $lease->tenant->name }}</td>
                </tr>
                <tr>
                    <td class="td-label">Phone</td>
                    <td class="td-value">{{ $lease->tenant->phone ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="td-label">Email</td>
                    <td class="td-value">{{ $lease->tenant->email }}</td>
                </tr>
            </table>
            @else
            <div style="padding:16px 12px;color:#9ca3af;font-size:8.5pt;">Tenant not yet assigned</div>
            @endif
        </div>

    </div>

    {{-- ── 1. PROPERTY INFORMATION ── --}}
    <div class="sec-head" style="margin-top:4px;">
        <div class="sec-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 10.5L12 3L21 10.5V20a1 1 0 01-1 1H5a1 1 0 01-1-1V10.5z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </div>
        <span class="sec-title">1. Property Information</span>
    </div>
    <div class="sec-card">
        <div class="card-full-row">
            <span class="cfr-label">Property Address:</span>
            <span class="cfr-value">{{ $fullAddress }}</span>
        </div>
        <table class="col4">
            <tr>
                <td>
                    <div class="col-head">Property Type</div>
                    <div class="col-val">{{ ($lease->property?->property_category === 'multi') ? 'Multi-Unit' : 'Residential Unit' }}</div>
                </td>
                <td>
                    <div class="col-head">Unit / Room No.</div>
                    <div class="col-val">{{ $lease->unit?->unit_number ?? '—' }}</div>
                </td>
                <td>
                    <div class="col-head">Floor / Level</div>
                    <div class="col-val">{{ $unitFloor }}</div>
                </td>
                <td>
                    <div class="col-head">Lot / Block (if any)</div>
                    <div class="col-val">{{ $propertyLot }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── 2. LEASE TERMS ── --}}
    <div class="sec-head">
        <div class="sec-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8"  y1="2" x2="8"  y2="6"/>
                <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <span class="sec-title">2. Lease Terms</span>
    </div>
    <div class="sec-card">
        <table class="col4">
            <tr>
                <td>
                    <div class="col-head">Lease Start Date</div>
                    <div class="col-val">{{ $lease->start_date->format('M d, Y') }}</div>
                </td>
                <td>
                    <div class="col-head">Lease End Date</div>
                    <div class="col-val" style="{{ $isOverdue ? 'color:#991b1b;' : '' }}">{{ $lease->end_date->format('M d, Y') }}</div>
                </td>
                <td>
                    <div class="col-head">Lease Duration</div>
                    <div class="col-val">{{ $months }} Months</div>
                </td>
                <td>
                    <div class="col-head">Renewal Option</div>
                    <div class="col-val" style="font-size:8.5pt;font-weight:500;">May be renewed upon mutual agreement</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── 3. RENTAL DETAILS ── --}}
    <div class="sec-head">
        <div class="sec-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 7v2m0 6v2"/>
                <path d="M9.5 9.5C9.5 8.12 10.62 7 12 7s2.5 1.12 2.5 2.5c0 1.65-2.5 2.5-2.5 4.5"/>
            </svg>
        </div>
        <span class="sec-title">3. Rental Details</span>
    </div>
    <div class="sec-card">
        <table class="col4">
            <tr>
                <td>
                    <div class="col-head">Monthly Rent</div>
                    <div class="col-val" style="color:#1b4332;">Tshs {{ number_format($lease->monthly_rent, 0) }}</div>
                </td>
                <td>
                    <div class="col-head">Due Date</div>
                    <div class="col-val">Every {{ $pdStr }} of the month</div>
                </td>
                <td>
                    <div class="col-head">Payment Method</div>
                    <div class="col-val">Bank Transfer / Cash / Check</div>
                </td>
                <td>
                    <div class="col-head">Security Deposit:</div>
                    <div class="col-val" style="color:#1b4332;">Tshs {{ number_format($lease->security_deposit ?? 0, 0) }}</div>
                    <div class="col-sub">(Refundable subject to terms)</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── 4. TERMS AND CONDITIONS ── --}}
    <div class="sec-head">
        <div class="sec-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
                <line x1="9"  y1="12" x2="15" y2="12"/>
                <line x1="9"  y1="16" x2="13" y2="16"/>
            </svg>
        </div>
        <span class="sec-title">4. Terms and Conditions</span>
    </div>
    <div class="terms-card">
        @if($lease->lease_terms)
            <div style="font-size:8.5pt;color:#374151;white-space:pre-wrap;line-height:1.5;">{{ $lease->lease_terms }}</div>
        @else
            <div class="terms-2col">
                <div class="terms-col">
                    @foreach(array_slice($defaultTerms, 0, 4) as $idx => $term)
                    <div class="term-row">
                        <span class="term-num">{{ $idx + 1 }}.</span>
                        <span>{{ $term }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="terms-col">
                    @foreach(array_slice($defaultTerms, 4, 4) as $idx => $term)
                    <div class="term-row">
                        <span class="term-num">{{ $idx + 5 }}.</span>
                        <span>{{ $term }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ── 5. SIGNATURES ── --}}
    <div class="sec-head">
        <div class="sec-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"/>
                <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
        </div>
        <span class="sec-title">5. Signatures</span>
    </div>
    <div class="sig-card">
        <div class="sig-intro">By signing below, both parties agree to the terms and conditions outlined in this agreement.</div>
        <div class="sig-row">

            {{-- Landlord --}}
            <div class="sig-block">
                <div class="sig-role-label">Landlord</div>
                <div class="sig-cursive">{{ $lease->landlord->name ?? 'Landlord' }}</div>
                <div class="sig-underline"></div>
                <div class="sig-printed-name">{{ $lease->landlord->name ?? '—' }}</div>
                <div class="sig-date-field">Date: ____________________</div>
            </div>

            {{-- Tenant --}}
            <div class="sig-block">
                <div class="sig-role-label">Tenant</div>
                <div class="sig-cursive">{{ $lease->tenant?->name ?? 'Tenant' }}</div>
                <div class="sig-underline"></div>
                <div class="sig-printed-name">{{ $lease->tenant?->name ?? '—' }}</div>
                <div class="sig-date-field">Date: ____________________</div>
            </div>

        </div>
    </div>

</div>

{{-- ════════════════ FOOTER ════════════════ --}}
<div class="page-footer">
    <div class="footer-item">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.14 2.18A2 2 0 012.11 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
        </svg>
        +255 756 301 304
    </div>
    <div class="footer-item">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
        </svg>
        info@remis.com
    </div>
    <div class="footer-item">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
        </svg>
        www.remis.com
    </div>
</div>

</body>
</html>
