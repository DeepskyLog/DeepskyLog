<x-app-layout>
    @if ($session ?? false)
        @livewire('create-session', ['session' => $session])
    @else
        @livewire('create-session')
    @endif
</x-app-layout>
