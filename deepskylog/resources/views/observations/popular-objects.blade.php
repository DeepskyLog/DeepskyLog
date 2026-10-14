<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">
                {{ __('Popular objects') }}
            </h2>

            <div class="inline-flex rounded-lg border border-gray-700 bg-gray-950 p-1">
                <a
                    href="{{ route('objects.popular', ['mode' => 'observations']) }}"
                    class="rounded-md px-4 py-2 text-sm font-medium transition {{ $mode === 'observations' ? 'bg-sky-500 text-gray-950' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                    @if ($mode === 'observations') aria-current="page" @endif
                >
                    {{ __('Observations') }}
                </a>
                <a
                    href="{{ route('objects.popular', ['mode' => 'drawings']) }}"
                    class="rounded-md px-4 py-2 text-sm font-medium transition {{ $mode === 'drawings' ? 'bg-sky-500 text-gray-950' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                    @if ($mode === 'drawings') aria-current="page" @endif
                >
                    {{ __('Drawings') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4">
                <p class="mb-4 text-sm text-gray-400">
                    {{ $mode === 'drawings'
                        ? __('Objects ordered by the number of drawings, from most observed to least observed.')
                        : __('Objects ordered by the number of observations, from most observed to least observed.') }}
                </p>

                <livewire:popular-objects-table :mode="$mode" :key="'popular-objects-' . $mode" />
            </div>
        </div>
    </div>
</x-app-layout>
