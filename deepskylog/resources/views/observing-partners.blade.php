<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl leading-tight font-semibold text-gray-200">
            {{
                __(
                    "Observing partners nearby",
                )
            }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        @livewire ("observing-partners")
    </div>
</x-app-layout>
