<div>
    <div class="flex items-center gap-3">
        <label class="text-xs text-gray-300">{{ __('Instrument:') }}</label>
    <div data-dsl-field="instrument" style="min-width:160px;" x-data x-init="(function(){
                    var attempts=0; var maxAttempts=12; var iv=setInterval(function(){
                        try {
                            var sel = $el.querySelector('select');
                            if (sel && sel.value) {
                                // write to hidden input if present
                                try { var h=document.getElementById('aladin-instrument-hidden'); if (h && (!h.value || h.value !== sel.value)) { h.value = sel.value; } } catch(e){}
                                try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch(e){}
                                clearInterval(iv); return;
                            }
                            // Try TomSelect internal instance
                            if (sel && sel.tom && typeof sel.tom.getValue === 'function') {
                                var v = sel.tom.getValue(); if (v) { try { var h2=document.getElementById('aladin-instrument-hidden'); if (h2 && (!h2.value || h2.value !== v)) { h2.value = v; } } catch(e){}; try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch(e){}; clearInterval(iv); return; }
                            }
                        } catch(e){}
                        attempts++; if (attempts>=maxAttempts) clearInterval(iv);
                    }, 120);
                })()">
            <x-select
                :async-data="route('instrument.select.api', ['instrument_set' => $instrumentSet ?? ''])"
                option-label="name"
                option-value="id"
                value="{{ $instrument ?? '' }}"
                wire:model.live="instrument"
                x-on:selected="(function(){ var ts = window.__dsl_last_user_interaction_ts || 0; if ((Date.now() - ts) < 1200) { document.getElementById('aladin-instrument-hidden').value = $event.detail.value; window.scheduleApplyAladinSelectsUpdate(); } })()"
                x-on:clear="(function(){ var ts = window.__dsl_last_user_interaction_ts || 0; if ((Date.now() - ts) < 1200) { document.getElementById('aladin-instrument-hidden').value = ''; window.scheduleApplyAladinSelectsUpdate(); } })()"
                placeholder="{{ __('(none)') }}"
            />
        </div>
    </div>

    <div class="mt-2 flex items-center gap-3">
        <label class="text-xs text-gray-300">{{ __('Eyepiece:') }}</label>
    <div data-dsl-field="eyepiece" style="min-width:160px;" x-data x-init="(function(){
                    var attempts=0; var maxAttempts=12; var iv=setInterval(function(){
                        try {
                            var sel = $el.querySelector('select');
                            if (sel && sel.value) {
                                try { var h=document.getElementById('aladin-eyepiece-hidden'); if (h && (!h.value || h.value !== sel.value)) { h.value = sel.value; } } catch(e){}
                                try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch(e){}
                                clearInterval(iv); return;
                            }
                            if (sel && sel.tom && typeof sel.tom.getValue === 'function') {
                                var v = sel.tom.getValue(); if (v) { try { var h2=document.getElementById('aladin-eyepiece-hidden'); if (h2 && (!h2.value || h2.value !== v)) { h2.value = v; } } catch(e){}; try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch(e){}; clearInterval(iv); return; }
                            }
                        } catch(e){}
                        attempts++; if (attempts>=maxAttempts) clearInterval(iv);
                    }, 120);
                })()">
            <x-select
                :async-data="route('eyepiece.select.api', ['instrument_set' => $instrumentSet ?? ''])"
                option-label="name"
                option-value="id"
                value="{{ $eyepiece ?? '' }}"
                wire:model.live="eyepiece"
                x-on:selected="(function(){ var ts = window.__dsl_last_user_interaction_ts || 0; if ((Date.now() - ts) < 1200) { document.getElementById('aladin-eyepiece-hidden').value = $event.detail.value; window.scheduleApplyAladinSelectsUpdate(); } })()"
                x-on:clear="(function(){ var ts = window.__dsl_last_user_interaction_ts || 0; if ((Date.now() - ts) < 1200) { document.getElementById('aladin-eyepiece-hidden').value = ''; window.scheduleApplyAladinSelectsUpdate(); } })()"
                placeholder="{{ __('(none)') }}"
            />
        </div>
    </div>

    <div class="mt-2 flex items-center gap-3">
        <label class="text-xs text-gray-300">{{ __('Lens:') }}</label>
    <div data-dsl-field="lens" style="min-width:160px;" x-data x-init="(function(){
                    var attempts=0; var maxAttempts=12; var iv=setInterval(function(){
                        try {
                            var sel = $el.querySelector('select');
                            if (sel && sel.value) {
                                try { var h=document.getElementById('aladin-lens-hidden'); if (h && (!h.value || h.value !== sel.value)) { h.value = sel.value; } } catch(e){}
                                try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch(e){}
                                clearInterval(iv); return;
                            }
                            if (sel && sel.tom && typeof sel.tom.getValue === 'function') {
                                var v = sel.tom.getValue(); if (v) { try { var h2=document.getElementById('aladin-lens-hidden'); if (h2 && (!h2.value || h2.value !== v)) { h2.value = v; } } catch(e){}; try { if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate(); } catch(e){}; clearInterval(iv); return; }
                            }
                        } catch(e){}
                        attempts++; if (attempts>=maxAttempts) clearInterval(iv);
                    }, 120);
                })()">
            <x-select
                :async-data="route('lens.select.api', ['instrument_set' => $instrumentSet ?? ''])"
                option-label="name"
                option-value="id"
                value="{{ $lens ?? '' }}"
                wire:model.live="lens"
                x-on:selected="(function(){ var ts = window.__dsl_last_user_interaction_ts || 0; if ((Date.now() - ts) < 1200) { document.getElementById('aladin-lens-hidden').value = $event.detail.value; window.scheduleApplyAladinSelectsUpdate(); } })()"
                x-on:clear="(function(){ var ts = window.__dsl_last_user_interaction_ts || 0; if ((Date.now() - ts) < 1200) { document.getElementById('aladin-lens-hidden').value = ''; window.scheduleApplyAladinSelectsUpdate(); } })()"
                placeholder="{{ __('(none)') }}"
            />
        </div>
    </div>
</div>
