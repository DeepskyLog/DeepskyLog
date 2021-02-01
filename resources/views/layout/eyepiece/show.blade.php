@extends("layout.master")

@section('title')
{{ $eyepiece->name }}
@endsection

@section('content')
<table class="table table-sm">
    <tr>
        <th>
            <h4>{{ $eyepiece->name }}</h4>
        </th>
        <th>
            @if ($media)
            <a href={{ $media->getUrl() }} data-lity>
                <img style="border-radius: 20%" src="{{ $media->getUrl('thumb') }}" alt="{{ $eyepiece->name }}">
            </a>
            @endif
        </th>
    </tr>

    <tr>
        <td>{{ _i("Type") }}</td>
        <td>{{ _i("Eyepiece") }}</td>
    </tr>

    <tr>
        <td>{{ _i("Generic name") }}</td>
        <td>{{ $eyepiece->genericname }}</td>
    </tr>

    <tr>
        <td>{{ _i("Focal Length") }}</td>
        <td>{{ $eyepiece->focalLength }} mm</td>
    </tr>

    <tr>
        <td>{{ _i("Apparent Field of View") }}</td>
        <td>{{ $eyepiece->apparentFOV }}&deg;</td>
    </tr>

    @if ($eyepiece->maxFocalLength)
    <tr>
        <td>{{ _i("Maximum Focal Length") }}</td>
        <td>{{ $eyepiece->maxFocalLength }} mm</td>
    </tr>
    @endif

    <tr>
        <td>{{ _i("Brand") }}</td>
        <td>{{  $eyepiece->brand }}</td>
    </tr>

    <tr>
        <td>{{ _i("Type") }}</td>
        <td>{{  $eyepiece->type }}</td>
    </tr>

    @auth
    @if ($eyepiece->user_id == Auth::user()->id)
    @if($eyepiece->sets()->count())
    <tr>
        <td>{{ _i('In equipment sets') }}</td>
        <td>
            <div class="trix-content">
                <ul>
                    @foreach($eyepiece->sets()->get() as $set)
                    <li><a href="/set/{{ $set->id }}">{{ $set->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </td>
    </tr>
    @endif
    <tr>
        <td>{{ _i("First observation") }}</td>
        <td>ENTER FIRST OBSERVATION OR REMOVE IF NOT YET USED</td>
    </tr>

    <tr>
        <td>{{ _i("Last observation") }}</td>
        <td>ENTER LAST OBSERVATION OR REMOVE IF NOT YET USED</td>
    </tr>
    @endif
    @endauth

    <tr>
        <td>{{ _i("Owner") }}</td>
        <td><a href="{{ route('users.show', $eyepiece->user->slug) }}">{{  $eyepiece->user->name }}</a></td>
    </tr>
    <tr>
        <td>{{ _i("Number of observations") }}</td>
        @if ($eyepiece->observations > 0)
        <td><a href="/observation/eyepiece/{{ $eyepiece->id }}">{{  $eyepiece->observations }}</a></td>
        @else
        <td>{{ $eyepiece->observations }}</td>
        @endif
    </tr>

</table>

@auth
@if (Auth::user()->id == $eyepiece->user_id || Auth::user()->isAdmin())
<a href="{{ route('eyepiece.edit', $eyepiece) }}">
    <button type="button" class="btn btn-sm btn-primary">
        {{ _i('Edit') }} {{  $eyepiece->name }}
    </button>
</a>
@endif
@endauth
@endsection
