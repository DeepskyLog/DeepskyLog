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
        <td>{{  $lens->observer->name }}</td>
    </tr>
    <tr>
        <td>{{ _i("Number of observations") }}</td>
        <td>{{  $lens->id }}</td>
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
