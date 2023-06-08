<div>
    @if (Gate::check('addTeamMember', $team))
        <x-section-border />

        <!-- Add Team Member -->
        <div class="mt-10 sm:mt-0">
            <x-form-section submit="addTeamMember">
                <x-slot name="title">
                    {{ __('Add Team Member') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Add a new team member to your team, allowing them to collaborate with you.') }}
                </x-slot>

                <x-slot name="form">
                    <div class="col-span-6">
                        <div class="max-w-xl text-sm text-gray-400">
                            {{ __('Please provide the name of the person you would like to add to this team.') }}
                        </div>
                    </div>

                    <!-- Member Name -->
                    <div class="col-span-6 sm:col-span-5">
                        <x-select label="{{ __('Name') }}" wire:model.defer="addTeamMemberForm.email" :async-data="route('addUserToTeam.index')"
                            option-label="name" option-value="id" />
                    </div>

                    <!-- Role -->
                    @if (count($this->roles) > 0)
                        <div class="col-span-6 lg:col-span-4">
                            <x-label for="role" value="{{ __('Role') }}" />
                            <x-input-error for="role" class="mt-2" />

                            <div class="relative z-0 mt-1 border border-gray-300 rounded-lg cursor-pointer">
                                @foreach ($this->roles as $index => $role)
                                    <button type="button"
                                        class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 {{ $index > 0 ? 'border-t border-gray-400 rounded-t-none' : '' }} {{ !$loop->last ? 'rounded-b-none' : '' }}"
                                        wire:click="$set('addTeamMemberForm.role', '{{ $role->key }}')">
                                        <div
                                            class="{{ isset($addTeamMemberForm['role']) && $addTeamMemberForm['role'] !== $role->key ? 'opacity-50' : '' }}">
                                            <!-- Role Name -->
                                            <div class="flex items-center">
                                                <div
                                                    class="text-sm text-gray-400 {{ $addTeamMemberForm['role'] == $role->key ? 'font-semibold' : '' }}">
                                                    {{ $role->name }}
                                                </div>

                                                @if ($addTeamMemberForm['role'] == $role->key)
                                                    <svg class="ml-2 h-5 w-5 text-green-400"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @endif
                                            </div>

                                            <!-- Role Description -->
                                            <div class="mt-2 text-xs text-gray-400 text-left">
                                                {{ $role->description }}
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-slot>

                <x-slot name="actions">
                    <x-action-message class="mr-3" on="saved">
                        {{ __('Added.') }}
                    </x-action-message>

                    <x-button type="submit">
                        {{ __('Add') }}
                    </x-button>
                </x-slot>
            </x-form-section>
        </div>
    @endif

    @if ($team->teamInvitations->isNotEmpty() && Gate::check('addTeamMember', $team))
        <x-section-border />

        <!-- Team Member Invitations -->
        <div class="mt-10 sm:mt-0">
            <x-action-section>
                <x-slot name="title">
                    {{ __('Pending Team Invitations') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('These people have been invited to your team and have been sent an invitation email. They may join the team by accepting the email invitation.') }}
                </x-slot>

                <x-slot name="content">
                    <div class="space-y-6">
                        @foreach ($team->teamInvitations as $invitation)
                            <div class="flex items-center justify-between">
                                <div class="text-gray-400">{{ $invitation->email }}</div>

                                <div class="flex items-center">
                                    @if (Gate::check('removeTeamMember', $team))
                                        <!-- Cancel Team Invitation -->
                                        <button class="cursor-pointer ml-6 text-sm text-red-500 focus:outline-none"
                                            wire:click="cancelTeamInvitation({{ $invitation->id }})">
                                            {{ __('Cancel') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot>
            </x-action-section>
        </div>
    @endif

    @if ($team->users->isNotEmpty())
        <x-section-border />

        <!-- Manage Team Members -->
        <div class="mt-10 sm:mt-0">
            <x-action-section>
                <x-slot name="title">
                    {{ __('Team Members') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('All of the people that are part of this team.') }}
                </x-slot>

                <!-- Team Member List -->
                <x-slot name="content">
                    <div class="space-y-6">
                        {{-- Powergrid with all the users in this group --}}
                        <livewire:admin-user-table team="{{ $team->id }}" />

                    </div>
                </x-slot>
            </x-action-section>
        </div>
    @endif

    <!-- Role Management Modal -->
    <x-modal.card blur title="{{ __('Manage Role') }}" wire:model="currentlyManagingRole">
        <div class="relative z-0 mt-1 border border-gray-200 rounded-lg cursor-pointer">
            @foreach ($this->roles as $index => $role)
                <button type="button"
                    class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 {{ $index > 0 ? 'border-t border-gray-200 rounded-t-none' : '' }} {{ !$loop->last ? 'rounded-b-none' : '' }}"
                    wire:click="$set('currentRole', '{{ $role->key }}')">
                    <div class="{{ $currentRole !== $role->key ? 'opacity-50' : '' }}">
                        <!-- Role Name -->
                        <div class="flex items-center">
                            <div class="text-sm text-gray-400 {{ $currentRole == $role->key ? 'font-semibold' : '' }}">
                                {{ $role->name }}
                            </div>

                            @if ($currentRole == $role->key)
                                <svg class="ml-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </div>

                        <!-- Role Description -->
                        <div class="mt-2 text-xs text-gray-400">
                            {{ $role->description }}
                        </div>
                    </div>
                </button>
            @endforeach
        </div>

        <x-slot name="footer">
            <x-button type="submit" label="{{ __('Cancel') }}" wire:click="stopManagingRole"
                wire:loading.attr="disabled" />

            <x-button class="ml-3" wire:click="updateRole" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-modal.card>

    <!-- Leave Team Confirmation Modal -->
    <x-modal.card blur title="{{ __('Leave Team') }}" wire:model="confirmingLeavingTeam">
        <div class="flex col-span-1">
            <x-icon name="exclamation-circle" class="w-10 h-10 text-red-600" />
            <div class="py-2 px-4">
                {{ __('Are you sure you would like to leave this team?') }}
            </div>
        </div>

        <x-slot name="footer">
            <x-button type="submit" label="{{ __('Cancel') }}" wire:click="$toggle('confirmingLeavingTeam')"
                wire:loading.attr="disabled" />

            <x-button negative class="ml-3" wire:click="leaveTeam" wire:loading.attr="disabled">
                {{ __('Leave') }}
            </x-button>
        </x-slot>
    </x-modal.card>

    <!-- Remove Team Member Confirmation Modal -->
    <x-modal.card blur title="{{ __('Remove Team Member') }}" wire:model="confirmingTeamMemberRemoval">
        <div class="flex col-span-1">
            <x-icon name="exclamation-circle" class="w-10 h-10 text-red-600" />
            <div class="py-2 px-4">
                {{ __('Are you sure you would like to remove this person from the team?') }}
            </div>
        </div>
        <x-slot name="footer">
            <x-button type="submit" label="{{ __('Cancel') }}" wire:click="$toggle('confirmingTeamMemberRemoval')"
                wire:loading.attr="disabled" />

            <x-button negative class="ml-3" wire:click="removeTeamMember" wire:loading.attr="disabled">
                {{ __('Remove') }}
            </x-button>
        </x-slot>
    </x-modal.card>
</div>
