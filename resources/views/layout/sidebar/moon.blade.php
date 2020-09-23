<li>
    <script type="text/javascript" src="{{ URL::asset('js/degrees.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/astro.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/moon.js') }}"></script>

	<p>
        <br />
        <h4>
            @auth
                {{ _i("Moon / Sun") }}
            @endauth
            @guest
                {{ _i("Moon") }}
            @endguest
        </h4>
        <span style="font-weight:normal;">
            @php
                $datestr = Session::get('date');
                $date = DateTime::createFromFormat('d/m/Y', $datestr);
                $nextdate = clone $date;
                $nextdate = $nextdate->modify('+1 day');
                $nextdatestr = $nextdate->format('d/m/Y');
            @endphp
            {{ _i("on") }} {{ $datestr }} <svg width="1.5em" height="1.5em" viewBox="0 1 16 16" class="bi bi-arrow-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
              </svg> {{ $nextdatestr }}
        </span>
    </p>

    {{-- Only show the information on sun and moon if logged in, but no standard location is given. --}}
    @auth
    @if (Auth::user()->stdlocation != 0)
	<table class="table table-sm">
        <tr>
            <td> {{ _i("Moon") }} </td>
            @php
                // Moon rise and set
                $location = App\Models\Location::where('id', Auth::user()->stdlocation)->first();

                $objAstroCalc = new \App\Libraries\AstroCalc(
                    $date, $location->latitude, $location->longitude,
                    $location->timezone
                );

                // Use time zones and real coordinates of the location
                $location = \App\Models\Location::where('id', Auth::user()->stdlocation)->first();

                $moon = $objAstroCalc->calculateMoonRiseTransitSettingTime();
            @endphp

            <td>@if ($moon->getRising())
                {{ $moon->getRising()->timezone($location->timezone)->format('H:i') }}
            @else
                -
            @endif
            </td>
            <td>
            @if ($moon->getSetting())
                {{ $moon->getSetting()->timezone($location->timezone)->format('H:i') }}
            @else
                -
            @endif
            </td>
        </tr>
	    <tr>
            <td>{{ _i("Sun") }}</td>
            @php
                // Use time zones and real coordinates of the location
                $sun_info_down = date_sun_info($date->getTimestamp(), $location->latitude, $location->longitude);
                $sun_info_up = date_sun_info($nextdate->getTimestamp(), $location->latitude, $location->longitude);

                function printDate($riseset, $location) {
                    if ($riseset > 1) {
                        $tz = new DateTimeZone($location->timezone);

                        $date = new DateTime("@" . $riseset);
                        $date->setTimezone($tz);
                        print $date->format("H:i");
                    } else {
                        print "-";
                    }
                }
            @endphp

            <td>
                @php
                    printDate($sun_info_down["sunset"], $location);
                @endphp
            </td>
            <td>
                @php
                    printDate($sun_info_up["sunrise"], $location);
                @endphp
            </td>
	    </tr>
	    <tr>
            <td>{{ _i("Naut.") }}</td>
            <td>
                @php
                    printDate($sun_info_down["nautical_twilight_end"], $location);
                @endphp
            </td>
            <td>
                @php
                    printDate($sun_info_up["nautical_twilight_begin"], $location);
                @endphp

            </td>
	    </tr>
	    <tr>
            <td>{{ _i("Astro.") }}</td>
            <td>
                @php
                    printDate($sun_info_down["astronomical_twilight_end"], $location);
                @endphp
            </td>
            <td>
                @php
                    printDate($sun_info_up["astronomical_twilight_begin"], $location);
                @endphp

            </td>
	    </tr>
    </table>
    @endif
    @endauth

    <p>
        @php
            $moon = new Solaris\MoonPhase($date);

            $file = "img/moon/m" . round(($moon->getPhaseRatio()) * 40) . ".gif";
            $illumination = round($moon->illumination() * 100) . "%";
        @endphp
        <img src="{{ asset($file) }}" title={{ $illumination }} height="100px" width="100px" alt={{ $illumination }} />
    </p>

    @php
        setlocale(LC_TIME, LaravelGettext::getLocale());
        // Next New moon
        $next = gmdate( 'j/m/Y', $moon->getNextNewMoon() );

        $date = DateTime::createFromFormat('j/n/Y', $next);
        $newMoonDate = strftime("%e %b", $date->getTimestamp());
    @endphp
    {{ _i("New moon") }}: {{ $newMoonDate }}
</li>
