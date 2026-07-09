@extends('layouts.landlord')

@section('title', 'Create Lease – Unit ' . $unit->unit_number)

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('landlord.properties.index') }}" class="hover:text-gray-700 transition-colors">Properties</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('landlord.properties.show', [$property, 'tab' => 'units']) }}"
           class="hover:text-gray-700 transition-colors">{{ $property->name }}</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">Create Lease – Unit {{ $unit->unit_number }}</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center font-bold text-amber-600 text-sm">
                {{ $unit->unit_number }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Lease</h1>
                <p class="text-sm text-gray-500">{{ $property->name }} &middot; Unit {{ $unit->unit_number }}</p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li class="flex items-center gap-2">
                        <span class="w-1 h-1 rounded-full bg-red-400 flex-shrink-0"></span>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="lease-form" method="POST"
          action="{{ route('landlord.properties.units.leases.store', [$property, $unit]) }}"
          class="space-y-6">
        @csrf

        {{-- ── Lease Period ──────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Lease Period</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Start Date <span class="text-red-400">*</span>
                    </label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        End Date <span class="text-red-400">*</span>
                    </label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm">
                </div>
            </div>
        </div>

        {{-- ── Financial Terms ───────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Financial Terms</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Monthly Rent <span class="text-gray-400 font-normal">(Tshs)</span> <span class="text-red-400">*</span>
                </label>
                <input type="number" name="monthly_rent" value="{{ old('monthly_rent') }}" min="0" step="0.01" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                       placeholder="300,000">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Payment Frequency <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <select name="payment_frequency" required
                                class="w-full px-4 py-2.5 pr-9 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm appearance-none bg-white">
                            @foreach(['monthly' => 'Monthly', 'weekly' => 'Weekly', 'bi-weekly' => 'Bi-Weekly', 'quarterly' => 'Quarterly', 'annually' => 'Annually'] as $val => $label)
                                <option value="{{ $val }}" {{ old('payment_frequency', 'monthly') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Payment Due Day <span class="text-gray-400 font-normal">(1–31)</span>
                    </label>
                    <input type="number" name="payment_day" value="{{ old('payment_day', 1) }}" min="1" max="31"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                           placeholder="1">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Expiry Reminder <span class="text-gray-400 font-normal">(days before end)</span>
                </label>
                <input type="number" name="lease_expiry_reminder_days" value="{{ old('lease_expiry_reminder_days', 30) }}" min="0" max="365"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                       placeholder="30">
            </div>
        </div>

        {{-- ── Lease Terms ────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Lease Terms</h2>
            <textarea name="lease_terms" rows="5"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm resize-none"
                      placeholder="Describe any special lease conditions, rules, or agreements…">{{ old('lease_terms') }}</textarea>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('landlord.properties.show', [$property, 'tab' => 'units']) }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 px-6 py-2.5 rounded-xl border border-gray-200 hover:border-gray-300 transition-colors">
                Cancel
            </a>
            <button type="submit" form="lease-form"
                    class="bg-primary-500 hover:bg-primary-600 text-white font-semibold px-8 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                Create Lease
            </button>
        </div>
    </form>

</div>
@endsection
