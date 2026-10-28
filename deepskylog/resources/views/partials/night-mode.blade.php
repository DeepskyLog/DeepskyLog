{{-- Red night mode: everything shown in red on black, to keep the eyes dark-adapted.
     Two layers over the page: the first takes the colour out (saturation
     blend with grey), the second keeps only the red of what is left
     (multiply with red), so text, charts, photos and maps all turn red. The
     choice is kept per browser (localStorage dsl-night-mode, '1' or '0') and
     applied before the page is painted. $nightDefault turns it on when the
     observer never chose (the page at the eyepiece). --}}
<style>
    html.dsl-night { background: #000; }
    html.dsl-night body::after,
    html.dsl-night::after {
        content: "";
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 2147483647;
    }
    html.dsl-night body::after { background: #808080; mix-blend-mode: saturation; }
    html.dsl-night::after { background: #ff0000; mix-blend-mode: multiply; }
</style>
<script>
    (function () {
        var key = 'dsl-night-mode';
        var fallback = @json(! empty($nightDefault));
        function stored() {
            try { return window.localStorage.getItem(key); } catch (e) { return null; }
        }
        function apply(on) {
            document.documentElement.classList.toggle('dsl-night', on);
            var meta = document.querySelector('meta[name="theme-color"]');
            if (meta) meta.setAttribute('content', on ? '#000000' : '#111827');
            document.querySelectorAll('[data-dsl-night-toggle]').forEach(function (button) {
                button.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
        }
        window.dslNightMode = function () {
            var value = stored();
            return value === null ? fallback : value === '1';
        };
        window.dslToggleNightMode = function () {
            var on = ! window.dslNightMode();
            try { window.localStorage.setItem(key, on ? '1' : '0'); } catch (e) {}
            apply(on);
        };
        apply(window.dslNightMode());
        document.addEventListener('DOMContentLoaded', function () { apply(window.dslNightMode()); });
        // Another tab changed it
        window.addEventListener('storage', function (event) {
            if (event.key === key) apply(window.dslNightMode());
        });
    })();
</script>
