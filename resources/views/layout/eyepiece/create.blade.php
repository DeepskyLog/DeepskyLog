@extends("layout.master")

@section('title')
    @if ($update)
        {{ $eyepiece->name }}
    @else
        {{ _i("Add a new eyepiece") }}
    @endif
@endsection

@section('content')

<h4>
    @if ($update)
        {{ $eyepiece->name }}
    @else
        {{ _i("Add a new eyepiece") }}
    @endif
</h4>

@if ($update)
    <form role="form" action="/eyepiece/{{ $eyepiece->id }}" method="POST" enctype="multipart/form-data">
    @method('PATCH')
@else
    <form role="form" action="/eyepiece" method="POST" enctype="multipart/form-data">
@endif
    @csrf
    <div>
        <hr />
        <input type="submit" class="btn btn-success float-right" name="add" value="@if ($update){{ _i("Change eyepiece") }}@else{{ _i("Add eyepiece") }}@endif">
        <br >
        @if (!$update)
        <div class="form-group">
            <label for="catalog">{{ _i("Select an existing eyepiece") }}</label>

            <div class="form">
                <select id="eyepiece" class="form-control">
                </select>
            </div>
        </div>

        {{ _i("or specify your eyepiece details manually") }}
        <br /><br />
        @endif

        <div class="form-group name">
            <label for="name">{{ _i("Name") }}</label>
            <input type="text" required placeholder="Televue 31mm Nagler" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30" value="@if ($eyepiece->name){{ $eyepiece->name }}@else{{ old('name') }}@endif" />
        </div>

        <div class="form-group genericname">
            <label for="name">{{ _i("Generic name") }}</label>
            <input type="text" class="form-control" readonly maxlength="64" name="genericname" size="30" />
        </div>

        <div class="form-group focalLength">
            <label for="name">{{ _i("Focal length") }}</label>
            <div class="input-group mb-3">
                <input type="number" placeholder="31" required max="99" min="1" class="form-control {{ $errors->has('focalLength') ? 'is-invalid' : '' }}" maxlength="5" name="focalLength" size="30" value="@if ($eyepiece->focalLength){{ $eyepiece->focalLength }}@else{{ old('focalLength') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text">mm</span>
                </div>
            </div>
        </div>

        <div class="form-group apparentFOV">
            <label for="name">{{ _i("Apparent Field of View") }}</label>
            <div class="input-group mb-3">
                <input type="number" placeholder="82" required max="150" min="20" class="form-control {{ $errors->has('apparentFOV') ? 'is-invalid' : '' }}" maxlength="5" name="apparentFOV" size="30" value="@if ($eyepiece->apparentFOV){{ $eyepiece->apparentFOV }}@else{{ old('apparentFOV') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text">&deg;</span>
                </div>
            </div>
        </div>

        <div class="form-group maxFocalLength">
            <label for="name">{{ _i("Maximum Focal length") }}</label>
            <div class="input-group mb-3">
                <input type="number" placeholder="31 - {{ _i("Only for zoom eyepieces") }}" max="99" min="1" class="form-control {{ $errors->has('maxFocalLength') ? 'is-invalid' : '' }}" maxlength="5" name="maxFocalLength" size="30" value="@if ($eyepiece->maxFocalLength){{ $eyepiece->maxFocalLength }}@else{{ old('maxFocalLength') }}@endif" />
                <div class="input-group-append">
                    <span class="input-group-text" id="name-addon">mm</span>
                </div>
            </div>
        </div>

        <div class="form-group brandInput">
            <label for="brandInput">{{ _i("Brand") }}</label>
            <select class="form-control brandSelect" name="brand" id="brand">
                @php
                    if ($eyepiece->brand) {
                        $selected = $eyepiece->brand;
                    } else {
                        $selected = old('brand');
                    }
                @endphp
                <option value=""></option>
                @foreach (\App\EyepieceBrand::all()->pluck('brand')->sort() as $brand)
                    <option value="{{ $brand }}" {{ ($selected == $brand ? "selected":"") }}>{{ $brand }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group typeInput">
            <label for="typeInput">{{ _i("Type") }}</label>
            <select class="form-control typeSelect" name="type" id="type">
                @php
                    if ($eyepiece->type) {
                        $selected = $eyepiece->type;
                    } else {
                        old('type');
                    }
                @endphp
                @foreach (\App\EyepieceType::where('brand', $eyepiece->brand)->pluck('type')->sort() as $type)
                    <option value="{{ $type }}" {{ ($selected == $type ? "selected":"") }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        {!! _i('Upload a picture of your eyepiece.') . ' (max 10 Mb)' !!}

        <input id="picture" name="picture" type="file">

        <br />

        <input type="submit" class="btn btn-success" name="add" value="@if ($update){{ _i("Change eyepiece") }}@else{{ _i("Add eyepiece") }}@endif" />
    </div>
</form>


@endsection

@push('scripts')

<script>
    $(document).ready(function() {
        $(".brandSelect").select2({
            tags: true
        });
    });

    $(document).ready(function() {
        $(".typeSelect").select2({
            tags: true
        });
    });

    $(document).ready(function() {
        $("#eyepiece").select2({
            ajax: {
                // Do the autocompletion. Get all eyepieces with the requested characters.
                url: '/eyepiece/autocomplete',
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

    $('#eyepiece').on("select2:selecting", function(e) {
        // Get the id of the selected eyepiece
        id = e.params.args.data.id;

        var self = this
        // Read the information of the eyepiece
        $.getJSON('/getEyepieceJson/' + id, function(data) {
            $('.name input').val(data.name);
            $('.focalLength input').val(data.focalLength);
            $('.apparentFOV input').val(data.apparentFOV);
            $('.maxFocalLength input').val(data.maxFocalLength);
        });
    });

    $("#picture").fileinput(
        {
            theme: "fas",
            allowedFileTypes: ['image'],    // allow only images
            'showUpload': false,
            maxFileSize: 10000,
            @if ($eyepiece->id != null && $eyepiece->getFirstMedia('eyepiece') != null)
            initialPreview: [
                '<img class="file-preview-image kv-preview-data" src="/eyepiece/{{ $eyepiece->id }}/getImage">'
            ],
            initialPreviewConfig: [
                {caption: "{{ $eyepiece->getFirstMedia('eyepiece')->file_name }}", size: {{ $eyepiece->getFirstMedia('eyepiece')->size }}, url: "/eyepiece/{{ $eyepiece->id }}/deleteImage", key: 1},
            ],
            @endif
        }
    );

    $('.focalLength, .maxFocalLength').on('input', function() {
        updateGenericName();
    });

    $('.brandSelect').on('input', function() {
        updateTypes($('.brandInput option:selected').text());
        updateGenericName();
    });

    $('.typeSelect').on('input', function() {
        updateGenericName();
    });

    $(document).ready(function() {
        updateGenericName();
    });

    function updateGenericName() {
        if (!$('.typeInput :selected').val()) {
            $type = '';
        } else {
            $type = $('.typeInput :selected').val();
        }

        if ($('.maxFocalLength input').val() != '') {
            $genericname = $('.focalLength input').val() + "-" + $('.maxFocalLength input').val()
                +  "mm " + $('.brandInput option:selected').text() + " " + $type;
        } else {
            $genericname = $('.focalLength input').val() + "mm " + $('.brandInput :selected').text()
                + " " + $type;
        }
        $(".genericname input").val($genericname);
    }

    function updateTypes(brand) {
        // Remove all options of select2
        $('.typeSelect').empty();

        // Read all correct options for the new brand using JSON
        $.getJSON('/getEyepieceTypeJson/' + brand, function(data) {
            var newOption = new Option('', '', false, false);
            $('.typeSelect').append(newOption).trigger('change');
            for (i in data) {
                // Add the new options to select2
                var newOption = new Option(data[i], data[i], false, false);
                $('.typeSelect').append(newOption).trigger('change');
            }
        });

    }
</script>
@endpush
