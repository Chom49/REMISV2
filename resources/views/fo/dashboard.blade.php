@extends('layouts.fo')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">Financial overview for {{ now()->format('F Y') }}</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wide mb-2">Collected ({{ now()->format('M') }})</p>
            <p class="text-3xl font-bold text-emerald-600">{{ number_format($totalCollected, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">TZS this month</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-amber-500 uppercase tracking-wide mb-2">Pending</p>
            <p class="text-3xl font-bold text-amber-600">{{ $pendingCount }}</p>
            <p class="text-xs text-gray-400 mt-1">Awaiting payment</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-2">Overdue</p>
            <p class="text-3xl font-bold text-red-600">{{ $overdueCount }}</p>
            <p class="text-xs text-gray-400 mt-1">Past due date</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide mb-2">Verified by Me</p>
            <p class="text-3xl font-bold text-indigo-600">{{ $verifiedByMe }}</p>
            <p class="text-xs text-gray-400 mt-1">All time</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Income chart --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-700 mb-4">Monthly Collections (6 months)</h2>
            <div class="relative h-40">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>

        {{-- Upcoming --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-gray-700 mb-4">Due in 7 Days</h2>
            @forelse($upcomingPayments as $p)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ optional($p->tenant)->name ?? '–' }}</p>
                    <p class="text-xs text-gray-400">Due {{ optional($p->due_date)->format('d M') }}</p>
                </div>
                <span class="text-xs font-semibold {{ $p->status === 'overdue' ? 'text-red-600' : 'text-amber-600' }} flex-shrink-0 ml-2">
                    TZS {{ number_format($p->amount, 0) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No upcoming payments.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent transactions --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">Recent Transactions</h2>
            <a href="{{ route('fo.payments.index') }}" class="text-xs text-indigo-600 font-medium hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentTransactions as $p)
            <div class="px-6 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ optional($p->tenant)->name ?? '–' }}</p>
                    <p class="text-xs text-gray-400">{{ optional($p->lease->property)->name }} • {{ optional($p->paid_date)->format('d M Y') }}</p>
                </div>
                <span class="text-sm font-bold text-emerald-600">TZS {{ number_format($p->amount, 0) }}</span>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-sm text-gray-400">No transactions yet this month.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    var months = @json($chartMonths);
    var income = @json($chartIncome);

    function initChart() {
        var canvas = document.getElementById('incomeChart');
        if (!canvas || !window.Chart) return;
        new window.Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'TZS',
                    data: income,
                    backgroundColor: '#40916c',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: { callback: function(v) { return 'TZS ' + v.toLocaleString(); } },
                        grid: { color: '#f3f4f6' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // On SPA navigation window.Chart is already set; on first load wait for modules to finish
    if (window.Chart) {
        initChart();
    } else {
        window.addEventListener('load', initChart);
    }
})();
</script>
@endpush
