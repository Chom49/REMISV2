@extends('layouts.landlord')

@section('title', 'Create Lease')

@section('content')

<div class="max-w-2xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('landlord.properties.index') }}" class="hover:text-gray-700">Properties</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('landlord.properties.show', $property) }}" class="hover:text-gray-700">{{ strtolower($property->name) }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 font-medium">Create lease</span>
    </div>

    <h1 class="text-xl font-bold text-gray-900 mb-6">Properties/{{ strtolower($property->name) }}/Create lease</h1>

    @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <h2 class="text-sm font-semibold text-gray-500 text-center mb-5">Lease details</h2>

        <form id="lease-form" method="POST" action="{{ route('landlord.properties.leases.store', $property) }}" class="space-y-5">
            @csrf

            {{-- Dates --}}
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Start Date</label>
                    <div class="relative">
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required
                               class="input-field pr-10">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">End Date</label>
                    <div class="relative">
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required
                               class="input-field pr-10">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Rent Amount --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rent Amount <span class="text-gray-400 font-normal">(Tshs)</span></label>
                <input type="number" name="monthly_rent" value="{{ old('monthly_rent', $property->rent_amount) }}" min="0" step="0.01" required
                       class="input-field" placeholder="300,000">
            </div>

            {{-- Payment Day + Frequency --}}
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Day</label>
                    <div class="relative">
                        <select name="payment_day" class="input-field appearance-none pr-8">
                            <option value="">Select day</option>
                            @for($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ old('payment_day') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment frequency</label>
                    <div class="relative">
                        <select name="payment_frequency" class="input-field appearance-none pr-8">
                            <option value="monthly" {{ old('payment_frequency', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="weekly" {{ old('payment_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="bi-weekly" {{ old('payment_frequency') == 'bi-weekly' ? 'selected' : '' }}>Bi-weekly</option>
                            <option value="quarterly" {{ old('payment_frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="annually" {{ old('payment_frequency') == 'annually' ? 'selected' : '' }}>Annually</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Deposit --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Deposit amount</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">Tsh</span>
                    <input type="number" name="security_deposit" value="{{ old('security_deposit', '0') }}" min="0" step="0.01"
                           class="input-field pl-10" placeholder="0.0">
                </div>
            </div>

            {{-- Reminder --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Lease expiry reminder days before</label>
                <input type="number" name="lease_expiry_reminder_days" value="{{ old('lease_expiry_reminder_days') }}" min="0" max="365"
                       class="input-field" placeholder="e.g. 30">
            </div>

        </form>
    </div>

    <div class="flex justify-end mt-5">
        <button type="submit" form="lease-form"
                class="bg-primary-100 hover:bg-primary-200 text-primary-800 font-semibold px-8 py-2.5 rounded-xl text-sm transition-colors">
            Create  Lease
        </button>
    </div>

</div>
@endsection
