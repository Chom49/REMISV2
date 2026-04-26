@extends('layouts.landlord')

@section('title', 'Tenants')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Tenants</h1>
        <a href="{{ route('landlord.tenants.create') }}"
           class="bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-colors duration-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            +New Tenant
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Tabs + Search --}}
        <div class="px-5 pt-4 border-b border-gray-100">
            <div class="flex items-center gap-6 text-sm font-medium mb-0">
                <button onclick="filterTab('all')" id="tab-all"
                        class="tab-btn pb-3 border-b-2 border-gray-900 text-gray-900 transition-colors">All</button>
                <button onclick="filterTab('current')" id="tab-current"
                        class="tab-btn pb-3 border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors">Current</button>
                <button onclick="filterTab('past')" id="tab-past"
                        class="tab-btn pb-3 border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors">Past Tenants</button>
            </div>
        </div>

        <div class="px-5 py-3 border-b border-gray-50">
            <div class="relative max-w-xs">
                <input type="text" id="tenant-search" placeholder="Search tenant"
                       oninput="searchTenants(this.value)"
                       class="w-full pl-4 pr-10 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent placeholder-gray-400">
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        @if($tenants->isEmpty())
            <div class="text-center py-16">
                <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-gray-400 text-sm">No tenants yet.</p>
                <a href="{{ route('landlord.tenants.create') }}" class="mt-2 inline-block text-xs text-primary-600 font-medium hover:underline">Add your first tenant</a>
            </div>
        @else
            <table class="w-full" id="tenants-table">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3">Name</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3">Email</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide px-6 py-3 hidden sm:table-cell">Mobile</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="tenants-body">
                    @foreach($tenants as $tenant)
                        <tr class="hover:bg-gray-50 transition-colors tenant-row"
                            data-name="{{ strtolower($tenant->name) }}"
                            data-status="{{ $tenant->leasesAsTenant()->where('status','active')->exists() ? 'current' : 'past' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-xs font-bold text-primary-700 flex-shrink-0">
                                        {{ strtoupper(substr($tenant->name, 0, 2)) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $tenant->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $tenant->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 hidden sm:table-cell">{{ $tenant->phone ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination hint --}}
            <div class="flex items-center justify-end px-6 py-3 border-t border-gray-50 text-xs text-gray-400 gap-2">
                <span>Rows per page:</span>
                <select class="border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function filterTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-gray-900', 'text-gray-900');
        btn.classList.add('border-transparent', 'text-gray-400');
    });
    const active = document.getElementById('tab-' + tab);
    active.classList.add('border-gray-900', 'text-gray-900');
    active.classList.remove('border-transparent', 'text-gray-400');

    document.querySelectorAll('.tenant-row').forEach(row => {
        if (tab === 'all') {
            row.style.display = '';
        } else {
            row.style.display = row.dataset.status === tab ? '' : 'none';
        }
    });
}

function searchTenants(query) {
    const q = query.toLowerCase();
    document.querySelectorAll('.tenant-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
}
</script>
@endpush
