<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <script>
            // Early DSL debug: install minimal hooks only when explicitly requested.
            // To enable, append `?dsl_debug=1` to the URL. This avoids expensive
            // global instrumentation during normal page loads which can block UI.
            (function(){
                try {
                    if (!(location && location.search && String(location.search).indexOf('dsl_debug=1') !== -1)) return;

                    var isSuspect = function(s) {
                        try {
                            if (!s && s !== 0) return false;
                            var raw = String(s || '');
                            if (!raw) return false;
                            if (raw.indexOf('/object/') !== -1) return true;
                            if (/m-\d+$/.test(raw)) return true;
                        } catch(e) { return false; }
                        return false;
                    };

                    var origScriptDesc = Object.getOwnPropertyDescriptor(HTMLScriptElement.prototype, 'src');
                    if (origScriptDesc && origScriptDesc.set) {
                        Object.defineProperty(HTMLScriptElement.prototype, 'src', {
                            set: function(v) {
                                try { if (isSuspect(v)) console.warn('[dsl-debug][early] script.src assigned suspicious value', v, '\nStack:', new Error().stack); } catch(e){}
                                return origScriptDesc.set.call(this, v);
                            },
                            get: origScriptDesc.get,
                            configurable: true,
                            enumerable: true
                        });
                    }

                    var origSetAttr = Element.prototype.setAttribute;
                    Element.prototype.setAttribute = function(name, value) {
                        try {
                            if (this && this.tagName && this.tagName.toLowerCase() === 'script' && String(name).toLowerCase() === 'src') {
                                if (isSuspect(value)) console.warn('[dsl-debug][early] setAttribute("src") on script', value, '\nStack:', new Error().stack);
                            }
                        } catch(e){}
                        return origSetAttr.call(this, name, value);
                    };

                    // Observe additions quickly (only while debugging)
                    try {
                        var moEarly = new MutationObserver(function(muts){
                            muts.forEach(function(m){
                                try {
                                    if (m.type === 'childList' && m.addedNodes && m.addedNodes.length) {
                                        m.addedNodes.forEach(function(n){
                                            try {
                                                if (n && n.tagName && n.tagName.toLowerCase() === 'script') {
                                                    var raw = n.getAttribute && n.getAttribute('src');
                                                    if (raw && isSuspect(raw)) console.warn('[dsl-debug][early] added script node', raw, '\nStack:', new Error().stack);
                                                }
                                            } catch(e){}
                                        });
                                    }
                                } catch(e){}
                            });
                        });
                        moEarly.observe(document.documentElement || document, { childList: true, subtree: true });
                    } catch(e){}
                    
                    try {
                        window.addEventListener('error', function(evt) {
                            try {
                                console.warn('[dsl-debug][early error]', evt && evt.message, 'filename=', evt && evt.filename, 'lineno=', evt && evt.lineno, 'colno=', evt && evt.colno, '\nerrorObjStack=', evt && evt.error && evt.error.stack);
                            } catch (inner) {}
                        });
                    } catch(e){}
                } catch(e){}
            })();
        </script>

        <title>{{ config("app.name", "DeepskyLog") }}</title>

        <!-- Fonts -->
        <link
            rel="stylesheet"
            href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap"
        />

        <!-- Scripts -->
        <wireui:scripts />
        @vite(["resources/css/app.css", "resources/js/app.js"])
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
        <style>
            /* Ensure TinyMCE UI is above map and other controls (Leaflet uses high z-index for some controls) */
            .tox, .tox-tinymce, .tox-tinymce-aux, .tox-tinymce-aux .tox, .tox .tox-toolbar, .tox .tox-toolbar__primary {
                z-index: 99999 !important;
            }

            /* Raise common dropdown/listbox/popover elements (WireUI, headlessui, choices, etc.)
               above TinyMCE's blocker. TinyMCE may use a very large z-index (1e15) for some
               auxiliary elements, so we choose a slightly larger value to ensure popovers
               are visible above it. Use !important to override vendor styles. */
            [x-ref="popover"],
            [x-ref="optionsContainer"],
            [role="listbox"],
            .headlessui-listbox__options,
            .listbox__options,
            .select-dropdown,
            [data-listbox],
            .choices__list,
            .dropdown-menu {
                position: relative;
                z-index: 1000000000000001 !important;
            }

            main a[href*="/messages/create"]::after,
            a[href*="/messages/create"]::after {
                content: "DSL";
                display: inline-block;
                margin-left: 0.5rem;
                font-size: 0.65rem;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                padding: 0.12rem 0.4rem;
                border-radius: 9999px;
                background: #2563eb; /* blue-600 */
                color: #ffffff;
                vertical-align: middle;
            }

            /* Make sure small UI links (icons/buttons) don't get overly padded by the pill */
            a.inline-flex[href*="/messages/create"]::after,
            a.inline-flex[href^="mailto:"]::after {
                margin-left: 0.35rem;
                font-size: 0.6rem;
                padding: 0.08rem 0.32rem;
            }
        </style>

        <!-- Styles -->
        @livewireStyles
    </head>

    <body class="font-sans antialiased dark">
        <div class="min-h-[calc(100vh-56px)] bg-gray-800 text-gray-300">
            @livewire("navigation-menu")

            <x-notifications />

            <!-- Page Content -->
                <main>
            <!-- Use full width on xl so large viewports can utilize more horizontal space -->
        <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col lg:flex-row gap-4 w-full items-stretch">
                            @livewire('ephemeris-aside')
                            <div class="flex-1" data-dsl-main-content>
                    {{-- Optional page header slot (used by pages like messages.create) --}}
                    @isset($header)
                        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 py-4">
                            <h1 class="text-xl font-semibold text-gray-100">{{ $header }}</h1>
                        </div>
                    @endisset

                                @if(isset($slot))
                                    {{ $slot }}
                                @else
                                    @yield('content')
                                @endif
                            </div>
                        </div>
                    </div>
                </main>
        </div>

        <footer class="bg-gray-800 shadow-sm">
            <div class="mx-auto flex max-w-(--breakpoint-xl) p-4">
                <div class="w-full md:flex md:items-center md:justify-between">
                    <span
                        class="text-sm text-gray-500 dark:text-gray-400 sm:text-center"
                    >
                        © 2004 - {{ now()->year }} - DeepskyLog - NGC / IC
                        database by
                        <a
                            href="http://www.klima-luft.de/steinicke/index_e.htm"
                            class="hover:underline"
                        >
                            Steinicke
                        </a>
                        , stars Tycho2+ & USNO-UCAC3 -
                        <a
                            href="{{ route("privacy") }}"
                            class="me-4 hover:underline md:me-6"
                        >
                            {{ __("Privacy Policy") }}
                        </a>
                    </span>
                </div>
                <div class="flex flex-row-reverse">
                    <a
                        href="https://www.facebook.com/deepskylog"
                        class="px-2 pt-0.5"
                    >
                        <x-socialstream-icons.facebook class="h-5 w-5" />
                    </a>

                    <a
                        href="https://www.instagram.com/deepskylog.be"
                        class="px-2"
                    >
                        <x-socialstream-icons.instagram class="h-5 w-5" />
                    </a>

                    <a
                        href="https://twitter.com/DeepskyLog"
                        class="px-2 pt-0.5"
                    >
                        <x-socialstream-icons.twitter class="h-5 w-5" />
                    </a>

                    <a
                        href="https://www.youtube.com/channel/UC66H7w2Fl9q3krRy_tHRK5g"
                        class="px-2"
                    >
                        <x-socialstream-icons.youtube class="h-5 w-5" />
                    </a>
                </div>
            </div>
        </footer>

    @stack("scripts")
        @stack("modals")

        @livewireScripts
        <script>
            // Ensure the ephemeris aside matches the main content height by copying min-height.
            (function(){
                function syncEphemHeight(){
                    try{
                        var main = document.querySelector('[data-dsl-main-content]') || document.querySelector('main');
                        var ep = document.querySelector('[data-dsl-ephemeris-aside]');
                        if (!main || !ep) return;
                        // Only force a min-height when the layout is side-by-side (desktop)
                        // Tailwind 'lg' breakpoint is 1024px — avoid setting a large min-height on small screens
                        var isLarge = (window.innerWidth || document.documentElement.clientWidth) >= 1024;
                        if (!isLarge) {
                            // clear any previously set minHeight so the aside flows naturally on stacked layout
                            ep.style.minHeight = '';
                            return;
                        }
                        // Prefer the article/container inside the main content if present
                        var source = main.querySelector('article') || main;
                        var rect = source.getBoundingClientRect();
                        var h = Math.max(0, rect.height || source.offsetHeight || main.offsetHeight || document.documentElement.clientHeight);
                        // Add small padding to ensure we cover margins/footers
                        ep.style.minHeight = (h + 12) + 'px';
                    }catch(e){ console.debug('[dsl-debug] syncEphemHeight failed', e); }
                }

                // Attempt a few syncs to catch delayed Livewire rendering, but keep retries modest
                function scheduleSync(retries){
                    retries = typeof retries === 'number' ? retries : 3;
                    var i = 0;
                    function run(){
                        syncEphemHeight();
                        i++;
                        if (i < retries) setTimeout(run, 180);
                    }
                    run();
                }

                window.addEventListener('load', function(){ scheduleSync(4); }, { passive: true });
                window.addEventListener('resize', function(){ scheduleSync(2); }, { passive: true });

                // Observe mutations that might change main height — limit observation to the main content
                try{
                    var mo = new MutationObserver(function(){ scheduleSync(2); });
                    var observedRoot = document.querySelector('[data-dsl-main-content]') || document.body;
                    mo.observe(observedRoot, { childList: true, subtree: true, attributes: true });
                }catch(e){}

                // If Livewire is present, hook into its `message.processed` events to re-sync after updates
                try{
                    if (window.Livewire && window.Livewire.hook) {
                        window.Livewire.hook('message.processed', function(){ scheduleSync(2); });
                    } else if (window.livewire && window.livewire.on) {
                        // older Livewire global
                        window.livewire.on('message.processed', function(){ scheduleSync(2); });
                    }
                }catch(e){}
            })();
        </script>
        <!-- DSL debug: detect if any script.src is set to the current page path or a route-like value (e.g. '/object/m-31') -->
        <script>
            (function(){
                try {
                    var path = (location && location.pathname) ? location.pathname : '';
                    var slug = null;
                    try { slug = document.getElementById('aladin-lite-container')?.getAttribute('data-slug') || null; } catch(e){}

                    function isSuspectSrc(s) {
                        try {
                            if (!s && s !== 0) return false;
                            var raw = String(s || '');
                            if (raw === path || raw === path.replace(/^\//, '')) return true;
                            if (raw.indexOf('/object/') !== -1) return true;
                            if (/m-\d+$/.test(raw)) return true;
                            if (slug && (raw === slug || raw.indexOf(slug) !== -1)) return true;
                        } catch (e) {
                            return false;
                        }
                        return false;
                    }

                    function report(tag, value, el) {
                        try {
                            console.warn('[dsl-debug]', tag, value, 'location.pathname=', path, 'slug=', slug, '\nStack:', new Error().stack);
                            if (el && el.setAttribute) try { el.setAttribute('data-dsl-flagged', '1'); } catch(e){}
                        } catch(e){}
                    }

                    // 1) scan existing script tags for suspicious src attributes (raw attribute helps find relative values like "m-31")
                    try {
                        var scripts = document.getElementsByTagName('script');
                        for (var i = 0; i < scripts.length; i++) {
                            try {
                                var s = scripts[i];
                                var raw = s.getAttribute && s.getAttribute('src');
                                if (raw && isSuspectSrc(raw)) report('existing script with suspicious src', raw, s);
                            } catch(e){}
                        }
                    } catch(e){}

                    // 2) override the script.src setter so dynamic assignments are detected
                    try {
                        var origDesc = Object.getOwnPropertyDescriptor(HTMLScriptElement.prototype, 'src');
                        if (origDesc && origDesc.set) {
                            Object.defineProperty(HTMLScriptElement.prototype, 'src', {
                                set: function(v) {
                                    try { if (isSuspectSrc(v)) report('script.src setter assigned suspicious value', v, this); } catch(e){}
                                    return origDesc.set.call(this, v);
                                },
                                get: origDesc.get,
                                configurable: true,
                                enumerable: true
                            });
                        }
                    } catch(e) { console.debug('[dsl-debug] failed to wrap script.src', e); }

                    // 3) intercept setAttribute for scripts (some code uses setAttribute rather than property)
                    try {
                        var origSetAttr = Element.prototype.setAttribute;
                        Element.prototype.setAttribute = function(name, value) {
                            try {
                                if (this && this.tagName && this.tagName.toLowerCase() === 'script' && String(name).toLowerCase() === 'src') {
                                    if (isSuspectSrc(value)) report('setAttribute("src") on script', value, this);
                                }
                            } catch(e){}
                            return origSetAttr.call(this, name, value);
                        };
                    } catch(e) { console.debug('[dsl-debug] failed to wrap setAttribute', e); }

                    // 4) observe DOM mutations for newly added script nodes or attribute changes
                    try {
                        var mo = new MutationObserver(function(mutations){
                            mutations.forEach(function(m){
                                try {
                                    if (m.type === 'childList' && m.addedNodes && m.addedNodes.length) {
                                        m.addedNodes.forEach(function(n){
                                            try {
                                                if (n && n.tagName && n.tagName.toLowerCase() === 'script') {
                                                    var raw = n.getAttribute && n.getAttribute('src');
                                                    if (raw && isSuspectSrc(raw)) report('added script node', raw, n);
                                                } else if (n && n.querySelectorAll) {
                                                    var nested = n.querySelectorAll('script');
                                                    for (var j = 0; j < nested.length; j++) {
                                                        try { var raw2 = nested[j].getAttribute && nested[j].getAttribute('src'); if (raw2 && isSuspectSrc(raw2)) report('added nested script', raw2, nested[j]); } catch(e){}
                                                    }
                                                }
                                            } catch(e){}
                                        });
                                    } else if (m.type === 'attributes' && m.attributeName === 'src') {
                                        try { var t = m.target; var raw = t.getAttribute && t.getAttribute('src'); if (raw && isSuspectSrc(raw)) report('mutation attribute src', raw, t); } catch(e){}
                                    }
                                } catch(e){}
                            });
                        });
                        mo.observe(document.documentElement || document, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] });
                    } catch(e) { console.debug('[dsl-debug] failed to install MutationObserver', e); }

                    // 5) on window.load, inspect performance resource entries for any fetched resources that look like page slugs
                    try {
                        window.addEventListener('load', function(){
                            try {
                                if (window.performance && typeof window.performance.getEntriesByType === 'function') {
                                    var entries = window.performance.getEntriesByType('resource') || [];
                                    for (var k = 0; k < entries.length; k++) {
                                        try {
                                            var url = entries[k].name || '';
                                            try {
                                                var p = (new URL(url, location.href)).pathname || '';
                                                if (/\/m-\d+$/.test(p) || /(^|\/)m-\d+$/.test(p.replace(/^\//, ''))) {
                                                    report('resource fetch matches suspicious pattern', url, null);
                                                }
                                            } catch(e) {}
                                        } catch(e){}
                                    }
                                }
                            } catch(e){}
                        });
                    } catch(e) {}

                } catch (e) {
                    console.debug('[dsl-debug] enhanced reporter failed', e);
                }
            })();
        </script>
        <script>
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.like-button');
                if (!btn) return;
                e.preventDefault();
                const type = btn.getAttribute('data-observation-type');
                const id = btn.getAttribute('data-observation-id');

                fetch(<?php echo json_encode(route('observation.like')); ?>, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ observation_type: type, observation_id: id })
                }).then(r => r.json()).then(data => {
                    if (data.count !== undefined) {
                        const countEl = btn.querySelector('.like-count');
                        const iconEl = btn.querySelector('.like-icon');
                        if (countEl) countEl.textContent = data.count;
                        if (iconEl) iconEl.innerHTML = data.status === 'liked' ? '❤️' : '👍';
                    }
                }).catch(() => {
                    // noop
                });
            });
        </script>
        <script>
            // Ensure team-switch links trigger a full navigation even when other
            // libraries (e.g. TinyMCE) add global click handlers that call
            // preventDefault(). We listen in the capture phase and, when a
            // `.dsl-switch-team` anchor is clicked without modifier keys, we
            // stop propagation so downstream listeners cannot turn the click
            // into an XHR/fetch. Respect Ctrl/Meta/Shift/Alt for opening in new
            // tabs/windows or modified behavior.
            document.addEventListener('click', function (e) {
                const el = e.target.closest && e.target.closest('a.dsl-switch-team');
                if (!el) return;

                // Respect modifier keys to allow opening in new tab/window
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                // If it's a normal left-click on the link, ensure native navigation
                // by stopping propagation on the capture phase so other handlers
                // can't prevent the default.
                try {
                    e.stopPropagation();
                } catch (err) {
                    // ignore
                }
                // Let the browser perform the default action (navigation).
            }, true);
        </script>
    </body>
</html>
