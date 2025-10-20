<div>
    <script>
        // Centralized Aladin update emitter + resolver.
        try {
            // server-provided object id (may be null)
            window.__dsl_embedded_objectId = @json($objectId ?? null);

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
            // Queueing emitter: if objectId isn't available yet, queue the emission and
            // process when an object id is found (or a timeout expires). This reduces
            // the race where Livewire mounts without an object id and recalculate()
            // receives null.
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
                            // When we resolve an oid, proactively set it on the Livewire component
                            try { if (window.Livewire && typeof Livewire.dispatchTo === 'function') Livewire.dispatchTo('aladin-preview-info', 'setObjectId', oid); } catch(e){}
                            // drain the queue, enriching each detail
                            while (window.__dsl_aladin_emit_queue.length > 0) {
                                    try {
                                        var d = window.__dsl_aladin_emit_queue.shift();
                                        if (!d || typeof d !== 'object') d = {};
                                        d.objectId = oid;
                                        d.__dsl_enriched = true;
                                        // Prefer dispatching a DOM CustomEvent so the component's Alpine
                                        // @dsl-aladin-updated handler triggers $wire.call('recalculate', ...)
                                        try {
                                            (function(detailToDispatch){
                                                setTimeout(function(){
                                                    try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: detailToDispatch })); } catch(e){}
                                                    try {
                                                        // Prefer global preview wire id when available
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
                                                        // final fallback: try to find any Livewire instance matching component name
                                                        if (window.Livewire && typeof Livewire.components === 'object') {
                                                            for (var k in Livewire.components) {
                                                                try {
                                                                    var inst = Livewire.components[k];
                                                                    if (inst && inst.fingerprint && String(inst.fingerprint.name).toLowerCase() === 'aladin-preview-info') {
                                                                        try { inst.call('recalculate', detailToDispatch); return; } catch(e){}
                                                                    }
                                                                } catch(e) { /* ignore per-item errors */ }
                                                            }
                                                        }
                                                    } catch(e){}
                                                }, 80);
                                            })(d);
                                            continue;
                                        } catch(e) {}
                        }
                        // if we've waited long enough, give up and flush queue with null objectId
                        if (now - start > (timeoutMs || 5000)) {
                            while (window.__dsl_aladin_emit_queue.length > 0) {
                                try {
                                    var d2 = window.__dsl_aladin_emit_queue.shift();
                                    if (!d2 || typeof d2 !== 'object') d2 = {};
                                    d2.objectId = null;
                                    d2.__dsl_enriched = true;
                                    try { if (window.Livewire && typeof Livewire.dispatchTo === 'function') { Livewire.dispatchTo('aladin-preview-info', 'recalculate', d2); continue; } } catch(e){}
                                    if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                        Livewire.dispatch('aladinUpdated', d2);
                                    } else {
                                        try { setTimeout(function(){ try { window.dispatchEvent(new CustomEvent('dsl-aladin-updated', { detail: d2 })); } catch(e){}
                                                            try {
                                                                var root2 = document.getElementById('dsl-aladin-preview-info');
                                                                if (root2) {
                                                                    var wId2 = root2.getAttribute('wire:id') || null;
                                                                    if (!wId2) { var p2 = root2.querySelector('[wire\:id]'); if (p2) wId2 = p2.getAttribute('wire:id'); }
                                                                    if (wId2 && window.Livewire && typeof Livewire.find === 'function') { try { Livewire.find(wId2).call('recalculate', d2); } catch(e){} }
                                                                }
                                                            } catch(e){}
                                                         }, 80); } catch(e){}
                                    }
                                } catch(e) { /* ignore */ }
                            }
                            clearInterval(iv);
                            window.__dsl_aladin_emit_processing = false;
                            return;
                        }
                    } catch(e) { /* ignore */ }
                }, 120);
            }

            window.__dsl_emitAladinUpdated = function(detail) {
                try {
                    if (!detail || typeof detail !== 'object') detail = {};
                    var oid = resolveObjectId(detail.objectId);
                    try { if (window.__dsl_debug_aladin) console.debug('[dsl] emit requested, resolved oid=', oid, 'rawDetail=', detail); } catch(e){}
                    if (oid && String(oid).trim() !== '') {
                        // resolved immediately; set on Livewire and emit
                            try { if (window.Livewire && typeof Livewire.dispatchTo === 'function') Livewire.dispatchTo('aladin-preview-info', 'setObjectId', oid); } catch(e){}
                        detail.objectId = oid;
                        detail.__dsl_enriched = true;
                        try { if (window.__dsl_debug_aladin) console.debug('[dsl] emit enriched ->', detail); } catch(e){}
                        // Prefer direct dispatchTo to call the component method immediately (Livewire v3)
                        try {
                            if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                try { Livewire.dispatchTo('aladin-preview-info', 'recalculate', detail); return; } catch(e) {}
                            }
                        } catch(e) {}
                        if (window.Livewire && typeof Livewire.dispatch === 'function') {
                            Livewire.dispatch('aladinUpdated', detail);
                            return;
                        }
                        // As a final fallback, dispatch a DOM event and attempt to call the specific
                        // Livewire instance directly via its wire:id. This covers cases where
                        // dispatchTo/dispatch do not target the mounted instance due to timing or id mismatch.
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
                                            try { Livewire.find(w).call('recalculate', detail); return; } catch(e) {}
                                        }
                                    }
                                } catch(e){}
                            }, 80);
                        } catch (e) {}
                        return;
                    }
                    // otherwise queue for processing
                    try { if (window.__dsl_debug_aladin) console.debug('[dsl] emit queued', detail); } catch(e){}
                    window.__dsl_aladin_emit_queue.push(detail);
                    processAladinEmitQueue(5000);
                } catch (e) { /* ignore */ }
            };

            // Capture-phase listener - attempt to enrich and forward events; retry a few times if id missing
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

                    // If Livewire not ready prefer a short retry so server component has a chance to be ready
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
                } catch (e) { /* ignore */ }
            }, true);
        } catch (e) { /* ignore */ }
    </script>

    <script src="{{ asset('js/aladin-selects.js') }}"></script>

    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-3">
            <label class="text-xs text-gray-300">{{ __('Instrument:') }}</label>
            <div data-dsl-field="instrument" style="min-width:160px;">
                <x-select
                    :async-data="route('instrument.select.api', ['instrument_set' => $instrumentSet ?? ''])"
                    option-label="name"
                    option-value="id"
                    value="{{ $instrument ?? '' }}"
                    wire:model.live="instrument"
                    placeholder="{{ __('(none)') }}"
                />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <label class="text-xs text-gray-300">{{ __('Eyepiece:') }}</label>
            <div data-dsl-field="eyepiece" style="min-width:160px;">
                <x-select
                    :async-data="route('eyepiece.select.api', ['instrument_set' => $instrumentSet ?? ''])"
                    option-label="name"
                    option-value="id"
                    value="{{ $eyepiece ?? '' }}"
                    wire:model.live="eyepiece"
                    placeholder="{{ __('(none)') }}"
                />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <label class="text-xs text-gray-300">{{ __('Lens:') }}</label>
            <div data-dsl-field="lens" style="min-width:160px;">
                <x-select
                    :async-data="route('lens.select.api', ['instrument_set' => $instrumentSet ?? ''])"
                    option-label="name"
                    option-value="id"
                    value="{{ $lens ?? '' }}"
                    wire:model.live="lens"
                    placeholder="{{ __('(none)') }}"
                />
            </div>
        </div>
    </div>
</div>
