@extends('layouts.landlord')

@section('title', 'Maintenance')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Maintenance</h1>
        <button onclick="document.getElementById('maint-modal').classList.remove('hidden')"
                class="bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Request
        </button>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Kanban board --}}
    <div class="grid grid-cols-3 gap-4">

        {{-- NEW --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-3 px-1">New</p>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm min-h-60 p-3 space-y-2">
                @forelse($new as $req)
                    <div class="bg-gray-50 rounded-xl p-3.5 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $req->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $req->property->name }}</p>
                                @if($req->tenant)
                                    <p class="text-xs text-gray-400">{{ $req->tenant->name }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('landlord.maintenance.update', $req) }}">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary-400 bg-white text-gray-600">
                                    <option value="open" selected>New</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </form>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $req->priority === 'urgent' || $req->priority === 'high' ? 'bg-red-100 text-red-700' : ($req->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($req->priority) }}
                            </span>
                            @if($req->due_date)
                                <span class="text-xs text-gray-400">Due {{ $req->due_date->format('d M') }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="h-40 flex items-center justify-center">
                        <p class="text-xs text-gray-300">No new requests</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- IN PROGRESS --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-3 px-1">In Progress</p>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm min-h-60 p-3 space-y-2">
                @forelse($inProgress as $req)
                    <div class="bg-blue-50 rounded-xl p-3.5 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $req->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $req->property->name }}</p>
                                @if($req->tenant)
                                    <p class="text-xs text-gray-400">{{ $req->tenant->name }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('landlord.maintenance.update', $req) }}">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary-400 bg-white text-gray-600">
                                    <option value="open">New</option>
                                    <option value="in_progress" selected>In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </form>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $req->priority === 'urgent' || $req->priority === 'high' ? 'bg-red-100 text-red-700' : ($req->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($req->priority) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="h-40 flex items-center justify-center">
                        <p class="text-xs text-gray-300">No requests in progress</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- COMPLETED --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-3 px-1">Completed</p>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm min-h-60 p-3 space-y-2">
                @forelse($completed as $req)
                    <div class="bg-green-50 rounded-xl p-3.5 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 leading-snug">{{ $req->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $req->property->name }}</p>
                            </div>
                            <form method="POST" action="{{ route('landlord.maintenance.destroy', $req) }}"
                                  onsubmit="return confirm('Remove this resolved request?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 hover:bg-red-200 text-red-500 hover:text-red-700
                                               flex items-center justify-center transition-colors"
                                        title="Remove">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                Resolved
                            </span>
                            <span class="text-xs text-gray-400">{{ $req->updated_at->format('d M Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="h-40 flex items-center justify-center">
                        <p class="text-xs text-gray-300">No completed requests</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

{{-- ===== NEW MAINTENANCE REQUEST MODAL ===== --}}
<div id="maint-modal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
     role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">
        <form method="POST" action="{{ route('landlord.maintenance.store') }}" class="p-6 space-y-4">
            @csrf

            {{-- Title --}}
            <input type="text" name="title" required
                   class="w-full border-0 border-b-2 border-gray-200 focus:border-primary-500 focus:outline-none text-base font-medium text-gray-700 pb-2 placeholder-gray-300 transition-colors bg-transparent"
                   placeholder="Enter title of Request">

            {{-- Property + Status --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Property</label>
                    <div class="relative">
                        <select name="property_id" required class="input-field appearance-none pr-7 text-sm">
                            <option value="">Select property</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <div class="relative">
                        <select name="status" class="input-field appearance-none pr-7 text-sm">
                            <option value="open">New</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Completed</option>
                        </select>
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Due Date + Priority --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Due Date</label>
                    <div class="relative">
                        <input type="date" name="due_date" class="input-field pr-8 text-sm">
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Priority</label>
                    <div class="relative">
                        <select name="priority" class="input-field appearance-none pr-7 text-sm">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high" selected>High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Viewable By --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Viewable By</label>
                <div class="relative">
                    <select name="viewable_by" class="input-field appearance-none pr-7 text-sm">
                        <option value="landlord_only">Landlord Only</option>
                        <option value="all">Landlord &amp; Tenant</option>
                    </select>
                    <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1.5">Enter Description</label>
                <textarea name="description" rows="3" required
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
