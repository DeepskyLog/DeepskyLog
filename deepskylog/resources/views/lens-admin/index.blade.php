<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("Lens Makes") }}
            </h2>
        </div>
    </div>
    <div class="mt-2">

        <x-card>
            <div>
                {{-- Powergrid with all the lens makes --}}
                <livewire:admin-lens-table/>
            </div>
        </x-card>
    </div>
</x-app-layout>
