<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {!! __("API Tokens") !!}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-screen mx-auto px-2 py-10 sm:px-6 lg:px-8">
            @livewire("api.api-token-manager")
        </div>
    </div>
</x-app-layout>
