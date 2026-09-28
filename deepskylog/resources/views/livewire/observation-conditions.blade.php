<div>
    <div class="mt-4">
        <h3 class="text-sm text-gray-300 mb-2">{{ __('Observation conditions') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-gray-400 mb-1">{{ __('Limiting magnitude') }}</label>
                <input name="limiting_magnitude" type="number" step="0.01" value="{{ $nelm }}" wire:model.debounce.500ms="nelm" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" />
            </div>

            <div>
                <label class="block text-xs text-gray-400 mb-1">{{ __('SQM') }}</label>
                <input name="sqm" type="number" step="0.01" value="{{ $sqm }}" wire:model.debounce.500ms="sqm" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" />
            </div>

            <div>
                <label class="block text-xs text-gray-400 mb-1">{{ __('Bortle') }}</label>
                <select name="bortle" wire:model.debounce.500ms="bortle" class="w-full rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100">
                    <option value="">{{ __('Select Bortle') }}</option>
                    @for ($i = 1; $i <= 9; $i++)
                        <option value="{{ $i }}" @selected((string) $bortle === (string) $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>
    {{-- debug output removed --}}
</div>
<script>
    (function(){
        // Client-side magnitude conversion utilities ported from PHP Magnitude
        const fstOffset = Number(window.__dsl_fst_offset || 0);

        function log10(x){ return Math.log10 ? Math.log10(x) : Math.log(x)/Math.log(10); }

        function nelmToSqm(nelm, fst=0){
            if (nelm === null || nelm === undefined) return null;
            nelm = Number(nelm);
            if (nelm > 8 || nelm < 0) return null;
            const sqm = 21.58 - 5 * log10(Math.pow(10, 1.586 - (nelm + fst) / 5.0) - 1.0);
            return sqm > 22 ? 22.0 : sqm;
        }

        function sqmToNelm(sqm, fst=0){
            if (sqm === null || sqm === undefined) return null;
            sqm = Number(sqm);
            if (sqm > 22.0 || sqm < 10.0) return null;
            let nelm = (7.93 - 5 * log10(1 + Math.pow(10, 4.316 - sqm / 5.0)));
            if (nelm < 2.5) nelm = 2.5;
            return nelm - fst;
        }

        function sqmToBortle(sqm){
            sqm = Number(sqm);
            if (sqm <= 17.5) return 9;
            if (sqm <= 18.0) return 8;
            if (sqm <= 18.5) return 7;
            if (sqm <= 19.1) return 6;
            if (sqm <= 20.4) return 5;
            if (sqm <= 21.3) return 4;
            if (sqm <= 21.5) return 3;
            if (sqm <= 21.7) return 2;
            return 1;
        }

        function nelmToBortle(nelm){
            nelm = Number(nelm);
            if (nelm < 3.6) return 9;
            if (nelm < 3.9) return 8;
            if (nelm < 4.4) return 7;
            if (nelm < 4.9) return 6;
            if (nelm < 5.8) return 5;
            if (nelm < 6.3) return 4;
            if (nelm < 6.4) return 3;
            if (nelm < 6.5) return 2;
            return 1;
        }

        function bortleToNelm(b, fst=0){
            b = Number(b);
            if (b === 1) return 6.6 - fst;
            if (b === 2) return 6.5 - fst;
            if (b === 3) return 6.4 - fst;
            if (b === 4) return 6.1 - fst;
            if (b === 5) return 5.4 - fst;
            if (b === 6) return 4.7 - fst;
            if (b === 7) return 4.2 - fst;
            if (b === 8) return 3.8 - fst;
            if (b === 9) return 3.6 - fst;
            return null;
        }

        function bortleToSqm(b){
            b = Number(b);
            if (b === 1) return 21.85;
            if (b === 2) return 21.6;
            if (b === 3) return 21.4;
            if (b === 4) return 20.85;
            if (b === 5) return 19.75;
            if (b === 6) return 18.8;
            if (b === 7) return 18.25;
            if (b === 8) return 17.75;
            if (b === 9) return 17.5;
            return null;
        }

        let dslProgrammatic = false;

        function toFloat(v){
            if (v === null || v === undefined || v === '') return null;
            v = String(v).replace(',', '.');
            const n = parseFloat(v);
            return Number.isFinite(n) ? n : null;
        }

        function setValue(el, v){
            if(!el) return;
            // mark programmatic update so handlers ignore the resulting events
            dslProgrammatic = true;
            if (v === null || v === undefined) {
                el.value = '';
            } else {
                el.value = v;
            }
            // dispatch appropriate event for the element type
            try {
                if (el.tagName === 'SELECT') {
                    el.dispatchEvent(new Event('change', {bubbles:true}));
                    el.dispatchEvent(new Event('input', {bubbles:true}));
                } else {
                    el.dispatchEvent(new Event('input', {bubbles:true}));
                }
            } finally {
                // allow handlers to run after current call stack
                setTimeout(function(){ dslProgrammatic = false; }, 0);
            }
        }

        function attachHandlers(){
            let nelmInput = document.querySelector('input[name="limiting_magnitude"]');
            let sqmInput = document.querySelector('input[name="sqm"]');
            let bortleSelect = document.querySelector('select[name="bortle"]');

            if (!nelmInput || !sqmInput || !bortleSelect) {
                // Try again shortly if Livewire hasn't rendered inputs yet
                setTimeout(attachHandlers, 200);
                return;
            }

            if (nelmInput.dataset.dslHandlersAdded) return;
            nelmInput.dataset.dslHandlersAdded = '1';
            sqmInput.dataset.dslHandlersAdded = '1';
            bortleSelect.dataset.dslHandlersAdded = '1';

            let tN=null, tS=null;
            nelmInput.addEventListener('input', function(){
                if (dslProgrammatic) return;
                clearTimeout(tN);
                tN = setTimeout(function(){
                    const v = toFloat(nelmInput.value);
                    if (v === null) { setValue(sqmInput, null); setValue(bortleSelect, null); return; }
                    const sqm = nelmToSqm(v, fstOffset);
                    const bortle = nelmToBortle(v);
                    setValue(sqmInput, sqm !== null ? sqm.toFixed(2) : null);
                    setValue(bortleSelect, bortle !== null ? String(bortle) : null);
                }, 300);
            });

            sqmInput.addEventListener('input', function(){
                if (dslProgrammatic) return;
                clearTimeout(tS);
                tS = setTimeout(function(){
                    const v = toFloat(sqmInput.value);
                    if (v === null) { setValue(nelmInput, null); setValue(bortleSelect, null); return; }
                    const nelm = sqmToNelm(v, fstOffset);
                    const bortle = sqmToBortle(v);
                    setValue(nelmInput, nelm !== null ? nelm.toFixed(1) : null);
                    setValue(bortleSelect, bortle !== null ? String(bortle) : null);
                }, 300);
            });

            bortleSelect.addEventListener('change', function(){
                if (dslProgrammatic) return;
                const v = toFloat(bortleSelect.value);
                if (v === null) { setValue(nelmInput, null); setValue(sqmInput, null); return; }
                const nelm = bortleToNelm(v, fstOffset);
                const sqm = bortleToSqm(v);
                setValue(nelmInput, nelm !== null ? nelm.toFixed(1) : null);
                setValue(sqmInput, sqm !== null ? sqm.toFixed(2) : null);
            });

            // global hook used by the select in the create view
            window.updateObservationConditionsFromLocation = function(locationId){
                if (!window.__dsl_locations) return;
                const data = window.__dsl_locations[String(locationId)];
                if (!data) { setValue(nelmInput, null); setValue(sqmInput, null); setValue(bortleSelect, null); return; }
                if (data.sqm !== null) {
                    const sqm = Number(data.sqm);
                    const nelm = sqmToNelm(sqm, fstOffset);
                    const bortle = sqmToBortle(sqm);
                    setValue(sqmInput, sqm.toFixed(2));
                    setValue(nelmInput, nelm !== null ? nelm.toFixed(1) : null);
                    setValue(bortleSelect, bortle !== null ? String(bortle) : null);
                } else if (data.limmag !== null) {
                    const nelm = Number(data.limmag);
                    const sqm = nelmToSqm(nelm, fstOffset);
                    const bortle = nelmToBortle(nelm);
                    setValue(nelmInput, nelm.toFixed(1));
                    setValue(sqmInput, sqm !== null ? sqm.toFixed(2) : null);
                    setValue(bortleSelect, bortle !== null ? String(bortle) : null);
                } else if (data.bortle !== null) {
                    const bortle = Number(data.bortle);
                    const nelm = bortleToNelm(bortle, fstOffset);
                    const sqm = bortleToSqm(bortle);
                    setValue(bortleSelect, String(bortle));
                    setValue(nelmInput, nelm !== null ? nelm.toFixed(1) : null);
                    setValue(sqmInput, sqm !== null ? sqm.toFixed(2) : null);
                }
            }
            // Values prefilled when editing (or kept after a validation error)
            // must not be replaced by those of the preselected location.
            const hasInitialValues = toFloat(nelmInput.value) !== null || toFloat(sqmInput.value) !== null;
            // If a location was selected before this script loaded, apply it now
            if (window.__dsl_pending_location !== undefined && hasInitialValues) {
                delete window.__dsl_pending_location;
            }
            if (window.__dsl_pending_location !== undefined) {
                try {
                    window.updateObservationConditionsFromLocation(window.__dsl_pending_location);
                } finally {
                    delete window.__dsl_pending_location;
                }
            }
            // Also, if the form has a preselected location (hidden input), apply it now
            try {
                const locHidden = document.getElementById('location_hidden');
                if (locHidden && locHidden.value && !hasInitialValues) {
                    window.updateObservationConditionsFromLocation(locHidden.value);
                }
            } catch (e) {
                // ignore
            }
            // If an event was emitted before Livewire was available, try to flush it now
            try {
                if (window.__dsl_pending_event && typeof window.__dsl_emit === 'function') {
                    window.__dsl_emit(window.__dsl_pending_event.name, window.__dsl_pending_event.payload);
                    delete window.__dsl_pending_event;
                }
            } catch (e) {
                // ignore
            }
        }

        // start attaching handlers
        attachHandlers();
    })();
</script>
