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
    <form role="form" action="/instrument/{{ $instrument->id }}" method="POST" enctype="multipart/form-data">
    @method('PATCH')
@else
    <form role="form" action="/instrument" method="POST" enctype="multipart/form-data">
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

            <div class="input-group mb-3">
                <input type="number" step='0.01' required class="form-control {{ $errors->has('diameter') ? 'is-invalid' : '' }}" maxlength="5" name="diameter" size="5" value="@if ($instrument->diameter > 0){{ Auth::user()->showInches ? $instrument->diameter / 25.4 : $instrument->diameter }}@else{{ old('diameter') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text" id="name-addon">{{ Auth::user()->showInches ? _i('inch') : _i('mm') }}</span>
                </div>
            </div>
        </div>

        <div class="form-group fd">
            <label for="fd">{{ _i("F/D") }}</label>
            <div class="input-group mb-3">
                <input type="number" min="0.1" step="0.01" class="form-control {{ $errors->has('fd') ? 'is-invalid' : '' }}" maxlength="4" name="fd" size="5" value="@if ($instrument->fd > 0){{ $instrument->fd }}@else{{ old('fd') }}@endif" />
            </div>
        </div>
        <div class="form-group focalLength">
            {{ _i(' or ') }}
            <label for="focalLength">{{ _i("Focal Length") }}</label>
                <div class="input-group mb-3">
                    <input type="number" min="0.1" step="0.01" class="form-control {{ $errors->has('focalLength') ? 'is-invalid' : '' }}" maxlength="4" name="focalLength" size="5" value="@if ($instrument->fd > 0){{ Auth::user()->showInches ? $instrument->fd * $instrument->diameter / 25.4 : $instrument->fd * $instrument->diameter }}@else{{ old('focalLength') }}@endif" />

                    <div class="input-group-append">
                        <span class="input-group-text" id="name-addon">{{ Auth::user()->showInches ? _i('inch') : _i('mm') }}</span>
                    </div>
            </div>
        </div>

        <div class="form-group fixedMagnification">
            <label for="fixedMagnification">{{ _i("Fixed Magnification") }}</label>
            <div class="input-group mb-3">
                <input type="string" class="form-control {{ $errors->has('fixedMagnification') ? 'is-invalid' : '' }}" maxlength="5" name="fixedMagnification" size="5" value="@if ($instrument->fixedMagnification > 0){{ $instrument->fixedMagnification }}@else{{ old('fixedMagnification') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text" id="name-addon">x</span>
                </div>
            </div>
        </div>

        {!! _i('Upload a picture of your instrument.') . ' (max 10 Mb)' !!}

        <input id="picture" name="picture" type="file">

        <br />
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

    $('.fd').on('input', function() {
        // If diameter is not set, don't do anything.
        if ($('.diameter input').val() != '') {
            $fl = parseFloat($('.fd input').val() * $('.diameter input').val()).toFixed(2);
            $('.focalLength input').val($fl);
        }
    });

    $('.focalLength').on('input', function() {
        // If diameter is not set, don't do anything.
        if ($('.diameter input').val() != '') {
            $fd = parseFloat($('.focalLength input').val() / $('.diameter input').val()).toFixed(2);
            $('.fd input').val($fd);
        }
    });

    $('.diameter').on('input', function() {
        // If fd is not set, don't do anything.
        if ($('.fd input').val() != '') {
            $focalLength = parseFloat($('.fd input').val() * $('.diameter input').val()).toFixed(2);
            $('.focalLength input').val($focalLength);
        }
    });

    $('#instrument').on("select2:selecting", function(e) {
        // Get the id of the selected instrument
        id = e.params.args.data.id;

        var self = this
        // Read the information of the instrument
        $.getJSON('/getInstrumentJson/' + id, function(data) {
            $('.name input').val(data.name);
            $('.type select').val(data.type);
            if ({{ Auth::user()->showInches }}) {
                $('.diameter input').val((data.diameter / 25.4).toFixed(1));
                $('.focalLength input').val((data.fd * data.diameter / 25.4).toFixed(1));
            } else {
                $('.diameter input').val(data.diameter);
                $('.focalLength input').val((data.fd * data.diameter).toFixed(2));
            }
            $('.fd input').val(data.fd);
            $('.fixedMagnification input').val(data.fixedMagnification);
        });
    });

    $("#picture").fileinput(
        {
            theme: "fas",
            allowedFileTypes: ['image'],    // allow only images
            'showUpload': false,
            maxFileSize: 10000,
            @if ($instrument->id != null && $instrument->getFirstMedia('instrument') != null)
            initialPreview: [
                '<img class="file-preview-image kv-preview-data" src="/instrument/{{ $instrument->id }}/getImage">'
            ],
            initialPreviewConfig: [
                {caption: "{{ $instrument->getFirstMedia('instrument')->file_name }}", size: {{ $instrument->getFirstMedia('instrument')->size }}, url: "/instrument/{{ $instrument->id }}/deleteImage", key: 1},
            ],
            @endif
        }
    );
</script>
@endpush
