{{-- Aladin Lite v2 from CDS, loaded without blocking the page: a normal stylesheet or a
     defer script holds back DOMContentLoaded (and Livewire, Alpine) until CDS answers,
     which takes minutes when CDS is down, and an async one the load event, so it is added
     only once the page has loaded. Include after jQuery, which Aladin v2 needs.
     Sets window.__dslAladinReady and fires `dsl:aladin-ready` on document once A is there. --}}
<script>
    (function () {
        function load() {
            var base = 'https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/';
            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = base + 'aladin.min.css';
            document.head.appendChild(css);
            var js = document.createElement('script');
            js.src = base + 'aladin.min.js';
            js.async = true;
            js.onload = function () {
                window.__dslAladinReady = true;
                document.dispatchEvent(new Event('dsl:aladin-ready'));
            };
            document.head.appendChild(js);
        }
        if (document.readyState === 'complete') load(); else window.addEventListener('load', load);
    })();
</script>
