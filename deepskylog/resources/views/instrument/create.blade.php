<x-app-layout>
    @if ($update)
        @livewire('create-instrument', ['instrument' => $instrument])
    @else
        @livewire('create-instrument')
    @endif
</x-app-layout>
