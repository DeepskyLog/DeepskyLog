<x-form-section submit="createTeam">
    <x-slot name="title">
        {{ __("Create a new team") }}
    </x-slot>

    <x-slot name="description">
        {{ __("Create a new team to collaborate with others on projects.") }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <x-label value="{!! __('Team Owner') !!}" />

            <div class="mt-2 flex items-center">
                <img
                    class="h-12 w-12 rounded-full object-cover"
                    src="{{ $this->user->profile_photo_url }}"
                    alt="{{ $this->user->name }}"
                />

                <div class="ml-4 leading-tight">
                    <div>{{ $this->user->name }}</div>
                    <div class="text-sm text-gray-400">
                        {{ $this->user->email }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-input
                label="{{ __('Team Name') }}"
                id="name"
                type="text"
                class="mt-1 block w-full"
                wire:model.live="state.name"
                autofocus
            />
            <x-input-error for="name" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-button type="submit">
            {{ __("Create") }}
        </x-button>
    </x-slot>
</x-form-section>
