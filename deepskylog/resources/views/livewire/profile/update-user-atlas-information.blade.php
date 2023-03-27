<x-form-section submit="updateAtlasInformation">
    <x-slot name="title">
        {{ __('Atlas Settings') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update the settings for the DeepskyLog atlases.') }}
    </x-slot>

    <x-slot name="form">
        <!-- FoV -->
        <div class="col-span-6 sm:col-span-5 text-sm text-gray-400">
            <x-inputs.number id="overview" label="Overview standard FoV (in arcminutes)" type="number" min="1"
                max="3600" wire:model.defer="overviewFoV" />
            <x-input-error for="overview" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-5 text-sm text-gray-400">
            <x-inputs.number id="lookup" label="Lookup standard FoV (in arcminutes)" type="number" min="1"
                max="3600" wire:model.defer="lookupFoV" />
            <x-input-error for="loopup" class="mt-2" />
        </div>
        <div class="col-span-6 sm:col-span-5 text-sm text-gray-400">
            <x-inputs.number id="detail" label="Detail standard FoV (in arcminutes)" type="number" min="1"
                max="3600" wire:model.defer="detailFoV" />
            <x-input-error for="detail" class="mt-2" />
        </div>

    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button type="submit" secondary label="{{ __('Save') }}" wire:loading.attr="disabled" />
    </x-slot>
</x-form-section>
