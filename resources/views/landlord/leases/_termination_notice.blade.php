{{--
    Termination notice delivery section — included inside each terminate modal form.
    Adds:
      • notify_tenant   (checkbox, value "1")
      • notice_channel  (radio: "email" | "sms")
--}}
<div class="pt-3 border-t border-gray-100">

    {{-- Toggle checkbox --}}
    <label class="flex items-center gap-3 cursor-pointer select-none group">
        <input type="checkbox"
               name="notify_tenant"
               value="1"
               checked
               onchange="
                   var panel = this.closest('form').querySelector('.notice-channel-panel');
                   if(panel) panel.classList.toggle('hidden', !this.checked);
               "
               class="w-4 h-4 rounded text-red-500 border-gray-300 focus:ring-red-400 cursor-pointer">
        <div>
            <p class="text-sm font-semibold text-gray-700">Send termination notice to tenant</p>
            <p class="text-xs text-gray-400 mt-0.5">Notify the tenant by email or SMS</p>
        </div>
    </label>

    {{-- Channel picker (visible when checkbox is checked) --}}
    <div class="notice-channel-panel mt-3 ml-7">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Deliver notice via</p>
        <div class="grid grid-cols-2 gap-2">

            {{-- Email --}}
            <label class="cursor-pointer">
                <input type="radio" name="notice_channel" value="email" checked class="sr-only peer">
                <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 border-gray-200 bg-white
                            transition-all select-none
                            peer-checked:border-red-400 peer-checked:bg-red-50
                            hover:border-gray-300 hover:bg-gray-50">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs font-semibold text-gray-700">Email</span>
                </div>
            </label>

            {{-- SMS --}}
            <label class="cursor-pointer">
                <input type="radio" name="notice_channel" value="sms" class="sr-only peer">
                <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 border-gray-200 bg-white
                            transition-all select-none
                            peer-checked:border-red-400 peer-checked:bg-red-50
                            hover:border-gray-300 hover:bg-gray-50">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
                    </svg>
                    <span class="text-xs font-semibold text-gray-700">SMS</span>
                </div>
            </label>

        </div>
    </div>

</div>
