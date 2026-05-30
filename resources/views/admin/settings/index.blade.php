@extends('layouts.admin')
@section('title', 'System Settings')

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
    <div>
        <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
        <p class="text-sm text-gray-500 mt-0.5">Configure global application behaviour and defaults.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Application Settings</h2>
            </div>

            <div class="divide-y divide-gray-50">
                @foreach($settings as $setting)
                <div class="px-5 py-5 flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="sm:w-64 flex-shrink-0">
                        <label for="setting_{{ $setting->key }}" class="block text-sm font-semibold text-gray-700">
                            {{ $setting->label }}
                        </label>
                        @if($setting->description)
                            <p class="text-xs text-gray-400 mt-1">{{ $setting->description }}</p>
                        @endif
                    </div>
                    <div class="flex-1">
                        @if($setting->type === 'boolean')
                            <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                                {{-- Hidden fallback ensures value 0 is submitted when unchecked --}}
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                <div class="relative">
                                    <input type="checkbox"
                                           id="setting_{{ $setting->key }}"
                                           name="settings[{{ $setting->key }}]"
                                           value="1"
                                           class="sr-only peer"
                                           {{ $setting->value ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer
                                                peer-checked:bg-primary-500 transition-colors duration-200"></div>
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow
                                                transition-transform duration-200 peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm text-gray-600">
                                    <span class="toggle-label">{{ $setting->value ? 'Enabled' : 'Disabled' }}</span>
                                </span>
                            </label>
                        @elseif($setting->type === 'textarea')
                            <textarea id="setting_{{ $setting->key }}"
                                      name="settings[{{ $setting->key }}]"
                                      rows="3"
                                      class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800
                                             focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400
                                             resize-none transition placeholder-gray-400">{{ $setting->value }}</textarea>
                        @elseif($setting->type === 'number')
                            <input type="number"
                                   id="setting_{{ $setting->key }}"
                                   name="settings[{{ $setting->key }}]"
                                   value="{{ $setting->value }}"
                                   min="1"
                                   class="w-36 text-sm border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800
                                          focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
                        @else
                            <input type="text"
                                   id="setting_{{ $setting->key }}"
                                   name="settings[{{ $setting->key }}]"
                                   value="{{ $setting->value }}"
                                   class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800
                                          focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition">
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white
                               text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Settings
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[type="checkbox"].sr-only').forEach(function(cb) {
    cb.addEventListener('change', function() {
        const label = this.closest('label').querySelector('.toggle-label');
        if (label) label.textContent = this.checked ? 'Enabled' : 'Disabled';
    });
});
</script>
@endpush
