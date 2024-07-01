<x-app-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session("status"))
            <div class="mb-4 text-sm font-medium text-green-600">
                {{ session("status") }}
            </div>
        @endif

        <form method="POST" action="{{ route("login") }}">
            @csrf

            <div>
                <x-input
                    label="{!! __('Email or user ID') !!}"
                    id="email"
                    class="mt-1 block w-full"
                    type="text"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
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
                    autocomplete="current-password"
                />
            </div>

            <div class="mt-4 block">
                <label for="remember_me" class="flex items-center">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="form-checkbox"
                        name="remember"
                    />
                    <span class="ml-2 text-sm text-gray-400">
                        {{ __("Remember me") }}
                    </span>
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                @if (Route::has("password.request"))
                    <a
                        class="text-sm text-gray-400 underline hover:text-gray-300"
                        href="{{ route("password.request") }}"
                    >
                        {{ __("Forgot your password?") }}
                    </a>
                @endif

                <x-button type="submit" class="ml-4">
                    {{ __("Login") }}
                </x-button>
            </div>
        </form>

        @if (JoelButcher\Socialstream\Socialstream::show())
            <x-socialstream />
        @endif
    </x-authentication-card>
</x-app-layout>
