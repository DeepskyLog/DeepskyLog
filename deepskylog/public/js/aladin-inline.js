(function () {
    // Centralized Aladin update emitter + resolver.
    try {
        // server-provided object id (may be null) - read from DOM if available
        try {
            var __aladinContainer = document.getElementById('aladin-lite-container');
            if (typeof window.__dsl_embedded_objectId === 'undefined' || window.__dsl_embedded_objectId === null) {
                window.__dsl_embedded_objectId = (__aladinContainer && __aladinContainer.getAttribute('data-object-id')) || null;
            }
        } catch (e) { window.__dsl_embedded_objectId = window.__dsl_embedded_objectId || null; }

        if (typeof window.__dsl_server_selected === 'undefined' || !window.__dsl_server_selected) window.__dsl_server_selected = {};
        if ((!window.__dsl_server_selected.objectId || String(window.__dsl_server_selected.objectId).trim() === '') && window.__dsl_embedded_objectId) {
            window.__dsl_server_selected.objectId = window.__dsl_embedded_objectId;
        }

        // Track Livewire readiness so we don't emit too early
        if (typeof window.__dsl_livewire_ready === 'undefined') window.__dsl_livewire_ready = false;
        try { window.addEventListener('livewire:load', function () { window.__dsl_livewire_ready = true; try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] livewire:load', Date.now()); } catch (e) { } }); } catch (e) { }

        function resolveObjectId(candidate) {
            var oid = candidate;
            try {
                if (!oid || String(oid).trim() === '' || oid === 'null') {
                    if (typeof window.__dsl_embedded_objectId !== 'undefined' && window.__dsl_embedded_objectId !== null) oid = window.__dsl_embedded_objectId;
                    if ((!oid || String(oid).trim() === '') && (window.__dsl_server_selected && window.__dsl_server_selected.objectId)) oid = window.__dsl_server_selected.objectId;
                    var el = document.getElementById('aladin-lite-container');
                    if (el && el.getAttribute) {
                        var a = el.getAttribute('data-object-id');
                        if (a && String(a).trim() !== '') oid = a;
                    }
                }
            } catch (e) { /* ignore */ }
            return oid || null;
        }

        // Emitter with small retry to avoid race with server JS initialization
        window.__dsl_aladin_emit_queue = window.__dsl_aladin_emit_queue || [];
        window.__dsl_aladin_emit_processing = window.__dsl_aladin_emit_processing || false;

        function processAladinEmitQueue(timeoutMs) {
            if (window.__dsl_aladin_emit_processing) return;
            window.__dsl_aladin_emit_processing = true;
            var start = Date.now();
            var iv = setInterval(function () {
                try {
                    var now = Date.now();
                    var oid = resolveObjectId();
                    try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] processAladinEmitQueue tick', { now: now, oid: oid, queue_len: window.__dsl_aladin_emit_queue.length }); } catch (e) { }
                    if (oid && String(oid).trim() !== '') {
                        try { if (window.Livewire && typeof Livewire.dispatchTo === 'function') Livewire.dispatchTo('aladin-preview-info', 'setObjectId', oid); } catch (e) { }
                        while (window.__dsl_aladin_emit_queue.length > 0) {
                            try {
                                var d = window.__dsl_aladin_emit_queue.shift();
                                if (!d || typeof d !== 'object') d = {};
                                d.objectId = oid;
                                d.__dsl_enriched = true;
                                try {
                                    (function (detailToDispatch) {
                                        setTimeout(function () {
                                            try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] dispatching dsl-aladin-updated (delayed)', Date.now(), detailToDispatch); } catch (e) { }
                                            try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detailToDispatch })); } catch (e) { }
                                            try {
                                                var wireId = window.__dsl_preview_wireId || null;
                                                if (!wireId) {
                                                    var root = document.getElementById('dsl-aladin-preview-info');
                                                    if (root) {
                                                        wireId = root.getAttribute('wire:id') || root.getAttribute('data-wired-id') || null;
                                                        if (!wireId) {
                                                            var possible = root.querySelector('[wire\\:id]');
                                                            if (possible) wireId = possible.getAttribute('wire:id');
                                                        }
                                                    }
                                                }
                                                if (wireId && window.Livewire && typeof Livewire.find === 'function') {
                                                    try { Livewire.find(wireId).call('recalculate', detailToDispatch); return; } catch (e) { }
                                                }
                                                if (window.Livewire && typeof Livewire.components === 'object') {
                                                    for (var k in Livewire.components) {
                                                        try {
                                                            var inst = Livewire.components[k];
                                                            if (inst && inst.fingerprint && String(inst.fingerprint.name).toLowerCase() === 'aladin-preview-info') {
                                                                try { inst.call('recalculate', detailToDispatch); return; } catch (e) { }
                                                            }
                                                        } catch (e) { }
                                                    }
                                                }
                                            } catch (e) { }
                                        }, 80);
                                    })(d);
                                    continue;
                                } catch (e) { }
                            } catch (e) { }
                        }
                    }
                    if (now - start > (timeoutMs || 5000)) {
                        while (window.__dsl_aladin_emit_queue.length > 0) {
                            try {
                                var d2 = window.__dsl_aladin_emit_queue.shift();
                                if (!d2 || typeof d2 !== 'object') d2 = {};
                                d2.objectId = null;
                                d2.__dsl_enriched = true;
                                try {
                                    if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                        try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] dispatchTo fallback (timeout)', Date.now(), d2); } catch (e) { }
                                        Livewire.dispatchTo('aladin-preview-info', 'recalculate', d2);
                                        setTimeout(function () { try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', d2); } } catch (e) { } }, 80);
                                        continue;
                                    }
                                } catch (e) { }
                                if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                    Livewire.dispatch('aladinUpdated', d2);
                                } else {
                                    try {
                                        setTimeout(function () {
                                            try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: d2 })); } catch (e) { }
                                            try {
                                                var root2 = document.getElementById('dsl-aladin-preview-info');
                                                if (root2) {
                                                    var wId2 = root2.getAttribute('wire:id') || null;
                                                    if (!wId2) { var p2 = root2.querySelector('[wire\\:id]'); if (p2) wId2 = p2.getAttribute('wire:id'); }
                                                    if (wId2 && window.Livewire && typeof Livewire.find === 'function') { try { Livewire.find(wId2).call('recalculate', d2); } catch (e) { } }
                                                }
                                            } catch (e) { }
                                        }, 80);
                                    } catch (e) { }
                                }
                            } catch (e) { }
                        }
                        try { console.debug && console.debug('[dsl] processAladinEmitQueue complete', Date.now()); } catch (e) { }
                        clearInterval(iv);
                        window.__dsl_aladin_emit_processing = false;
                        return;
                    }
                } catch (e) { }
            }, 120);
        }

        window.__dsl_emitAladinUpdated = function (detail) {
            try {
                if (!detail || typeof detail !== 'object') detail = {};
                var oid = resolveObjectId(detail.objectId);
                // attach resolved objectId if available
                if (oid && String(oid).trim() !== '') {
                    try { if (window.Livewire && typeof Livewire.dispatchTo === 'function') Livewire.dispatchTo('aladin-preview-info', 'setObjectId', oid); } catch (e) { }
                    detail.objectId = oid;
                }

                try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] __dsl_emitAladinUpdated called', Date.now(), detail); } catch (e) { }
                // Try immediate delivery paths. If any succeed, return. Otherwise queue for later processing.
                try {
                    if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                        try {
                            Livewire.dispatchTo('aladin-preview-info', 'recalculate', detail);
                            // schedule a delayed targeted call to the specific preview instance in case dispatchTo didn't produce a network request
                            setTimeout(function () {
                                try {
                                    var widx = window.__dsl_preview_wireId || null;
                                    if (!widx) {
                                        var rroot = document.getElementById('dsl-aladin-preview-info');
                                        if (rroot) {
                                            widx = rroot.getAttribute('wire:id') || rroot.getAttribute('data-wired-id') || null;
                                            if (!widx) { var pposs = rroot.querySelector('[wire\\:id]'); if (pposs) widx = pposs.getAttribute('wire:id'); }
                                        }
                                    }
                                    if (widx && window.Livewire && typeof Livewire.find === 'function') {
                                        try { Livewire.find(widx).call('recalculate', detail); } catch (e) { }
                                    }
                                } catch (e) { }
                            }, 160);
                            return;
                        } catch (e) { }
                    }
                } catch (e) { }

                try {
                    if (window.Livewire && typeof Livewire.dispatch === 'function') {
                        try { Livewire.dispatch('aladinUpdated', detail); return; } catch (e) { }
                    }
                } catch (e) { }

                // DOM event + Livewire.find fallback after short delay
                try {
                    setTimeout(function () {
                        try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] __dsl_emitAladinUpdated dispatch fallback (80ms)', Date.now(), detail); } catch (e) { }
                        try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detail })); } catch (e) { }
                        try {
                            var r = document.getElementById('dsl-aladin-preview-info');
                            if (r) {
                                var w = r.getAttribute('wire:id') || r.getAttribute('data-wired-id') || null;
                                if (!w) {
                                    var p = r.querySelector('[wire\\:id]'); if (p) w = p.getAttribute('wire:id');
                                }
                                if (w && window.Livewire && typeof Livewire.find === 'function') {
                                    try { Livewire.find(w).call('recalculate', detail); return; } catch (e) { }
                                }
                            }
                        } catch (e) { }
                    }, 80);
                } catch (e) { }

                // If immediate attempts didn't return, queue the payload for the emit queue which will attempt periodic delivery
                window.__dsl_aladin_emit_queue.push(detail);
                processAladinEmitQueue(5000);

                // As a last-resort delayed unconditional dispatch (helps when Livewire initializes a bit later)
                try {
                    setTimeout(function () {
                        try {
                            try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] last-resort Livewire.dispatch (220ms)', Date.now(), detail); } catch (e) { }
                            if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                try { Livewire.dispatch('aladinUpdated', detail); } catch (e) { }
                            }
                        } catch (e) { }
                    }, 220);
                } catch (e) { }

            } catch (e) { }
        };

        window.addEventListener('dsl-aladin-updated', function (ev) {
            try {
                var detail = ev && ev.detail ? ev.detail : {};
                try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] dsl-aladin-updated event handler start', Date.now(), detail); } catch (e) { }
                if (detail && detail.__dsl_enriched) return;
                var oid = resolveObjectId(detail.objectId);
                var retry = (detail && detail.__dsl_retry) ? detail.__dsl_retry : 0;
                if ((!oid || String(oid).trim() === '') && retry < 6) {
                    detail.__dsl_retry = retry + 1;
                    setTimeout(function () { try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detail })); } catch (e) { } }, 100 + (retry * 120));
                    return;
                }
                if (oid && oid !== '') {
                    detail.objectId = oid;
                    try { detail.__dsl_enriched = true; } catch (e) { }
                    try {
                        if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                            Livewire.dispatchTo('aladin-preview-info', 'setObjectId', oid);
                        }
                    } catch (e) { }
                } else {
                    detail.objectId = null;
                    try { detail.__dsl_enriched = true; } catch (e) { }
                }

                if (window.Livewire && typeof Livewire.dispatch === 'function' && window.__dsl_livewire_ready !== true && retry < 6) {
                    detail.__dsl_retry = retry + 1;
                    setTimeout(function () { try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detail })); } catch (e) { } }, 150 + (retry * 120));
                    return;
                }

                if (window.Livewire && typeof Livewire.dispatch === 'function') {
                    try { if (window.__dsl_debug_enabled && console.debug) console.debug('[dsl] dispatching Livewire.aladinUpdated', Date.now(), detail); } catch (e) { }
                    Livewire.dispatch('aladinUpdated', detail);
                    return;
                }
                try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detail })); } catch (e) { }
            } catch (e) { }
        }, true);

    } catch (e) { }
})();

// Aladin legend updater: compute FoV and Magnification locally and update DOM
(function () {
    function parseBase64JsonAttr(el, name) {
        try {
            var raw = el && el.getAttribute && el.getAttribute(name);
            if (!raw) return null;
            try { return JSON.parse(atob(raw)); } catch (e) { }
            try { return JSON.parse(raw); } catch (e) { }
        } catch (e) { return null; }
    }

    function findById(list, id) {
        if (!list || !id) return null;
        for (var i = 0; i < list.length; i++) {
            try { if (String(list[i].id) === String(id)) return list[i]; } catch (e) { }
        }
        return null;
    }

    function formatArcminFromDeg(deg) {
        if (typeof deg !== 'number' || !isFinite(deg)) return null;
        var arcmin = deg * 60.0;
        return (Math.round(arcmin * 100) / 100).toFixed(2) + "'";
    }

    // Apply computed FoV to the Aladin instance and draw an FoV circle
    function applyFovToAladin(fovDeg) {
        try {
            if (!fovDeg || isNaN(Number(fovDeg))) return;
            var container = document.getElementById('aladin-lite-container');
            if (!container) return;
            var f = Number(fovDeg);
            // Prefer a slightly smaller display FOV so the preview is a bit more zoomed in
            // Use 0.90 to avoid over-zooming; tweakable if needed
            var displayFovDeg = Math.max(0.01, f * 1.05);
            try {
                var cw = container.clientWidth || container.offsetWidth || 300;
                var ch = container.clientHeight || container.offsetHeight || 300;
                var paddingPx = 24;
                if (ch > paddingPx + 10 && f < 2.5 && cw > 0) {
                    var displayFit = f * (cw / Math.max(1, (ch - paddingPx)));
                    // Do not increase displayFovDeg above the base target; allow a smaller fit to shrink further.
                    displayFovDeg = Math.min(displayFovDeg, Math.max(0.01, displayFit));
                }
            } catch (e) { /* ignore sizing */ }

            var setOnInstance = function (inst) {
                try {
                    if (!inst) return;
                    try { inst.__dslEyeFov = f; } catch (e) { }
                    try { if (typeof inst.setFov === 'function') inst.setFov(displayFovDeg); } catch (e) { }
                    try {
                        // Prefer the numeric values encoded in the data-aladin payload (base64 JSON)
                        var defaults = parseBase64JsonAttr(container, 'data-aladin') || {};
                        var raDeg = null;
                        var decDeg = null;
                        try {
                            if (defaults && typeof defaults.ra_raw !== 'undefined' && defaults.ra_raw !== null) {
                                // ra_raw is in hours; convert to degrees
                                raDeg = Number(defaults.ra_raw) * 15.0;
                            }
                            if (defaults && typeof defaults.dec_raw !== 'undefined' && defaults.dec_raw !== null) {
                                decDeg = Number(defaults.dec_raw);
                            }
                        } catch (e) { }
                        // Fallback: parse attributes (may be in HMS/DMS form)
                        if ((raDeg === null || !isFinite(raDeg)) && container.getAttribute('data-ra')) {
                            var rawRa = container.getAttribute('data-ra');
                            try {
                                // Try numeric first
                                var pf = parseFloat(rawRa);
                                if (!isNaN(pf) && pf !== 0) {
                                    raDeg = pf;
                                } else if (/h|m|s/.test(rawRa)) {
                                    // parse HMS like 00h42m44.3s or 00:42:44.3
                                    var parts = rawRa.replace(/h|m|s/g, ':').replace(/\s+/g, '').split(':').filter(Boolean);
                                    if (parts.length >= 3) {
                                        var hh = parseFloat(parts[0]) || 0;
                                        var mm = parseFloat(parts[1]) || 0;
                                        var ss = parseFloat(parts[2]) || 0;
                                        raDeg = (hh + (mm / 60) + (ss / 3600)) * 15.0;
                                    }
                                }
                            } catch (e) { }
                        }
                        if ((decDeg === null || !isFinite(decDeg)) && container.getAttribute('data-dec')) {
                            var rawDec = container.getAttribute('data-dec');
                            try {
                                // strip HTML entities like &amp;#039; and &amp;quot;
                                var txt = rawDec.replace(/&amp;#x27;|&amp;#039;|&amp;quot;|&amp;quot;|&quot;/g, "'").replace(/&amp;/g, '&');
                                // parse DMS like 41°16'05.9" or 41:16:05.9
                                var dms = txt.replace(/°|\u00B0/g, ':').replace(/["'′″]/g, ':').replace(/\s+/g, '').split(':').filter(Boolean);
                                if (dms.length >= 3) {
                                    var dd = parseFloat(dms[0]) || 0;
                                    var mm2 = parseFloat(dms[1]) || 0;
                                    var ss2 = parseFloat(dms[2]) || 0;
                                    // handle possible leading +/-
                                    var sign = (String(dms[0]).trim().charAt(0) === '-') ? -1 : 1;
                                    decDeg = sign * (Math.abs(dd) + (mm2 / 60) + (ss2 / 3600));
                                } else {
                                    var pf2 = parseFloat(rawDec);
                                    if (!isNaN(pf2)) decDeg = pf2;
                                }
                            } catch (e) { }
                        }
                        if (raDeg !== null && decDeg !== null) addFovCircle(inst, raDeg, decDeg, f);
                    } catch (e) { }
                } catch (e) { }
            };

            // find aladin instance (common global set by init)
            var inst = window.__dsl_aladin_instance || null;
            if (inst) {
                setOnInstance(inst);
                return;
            }

            // retry a few times in case aladin hasn't finished initializing yet
            var retries = 0;
            var retryFn = function () {
                try {
                    inst = window.__dsl_aladin_instance || null;
                    if (inst) {
                        setOnInstance(inst);
                        return;
                    }
                    retries++;
                    if (retries < 8) setTimeout(retryFn, 180);
                } catch (e) { }
            };
            retryFn();

        } catch (e) { }
    }

    function addFovCircle(aladinInstance, ra, dec, eyeFovDeg) {
        try {
            if (!aladinInstance) return;
            // If eyeFovDeg not provided, draw a fixed-size circle (0.2 deg)
            var eyeFov = (eyeFovDeg && !isNaN(Number(eyeFovDeg))) ? Number(eyeFovDeg) : 0.2;
            var Alib = window.A || null;
            var radius = Number(eyeFov) / 2.0; // degrees
            var circleOpts = { color: 'lime', lineWidth: 3, opacity: 0.95 };

            // remove any previous overlay/catalog created by us
            try {
                if (aladinInstance.removeOverlay) {
                    try { aladinInstance.removeOverlay('fov-overlay'); } catch (e) { }
                }
                if (aladinInstance.removeCatalog) {
                    try { aladinInstance.removeCatalog('fov-markers'); } catch (e) { }
                }
                // remove any DOM overlay we may have added
                try { var existingDom = document.getElementById('aladin-fov-dom'); if (existingDom && existingDom.parentNode) existingDom.parentNode.removeChild(existingDom); } catch (e) { }
                // clear any previous interval watcher attached to the instance
                try { if (aladinInstance.__dslFovInterval) { clearInterval(aladinInstance.__dslFovInterval); aladinInstance.__dslFovInterval = null; } } catch (e) { }
            } catch (e) { }

            if (Alib && typeof Alib.graphicOverlay === 'function' && typeof Alib.circle === 'function') {
                try {
                    var circ = Alib.circle(ra, dec, radius, circleOpts);
                    var overlay = Alib.graphicOverlay({ name: 'fov-overlay' });
                    if (overlay && typeof overlay.add === 'function') {
                        overlay.add(circ);
                        try { aladinInstance.addOverlay(overlay); } catch (e) { }
                        return;
                    }
                } catch (e) { }
            }

            // Always add a simple DOM-based overlay as a guaranteed visible fallback
            try {
                var containerEl = document.getElementById('aladin-lite-container');
                if (containerEl) {
                    var minDim = Math.min(containerEl.clientWidth || 300, containerEl.clientHeight || 300);
                    var sizePx = Math.min(Math.round(minDim * 0.5), 320);
                    var dom = document.createElement('div');
                    dom.id = 'aladin-fov-dom';
                    dom.style.position = 'absolute';
                    dom.style.pointerEvents = 'none';
                    dom.style.boxSizing = 'border-box';
                    dom.style.border = '3px solid rgba(0,255,0,0.9)';
                    dom.style.borderRadius = '50%';
                    dom.style.width = sizePx + 'px';
                    dom.style.height = sizePx + 'px';
                    dom.style.left = '50%';
                    dom.style.top = '50%';
                    dom.style.transform = 'translate(-50%,-50%)';
                    dom.style.zIndex = 5;
                    containerEl.style.position = containerEl.style.position || 'relative';
                    containerEl.style.overflow = containerEl.style.overflow || 'hidden';
                    containerEl.appendChild(dom);
                    try {
                        // clear any previous watcher
                        try { if (aladinInstance.__dslFovInterval) { clearInterval(aladinInstance.__dslFovInterval); aladinInstance.__dslFovInterval = null; } } catch (e) { }
                        aladinInstance.__dslFovInterval = setInterval(function () {
                            try {
                                var fovArr = (typeof aladinInstance.getFov === 'function') ? aladinInstance.getFov() : null;
                                var currentDisplay = null;
                                if (fovArr && fovArr.length) {
                                    var nums = fovArr.map(function (v) { return Number(v); }).filter(function (n) { return !isNaN(n); });
                                    if (nums.length) currentDisplay = nums[0];
                                }
                                if (!currentDisplay && typeof aladinInstance.__dslLastDisplayFov === 'number') currentDisplay = aladinInstance.__dslLastDisplayFov;
                                var eye = (typeof aladinInstance.__dslEyeFov === 'number') ? aladinInstance.__dslEyeFov : eyeFov;
                                var w = containerEl.clientWidth || containerEl.offsetWidth || 300;
                                var h = containerEl.clientHeight || containerEl.offsetHeight || 300;
                                var minD = Math.min(w, h);
                                var radiusPx = (currentDisplay && eye) ? ((eye / currentDisplay) * (minD / 2)) : (minD * 0.25);
                                var size = Math.round(radiusPx * 2);
                                // If the circle diameter is larger than the container's smaller dimension, hide it but keep watching
                                if (size > minD) {
                                    try { dom.style.display = 'none'; } catch (e) { }
                                } else {
                                    try { dom.style.display = 'block'; } catch (e) { }
                                    dom.style.width = size + 'px';
                                    dom.style.height = size + 'px';
                                    var borderPx = Math.max(1, Math.round(Math.min(6, size / 80)));
                                    dom.style.border = borderPx + 'px solid rgba(0,255,0,0.95)';
                                }
                            } catch (e) { }
                        }, 250);
                    } catch (e) { }
                }
            } catch (e) { }

            // Fallback: approximate circle with markers
            var points = [];
            var steps = 72; // smoother ring
            var decRad = dec * Math.PI / 180.0;
            var visColor = 'lime';
            var visSize = 24;
            for (var i = 0; i < steps; i++) {
                var theta = (i / steps) * 2.0 * Math.PI;
                var dDec = radius * Math.sin(theta);
                var dRa = (radius * Math.cos(theta)) / Math.max(0.0001, Math.cos(decRad));
                var pRa = ra + dRa;
                var pDec = dec + dDec;
                try {
                    if (Alib && typeof Alib.marker === 'function') {
                        var m = Alib.marker([pRa, pDec], { color: visColor, size: visSize });
                        points.push(m);
                    }
                } catch (e) { }
            }
            if (points.length === 0) return;
            try {
                if (Alib && typeof Alib.catalog === 'function') {
                    var cat = Alib.catalog({ name: 'fov-markers', color: visColor });
                    cat.addSources(points);
                    try { aladinInstance.addCatalog(cat); } catch (e) { }
                }
            } catch (e) { }
        } catch (e) { }
    }

    function updateLegend(detail) {
        try {
            var container = document.getElementById('aladin-lite-container');
            if (!container) return;
            var avail = parseBase64JsonAttr(container, 'data-available') || {};
            var defaults = parseBase64JsonAttr(container, 'data-aladin') || {};

            // determine selected ids: prefer incoming detail when present (even if null), then hidden inputs, then server-selected attributes
            function pickSelected(detailObj, key, hiddenId, dataAttr, defaultVal) {
                try {
                    if (detailObj && Object.prototype.hasOwnProperty.call(detailObj, key)) return detailObj[key];
                    var h = document.getElementById(hiddenId);
                    if (h && typeof h.value !== 'undefined' && String(h.value).trim() !== '') return h.value;
                    var a = container.getAttribute(dataAttr);
                    if (a && String(a).trim() !== '') return a;
                    if (typeof defaultVal !== 'undefined') return defaultVal;
                } catch (e) { }
                return null;
            }

            var instId = pickSelected(detail, 'instrument', 'aladin-instrument-hidden', 'data-selected-instrument', (defaults.instrument && defaults.instrument.id) || null);
            var epId = pickSelected(detail, 'eyepiece', 'aladin-eyepiece-hidden', 'data-selected-eyepiece', (defaults.eyepiece && defaults.eyepiece.id) || null);
            var lensId = pickSelected(detail, 'lens', 'aladin-lens-hidden', 'data-selected-lens', null);

            var instMeta = (defaults.instrument && (String(defaults.instrument.id) === String(instId) ? defaults.instrument : null)) || findById(avail.instruments || [], instId) || defaults.instrument || null;
            var epMeta = (defaults.eyepiece && (String(defaults.eyepiece.id) === String(epId) ? defaults.eyepiece : null)) || findById(avail.eyepieces || [], epId) || defaults.eyepiece || null;
            var lensMeta = findById(avail.lenses || [], lensId) || null;

            var mag = null;
            try {
                if (instMeta && instMeta.fixedMagnification) { mag = Number(instMeta.fixedMagnification) || null; }
                if (!mag && instMeta && instMeta.focal_length_mm && epMeta && epMeta.focal_length_mm) {
                    var lf = lensMeta && lensMeta.factor ? Number(lensMeta.factor) : 1.0;
                    mag = Math.round((Number(instMeta.focal_length_mm) / Number(epMeta.focal_length_mm)) * lf);
                    if (!isFinite(mag) || mag <= 0) mag = null;
                }
            } catch (e) { mag = null; }

            var fovDeg = null;
            try {
                if (epMeta && epMeta.apparent_fov_deg && mag) {
                    fovDeg = Math.max(0.01, Number(epMeta.apparent_fov_deg) / Number(mag));
                } else if (epMeta && epMeta.apparent_fov_deg && !mag) {
                    fovDeg = Number(epMeta.apparent_fov_deg);
                }
            } catch (e) { fovDeg = null; }

            var fovEl = document.getElementById('aladin-fov');
            var magEl = document.getElementById('aladin-mag');
            if (fovEl) {
                if (fovDeg !== null && isFinite(fovDeg)) fovEl.textContent = formatArcminFromDeg(Number(fovDeg)); else fovEl.textContent = '—';
            }
            if (magEl) {
                if (mag !== null && isFinite(mag)) magEl.textContent = String(mag) + 'x'; else magEl.textContent = '—';
            }
            // Apply to Aladin preview: set display FOV and draw FoV circle
            try { applyFovToAladin(fovDeg); } catch (e) { }
        } catch (e) { }
    }

    // initial update on load
    document.addEventListener('DOMContentLoaded', function () { updateLegend(null); });
    // update when selects dispatch their event
    window.addEventListener('dsl-aladin-updated', function (ev) { try { updateLegend(ev && ev.detail ? ev.detail : null); } catch (e) { } }, true);
    // update when server sends preview info (contains opt mag etc.) — prefer local calculation but allow server-sent mag as fallback
    window.addEventListener('aladin-preview-info-updated', function (ev) { try { var d = ev && ev.detail ? ev.detail : null; if (d && d.optimum_detection_magnification) { var magEl = document.getElementById('aladin-mag'); if (magEl && (!magEl.textContent || magEl.textContent.trim() === '—')) magEl.textContent = String(d.optimum_detection_magnification) + 'x'; } } catch (e) { } }, true);
    // observe hidden inputs in case Livewire updates them directly
    try {
        var obsRoot = document.getElementById('aladin-lite-container') || document.body;
        var mo = new MutationObserver(function (muts) { muts.forEach(function (m) { try { if (m.type === 'attributes' && (m.target && (m.target.id === 'aladin-instrument-hidden' || m.target.id === 'aladin-eyepiece-hidden' || m.target.id === 'aladin-lens-hidden'))) { updateLegend(null); } } catch (e) { } }); });
        mo.observe(document, { attributes: true, subtree: true, attributeFilter: ['value'], });
    } catch (e) { }
})();

// Additional bootstrapping for the selects -> Livewire integration
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        function currentPayload(overrides) {
            overrides = overrides || {};
            var inst = null, ep = null, ln = null;
            try { var __el_inst = document.getElementById('aladin-instrument-hidden'); inst = (__el_inst && __el_inst.value) || null; } catch (e) { inst = null; }
            try { var __el_ep = document.getElementById('aladin-eyepiece-hidden'); ep = (__el_ep && __el_ep.value) || null; } catch (e) { ep = null; }
            try { var __el_ln = document.getElementById('aladin-lens-hidden'); ln = (__el_ln && __el_ln.value) || null; } catch (e) { ln = null; }
            var oid = null;
            try {
                var __el_oid = document.getElementById('object-id-hidden');
                oid = (__el_oid && __el_oid.value) || null;
                if ((!oid || String(oid).trim() === '') && document.getElementById('aladin-lite-container')) {
                    oid = document.getElementById('aladin-lite-container').getAttribute('data-object-id') || oid;
                }
                if ((!oid || String(oid).trim() === '') && typeof window.__dsl_server_selected !== 'undefined' && window.__dsl_server_selected && window.__dsl_server_selected.objectId) {
                    oid = window.__dsl_server_selected.objectId || oid;
                }
                if ((!oid || String(oid).trim() === '') && typeof window.__dsl_embedded_objectId !== 'undefined' && window.__dsl_embedded_objectId) {
                    oid = window.__dsl_embedded_objectId || oid;
                }
            } catch (e) { oid = oid || null; }
            var slug = null;
            try { var __el_slug = document.getElementById('aladin-lite-container'); slug = (__el_slug && __el_slug.getAttribute && __el_slug.getAttribute('data-slug')) || null; } catch (e) { slug = null; }
            var payload = { objectId: oid, objectSlug: slug, instrument: inst, eyepiece: ep, lens: ln };
            try { for (var k in overrides) { if (Object.prototype.hasOwnProperty.call(overrides, k)) payload[k] = overrides[k]; } } catch (e) { }
            return payload;
        }

        function handleSelected(event, fieldId, overrideValues) {
            var value = event.detail && event.detail.value ? event.detail.value : null;
            try { document.getElementById(fieldId).value = value || ''; } catch (e) { }
            try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) { }
            try {
                var payload = currentPayload(overrideValues || {});
                try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); return; } } catch (e) { }
                try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: payload })); return; } catch (e) { }
                try {
                    var root = document.getElementById('dsl-aladin-preview-info');
                    var wireId = (window.__dsl_preview_wireId || null);
                    if (!wireId && root) {
                        wireId = root.getAttribute('wire:id') || root.getAttribute('data-wired-id') || null;
                        if (!wireId) {
                            var possible = root.querySelector('[wire\\:id]');
                            if (possible) wireId = possible.getAttribute('wire:id');
                        }
                    }
                    if (wireId && window.Livewire && typeof Livewire.find === 'function') {
                        try { Livewire.find(wireId).call('recalculate', payload); return; } catch (e) { }
                    }
                } catch (e) { }
                try { if (window.Livewire && typeof Livewire.dispatch === 'function' && payload && payload.objectId) { Livewire.dispatch('aladinUpdated', payload); return; } } catch (e) { }
                // final unconditional fallback: ensure Livewire receives the update even if previous paths failed
                try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', payload); } } catch (e) { }
            } catch (e) { }
        }

        function handleClear(fieldId, emitPayload) {
            try { document.getElementById(fieldId).value = ''; } catch (e) { }
            try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) { }

            try {
                var payload = currentPayload(emitPayload || {});

                try {
                    if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                        Livewire.dispatchTo('aladin-preview-info', 'recalculate', payload);
                    }
                } catch (e) { }

                try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); } } catch (e) { }

                try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: payload })); } catch (e) { }

                try {
                    setTimeout(function () { try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', payload); } } catch (e) { } }, 40);
                    // unconditional fallback as well
                    try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', payload); } } catch (e) { }
                } catch (e) { }

            } catch (e) { }
        }

        document.addEventListener('selected', function (ev) {
            try {
                var el = ev && ev.target ? ev.target : null;
                var wrapper = el ? el.closest('[data-dsl-field]') : null;
                var field = wrapper ? (wrapper.getAttribute('data-dsl-field') || null) : null;
                if (!field) return;
                if (field === 'instrument') handleSelected(ev, 'aladin-instrument-hidden', { instrument: ev.detail && ev.detail.value ? ev.detail.value : null });
                else if (field === 'eyepiece') handleSelected(ev, 'aladin-eyepiece-hidden', { eyepiece: ev.detail && ev.detail.value ? ev.detail.value : null });
                else if (field === 'lens') handleSelected(ev, 'aladin-lens-hidden', { lens: ev.detail && ev.detail.value ? ev.detail.value : null });
            } catch (e) { }
        }, true);

        document.addEventListener('clear', function (ev) {
            try {
                var el = ev && ev.target ? ev.target : null;
                var wrapper = el ? el.closest('[data-dsl-field]') : null;
                var field = wrapper ? (wrapper.getAttribute('data-dsl-field') || null) : null;
                if (!field) return;
                if (field === 'instrument') handleClear('aladin-instrument-hidden', { instrument: null });
                else if (field === 'eyepiece') handleClear('aladin-eyepiece-hidden', { eyepiece: null });
                else if (field === 'lens') handleClear('aladin-lens-hidden', { lens: null });
            } catch (e) { }
        }, true);
    });
})();
