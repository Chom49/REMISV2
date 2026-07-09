@extends('layouts.fo')
@section('title', 'Reports')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Financial Reports</h1>
        <p class="text-sm text-gray-500 mt-0.5">Monthly collection summaries and payment history</p>
    </div>

    {{-- Monthly summary --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <h2 class="text-sm font-bold text-gray-700">Monthly Collections</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Month</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Payments</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Collected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                @forelse($monthly as $row)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-3 font-semibold text-gray-800">
                        {{ \Carbon\Carbon::createFromDate($row->y, $row->m, 1)->format('F Y') }}
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $row->cnt }}</td>
                    <td class="px-6 py-3 font-bold text-emerald-600">TZS {{ number_format($row->total, 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-400">No payment data yet.</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- All payments --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <h2 class="text-sm font-bold text-gray-700">All Payments</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Property</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Due</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                @forelse($allPayments as $p)
                @php
                $sc = ['paid'=>'bg-emerald-100 text-emerald-700',
                       'pending'=>'bg-amber-100 text-amber-700',
                       'overdue'=>'bg-red-100 text-red-700'][$p->status] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-3 font-medium text-gray-800">{{ optional($p->tenant)->name ?? '–' }}</td>
                    <td class="px-6 py-3 text-gray-600">
                        {{ optional(optional($p->lease)->property)->name ?? '–' }}
                        @if(optional(optional($p->lease)->unit)->unit_number)
                        <span class="text-gray-400">/ {{ $p->lease->unit->unit_number }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 font-semibold text-gray-800">TZS {{ number_format($p->amount, 0) }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ optional($p->due_date)->format('d M Y') }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ optional($p->paid_date)->format('d M Y') ?? '–' }}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sc }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-500 text-xs font-mono">{{ $p->reference ?? $p->nmb_receipt_number ?? '–' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-400">No payments found.</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($allPayments->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">
            {{ $allPayments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
