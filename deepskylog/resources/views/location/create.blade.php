<x-app-layout>
    @if ($update)
        @livewire('create-location', ['location' => $location])
    @else
        @livewire('create-location')
    @endif
</x-app-layout>
