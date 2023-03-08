<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="block">
                <x-input label="{{ __('Email') }}" id="email" class="block mt-1 w-full" type="email" name="email"
                    :value="old('email', $request->email)" required autofocus />
            </div>

            <div class="mt-4">
                <x-inputs.password label="{{ __('Password') }}" id="password" class="block mt-1 w-full" type="password"
                    name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-inputs.password label="{{ __('Confirm Password') }}" id="password_confirmation"
                    class="block mt-1 w-full" type="password" name="password_confirmation" required
                    autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Reset Password') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
