{{-- Avoid inline `use` in Blade; Livewire component handles data and models --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Popular observations') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4">
                <livewire:popular-observations-table />
            </div>
        </div>
    </div>
</x-app-layout>
