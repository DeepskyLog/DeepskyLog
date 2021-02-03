<div>
    <form wire:submit.prevent="save" role="form">

        @auth
        @if (count(auth()->user()->instruments) > 0)

        @php
        $allInstruments = \App\Models\Instrument::getInstrumentOptions();
        $allLocations = App\Models\Location::getLocationOptions();
        @endphp
        {{ _i('Using') }}
        <div x-data=''>
            <x-input.select-live-wire wire:model="instrument" prettyname="myinstrument" :options="$allInstruments"
                selected="('instrument')" />
        </div>

        {{ _i('at') }}

        <div x-data=''>
            <x-input.select-live-wire wire:model="location" prettyname="mylocation" :options="$allLocations"
                selected="('location')" />
        </div>
        <br />
        @endif
        @endauth
    </form>

    <br />
    <livewire:nearby-table hideable="select" exportable :targetsToShow='$targetsToShow' />

</div>
