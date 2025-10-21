<div>
    <script src="<?php echo e(asset('js/aladin-selects.js')); ?>"></script>
    <script src="<?php echo e(asset('js/aladin-inline.js')); ?>"></script>

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
