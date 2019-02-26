<li>
    <script type="text/javascript" src="{{ URL::asset('js/degrees.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/astro.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/moon.js') }}"></script>

	<p>
        <br />
        <h4>
	        {{ _i("Moon / Sun") }}
        </h4>
        <span style="font-weight:normal;">
            {{ _i("on") }} 28/02&gt;&lt;01/03/2019
        </span>
	</p>
	<table class="table table-sm">
	    <tr>
            <td> {{ _i("Moon") }} </td>
            @php
                // TODO: Use real location (timezone) and date.
                // Moon rise and set
                use App\Libraries\AstroCalc;

                $objAstroCalc = new AstroCalc(2, 25, 2019, 50.8322, 4.86463, "Europe/Brussels");

                $moon = $objAstroCalc->calculateMoonRiseTransitSettingTime();
            @endphp

            <td>{{ $moon[0] }}</td>
            <td>{{ $moon[2] }}</td>
	    </tr>
	    <tr>
            <td>{{ _i("Sun") }}</td>
            @php
                // TODO: Use time zones and real coordinates of the location
                $sun_info_down = date_sun_info(strtotime("02/25/2019"), 50.8322, 4.86463);
                $sun_info_up = date_sun_info(strtotime("02/26/2019"), 50.8322, 4.86463);

                function printDate($riseset) {
                    if ($riseset > 1) {
                        $tz = new DateTimeZone('Europe/Brussels');

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
                    printDate($sun_info_down["sunset"]);
                @endphp
            </td>
            <td>
                @php
                    printDate($sun_info_up["sunrise"]);
                @endphp
            </td>
	    </tr>
	    <tr>
            <td>{{ _i("Naut.") }}</td>
            <td>
                @php
                    printDate($sun_info_down["nautical_twilight_end"]);
                @endphp
            </td>
            <td>
                @php
                    printDate($sun_info_up["nautical_twilight_begin"]);
                @endphp

            </td>
	    </tr>
	    <tr>
            <td>{{ _i("Astro.") }}</td>
            <td>
                @php
                    printDate($sun_info_down["astronomical_twilight_end"]);
                @endphp
            </td>
            <td>
                @php
                    printDate($sun_info_up["astronomical_twilight_begin"]);
                @endphp

            </td>
	    </tr>
	</table>

    <p>
        @php
            // TODO: Use correct date
            $moon = new Solaris\MoonPhase();

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
