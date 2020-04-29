@extends("layout.master")

@section('title')
    {{ $location->name }}
@endsection

@section('content')
<table class="table table-sm">
    <tr>
        <th><h4>{{ $location->name }}</h4></th>
        <th>
            @if ($media)
            <img style="border-radius: 20%" src="{{ $media->getUrl('thumb') }}" alt="{{ $location->name }}">
            @endif
        </th>
    </tr>

    <tr>
        <td>{{ _i("Type") }}</td>
        <td>{{ _i("Location") }}</td>
    </tr>

    <tr>
        <td>{{ _i("Owner") }}</td>
        <td><a href="/users/{{ $location->user_id }}">{{ $location->user->name }}</a></td>
    </tr>
    <tr>
        <td>{{ _i("Country") }}</td>
        <td>{{ Countries::getOne($location->country, LaravelGettext::getLocaleLanguage()) }}</td>
    </tr>
    <tr>
        <td>{{ _i("Elevation") }}</td>
        <td>{{ $location->elevation }} m</td>
    </tr>
    @if ($location->skyBackground != NULL)
    <tr>
        <td>{{ _i("SQM") }}</td>
        <td>{{ $location->skyBackground }}</td>
    </tr>
    @endif
    @if ($location->limitingMagnitude != NULL)
    <tr>
        <td>{{ _i("NELM") }}</td>
        <td>{{ $location->limitingMagnitude }}</td>
    </tr>
    @endif
    @if ($location->bortle != NULL)
    <tr>
        <td>{{ _i("Bortle") }}</td>
        <td>{{ $location->bortle }} -
            @if ($location->bortle == 1){{ _i("Excellent dark-sky site")}}@endif
            @if ($location->bortle == 2){{_i("Typical truly dark site")}}@endif
            @if ($location->bortle == 3){{_i("Rural sky")}}@endif
            @if ($location->bortle == 4){{_i("Rural/suburban transition")}}@endif
            @if ($location->bortle == 5){{_i("Suburban sky")}}@endif
            @if ($location->bortle == 6){{_i("Bright suburban sky")}}@endif
            @if ($location->bortle == 7){{_i("Suburban/urban transition")}}@endif
            @if ($location->bortle == 8){{_i("City sky")}}@endif
            @if ($location->bortle == 9){{_i("Inner-city sky")}}@endif
        </td>
    </tr>
    @endif

    @auth
    @if ($location->user_id == Auth::user()->id)
        <tr>
            <td>{{ _i("First observation") }}</td>
            <td>ENTER FIRST OBSERVATION OR REMOVE IF NOT YET USED</td>
        </tr>

        <tr>
            <td>{{ _i("Last observation") }}</td>
            <td>ENTER LAST OBSERVATION OR REMOVE IF NOT YET USED</td>
        </tr>
        <tr>
            <td>{{ _i("Used instruments") }}</td>
            <td>TODO</td>
        </tr>
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
    @endif
    @endauth

    <tr>
        <td>{{ _i("Number of observations") }}</td>
        @if ($location->observations > 0)
            <td><a href="/observation/location/{{ $location->id }}">{{  $location->observations }}</a></td>
        @else
            <td>{{ $location->observations }}</td>
        @endif
    </tr>
    <tr>
        <td>{{ _i('Weather predictions') }}</td>
        <td>
            <a href="http://clearoutside.com/forecast/{{ round($location->latitude, 2) }}/{{ round($location->longitude, 2) }}">
            <img src="http://clearoutside.com/forecast_image_small/{{ round($location->latitude, 2) }}/{{ round($location->longitude, 2) }}/forecast.png" />
            </a>
        </td>
    </tr>

</table>

<div id="map"></div>
@auth
    @if (Auth::user()->id == $location->user_id || Auth::user()->isAdmin())
    <br />
    <a href="/location/{{ $location->id }}/edit">
        <button type="button" class="btn btn-sm btn-primary">
            {{ _i('Edit') }} {{ $location->name }}
        </button>
    </a>
    @endif
@endauth
@endsection

@push('scripts')

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env("GOOGLEMAPS_KEY") }}&v=3.exp&language=en&libraries=places">

</script>

<script type="text/javascript">
    var map;
    var loca = new google.maps.LatLng({{ $location->latitude }}, {{ $location->longitude }});
    var myLocationMarker;
    var myLocations = [];

    function initialize() {
        map = new google.maps.Map(document.getElementById('map'), {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: loca,
            zoom: 15
        });
        myLocationMarker = new google.maps.Marker({
            map: map,
            position: loca,
            draggable: false
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);

</script>

@endpush
