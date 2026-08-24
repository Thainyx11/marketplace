@if (session('status') || session('error'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)"
         x-show="show" x-transition
         class="fixed top-20 right-4 z-50 max-w-sm w-full"
         style="display: none;">
        @if (session('status'))
            <div class="flex items-start gap-3 bg-white dark:bg-gray-800 border-l-4 border-emerald-500 shadow-lg rounded-lg p-4">
                <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m5.25 2.25a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-gray-700 dark:text-gray-200">{{ session('status') }}</p>
                <button type="button" @click="show = false" class="ms-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-3 bg-white dark:bg-gray-800 border-l-4 border-red-500 shadow-lg rounded-lg p-4">
                <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <p class="text-sm text-gray-700 dark:text-gray-200">{{ session('error') }}</p>
                <button type="button" @click="show = false" class="ms-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
            </div>
        @endif
    </div>
@endif
