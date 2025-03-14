<x-app-layout>
    @if ($update)
        @livewire('create-eyepiece', ['eyepiece' => $eyepiece])
    @else
        @livewire('create-eyepiece')
    @endif
</x-app-layout>
