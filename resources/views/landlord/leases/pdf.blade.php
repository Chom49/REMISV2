<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Lease Agreement</title>
<style>
@page {
    margin-top: 16mm;
    margin-bottom: 5mm;
    margin-left: 28mm;
    margin-right: 28mm;
    size: A4 portrait;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 10pt;
    color: #111111;
    background: #ffffff;
    line-height: 1.58;
    padding-left: 10mm;
    padding-right: 10mm;
    padding-bottom: 0;
}

.header {
    text-align: center;
    margin-top: 18mm;
    margin-bottom: 14px;
}

.brand-txt {
    font-size: 13pt;
    font-weight: bold;
    letter-spacing: 6px;
    color: #1b4332;
    display: block;
    margin-top: 5px;
}

.intro {
    text-align: justify;
    margin-bottom: 12px;
    font-size: 10pt;
    line-height: 1.62;
}

.blank {
    display: inline-block;
    border-bottom: 1.5px solid #111111;
    vertical-align: bottom;
    line-height: 1.3;
}

.filled {
    display: inline-block;
    border-bottom: 1.5px solid #111111;
    vertical-align: bottom;
    line-height: 1.3;
    min-width: 40px;
}

.tc-heading {
    font-weight: bold;
    font-size: 10.5pt;
    margin-bottom: 7px;
    text-decoration: underline;
}

.term-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}

.term-n {
    width: 24px;
    vertical-align: top;
    padding: 0 4px 7px 0;
    white-space: nowrap;
    font-size: 10pt;
    line-height: 1.55;
}

.term-t {
    vertical-align: top;
    padding: 0 0 7px 0;
    font-size: 10pt;
    line-height: 1.55;
}

.sig-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.sig-left  { width: 50%; vertical-align: top; padding-right: 8px; }
.sig-right { width: 50%; vertical-align: top; padding-left: 8px; }

.sig-role {
    font-weight: bold;
    font-size: 10.5pt;
    display: block;
    margin-bottom: 9px;
}

.sig-field {
    font-size: 10pt;
    margin-bottom: 9px;
    line-height: 1.3;
}

.sig-line {
    display: inline-block;
    border-bottom: 1px solid #111111;
    vertical-align: bottom;
    margin-left: 4px;
}
</style>
</head>
<body>

@php
    $today = now();

    $agrDay = (int) $today->format('j');
    $agrSfx = ($agrDay % 100 >= 11 && $agrDay % 100 <= 13) ? 'th'
        : match($agrDay % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };

    $addrParts   = array_filter([$lease->property->address ?? null, $lease->property->city ?? null, $lease->property->county ?? null]);
    $fullAddress = implode(', ', $addrParts) ?: null;

    $unitNumber  = $lease->unit?->unit_number;
    $floorNumber = $lease->unit?->floor_number;
    $unitLabel   = $unitNumber ? 'Unit ' . $unitNumber . ($floorNumber ? ', Floor ' . $floorNumber : '') : null;

    $landlordName  = $lease->landlord->name ?? null;
    $tenantName    = $lease->tenant?->name  ?? null;
    $rentFormatted = 'Tshs ' . number_format($lease->monthly_rent, 0);

    $defaultTerms = [
        'The Tenant shall pay rent on time as agreed in this contract.',
        'The Tenant shall maintain the property in good condition and avoid any illegal or disruptive activities within the premises.',
        'The Tenant shall not sublease or transfer the property without written permission from the Landlord.',
        'The Landlord may inspect the property with prior notice where necessary.',
        'Failure to comply with the lease terms, including non-payment of rent or property misuse, may result in termination of the lease agreement.',
        'Upon expiration or termination of this lease, the Tenant agrees to vacate the premises peacefully unless a renewal agreement is signed.',
        'Both parties agree to comply with all applicable property and tenancy laws.',
        'The Tenant shall be responsible for any damage caused to the property beyond normal wear and tear and shall bear the cost of repairs or replacement where necessary.',
        'The Tenant shall keep the premises clean, hygienic, and free from activities that may cause nuisance or disturbance to neighbors.',
        'The Landlord reserves the right to terminate this agreement upon written notice if the Tenant repeatedly breaches the terms and conditions of this lease.',
    ];

    // Use custom terms only when the landlord has entered 7 or more numbered lines.
    // A short one-liner in lease_terms is a note, not a replacement for legal terms.
    $termsToShow = [];
    if ($lease->lease_terms) {
        foreach (preg_split('/\r?\n/', trim($lease->lease_terms)) as $line) {
            $line = trim($line);
            if ($line !== '') $termsToShow[] = preg_replace('/^\d+\.\s*/', '', $line);
        }
    }
    if (count($termsToShow) < 10) {
        $termsToShow = $defaultTerms;
    }
@endphp

{{-- ══ LOGO ══ --}}
<div class="header">
    @php
        $logoPath   = public_path('images/signIn/logo_transparent.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    @endphp
    @if($logoBase64)
        <img src="data:image/png;base64,{{ $logoBase64 }}"
             style="height:72px; display:block; margin:0 auto;" alt="REMIS">
    @else
        <span class="brand-txt" style="font-size:22pt; letter-spacing:8px;">REMIS</span>
    @endif
</div>

{{-- ══ INTRO PARAGRAPH ══ --}}
<div class="intro">
    This Lease Agreement is made and entered into on this
    @if($agrDay)<span class="filled">{{ $agrDay }}{{ $agrSfx }}</span>@else<span class="blank" style="width:28px;">&nbsp;</span>@endif
    day of
    @if($today)<span class="filled">{{ $today->format('F Y') }}</span>@else<span class="blank" style="width:80px;">&nbsp;</span>@endif
    between the Landlord,
    @if($landlordName)<span class="filled">&nbsp;{{ $landlordName }}&nbsp;</span>@else<span class="blank" style="width:130px;">&nbsp;</span>@endif,
    and the Tenant,
    @if($tenantName)<span class="filled">&nbsp;{{ $tenantName }}&nbsp;</span>@else<span class="blank" style="width:130px;">&nbsp;</span>@endif.
    The Landlord hereby agrees to lease the property located at
    @if($fullAddress)<span class="filled">&nbsp;{{ $fullAddress }}&nbsp;</span>@else<span class="blank" style="width:170px;">&nbsp;</span>@endif,
    including Unit/Space
    @if($unitLabel)<span class="filled">&nbsp;{{ $unitLabel }}&nbsp;</span>@else<span class="blank" style="width:100px;">&nbsp;</span>@endif,
    to the Tenant for residential/commercial purposes under the agreed rental terms and conditions.
    The lease shall commence on
    @if($lease->start_date)<span class="filled">&nbsp;{{ $lease->start_date->format('d F Y') }}&nbsp;</span>@else<span class="blank" style="width:100px;">&nbsp;</span>@endif
    and end on
    @if($lease->end_date)<span class="filled">&nbsp;{{ $lease->end_date->format('d F Y') }}&nbsp;</span>@else<span class="blank" style="width:100px;">&nbsp;</span>@endif.
    The Tenant agrees to pay a rent of
    @if($lease->monthly_rent)<span class="filled">&nbsp;{{ $rentFormatted }}&nbsp;</span>@else<span class="blank" style="width:100px;">&nbsp;</span>@endif
    on or before the agreed due date. By signing this agreement, both parties acknowledge
    and agree to comply with the responsibilities, obligations, and conditions outlined within this contract.
</div>

{{-- ══ TERMS AND CONDITIONS ══ --}}
<div class="tc-heading">Terms and Conditions</div>

<table class="term-table">
    @foreach($termsToShow as $idx => $term)
    <tr>
        <td class="term-n">{{ $idx + 1 }}.</td>
        <td class="term-t">{{ $term }}</td>
    </tr>
    @endforeach
</table>

{{-- ══ SIGNATURES ══ --}}
<table class="sig-table">
    <tr>
        <td class="sig-left">
            <span class="sig-role">Landlord</span>
        </td>
        <td class="sig-right">
            <span class="sig-role">Tenant</span>
        </td>
    </tr>
    <tr>
        <td class="sig-left">
            <div class="sig-field">Name: <span class="sig-line" style="width:148px;"></span></div>
        </td>
        <td class="sig-right">
            <div class="sig-field">Name: <span class="sig-line" style="width:148px;"></span></div>
        </td>
    </tr>
    <tr>
        <td class="sig-left">
            <div class="sig-field">Signature: <span class="sig-line" style="width:118px;"></span></div>
        </td>
        <td class="sig-right">
            <div class="sig-field">Signature: <span class="sig-line" style="width:118px;"></span></div>
        </td>
    </tr>
    <tr>
        <td class="sig-left">
            <div class="sig-field">Date: <span class="sig-line" style="width:140px;"></span></div>
        </td>
        <td class="sig-right">
            <div class="sig-field">Date: <span class="sig-line" style="width:140px;"></span></div>
        </td>
    </tr>
</table>


</body>
</html>
