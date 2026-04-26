@extends('layouts.landlord')

@section('title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

<div class="space-y-6">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Greeting --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Hello {{ explode(' ', $landlord->name)[0] }}</h1>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Rent Received --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs text-gray-400 font-medium">Rent Received</p>
                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xl font-bold text-gray-900">Tshs{{ number_format($stats['total_revenue'], 0) }}</p>
        </div>

        {{-- Upcoming Payments --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs text-gray-400 font-medium">Upcoming Payments</p>
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-xl font-bold text-gray-900">Tshs{{ number_format($stats['upcoming_rent'], 0) }}</p>
        </div>

        {{-- Rent Overdue --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs text-gray-400 font-medium">Rent Overdue</p>
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
            </div>
            <p class="text-xl font-bold text-gray-900">Tshs{{ number_format($stats['overdue_amount'], 0) }}</p>
        </div>

        {{-- Properties --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs text-gray-400 font-medium">Properties</p>
                <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
            </div>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total_properties'] }}</p>
        </div>
    </div>

    {{-- ===== CHARTS ROW ===== --}}
    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Income vs Expenses Chart (2/3) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Income vs Expenses</h2>
                <span class="text-xs text-gray-400 bg-gray-50 px-3 py-1 rounded-lg">Last 6 months</span>
            </div>
            <div class="relative h-48">
                <canvas id="incomeExpensesChart"></canvas>
            </div>
            <div class="flex items-center gap-5 mt-3">
                <span class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="w-3 h-0.5 bg-primary-500 inline-block rounded"></span> Income
                </span>
                <span class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="w-3 h-0.5 bg-red-400 inline-block rounded"></span> Expenses
                </span>
            </div>
        </div>

        {{-- Rent Collection Donut (1/3) --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Rent Collection</h2>
            <div class="flex justify-center mb-4">
                <div class="relative w-36 h-36">
                    <canvas id="collectionChart"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-lg font-bold text-gray-900">{{ $stats['collection_rate'] }}%</span>
                    </div>
                </div>
            </div>
            <div class="space-y-2 mt-auto">
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-primary-500 inline-block"></span>
                        Collected
                    </span>
                    <span class="font-semibold text-gray-800">{{ $stats['collected_units'] }} units</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span>
                        Pending
                    </span>
                    <span class="font-semibold text-gray-800">{{ $stats['pending_units'] }} unit</span>
                </div>
                <div class="flex items-center justify-between text-sm border-t border-gray-100 pt-2 mt-2">
                    <span class="text-gray-500">Collection Rate</span>
                    <span class="font-bold text-primary-600">{{ $stats['collection_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PROPERTIES + MAINTENANCE ROW ===== --}}
    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Properties List --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Properties</h2>
                <a href="{{ route('landlord.properties.index') }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">View all</a>
            </div>

            @if($properties->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-400 text-sm">No properties yet.</p>
                    <a href="{{ route('landlord.properties.create') }}" class="mt-3 inline-block text-xs text-primary-600 font-medium hover:underline">Add your first property</a>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($properties->take(3) as $property)
                        <a href="{{ route('landlord.properties.show', $property) }}"
                           class="flex items-center gap-3 py-2.5 px-3 rounded-xl hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-full bg-primary-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $property->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Maintenance Requests --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Maintenance Requests</h2>
                <a href="#" class="text-xs text-primary-600 hover:text-primary-700 font-medium">View all</a>
            </div>

            @if($maintenanceRequests->isEmpty())
                <p class="text-gray-400 text-sm text-center py-8">No open maintenance requests.</p>
            @else
                <div class="space-y-2">
                    @foreach($maintenanceRequests as $req)
                        <div class="flex items-center justify-between py-2.5 px-3 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-orange-50 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-700 font-medium">{{ $req->property->name }}</p>
                                    <p class="text-xs text-gray-400">{{ Str::limit($req->title, 40) }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0
                                {{ $req->priority === 'high' || $req->priority === 'urgent' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($req->priority) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function() {
    const months = @json($chartData['months']);
    const income  = @json($chartData['income']);
    const expenses = @json($chartData['expenses']);

    // Income vs Expenses area chart
    new Chart(document.getElementById('incomeExpensesChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Income',
                    data: income,
                    borderColor: '#40916c',
                    backgroundColor: 'rgba(64,145,108,0.15)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                },
                {
                    label: 'Expenses',
                    data: expenses,
                    borderColor: '#f87171',
                    backgroundColor: 'rgba(248,113,113,0.10)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip: { callbacks: {
                label: ctx => 'Tshs ' + ctx.parsed.y.toLocaleString()
            }}},
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
                y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af',
                    callback: v => 'Tshs ' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) }}
            }
        }
    });

    // Rent collection donut
    const collected = {{ $stats['collected_units'] }};
    const pending   = {{ $stats['pending_units'] }};
    new Chart(document.getElementById('collectionChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [collected, pending],
                backgroundColor: ['#40916c', '#fbbf24'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });
})();
</script>
@endpush
