@extends("layout.master")

@section('title')
    {{ $lens->name }}
@endsection

@section('content')
<h4>
    {{ $lens->name }}
</h4>

<table class="table table-sm">
    <tr>
        <td>{{ _i("Type") }}</td>
        <td>{{ _i("Lens") }}</td>
    </tr>

    <tr>
        <td>{{ _i("Factor") }}</td>
        <td>{{  $lens->factor }}</td>
    </tr>
    <tr>
        <td>{{ _i("Owner") }}</td>
        <td><a href="/observer/{{ $lens->observer_id }}">{{  $lens->observer->name }}</a></td>
    </tr>
    <tr>
        <td>{{ _i("Number of observations") }}</td>
        @if ($lens->observations > 0)
            <td><a href="/observation/lens/{{ $lens->id }}">{{  $lens->observations }}</a></td>
        @else
            <td>{{  $lens->observations }}</td>
        @endif
    </tr>

</table>

@auth
    @if (Auth::user()->id === $lens->observer_id || Auth::user()->isAdmin())
    <a href="/lens/{{  $lens->id }}/edit">
        <button type="button" class="btn btn-sm btn-primary">
            Edit {{  $lens->name }}
        </button>
    </a>
    @endif
@endauth
@endsection
