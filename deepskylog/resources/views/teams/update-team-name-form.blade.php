<x-form-section submit="updateTeamName">
    <x-slot name="title">
        {!! __("Team Name") !!}
    </x-slot>

    <x-slot name="description">
        {!! __('The team\'s name and owner information.') !!}
    </x-slot>

    <x-slot name="form">
        <!-- Team Owner Information -->
        <div class="col-span-6">
            <x-label value="{!! __('Team Owner') !!}" />

            <div class="mt-2 flex items-center">
                <img
                    class="h-12 w-12 rounded-full object-cover"
                    src="{{ $team->owner->profile_photo_url }}"
                    alt="{{ $team->owner->name }}"
                />

                <div class="ml-4 leading-tight">
                    <div>{{ $team->owner->name }}</div>
                    <div class="text-sm text-gray-400">
                        {{ $team->owner->email }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-input
                label="{{ __('Team Name') }}"
                id="name"
                type="text"
                class="mt-1 block w-full"
                wire:model.live="state.name"
                :disabled="true"
            />

            <x-input-error for="name" class="mt-2" />
        </div>
    </x-slot>
</x-form-section>
