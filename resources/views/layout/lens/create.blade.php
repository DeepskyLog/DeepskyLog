@extends("layout.master")

@section('title')
    @if ($update)
        {{ $lens->name }}
    @else
        {{ _i("Add a new lens") }}
    @endif
@endsection

@section('content')

<h4>
    @if ($update)
        {{ $lens->name }}
    @else
        {{ _i("Add a new lens") }}
    @endif
</h4>

@if ($update)
    <form role="form" action="/lens/{{ $lens->id }}" method="POST" enctype="multipart/form-data">
    @method('PATCH')
@else
    <form role="form" action="/lens" method="POST" enctype="multipart/form-data">
@endif
    @csrf
    <div>
        <hr />
        <input type="submit" class="btn btn-success float-right" name="add" value="@if ($update){{ _i("Change lens") }}@else{{ _i("Add lens") }}@endif">
        <br >
        @if (!$update)
        <div class="form-group">
            <label for="catalog">{{ _i("Select an existing lens") }}</label>

            <div class="form">
                <select id="lens" class="form-control">
                </select>
            </div>
        </div>

        {{ _i("or specify your lens details manually") }}
        <br /><br />

        @endif

        <div class="form-group name">
            <label for="name">{{ _i("Name") }}</label>
            <input type="text" required class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30" value="@if ($lens->name){{ $lens->name }}@else{{ old('name') }}@endif" />
            <span class="help-block">{{ _i("e.g. Televue 2x Barlow") }}</span>
        </div>

        <div class="form-group factor">
            <label for="factor">{{ _i("Factor") }}</label>
            <div class="form-inline">
                <input type="number" min="0.01" max="9.99" required step="0.01" class="form-control {{ $errors->has('factor') ? 'is-invalid' : '' }}" maxlength="5" name="factor" size="5" value="@if ($lens->factor > 0){{ $lens->factor }}@else{{ old('factor') }}@endif" />
            </div>
            <span class="help-block">{{ _i("> 1.0 for Barlow lenses, < 1.0 for shapley lenses.") }}</span>
        </div>

        {!! _i('Upload a picture of your lens.') . ' (max 10 Mb)' !!}

        <input id="picture" name="picture" type="file">

        <br />

        <input type="submit" class="btn btn-success" name="add" value="@if ($update){{ _i("Change lens") }}@else{{ _i("Add lens") }}@endif" />
    </div>
</form>


@endsection

@push('scripts')

<script>
    $(document).ready(function() {
        $("select").select2({
            ajax: {
                // Do the autocompletion. Get all lenses with the requested characters.
                url: '/lens/autocomplete',
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

    $('#lens').on("select2:selecting", function(e) {
        // Get the id of the selected lens
        id = e.params.args.data.id;

        var self = this
        // Read the information of the lens
        $.getJSON('/getLensJson/' + id, function(data) {
            $('.name input').val(data.name);
            $('.factor input').val(Math.round(data.factor * 100) / 100);
        });
    });

    $("#picture").fileinput(
        {
            theme: "fas",
            allowedFileTypes: ['image'],    // allow only images
            'showUpload': false,
            maxFileSize: 10000,
            @if ($lens->id != null && $lens->getFirstMedia('lens') != null)
            initialPreview: [
                '<img class="file-preview-image kv-preview-data" src="/lens/{{ $lens->id }}/getImage">'
            ],
            initialPreviewConfig: [
                {caption: "{{ $lens->getFirstMedia('lens')->file_name }}", size: {{ $lens->getFirstMedia('lens')->size }}, url: "/lens/{{ $lens->id }}/deleteImage", key: 1},
            ],
            @endif
        }
    );
</script>
@endpush
