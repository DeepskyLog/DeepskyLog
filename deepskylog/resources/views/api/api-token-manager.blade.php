<div>
    <!-- Generate API Token -->
    <x-form-section submit="createApiToken">
        <x-slot name="title">
            {{ __("Create API Token") }}
        </x-slot>

        <x-slot name="description">
            {{ __("API tokens allow third-party services to authenticate with our application on your behalf.") }}
        </x-slot>

        <x-slot name="form">
            <!-- Token Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-input
                    label="{{ __('Token Name') }}"
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model.live="createApiTokenForm.name"
                    autofocus
                />
                <x-input-error for="name" class="mt-2" />
            </div>

            <!-- Token Permissions -->
            @if (Laravel\Jetstream\Jetstream::hasPermissions())
                <div class="col-span-6">
                    <x-label
                        for="permissions"
                        value="{{ __('Permissions') }}"
                    />

                    <div class="mt-2 grid grid-cols-1 gap-4 md:grid-cols-2">
                        @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
                            <label class="flex items-center">
                                <x-checkbox
                                    wire:model.live="createApiTokenForm.permissions"
                                    :value="$permission"
                                />
                                <span class="ml-2 text-sm text-gray-600">
                                    {{ $permission }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="mr-3" on="created">
                {{ __("Created.") }}
            </x-action-message>

            <x-button type="submit">
                {{ __("Create") }}
            </x-button>
        </x-slot>
    </x-form-section>

    @if ($this->user->tokens->isNotEmpty())
        <x-section-border />

        <!-- Manage API Tokens -->
        <div class="mt-10 sm:mt-0">
            <x-action-section>
                <x-slot name="title">
                    {{ __("Manage API Tokens") }}
                </x-slot>

                <x-slot name="description">
                    {{ __("You may delete any of your existing tokens if they are no longer needed.") }}
                </x-slot>

                <!-- API Token List -->
                <x-slot name="content">
                    <div class="space-y-6">
                        @foreach ($this->user->tokens->sortBy("name") as $token)
                            <div class="flex items-center justify-between">
                                <div class="break-all">
                                    {{ $token->name }}
                                </div>

                                <div class="ml-2 flex items-center">
                                    @if ($token->last_used_at)
                                        <div class="text-sm text-gray-400">
                                            {{ __("Last used") }}
                                            {{ $token->last_used_at->diffForHumans() }}
                                        </div>
                                    @endif

                                    @if (Laravel\Jetstream\Jetstream::hasPermissions())
                                        <button
                                            class="ml-6 cursor-pointer text-sm text-gray-400 underline"
                                            wire:click="manageApiTokenPermissions({{ $token->id }})"
                                        >
                                            {{ __("Permissions") }}
                                        </button>
                                    @endif

                                    <button
                                        class="ml-6 cursor-pointer text-sm text-red-500"
                                        wire:click="confirmApiTokenDeletion({{ $token->id }})"
                                    >
                                        {{ __("Delete") }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot>
            </x-action-section>
        </div>
    @endif

    <!-- Token Value Modal -->
    <x-modal-card
        blur
        title="{{ __('API Token') }}"
        wire:model.live="displayingToken"
    >
        <div>
            {{ __('Please copy your new API token. For your security, it won\'t be shown again.') }}
        </div>

        <x-input
            x-ref="plaintextToken"
            type="text"
            readonly
            :value="$plainTextToken"
            class="mt-4 w-full break-all rounded bg-gray-100 px-4 py-2 font-mono text-sm text-gray-500"
            autofocus
            autocomplete="off"
            autocorrect="off"
            autocapitalize="off"
            spellcheck="false"
            @showing-token-modal.window="setTimeout(() => $refs.plaintextToken.select(), 250)"
        />

        <x-slot name="footer">
            <x-button
                type="submit"
                label="{{ __('Close') }}"
                wire:click="$set('displayingToken', false)"
                wire:loading.attr="disabled"
            />
        </x-slot>
    </x-modal-card>

    <!-- API Token Permissions Modal -->
    <x-modal-card
        blur
        title="{{ __('API Token Permissions') }}"
        wire:model.live="managingApiTokenPermissions"
    >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
                <label class="flex items-center">
                    <x-checkbox
                        wire:model.live="updateApiTokenForm.permissions"
                        :value="$permission"
                    />
                    <span class="ml-2 text-sm text-gray-600">
                        {{ $permission }}
                    </span>
                </label>
            @endforeach
        </div>

        <x-slot name="footer">
            <x-button
                type="submit"
                label="{{ __('Cancel') }}"
                wire:click="$set('managingApiTokenPermissions', false)"
                wire:loading.attr="disabled"
            />

            <x-button
                class="ml-3"
                wire:click="updateApiToken"
                wire:loading.attr="disabled"
            >
                {{ __("Save") }}
            </x-button>
        </x-slot>
    </x-modal-card>

    <!-- Delete Token Confirmation Modal -->
    <x-modal-card
        blur
        title="{{ __('Delete API Token') }}"
        wire:model.live="confirmingApiTokenDeletion"
    >
        <div class="col-span-1 flex">
            <x-icon name="exclamation-circle" class="h-10 w-10 text-red-600" />
            <div class="px-4 py-2">
                {{ __("Are you sure you would like to delete this API token?") }}
            </div>
        </div>

        <x-slot name="footer">
            <x-button
                type="submit"
                label="{{ __('Cancel') }}"
                wire:click="$toggle('confirmingApiTokenDeletion')"
                wire:loading.attr="disabled"
            />

            <x-button
                negative
                class="ml-3"
                wire:click="deleteApiToken"
                wire:loading.attr="disabled"
            >
                {{ __("Delete") }}
            </x-button>
        </x-slot>
    </x-modal-card>
</div>
