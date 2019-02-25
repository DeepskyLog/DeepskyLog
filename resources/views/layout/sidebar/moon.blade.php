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
            <script>
                // TODO: Add moon rise / set
                // TODO: Next day for moonrise
                // TODO: Timezones
                // TODO: Moon phase picture

                year = 2019;
                month = 2;
                day = 25;
                latitude = 50.8322;
                longitude = 4.86463;
                TZ = 60;
                var moonRiseSet = new MoonRise(year,month,day,TZ,latitude,longitude);
                //alert(moonRiseSet);
            </script>

            <td>12:01</td>
            <td>22:24</td>
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
        <img src="{{ asset('img/moon/m7.gif') }}" title="45%" height="100px" width="100px" alt="45%" />
    </p>

    @php
        setlocale(LC_TIME, LaravelGettext::getLocale());
        $date = DateTime::createFromFormat('j/n/Y', '3/1/2019');
        $newMoonDate = strftime("%e %b", $date->getTimestamp());
    @endphp
    {{ _i("New moon") }}: {{ $newMoonDate }}
</li>
