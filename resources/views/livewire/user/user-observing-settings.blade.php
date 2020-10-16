<div>
    <br />
    <form wire:submit.prevent="save" role="form" action="/users/{{ $user->id }}/settings">

        {{-- Standard location --}}
        <div class="form-group">
            <label for="stdlocation">{{ _i('Default observing site') }}</label>
            <div wire:ignore>
                <select class="form-control location" style="width: 100%" id="stdlocation" name="stdlocation">
                    {!! App\Models\Location::getLocationOptions() !!}
                </select>
            </div>
            <span class="help-block">
                <a href="/location/create">{{ _i('Add new observing site') }}</a>
            </span>
        </div>

        {{-- Standard instrument --}}
        <div class="form-group">
            <label for="stdinstrument">{{ _i('Default instrument') }}</label>
            <div wire:ignore>
                <select class="form-control instrument" style="width: 100%" id="stdinstrument" name="stdinstrument">
                    {!! App\Models\Instrument::getInstrumentOptions() !!}
                </select>
            </div>
            <span class="help-block">
                <a href="/instrument/create"> {{ _i('Add instrument') }}</a>
            </span>
        </div>

        {{-- Standard eyepiece --}}
        <div class="form-group">
            <label for="stdeyepiece">{{ _i('Default eyepiece') }}</label>
            <div wire:ignore>
                <select class="form-control eyepiece" style="width: 100%" id="stdeyepiece" name="stdeyepiece">
                    {!! App\Models\Eyepiece::getEyepieceOptions() !!}
                </select>
            </div>
            <span class="help-block">
                <a href="/eyepiece/create"> {{ _i('Add eyepiece') }}</a>
            </span>
        </div>

        {{-- Standard lens --}}
        <div class="form-group">
            <label for="stdlens">{{ _i('Default lens') }}</label>
            <div wire:ignore>
                <select class="form-control lens" style="width: 100%" id="stdlens" name="stdlens">
                    {!! App\Models\Lens::getLensOptions() !!}
                </select>
            </div>
            <span class="help-block">
                <a href="/lens/create"> {{ _i('Add lens') }}</a>
            </span>
        </div>

        {{-- Standard atlas --}}
        <div class="form-group">
            <label for="stdatlas">{{ _i('Default atlas') }}</label>
            <div wire:ignore>
                <select class="form-control atlas" style="width: 100%" id="standardAtlasCode" name="standardAtlasCode">
                    @foreach (\App\Models\Atlas::All() as $atlas)
                    <option @if ($atlas->code == $user->standardAtlasCode)
                        selected
                        @endif value="{{ $atlas->code }}">{{ $atlas->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- TODO --}}
        <div class="form-group">
            <label for="showInches">{{ _i('Default units') }}</label>
            <div wire:ignore>
                <select class="form-control units" style="width: 100%" id="showInches" name="showInches">
                    <option @if (0==$user->showInches) selected @endif value="0">{{ _i('Metric (mm)') }}</option>
                    <option @if (1==$user->showInches) selected @endif value="1">{{ _i('Imperial (inches)') }}</option>
                </select>
            </div>
        </div>

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

@push('scripts')
<script>
    $(document).ready(function() {
            $('.location').select2();
            $('.location').on('change', function(e) {
                @this.set('stdlocation', e.target.value);
            });
            $('.instrument').select2();
            $('.instrument').on('change', function(e) {
                @this.set('stdinstrument', e.target.value);
            });
            $('.eyepiece').select2();
            $('.eyepiece').on('change', function(e) {
                @this.set('stdeyepiece', e.target.value);
            });
            $('.lens').select2();
            $('.lens').on('change', function(e) {
                @this.set('stdlens', e.target.value);
            });
            $('.atlas').select2();
            $('.atlas').on('change', function(e) {
                @this.set('standardAtlasCode', e.target.value);
            });
            $('.units').select2();
            $('.units').on('change', function(e) {
                @this.set('showInches', e.target.value);
            });
        });

</script>
@endpush
