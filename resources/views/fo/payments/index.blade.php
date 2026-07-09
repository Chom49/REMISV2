@extends('layouts.fo')
@section('title', 'Payments')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage control numbers, verify transactions, and mark payments as paid</p>
    </div>

    {{-- Flash --}}
    @foreach(['success','error','warning'] as $type)
    @if(session($type))
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm border
        {{ $type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' :
           ($type === 'error'   ? 'bg-red-50 border-red-200 text-red-700' :
                                  'bg-amber-50 border-amber-200 text-amber-700') }}">
        {{ session($type) }}
    </div>
    @endif
    @endforeach

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
        $filters = ['all' => ['label'=>'All Tenants','color'=>'text-gray-500'],
                    'pending'  => ['label'=>'Pending','color'=>'text-amber-500'],
                    'overdue'  => ['label'=>'Overdue','color'=>'text-red-500'],
                    'upcoming' => ['label'=>'Due Soon','color'=>'text-indigo-500']];
        @endphp
        @foreach($filters as $key => $f)
        <a href="{{ route('fo.payments.index', ['filter' => $key]) }}"
           class="bg-white rounded-2xl border shadow-sm p-5 transition
                  {{ $filter === $key ? 'border-primary-300 ring-2 ring-primary-100' : 'border-gray-100 hover:border-primary-200' }}">
            <p class="text-xs font-semibold {{ $f['color'] }} uppercase tracking-wide mb-2">{{ $f['label'] }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $stats[$key] ?? 0 }}</p>
        </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-3">
            <h2 class="text-sm font-bold text-gray-700 flex-1">Tenant Payments</h2>
            @if($filter !== 'all')
            <a href="{{ route('fo.payments.index') }}" class="text-xs text-gray-400 hover:text-gray-600">Clear filter</a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-50 bg-gray-50/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Property / Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Control Number</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                @forelse($tenants as $tenant)
                @php
                    $row     = $rowData[$tenant->id] ?? [];
                    $payment = $row['payment'] ?? null;
                    $lease   = $row['active_lease'] ?? null;
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-3">
                        <p class="font-semibold text-gray-900">{{ $tenant->name }}</p>
                        <p class="text-xs text-gray-400">{{ $tenant->email }}</p>
                    </td>
                    <td class="px-6 py-3 text-gray-600">
                        {{ optional(optional($lease)->property)->name ?? '–' }}
                        @if(optional(optional($lease)->unit)->unit_number)
                        <span class="text-gray-400">/ {{ $lease->unit->unit_number }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 font-semibold text-gray-800">
                        {{ $payment ? 'TZS ' . number_format($payment->amount, 0) : '–' }}
                    </td>
                    <td class="px-6 py-3 text-gray-600">
                        {{ $payment ? optional($payment->due_date)->format('d M Y') : '–' }}
                    </td>
                    <td class="px-6 py-3">
                        @if($payment)
                        @php
                        $sc = ['paid'=>'bg-emerald-100 text-emerald-700',
                               'pending'=>'bg-amber-100 text-amber-700',
                               'overdue'=>'bg-red-100 text-red-700'][$payment->status] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sc }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                        @else
                        <span class="text-gray-400 text-xs">No invoice</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        @if($payment && $payment->control_number)
                        <div>
                            <code class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded">{{ $payment->control_number }}</code>
                            @if($payment->control_number_sent_at)
                            <p class="text-xs text-gray-400 mt-0.5">Sent {{ $payment->control_number_sent_at->diffForHumans() }}</p>
                            @endif
                        </div>
                        @elseif($payment)
                        <span class="text-xs text-gray-400">Not generated</span>
                        @else
                        <span class="text-gray-300">–</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        @if($payment && $payment->status !== 'paid')
                        <div class="flex items-center justify-end gap-2 flex-wrap">

                            {{-- Generate CN --}}
                            @if($row['can_generate'] ?? false)
                            <form method="POST" action="{{ route('fo.payments.generate', $payment) }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Generate CN
                                </button>
                            </form>
                            @endif

                            {{-- Send --}}
                            @if($payment->control_number && ($row['active_control'] ?? false))
                            <div class="relative">
                                <button type="button"
                                        onclick="toggleSendMenu(this)"
                                        class="inline-flex items-center gap-1 bg-primary-500 hover:bg-primary-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Send
                                </button>
                                <div class="send-menu hidden absolute right-0 mt-1 w-36 bg-white rounded-xl border border-gray-200 shadow-lg z-20 py-1">
                                    <form method="POST" action="{{ route('fo.payments.send', $payment) }}">
                                        @csrf
                                        <input type="hidden" name="channel" value="email">
                                        <button type="submit" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-50 hover:text-primary-700">Send via Email</button>
                                    </form>
                                    <form method="POST" action="{{ route('fo.payments.send', $payment) }}">
                                        @csrf
                                        <input type="hidden" name="channel" value="sms">
                                        <button type="submit" class="w-full text-left px-3 py-2 text-xs hover:bg-primary-50 hover:text-primary-700">Send via SMS</button>
                                    </form>
                                </div>
                            </div>
                            @endif

                            {{-- Check status --}}
                            @if($payment->control_number)
                            <button type="button"
                                    onclick="checkPaymentStatus({{ $payment->id }}, '{{ route('fo.payments.status', $payment) }}')"
                                    class="inline-flex items-center gap-1 bg-primary-100 hover:bg-primary-200 text-primary-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Check
                            </button>
                            @endif

                            {{-- Manual mark paid --}}
                            <button type="button"
                                    onclick="openMarkPaid({{ $payment->id }}, '{{ addslashes($tenant->name) }}', '{{ addslashes(number_format($payment->amount, 0)) }}')"
                                    class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                Mark Paid
                            </button>
                        </div>
                        @elseif($payment && $payment->status === 'paid')
                        <span class="text-xs text-emerald-600 font-medium">Paid {{ optional($payment->paid_date)->format('d M Y') }}</span>
                        @else
                        <span class="text-xs text-gray-400">–</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">No tenants found.</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Mark Paid Modal --}}
<div id="markPaidModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-base font-bold text-gray-900 mb-1">Mark Payment as Paid</h3>
        <p id="markPaidDesc" class="text-sm text-gray-500 mb-4"></p>
        <form id="markPaidForm" method="POST" action="#">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reference / Receipt No. (optional)</label>
                    <input type="text" name="reference" placeholder="e.g. NMB-1234567"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes (optional)</label>
                    <textarea name="notes" rows="2" placeholder="Any notes…"
                              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-xl text-sm transition">
                    Confirm Paid
                </button>
                <button type="button" onclick="document.getElementById('markPaidModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 rounded-xl text-sm transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Status result toast --}}
<div id="statusToast" class="fixed bottom-4 right-4 z-50 hidden max-w-sm"></div>
@endsection

@push('scripts')
<script>
function toggleSendMenu(btn) {
    const menu = btn.nextElementSibling;
    const isOpen = !menu.classList.contains('hidden');
    document.querySelectorAll('.send-menu').forEach(m => m.classList.add('hidden'));
    if (!isOpen) menu.classList.remove('hidden');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('.send-menu').forEach(m => m.classList.add('hidden'));
    }
});

const markPaidRouteTemplate = '{{ route("fo.payments.mark-paid", ["payment" => "__ID__"]) }}';
function openMarkPaid(id, name, amount) {
    document.getElementById('markPaidDesc').textContent = name + ' – TZS ' + amount;
    document.getElementById('markPaidForm').action = markPaidRouteTemplate.replace('__ID__', id);
    document.getElementById('markPaidModal').classList.remove('hidden');
}

function checkPaymentStatus(id, url) {
    const toast = document.getElementById('statusToast');
    toast.innerHTML = '<div class="bg-white border border-gray-200 shadow-lg rounded-xl px-4 py-3 text-sm text-gray-700">Checking payment status…</div>';
    toast.classList.remove('hidden');
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            let msg = '', cls = '';
            if (data.status === 'just_paid') {
                msg = 'Payment confirmed! TZS ' + data.amount + ' received.';
                cls = 'bg-emerald-50 border-emerald-300 text-emerald-800';
                setTimeout(() => location.reload(), 2000);
            } else if (data.status === 'paid') {
                msg = 'Already marked as paid on ' + data.paid_at + '.';
                cls = 'bg-blue-50 border-blue-300 text-blue-800';
            } else if (data.status === 'pending') {
                msg = 'Payment not yet received.';
                cls = 'bg-amber-50 border-amber-300 text-amber-800';
            } else {
                msg = data.message || 'Unknown status.';
                cls = 'bg-gray-50 border-gray-200 text-gray-700';
            }
            toast.innerHTML = '<div class="border shadow-lg rounded-xl px-4 py-3 text-sm ' + cls + '">' + msg + '</div>';
            setTimeout(() => toast.classList.add('hidden'), 5000);
        })
        .catch(() => {
            toast.innerHTML = '<div class="bg-red-50 border-red-200 shadow-lg rounded-xl px-4 py-3 text-sm text-red-700">Could not check status.</div>';
            setTimeout(() => toast.classList.add('hidden'), 4000);
        });
}
</script>
@endpush
