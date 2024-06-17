<x-app-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route("register") }}">
            @csrf

            <div>
                <x-input
                    label="{{ __('Name') }}"
                    id="name"
                    class="mt-1 block w-full"
                    type="text"
                    name="name"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                />
            </div>

            <div class="mt-4">
                <x-input
                    label="{{ __('User name') }}"
                    id="username"
                    class="mt-1 block w-full"
                    type="text"
                    name="username"
                    :value="old('username')"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>

            <div class="mt-4">
                <x-input
                    label="{{ __('Email') }}"
                    id="email"
                    class="mt-1 block w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                />
            </div>

            <div class="mt-4">
                <x-password
                    label="{{ __('Password') }}"
                    id="password"
                    class="mt-1 block w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                />
            </div>

            <div class="mt-4">
                <x-password
                    label="{{ __('Confirm Password') }}"
                    id="password_confirmation"
                    class="mt-1 block w-full"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" />

                            <div class="ml-2">
                                {!!
                                    __("I agree to the :privacy_policy", [
                                        "privacy_policy" =>
                                            '<a target="_blank" href="' .
                                            route("policy.show") .
                                            '" class="underline text-sm text-gray-400 hover:text-gray-300">' .
                                            __("Privacy Policy") .
                                            "</a>",
                                    ])
                                !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="mt-4 flex items-center justify-end">
                <a
                    class="text-sm text-gray-400 underline hover:text-gray-300"
                    href="{{ route("login") }}"
                >
                    {{ __("Already registered?") }}
                </a>

                <x-button type="submit" class="ml-4">
                    {{ __("Register") }}
                </x-button>
            </div>
        </form>

        @if (JoelButcher\Socialstream\Socialstream::show())
            <x-socialstream />
        @endif
    </x-authentication-card>
</x-app-layout>
