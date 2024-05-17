<x-form-section submit="updateLanguageInformation">
    <x-slot name="title">
        {{ __('Language Settings') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update the language settings.') }}
    </x-slot>

    <x-slot name="form">
        {{-- DeepskyLog language --}}
        <div class="col-span-6 sm:col-span-5">
            <x-select label="{{ __('DeepskyLog UI language') }}" wire:model.live="language" :async-data="route('ui_languages.index')"
                option-label="name" option-value="id" />
        </div>

        {{-- Standard language of observations --}}
        <div class="col-span-6 sm:col-span-5">
            <x-select label="{{ __('Standard language for observations') }}" wire:model.live="observationlanguage"
                :async-data="route('observation_languages.index')" option-label="name" option-value="id" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button type="submit" secondary label="{{ __('Save') }}" wire:loading.attr="disabled" />
    </x-slot>

</x-form-section>
