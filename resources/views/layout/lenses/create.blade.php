@extends("layout.master")

@section('content')

<h4>
    @if ($update)
        {{ $lens->name }}
    @else
        {{ _i("Add a new lens") }}
    @endif
</h4>

@if ($update)
    <form role="form" action="/lenses/{{ $lens->id }}" method="POST">
    @method('PATCH')
@else
    <form role="form" action="/lenses" method="POST">
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
                <select class="form-control" onchange="location = this.options[this.selectedIndex].value;" name="catalog">
                    <option>&nbsp;</option>
                    @foreach (\App\Lenses::all() as $lensloop)
                        <option value="/lens/create/{{ $lensloop->id }}"
                        @if ($lens->id == $lensloop->id)
                            selected="selected"
                        @endif
                        >{{ $lensloop->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{ _i("or specify your lens details manually") }}
        <br /><br />

        @endif

        <div class="form-group">
            <label for="name">{{ _i("Name") }}</label>
            <input type="text" required class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30" value="@if ($lens->name){{ $lens->name }}@else{{ old('name') }}@endif" />
            <span class="help-block">{{ _i("e.g. Televue 2x Barlow") }}</span>
        </div>

        <div class="form-group">
            <label for="factor">{{ _i("Factor") }}</label>
            <div class="form-inline">
                <input type="number" min="0.01" max="9.99" required step="0.01" class="form-control {{ $errors->has('factor') ? 'is-invalid' : '' }}" maxlength="5" name="factor" size="5" value="@if ($lens->factor > 0){{ $lens->factor }}@else{{ old('factor') }}@endif" />
            </div>
            <span class="help-block">{{ _i("> 1.0 for Barlow lenses, < 1.0 for shapley lenses.") }}</span>
        </div>

        <!--TODO: Use the real observer_id -->
        <input type="hidden" name="observer_id" value="3">

        <input type="submit" class="btn btn-success" name="add" value="@if ($update){{ _i("Change lens") }}@else{{ _i("Add lens") }}@endif" />
    </div>
</form>

@endsection
