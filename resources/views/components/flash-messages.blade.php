{{--
    FIX: several admin Livewire/Volt actions (order status override, settings
    save, user create/edit, category-delete error...) call session()->flash()
    from inside a component method with no redirect afterwards. That flash
    was never visible: this partial lives in the shared layout, outside every
    Livewire component's own render boundary, so a component's AJAX response
    can't touch it — only session('status')/session('error') read at the
    *initial* full-page load ever rendered here. Confirmed live: after
    forcing an order status from the admin "litige" tool, the DOM contained
    no trace of the flashed message at all.

    First fix attempt used an Alpine `x-on:flash-message.window` listener,
    which worked immediately after page load but silently stopped firing
    after *any* Livewire AJAX update happened anywhere on the page (verified
    live: Alpine's own reactive state was still live and mutable via
    Alpine.$data(), but the window listener bound at directive-init time no
    longer reached it — Livewire's morph cycle reinitializes Alpine scopes it
    walks past, orphaning listeners bound to the old one). Plain JS avoids
    that failure mode entirely: Livewire.on() is registered once on the
    Livewire singleton itself, not on a DOM element's Alpine directive, so it
    can't be silently orphaned by a later morph.
--}}
@php
    $initialStatus = session('status');
    $initialError = session('error');
@endphp
<div id="flash-messages" class="fixed top-20 right-4 z-50 max-w-sm w-full hidden">
    <div id="flash-status" class="hidden flex items-start gap-3 bg-white dark:bg-gray-800 border-l-4 border-emerald-500 shadow-lg rounded-lg p-4">
        <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m5.25 2.25a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p id="flash-status-text" class="text-sm text-gray-700 dark:text-gray-200"></p>
        <button type="button" onclick="window.__hideFlashMessage()" class="ms-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
    </div>

    <div id="flash-error" class="hidden flex items-start gap-3 bg-white dark:bg-gray-800 border-l-4 border-red-500 shadow-lg rounded-lg p-4">
        <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <p id="flash-error-text" class="text-sm text-gray-700 dark:text-gray-200"></p>
        <button type="button" onclick="window.__hideFlashMessage()" class="ms-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
    </div>
</div>

<script>
    window.__showFlashMessage = function (message, type) {
        const wrapper = document.getElementById('flash-messages');
        const statusBox = document.getElementById('flash-status');
        const errorBox = document.getElementById('flash-error');
        document.getElementById('flash-status-text').textContent = message;
        document.getElementById('flash-error-text').textContent = message;

        statusBox.classList.toggle('hidden', type === 'error');
        statusBox.classList.toggle('flex', type !== 'error');
        errorBox.classList.toggle('hidden', type !== 'error');
        errorBox.classList.toggle('flex', type === 'error');
        wrapper.classList.remove('hidden');

        clearTimeout(window.__flashMessageTimeout);
        window.__flashMessageTimeout = setTimeout(window.__hideFlashMessage, 5000);
    };

    window.__hideFlashMessage = function () {
        document.getElementById('flash-messages').classList.add('hidden');
    };

    @if ($initialStatus)
        window.__showFlashMessage(@js($initialStatus), 'status');
    @elseif ($initialError)
        window.__showFlashMessage(@js($initialError), 'error');
    @endif

    document.addEventListener('livewire:init', () => {
        Livewire.on('flash-message', (event) => window.__showFlashMessage(event.message, event.type));
    });
</script>
