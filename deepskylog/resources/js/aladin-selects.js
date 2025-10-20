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
        } catch(e) { oid = oid || null; }
        var payload = { objectId: oid, instrument: inst, eyepiece: ep, lens: ln };
        // apply overrides (like instrument/eyepiece/lens from the immediate event)
        try { for (var k in overrides) { if (Object.prototype.hasOwnProperty.call(overrides, k)) payload[k] = overrides[k]; } } catch(e){}
        return payload;
    }

    function handleSelected(event, fieldId, overrideValues) {
        var value = event.detail && event.detail.value ? event.detail.value : null;
        try { document.getElementById(fieldId).value = value || ''; } catch (e) {}
        try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) {}
            try { var payload = currentPayload(overrideValues || {});
                // Prefer central emitter (queueing/enrichment) and DOM event so the component's
                // Alpine handler ($wire.call) runs and triggers a full Livewire update.
                try { if (typeof window.__dsl_emitAladinUpdated === 'function') { try { if (window.__dsl_debug_aladin) console.debug('[dsl] used __dsl_emitAladinUpdated ->', payload); } catch(e){} window.__dsl_emitAladinUpdated(payload); return; } } catch(e){}
                try { if (window.__dsl_debug_aladin) console.debug('[dsl] using DOM event ->', payload); } catch(e){}
                try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: payload })); return; } catch(e){}
                // aggressive fallback: find visible Livewire component and call directly
                try {
                    var root = document.getElementById('dsl-aladin-preview-info');
                    if (root) {
                        var wireId = root.getAttribute('wire:id') || root.getAttribute('data-wired-id') || null;
                        if (!wireId) {
                            var possible = root.querySelector('[wire\:id]');
                            if (possible) wireId = possible.getAttribute('wire:id');
                        }
                        if (wireId && window.Livewire && typeof Livewire.find === 'function') {
                            try { Livewire.find(wireId).call('recalculate', payload); if (window.__dsl_debug_aladin) console.debug('[dsl] used Livewire.find ->', payload); return; } catch(e){}
                        }
                    }
                } catch(e){}
                try { if (window.Livewire && typeof Livewire.dispatch === 'function') { try { if (window.__dsl_debug_aladin) console.debug('[dsl] used Livewire.dispatch ->', payload); } catch(e){} Livewire.dispatch('aladinUpdated', payload); return; } } catch(e){}
            } catch (e) {}
    }

    function handleClear(fieldId, emitPayload) {
        try { document.getElementById(fieldId).value = ''; } catch (e) {}
        try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch (e) {}

        try {
            var payload = currentPayload(emitPayload || {});

            // 1) Prefer direct Livewire dispatchTo (v3) when available
            try {
                if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                    Livewire.dispatchTo('aladin-preview-info', 'recalculate', payload);
                    if (window.__dsl_debug_aladin) console.debug('[dsl] used dispatchTo ->', payload);
                }
            } catch (e) { /* ignore */ }

            // 2) Use the centralized emitter if present (queues/enriches)
            try { if (typeof window.__dsl_emitAladinUpdated === 'function') { window.__dsl_emitAladinUpdated(payload); if (window.__dsl_debug_aladin) console.debug('[dsl] used __dsl_emitAladinUpdated ->', payload); } } catch(e){}

            // 3) Dispatch DOM event for Alpine/Blade listeners
            try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: payload })); if (window.__dsl_debug_aladin) console.debug('[dsl] DOM event dispatched ->', payload); } catch(e){}

            // 4) Final fallback: ensure Livewire.dispatch('aladinUpdated', payload) is invoked
            // after a tiny delay to avoid races with DOM updates.
            try {
                setTimeout(function(){ try { if (window.Livewire && typeof Livewire.dispatch === 'function') { Livewire.dispatch('aladinUpdated', payload); if (window.__dsl_debug_aladin) console.debug('[dsl] fallback Livewire.dispatch ->', payload); } } catch(e){} }, 40);
            } catch(e) {}

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