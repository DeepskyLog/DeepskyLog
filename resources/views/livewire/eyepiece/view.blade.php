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

    {{ _i('Show equipment') }}
    @php
    $equipment = '<option value="0">' . _i('All my equipment') . '</option>';
    $equipment .= '<option value="-1">' . _i('All my active equipment') . '</option>';
    foreach(\App\Models\User::where('id', Auth::id())->first()->sets()->get() as $set) {
    $equipment .= '<option value="' . $set->id . '">' . _i('Equipment set') . ': ' . $set->name . '</option>';
    }
    @endphp
    <div x-data='' wire:ignore>
        <x-input.select-live-wire-equipment-set wire:model='equipment' prettyname='myequipment' :options='$equipment'
            selected="('equipment')" />
    </div>
    <br />
    @if ($allInstruments)
    {{ _i('Information about eyepieces with ') }}

    {{-- Use choices directly for the instruments --}}
    <div wire:ignore x-data=''>
        <select class="form-control-sm" id="myinstrument2"></select>
    </div>
    <br />
    {{-- Use choices directly for the lenses --}}
    <div wire:ignore x-data=''>
        <select class="form-control-sm" id="mylens2"></select>
    </div>
    @endif
    <br />

    @if (strpos(Request::url(), 'admin') === false)
    <livewire:eyepiece-table hideable="select" exportable />
    @else
    <livewire:eyepiece-table hideable="select" hide="active" exportable />
    @endif

    <script>
        // For the instruments
        const instr = document.querySelector('#myinstrument2');
        const instrumentChoices = new Choices(instr, { position:'bottom', shouldSort:false });

        // Use ajax to get the list of options
        $.ajax({
            // * 0 => all my equipment, -1 => all my active equipment, > 0 => the id of the equipment set
            url: '/getinstruments/0',
            type: "GET",
            dataType: "json",
            success:function(instruments) {
                // Loop over all instruments
                var options = [];
                var arrayLength = instruments.length;
                for (var i = 0; i < arrayLength; i=i+4) {
                    options.push({ value: instruments[i], label: instruments[i + 1], selected: instruments[i + 2], disabled: instruments[i + 3]});
                }

                instrumentChoices.setChoices(options, 'value', 'label', true);
            }
        });


        Livewire.on('equipmentChanged', id => {
            // Use ajax to get the list of options
            $.ajax({
                url: '/getinstruments/'+id,
                type: "GET",
                dataType: "json",
                success:function(instruments) {
                    // Loop over all instruments
                    var options = [];
                    var arrayLength = instruments.length;
                    for (var i = 0; i < arrayLength; i=i+4) {
                        options.push({ value: instruments[i], label: instruments[i + 1], selected: instruments[i + 2], disabled: instruments[i + 3]});
                    }

                    instrumentChoices.setChoices(options, 'value', 'label', true);
                }
            });
        });

        // On select, update the table!
        instr.addEventListener('choice',
            function(event) {
                Livewire.emit('instrumentChanged', event.detail.choice.value);
            }
        );

        // For the lenses
        const lns = document.querySelector('#mylens2');
        const lensChoices = new Choices(lns, { position:'bottom', shouldSort:false });

        // Use ajax to get the list of options
        $.ajax({
            // * 0 => all my equipment, -1 => all my active equipment, > 0 => the id of the equipment set
            url: '/getlenses/0',
            type: "GET",
            dataType: "json",
            success:function(lenses) {
                // Loop over all lenses
                var options = [];
                var arrayLength = lenses.length;
                for (var i = 0; i < arrayLength; i=i+4) {
                    options.push({ value: lenses[i], label: lenses[i + 1], selected: lenses[i + 2], disabled: lenses[i + 3]});
                }

                lensChoices.setChoices(options, 'value', 'label', true);
            }
        });


        Livewire.on('equipmentChanged', id => {
            // Use ajax to get the list of options
            $.ajax({
                url: '/getlenses/'+id,
                type: "GET",
                dataType: "json",
                success:function(lenses) {
                    // Loop over all lenses
                    var options = [];
                    var arrayLength = lenses.length;
                    for (var i = 0; i < arrayLength; i=i+4) {
                        options.push({ value: lenses[i], label: lenses[i + 1], selected: lenses[i + 2], disabled: lenses[i + 3]});
                    }

                    lensChoices.setChoices(options, 'value', 'label', true);
                }
            });
        });

        // On select, update the table!
        lns.addEventListener('choice',
            function(event) {
                Livewire.emit('lensChanged', event.detail.choice.value);
            }
        );

    </script>

</div>
