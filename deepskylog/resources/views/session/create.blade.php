<x-app-layout>
    @php
        $adaptSource = null;
        if (request()->has('adapt_from')) {
            try {
                $adaptSource = \App\Models\ObservationSession::find(request()->get('adapt_from'));
            } catch (\Throwable $e) {
                $adaptSource = null;
            }
        }
    @endphp

    @if ($adaptSource)
        @livewire('create-session', ['session' => $adaptSource])
    @elseif ($session ?? false)
        @livewire('create-session', ['session' => $session])
    @else
        @livewire('create-session')
    @endif
</x-app-layout>
