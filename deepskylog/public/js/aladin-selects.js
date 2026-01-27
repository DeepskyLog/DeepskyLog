document.addEventListener('DOMContentLoaded', function () {
    function currentPayload(overrides) {
        overrides = overrides || {};
        var inst = null, ep = null, ln = null;
        try { var __el_inst = document.getElementById('aladin-instrument-hidden'); inst = (__el_inst && __el_inst.value) || null; } catch (e) { inst = null; }
        try { var __el_ep = document.getElementById('aladin-eyepiece-hidden'); ep = (__el_ep && __el_ep.value) || null; } catch (e) { ep = null; }
        try { var __el_ln = document.getElementById('aladin-lens-hidden'); ln = (__el_ln && __el_ln.value) || null; } catch (e) { ln = null; }
        var oid = null;
        try {
            var __el_oid = document.getElementById('object-id-hidden'); oid = (__el_oid && __el_oid.value) || null;
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
        } catch (e) { oid = oid || null; }
        // include slug as an additional hint for server-side resolution
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

        var payload = currentPayload(overrideValues || {});
        // Only use the centralized emitter; remove all fallbacks.
        try {
            if (typeof window.__dsl_emitAladinUpdated === 'function') {
                try { window.__dsl_emitAladinUpdated(payload); } catch (e) { }
            }
        } catch (e) { }

    }

    function handleClear(fieldId, emitPayload) {
        try { document.getElementById(fieldId).value = ''; } catch (e) { }
        try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) { }

        try {
            var payload = currentPayload(emitPayload || {});
            // Only use the centralized emitter; remove all fallbacks.
            try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); } } catch (e) { }

        } catch (e) { }
    }

    // Delegated capture-phase listeners for 'selected' and 'clear' custom events.
    // This handles dynamically rendered selects and Livewire re-renders.
    document.addEventListener('selected', function (ev) {
        try {
            var el = ev && ev.target ? ev.target : null;
            // determine which field wrapper this event belongs to
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