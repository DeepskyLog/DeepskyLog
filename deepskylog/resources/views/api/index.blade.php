<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {!! __("API Tokens") !!}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-screen mx-auto px-2 py-10 sm:px-6 lg:px-8">
            <p class="mb-6 text-sm text-gray-300">
                {{ __("Programs such as pydeepskylog read your observations, equipment, locations, sessions and observing lists with a token that has the permission read.") }}
                <a href="{{ route("api.documentation") }}" class="text-blue-400 hover:underline">{{ __("API documentation") }}</a>
            </p>
            @livewire("api.api-token-manager")
        </div>
    </div>
</x-app-layout>
