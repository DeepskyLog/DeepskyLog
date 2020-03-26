@extends("layout.master")

@section('title')
    {{ $lens->name }}
@endsection

@section('content')
<table class="table table-sm">
    <tr>
        <th><h4>{{ $lens->name }}</h4></th>
        <th><img width="100" style="border-radius: 20%" src="/lens/{{ $lens->id }}/getImage"></th>
    </tr>
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
        <td><a href="/users/{{ $lens->user_id }}">{{  $lens->user->name }}</a></td>
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
    @if (Auth::user()->id == $lens->user_id || Auth::user()->isAdmin())
    <a href="/lens/{{  $lens->id }}/edit">
        <button type="button" class="btn btn-sm btn-primary">
            {{ _i('Edit') }} {{  $lens->name }}
        </button>
    </a>
    @endif
@endauth
@endsection
