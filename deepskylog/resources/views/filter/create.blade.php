<x-app-layout>
    @if ($update)
        @livewire('create-filter', ['filter' => $filter])
    @else
        @livewire('create-filter')
    @endif
</x-app-layout>
