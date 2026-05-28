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
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-800 px-4 py-3 text-sm text-gray-400 sm:px-6">
                    {{ $mode === 'drawings'
                        ? __('Objects ordered by the number of drawings, from most observed to least observed.')
                        : __('Objects ordered by the number of observations, from most observed to least observed.') }}
                </div>

                @if ($popularObjects->isEmpty())
                    <div class="px-4 py-8 text-center text-sm text-gray-400 sm:px-6">
                        {{ $mode === 'drawings' ? __('No drawings found.') : __('No observations found.') }}
                    </div>
                @else
                    <div class="divide-y divide-gray-800">
                        @foreach ($popularObjects as $object)
                            <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6">
                                <a href="{{ $object->object_url }}" class="min-w-0 text-base font-semibold text-gray-100 hover:text-sky-300 hover:underline">
                                    {{ $object->name }}
                                </a>

                                <a href="{{ $object->count_url }}" class="shrink-0 rounded-full bg-gray-800 px-3 py-1 text-sm font-medium text-sky-300 hover:bg-gray-700 hover:text-sky-200">
                                    @if ($mode === 'drawings')
                                        {{ $object->count }} {{ Str::plural(__('drawing'), $object->count) }}
                                    @else
                                        {{ $object->count }} {{ Str::plural(__('observation'), $object->count) }}
                                    @endif
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-800 px-4 py-4 sm:px-6">
                        {{ $popularObjects->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>