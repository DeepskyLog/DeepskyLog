// Auto-generated concatenation of inline scripts

/* /tmp/inline_script_01.js */

// Early DSL debug: install minimal hooks only when explicitly requested.
// To enable, append `?dsl_debug=1` to the URL. This avoids expensive
// global instrumentation during normal page loads which can block UI.
(function () {
    try {
        if (!(location && location.search && String(location.search).indexOf('dsl_debug=1') !== -1)) return;

        var isSuspect = function (s) {
            try {
                if (!s && s !== 0) return false;
                var raw = String(s || '');
                if (!raw) return false;
                if (raw.indexOf('/object/') !== -1) return true;
                if (/m-\d+$/.test(raw)) return true;
            } catch (e) {
                return false;
            }
            return false;
        };

        var origScriptDesc = Object.getOwnPropertyDescriptor(HTMLScriptElement.prototype, 'src');
        if (origScriptDesc && origScriptDesc.set) {
            Object.defineProperty(HTMLScriptElement.prototype, 'src', {
                set: function (v) {
                    try {
                        if (isSuspect(v)) console.warn(
                            '[dsl-debug][early] script.src assigned suspicious value', v,
                            '\nStack:', new Error().stack);
                    } catch (e) { }
                    return origScriptDesc.set.call(this, v);
                },
                get: origScriptDesc.get,
                configurable: true,
                enumerable: true
            });
        }

        var origSetAttr = Element.prototype.setAttribute;
        Element.prototype.setAttribute = function (name, value) {
            try {
                if (this && this.tagName && this.tagName.toLowerCase() === 'script' && String(name)
                    .toLowerCase() === 'src') {
                    if (isSuspect(value)) console.warn('[dsl-debug][early] setAttribute("src") on script',
                        value, '\nStack:', new Error().stack);
                }
            } catch (e) { }
            return origSetAttr.call(this, name, value);
        };

        // Observe additions quickly (only while debugging)
        try {
            var moEarly = new MutationObserver(function (muts) {
                muts.forEach(function (m) {
                    try {
                        if (m.type === 'childList' && m.addedNodes && m.addedNodes.length) {
                            m.addedNodes.forEach(function (n) {
                                try {
                                    if (n && n.tagName && n.tagName.toLowerCase() ===
                                        'script') {
                                        var raw = n.getAttribute && n.getAttribute(
                                            'src');
                                        if (raw && isSuspect(raw)) console.warn(
                                            '[dsl-debug][early] added script node',
                                            raw, '\nStack:', new Error().stack);
                                    }
                                } catch (e) { }
                            });
                        }
                    } catch (e) { }
                });
            });
            moEarly.observe(document.documentElement || document, {
                childList: true,
                subtree: true
            });
        } catch (e) { }

        try {
            window.addEventListener('error', function (evt) {
                try {
                    console.warn('[dsl-debug][early error]', evt && evt.message, 'filename=', evt && evt
                        .filename, 'lineno=', evt && evt.lineno, 'colno=', evt && evt.colno,
                        '\nerrorObjStack=', evt && evt.error && evt.error.stack);
                } catch (inner) { }
            });
        } catch (e) { }
    } catch (e) { }
})();


;

/* /tmp/inline_script_02.js */

window.Wireui = {
    cache: {},
    hook: function (hookName, callback) {
        window.addEventListener('wireui:' + hookName, callback);
    },
    dispatchHook: function (hookName) {
        window.dispatchEvent(new Event('wireui:' + hookName));
    }
};


;

/* /tmp/inline_script_03.js */

// Global flag to enable DSL debug traces when `?dsl_debug=1` is present.
window.__dsl_debug_enabled = !!(location && location.search && String(location.search).indexOf('dsl_debug=1') !== -
    1);


;

/* /tmp/inline_script_04.js */

(function () {
    try {
        // Immediate fallback: decode the inline Base64 payload (if present)
        // and update the top-of-page illuminated fraction so guests and
        // any clients get a fast synchronous update even if other handlers
        // miss the Livewire event. This runs during initial render.
        // If a Moon-specific Livewire component is present on the page,
        // prefer that authoritative path and skip the inline payload fallback.
        if (document.getElementById('dsl-moon-ephem-cell')) return;
        var el = document.getElementById('dsl-ephem-payload');
        if (!el) return;
        var raw = el.getAttribute('data-dsl-ephem-payload');
        if (!raw) return;
        var obj = null;
        try {
            obj = JSON.parse(atob(raw));
        } catch (e) {
            try {
                obj = JSON.parse(raw);
            } catch (e2) {
                obj = null;
            }
        }
        if (!obj) return;
        var illum = (typeof obj.illuminated_fraction !== 'undefined') ? obj.illuminated_fraction : (obj
            .ephemerides && obj.ephemerides.illuminated_fraction ? obj.ephemerides.illuminated_fraction :
            null);
        if (illum !== null && illum !== '' && !isNaN(Number(illum))) {
            var top = document.getElementById('dsl-top-illum');
            if (top) {
                try {
                    top.textContent = (Number(illum) * 100.0).toFixed(1) + '%';
                } catch (e) { }
            }
        }
    } catch (e) { }
})();


;

/* /tmp/inline_script_05.js */

// Legacy comet chart debug logs removed — Livewire handles comet UI now.


;

/* /tmp/inline_script_06.js */

// Ensure the ephemeris aside matches the main content height by copying min-height.
(function () {
    function syncEphemHeight() {
        try {
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
            var h = Math.max(0, rect.height || source.offsetHeight || main.offsetHeight || document
                .documentElement.clientHeight);
            // Add small padding to ensure we cover margins/footers
            ep.style.minHeight = (h + 12) + 'px';
        } catch (e) {
            if (window.__dsl_debug_enabled) console.debug('[dsl-debug] syncEphemHeight failed', e);
        }
    }

    // Attempt a few syncs to catch delayed Livewire rendering, but keep retries modest
    function scheduleSync(retries) {
        retries = typeof retries === 'number' ? retries : 3;
        var i = 0;

        function run() {
            syncEphemHeight();
            i++;
            if (i < retries) setTimeout(run, 180);
        }
        run();
    }

    window.addEventListener('load', function () {
        scheduleSync(4);
    }, {
        passive: true
    });
    window.addEventListener('resize', function () {
        scheduleSync(2);
    }, {
        passive: true
    });

    // Observe mutations that might change main height — limit observation to the main content
    try {
        var mo = new MutationObserver(function () {
            scheduleSync(2);
        });
        var observedRoot = document.querySelector('[data-dsl-main-content]') || document.body;
        mo.observe(observedRoot, {
            childList: true,
            subtree: true,
            attributes: true
        });
    } catch (e) { }

    // If Livewire is present, hook into its `message.processed` events to re-sync after updates
    try {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('message.processed', function () {
                scheduleSync(2);
            });
        } else if (window.livewire && window.livewire.on) {
            // older Livewire global
            window.livewire.on('message.processed', function () {
                scheduleSync(2);
            });
        }
    } catch (e) { }
})();


;

/* /tmp/inline_script_07.js */

// Ensure popovers (WireUI) opened near viewport edges are nudged back into view.
// This helps the datepicker inside the narrow left ephemeris aside which would
// otherwise be shown partly off-screen to the left.
(function () {
    function adjustPopovers() {
        try {
            var pads = 8; // px padding from viewport edges
            var popovers = document.querySelectorAll('[x-ref="popover"]');
            popovers.forEach(function (pop) {
                // visible check
                if (!pop || pop.offsetParent === null) return;

                // find the panel inside the popover (the actual popover panel)
                var panel = pop.querySelector('div[tabindex="-1"]');
                if (!panel) return;

                // store original transform so we can rebase adjustments
                if (!panel.dataset.dslOrigTransform) panel.dataset.dslOrigTransform = panel.style
                    .transform || '';

                // reset any previous adjustment
                panel.style.transform = panel.dataset.dslOrigTransform;

                var rect = panel.getBoundingClientRect();
                var shift = 0;
                if (rect.left < pads) {
                    shift = pads - rect.left;
                } else if (rect.right > (window.innerWidth - pads)) {
                    shift = (window.innerWidth - pads) - rect.right;
                }

                if (shift !== 0) {
                    // apply translateX to move it into view; round to avoid subpixel blurriness
                    var tx = Math.round(shift);
                    panel.style.transform = (panel.dataset.dslOrigTransform ? panel.dataset
                        .dslOrigTransform + ' ' : '') + 'translateX(' + tx + 'px)';
                    panel.dataset.dslAdjusted = tx;
                } else {
                    delete panel.dataset.dslAdjusted;
                }
            });
        } catch (e) {
            if (window.__dsl_debug_enabled) console.debug('[dsl-debug] adjustPopovers failed', e);
        }
    }

    // Run after clicks (opening popovers is usually a click) and on resize
    document.addEventListener('click', function () {
        setTimeout(adjustPopovers, 60);
    }, {
        capture: false,
        passive: true
    });
    window.addEventListener('resize', function () {
        setTimeout(adjustPopovers, 60);
    }, {
        passive: true
    });

    // Also adjust when Livewire messages are processed (popover may open after update)
    try {
        if (window.Livewire && window.Livewire.hook) {
            window.Livewire.hook('message.processed', function () {
                setTimeout(adjustPopovers, 40);
            });
        } else if (window.livewire && window.livewire.on) {
            window.livewire.on('message.processed', function () {
                setTimeout(adjustPopovers, 40);
            });
        }
    } catch (e) { }

    // Initial run in case a popover is already visible
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(adjustPopovers, 120);
    });
    // Expose for manual debugging
    window.DSL_adjustPopovers = adjustPopovers;
})();


;

/* /tmp/inline_script_08.js */

(function () {
    try {
        if (!window.__dsl_debug_enabled) return;
        var path = (location && location.pathname) ? location.pathname : '';
        var slug = null;
        try {
            slug = document.getElementById('aladin-lite-container')?.getAttribute('data-slug') || null;
        } catch (e) { }

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
                console.warn('[dsl-debug]', tag, value, 'location.pathname=', path, 'slug=', slug, '\nStack:',
                    new Error().stack);
                if (el && el.setAttribute) try {
                    el.setAttribute('data-dsl-flagged', '1');
                } catch (e) { }
            } catch (e) { }
        }

        // 1) scan existing script tags for suspicious src attributes (raw attribute helps find relative values like "m-31")
        try {
            var scripts = document.getElementsByTagName('script');
            for (var i = 0; i < scripts.length; i++) {
                try {
                    var s = scripts[i];
                    var raw = s.getAttribute && s.getAttribute('src');
                    if (raw && isSuspectSrc(raw)) report('existing script with suspicious src', raw, s);
                } catch (e) { }
            }
        } catch (e) { }

        // 2) override the script.src setter so dynamic assignments are detected
        try {
            var origDesc = Object.getOwnPropertyDescriptor(HTMLScriptElement.prototype, 'src');
            if (origDesc && origDesc.set) {
                Object.defineProperty(HTMLScriptElement.prototype, 'src', {
                    set: function (v) {
                        try {
                            if (isSuspectSrc(v)) report(
                                'script.src setter assigned suspicious value', v, this);
                        } catch (e) { }
                        return origDesc.set.call(this, v);
                    },
                    get: origDesc.get,
                    configurable: true,
                    enumerable: true
                });
            }
        } catch (e) {
            console.debug('[dsl-debug] failed to wrap script.src', e);
        }

        // 3) intercept setAttribute for scripts (some code uses setAttribute rather than property)
        try {
            var origSetAttr = Element.prototype.setAttribute;
            Element.prototype.setAttribute = function (name, value) {
                try {
                    if (this && this.tagName && this.tagName.toLowerCase() === 'script' && String(name)
                        .toLowerCase() === 'src') {
                        if (isSuspectSrc(value)) report('setAttribute("src") on script', value, this);
                    }
                } catch (e) { }
                return origSetAttr.call(this, name, value);
            };
        } catch (e) {
            console.debug('[dsl-debug] failed to wrap setAttribute', e);
        }

        // 4) observe DOM mutations for newly added script nodes or attribute changes
        try {
            var mo = new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    try {
                        if (m.type === 'childList' && m.addedNodes && m.addedNodes.length) {
                            m.addedNodes.forEach(function (n) {
                                try {
                                    if (n && n.tagName && n.tagName.toLowerCase() ===
                                        'script') {
                                        var raw = n.getAttribute && n.getAttribute(
                                            'src');
                                        if (raw && isSuspectSrc(raw)) report(
                                            'added script node', raw, n);
                                    } else if (n && n.querySelectorAll) {
                                        var nested = n.querySelectorAll('script');
                                        for (var j = 0; j < nested.length; j++) {
                                            try {
                                                var raw2 = nested[j].getAttribute &&
                                                    nested[j].getAttribute('src');
                                                if (raw2 && isSuspectSrc(raw2)) report(
                                                    'added nested script', raw2,
                                                    nested[j]);
                                            } catch (e) { }
                                        }
                                    }
                                } catch (e) { }
                            });
                        } else if (m.type === 'attributes' && m.attributeName === 'src') {
                            try {
                                var t = m.target;
                                var raw = t.getAttribute && t.getAttribute('src');
                                if (raw && isSuspectSrc(raw)) report('mutation attribute src',
                                    raw, t);
                            } catch (e) { }
                        }
                    } catch (e) { }
                });
            });
            mo.observe(document.documentElement || document, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['src']
            });
        } catch (e) {
            console.debug('[dsl-debug] failed to install MutationObserver', e);
        }

        // 5) on window.load, inspect performance resource entries for any fetched resources that look like page slugs
        // Livewire client-side timing hooks (dev-only). Enable by appending ?dsl_debug=1
        (function () {
            try {
                if (!(location && location.search && String(location.search).indexOf('dsl_debug=1') !== -1)) return;
                if (window.Livewire && typeof window.Livewire.hook === 'function') {
                    window.__dsl_livewire_timing = {};
                    window.Livewire.hook('message.sent', function (message) {
                        try {
                            var id = (message && message.id) ? message.id : ('m' + Math.random().toString(36).slice(2, 8));
                            window.__dsl_livewire_timing[id] = { sent: performance.now(), payloadSize: 0 };
                            try { window.__dsl_livewire_timing[id].payloadSize = JSON.stringify(message).length; } catch (e) { window.__dsl_livewire_timing[id].payloadSize = 0; }
                            console.debug('[dsl-debug][livewire] sent', id, 'component=', message && message.component, 'payloadBytes=', window.__dsl_livewire_timing[id].payloadSize);
                            // attach id back so processed hook can correlate
                            try { message.__dsl_id = id; } catch (e) { }
                        } catch (e) { }
                    });

                    window.Livewire.hook('message.processed', function (message) {
                        try {
                            var id = (message && message.__dsl_id) ? message.__dsl_id : null;
                            var now = performance.now();
                            var sentAt = id && window.__dsl_livewire_timing && window.__dsl_livewire_timing[id] ? window.__dsl_livewire_timing[id].sent : null;
                            var payloadSize = id && window.__dsl_livewire_timing && window.__dsl_livewire_timing[id] ? window.__dsl_livewire_timing[id].payloadSize : (message ? (JSON.stringify(message).length || 0) : 0);
                            var dur = sentAt ? Math.round(now - sentAt) : null;
                            console.debug('[dsl-debug][livewire] processed', id, 'component=', message && message.component, 'duration_ms=', dur, 'responseBytes=', (message && message.response && message.response.effects) ? JSON.stringify(message.response).length : payloadSize);
                            if (id && window.__dsl_livewire_timing) try { delete window.__dsl_livewire_timing[id]; } catch (e) { }
                        } catch (e) { }
                    });
                }
            } catch (e) { }
        })();
        try {
            window.addEventListener('load', function () {
                try {
                    if (window.performance && typeof window.performance.getEntriesByType ===
                        'function') {
                        var entries = window.performance.getEntriesByType('resource') || [];
                        for (var k = 0; k < entries.length; k++) {
                            try {
                                var url = entries[k].name || '';
                                try {
                                    var p = (new URL(url, location.href)).pathname || '';
                                    if (/\/m-\d+$/.test(p) || /(^|\/)m-\d+$/.test(p.replace(/^\//,
                                        ''))) {
                                        report('resource fetch matches suspicious pattern', url, null);
                                    }
                                } catch (e) { }
                            } catch (e) { }
                        }
                    }
                } catch (e) { }
            });
        } catch (e) { }

    } catch (e) {
        console.debug('[dsl-debug] enhanced reporter failed', e);
    }
})();


;

/* /tmp/inline_script_09.js */

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.like-button');
    if (!btn) return;
    e.preventDefault();
    const type = btn.getAttribute('data-observation-type');
    const id = btn.getAttribute('data-observation-id');

    fetch("http:\/\/localhost\/observation\/like", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                'content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            observation_type: type,
            observation_id: id
        })
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


;

/* /tmp/inline_script_10.js */

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


;
/* Aladin init fallback: create instance if not present */
(function () {
    function tryInit() {
        try {
            var c = document.getElementById('aladin-lite-container');
            if (!c) return false;
            // If the server marked this object as a comet, do not initialize Aladin here.
            try {
                var objType = (c.getAttribute && c.getAttribute('data-object-type')) || null;
                if (objType && String(objType).toLowerCase() === 'comet') return false;
            } catch (e) { }
            if (window.__dsl_aladin_instance) return true;
            if (typeof A === 'undefined' || typeof A.aladin !== 'function') return false;
            var data = c.getAttribute('data-aladin');
            var opts = {};
            try { if (data) opts = JSON.parse(atob(data)); } catch (e) { }
            // Ensure minimal options: target or ra/dec and fov defaults
            if (!opts.target && (c.getAttribute('data-name') || c.getAttribute('data-slug'))) opts.target = c.getAttribute('data-name') || c.getAttribute('data-slug');
            if (!opts.fov) opts.fov = opts.fov || 0.5;
            try { window.__dsl_aladin_instance = A.aladin('#aladin-lite-container', opts); } catch (e) { if (window.__dsl_debug_enabled && console.warn) console.warn('[dsl] aladin init failed', e); }
            return true;
        } catch (e) { return false; }
    }
    var attempts = 0;
    function waiter() {
        if (tryInit()) return;
        attempts++;
        if (attempts > 100) return;
        setTimeout(waiter, 50);
    }
    if (document.readyState === 'complete' || document.readyState === 'interactive') waiter(); else document.addEventListener('DOMContentLoaded', waiter);
})();
