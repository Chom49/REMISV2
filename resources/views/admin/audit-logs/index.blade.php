@extends('layouts.admin')
@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Audit Logs & Security</h1>
            <p class="text-sm text-gray-500 mt-0.5">Track all system actions and user activity.</p>
        </div>
        <form method="POST" action="{{ route('admin.audit-logs.clear') }}"
              onsubmit="return confirm('Delete all log entries older than 90 days?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-red-600
                           border border-red-200 px-5 py-2.5 rounded-xl hover:bg-red-50 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Clear &gt;90 days
            </button>
        </form>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Filters</h2>
        </div>
        <form method="GET" class="px-5 py-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Action</label>
                    <select name="action"
                            class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 text-gray-700
                                   focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
                        <option value="">All actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">User</label>
                    <select name="user_id"
                            class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 text-gray-700
                                   focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
                        <option value="">All users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 text-gray-700
                                  focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2.5 text-gray-700
                                  focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                               text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
                    Filter
                </button>
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-800
                          px-5 py-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Clear
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        @if($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-gray-600 font-semibold">No log entries found</p>
                <p class="text-sm text-gray-400 mt-1">Try adjusting your filters.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Time</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Action</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Description</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden sm:table-cell">User</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden md:table-cell">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-4 text-gray-400 text-xs whitespace-nowrap">
                            {{ $log->created_at->format('d M Y') }}<br>
                            <span class="text-gray-300">{{ $log->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-700 max-w-xs">{{ $log->description }}</td>
                        <td class="px-5 py-4 hidden sm:table-cell">
                            @if($log->user)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                    </div>
                                    <span class="text-gray-700">{{ $log->user->name }}</span>
                                </div>
                            @else
                                <span class="text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 hidden md:table-cell text-gray-500 font-mono text-xs">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries
            </p>
            @if($logs->hasPages())
                {{ $logs->links() }}
            @endif
        </div>
        @endif
    </div>

</div>
@endsection
