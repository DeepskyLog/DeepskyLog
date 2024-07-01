<x-app-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div x-data="{ recovery: false }">
            <div class="mb-4 text-sm text-gray-600" x-show="! recovery">
                {!! __("Please confirm access to your account by entering the authentication code provided by your authenticator application.") !!}
            </div>

            <div class="mb-4 text-sm text-gray-600" x-show="recovery">
                {!! __("Please confirm access to your account by entering one of your emergency recovery codes.") !!}
            </div>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route("two-factor.login") }}">
                @csrf

                <div class="mt-4" x-show="! recovery">
                    <x-input
                        label="{{ __('Code') }}"
                        id="code"
                        class="mt-1 block w-full"
                        type="text"
                        inputmode="numeric"
                        name="code"
                        autofocus
                        x-ref="code"
                        autocomplete="one-time-code"
                    />
                </div>

                <div class="mt-4" x-show="recovery">
                    <x-input
                        label="{{ __('Recovery Code') }}"
                        id="recovery_code"
                        class="mt-1 block w-full"
                        type="text"
                        name="recovery_code"
                        x-ref="recovery_code"
                        autocomplete="one-time-code"
                    />
                </div>

                <div class="mt-4 flex items-center justify-end">
                    <button
                        type="button"
                        class="cursor-pointer text-sm text-gray-600 underline hover:text-gray-900"
                        x-show="! recovery"
                        x-on:click="
                            recovery = true
                            $nextTick(() => {
                                $refs.recovery_code.focus()
                            })
                        "
                    >
                        {{ __("Use a recovery code") }}
                    </button>

                    <button
                        type="button"
                        class="cursor-pointer text-sm text-gray-600 underline hover:text-gray-900"
                        x-show="recovery"
                        x-on:click="
                            recovery = false
                            $nextTick(() => {
                                $refs.code.focus()
                            })
                        "
                    >
                        {!! __("Use an authentication code") !!}
                    </button>

                    <x-button class="ml-4" type="submit">
                        {{ __("Log in") }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-app-layout>
