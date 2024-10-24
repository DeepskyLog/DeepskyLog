<x-app-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-300">
            {!! __("Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.") !!}
        </div>

        @if (session("status"))
            <div class="mb-4 text-sm font-medium text-green-600">
                {{ session("status") }}
            </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route("password.email") }}">
            @csrf

            <div class="block">
                <x-input
                    label="{{ __('Email') }}"
                    id="email"
                    class="mt-1 block w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <x-button type="submit">
                    {{ __("Email Password Reset Link") }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-app-layout>
