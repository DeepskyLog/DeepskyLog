@extends("layout.master")

@section('title')
    {{ $filter->name }}
@endsection

@section('content')
<table class="table table-sm">
    <tr>
        <th><h4>{{ $filter->name }}</h4></th>
        <th><img width="100" style="border-radius: 20%" src="/filter/{{ $filter->id }}/getImage"></th>
    </tr>
    <tr>
        <td>{{ _i("Type") }}</td>
        <td>{{ _i("Filter") }}</td>
    </tr>

    <tr>
        <td>{{ _i("Filter Type") }}</td>
        <td>{{ _i($filter->typeName()) }}</td>
    </tr>

    @if ($filter->type === 0 || $filter->type ===6)
        @if ($filter->color !== 0)
        <tr>
            <td>{{ _i("Color") }}</td>
            <td>{{ _i($filter->colorName()) }}</td>
        </tr>
        @endif

        @if ($filter->wratten !== 0)
        <tr>
            <td>{{ _i("Wratten number") }}</td>
            <td>{{ $filter->wratten }}</td>
        </tr>
        @endif

        @if ($filter->schott !== 0)
        <tr>
            <td>{{ _i("Schott number") }}</td>
            <td>{{ $filter->schott }}</td>
        </tr>
        @endif
    @endif

    <tr>
        <td>{{ _i("Owner") }}</td>
        <td><a href="/users/{{ $filter->user_id }}">{{  $filter->user->name }}</a></td>
    </tr>
    <tr>
        <td>{{ _i("Number of observations") }}</td>
        @if ($filter->observations > 0)
            <td><a href="/observation/filter/{{ $filter->id }}">{{  $filter->observations }}</a></td>
        @else
            <td>{{ $filter->observations }}</td>
        @endif
    </tr>

</table>

@auth
    @if (Auth::user()->id == $filter->user_id || Auth::user()->isAdmin())
    <a href="/filter/{{ $filter->id }}/edit">
        <button type="button" class="btn btn-sm btn-primary">
            {{ _i('Edit') }} {{  $filter->name }}
        </button>
    </a>
    @endif
@endauth
@endsection
