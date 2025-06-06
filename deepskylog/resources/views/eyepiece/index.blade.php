<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Eyepieces") }}
            </h2>
        </div>
    </div>
    <div class="mt-2">
        <div class="flex justify-center">
            <x-button
                :href="route('eyepiece.create')"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            >
                {{ __("Add Eyepiece") }}
            </x-button>
        </div>

        <x-card>
            <div>
                {{-- Powergrid with all the eyepieces --}}
                <livewire:eyepiece-table/>
            </div>
        </x-card>
    </div>
</x-app-layout>
