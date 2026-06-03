<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Overdue Rent Payments Report</title>
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
    color: #991b1b;
    margin: 8px 0 3px;
    letter-spacing: 1px;
}
.report-meta { font-size: 8pt; color: #555; margin-bottom: 12px; }
.divider { border: none; border-top: 2px solid #991b1b; margin-bottom: 12px; }
.intro {
    font-size: 8.5pt;
    color: #333;
    text-align: justify;
    margin-bottom: 16px;
    line-height: 1.65;
    padding: 9px 12px;
    background: #fff5f5;
    border-left: 3.5px solid #991b1b;
}
.summary-bar {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
}
.summary-bar td {
    text-align: center;
    padding: 8px 4px;
    background: #fff8f8;
    border: 1px solid #f5c6c6;
}
.sum-val { font-size: 13pt; font-weight: bold; color: #991b1b; display: block; }
.sum-lbl { font-size: 7.5pt; color: #555; }
table.data-table {
    width: 100%;
    border-collapse: collapse;
}
table.data-table thead tr {
    background: #991b1b;
    color: #fff;
}
table.data-table thead th {
    padding: 7px 7px;
    text-align: left;
    font-size: 8pt;
    font-weight: bold;
    white-space: nowrap;
}
table.data-table tbody tr:nth-child(even) { background: #fff8f8; }
table.data-table tbody tr:nth-child(odd)  { background: #fff; }
table.data-table tbody td {
    padding: 5.5px 7px;
    font-size: 8pt;
    border-bottom: 0.5px solid #f0dede;
    vertical-align: top;
}
.days-hi { color: #dc2626; font-weight: bold; }
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

    $totalOverdue = $payments->sum('amount');
    $maxDays      = $payments->map(fn($p) => now()->diffInDays($p->due_date))->max() ?? 0;
@endphp

{{-- HEADER --}}
<div class="header">
    @if($logoBase64)
        <img src="data:image/png;base64,{{ $logoBase64 }}" style="height:62px; display:block; margin:0 auto;" alt="REMIS">
    @else
        <div style="font-size:22pt; font-weight:bold; letter-spacing:8px; color:#1b4332;">REMIS</div>
    @endif
    <div class="report-title">Overdue Rent Payments Report</div>
    <div class="report-meta">Generated on {{ now()->format('d F Y, H:i') }}</div>
</div>

<hr class="divider">

{{-- INTRO --}}
<div class="intro">
    This report lists all rent payments that are currently overdue or past their due date without confirmation of payment.
    It includes the tenant name, property, unit, original due date, number of days overdue, and the outstanding amount.
    Immediate action is recommended for all listed entries to recover outstanding balances and enforce lease obligations.
</div>

{{-- SUMMARY BAR --}}
<table class="summary-bar">
    <tr>
        <td style="width:34%"><span class="sum-val">{{ $payments->count() }}</span><span class="sum-lbl">Overdue Records</span></td>
        <td style="width:33%"><span class="sum-val">Tshs {{ number_format($totalOverdue, 0) }}</span><span class="sum-lbl">Total Outstanding</span></td>
        <td style="width:33%"><span class="sum-val">{{ $maxDays }} days</span><span class="sum-lbl">Longest Overdue</span></td>
    </tr>
</table>

{{-- DATA TABLE --}}
@if($payments->isEmpty())
    <p style="text-align:center; color:#16a34a; padding:20px; font-weight:bold;">No overdue payments. All records are up to date.</p>
@else
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:18%">Tenant</th>
            <th style="width:20%">Property</th>
            <th style="width:8%">Unit</th>
            <th style="width:12%">Due Date</th>
            <th style="width:13%">Days Overdue</th>
            <th style="width:13%" class="text-right">Amount (Tshs)</th>
            <th style="width:12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $i => $p)
        @php $daysOverdue = (int) now()->startOfDay()->diffInDays($p->due_date); @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $p->tenant->name ?? '—' }}</td>
            <td>{{ $p->lease->property->name ?? '—' }}</td>
            <td>{{ $p->lease->unit->unit_number ?? '—' }}</td>
            <td>{{ $p->due_date?->format('d/m/Y') ?? '—' }}</td>
            <td><span class="days-hi">{{ $daysOverdue }} days</span></td>
            <td class="text-right">{{ number_format($p->amount, 0) }}</td>
            <td><span class="days-hi">Overdue</span></td>
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
