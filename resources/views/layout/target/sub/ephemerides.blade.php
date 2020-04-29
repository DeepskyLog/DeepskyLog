@if ($target->ra != null && $target->decl != null)
<div id="ephemeridesdiv">

<h4>{{ _i("Ephemerides for %s in %s",
        $target->name,
        \App\Location::where('id', Auth::user()->stdlocation)->first()->name) }}
</h4>
<hr />
@php
    $ephemerides = $target->getYearEphemerides();
@endphp

<table class="table table-sm table-striped">
    <tr class="thead-dark">
        <th>{{ _i("Month") }} > </th>

        @foreach ($ephemerides as $ephem)
            <th>{{ $ephem['count'] }}</th>
        @endforeach
    </tr>

    <tr>
        <td class="centered">{{ _i("Max Alt") }}</td>

        @foreach ($ephemerides as $ephem)
            <td class="centered {{ $ephem['max_alt_color'] }}">
                <span class="" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="{{ $ephem['max_alt_popup']}}">
                    {!! $ephem['max_alt'] !!}
                </span>
            </td>

        @endforeach
    </tr>

    <tr>
        <td class="centered">{{ _i("Transit") }}</td>

        @foreach ($ephemerides as $ephem)
            <td class="centered {{ $ephem['transit_color'] }}">
                <span class="" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="{{ $ephem['transit_popup']}}">
                    {{ $ephem['transit'] }}
                </span>
            </td>
        @endforeach
    </tr>

    <tr>
        <td class="centered">{{ _i("Astronomical night") }}</td>

        @foreach ($ephemerides as $ephem)
            <td class="centered">
                {{ $ephem['astronomical_twilight_end'] ? $ephem['astronomical_twilight_end']->format("H:i") : '' }}
                <br />-<br />
                {{ $ephem['astronomical_twilight_begin'] ? $ephem['astronomical_twilight_begin']->format("H:i") : '' }}
            </td>
        @endforeach
    </tr>

    <tr>
        <td class="centered">{{ _i("Nautical night") }}</td>

        @foreach ($ephemerides as $ephem)
            <td class="centered">
                {{ $ephem['nautical_twilight_end'] ? $ephem['nautical_twilight_end']->format("H:i") : '' }}
                <br />-<br />
                {{ $ephem['nautical_twilight_begin'] ? $ephem['nautical_twilight_begin']->format("H:i") : '' }}
            </td>
        @endforeach
    </tr>

    <tr>
        <td class="centered">{!! _i("Object rise<br />-<br />set") !!}</td>

        @foreach ($ephemerides as $ephem)
            <td class="centered {{ $ephem['rise_color'] }}">
                <span class="" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="{{ $ephem['rise_popup']}}">
                    {{ $ephem['rise'] != '-' ? $ephem['rise'] : '' }}
                    <br />-<br />
                    {{ $ephem['set'] != '-' ? $ephem['set'] : '' }}
                </span>
            </td>
        @endforeach
    </tr>

</table>
<hr />
</div>
@endif
