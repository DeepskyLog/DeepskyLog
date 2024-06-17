<x-form-section submit="updateAtlasInformation">
    <x-slot name="title">
        {{ __("Atlas Settings") }}
    </x-slot>

    <x-slot name="description">
        {{ __("Update the settings for the DeepskyLog atlases.") }}
    </x-slot>

    <x-slot name="form">
        <!-- FoV -->
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="overview"
                label="Overview standard FoV (in arcminutes)"
                type="number"
                min="1"
                max="3600"
                wire:model.live="overviewFoV"
            />
            <x-input-error for="overview" class="mt-2" />
        </div>
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="lookup"
                label="Lookup standard FoV (in arcminutes)"
                type="number"
                min="1"
                max="3600"
                wire:model.live="lookupFoV"
            />
            <x-input-error for="lookup" class="mt-2" />
        </div>
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="detail"
                label="Detail standard FoV (in arcminutes)"
                type="number"
                min="1"
                max="3600"
                wire:model.live="detailFoV"
            />
            <x-input-error for="detail" class="mt-2" />
        </div>

        <!-- Object magnitudes -->
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="overviewdsos"
                label="Overview standard object magnitude"
                type="number"
                min="1.0"
                max="20.0"
                step="0.1"
                wire:model.live="overviewdsos"
            />
            <x-input-error for="overviewdsos" class="mt-2" />
        </div>
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="lookupdsos"
                label="Lookup standard object magnitude"
                type="number"
                min="1.0"
                max="20.0"
                step="0.1"
                wire:model.live="lookupdsos"
            />
            <x-input-error for="lookupdsos" class="mt-2" />
        </div>
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="detaildsos"
                label="Detail standard object magnitude"
                type="number"
                min="1.0"
                max="20.0"
                step="0.1"
                wire:model.live="detaildsos"
            />
            <x-input-error for="detaildsos" class="mt-2" />
        </div>

        <!-- Star magnitudes -->
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="overviewstars"
                label="Overview standard star magnitude"
                type="number"
                min="1.0"
                max="20.0"
                step="0.1"
                wire:model.live="overviewstars"
            />
            <x-input-error for="overviewstars" class="mt-2" />
        </div>
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="lookupstars"
                label="Lookup standard star magnitude"
                type="number"
                min="1.0"
                max="20.0"
                step="0.1"
                wire:model.live="lookupstars"
            />
            <x-input-error for="lookupstars" class="mt-2" />
        </div>
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="detailstars"
                label="Detail standard star magnitude"
                type="number"
                min="1.0"
                max="20.0"
                step="0.1"
                wire:model.live="detailstars"
            />
            <x-input-error for="detailstars" class="mt-2" />
        </div>

        <!-- Photo FoV -->
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="photosize1"
                label="Standard size of first picture (in arcminutes)"
                type="number"
                min="1"
                max="3600"
                wire:model.live="photosize1"
            />
            <x-input-error for="photosize1" class="mt-2" />
        </div>
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="photosize2"
                label="Standard size of second picture (in arcminutes)"
                type="number"
                min="1"
                max="3600"
                wire:model.live="photosize2"
            />
            <x-input-error for="photosize2" class="mt-2" />
        </div>

        <!-- Atlas page font -->
        <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
            <x-number
                id="atlaspagefont"
                label="Font size printed atlas pages (6..9)"
                type="number"
                min="6"
                max="9"
                wire:model.live="atlaspagefont"
            />
            <x-input-error for="atlaspagefont" class="mt-2" />
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
