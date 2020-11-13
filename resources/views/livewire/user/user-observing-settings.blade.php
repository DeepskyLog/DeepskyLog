<div>
    <br />
    <form wire:submit.prevent="save" role="form" action="/users/{{ $user->slug }}/settings">

        @php
        $allLocations = App\Models\Location::getLocationOptions();
        $allInstruments = App\Models\Instrument::getInstrumentOptions();
        $allEyepieces = App\Models\Eyepiece::getEyepieceOptions();
        $allLenses = App\Models\Lens::getLensOptions();
        $allAtlases = '';
        foreach (\App\Models\Atlas::All() as $atlas) {
        $allAtlases .= '<option ';
            if ($atlas->code == $user->standardAtlasCode) {
                $allAtlases .= ' selected ';
            }
            $allAtlases .= ' value="' . $atlas->code . '">' . $atlas->name . '</option>';
        }
        $units = '<option ';
        if (0==$user->showInches) {
            $units .= ' selected ';
        }
        $units .= ' value="0">' . _i('Metric (mm)') . '</option>';
        $units .= '<option ';
        if (1==$user->showInches) {
            $units .= ' selected ';
        }
        $units .= ' value="1">' . _i('Imperial (inches)') . '</option>';;

        @endphp

        {{-- Standard location --}}
        <div class="form-group">
            <label for="stdlocation">{{ _i('Default observing site') }}</label>
            <div x-data=''>
                <div class="form-group">
                    <x-input.select-live-wire wire:model="stdlocation" prettyname="mylocation" :options="$allLocations"
                        selected="('stdlocation')" />
                </div>
            </div>
            <span class="help-block">
                <a href="/location/create">{{ _i('Add new observing site') }}</a>
            </span>
        </div>
        <p hidden>{{ $stdlocation }}</p>

        {{-- Standard instrument --}}
        <div class="form-group">
            <label for="stdinstrument">{{ _i('Default instrument') }}</label>
            <div x-data=''>
                <div class="form-group">
                    <x-input.select-live-wire wire:model="stdinstrument" prettyname="myinstrument"
                        :options="$allInstruments" selected="('stdinstrument')" />
                </div>
            </div>
            <span class="help-block">
                <a href="/instrument/create"> {{ _i('Add instrument') }}</a>
            </span>
        </div>
        <p hidden>{{ $stdinstrument }}</p>

        {{-- Standard eyepiece --}}
        <div class="form-group">
            <label for="stdeyepiece">{{ _i('Default eyepiece') }}</label>
            <div x-data=''>
                <div class="form-group">
                    <x-input.select-live-wire wire:model="stdeyepiece" prettyname="myeyepiece" :options="$allEyepieces"
                        selected="('stdeyepiece')" />
                </div>
            </div>
            <span class="help-block">
                <a href="/eyepiece/create"> {{ _i('Add eyepiece') }}</a>
            </span>
        </div>
        <p hidden>{{ $stdeyepiece }}</p>

        {{-- Standard lens --}}
        <div class="form-group">
            <label for="stdlens">{{ _i('Default lens') }}</label>
            <div x-data=''>
                <div class="form-group">
                    <x-input.select-live-wire wire:model="stdlens" prettyname="mylens" :options="$allLenses"
                        selected="('stdlens')" />
                </div>
            </div>
            <span class="help-block">
                <a href="/lens/create"> {{ _i('Add lens') }}</a>
            </span>
        </div>
        <p hidden>{{ $stdlens }}</p>

        {{-- Standard atlas --}}
        <div class="form-group">
            <label for="stdatlas">{{ _i('Default atlas') }}</label>
            <div x-data=''>
                <div class="form-group">
                    <x-input.select-live-wire wire:model="standardAtlasCode" prettyname="myatlas" :options="$allAtlases"
                        selected="('standardAtlasCode')" />
                </div>
            </div>
        </div>
        <p hidden>{{ $standardAtlasCode }}</p>

        {{-- Imperial or metric units  --}}
        <div class="form-group">
            <label for="showInches">{{ _i('Default units') }}</label>
            <div x-data=''>
                <div class="form-group">
                    <x-input.select-live-wire wire:model="showInches" prettyname="myUnits" :options="$units"
                        selected="('showInches')" />
                </div>
            </div>
        </div>
        <p hidden>{{ $showInches }}</p>

        {{-- Submit button --}}
        <div>
            @if (!$errors->isEmpty())
            <div class="alert alert-danger">
                {{  _i('Please fix the errors in the settings.') }}
            </div>
            @else
            <input type="submit" class="btn btn-success" name="add" value="{{ _i('Update') }}" />
            @endif
            @if (session()->has('message'))
            <br /><br />
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
            @endif
        </div>
    </form>
</div>
