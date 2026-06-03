<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Rent Payments Report</title>
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
.badge-paid    { color: #15803d; font-weight: bold; }
.badge-overdue { color: #dc2626; font-weight: bold; }
.badge-pending { color: #b45309; font-weight: bold; }
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

    $totalAmount  = $payments->sum('amount');
    $paidCount    = $payments->where('status', 'paid')->count();
    $overdueCount = $payments->whereIn('status', ['overdue'])->filter(fn($p) => $p->due_date < now())->count()
                 ?: $payments->where('status', 'overdue')->count();
    $pendingCount = $payments->where('status', 'pending')->count();
@endphp

{{-- HEADER --}}
<div class="header">
    @if($logoBase64)
        <img src="data:image/png;base64,{{ $logoBase64 }}" style="height:62px; display:block; margin:0 auto;" alt="REMIS">
    @else
        <div style="font-size:22pt; font-weight:bold; letter-spacing:8px; color:#1b4332;">REMIS</div>
    @endif
    <div class="report-title">Rent Payments Report</div>
    <div class="report-meta">Generated on {{ now()->format('d F Y, H:i') }}</div>
</div>

<hr class="divider">

{{-- INTRO --}}
<div class="intro">
    This report provides a comprehensive overview of all rent payment records associated with your properties.
    It includes payment status, due dates, paid dates, amounts, and references for each transaction.
    Use this report to monitor collection performance, identify outstanding balances, and maintain accurate financial records.
</div>

{{-- SUMMARY BAR --}}
<table class="summary-bar">
    <tr>
        <td style="width:25%"><span class="sum-val">{{ $payments->count() }}</span><span class="sum-lbl">Total Records</span></td>
        <td style="width:25%"><span class="sum-val badge-paid">{{ $paidCount }}</span><span class="sum-lbl">Paid</span></td>
        <td style="width:25%"><span class="sum-val badge-pending">{{ $pendingCount }}</span><span class="sum-lbl">Pending</span></td>
        <td style="width:25%"><span class="sum-val">Tshs {{ number_format($totalAmount, 0) }}</span><span class="sum-lbl">Total Amount</span></td>
    </tr>
</table>

{{-- DATA TABLE --}}
@if($payments->isEmpty())
    <p style="text-align:center; color:#888; padding:20px;">No payment records found.</p>
@else
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:17%">Tenant</th>
            <th style="width:18%">Property</th>
            <th style="width:8%">Unit</th>
            <th style="width:11%">Due Date</th>
            <th style="width:11%">Paid Date</th>
            <th style="width:13%" class="text-right">Amount (Tshs)</th>
            <th style="width:9%">Status</th>
            <th style="width:9%">Reference</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $i => $p)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $p->tenant->name ?? '—' }}</td>
            <td>{{ $p->lease->property->name ?? '—' }}</td>
            <td>{{ $p->lease->unit->unit_number ?? '—' }}</td>
            <td>{{ $p->due_date?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $p->paid_date?->format('d/m/Y') ?? '—' }}</td>
            <td class="text-right">{{ number_format($p->amount, 0) }}</td>
            <td>
                @if($p->status === 'paid')
                    <span class="badge-paid">Paid</span>
                @elseif($p->status === 'overdue')
                    <span class="badge-overdue">Overdue</span>
                @else
                    <span class="badge-pending">Pending</span>
                @endif
            </td>
            <td>{{ $p->reference ?? '—' }}</td>
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
