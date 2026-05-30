@extends('layouts.admin')
@section('title', 'User Accounts')

@section('content')
<div class="space-y-6">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Page header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">User Account Management</h1>
        <p class="text-sm text-gray-500 mt-0.5">View, manage roles, and remove user accounts.</p>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-gray-100">

            {{-- Role filter tabs --}}
            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
                <a href="{{ route('admin.users.index') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                          {{ !request('role') ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    All
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'landlord']) }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                          {{ request('role') === 'landlord' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Landlords
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'tenant']) }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                          {{ request('role') === 'tenant' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Tenants
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'admin']) }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                          {{ request('role') === 'admin' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Admins
                </a>
            </div>

            {{-- Search --}}
            <form method="GET" action="{{ route('admin.users.index') }}" class="relative sm:ml-auto sm:w-72">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name or email…"
                       class="w-full bg-white pl-10 pr-4 py-2.5 text-sm text-gray-800 border border-gray-200
                              rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2
                              focus:ring-primary-400 focus:border-primary-400 transition">
            </form>
        </div>

        {{-- Empty state --}}
        @if($users->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-gray-600 font-semibold">No users found</p>
                <p class="text-sm text-gray-400 mt-1">Try adjusting your search or filter.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">User</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Role</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden sm:table-cell">Phone</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden md:table-cell">Joined</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50/70 transition-colors">

                        {{-- User --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary-500 flex items-center justify-center
                                            text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Role --}}
                        <td class="px-5 py-4">
                            @php
                                $roleCls = match($user->role) {
                                    'admin'    => 'bg-primary-50 text-primary-700',
                                    'landlord' => 'bg-blue-50 text-blue-700',
                                    'tenant'   => 'bg-amber-50 text-amber-700',
                                    default    => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $roleCls }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>

                        {{-- Phone --}}
                        <td class="px-5 py-4 hidden sm:table-cell text-gray-500">{{ $user->phone ?? '—' }}</td>

                        {{-- Joined --}}
                        <td class="px-5 py-4 hidden md:table-cell">
                            <p class="text-gray-700 font-medium">{{ $user->created_at->format('d M Y') }}</p>
                        </td>

                        {{-- Actions dropdown --}}
                        <td class="px-5 py-4 text-right">
                            <div class="relative inline-block" x-data="{ open: false }">

                                <button @click="open = !open" @click.outside="open = false"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl
                                               border border-gray-300 bg-white hover:bg-gray-50
                                               hover:border-gray-400 text-gray-700 text-xs font-semibold
                                               shadow-sm transition-all focus:outline-none
                                               focus:ring-2 focus:ring-primary-300 focus:ring-offset-1">
                                    Actions
                                    <svg class="w-3.5 h-3.5 text-gray-500 transition-transform duration-150"
                                         :class="open ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl
                                            shadow-xl border border-gray-100 z-30 py-1.5 overflow-hidden"
                                     style="display:none;">

                                    <p class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Change Role</p>

                                    @foreach(['landlord', 'tenant', 'admin'] as $role)
                                    @if($role !== $user->role)
                                    <form method="POST" action="{{ route('admin.users.update-role', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="role" value="{{ $role }}">
                                        <button type="submit"
                                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm
                                                       text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                                            <span class="w-2 h-2 rounded-full
                                                {{ $role === 'admin' ? 'bg-primary-500' : ($role === 'landlord' ? 'bg-blue-500' : 'bg-amber-500') }}
                                            "></span>
                                            Make {{ ucfirst($role) }}
                                        </button>
                                    </form>
                                    @endif
                                    @endforeach

                                    @if($user->id !== Auth::id())
                                    <div class="my-1 mx-3 border-t border-gray-100"></div>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm
                                                       text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete Account
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} {{ Str::plural('user', $users->total()) }}
            </p>
            @if($users->hasPages())
                {{ $users->links() }}
            @endif
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
