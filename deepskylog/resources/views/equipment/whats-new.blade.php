<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl leading-tight font-semibold text-gray-200">
            {{ __("What's new for me") }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        @livewire ("whats-new-equipment", ["kind" => $kind, "equipmentId" => $equipment->id])
    </div>
</x-app-layout>
