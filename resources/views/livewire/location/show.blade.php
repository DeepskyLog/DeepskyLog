<div>
    <table class="table table-sm">
        <tr>
            <th>
                <h4>{{ $location->name }}</h4>
            </th>
            <th>
                @if ($media)
                <a href={{ $media->getUrl() }} data-lity>
                    <img style="border-radius: 20%" src="{{ $media->getUrl('thumb') }}" alt="{{ $location->name }}">
                </a>
                @endif
            </th>
        </tr>

        <tr>
            <td>{{ _i("Type") }}</td>
            <td>{{ _i("Location") }}</td>
        </tr>

        <tr>
            <td>{{ _i("Owner") }}</td>
            <td><a href="{{ route('users.show', $location->user) }}">{{ $location->user->name }}</a></td>
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
            <td>{{ _i("Length of night") }}</td>
            <td>
                {!! $location->getLengthOfNightPlot() !!}
            </td>
        </tr>
        <tr>
            <td>{{ _i("Today sunrise / sunset / transit") }}</td>
            <td>{{ $location->sunriseSetTransit() }}</td>
        </tr>
        <tr>
            <td>{{ _i("Today Civil Darkness") }}</td>
            <td>{{ $location->civilTwilight() }}</td>
        </tr>
        <tr>
            <td>{{ _i("Today Nautical Darkness") }}</td>
            <td>{{ $location->nauticalTwilight() }}</td>
        </tr>
        <tr>
            <td>{{ _i("Today Astronomical Darkness") }}</td>
            <td>{{ $location->astronomicalTwilight() }}</td>
        </tr>
        <tr>
            <td>{{ _i('Weather predictions') }}</td>
            <td>
                <a
                    href="http://clearoutside.com/forecast/{{ round($location->latitude, 2) }}/{{ round($location->longitude, 2) }}">
                    <img
                        src="http://clearoutside.com/forecast_image_small/{{ round($location->latitude, 2) }}/{{ round($location->longitude, 2) }}/forecast.png" />
                </a>
            </td>
        </tr>

    </table>

</div>
