@extends('layouts.landlord')

@section('title', 'Reports')

@section('content')

<div class="space-y-5">

    <h1 class="text-xl font-bold text-gray-900">Reports</h1>

    {{-- Two report group cards --}}
    <div class="grid gap-5">

        {{-- Group 1: Properties & Financials --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            @php
            $group1 = [
                ['label' => 'property directory',       'icon' => 'chart'],
                ['label' => 'Overdue rent payments',    'icon' => 'chart-up'],
                ['label' => 'Breakdown statement',      'icon' => 'chart'],
                ['label' => 'tenants',                  'icon' => 'chart-up'],
                ['label' => 'Rent payments',            'icon' => 'chart'],
                ['label' => 'Maintenance',              'icon' => 'chart-up'],
            ];
            @endphp

            <div class="grid sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                @foreach($group1 as $i => $item)
                    @if($i % 2 === 0)
                        @if($i > 0)</div>@endif
                        <div class="divide-y divide-gray-50">
                    @endif
                    <a href="#"
                       class="flex items-center gap-3 px-6 py-4 hover:bg-gray-50 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-700 group-hover:text-gray-900 capitalize">{{ $item['label'] }}</span>
                    </a>
                @endforeach
                </div>
            </div>
        </div>

        {{-- Group 2: Lease & Occupancy --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
            @php
            $group2 = [
                ['label' => 'Lease expiry',             'icon' => 'chart'],
                ['label' => 'upcoming rent payments',   'icon' => 'chart-up'],
                ['label' => 'Lease Contracts',          'icon' => 'chart'],
                ['label' => 'Occupancy',                'icon' => 'chart-up'],
                ['label' => 'Rent  Received',           'icon' => 'chart'],
                ['label' => 'Expenses',                 'icon' => 'chart-up'],
            ];
            @endphp

            <div class="grid sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                @foreach($group2 as $i => $item)
                    @if($i % 2 === 0)
                        @if($i > 0)</div>@endif
                        <div class="divide-y divide-gray-50">
                    @endif
                    <a href="#"
                       class="flex items-center gap-3 px-6 py-4 hover:bg-gray-50 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-700 group-hover:text-gray-900 capitalize">{{ $item['label'] }}</span>
                    </a>
                @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
