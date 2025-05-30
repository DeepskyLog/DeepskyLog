<x-app-layout>
    @if ($update)
        @livewire('create-lens', ['lens' => $lens])
    @else
        @livewire('create-lens')
    @endif
</x-app-layout>
