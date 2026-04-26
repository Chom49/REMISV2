@extends('layouts.tenant')

@section('title', 'Payment History')

@section('content')

<div class="space-y-5">

    @if($activeLease)
        <h1 class="text-xl font-bold text-gray-900">{{ $activeLease->property->name }}</h1>
    @else
        <h1 class="text-xl font-bold text-gray-900">Payment History</h1>
    @endif

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        @if($payments->isEmpty())
            <div class="text-center py-16">
                <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <p class="text-gray-400 text-sm">No payment history yet.</p>
            </div>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3.5">Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3.5 hidden sm:table-cell">Category</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3.5 hidden md:table-cell">Description</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3.5">Status</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3.5">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ ($payment->paid_date ?? $payment->due_date)->format('d M') }}
                            </td>
                            <td class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide hidden sm:table-cell">
                                Rent
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 hidden md:table-cell">
                                Rent for {{ $payment->lease?->property?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-semibold
                                    {{ $payment->status === 'paid' ? 'text-green-600' : ($payment->status === 'overdue' ? 'text-red-600' : 'text-yellow-600') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-800">
                                Tzs {{ number_format($payment->amount, 0) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
