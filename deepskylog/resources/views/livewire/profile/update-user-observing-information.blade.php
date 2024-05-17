<x-form-section submit="updateObservingInformation">
    <x-slot name="title">
        {{ __("Observing Settings") }}
    </x-slot>

    <x-slot name="description">
        {{ __("Update the settings for observing.") }}
    </x-slot>

    @if (! auth()->user()->stdlocation)
        @push("scripts")
            <script>
                window.onload = function () {
                    const title = '{{ __("Missing information!") }}';
                    const description =
                        '{{ __('You haven\'t selected a standard location for your observations! DeepskyLog can only calculate how easy an object can be seen if the standard location is set.') }}';
                    window.$wireui.notify({
                        title: title,
                        description: description,
                        icon: 'warning',
                    });
                };
            </script>
        @endpush
    @elseif (! auth()->user()->stdtelescope)
        @push("scripts")
            <script>
                window.onload = function () {
                    const title = '{{ __("Missing information!") }}';
                    const description =
                        '{{ __('You haven\'t selected a standard instrument for your observations! DeepskyLog can only calculate how easy an object can be seen if the standard instrument is set.') }}';
                    window.$wireui.notify({
                        title: title,
                        description: description,
                        icon: 'warning',
                    });
                };
            </script>
        @endpush
    @endif

    <x-slot name="form">
        {{-- Default observing site --}}
        <div class="col-span-6 sm:col-span-5">
            <x-select
                label="{{ __('Default observing site') }}"
                wire:model.live="stdlocation"
                :async-data="route('locations.index')"
                option-label="name"
                option-value="id"
            />
        </div>

        {{-- Default instrument --}}
        <div class="col-span-6 sm:col-span-5">
            <x-select
                label="{{ __('Default instrument') }}"
                wire:model.live="stdtelescope"
                :async-data="route('instruments.index')"
                option-label="name"
                option-value="id"
            />
        </div>

        {{-- Default atlas --}}
        <div class="col-span-6 sm:col-span-5">
            <x-select
                label="{{ __('Default atlas') }}"
                wire:model.live="standardAtlasCode"
                :async-data="route('atlas.index')"
                option-label="name"
                option-value="id"
            />
        </div>

        {{-- Units for instrument diameter --}}
        <div class="col-span-6 sm:col-span-5">
            @if (auth()->user()->showInches)
                <x-toggle
                    label="{{ __('Show instrument diameter in inch') }}"
                    name="showInches"
                    id="showInches"
                    wire:model.live="showInches"
                    checked
                />
            @else
                <x-toggle
                    label="{{ __('Show instrument diameter in inch') }}"
                    name="showInches"
                    id="showInches"
                    wire:model.live="showInches"
                />
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __("Saved.") }}
        </x-action-message>

        <x-button
            type="submit"
            secondary
            label="{{ __('Save') }}"
            wire:loading.attr="disabled"
        />
    </x-slot>
</x-form-section>
