<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Tenants Report</title>
<style>
@page {
    size: A4 portrait;
    margin-top: 16mm;
    margin-bottom: 10mm;
    margin-left: 28mm;
    margin-right: 28mm;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 8.5pt;
    color: #111;
    background: #fff;
    line-height: 1.5;
    padding-left: 10mm;
    padding-right: 10mm;
}
.header { text-align: center; margin-top: 18mm; margin-bottom: 14px; }
.report-title {
    font-size: 15pt;
    font-weight: bold;
    color: #1b4332;
    margin: 8px 0 3px;
    letter-spacing: 1px;
}
.report-meta { font-size: 8pt; color: #555; margin-bottom: 12px; }
.divider { border: none; border-top: 2px solid #1b4332; margin-bottom: 12px; }
.intro {
    font-size: 8.5pt;
    color: #333;
    text-align: justify;
    margin-bottom: 16px;
    line-height: 1.65;
    padding: 9px 12px;
    background: #f0faf5;
    border-left: 3.5px solid #1b4332;
}
.summary-bar {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
}
.summary-bar td {
    text-align: center;
    padding: 8px 4px;
    background: #f8fbf9;
    border: 1px solid #c8e6d8;
}
.sum-val { font-size: 13pt; font-weight: bold; color: #1b4332; display: block; }
.sum-lbl { font-size: 7.5pt; color: #555; }
table.data-table {
    width: 100%;
    border-collapse: collapse;
}
table.data-table thead tr {
    background: #1b4332;
    color: #fff;
}
table.data-table thead th {
    padding: 7px 7px;
    text-align: left;
    font-size: 8pt;
    font-weight: bold;
    white-space: nowrap;
}
table.data-table tbody tr:nth-child(even) { background: #f5fbf8; }
table.data-table tbody tr:nth-child(odd)  { background: #fff; }
table.data-table tbody td {
    padding: 5.5px 7px;
    font-size: 8pt;
    border-bottom: 0.5px solid #dde8e2;
    vertical-align: top;
}
.badge-active      { color: #15803d; font-weight: bold; }
.badge-terminated  { color: #dc2626; font-weight: bold; }
.badge-expired     { color: #b45309; font-weight: bold; }
.text-right { text-align: right; }
.footer {
    margin-top: 18px;
    font-size: 7.5pt;
    color: #888;
    text-align: center;
    border-top: 1px solid #dde;
    padding-top: 7px;
}
</style>
</head>
<body>

@php
    $logoPath   = public_path('images/signIn/logo_transparent.png');
    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

    $activeCount     = 0;
    $terminatedCount = 0;
    foreach ($tenants as $t) {
        $lease = $t->leasesAsTenant->first();
        if ($lease) {
            if ($lease->status === 'active') $activeCount++;
            elseif ($lease->status === 'terminated') $terminatedCount++;
        }
    }
@endphp

{{-- HEADER --}}
<div class="header">
    @if($logoBase64)
        <img src="data:image/png;base64,{{ $logoBase64 }}" style="height:62px; display:block; margin:0 auto;" alt="REMIS">
    @else
        <div style="font-size:22pt; font-weight:bold; letter-spacing:8px; color:#1b4332;">REMIS</div>
    @endif
    <div class="report-title">Tenants Report</div>
    <div class="report-meta">Generated on {{ now()->format('d F Y, H:i') }}</div>
</div>

<hr class="divider">

{{-- INTRO --}}
<div class="intro">
    This report presents a complete directory of all tenants registered under your account, along with their
    contact details, assigned property and unit, current lease status, monthly rent, and lease period.
    Use this report for tenant management, lease renewal planning, and occupancy monitoring.
</div>

{{-- SUMMARY BAR --}}
<table class="summary-bar">
    <tr>
        <td style="width:34%"><span class="sum-val">{{ $tenants->count() }}</span><span class="sum-lbl">Total Tenants</span></td>
        <td style="width:33%"><span class="sum-val badge-active">{{ $activeCount }}</span><span class="sum-lbl">Active Leases</span></td>
        <td style="width:33%"><span class="sum-val badge-terminated">{{ $terminatedCount }}</span><span class="sum-lbl">Terminated Leases</span></td>
    </tr>
</table>

{{-- DATA TABLE --}}
@if($tenants->isEmpty())
    <p style="text-align:center; color:#888; padding:20px;">No tenants found.</p>
@else
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:17%">Name</th>
            <th style="width:18%">Email</th>
            <th style="width:11%">Phone</th>
            <th style="width:16%">Property</th>
            <th style="width:7%">Unit</th>
            <th style="width:9%">Lease End</th>
            <th style="width:11%" class="text-right">Rent (Tshs)</th>
            <th style="width:7%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tenants as $i => $t)
        @php $lease = $t->leasesAsTenant->first(); @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $t->name }}</td>
            <td style="font-size:7.5pt;">{{ $t->email }}</td>
            <td>{{ $t->phone ?? '—' }}</td>
            <td>{{ $lease?->property->name ?? '—' }}</td>
            <td>{{ $lease?->unit->unit_number ?? '—' }}</td>
            <td>{{ $lease?->end_date?->format('d/m/Y') ?? '—' }}</td>
            <td class="text-right">{{ $lease ? number_format($lease->monthly_rent, 0) : '—' }}</td>
            <td>
                @if(!$lease)
                    <span style="color:#888;">No lease</span>
                @elseif($lease->status === 'active')
                    <span class="badge-active">Active</span>
                @elseif($lease->status === 'terminated')
                    <span class="badge-terminated">Terminated</span>
                @else
                    <span class="badge-expired">{{ ucfirst($lease->status) }}</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    REMIS &mdash; Rental Management Information System &nbsp;|&nbsp; Confidential &nbsp;|&nbsp; Page 1
</div>

</body>
</html>
