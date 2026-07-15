@extends('layouts.landlord')
@section('title', 'Financial Officer')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Financial Officer</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your financial officer accounts and review performance</p>
        </div>
        <a href="{{ route('landlord.fo.create') }}"
           class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                  text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add FO Account
        </a>
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

    @if(count($officers) === 0)
    {{-- Empty state --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-gray-900 mb-1">No Financial Officer yet</h3>
        <p class="text-sm text-gray-500 mb-5">Create an account to delegate payment management.</p>
        <a href="{{ route('landlord.fo.create') }}"
           class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition">
            Create Account
        </a>
    </div>
    @else

    {{-- FO cards --}}
    <div class="grid md:grid-cols-2 gap-5">
        @foreach($officers as $fo)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        {{ strtoupper(substr($fo->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">{{ $fo->name }}</p>
                        <p class="text-xs text-gray-400">{{ $fo->email }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                    {{ $fo->tenant_status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $fo->tenant_status === 'active' ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="grid grid-cols-3 gap-3 mb-4 text-center">
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xl font-bold text-emerald-600">{{ $fo->paymentsVerified ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Verified</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xl font-bold text-indigo-600">{{ number_format(($fo->totalCollected ?? 0) / 1000, 0) }}K</p>
                    <p class="text-xs text-gray-400 mt-0.5">Collected</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xs font-medium text-gray-600">{{ optional($fo->created_at)->format('d M Y') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Since</p>
                </div>
            </div>

            {{-- Resend invitation --}}
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-1.5">Resend Invitation</p>
            <div class="flex gap-2 mb-2">
                <form method="POST" action="{{ route('landlord.fo.resend-invitation', $fo) }}" class="flex-1"
                      onsubmit="return confirm('Resend invitation email to {{ addslashes($fo->email) }}? A new temporary password will be generated.')">
                    @csrf
                    <input type="hidden" name="channel" value="email">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5 bg-indigo-50 hover:bg-indigo-100
                                   text-indigo-700 border border-indigo-200 text-xs font-semibold px-3 py-2 rounded-xl transition">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Email
                    </button>
                </form>
                <form method="POST" action="{{ route('landlord.fo.resend-invitation', $fo) }}" class="flex-1"
                      onsubmit="return confirm('Resend invitation via SMS to {{ addslashes($fo->phone ?? '') }}? A new temporary password will be generated.')">
                    @csrf
                    <input type="hidden" name="channel" value="sms">
                    <button type="submit" {{ empty($fo->phone) ? 'disabled title=No phone number on file' : '' }}
                            class="w-full inline-flex items-center justify-center gap-1.5 bg-emerald-50 hover:bg-emerald-100
                                   text-emerald-700 border border-emerald-200 text-xs font-semibold px-3 py-2 rounded-xl transition
                                   disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-emerald-50">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
                        </svg>
                        SMS
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('landlord.fo.edit', $fo) }}"
                   class="flex-1 inline-flex items-center justify-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700
                          text-xs font-semibold px-3 py-2 rounded-xl transition">
                    Edit
                </a>

                <form method="POST" action="{{ route('landlord.fo.toggle', $fo) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5
                                   {{ $fo->tenant_status === 'active'
                                       ? 'bg-amber-100 hover:bg-amber-200 text-amber-700'
                                       : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700' }}
                                   text-xs font-semibold px-3 py-2 rounded-xl transition"
                            onclick="return confirm('{{ $fo->tenant_status === 'active' ? 'Deactivate' : 'Activate' }} this account?')">
                        {{ $fo->tenant_status === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('landlord.fo.destroy', $fo) }}"
                      onsubmit="return confirm('Delete this Financial Officer account? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center p-2 bg-red-50 hover:bg-red-100
                                   text-red-600 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Financial summary --}}
    @if(isset($summary))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-700 mb-4">Financial Summary (This Month)</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 mb-1">Collected</p>
                <p class="text-xl font-bold text-emerald-600">TZS {{ number_format($summary['collected'] ?? 0, 0) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Pending</p>
                <p class="text-xl font-bold text-amber-600">{{ $summary['pending'] ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Overdue</p>
                <p class="text-xl font-bold text-red-600">{{ $summary['overdue'] ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">Verified by FO</p>
                <p class="text-xl font-bold text-indigo-600">{{ $summary['verified'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    @endif

    @endif

</div>
@endsection
