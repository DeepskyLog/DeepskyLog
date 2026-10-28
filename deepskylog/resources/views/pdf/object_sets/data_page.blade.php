<div class="sheet">
    <h1>{{ __('Atlas pages for :object', ['object' => $object['name']]) }}</h1>

    <table class="facts">
        <tr>
            <td colspan="2"><span class="label">{{ __('Alternative names') }}:</span> {{ $object['alt_names'] === [] ? '-' : implode(', ', $object['alt_names']) }}</td>
            <td><span class="label">{{ __('Observer') }}:</span> {{ $observer !== '' ? $observer : '-' }}</td>
        </tr>
        <tr>
            <td><span class="label">{{ __('Right ascension') }}:</span> {{ $object['ra'] }}</td>
            <td><span class="label">{{ __('Magnitude') }}:</span> {{ $object['mag'] }}</td>
            <td><span class="label">{{ __('Constellation') }}:</span> {{ $object['constellation'] }}</td>
        </tr>
        <tr>
            <td><span class="label">{{ __('Declination') }}:</span> {{ $object['decl'] }}</td>
            <td><span class="label">{{ __('Surface brightness') }}:</span> {{ $object['subr'] }}</td>
            <td><span class="label">{{ __('Type') }}:</span> {{ $object['type'] }}</td>
        </tr>
        <tr>
            <td><span class="label">{{ __('Size') }}:</span> {{ $object['size'] }}</td>
            <td><span class="label">{{ __('Position angle') }}:</span> {{ $object['pa'] }}</td>
            <td><span class="label">{{ __('Observations in DeepskyLog') }}:</span> {{ $object['seen'] }}</td>
        </tr>
        <tr>
            <td></td>
            <td><span class="label">{{ __('Your observations') }}:</span> {{ $object['your_seen'] }}</td>
            <td><span class="label">{{ __('Last seen by you') }}:</span> {{ $object['last_seen'] }}</td>
        </tr>
    </table>

    @if ($object['description'] !== '')
        <h2>{{ __('Description') }}</h2>
        <div>{{ $object['description'] }}</div>
    @endif

    @if (!empty($night))
        <h2>{{ __('Ephemerides for :from - :to in :location (local time)', ['from' => $night['date'], 'to' => $night['next_date'], 'location' => $night['location']]) }}</h2>
        <table class="facts">
            <tr>
                <td><span class="label">{{ __('Sunset') }}:</span> {{ $night['sunset'] }}</td>
                <td><span class="label">{{ __('Sunrise') }}:</span> {{ $night['sunrise'] }}</td>
                <td><span class="label">{{ __('Moonrise') }} / {{ __('Moonset') }}:</span> {{ $night['moon_rise'] }} / {{ $night['moon_set'] }}</td>
            </tr>
            <tr>
                <td><span class="label">{{ __('Nautical twilight') }}:</span> {{ __(':from to :to', ['from' => $night['nautical_end'], 'to' => $night['nautical_begin']]) }}</td>
                <td><span class="label">{{ __('Astronomical twilight') }}:</span> {{ __(':from to :to', ['from' => $night['astronomical_end'], 'to' => $night['astronomical_begin']]) }}</td>
                <td><span class="label">{{ __('Best time') }}:</span> {{ $night['best_time'] }}</td>
            </tr>
            <tr>
                <td><span class="label">{{ __('Rise') }}:</span> {{ $night['rise'] }}</td>
                <td><span class="label">{{ __('Transit') }}:</span> {{ $night['transit'] }}</td>
                <td><span class="label">{{ __('Set') }}:</span> {{ $night['set'] }}</td>
            </tr>
            <tr>
                <td><span class="label">{{ __('Maximum altitude') }}:</span> {{ $night['max_altitude'] }}</td>
                <td colspan="2"><span class="label">{{ __('Maximum altitude during astronomical darkness') }}:</span> {{ $night['max_altitude_night'] }}</td>
            </tr>
        </table>
    @endif

    @if (!empty($year))
        <h2>{{ __('Ephemerides for :object in :location in :year (local time)', ['object' => $object['name'], 'location' => $year['location'], 'year' => $year['year']]) }}</h2>
        @foreach (array_chunk($year['dates'], 12) as $half)
            <table class="year" style="margin-bottom: 2mm;">
                <tr>
                    <th class="rowhead">{{ __('Date') }}</th>
                    @foreach ($half as $d)
                        <th>{{ $d['label'] }}</th>
                    @endforeach
                </tr>
                <tr>
                    <td class="rowhead">{{ __('Max. altitude') }}</td>
                    @foreach ($half as $d)
                        <td>{{ $d['max_alt_night'] }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td class="rowhead">{{ __('Transit') }}</td>
                    @foreach ($half as $d)
                        <td>{{ $d['transit'] }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td class="rowhead">{{ __('Astronomical night') }}</td>
                    @foreach ($half as $d)
                        <td>{!! str_replace(' - ', '<br />', e($d['night'])) !!}</td>
                    @endforeach
                </tr>
                <tr>
                    <td class="rowhead">{{ __('Rise - set') }}</td>
                    @foreach ($half as $d)
                        <td>{{ $d['rise'] }}<br />{{ $d['set'] }}</td>
                    @endforeach
                </tr>
            </table>
        @endforeach
        <div class="muted" style="font-size: 7pt;">{{ __('Max. altitude is the highest the object gets during astronomical darkness.') }}</div>
    @endif

</div>
