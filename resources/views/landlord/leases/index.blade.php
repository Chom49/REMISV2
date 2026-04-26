@extends('layouts.landlord')
@section('title', 'Lease Contracts')

@section('content')
<div class="space-y-6">

    {{-- ── Page header ─────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Lease Contracts</h1>
            <p class="text-sm text-gray-500 mt-0.5">All rental agreements across your properties</p>
        </div>
        {{-- Total badge --}}
        <span class="inline-flex items-center gap-1.5 bg-primary-50 text-primary-700 text-sm font-semibold px-4 py-2 rounded-full border border-primary-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            {{ $stats['total'] }} {{ Str::plural('Contract', $stats['total']) }}
        </span>
    </div>

    {{-- ── Summary cards ────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Active --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['active'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Running leases</p>
        </div>

        {{-- Expiring Soon --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Expiring</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['expiring_soon'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Within 30 days</p>
        </div>

        {{-- Overdue / Past end date --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Overdue</span>
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['expired'] }}</p>
            <p class="text-xs text-gray-400 mt-1">Past end date</p>
        </div>

        {{-- Total --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</span>
                <div class="w-9 h-9 rounded-xl bg-primary-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-1">All contracts</p>
        </div>
    </div>

    {{-- ── Filters + Search ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">

        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-gray-100">

            {{-- Status filter tabs --}}
            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1 text-sm font-medium">
                <button onclick="filterLeases('all')" id="tab-all"
                        class="px-3 py-1.5 rounded-lg bg-white text-gray-800 shadow-sm transition-all">All</button>
                <button onclick="filterLeases('active')" id="tab-active"
                        class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-800 transition-all">Active</button>
                <button onclick="filterLeases('expiring')" id="tab-expiring"
                        class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-800 transition-all">Expiring</button>
                <button onclick="filterLeases('overdue')" id="tab-overdue"
                        class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-800 transition-all">Overdue</button>
            </div>

            {{-- Search --}}
            <div class="relative sm:ml-auto sm:w-72">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="lease-search" type="text" placeholder="Search tenant or property…"
                       oninput="searchLeases(this.value)"
                       class="w-full bg-white pl-10 pr-4 py-2.5 text-sm text-gray-800
                              border border-gray-300 rounded-xl shadow-sm
                              placeholder-gray-400
                              focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400
                              transition">
            </div>
        </div>

        {{-- ── Table ──────────────────────────────────────────────── --}}
        @if ($leases->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-gray-600 font-medium">No lease contracts yet</p>
                <p class="text-sm text-gray-400 mt-1">Add a lease from any property's detail page.</p>
                <a href="{{ route('landlord.properties.index') }}"
                   class="mt-5 inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                    Go to Properties
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
                            <th class="px-5 py-3.5">Property</th>
                            <th class="px-5 py-3.5">Tenant</th>
                            <th class="px-5 py-3.5 hidden md:table-cell">Lease Term</th>
                            <th class="px-5 py-3.5 hidden lg:table-cell">Rent / mo</th>
                            <th class="px-5 py-3.5 hidden lg:table-cell">Frequency</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 hidden xl:table-cell">Days Left</th>
                        </tr>
                    </thead>
                    <tbody id="lease-table" class="divide-y divide-gray-50">

                        @foreach ($leases as $lease)
                            @php
                                $today       = now()->startOfDay();
                                $daysLeft    = $today->diffInDays($lease->end_date, false);   // negative = past
                                $isExpiring  = $lease->status === 'active' && $daysLeft >= 0 && $daysLeft <= 30;
                                $isOverdue   = $lease->status === 'active' && $daysLeft < 0;

                                $badgeClass  = match(true) {
                                    $isOverdue  => 'bg-red-50 text-red-600 border-red-100',
                                    $isExpiring => 'bg-amber-50 text-amber-700 border-amber-100',
                                    $lease->status === 'active' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    default     => 'bg-gray-100 text-gray-500 border-gray-200',
                                };
                                $badgeLabel  = match(true) {
                                    $isOverdue  => 'Overdue',
                                    $isExpiring => 'Expiring',
                                    $lease->status === 'active' => 'Active',
                                    default     => ucfirst($lease->status),
                                };
                                $filterAttr  = match(true) {
                                    $isOverdue  => 'overdue',
                                    $isExpiring => 'expiring',
                                    $lease->status === 'active' => 'active',
                                    default     => 'other',
                                };

                                $tenantName   = $lease->tenant->name  ?? '—';
                                $propertyName = $lease->property->name ?? '—';
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition-colors lease-row"
                                data-filter="{{ $filterAttr }}"
                                data-search="{{ strtolower($tenantName . ' ' . $propertyName) }}">

                                {{-- Property --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 truncate max-w-[160px]">{{ $propertyName }}</p>
                                            <p class="text-xs text-gray-400">{{ ucfirst($lease->property->type ?? '') }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tenant --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($tenantName, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $tenantName }}</p>
                                            <p class="text-xs text-gray-400">{{ $lease->tenant->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Lease Term --}}
                                <td class="px-5 py-4 hidden md:table-cell">
                                    <p class="text-gray-700">{{ $lease->start_date->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-400">to {{ $lease->end_date->format('d M Y') }}</p>
                                </td>

                                {{-- Monthly Rent --}}
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    <p class="font-semibold text-gray-900">
                                        Tzs {{ number_format($lease->monthly_rent, 0) }}
                                    </p>
                                </td>

                                {{-- Payment Frequency --}}
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    <span class="text-gray-500 capitalize">{{ $lease->payment_frequency ?? 'monthly' }}</span>
                                </td>

                                {{-- Status badge --}}
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </td>

                                {{-- Days left --}}
                                <td class="px-5 py-4 hidden xl:table-cell">
                                    @if ($daysLeft >= 0)
                                        <span class="text-sm font-medium {{ $isExpiring ? 'text-amber-600' : 'text-gray-600' }}">
                                            {{ $daysLeft }} days
                                        </span>
                                    @else
                                        <span class="text-sm font-medium text-red-500">
                                            {{ abs($daysLeft) }}d overdue
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach

                    </tbody>
                </table>

                {{-- No results after filter --}}
                <div id="no-results" class="hidden py-16 text-center">
                    <p class="text-gray-400 text-sm">No leases match your filter.</p>
                </div>
            </div>

            {{-- Table footer --}}
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Showing <span id="visible-count">{{ $leases->count() }}</span> of {{ $leases->count() }} contracts
                </p>
                <p class="text-xs text-gray-400">
                    Total monthly rent:
                    <span class="font-semibold text-gray-700">
                        Tzs {{ number_format($leases->where('status','active')->sum('monthly_rent'), 0) }}
                    </span>
                </p>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
let currentFilter = 'all';

function filterLeases(filter) {
    currentFilter = filter;

    // Update tab styles
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.className = 'px-3 py-1.5 rounded-lg text-gray-500 hover:text-gray-800 transition-all';
    });
    const active = document.getElementById('tab-' + filter);
    if (active) active.className = 'px-3 py-1.5 rounded-lg bg-white text-gray-800 shadow-sm transition-all';

    applyFilters();
}

function searchLeases(query) {
    applyFilters(query.toLowerCase());
}

function applyFilters(searchQuery = null) {
    const q = searchQuery ?? document.getElementById('lease-search').value.toLowerCase();
    const rows = document.querySelectorAll('.lease-row');
    let visible = 0;

    rows.forEach(row => {
        const matchFilter = currentFilter === 'all' || row.dataset.filter === currentFilter;
        const matchSearch = !q || row.dataset.search.includes(q);
        const show = matchFilter && matchSearch;
        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    const countEl = document.getElementById('visible-count');
    if (countEl) countEl.textContent = visible;

    const noResults = document.getElementById('no-results');
    if (noResults) noResults.classList.toggle('hidden', visible > 0);
}
</script>
@endpush
