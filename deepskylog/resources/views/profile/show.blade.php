<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __("Profile") }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-screen mx-auto bg-gray-900 py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire("profile.update-profile-information-form")

                <x-section-border />
            @endif

            @livewire("profile.update-user-observing-information")

            <x-section-border />

            @livewire("profile.update-user-language-information")

            <x-section-border />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()) && ! is_null($user->password))
                <div class="mt-10 sm:mt-0">
                    @livewire("profile.update-password-form")
                </div>

                <x-section-border />
            @else
                <div class="mt-10 sm:mt-0">
                    @livewire("profile.set-password-form")
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication() && ! is_null($user->getAuthPassword()))
                <div class="mt-10 sm:mt-0">
                    @livewire("profile.two-factor-authentication-form")
                </div>

                <x-section-border />
            @endif

            @if (JoelButcher\Socialstream\Socialstream::show())
                <div class="mt-10 sm:mt-0">
                    @livewire("profile.connected-accounts-form")
                </div>
            @endif

            @if (! is_null($user->getAuthPassword()))
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire("profile.logout-other-browser-sessions-form")
                </div>
            @endif

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures() && ! is_null($user->getAuthPassword()))
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire("profile.delete-user-form")
                </div>
            @endif

            <x-section-border />

            @livewire("profile.update-user-atlas-information")
        </div>
    </div>
</x-app-layout>
