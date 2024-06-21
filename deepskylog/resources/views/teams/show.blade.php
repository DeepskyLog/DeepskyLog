<x-app-layout>
    <x-slot name="header">
        <h2 class="bg-gray-900 text-xl font-semibold leading-tight">
            {{ __("Team Settings") }}
        </h2>
    </x-slot>

    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            @livewire("teams.update-team-name-form", ["team" => $team])

            @if (Auth::user()->hasAdministratorPrivileges())
                @livewire("teams.team-member-manager", ["team" => $team])
            @endif
        </div>
    </div>
</x-app-layout>
