document.addEventListener('DOMContentLoaded', function () {
    function currentPayload(overrides) {
        overrides = overrides || {};
        var inst = null, ep = null, ln = null;
        try { inst = document.getElementById('aladin-instrument-hidden')?.value || null; } catch (e) { inst = null; }
        try { ep = document.getElementById('aladin-eyepiece-hidden')?.value || null; } catch (e) { ep = null; }
        try { ln = document.getElementById('aladin-lens-hidden')?.value || null; } catch (e) { ln = null; }
        var oid = null;
        try {
            oid = document.getElementById('object-id-hidden')?.value || null;
            if ((!oid || String(oid).trim() === '') && document.getElementById('aladin-lite-container')) {
                oid = document.getElementById('aladin-lite-container').getAttribute('data-object-id') || oid;
            }
        } catch (e) { oid = oid || null; }
        var payload = { objectId: oid, instrument: inst, eyepiece: ep, lens: ln };
        // apply overrides (like instrument/eyepiece/lens from the immediate event)
        try { for (var k in overrides) { if (Object.prototype.hasOwnProperty.call(overrides, k)) payload[k] = overrides[k]; } } catch (e) { }
        return payload;
    }

    function handleSelected(event, fieldId, overrideValues) {
        var value = event.detail && event.detail.value ? event.detail.value : null;
        try { document.getElementById(fieldId).value = value || ''; } catch (e) { }
        try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) { }
        try {
            var payload = currentPayload(overrideValues || {});
            // Only use the centralized emitter. Do not fall back to DOM events
            // or direct Livewire calls here; that caused duplicate emissions.
            try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); } } catch (e) { }
        } catch (e) { }
    }

    function handleClear(fieldId, emitPayload) {
        try { document.getElementById(fieldId).value = ''; } catch (e) { }
        try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) { }

        try {
            var payload = currentPayload(emitPayload || {});

            // Only use the centralized emitter; do not invoke other fallbacks here.
            try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); } } catch (e) { }

        } catch (e) { /* ignore */ }
    }

    document.querySelectorAll('[data-dsl-field="instrument"] x-select').forEach(function (select) {
        select.addEventListener('selected', function (event) {
            handleSelected(event, 'aladin-instrument-hidden', { instrument: event.detail && event.detail.value ? event.detail.value : null });
        });
        select.addEventListener('clear', function () { handleClear('aladin-instrument-hidden', { instrument: null }); });
    });

    document.querySelectorAll('[data-dsl-field="eyepiece"] x-select').forEach(function (select) {
        select.addEventListener('selected', function (event) { handleSelected(event, 'aladin-eyepiece-hidden', { eyepiece: event.detail && event.detail.value ? event.detail.value : null }); });
        select.addEventListener('clear', function () { handleClear('aladin-eyepiece-hidden', { eyepiece: null }); });
    });

    document.querySelectorAll('[data-dsl-field="lens"] x-select').forEach(function (select) {
        select.addEventListener('selected', function (event) { handleSelected(event, 'aladin-lens-hidden', { lens: event.detail && event.detail.value ? event.detail.value : null }); });
        select.addEventListener('clear', function () { handleClear('aladin-lens-hidden', { lens: null }); });
    });
});