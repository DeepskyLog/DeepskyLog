<x-action-section>
    <x-slot name="title">
        {{ __("Connected Accounts") }}
    </x-slot>

    <x-slot name="description">
        {{ __("Connect your social media accounts to enable Sign In with OAuth.") }}
    </x-slot>

    <x-slot name="content">
        <h3 class="text-lg font-medium text-gray-200">
            @if (count($this->accounts) == 0)
                {{ __("You have no connected accounts.") }}
            @else
                {{ __("Your connected accounts.") }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-gray-300">
            {{ __("You are free to connect any social accounts to your profile and may remove any connected accounts at any time. If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your password.") }}
        </div>

        <div class="mt-5 space-y-6">
            @foreach ($this->providers as $provider)
                @php
                    $account = null;
                    $account = $this->accounts->where("provider", $provider["id"])->first();
                @endphp

                <x-connected-account
                    :provider="$provider"
                    created-at="{{ $account?->created_at }}"
                >
                    <x-slot name="action">
                        @if (! is_null($account))
                            <div class="flex items-center space-x-6">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos() && ! is_null($account->avatar_path))
                                    <button
                                        class="ml-6 cursor-pointer text-sm text-gray-500 focus:outline-none"
                                        wire:click="setAvatarAsProfilePhoto({{ $account->id }})"
                                    >
                                        {{ __("Use Avatar as Profile Photo") }}
                                    </button>
                                @endif

                                @if ($this->accounts->count() > 1 || ! is_null($this->user->password))
                                    <x-button
                                        negative
                                        wire:click="confirmRemoveAccount({{ $account->id }})"
                                        wire:loading.attr="disabled"
                                    >
                                        {{ __("Remove") }}
                                    </x-button>
                                @endif
                            </div>
                        @else
                            <x-action-link
                                href="{{ route('oauth.redirect', ['provider' => $provider['id']]) }}"
                            >
                                {{ __("Connect") }}
                            </x-action-link>
                        @endif
                    </x-slot>
                </x-connected-account>
            @endforeach
        </div>

        <!-- Logout Other Devices Confirmation Modal -->
        <x-modal-card
            blur
            title="{{ __('Are you sure you want to remove this account?') }}"
            wire:model.live="confirmingAccountRemoval"
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-1">
                {{ __("Please enter your password to confirm you would like to remove this account.") }}

                <div
                    x-data="{}"
                    x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)"
                >
                    <x-password
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="{{ __('Password') }}"
                        x-ref="password"
                        wire:model.live="password"
                        wire:keydown.enter="removeConnectedAccount"
                    />

                    <x-input-error for="password" />
                </div>
            </div>

            <x-slot name="footer">
                <x-button
                    type="submit"
                    label="{{ __('Cancel') }}"
                    wire:click="$toggle('confirmingAccountRemoval')"
                    wire:loading.attr="disabled"
                />

                <x-button
                    type="submit"
                    secondary
                    wire:click="removeConnectedAccount"
                    wire:loading.attr="disabled"
                >
                    {{ __("Remove Account") }}
                </x-button>
            </x-slot>
        </x-modal-card>
    </x-slot>
</x-action-section>
