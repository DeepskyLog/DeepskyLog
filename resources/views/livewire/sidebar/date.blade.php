<div>
    &nbsp;&nbsp;
    <h4>
        {{ _i('Date') }}
    </h4>
    <form wire:submit.prevent="schedule">
        <input type="text" disable class="form-control form-control-sm" wire:model="carbonDateString" id="dateInput">
    </form>

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
            {{ _i("Night of ") }} {{ $date->format('d/m/Y') }} {{ _i(' to ') }}
            {{ $date->copy()->modify('+1 day')->format('d/m/Y') }}
        </span>
    </p>

    {{-- Only show the information on sun and moon if logged in, and if standard location is given. --}}
    @auth
    @if (Auth::user()->stdlocation != 0)
    @php
    $location = \App\Models\Location::where('id', Auth::user()->stdlocation)->first();

    $objAstroCalc = new \App\Libraries\astrocalc(
    $date,
    $location->latitude,
    $location->longitude,
    $location->timezone
    );

    $moon = $objAstroCalc->calculateMoonRiseTransitSettingTime();

    @endphp
    <table class="table table-sm">
        <tr>
            <td> {{ _i("Moon") }} </td>

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
            $sun_info_up = date_sun_info($date->copy()->modify('+1 day')->getTimestamp(), $location->latitude,
            $location->longitude);

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

    $nmdate = DateTime::createFromFormat('j/n/Y', $next);
    $newMoonDate = strftime("%e %b", $nmdate->getTimestamp());
    @endphp
    {{ _i("New moon") }}: {{ $newMoonDate }}


    <script type="text/javascript" src="{{ URL::asset('js/degrees.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/astro.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/moon.js') }}"></script>
    <script>
        function getMonth(currentMonth, dayClass) {
            return currentMonth + (dayClass.includes('prevMonthDay') ? -1 : (0 + Number(dayClass.includes('nextMonthDay'))));
        }
        flatpickr("#dateInput", {
            // Show the new moon in a different color
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                var newMoon = 0;
                var key = new Date(fp.currentYear, getMonth(fp.currentMonth, dayElem.className), dayElem.textContent);

                // Utilize dayElem.dateObj, which is the corresponding Date
                var year = dayElem.dateObj.getFullYear();
                var month = dayElem.dateObj.getMonth() + 1;
                var moon = new MoonQuarters(year, month, 1);
                var date = jdtocd(moon[0]);
                if ((new Date( date[1] + '/' + date[2] + '/'  + date[0] )).getTime() == key.getTime()) {
                    newMoon = 1;
                }

              	if (month - 1 < 1) {
        	        var moon = new MoonQuarters(year - 1, 12, 1);
      		    } else {
      	    	    var moon = new MoonQuarters(year, month - 1, 1);
              	}
                var date = jdtocd(moon[0]);
                if ((new Date( date[1] + '/' + date[2] + '/'  + date[0] )).getTime() == key.getTime()) {
                    newMoon = 1;
                }

       	    	if (month + 1 > 12) {
               		var moon = new MoonQuarters(year + 1, 1, 1);
               	} else {
        	        var moon = new MoonQuarters(year, month + 1, 1);
       	    	}
                var date = jdtocd(moon[0]);
                if ((new Date( date[1] + '/' + date[2] + '/'  + date[0] )).getTime() == key.getTime()) {
                    newMoon = 1;
                }

                if (newMoon)
                    dayElem.innerHTML = "<span class='event busy' style='background-color: #00FF00'>&nbsp;" + dayElem.innerHTML +
                        "&nbsp;</span>";
                },
                locale: "{{ \deepskylog\LaravelGettext\Facades\LaravelGettext::getLocaleLanguage() }}",
                defaultDate: "{{ $carbonDateString }}",
                allowInput: false,
                dateFormat: "@php if (\deepskylog\LaravelGettext\Facades\LaravelGettext::getLocaleLanguage() == 'en') echo 'F j, Y'; else echo 'j F Y'; @endphp"

        });
    </script>
</div>
