<x-app-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __("This is a secure area of the application. Please confirm your password before continuing.") }}
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route("password.confirm") }}">
            @csrf

            <div>
                <x-password
                    label="{{ __('Password') }}"
                    id="password"
                    class="mt-1 block w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
            </div>

            <div class="mt-4 flex justify-end">
                <x-button type="submit" class="ml-4">
                    {{ __("Confirm") }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-app-layout>
