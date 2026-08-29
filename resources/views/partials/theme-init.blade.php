{{--
    Sets the `dark` class on <html> before the first paint, so switching to
    dark mode doesn't flash light mode first. Kept as a tiny inline script
    (not a Vite asset) precisely so it runs before the stylesheet/JS bundle
    are even requested. Defaults to the OS preference until the visitor picks
    an explicit theme via the toggle, which is then remembered in localStorage.
--}}
<script>
    (function () {
        var stored = localStorage.getItem('theme');
        var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', dark);
    })();
</script>
