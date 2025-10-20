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
            // fallback to embedded globals set by Blade
            if ((!oid || String(oid).trim() === '') && typeof window.__dsl_server_selected !== 'undefined' && window.__dsl_server_selected && window.__dsl_server_selected.objectId) {
                oid = window.__dsl_server_selected.objectId || oid;
            }
            if ((!oid || String(oid).trim() === '') && typeof window.__dsl_embedded_objectId !== 'undefined' && window.__dsl_embedded_objectId) {
                oid = window.__dsl_embedded_objectId || oid;
            }
        } catch(e) { oid = oid || null; }
    // include slug as an additional hint for server-side resolution
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
            try { if (typeof window.__dsl_emitAladinUpdated === 'function') { try { if (window.__dsl_debug_aladin) console.debug('[dsl] used __dsl_emitAladinUpdated ->', payload); } catch(e){} window.__dsl_emitAladinUpdated(payload); return; } } catch(e){}
            try { if (window.__dsl_debug_aladin) console.debug('[dsl] using DOM event ->', payload); } catch(e){}
            try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: payload })); return; } catch(e){}
            // aggressive fallback: find visible Livewire component and call directly
            try {
                var root = document.getElementById('dsl-aladin-preview-info');
                // prefer the published global preview wire id, fallback to DOM lookup
                var wireId = (window.__dsl_preview_wireId || null);
                if (!wireId && root) {
                    wireId = root.getAttribute('wire:id') || root.getAttribute('data-wired-id') || null;
                    if (!wireId) {
                        var possible = root.querySelector('[wire\\:id]');
                        if (possible) wireId = possible.getAttribute('wire:id');
                    }
                }
                if (wireId && window.Livewire && typeof Livewire.find === 'function') {
                    try { Livewire.find(wireId).call('recalculate', payload); if (window.__dsl_debug_aladin) console.debug('[dsl] used Livewire.find ->', payload); return; } catch(e){}
                }
            } catch(e){}
            try { if (window.Livewire && typeof Livewire.dispatch === 'function' && payload && payload.objectId) { try { if (window.__dsl_debug_aladin) console.debug('[dsl] used Livewire.dispatch ->', payload); } catch(e){} Livewire.dispatch('aladinUpdated', payload); return; } } catch(e){}
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
                    if (window.__dsl_debug_aladin) console.debug('[dsl] used dispatchTo ->', payload);
                }
            } catch(e) { /* ignore */ }

            try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); if (window.__dsl_debug_aladin) console.debug('[dsl] used __dsl_emitAladinUpdated ->', payload); } } catch(e){}

            try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: payload })); if (window.__dsl_debug_aladin) console.debug('[dsl] DOM event dispatched ->', payload); } catch(e){}

            try {
                setTimeout(function(){ try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', payload); if (window.__dsl_debug_aladin) console.debug('[dsl] fallback Livewire.dispatch ->', payload); } } catch(e){} }, 40);
            } catch(e) {}

        } catch (e) {}
    }

    // Delegated capture-phase listeners for 'selected' and 'clear' custom events.
    // This handles dynamically rendered selects and Livewire re-renders.
    document.addEventListener('selected', function(ev){
        try {
            var el = ev && ev.target ? ev.target : null;
            // determine which field wrapper this event belongs to
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