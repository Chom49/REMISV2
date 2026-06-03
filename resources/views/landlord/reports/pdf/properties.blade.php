<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Properties Report</title>
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
.badge-active   { color: #15803d; font-weight: bold; }
.badge-inactive { color: #b45309; font-weight: bold; }
.text-center { text-align: center; }
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

    $totalUnits    = $properties->sum(fn($p) => $p->units->count() ?: 1);
    $totalOccupied = $properties->sum(fn($p) => $p->isMultiUnit()
        ? $p->units->where('status', 'occupied')->count()
        : ($p->status === 'occupied' ? 1 : 0));
    $totalVacant   = $totalUnits - $totalOccupied;
@endphp

{{-- HEADER --}}
<div class="header">
    @if($logoBase64)
        <img src="data:image/png;base64,{{ $logoBase64 }}" style="height:62px; display:block; margin:0 auto;" alt="REMIS">
    @else
        <div style="font-size:22pt; font-weight:bold; letter-spacing:8px; color:#1b4332;">REMIS</div>
    @endif
    <div class="report-title">Properties Report</div>
    <div class="report-meta">Generated on {{ now()->format('d F Y, H:i') }}</div>
</div>

<hr class="divider">

{{-- INTRO --}}
<div class="intro">
    This report provides a full directory of all properties registered under your account, including their location,
    type, category, unit count, occupancy figures, and current status. Use this report to assess your
    property portfolio, track occupancy rates, and plan maintenance or marketing activities for vacant units.
</div>

{{-- SUMMARY BAR --}}
<table class="summary-bar">
    <tr>
        <td style="width:25%"><span class="sum-val">{{ $properties->count() }}</span><span class="sum-lbl">Total Properties</span></td>
        <td style="width:25%"><span class="sum-val">{{ $totalUnits }}</span><span class="sum-lbl">Total Units</span></td>
        <td style="width:25%"><span class="sum-val badge-active">{{ $totalOccupied }}</span><span class="sum-lbl">Occupied</span></td>
        <td style="width:25%"><span class="sum-val badge-inactive">{{ $totalVacant }}</span><span class="sum-lbl">Vacant</span></td>
    </tr>
</table>

{{-- DATA TABLE --}}
@if($properties->isEmpty())
    <p style="text-align:center; color:#888; padding:20px;">No properties found.</p>
@else
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:19%">Property Name</th>
            <th style="width:28%">Address</th>
            <th style="width:9%">Type</th>
            <th style="width:9%">Category</th>
            <th style="width:8%" class="text-center">Units</th>
            <th style="width:8%" class="text-center">Occupied</th>
            <th style="width:7%" class="text-center">Vacant</th>
            <th style="width:6%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($properties as $i => $prop)
        @php
            $isMulti   = $prop->isMultiUnit();
            $unitCount = $isMulti ? $prop->units->count() : 1;
            $occupied  = $isMulti ? $prop->units->where('status', 'occupied')->count()
                                  : ($prop->status === 'occupied' ? 1 : 0);
            $vacant    = $unitCount - $occupied;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $prop->name }}</td>
            <td style="font-size:7.5pt;">{{ $prop->address ?? '—' }}</td>
            <td>{{ ucfirst($prop->type ?? '—') }}</td>
            <td>{{ $isMulti ? 'Multi-unit' : 'Single' }}</td>
            <td class="text-center">{{ $unitCount }}</td>
            <td class="text-center"><span class="badge-active">{{ $occupied }}</span></td>
            <td class="text-center"><span class="badge-inactive">{{ $vacant }}</span></td>
            <td>
                @if($prop->status === 'occupied' || $occupied > 0)
                    <span class="badge-active">Active</span>
                @else
                    <span class="badge-inactive">Vacant</span>
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
