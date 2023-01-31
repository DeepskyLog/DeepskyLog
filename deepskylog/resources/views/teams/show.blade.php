<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl bg-gray-900 leading-tight">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 bg-gray-900">
            @livewire('teams.update-team-name-form', ['team' => $team])

            @if (Auth::user()->hasAdministratorPrivileges())
                @livewire('teams.team-member-manager', ['team' => $team])
            @endif

        </div>
    </div>
</x-app-layout>
