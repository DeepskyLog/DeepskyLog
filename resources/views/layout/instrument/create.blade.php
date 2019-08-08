@extends("layout.master")

@section('title')
    @if ($update)
        {{ $instrument->name }}
    @else
        {{ _i("Add a new instrument") }}
    @endif
@endsection

@section('content')

<h4>
    @if ($update)
        {{ $instrument->name }}
    @else
        {{ _i("Add a new instrument") }}
    @endif
</h4>

@if ($update)
    <form role="form" action="/instrument/{{ $instrument->id }}" method="POST">
    @method('PATCH')
@else
    <form role="form" action="/instrument" method="POST">
@endif
    @csrf
    <div>
        <hr />
        <input type="submit" class="btn btn-success float-right" name="add" value="@if ($update){{ _i("Change instrument") }}@else{{ _i("Add instrument") }}@endif">
        <br >
        @if (!$update)
        <div class="form-group">
            <label for="catalog">{{ _i("Select an existing instrument") }}</label>

            <div class="form">
                <select id="instrument" class="form-control">
                </select>
            </div>
        </div>

        {{ _i("or specify your instrument details manually") }}
        <br /><br />
        @endif

        <div class="form-group name">
            <label for="name">{{ _i("Name") }}</label>
            <input type="text" required class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30" value="@if ($instrument->name){{ $instrument->name }}@else{{ old('name') }}@endif" />
            <span class="help-block">{{ _i("e.g. Televue 2x Barlow") }}</span>
        </div>

        <div class="form-group type">
            <label for="type">{{ _i("Instrument type") }}</label>

            <div class="form">
                <select id="type" name="type" class="form-control">
                    @foreach (\App\InstrumentType::all() as $type)
                        <option value="{{ $type->id }}" @if ($instrument->type == $type->id || old('type') == $type->id) selected="selected" @endif>{{ _i($type->type) }}</option>
                    @endforeach

                 </select>
            </div>
        </div>

        <div class="form-group diameter">
            <label for="diameter">{{ _i("Diameter") }}</label>
            <div class="form-inline">
                <input type="string" class="form-control {{ $errors->has('diameter') ? 'is-invalid' : '' }}" maxlength="5" name="diameter" size="5" value="@if ($instrument->diameter > 0){{ $instrument->diameter }}@else{{ old('diameter') }}@endif" />
            </div>
        </div>

        <div class="form-group fd">
            <label for="fd">{{ _i("F/D") }}</label>
            <div class="form-inline">
                <input type="string" class="form-control {{ $errors->has('fd') ? 'is-invalid' : '' }}" maxlength="5" name="fd" size="5" value="@if ($instrument->fd > 0){{ $instrument->fd }}@else{{ old('fd') }}@endif" />
            </div>
        </div>

        <div class="form-group fixedMagnification">
            <label for="fixedMagnification">{{ _i("Fixed Magnification") }}</label>
            <div class="form-inline">
                <input type="string" class="form-control {{ $errors->has('fixedMagnification') ? 'is-invalid' : '' }}" maxlength="5" name="fixedMagnification" size="5" value="@if ($instrument->fixedMagnification > 0){{ $instrument->fixedMagnification }}@else{{ old('fixedMagnification') }}@endif" />
            </div>
        </div>

        <input type="submit" class="btn btn-success" name="add" value="@if ($update){{ _i("Change instrument") }}@else{{ _i("Add instrument") }}@endif" />
    </div>
</form>


@endsection

@push('scripts')

<script>
    $(document).ready(function() {
        $("#instrument").select2({
            ajax: {
                // Do the autocompletion. Get all instruments with the requested characters.
                url: '/instrument/autocomplete',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
            cache: true
            }
        });
    });

    $('#instrument').on("select2:selecting", function(e) {
        // Get the id of the selected instrument
        id = e.params.args.data.id;

        var self = this
        // Read the information of the instrument
        $.getJSON('/getInstrumentJson/' + id, function(data) {
            $('.name input').val(data.name);
            $('.type input').val(data.type);
            $('.diameter input').val(data.diameter);
            $('.fd input').val(data.fd);
            $('.fixedMagnification input').val(data.fixedMagnification);
        });
    });
</script>
@endpush
