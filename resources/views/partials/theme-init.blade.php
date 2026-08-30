{{--
    Sets the `dark` class on <html> before the first paint, so switching to
    dark mode doesn't flash light mode first. Kept as a tiny inline script
    (not a Vite asset) precisely so it runs before the stylesheet/JS bundle
    are even requested. Defaults to the OS preference until the visitor picks
    an explicit theme via the toggle, which is then remembered in localStorage.

    FIX: this only ran once, on the very first full page load. Livewire's
    wire:navigate swaps <body> without re-running this script, and its own
    navigation cycle was resetting <html>'s class list — every internal
    click silently reverted the theme to light regardless of the stored
    preference. Confirmed live: after forcing dark mode, sampling
    document.documentElement.className every 50ms across a wire:navigate
    click showed it empty for the entire transition, even though
    localStorage still held 'dark'. Re-applying on `livewire:navigated`
    (fired after each such navigation completes) fixes it.
--}}
<script>
    (function () {
        function applyTheme() {
            var stored = localStorage.getItem('theme');
            var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        }
        applyTheme();
        document.addEventListener('livewire:navigated', applyTheme);
    })();
</script>
