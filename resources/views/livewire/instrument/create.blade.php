<div>
    <form role="form" wire:submit.prevent="save">

        @if(!$update)
        {{-- The selection of an existing instrument --}}
        <div class="form-group">
            <label for="catalog">{{ _i("Select an existing instrument") }}</label>

            @php
            $allInstruments = [0 => ''] + \App\Models\Instrument::all()->unique('name')->pluck('name', 'id')->toArray();
            @endphp
            <div class="form">
                <div x-data=''>
                    <x-input.select-live-wire-collection wire:model="sel_instrument" prettyname="myinstrument"
                        :options="$allInstruments" selected="('sel_instrument')" />
                </div>
            </div>
        </div>

        {{ _i("or specify your instrument details manually") }}

        <br /><br />
        @endif

        {{-- The name of the instrument --}}
        <div class="form-group name">
            <label for="name">{{ _i("Name") }}</label>
            <input wire:model="name" type="text" required class="form-control @error('name') is-invalid @enderror"
                maxlength="64" name="name" size="30" placeholder='Alkaid 16" f/4.2 Dobson'
                value="@if ($instrument->name){{ $instrument->name }}@else{{ old('name') }}@endif" />
            @error('name') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- The type of the instrument --}}
        <div class="form-group type">
            <label for="type">{{ _i("Instrument type") }}</label>

            @php
            $allTypes = \App\Models\InstrumentType::all()->pluck('type', 'id')->toArray();
            @endphp

            <div class="form">
                <div x-data=''>
                    <x-input.select-live-wire-collection :first="$type" wire:model="type" prettyname="mytype"
                        :options="$allTypes" selected="('type')" />
                </div>
            </div>
        </div>

        {{-- The diameter of the instrument --}}
        <div class="form-group diameter">
            <label for="diameter">{{ _i("Diameter") }}</label>

            <div class="input-group mb-3">
                <input wire:model="diameter" type="number" step='0.01' required
                    class="form-control @error('diameter') is-invalid @enderror" maxlength="5" name="diameter" size="5"
                    value="@if ($instrument->diameter > 0){{ $instrument->diameter }}@else{{ old('diameter') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text"
                        id="name-addon">{{ Auth::user()->showInches ? _i('inch') : _i('mm') }}</span>
                </div>
            </div>
            @error('diameter') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- F/D of the instrument --}}
        <div class="form-group fd">
            <label for="fd">{{ _i("F/D") }}</label>
            <div class="input-group mb-3">
                <input wire:model="fd" type="number" min="0.1" step="0.01"
                    class="form-control @error('fd') is-invalid @enderror" maxlength="4" name="fd" size="5"
                    value="@if ($instrument->fd > 0){{ $instrument->fd }}@else{{ old('fd') }}@endif" />
            </div>
            @error('fd') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- Focal length of the instrument --}}
        <div class="form-group focalLength">
            {{ _i(' or ') }}
            <label for="focalLength">{{ _i("Focal Length") }}</label>
            <div class="input-group mb-3">
                <input wire:model="focalLength" type="number" min="0.1" step="0.01"
                    class="form-control @error('focalLength') is-invalid @enderror" maxlength="4" name="focalLength"
                    size="5"
                    value="@if ($instrument->fd > 0){{ Auth::user()->showInches ? $instrument->fd * $instrument->diameter / 25.4 : $instrument->fd * $instrument->diameter }}@else{{ old('focalLength') }}@endif" />

                <div class="input-group-append">
                    <span class="input-group-text"
                        id="name-addon">{{ Auth::user()->showInches ? _i('inch') : _i('mm') }}</span>
                </div>
            </div>
            @error('focalLength') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- Fixed magnification of the instrument --}}
        <div class="form-group fixedMagnification">
            <label for="fixedMagnification">{{ _i("Fixed Magnification") }}</label>
            <div class="input-group mb-3">
                <input wire:model="fixedMagnification" type="string"
                    class="form-control @error('fixedMagnification') is-invalid @enderror" maxlength="5"
                    name="fixedMagnification" size="5"
                    value="@if ($instrument->fixedMagnification > 0){{ $instrument->fixedMagnification }}@else{{ old('fixedMagnification') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text" id="name-addon">x</span>
                </div>
            </div>
            @error('fixedMagnification') <span class="small text-error">{{ $message }}<br /></span> @enderror
        </div>

        {{-- Instrument picture --}}
        {{ _i('Upload a picture of your instrument.') . ' (max 10 Mb)' }}

        <x-media-library-attachment rules="max:10240" name="media" />

        <br />
        <input type="submit" class="btn btn-success" name="add"
            value="@if ($update){{ _i("Change instrument") }}@else{{ _i("Add instrument") }}@endif" />

    </form>
</div>
