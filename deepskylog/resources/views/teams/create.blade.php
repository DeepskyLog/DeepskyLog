<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __("Create Team") }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-screen mx-auto bg-gray-900 py-10 sm:px-6 lg:px-8">
            @livewire("teams.create-team-form")
        </div>
    </div>
</x-app-layout>
