@extends('layouts.tenant')

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
            +New  Request
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

        {{-- NEW column --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-3 px-1">New</p>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm min-h-52 p-3 space-y-2">
                @forelse($new as $req)
                    <div class="bg-gray-50 rounded-xl p-3.5 hover:shadow-sm transition-shadow">
                        <p class="text-sm font-medium text-gray-800 leading-snug">{{ $req->title }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $req->created_at->format('d M Y') }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $req->priority === 'urgent' || $req->priority === 'high' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($req->priority) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="h-36 flex items-center justify-center">
                        <p class="text-xs text-gray-300">No requests</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- IN PROGRESS column --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-3 px-1">In Progress</p>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm min-h-52 p-3 space-y-2">
                @forelse($inProgress as $req)
                    <div class="bg-blue-50 rounded-xl p-3.5 hover:shadow-sm transition-shadow">
                        <p class="text-sm font-medium text-gray-800 leading-snug">{{ $req->title }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $req->created_at->format('d M Y') }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                In Progress
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="h-36 flex items-center justify-center">
                        <p class="text-xs text-gray-300">No requests</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- COMPLETED column --}}
        <div>
            <p class="text-sm font-semibold text-gray-700 mb-3 px-1">Completed</p>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm min-h-52 p-3 space-y-2">
                @forelse($completed as $req)
                    <div class="bg-green-50 rounded-xl p-3.5 hover:shadow-sm transition-shadow">
                        <p class="text-sm font-medium text-gray-800 leading-snug">{{ $req->title }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $req->updated_at->format('d M Y') }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                Resolved
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="h-36 flex items-center justify-center">
                        <p class="text-xs text-gray-300">No requests</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

{{-- ===== NEW REQUEST MODAL ===== --}}
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
                    <input type="date" name="due_date" class="input-field text-sm">
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
