<div class="col-span-1 md:col-span-2">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div data-dsl-field="instrument">
            <label class="block text-xs text-gray-400 mb-1">{{ __('Instrument') }}</label>
            <x-select
                :async-data="route('instrument.select.api', ['instrument_set' => $instrumentSetId ?? '', 'selected' => $instrument_id ?? ''])"
                option-label="name"
                option-value="id"
                placeholder="{{ __('Select instrument') }}"
                class="w-full"
                x-on:selected="document.getElementById('aladin-instrument-hidden').value = $event.detail.value; window.dslApplyInstrumentLens($event.detail); window.dslComputeMagnification($event.detail.value, document.getElementById('aladin-eyepiece-hidden').value, document.getElementById('aladin-lens-hidden').value)"
                x-on:clear="document.getElementById('aladin-instrument-hidden').value = ''; window.dslComputeMagnification('', document.getElementById('aladin-eyepiece-hidden').value, document.getElementById('aladin-lens-hidden').value)"
                value="{{ $instrument_id ?? '' }}"
            >
                @if(!empty($instrument_id))
                    @php $inst = \App\Models\Instrument::find($instrument_id); @endphp
                    @if($inst)
                        <x-select.option label="{{ $inst->fullName() }}" value="{{ $inst->id }}" />
                    @else
                        <x-select.option label="{{ $instrument_id }}" value="{{ $instrument_id }}" />
                    @endif
                @endif
            </x-select>
            <input type="hidden" id="aladin-instrument-hidden" name="instrument_id" wire:model.defer="instrument_id" value="{{ $instrument_id ?? '' }}" />
            @if(!empty($instrument_id))
                @php $inst = \App\Models\Instrument::find($instrument_id); @endphp
                @if($inst)
                    <div hidden data-dsl-label="{{ $inst->fullName() }}" data-dsl-value="{{ $inst->id }}"></div>
                @endif
            @endif
        </div>

        <div data-dsl-field="eyepiece">
            <label class="block text-xs text-gray-400 mb-1">{{ __('Eyepiece') }}</label>
            <x-select
                :async-data="route('eyepiece.select.api', ['instrument_set' => $instrumentSetId ?? '', 'selected' => $eyepiece_id ?? ''])"
                option-label="name"
                option-value="id"
                placeholder="{{ __('Select eyepiece') }}"
                class="w-full"
                x-on:selected="document.getElementById('aladin-eyepiece-hidden').value = $event.detail.value; window.dslComputeMagnification(document.getElementById('aladin-instrument-hidden').value, $event.detail.value, document.getElementById('aladin-lens-hidden').value)"
                x-on:clear="document.getElementById('aladin-eyepiece-hidden').value = ''; window.dslComputeMagnification(document.getElementById('aladin-instrument-hidden').value, '', document.getElementById('aladin-lens-hidden').value)"
                value="{{ $eyepiece_id ?? '' }}"
            >
                @if(!empty($eyepiece_id))
                    @php $ep = \App\Models\Eyepiece::find($eyepiece_id); @endphp
                    @if($ep)
                        <x-select.option label="{{ $ep->fullName() }}" value="{{ $ep->id }}" />
                    @else
                        <x-select.option label="{{ $eyepiece_id }}" value="{{ $eyepiece_id }}" />
                    @endif
                @endif
            </x-select>
            <input type="hidden" id="aladin-eyepiece-hidden" name="eyepiece_id" wire:model.defer="eyepiece_id" value="{{ $eyepiece_id ?? '' }}" />
            @if(!empty($eyepiece_id))
                @php $ep2 = \App\Models\Eyepiece::find($eyepiece_id); @endphp
                @if($ep2)
                    <div hidden data-dsl-label="{{ $ep2->fullName() }}" data-dsl-value="{{ $ep2->id }}"></div>
                @endif
            @endif
        </div>

        <div data-dsl-field="lens">
            <label class="block text-xs text-gray-400 mb-1">{{ __('Lens') }}</label>
            <x-select
                :async-data="route('lens.select.api', ['instrument_set' => $instrumentSetId ?? '', 'selected' => $lens_id ?? ''])"
                option-label="name"
                option-value="id"
                placeholder="{{ __('Select lens') }}"
                class="w-full"
                x-on:selected="document.getElementById('aladin-lens-hidden').value = $event.detail.value; window.dslComputeMagnification(document.getElementById('aladin-instrument-hidden').value, document.getElementById('aladin-eyepiece-hidden').value, $event.detail.value)"
                x-on:clear="document.getElementById('aladin-lens-hidden').value = ''; window.dslComputeMagnification(document.getElementById('aladin-instrument-hidden').value, document.getElementById('aladin-eyepiece-hidden').value, '')"
                value="{{ $lens_id ?? '' }}"
            >
                @if(!empty($lens_id))
                    @php $ln = \App\Models\Lens::find($lens_id); @endphp
                    @if($ln)
                        <x-select.option label="{{ $ln->fullName() }}" value="{{ $ln->id }}" />
                    @else
                        <x-select.option label="{{ $lens_id }}" value="{{ $lens_id }}" />
                    @endif
                @endif
            </x-select>
            <input type="hidden" id="aladin-lens-hidden" name="lens_id" wire:model.defer="lens_id" value="{{ $lens_id ?? '' }}" />
            @if(!empty($lens_id))
                @php $ln2 = \App\Models\Lens::find($lens_id); @endphp
                @if($ln2)
                    <div hidden data-dsl-label="{{ $ln2->fullName() }}" data-dsl-value="{{ $ln2->id }}"></div>
                @endif
            @endif
        </div>
    </div>

    <div class="mt-3">
        <label class="block text-xs text-gray-400 mb-1">{{ __('Magnification') }}</label>
        <div class="flex items-center gap-2">
            <div class="flex items-center">
                <input type="text" name="magnification" wire:model.defer="manual_magnification" class="w-32 rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100" />
                <span class="ml-2 text-gray-400">x</span>
            </div>
        </div>
    </div>
</div>

<script>
    // ── Magnification: compute client-side to avoid Livewire re-renders ──────
    window.dslComputeMagnification = function(instrumentId, eyepieceId, lensId) {
        var magInput = document.querySelector('[name="magnification"]');
        if (!instrumentId) {
            if (magInput) magInput.value = '';
            return;
        }
        // An instrument with a fixed magnification needs no eyepiece.
        var url = '/api/magnification?instrument_id=' + encodeURIComponent(instrumentId);
        if (eyepieceId) url += '&eyepiece_id=' + encodeURIComponent(eyepieceId);
        if (lensId) url += '&lens_id=' + encodeURIComponent(lensId);
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (magInput && data.magnification != null) {
                    magInput.value = data.magnification;
                }
            })
            .catch(function() {});
    };

    // ── The lens follows the instrument: an instrument that is always used
    //    with a lens (a coma corrector) selects it, one without clears it ──
    window.dslApplyInstrumentLens = function(option) {
        // Only the observer's own instruments say which lens they use.
        if (!option || !Object.prototype.hasOwnProperty.call(option, 'lens_id')) return;

        var hidden = document.getElementById('aladin-lens-hidden');
        if (hidden) {
            hidden.value = option.lens_id ? String(option.lens_id) : '';
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
        }

        var wrapper = document.querySelector('[data-dsl-field="lens"]');
        var alpineEl = wrapper ? wrapper.querySelector('[x-data]') : null;
        if (!alpineEl) return;
        var data = null;
        try {
            if (window.Alpine && typeof window.Alpine.$data === 'function') {
                data = window.Alpine.$data(alpineEl);
            }
        } catch (e) {}
        if (!data && alpineEl._x_dataStack) {
            data = alpineEl._x_dataStack[0];
        }
        if (!data) return;

        data.selected = option.lens_id
            ? { label: option.lens_name || String(option.lens_id), value: option.lens_id, isSelected: true }
            : undefined;
    };

    // ── Preselect: set Alpine internal state so WireUI shows label + clear (×) ─
    (function preSelectFields() {
        var fields = ['instrument', 'eyepiece', 'lens'];
        var attempts = 0;
        var maxAttempts = 50;

        function applyAll() {
            var pending = false;

            fields.forEach(function(field) {
                var wrapper = document.querySelector('[data-dsl-field="' + field + '"]');
                if (!wrapper) return;

                var hint = wrapper.querySelector('[data-dsl-label]');
                if (!hint) return;

                var label = hint.getAttribute('data-dsl-label');
                var value = hint.getAttribute('data-dsl-value');
                if (!label || !value) return;

                // Find the Alpine root inside the wrapper
                var alpineEl = wrapper.querySelector('[x-data]');
                if (!alpineEl) { pending = true; return; }

                var data = null;
                try {
                    if (window.Alpine && typeof window.Alpine.$data === 'function') {
                        data = window.Alpine.$data(alpineEl);
                    }
                } catch (e) {}
                // Alpine v2 fallback
                if (!data && alpineEl._x_dataStack) {
                    data = alpineEl._x_dataStack[0];
                }
                if (!data) { pending = true; return; }

                // Only set if not already user-selected
                if (data.selected && data.selected.value != null) return;

                data.selected = { label: label, value: value, isSelected: true };
            });

            attempts++;
            if (pending && attempts < maxAttempts) {
                setTimeout(applyAll, 100);
            }
        }

        document.addEventListener('alpine:initialized', function() { setTimeout(applyAll, 0); });
        setTimeout(applyAll, 200);
    })();
</script>
