<x-form-section submit="updateLanguageInformation">
    <x-slot name="title">
        {{ __("Language Settings") }}
    </x-slot>

    <x-slot name="description">
        {{ __("Update the language settings.") }}
    </x-slot>

    <x-slot name="form">
        {{-- DeepskyLog language --}}
        <div class="col-span-6 sm:col-span-5">
            <x-select
                label="{!! __('DeepskyLog UI language') !!}"
                wire:model.live="language"
                :async-data="route('ui_languages.index')"
                option-label="name"
                option-value="id"
            />
        </div>

        {{-- Standard language of observations --}}
        <div class="col-span-6 sm:col-span-5">
            <x-select
                label="{{ __('Standard language for observations') }}"
                wire:model.live="observationlanguage"
                :async-data="route('observation_languages.index')"
                option-label="name"
                option-value="id"
            />
        </div>

        {{-- Translate all the descriptions? --}}
        <div class="col-span-6 sm:col-span-5">
            <x-toggle
                label="{!! __('Translate all the observations to the language of the UI') !!}"
                name="translate"
                id="translate"
                wire:model.live="translate"
            />
        </div>

        <!-- Send messages as emails -->
        <div class="col-span-6 sm:col-span-5">
            <x-toggle
                label="{{ __('Send messages as email') }}"
                name="sendMail"
                id="sendMail"
                wire:model.live="sendMail"
            />&nbsp;
        </div>

        <!-- Daily message with the new observations of the observers one follows -->
        <div class="col-span-6 sm:col-span-5">
            <x-toggle
                label="{{ __('Send me a daily message with the new observations of the observers I follow') }}"
                name="followDigest"
                id="followDigest"
                wire:model.live="followDigest"
            />
        </div>

        <!-- Message when a clear, dark night is forecast at the standard location -->
        <div class="col-span-6 sm:col-span-5">
            <x-toggle
                label="{{ __('Send me a message when a clear, dark night is forecast at my standard location') }}"
                name="clearNightAlert"
                id="clearNightAlert"
                wire:model.live="clearNightAlert"
            />
        </div>

        <!-- Message about a session of the last days without observations -->
        <div class="col-span-6 sm:col-span-5">
            <x-toggle
                label="{{ __('Remind me of my sessions without observations') }}"
                name="sessionReminder"
                id="sessionReminder"
                wire:model.live="sessionReminder"
            />
        </div>

        <!-- Message with the year in review, early in January -->
        <div class="col-span-6 sm:col-span-5">
            <x-toggle
                label="{{ __('Send me my year in review in January') }}"
                name="yearReview"
                id="yearReview"
                wire:model.live="yearReview"
            />
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
