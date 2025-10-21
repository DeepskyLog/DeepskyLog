(function(){
    // Centralized Aladin update emitter + resolver.
    try {
        // server-provided object id (may be null) - read from DOM if available
        try {
            var __aladinContainer = document.getElementById('aladin-lite-container');
            if (typeof window.__dsl_embedded_objectId === 'undefined' || window.__dsl_embedded_objectId === null) {
                window.__dsl_embedded_objectId = (__aladinContainer && __aladinContainer.getAttribute('data-object-id')) || null;
            }
        } catch(e) { window.__dsl_embedded_objectId = window.__dsl_embedded_objectId || null; }

        if (typeof window.__dsl_server_selected === 'undefined' || !window.__dsl_server_selected) window.__dsl_server_selected = {};
        if ((!window.__dsl_server_selected.objectId || String(window.__dsl_server_selected.objectId).trim() === '') && window.__dsl_embedded_objectId) {
            window.__dsl_server_selected.objectId = window.__dsl_embedded_objectId;
        }

        // Track Livewire readiness so we don't emit too early
        if (typeof window.__dsl_livewire_ready === 'undefined') window.__dsl_livewire_ready = false;
        try { window.addEventListener('livewire:load', function(){ window.__dsl_livewire_ready = true; }); } catch (e) {}

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
                var iv = setInterval(function(){
                try {
                    var now = Date.now();
                    var oid = resolveObjectId();
                    if (oid && String(oid).trim() !== '') {
                        try { if (window.Livewire && typeof Livewire.dispatchTo === 'function') Livewire.dispatchTo('aladin-preview-info', 'setObjectId', oid); } catch(e){}
                        while (window.__dsl_aladin_emit_queue.length > 0) {
                            try {
                                var d = window.__dsl_aladin_emit_queue.shift();
                                if (!d || typeof d !== 'object') d = {};
                                d.objectId = oid;
                                d.__dsl_enriched = true;
                                try {
                                    (function(detailToDispatch){
                                        setTimeout(function(){
                                            try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detailToDispatch })); } catch(e){}
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
                                                    try { Livewire.find(wireId).call('recalculate', detailToDispatch); return; } catch(e){}
                                                }
                                                if (window.Livewire && typeof Livewire.components === 'object') {
                                                    for (var k in Livewire.components) {
                                                        try {
                                                            var inst = Livewire.components[k];
                                                            if (inst && inst.fingerprint && String(inst.fingerprint.name).toLowerCase() === 'aladin-preview-info') {
                                                                try { inst.call('recalculate', detailToDispatch); return; } catch(e){}
                                                            }
                                                        } catch(e) { }
                                                    }
                                                }
                                            } catch(e){}
                                        }, 80);
                                    })(d);
                                    continue;
                                } catch(e) {}
                            } catch(e) { }
                        }
                    }
                    if (now - start > (timeoutMs || 5000)) {
                        while (window.__dsl_aladin_emit_queue.length > 0) {
                            try {
                                var d2 = window.__dsl_aladin_emit_queue.shift();
                                if (!d2 || typeof d2 !== 'object') d2 = {};
                                d2.objectId = null;
                                d2.__dsl_enriched = true;
                                    try { if (window.Livewire && typeof Livewire.dispatchTo === 'function') { 
                                        Livewire.dispatchTo('aladin-preview-info', 'recalculate', d2); 
                                        setTimeout(function(){ try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', d2); } } catch(e){} }, 80);
                                        continue; 
                                    } } catch(e){}
                                if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                    Livewire.dispatch('aladinUpdated', d2);
                                } else {
                                    try { setTimeout(function(){ try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: d2 })); } catch(e){}
                                                            try {
                                                                var root2 = document.getElementById('dsl-aladin-preview-info');
                                                                if (root2) {
                                                                    var wId2 = root2.getAttribute('wire:id') || null;
                                                                    if (!wId2) { var p2 = root2.querySelector('[wire\\:id]'); if (p2) wId2 = p2.getAttribute('wire:id'); }
                                                                    if (wId2 && window.Livewire && typeof Livewire.find === 'function') { try { Livewire.find(wId2).call('recalculate', d2); } catch(e){} }
                                                                }
                                                            } catch(e){}
                                                         }, 80); } catch(e){}
                                }
                            } catch(e) { }
                        }
                        clearInterval(iv);
                        window.__dsl_aladin_emit_processing = false;
                        return;
                    }
                } catch(e) { }
            }, 120);
        }

        window.__dsl_emitAladinUpdated = function(detail) {
            try {
                if (!detail || typeof detail !== 'object') detail = {};
                var oid = resolveObjectId(detail.objectId);
                // attach resolved objectId if available
                if (oid && String(oid).trim() !== '') {
                    try { if (window.Livewire && typeof Livewire.dispatchTo === 'function') Livewire.dispatchTo('aladin-preview-info', 'setObjectId', oid); } catch(e){}
                    detail.objectId = oid;
                }

                // Try immediate delivery paths. If any succeed, return. Otherwise queue for later processing.
                try {
                    if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                        try {
                            Livewire.dispatchTo('aladin-preview-info', 'recalculate', detail);
                            // schedule a delayed targeted call to the specific preview instance in case dispatchTo didn't produce a network request
                            setTimeout(function(){
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
                                        try { Livewire.find(widx).call('recalculate', detail); } catch(e) { }
                                    }
                                } catch(e) { }
                            }, 160);
                            return;
                        } catch(e) { }
                    }
                } catch(e) { }

                try {
                    if (window.Livewire && typeof Livewire.dispatch === 'function') {
                        try { Livewire.dispatch('aladinUpdated', detail); return; } catch(e) { }
                    }
                } catch(e) { }

                // DOM event + Livewire.find fallback after short delay
                try {
                    setTimeout(function(){
                        try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detail })); } catch(e){}
                        try {
                            var r = document.getElementById('dsl-aladin-preview-info');
                            if (r) {
                                var w = r.getAttribute('wire:id') || r.getAttribute('data-wired-id') || null;
                                if (!w) {
                                    var p = r.querySelector('[wire\\:id]'); if (p) w = p.getAttribute('wire:id');
                                }
                                if (w && window.Livewire && typeof Livewire.find === 'function') {
                                    try { Livewire.find(w).call('recalculate', detail); return; } catch(e) { }
                                }
                            }
                        } catch(e){}
                    }, 80);
                } catch (e) {}

                // If immediate attempts didn't return, queue the payload for the emit queue which will attempt periodic delivery
                window.__dsl_aladin_emit_queue.push(detail);
                processAladinEmitQueue(5000);

                // As a last-resort delayed unconditional dispatch (helps when Livewire initializes a bit later)
                try {
                    setTimeout(function(){
                        try {
                            if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                try { Livewire.dispatch('aladinUpdated', detail); } catch(e) { }
                            }
                        } catch(e) {}
                    }, 220);
                } catch(e) {}

            } catch (e) { }
        };

    window.addEventListener('dsl-aladin-updated', function(ev) {
            try {
                var detail = ev && ev.detail ? ev.detail : {};
                if (detail && detail.__dsl_enriched) return;
                var oid = resolveObjectId(detail.objectId);
                var retry = (detail && detail.__dsl_retry) ? detail.__dsl_retry : 0;
                if ((!oid || String(oid).trim() === '') && retry < 6) {
                    detail.__dsl_retry = retry + 1;
                    setTimeout(function(){ try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detail })); } catch(e){} }, 100 + (retry * 120));
                    return;
                }
                if (oid && oid !== '') {
                    detail.objectId = oid;
                    try { detail.__dsl_enriched = true; } catch (e) {}
                    try {
                        if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                            Livewire.dispatchTo('aladin-preview-info', 'setObjectId', oid);
                        }
                    } catch (e) {}
                } else {
                    detail.objectId = null;
                    try { detail.__dsl_enriched = true; } catch (e) {}
                }

                if (window.Livewire && typeof Livewire.dispatch === 'function' && window.__dsl_livewire_ready !== true && retry < 6) {
                    detail.__dsl_retry = retry + 1;
                    setTimeout(function(){ try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detail })); } catch(e){} }, 150 + (retry * 120));
                    return;
                }

                if (window.Livewire && typeof Livewire.dispatch === 'function') {
                    Livewire.dispatch('aladinUpdated', detail);
                    return;
                }
                try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detail })); } catch (e) {}
            } catch (e) { }
        }, true);

    } catch (e) { }
})();

// Additional bootstrapping for the selects -> Livewire integration
(function(){
    document.addEventListener('DOMContentLoaded', function () {
        function currentPayload(overrides) {
            overrides = overrides || {};
            var inst = null, ep = null, ln = null;
            try { inst = document.getElementById('aladin-instrument-hidden')?.value || null; } catch(e) { inst = null; }
            try { ep = document.getElementById('aladin-eyepiece-hidden')?.value || null; } catch(e) { ep = null; }
            try { ln = document.getElementById('aladin-lens-hidden')?.value || null; } catch(e) { ln = null; }
            var oid = null;
            try {
                oid = document.getElementById('object-id-hidden')?.value || null;
                if ((!oid || String(oid).trim() === '') && document.getElementById('aladin-lite-container')) {
                    oid = document.getElementById('aladin-lite-container').getAttribute('data-object-id') || oid;
                }
                if ((!oid || String(oid).trim() === '') && typeof window.__dsl_server_selected !== 'undefined' && window.__dsl_server_selected && window.__dsl_server_selected.objectId) {
                    oid = window.__dsl_server_selected.objectId || oid;
                }
                if ((!oid || String(oid).trim() === '') && typeof window.__dsl_embedded_objectId !== 'undefined' && window.__dsl_embedded_objectId) {
                    oid = window.__dsl_embedded_objectId || oid;
                }
            } catch(e) { oid = oid || null; }
            var slug = null;
            try { slug = document.getElementById('aladin-lite-container')?.getAttribute('data-slug') || null; } catch(e) { slug = null; }
            var payload = { objectId: oid, objectSlug: slug, instrument: inst, eyepiece: ep, lens: ln };
            try { for (var k in overrides) { if (Object.prototype.hasOwnProperty.call(overrides, k)) payload[k] = overrides[k]; } } catch(e){}
            return payload;
        }

        function handleSelected(event, fieldId, overrideValues) {
            var value = event.detail && event.detail.value ? event.detail.value : null;
            try { document.getElementById(fieldId).value = value || ''; } catch (e) {}
            try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) {}
            try { var payload = currentPayload(overrideValues || {});
                try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); return; } } catch(e){}
                try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: payload })); return; } catch(e){}
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
                        try { Livewire.find(wireId).call('recalculate', payload); return; } catch(e){}
                    }
                } catch(e){}
                try { if (window.Livewire && typeof Livewire.dispatch === 'function' && payload && payload.objectId) { Livewire.dispatch('aladinUpdated', payload); return; } } catch(e){}
                // final unconditional fallback: ensure Livewire receives the update even if previous paths failed
                try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', payload); } } catch(e){}
            } catch (e) {}
        }

        function handleClear(fieldId, emitPayload) {
            try { document.getElementById(fieldId).value = ''; } catch (e) {}
            try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) {}

            try {
                var payload = currentPayload(emitPayload || {});

                try {
                    if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                        Livewire.dispatchTo('aladin-preview-info', 'recalculate', payload);
                    }
                } catch(e) { }

                try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); } } catch(e){}

                try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: payload })); } catch(e){}

                try {
                    setTimeout(function(){ try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', payload); } } catch(e){} }, 40);
                    // unconditional fallback as well
                    try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', payload); } } catch(e){}
                } catch(e) {}

            } catch (e) {}
        }

    document.addEventListener('selected', function(ev){
            try {
                var el = ev && ev.target ? ev.target : null;
                var wrapper = el ? el.closest('[data-dsl-field]') : null;
                var field = wrapper ? (wrapper.getAttribute('data-dsl-field') || null) : null;
                if (!field) return;
                if (field === 'instrument') handleSelected(ev, 'aladin-instrument-hidden', { instrument: ev.detail && ev.detail.value ? ev.detail.value : null });
                else if (field === 'eyepiece') handleSelected(ev, 'aladin-eyepiece-hidden', { eyepiece: ev.detail && ev.detail.value ? ev.detail.value : null });
                else if (field === 'lens') handleSelected(ev, 'aladin-lens-hidden', { lens: ev.detail && ev.detail.value ? ev.detail.value : null });
            } catch(e) {}
        }, true);

        document.addEventListener('clear', function(ev){
            try {
                var el = ev && ev.target ? ev.target : null;
                var wrapper = el ? el.closest('[data-dsl-field]') : null;
                var field = wrapper ? (wrapper.getAttribute('data-dsl-field') || null) : null;
                if (!field) return;
                if (field === 'instrument') handleClear('aladin-instrument-hidden', { instrument: null });
                else if (field === 'eyepiece') handleClear('aladin-eyepiece-hidden', { eyepiece: null });
                else if (field === 'lens') handleClear('aladin-lens-hidden', { lens: null });
            } catch(e) {}
        }, true);
    });
})();
