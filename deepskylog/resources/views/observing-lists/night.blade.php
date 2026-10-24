<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-200">{{ __("New observing list for a night") }}</h2>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        @livewire("night-list-wizard")
    </div>
</x-app-layout>
