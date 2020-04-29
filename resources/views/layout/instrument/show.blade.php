@extends("layout.master")

@section('title')
    {{ $instrument->name }}
@endsection

@section('content')
<table class="table table-sm">
    <tr>
        <th><h4>{{ $instrument->name }}</h4></th>
        <th>
            @if ($media)
            <img style="border-radius: 20%" src="{{ $media->getUrl('thumb') }}" alt="{{ $instrument->name }}">
            @endif
        </th>
    </tr>
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
        @auth
        <td>{{ Auth::user()->showInches ? (number_format($instrument->diameter / 25.4, 2, '.', ',')) . ' ' . _i('inch') : $instrument->diameter . ' ' . _i('mm')}}</td>
        @endauth
        @guest
        <td>{{ $instrument->diameter . ' ' . _i('mm')}}</td>
        @endguest
    </tr>


    @if ($instrument->fd)
        <tr>
            <td>{{ _i("Focal Length") }}</td>
            @auth
            <td>{{ Auth::user()->showInches ? (number_format($instrument->fd * $instrument->diameter / 25.4, 2, '.' ,',')) . ' ' . _i('inch') : $instrument->fd * $instrument->diameter . ' ' . _i('mm')}} (F/{{ $instrument->fd }})</td>
            @endauth
            @guest
            <td>{{ $instrument->fd * $instrument->diameter . ' ' . _i('mm')}}</td>
            @endguest
            </tr>
    @endif

    @if ($instrument->fixedMagnification)
        <tr>
            <td>{{ _i("Fixed Magnification") }}</td>
            <td>{{ $instrument->fixedMagnification }}</td>
        </tr>
    @endif

    <tr>
        <td>{{ _i("Owner") }}</td>
        <td><a href="/users/{{ $instrument->user_id }}">{{  $instrument->user->name }}</a></td>
    </tr>
    <tr>
        <td>{{ _i("Number of observations") }}</td>
        @if ($instrument->observations > 0)
            <td><a href="/observation/instrument/{{ $instrument->id }}">{{  $instrument->observations }}</a></td>
        @else
            <td>{{ $instrument->observations }}</td>
        @endif
    </tr>

    <tr>
        <td>{{ _i("First light") }}</td>
        <td>ENTER FIRST LIGHT OR REMOVE IF NOT YET USED</td>
    </tr>

    <tr>
            <td>{{ _i("Last used on") }}</td>
            <td>ENTER LAST USED OR REMOVE IF NOT YET USED</td>
    </tr>

    @auth
    @if ($instrument->user_id == Auth::user()->id)
        <tr>
            <td>{{ _i("Used eyepieces") }}</td>
            <td>TODO</td>
        </tr>

        <tr>
            <td>{{ _i("Used filters") }}</td>
            <td>TODO</td>
        </tr>

        <tr>
            <td>{{ _i("Used lenses") }}</td>
            <td>TODO</td>
        </tr>

        <tr>
            <td>{{ _i("Observed in the following locations") }}</td>
            <td>ADD GOOGLE MAPS PAGE</td>
        </tr>
    @endif
    @endauth
</table>

@auth
    @if (Auth::user()->id == $instrument->user_id || Auth::user()->isAdmin())
    <a href="/instrument/{{ $instrument->id }}/edit">
        <button type="button" class="btn btn-sm btn-primary">
            {{ _i('Edit') }} {{  $instrument->name }}
        </button>
    </a>
    @endif
@endauth
@endsection
