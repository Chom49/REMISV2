@extends('layouts.tenant')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-6">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($activeLease)

        {{-- Property heading --}}
        <h1 class="text-xl font-bold text-gray-900">{{ $activeLease->property->name }}</h1>

        {{-- Payments table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3">Due</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3 hidden sm:table-cell">Category</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3 hidden md:table-cell">Description</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3">Status</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3">Amount</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 text-sm text-gray-700 font-medium whitespace-nowrap">
                                {{ $payment->due_date->format('d M') }}
                            </td>
                            <td class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Rent</td>
                            <td class="px-5 py-4 text-sm text-gray-600 hidden md:table-cell">
                                Rent for {{ $activeLease->property->name }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-bold uppercase tracking-wide
                                    {{ $payment->status === 'paid' ? 'text-green-600' : ($payment->status === 'overdue' ? 'text-red-600' : 'text-yellow-600') }}">
                                    {{ strtoupper($payment->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-800">
                                Tzs {{ number_format($payment->amount, 0) }}
                            </td>
                            <td class="px-5 py-4">
                                @if($payment->status !== 'paid')
                                    <a href="{{ route('tenant.payments.checkout', $payment) }}"
                                       class="bg-primary-500 hover:bg-primary-600 text-white text-xs font-bold px-4 py-1.5 rounded-lg transition-colors uppercase tracking-wide">
                                        PAY
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300 font-medium">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">No payments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Maintenance Requests --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Maintenance Requests</h2>

            @if($maintenanceRequests->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm text-gray-400 mb-4">You have no maintenance requests</p>
                    <button onclick="document.getElementById('maint-modal').classList.remove('hidden')"
                            class="bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2 rounded-full transition-colors">
                        +New Request
                    </button>
                </div>
            @else
                <div class="space-y-2 mb-4">
                    @foreach($maintenanceRequests as $req)
                        <div class="flex items-center justify-between py-2.5 px-3 rounded-xl hover:bg-gray-50">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $req->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $req->created_at->format('d M Y') }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $req->status === 'resolved' || $req->status === 'closed' ? 'bg-green-100 text-green-700' :
                                   ($req->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <button onclick="document.getElementById('maint-modal').classList.remove('hidden')"
                        class="bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2 rounded-full transition-colors">
                    +New Request
                </button>
            @endif
        </div>

    @else

        {{-- No active lease state --}}
        <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-600">No Active Lease</h3>
            <p class="text-gray-400 text-sm mt-1">Your landlord will assign you a property and lease agreement.</p>
        </div>

    @endif

</div>

{{-- ===== NEW MAINTENANCE REQUEST MODAL ===== --}}
<div id="maint-modal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
     role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">

        <form method="POST" action="{{ route('tenant.maintenance.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="text" name="title" required
                   class="w-full border-0 border-b-2 border-gray-200 focus:border-primary-500 focus:outline-none text-base font-medium text-gray-700 pb-2 placeholder-gray-300 transition-colors bg-transparent"
                   placeholder="Enter title of Request">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Priority</label>
                    <div class="relative">
                        <select name="priority" class="input-field appearance-none pr-7 text-sm">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Due Date</label>
                    <div class="relative">
                        <input type="date" name="due_date" class="input-field pr-8 text-sm">
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-2">Enter Description</label>
                <textarea name="description" rows="4" required
                          class="input-field resize-none text-sm" placeholder="Enter the description"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('maint-modal').classList.add('hidden')"
                        class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2">Cancel</button>
                <button type="submit"
                        class="bg-primary-100 hover:bg-primary-200 text-primary-800 font-semibold px-6 py-2 rounded-xl text-sm transition-colors">
                    Save
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
