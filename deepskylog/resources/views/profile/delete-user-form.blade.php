<x-action-section>
    <x-slot name="title">
        {{ __("Delete Account") }}
    </x-slot>

    <x-slot name="description">
        {{ __("Permanently delete your account.") }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-300">
            {{ __("Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.") }}
        </div>

        <div class="mt-5">
            <x-button
                negative
                wire:click="confirmUserDeletion"
                wire:loading.attr="disabled"
            >
                {{ __("Delete Account") }}
            </x-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-modal-card
            blur
            title="{{ __('Delete Account') }}"
            wire:model.live="confirmingUserDeletion"
        >
            {{ __("Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.") }}

            <div
                class="mt-4"
                x-data="{}"
                x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)"
            >
                <x-password
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                    x-ref="password"
                    wire:model.live="password"
                    wire:keydown.enter="deleteUser"
                />

                <x-input-error for="password" class="mt-2" />
            </div>

            <x-slot name="footer">
                <x-button
                    type="submit"
                    label="{{ __('Cancel') }}"
                    wire:click="$toggle('confirmingUserDeletion')"
                    wire:loading.attr="disabled"
                />

                <x-button
                    negative
                    class="ml-3"
                    wire:click="deleteUser"
                    wire:loading.attr="disabled"
                >
                    {{ __("Delete Account") }}
                </x-button>
            </x-slot>
        </x-modal-card>
    </x-slot>
</x-action-section>
