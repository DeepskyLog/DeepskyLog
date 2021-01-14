<div>
    @php if (strpos(Request::url(), 'admin') === false) {
    // Add button to select the standard instrument
    $allInstruments = \App\Models\Instrument::getInstrumentOptions();
    $allLenses = App\Models\Lens::getLensOptions();
    } else {
    $allInstruments = null;
    $allLenses = null;
    }
    @endphp

    <div>
        @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
        @endif
    </div>
    <h4>
        <!-- We have to check if admin is part of request. -->
        @php if (strpos(Request::url(), 'admin') !== false) {
        echo _i("All eyepieces");
        } else {
        echo _i("Eyepieces of %s", Auth::user()->name);
        }
        @endphp
    </h4>
    <hr />

    @php if (strpos(Request::url(), 'admin') === false) {
    echo '<a class="btn btn-success float-right" href="/eyepiece/create">' .
        _i("Add eyepiece") . '</a>
    <br /><br />';
    }
    @endphp

    @if ($allInstruments)
    {{ _i('Information about eyepieces with ') }}
    <div x-data=''>
        <x-input.select-live-wire wire:model='instrument' prettyname='myinstrument' :options='$allInstruments'
            selected="('instrument')" />
    </div>
    <br />
    <div x-data=''>
        <x-input.select-live-wire wire:model="lens" prettyname="mylens" :options="$allLenses" selected="('lens')" />
    </div>

    @endif
    <br />

    @if (strpos(Request::url(), 'admin') === false)
    <livewire:eyepiece-table hideable="select" exportable />
    @else
    <livewire:eyepiece-table hideable="select" hide="active" exportable />
    @endif
</div>
