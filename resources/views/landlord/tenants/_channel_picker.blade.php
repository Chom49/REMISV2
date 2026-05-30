{{--
    Invitation Delivery Channel picker.
    Props:
      $formId  – unique suffix for radio input name/id to avoid conflicts ('tin' | 'manual' | 'resend-{id}')
--}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
    <div class="flex items-center gap-2 pb-3 border-b border-gray-100 mb-4">
        <div class="w-7 h-7 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </div>
        <div>
            <h2 class="text-sm font-bold text-gray-800">Invitation Delivery</h2>
            <p class="text-xs text-gray-400">How should the login credentials be sent to the tenant?</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">

        {{-- Email option --}}
        <label class="cursor-pointer">
            <input type="radio" name="channel" value="email"
                   id="channel-email-{{ $formId }}" class="sr-only peer"
                   {{ old('channel', 'email') === 'email' ? 'checked' : '' }}>
            <div class="flex flex-col items-center gap-2 px-4 py-3.5 rounded-xl border-2 border-gray-200
                        bg-white transition-all
                        peer-checked:border-primary-500 peer-checked:bg-primary-50
                        hover:border-gray-300 hover:bg-gray-50 select-none">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center
                            peer-checked:bg-primary-100 transition-colors">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-800">Email</p>
                    <p class="text-xs text-gray-400 mt-0.5">Send to email address</p>
                </div>
            </div>
        </label>

        {{-- SMS option --}}
        <label class="cursor-pointer">
            <input type="radio" name="channel" value="sms"
                   id="channel-sms-{{ $formId }}" class="sr-only peer"
                   {{ old('channel') === 'sms' ? 'checked' : '' }}>
            <div class="flex flex-col items-center gap-2 px-4 py-3.5 rounded-xl border-2 border-gray-200
                        bg-white transition-all
                        peer-checked:border-primary-500 peer-checked:bg-primary-50
                        hover:border-gray-300 hover:bg-gray-50 select-none">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
                    </svg>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-800">SMS</p>
                    <p class="text-xs text-gray-400 mt-0.5">Send to phone number</p>
                </div>
            </div>
        </label>

    </div>
</div>
