{{-- Avoid inline `use` in Blade; Livewire component handles data and models --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">
                {{ __('Popular observations') }}
            </h2>

            <x-residence-filter :options="$countryOptions" :selected="$country" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4">
                <livewire:popular-observations-table :country="$country" :key="'popular-observations-' . $country" />
            </div>
        </div>
    </div>
</x-app-layout>
