@extends('layouts.tenant')
@section('title', 'Dashboard')

@section('content')

<div class="space-y-6">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Welcome Header ───────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Welcome back, {{ explode(' ', $tenant->name)[0] }}
            </h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ now()->format('l, d F Y') }}</p>
        </div>
        @if($activeLease)
            <span class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3.5 py-1.5 rounded-full
                         bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Active Tenant
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3.5 py-1.5 rounded-full
                         bg-gray-100 border border-gray-200 text-gray-500 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                Pending Lease
            </span>
        @endif
    </div>

    {{-- ── Stat Cards ────────────────────────────────────────────── --}}
    @php
        $pendingCount  = $payments->whereIn('status', ['pending', 'overdue'])->count();
        $overdueCount  = $payments->where('status', 'overdue')->count();
        $daysLeft      = $activeLease ? now()->startOfDay()->diffInDays($activeLease->end_date, false) : null;
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Active Lease Card --}}
        @if($activeLease)
        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5 col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">Active Lease</p>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-emerald-600">1</p>
            <p class="text-xs text-gray-400 mt-1 truncate">{{ $activeLease->property->name }}</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Active Lease</p>
                <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-400">No Active Lease</p>
            <p class="text-xs text-gray-300 mt-1">Awaiting assignment</p>
        </div>
        @endif

        {{-- Monthly Rent --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Monthly Rent</p>
                <div class="w-8 h-8 rounded-xl bg-gray-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            @if($activeLease)
                <p class="text-xl font-bold text-gray-900 leading-tight">
                    Tshs {{ number_format($activeLease->monthly_rent, 0) }}
                </p>
                <p class="text-xs text-gray-400 mt-1">per month</p>
            @else
                <p class="text-2xl font-bold text-gray-200">—</p>
                <p class="text-xs text-gray-300 mt-1">no lease</p>
            @endif
        </div>

        {{-- Lease End / Days Left --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
            @if($activeLease && $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30) border-amber-100 @endif">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold
                    @if($activeLease && $daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0) text-amber-500
                    @else text-gray-400 @endif uppercase tracking-wide">
                    Lease Ends
                </p>
                <div class="w-8 h-8 rounded-xl
                    @if($activeLease && $daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0) bg-amber-50
                    @else bg-gray-50 @endif flex items-center justify-center">
                    <svg class="w-4 h-4
                        @if($activeLease && $daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0) text-amber-400
                        @else text-gray-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            @if($activeLease)
                <p class="text-xl font-bold
                    @if($daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0) text-amber-600
                    @elseif($daysLeft !== null && $daysLeft < 0) text-red-600
                    @else text-gray-900 @endif leading-tight">
                    @if($daysLeft !== null && $daysLeft < 0)
                        Overdue
                    @elseif($daysLeft !== null && $daysLeft <= 30)
                        {{ $daysLeft }}d left
                    @else
                        {{ $activeLease->end_date->format('d M Y') }}
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    @if($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30)
                        ends {{ $activeLease->end_date->format('d M Y') }}
                    @elseif($daysLeft !== null && $daysLeft < 0)
                        expired {{ $activeLease->end_date->format('d M Y') }}
                    @else
                        {{ $daysLeft }} days remaining
                    @endif
                </p>
            @else
                <p class="text-2xl font-bold text-gray-200">—</p>
                <p class="text-xs text-gray-300 mt-1">no lease</p>
            @endif
        </div>

        {{-- Payments Due --}}
        <div class="bg-white rounded-2xl border
            {{ $overdueCount > 0 ? 'border-red-100' : 'border-gray-100' }} shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold
                    {{ $overdueCount > 0 ? 'text-red-500' : 'text-gray-400' }} uppercase tracking-wide">
                    Payments Due
                </p>
                <div class="w-8 h-8 rounded-xl {{ $overdueCount > 0 ? 'bg-red-50' : 'bg-gray-50' }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $overdueCount > 0 ? 'text-red-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            @if($activeLease)
                <p class="text-3xl font-bold {{ $overdueCount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $pendingCount }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $overdueCount > 0 ? $overdueCount . ' overdue' : 'pending payment' . ($pendingCount !== 1 ? 's' : '') }}
                </p>
            @else
                <p class="text-3xl font-bold text-gray-200">0</p>
                <p class="text-xs text-gray-300 mt-1">no lease</p>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
         BRANCH: HAS ACTIVE LEASE
    ══════════════════════════════════════════════════════ --}}
    @if($activeLease)

    {{-- Lease Details Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Lease Details</h2>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Active
            </span>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
            <div class="px-6 py-4">
                <p class="text-xs text-gray-400 mb-0.5">Property</p>
                <p class="text-sm font-semibold text-gray-900">{{ $activeLease->property->name }}</p>
                @if($activeLease->unit)
                    <p class="text-xs text-gray-400 mt-0.5">Unit {{ $activeLease->unit->unit_number }}</p>
                @endif
            </div>
            <div class="px-6 py-4">
                <p class="text-xs text-gray-400 mb-0.5">Lease Period</p>
                <p class="text-sm font-semibold text-gray-900">{{ $activeLease->start_date->format('d M Y') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">to {{ $activeLease->end_date->format('d M Y') }}</p>
            </div>
            <div class="px-6 py-4">
                <p class="text-xs text-gray-400 mb-0.5">Monthly Rent</p>
                <p class="text-sm font-semibold text-gray-900">Tshs {{ number_format($activeLease->monthly_rent, 0) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst(str_replace('-', ' ', $activeLease->payment_frequency ?? 'monthly')) }}</p>
            </div>
            <div class="px-6 py-4">
                <p class="text-xs text-gray-400 mb-0.5">Landlord</p>
                <p class="text-sm font-semibold text-gray-900">{{ $activeLease->landlord->name ?? '—' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $activeLease->landlord->email ?? '' }}</p>
            </div>
        </div>
    </div>

    {{-- Payments --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Recent Payments</h2>
            <a href="{{ route('tenant.payments.history') }}"
               class="text-xs text-primary-600 hover:text-primary-800 font-semibold transition-colors">
                View All →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Due Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Description</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Amount</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-900">{{ $payment->due_date->format('d M Y') }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-500 hidden sm:table-cell">
                                Rent — {{ $activeLease->property->name }}
                                @if($activeLease->unit) · Unit {{ $activeLease->unit->unit_number }} @endif
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-900">
                                Tshs {{ number_format($payment->amount, 0) }}
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $badgeClass = match($payment->status) {
                                        'paid'    => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'overdue' => 'bg-red-50 text-red-700 border border-red-200',
                                        default   => 'bg-amber-50 text-amber-700 border border-amber-200',
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($payment->status !== 'paid')
                                    <a href="{{ route('tenant.payments.checkout', $payment) }}"
                                       class="inline-flex items-center gap-1.5 bg-primary-500 hover:bg-primary-600
                                              text-white text-xs font-bold px-4 py-1.5 rounded-lg transition-colors">
                                        Pay Now
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <p class="text-sm text-gray-400">No payments recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Maintenance Requests --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Maintenance Requests</h2>
            <button onclick="document.getElementById('maint-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 bg-primary-500 hover:bg-primary-600
                           text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Request
            </button>
        </div>

        @if($maintenanceRequests->isEmpty())
            <div class="py-12 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-400">No maintenance requests yet.</p>
                <p class="text-xs text-gray-300 mt-1">Report an issue and your landlord will be notified.</p>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($maintenanceRequests as $req)
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50/70 transition-colors">
                        <div class="flex items-center gap-3">
                            @php
                                $priorityColor = match($req->priority ?? 'medium') {
                                    'urgent' => 'bg-red-100 text-red-600',
                                    'high'   => 'bg-orange-100 text-orange-600',
                                    'medium' => 'bg-amber-100 text-amber-600',
                                    default  => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <div class="w-8 h-8 rounded-lg {{ $priorityColor }} flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $req->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ ucfirst($req->priority ?? 'medium') }} priority · {{ $req->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <span class="flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-semibold
                            {{ $req->status === 'resolved' || $req->status === 'closed'
                                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                : ($req->status === 'in_progress'
                                    ? 'bg-blue-50 text-blue-700 border border-blue-200'
                                    : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════
         BRANCH: NO ACTIVE LEASE
    ══════════════════════════════════════════════════════ --}}
    @else

    <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 overflow-hidden">
        <div class="px-8 py-16 text-center max-w-sm mx-auto">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-700">No Active Lease</h3>
            <p class="text-sm text-gray-400 mt-2 leading-relaxed">
                Your landlord hasn't assigned a lease to your account yet.
                You'll be notified once a lease is set up for you.
            </p>
            <div class="mt-6 flex items-center justify-center gap-2 text-xs text-gray-400">
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Contact your landlord if you think this is a mistake.
            </div>
        </div>
    </div>

    @endif

</div>

{{-- ===== MAINTENANCE REQUEST MODAL ===== --}}
<div id="maint-modal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
     role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">

        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">New Maintenance Request</h3>
            <button type="button" onclick="document.getElementById('maint-modal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('tenant.maintenance.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Title <span class="text-red-400">*</span>
                </label>
                <input type="text" name="title" required
                       placeholder="Brief description of the issue"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                              placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-400
                              focus:border-primary-400 transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Priority</label>
                    <select name="priority"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                                   focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white transition">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Due Date</label>
                    <input type="date" name="due_date"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                                  focus:outline-none focus:ring-2 focus:ring-primary-400 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Description <span class="text-red-400">*</span>
                </label>
                <textarea name="description" rows="4" required
                          placeholder="Describe the issue in detail…"
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-900
                                 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-400
                                 focus:border-primary-400 resize-none transition"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" onclick="document.getElementById('maint-modal').classList.add('hidden')"
                        class="text-sm font-semibold text-gray-500 hover:text-gray-700 px-5 py-2.5
                               border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600
                               text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('maint-modal')?.addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>
@endpush
