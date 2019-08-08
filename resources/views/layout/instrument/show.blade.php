@extends("layout.master")

@section('title')
    {{ $instrument->name }}
@endsection

@section('content')
<h4>
    {{ $instrument->name }}
</h4>

<table class="table table-sm">
    <tr>
        <td>{{ _i("Type") }}</td>
        <td>{{ _i("Instrument") }}</td>
    </tr>

    <tr>
        <td>{{ _i("Instrument Type") }}</td>
        <td>{{ _i($instrument->typeName()) }}</td>
    </tr>

    <tr>
        <td>{{ _i("Diameter") }}</td>
        <td>{{ $instrument->diameter }}</td>
    </tr>

    <tr>
        <td>{{ _i("F/D") }}</td>
        <td>{{ $instrument->fd }}</td>
    </tr>

    <tr>
        <td>{{ _i("Focal Length") }}</td>
        <td>{{ $instrument->fd * $instrument->diameter }}</td>
    </tr>

    <tr>
        <td>{{ _i("Fixed Magnification") }}</td>
        <td>{{ $instrument->fixedMagnification }}</td>
    </tr>

    <tr>
        <td>{{ _i("Owner") }}</td>
        <td><a href="/users/{{ $instrument->observer_id }}">{{  $instrument->observer->name }}</a></td>
    </tr>
    <tr>
        <td>{{ _i("Number of observations") }}</td>
        @if ($instrument->observations > 0)
            <td><a href="/observation/instrument/{{ $instrument->id }}">{{  $instrument->observations }}</a></td>
        @else
            <td>{{ $instrument->observations }}</td>
        @endif
    </tr>

</table>

@auth
    @if (Auth::user()->id === $instrument->observer_id || Auth::user()->isAdmin())
    <a href="/instrument/{{ $instrument->id }}/edit">
        <button type="button" class="btn btn-sm btn-primary">
            Edit {{  $instrument->name }}
        </button>
    </a>
    @endif
@endauth
@endsection
