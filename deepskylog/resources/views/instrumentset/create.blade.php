<x-app-layout>
    @if ($update)
        @livewire('create-instrument-set', ['set' => $set])
    @else
        @livewire('create-instrument-set')
    @endif
</x-app-layout>
