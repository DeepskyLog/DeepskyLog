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

<div id="lens">

@if ($update)
    <form role="form" action="/lens/{{ $lens->id }}" method="POST">
    @method('PATCH')
@else
    <form role="form" action="/lens" method="POST">
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
                <select2 class="form-control" @input="selectLens" name="lens" v-model="selected">
                    @foreach (\App\Lens::all()->unique('name') as $lensloop)
                        <option v-bind:value="{{ $lensloop->id }}"
                        @if ($lens->id == $lensloop->id)
                            selected="selected"
                        @endif
                        >{{ $lensloop->name }}</option>
                    @endforeach
                </select2>
            </div>
        </div>

        {{ _i("or specify your lens details manually") }}
        <br /><br />

        @endif

        <div class="form-group">
            <label for="name">{{ _i("Name") }}</label>
            <input v-model="name" type="text" required class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30" value="@if ($lens->name){{ $lens->name }}@else{{ old('name') }}@endif" />
            <span class="help-block">{{ _i("e.g. Televue 2x Barlow") }}</span>
        </div>

        <div class="form-group">
            <label for="factor">{{ _i("Factor") }}</label>
            <div class="form-inline">
                <input v-model="factor" type="number" min="0.01" max="9.99" required step="0.01" class="form-control {{ $errors->has('factor') ? 'is-invalid' : '' }}" maxlength="5" name="factor" size="5" value="@if ($lens->factor > 0){{ $lens->factor }}@else{{ old('factor') }}@endif" />
            </div>
            <span class="help-block">{{ _i("> 1.0 for Barlow lenses, < 1.0 for shapley lenses.") }}</span>
        </div>

        <input type="submit" class="btn btn-success" name="add" value="@if ($update){{ _i("Change lens") }}@else{{ _i("Add lens") }}@endif" />
    </div>
</form>
</div>


@endsection

@push('scripts')
<script>
    new Vue({
        el: '#lens',
        data: {
            factor: '',
            selected: '',
            name: '',
        },
        methods:{
            selectLens() {
                // create a closure to access component in the callback below
                var self = this
                $.getJSON('/getLensJson/' + this.selected, function(data) {
                    self.name = data.name;
                    self.factor = Math.round(data.factor * 100) / 100;
                });
            }

        }
    })
</script>
@endpush
