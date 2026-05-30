@extends('layouts.admin')
@section('title', 'Backup & Restore')

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
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Backup & Restore</h1>
            <p class="text-sm text-gray-500 mt-0.5">Create and manage database backups.</p>
        </div>
        <form method="POST" action="{{ route('admin.backups.create') }}"
              onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').textContent='Creating…'">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                           text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Create Backup
            </button>
        </form>
    </div>

    {{-- Info --}}
    <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 text-sm text-blue-800">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Backups are SQL dumps saved to <code class="font-mono text-blue-700 bg-blue-100 px-1 rounded">storage/app/backups/</code>.
        Requires <code class="font-mono text-blue-700 bg-blue-100 px-1 rounded">mysqldump</code> to be available in the system PATH.</span>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        @if($backups->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <p class="text-gray-600 font-semibold">No backups yet</p>
                <p class="text-sm text-gray-400 mt-1">Click "Create Backup" to generate your first backup.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Filename</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden sm:table-cell">Size</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Status</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden md:table-cell">Created By</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5 hidden lg:table-cell">Date</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($backups as $backup)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-4">
                            <p class="font-medium text-gray-800 font-mono text-xs">{{ $backup->filename }}</p>
                        </td>
                        <td class="px-5 py-4 hidden sm:table-cell text-gray-600">{{ $backup->formattedSize() }}</td>
                        <td class="px-5 py-4">
                            @php
                                $cls = match($backup->status) {
                                    'completed' => 'bg-emerald-50 text-emerald-700',
                                    'pending'   => 'bg-amber-50 text-amber-700',
                                    'failed'    => 'bg-red-50 text-red-700',
                                    default     => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $cls }}">
                                {{ ucfirst($backup->status) }}
                            </span>
                            @if($backup->notes && $backup->status === 'failed')
                                <p class="text-xs text-red-500 mt-1 max-w-xs" title="{{ $backup->notes }}">{{ Str::limit($backup->notes, 60) }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 hidden md:table-cell text-gray-600">{{ $backup->creator?->name ?? '—' }}</td>
                        <td class="px-5 py-4 hidden lg:table-cell">
                            <p class="text-gray-700 font-medium">{{ $backup->created_at->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $backup->created_at->format('H:i') }}</p>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($backup->status === 'completed')
                                <a href="{{ route('admin.backups.download', $backup) }}"
                                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-gray-300
                                          bg-white hover:bg-gray-50 hover:border-gray-400 text-gray-700
                                          text-xs font-semibold shadow-sm transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download
                                </a>
                                @endif
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}"
                                      onsubmit="return confirm('Delete this backup permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl
                                                   border border-red-200 text-red-600 hover:bg-red-50
                                                   text-xs font-semibold transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($backups->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $backups->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
