<x-action-section>
    <x-slot name="title">
        {{ __('Delete Team') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Permanently delete this team.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-400">
            {{ __('Once a team is deleted, all of its resources and data will be permanently deleted. Before deleting this team, please download any data or information regarding this team that you wish to retain.') }}
        </div>

        <div class="mt-5">
            <x-button negative wire:click="$toggle('confirmingTeamDeletion')" wire:loading.attr="disabled">
                {{ __('Delete Team') }}
            </x-button>
        </div>

        <!-- Delete Team Confirmation Modal -->
        <x-modal.card blur title="{{ __('Delete Team') }}" wire:model="confirmingTeamDeletion">
            <div class="flex col-span-1">
                <x-icon name="exclamation-circle" class="w-10 h-10 text-red-600" />
                <div class="py-2 px-4">
                    {{ __('Are you sure you want to delete this team? Once a team is deleted, all of its resources and data will be permanently deleted.') }}
                </div>
            </div>

            <x-slot name="footer">
                <x-button type="submit" label="{{ __('Cancel') }}" wire:click="$toggle('confirmingTeamDeletion')"
                    wire:loading.attr="disabled" />

                <x-button negative class="ml-3" wire:click="deleteTeam" wire:loading.attr="disabled">
                    {{ __('Delete Team') }}
                </x-button>
            </x-slot>
        </x-modal.card>
    </x-slot>
</x-action-section>
