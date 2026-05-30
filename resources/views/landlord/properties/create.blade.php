@extends('layouts.landlord')

@section('title', 'Add Property')

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('landlord.properties.index') }}" class="hover:text-gray-600 transition-colors">Properties</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-600">Add Property</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Add a new property</h1>
        <p class="text-sm text-gray-500 mt-1">Fill in the details below. Units will be created automatically.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li class="flex items-center gap-2">
                        <span class="w-1 h-1 rounded-full bg-red-400 flex-shrink-0"></span>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="property-form" method="POST" action="{{ route('landlord.properties.store') }}"
          enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- ── Section 1: Basic Info ──────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Basic Information</h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Property Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                           placeholder="e.g. Sunrise Apartments">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Property Address <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="address" value="{{ old('address') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                           placeholder="Street address">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Region</label>
                    <div class="relative">
                        <select name="county"
                                class="w-full px-4 py-2.5 pr-9 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm appearance-none bg-white">
                            <option value="">Select region</option>
                            @foreach(['Dar es Salaam','Arusha','Mwanza','Dodoma','Tanga','Zanzibar','Mbeya','Morogoro','Other'] as $r)
                                <option value="{{ $r }}" {{ old('county') == $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Total Area <span class="text-gray-400 font-normal">(m²) – optional</span>
                </label>
                <input type="number" name="total_area" value="{{ old('total_area') }}" step="0.01" min="0"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                       placeholder="0.00">
            </div>
        </div>

        {{-- ── Section 2: Property Type & Floor Layout ─────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Property Type</h2>

            {{-- Category cards --}}
            <div class="grid sm:grid-cols-2 gap-3">
                <label id="label-single"
                       class="flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all
                              {{ old('property_category', 'single') === 'single' ? 'border-primary-400 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="property_category" value="single" id="cat-single"
                           class="mt-0.5 text-primary-600 focus:ring-primary-500"
                           {{ old('property_category', 'single') === 'single' ? 'checked' : '' }}>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="text-sm font-semibold text-gray-800">Single Unit</span>
                        </div>
                        <p class="text-xs text-gray-500">One standalone rental unit — house, studio, or standalone apartment.</p>
                    </div>
                </label>

                <label id="label-multi"
                       class="flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition-all
                              {{ old('property_category') === 'multi' ? 'border-primary-400 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="property_category" value="multi" id="cat-multi"
                           class="mt-0.5 text-primary-600 focus:ring-primary-500"
                           {{ old('property_category') === 'multi' ? 'checked' : '' }}>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-sm font-semibold text-gray-800">Multi Unit</span>
                        </div>
                        <p class="text-xs text-gray-500">Multiple rental units in one building — apartment block, shops, or office complex.</p>
                    </div>
                </label>
            </div>

            {{-- ── Multi-Unit Sub-section ── --}}
            <div id="multi-unit-section" class="{{ old('property_category') === 'multi' ? '' : 'hidden' }} space-y-5">

                <div class="border-t border-gray-100 pt-5">
                    <p class="text-sm font-semibold text-gray-700 mb-1">Floor Layout</p>
                    <p class="text-xs text-gray-400 mb-3">Choose how units are organised in this property.</p>
                    <div class="grid sm:grid-cols-2 gap-3">

                        <label id="label-fl-single"
                               class="flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all
                                      {{ old('floor_layout', 'single_floor') !== 'multi_floor' ? 'border-primary-400 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" name="floor_layout" value="single_floor" id="fl-single"
                                   class="mt-0.5 text-primary-600 focus:ring-primary-500"
                                   {{ old('floor_layout', 'single_floor') !== 'multi_floor' ? 'checked' : '' }}>
                            <div>
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800">Single Floor</span>
                                </div>
                                <p class="text-xs text-gray-500">All units on one level. Ideal for shops, market spaces, ground-floor complexes.</p>
                            </div>
                        </label>

                        <label id="label-fl-multi"
                               class="flex items-start gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all
                                      {{ old('floor_layout') === 'multi_floor' ? 'border-primary-400 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" name="floor_layout" value="multi_floor" id="fl-multi"
                                   class="mt-0.5 text-primary-600 focus:ring-primary-500"
                                   {{ old('floor_layout') === 'multi_floor' ? 'checked' : '' }}>
                            <div>
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800">Multi-Floor</span>
                                </div>
                                <p class="text-xs text-gray-500">Units across multiple floors. Ideal for apartment blocks, office towers, multi-storey buildings.</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- ── Single Floor Config ── --}}
                <div id="single-floor-config" class="{{ old('floor_layout') === 'multi_floor' ? 'hidden' : '' }}">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Number of Units / Spaces <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="number_of_units" id="number_of_units"
                                   value="{{ old('number_of_units') }}" min="1" max="500"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                                   placeholder="e.g. 12">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Unit Name Prefix <span class="text-gray-400 font-normal">– optional</span>
                            </label>
                            <input type="text" name="unit_prefix" id="unit_prefix"
                                   value="{{ old('unit_prefix') }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                                   placeholder="e.g. Shop, Room, Apt">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        Units will be named: <span id="prefix-preview" class="font-medium text-gray-600">Unit 1, Unit 2, Unit 3…</span>
                    </p>
                </div>

                {{-- ── Multi-Floor Builder (Number-Based) ── --}}
                <div id="multi-floor-config" class="{{ old('floor_layout') === 'multi_floor' ? '' : 'hidden' }}">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Number of Floors <span class="text-red-400">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" id="num-floors-input" name="num_floors"
                                   value="{{ old('num_floors') }}" min="1" max="50"
                                   class="w-28 px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition text-sm"
                                   placeholder="e.g. 3">
                            <p class="text-xs text-gray-400">Enter the total number of floors in this building.</p>
                        </div>
                    </div>

                    <div id="floor-rows-container" class="space-y-3"></div>

                    <p class="text-xs text-gray-400 mt-3" id="multi-floor-summary"></p>
                </div>

            </div>{{-- /multi-unit-section --}}
        </div>

        {{-- ── Section 3: Property Image ───────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Property Image</h2>

            <div id="drop-zone"
                 class="relative flex flex-col items-center justify-center border-2 border-dashed border-gray-200
                        rounded-xl py-10 px-6 text-center cursor-pointer hover:border-primary-300 hover:bg-primary-50/30
                        transition-all group">
                <input type="file" name="image" id="image-input" accept="image/*"
                       class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">

                <div id="upload-placeholder">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 group-hover:bg-primary-100 flex items-center justify-center mx-auto mb-3 transition-colors">
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600">Click to upload or drag & drop</p>
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP up to 4 MB</p>
                </div>

                <div id="image-preview" class="hidden w-full">
                    <img id="preview-img" src="" alt="" class="max-h-48 mx-auto rounded-xl object-cover shadow-sm">
                    <p id="preview-name" class="text-xs text-gray-500 mt-2"></p>
                </div>
            </div>
        </div>

        {{-- ── Actions ──────────────────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('landlord.properties.index') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 px-6 py-2.5 rounded-xl border border-gray-200 hover:border-gray-300 transition-colors">
                Cancel
            </a>
            <button type="submit" form="property-form"
                    class="bg-primary-500 hover:bg-primary-600 text-white font-semibold px-8 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                Create Property
            </button>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
(function () {

    // ─── Category radio ──────────────────────────────────────────
    const catSingle    = document.getElementById('cat-single');
    const catMulti     = document.getElementById('cat-multi');
    const labelSingle  = document.getElementById('label-single');
    const labelMulti   = document.getElementById('label-multi');
    const multiSection = document.getElementById('multi-unit-section');

    function syncCategory() {
        const isMulti = catMulti.checked;
        labelSingle.classList.toggle('border-primary-400', !isMulti);
        labelSingle.classList.toggle('bg-primary-50',      !isMulti);
        labelSingle.classList.toggle('border-gray-200',     isMulti);
        labelMulti.classList.toggle('border-primary-400',  isMulti);
        labelMulti.classList.toggle('bg-primary-50',       isMulti);
        labelMulti.classList.toggle('border-gray-200',    !isMulti);
        multiSection.classList.toggle('hidden', !isMulti);
    }
    catSingle.addEventListener('change', syncCategory);
    catMulti.addEventListener('change',  syncCategory);
    syncCategory();

    // ─── Floor-layout radio ──────────────────────────────────────
    const flSingle     = document.getElementById('fl-single');
    const flMulti      = document.getElementById('fl-multi');
    const labelFlS     = document.getElementById('label-fl-single');
    const labelFlM     = document.getElementById('label-fl-multi');
    const singleCfg    = document.getElementById('single-floor-config');
    const multiCfg     = document.getElementById('multi-floor-config');
    const numInput     = document.getElementById('number_of_units');

    function syncFloorLayout() {
        const isMF = flMulti.checked;

        labelFlS.classList.toggle('border-primary-400', !isMF);
        labelFlS.classList.toggle('bg-primary-50',      !isMF);
        labelFlS.classList.toggle('border-gray-200',     isMF);
        labelFlM.classList.toggle('border-primary-400',  isMF);
        labelFlM.classList.toggle('bg-primary-50',       isMF);
        labelFlM.classList.toggle('border-gray-200',    !isMF);

        singleCfg.classList.toggle('hidden',  isMF);
        multiCfg.classList.toggle('hidden',  !isMF);

        if (numInput) numInput.required = !isMF && catMulti.checked;

        updateMultiFloorSummary();
    }
    flSingle.addEventListener('change', syncFloorLayout);
    flMulti.addEventListener('change',  syncFloorLayout);

    // ─── Unit prefix live preview ────────────────────────────────
    const prefixInput   = document.getElementById('unit_prefix');
    const prefixPreview = document.getElementById('prefix-preview');

    function updatePrefixPreview() {
        const p = (prefixInput.value.trim()) || 'Unit';
        prefixPreview.textContent = `${p} 1, ${p} 2, ${p} 3…`;
    }
    if (prefixInput) {
        prefixInput.addEventListener('input', updatePrefixPreview);
        updatePrefixPreview();
    }

    // ─── Image preview ───────────────────────────────────────────
    document.getElementById('image-input').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        document.getElementById('preview-img').src = URL.createObjectURL(file);
        document.getElementById('preview-name').textContent = file.name;
        document.getElementById('upload-placeholder').classList.add('hidden');
        document.getElementById('image-preview').classList.remove('hidden');
    });

    // ─── Multi-floor number-based builder ───────────────────────

    function esc(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    const numFloorsInput     = document.getElementById('num-floors-input');
    const floorRowsContainer = document.getElementById('floor-rows-container');
    const multiFloorSummary  = document.getElementById('multi-floor-summary');
    const OLD_FLOOR_CONFIGS  = @json(old('floor_configs', []));

    function generateFloorRows(n) {
        n = Math.min(Math.max(parseInt(n) || 0, 0), 50);
        floorRowsContainer.innerHTML = '';
        for (let i = 0; i < n; i++) {
            const letter   = i < 26 ? String.fromCharCode(65 + i) : String(i + 1);
            const oldCfg   = OLD_FLOOR_CONFIGS[i] || {};
            const nameVal  = oldCfg.name  || (i === 0 ? 'Ground Floor' : 'Floor ' + (i + 1));
            const countVal = oldCfg.count || '';

            const row = document.createElement('div');
            row.className          = 'floor-row border border-gray-200 rounded-xl bg-white overflow-hidden shadow-sm';
            row.dataset.floorIndex = i;

            row.innerHTML = `
<div class="flex items-center gap-3 px-4 py-3 bg-primary-50 border-b border-primary-100">
    <div class="w-7 h-7 rounded-lg bg-primary-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">${letter}</div>
    <input type="text"
           name="floor_configs[${i}][name]"
           value="${esc(nameVal)}"
           placeholder="e.g. Ground Floor, Floor 1"
           required
           class="flex-1 text-sm font-semibold bg-transparent border-0 outline-none text-gray-800 placeholder-gray-400 focus:ring-0 p-0">
    <span class="text-xs text-primary-400 font-medium whitespace-nowrap">Floor ${i + 1}</span>
</div>
<div class="p-4">
    <div class="flex items-start gap-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1.5">Units / Spaces</label>
            <input type="number"
                   name="floor_configs[${i}][count]"
                   value="${esc(countVal)}"
                   min="1" max="200" required
                   class="floor-count-input w-24 px-3 py-2 rounded-lg border border-gray-200 text-sm text-center outline-none focus:border-primary-400 focus:ring-1 focus:ring-primary-100 transition"
                   placeholder="e.g. 5"
                   data-floor-index="${i}"
                   data-letter="${letter}">
        </div>
        <div class="flex-1 min-w-0">
            <label class="block text-xs text-gray-500 mb-1.5">Auto-generated names</label>
            <p class="floor-preview text-xs font-medium text-primary-600 bg-primary-50 border border-primary-100 rounded-lg px-3 py-2 min-h-[2.25rem] flex items-center break-all">
                <span class="text-gray-300 italic">Enter unit count to preview…</span>
            </p>
        </div>
    </div>
</div>`;

            floorRowsContainer.appendChild(row);

            const countInput = row.querySelector('.floor-count-input');
            countInput.addEventListener('input', function () {
                refreshFloorPreview(row, letter, parseInt(this.value) || 0);
                updateMultiFloorSummary();
            });
            if (countVal) refreshFloorPreview(row, letter, parseInt(countVal) || 0);
        }
        updateMultiFloorSummary();
    }

    function refreshFloorPreview(row, letter, count) {
        const preview = row.querySelector('.floor-preview');
        if (count < 1) {
            preview.innerHTML = '<span class="text-gray-300 italic">Enter unit count to preview…</span>';
            return;
        }
        const maxShow = 4;
        const names   = [];
        for (let i = 1; i <= Math.min(count, maxShow); i++) names.push(letter + i);
        let text = names.join(', ');
        if (count > maxShow) text += ', … ' + letter + count;
        preview.textContent = text;
    }

    function updateMultiFloorSummary() {
        const rows = floorRowsContainer ? floorRowsContainer.querySelectorAll('.floor-row') : [];
        let total  = 0;
        rows.forEach(row => { total += parseInt(row.querySelector('.floor-count-input')?.value) || 0; });
        const floors = rows.length;
        if (multiFloorSummary) {
            multiFloorSummary.textContent = floors > 0
                ? total + ' unit' + (total !== 1 ? 's' : '') + ' across ' + floors + ' floor' + (floors !== 1 ? 's' : '') + ' will be created.'
                : '';
        }
    }

    if (numFloorsInput) {
        numFloorsInput.addEventListener('input', function () { generateFloorRows(this.value); });
        @if(old('floor_layout') === 'multi_floor' && old('num_floors'))
        generateFloorRows({{ (int) old('num_floors') }});
        @endif
    }

    // Initial sync (handles page load state for old() category + layout)
    syncFloorLayout();

})();
</script>
@endpush
