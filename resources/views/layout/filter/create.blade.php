@extends("layout.master")

@section('title')
    @if ($update)
        {{ $filter->name }}
    @else
        {{ _i("Add a new filter") }}
    @endif
@endsection

@section('content')

<h4>
    @if ($update)
        {{ $filter->name }}
    @else
        {{ _i("Add a new filter") }}
    @endif
</h4>

@if ($update)
    <form role="form" action="/filter/{{ $filter->id }}" method="POST" enctype="multipart/form-data">
    @method('PATCH')
@else
    <form role="form" action="/filter" method="POST" enctype="multipart/form-data">
@endif
    @csrf
    <div>
        <hr />
        <input type="submit" class="btn btn-success float-right" name="add" value="@if ($update){{ _i("Change filter") }}@else{{ _i("Add filter") }}@endif">
        <br >
        @if (!$update)
        <div class="form-group">
            <label for="catalog">{{ _i("Select an existing filter") }}</label>

            <div class="form">
                <select id="filter" class="form-control">
                </select>
            </div>
        </div>

        {{ _i("or specify your filter details manually") }}
        <br /><br />
        @endif

        <div class="form-group name">
            <label for="name">{{ _i("Name") }}</label>
            <input type="text" required class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30" value="@if ($filter->name){{ $filter->name }}@else{{ old('name') }}@endif" />
            <span class="help-block">{{ _i("e.g. Televue 2x Barlow") }}</span>
        </div>

        <div class="form-group type">
            <label for="type">{{ _i("Filter type") }}</label>

            <div class="form">
                <select id="type" name="type" class="form-control">
                    @foreach (\App\FilterType::all() as $type)
                        <option value="{{ $type->id }}" @if ($filter->type == $type->id || old('type') == $type->id) selected="selected" @endif>{{ _i($type->type) }}</option>
                    @endforeach

                 </select>
            </div>
        </div>

        <div class="form-group colorDiv">
            <label for="type">{{ _i("Color") }}</label>

            <div class="form">
                <select id="color" name="color" class="form-control" width="100">
                    <option value="0"> </option>
                    @foreach (\App\FilterColor::all() as $color)
                        <option value="{{ $color->id }}" @if ($filter->color == $color->id || old('color') == $color->id) selected="selected" @endif>{{ _i($color->color) }}</option>
                    @endforeach

                </select>
            </div>
        </div>

        <div class="form-group wratten">
            <label for="wratten">{{ _i("Wratten number") }}</label>
            <div class="form-inline">
                <input type="string" class="form-control {{ $errors->has('wratten') ? 'is-invalid' : '' }}" maxlength="5" name="wratten" size="5" value="@if ($filter->wratten > 0){{ $filter->wratten }}@else{{ old('wratten') }}@endif" />
            </div>
        </div>

        <div class="form-group schott">
            <label for="schott">{{ _i("Schott number") }}</label>
            <div class="form-inline">
                <input type="string" class="form-control {{ $errors->has('schott') ? 'is-invalid' : '' }}" maxlength="5" name="schott" size="5" value="@if ($filter->schott > 0){{ $filter->schott }}@else{{ old('schott') }}@endif" />
            </div>
        </div>

        {!! _i('Upload a picture of your filter.') . ' (max 10 Mb)' !!}

        <input id="picture" name="picture" type="file">

        <br />

        <input type="submit" class="btn btn-success" name="add" value="@if ($update){{ _i("Change filter") }}@else{{ _i("Add filter") }}@endif" />
    </div>
</form>


@endsection

@push('scripts')

<script>
    $(document).ready(function() {
        $("#type").change(function() {
            if($(this).val() == "0" || $(this).val() == "6") {
                $("div.wratten").show();
                $("div.schott").show();
                $("div.colorDiv").show();
                // Set width to 100%
                //$("select").select2({ width: '100%' });
            } else {
                $("div.wratten").hide();
                $("div.schott").hide();
                $("div.colorDiv").hide();
            }
        });

        $("div.wratten").show();
        $("div.schott").show();
        $("div.colorDiv").show();

        if($("#type").val() == "0" || $("#type").val() == "6") {
            $("div.wratten").show();
            $("div.schott").show();
            $("div.colorDiv").show();
        } else {
            $("div.wratten").hide();
            $("div.schott").hide();
            $("div.colorDiv").hide();
        }

        //$("select").select2({ width: '100%' });
        $("#filter").select2({
            ajax: {
                // Do the autocompletion. Get all filters with the requested characters.
                url: '/filter/autocomplete',
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

    $('#filter').on("select2:selecting", function(e) {
        // Get the id of the selected filter
        id = e.params.args.data.id;

        var self = this
        // Read the information of the filter
        $.getJSON('/getFilterJson/' + id, function(data) {
            $('.name input').val(data.name);
            $('.wratten input').val(data.wratten);
            $('.schott input').val(data.schott);
        });
    });

    $("#picture").fileinput(
        {
            theme: "fas",
            allowedFileTypes: ['image'],    // allow only images
            'showUpload': false,
            maxFileSize: 10000,
            @if ($filter->id != null && $filter->getFirstMedia('filter') != null)
            initialPreview: [
                '<img class="file-preview-image kv-preview-data" src="/filter/{{ $filter->id }}/getImage">'
            ],
            initialPreviewConfig: [
                {caption: "{{ $filter->getFirstMedia('filter')->file_name }}", size: {{ $filter->getFirstMedia('filter')->size }}, url: "/filter/{{ $filter->id }}/deleteImage", key: 1},
            ],
            @endif
        }
    );

</script>
@endpush
