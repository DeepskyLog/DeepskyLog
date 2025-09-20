{{-- Avoid inline `use` in Blade; Location is available as $location --}}
<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-4 grid-cols-1">
                <div class="md:col-span-1 col-span-1">
                    <img class="w-full md:w-64 mx-auto object-cover" src="{{ $image }}"
                         alt="{{ $location->name }}">

                    @if (!empty($location->description))
                        <div class="w-full md:w-64 mx-auto mt-4 p-3 border border-gray-700 bg-gray-800 text-gray-100 rounded">
                            {!! $location->description !!}
                        </div>
                    @endif

                    @php
                        // Compute auth flags safely to avoid errors when no user is logged in
                        $isLoggedIn = Auth::check();
                        $isOwner = $isLoggedIn && Auth::user()->id == $location->user_id;
                        $isAdmin = $isLoggedIn && Auth::user()->isAdministrator();
                        $fstOffset = $isLoggedIn ? (Auth::user()->fstOffset ?? 0) : 0;
                        $showInches = $isLoggedIn && (Auth::user()->showInches ?? false);
                    @endphp

                    @if (!$location->hidden || $isOwner || $isAdmin)
                        <div id="location-map" class="w-full md:w-64 h-64 mx-auto mt-4 rounded overflow-hidden" style="z-index:1;"></div>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <h4 class="font-bold text-xl">{{ $location->name }}
                        @if (!$location->active)
                            <div class="text-sm">{{ __("(Not active anymore)") }}</div>
                        @endif
                    </h4>
                    <br/>
                    <table class="table-auto w-full">

                        @if (!$location->hidden || $isOwner || $isAdmin)
                            <tr>
                                <td>{{ __("Latitude") }}</td>
                                <td>{{ \App\Models\Location::dms($location->latitude, true) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __("Longitude") }}</td>
                                <td>{{ \App\Models\Location::dms($location->longitude, false) }}</td>
                            </tr>

                            <tr>
                                <td>{{ __("Elevation") }}</td>
                                <td>
                                    @if ($showInches)
                                        {{ intval($location->elevation * 3.28084) }} {{ __('ft') }}
                                    @else
                                        {{ __($location->elevation) }} {{ __('m') }}
                                    @endif
                                </td>
                            </tr>

                        @endif

                        <tr>
                            <td>{{ __("Country") }}</td>
                            <td>{{ $location->country ?? __('Unknown') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __("Timezone") }}</td>
                            <td>{{ $location->timezone ?? __('Unknown') }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('SQM') }}</td>
                            <td>
                                {{ $location->getSqm($fstOffset) ?? __('Unknown') }}
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('NELM') }}</td>
                            <td>
                                {{ $location->getNelm($fstOffset) ?? __('Unknown') }}
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('Bortle') }}</td>
                            <td>
                                {{ $location->getBortle() ?? __('Unknown') }}
                            </td>
                        </tr>

                        @if (!$location->hidden || $isOwner || $isAdmin)
                            <tr>
                                <td>{{ __('Length of night') }}</td>
                                <td>
                                    {!! $location->getLengthOfNightPlot() !!}
                                </td>
                            </tr>
                            <tr>
                                <td>{{ __('Today sunrise / sunset / transit') }}</td>
                                <td>{{ $location->sunriseSetTransit() }}</td>
                            </tr>

                            <tr>
                                <td>{{ __('Today Civil Darkness') }}</td>
                                <td>{{ $location->civilTwilight() }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Today Nautical Darkness') }}</td>
                                <td>{{ $location->nauticalTwilight() }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('Today Astronomical Darkness') }}</td>
                                <td>{{ $location->astronomicalTwilight() }}</td>
                            </tr>
                        @endif

                        @if ($isOwner || $isAdmin)
                            <tr>
                                <td>{{ __("Details visible for other users") }}</td>
                                <td>
                                    @if($location->hidden)
                                        {{ __('No') }}
                                    @else
                                        {{ __('Yes') }}
                                    @endif
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <td>{{ __("Owner") }}</td>
                            <td>
                                <a href="{{ route('observer.show', $location->user->slug) }}">{{  $location->user->name }}</a>
                            </td>
                        </tr>


                        <tr>
                            <td>{{ __("Number of observations") }}</td>
                            <td>
                                <a href="/observation/location/{{ $location->id }}">
                                    {{  $location->observations }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __("First observation") }}</td>
                            <td>
                                @php
                                    $first_observation_date = $location->first_observation_date();
                                    $last_observation_date = $location->last_observation_date();
                                @endphp
                                @if (! is_null($first_observation_date[0]))
                                    <a
                                        href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $first_observation_date[1] }}"
                                    >
                                        {{$first_observation_date[0] }}
                                    </a>
                                @else
                                    {{ __("No observations added!") }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>{{ __("Last observation") }}</td>
                            <td>
                                @if (! is_null($last_observation_date[0]))
                                    <a
                                        href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $last_observation_date[1] }}"
                                    >
                                        {{ $last_observation_date[0] }}
                                    </a>
                                @else
                                    {{ __("No observations added!") }}
                                @endif
                            </td>
                        </tr>

                        @auth
                            @if ($isOwner)
                                <tr>
                                    <td>{{ __("Used instruments") }}</td>
                                    <td>{!! $location->get_used_instruments_as_string() !!}</td>
                                </tr>

                            @endif
                        @endauth

                        <tr>
                            <td>{{ __('Weather Prospects') }}</td>
                            <td>
                                <a href="https://clearoutside.com/forecast/{{ round($location->latitude, 2) }}/{{ round($location->longitude, 2) }}">
                                    <img alt="Weather forecast" src="https://clearoutside.com/forecast_image_small/{{ round($location->latitude, 2) }}/{{ round($location->longitude, 2) }}/forecast.png" />
                                </a>
                            </td>
                        </tr>
                    </table>

                    @auth
                        @if ($isOwner || $isAdmin)
                            <br/>
                            <a href="/location/{{$location->user->slug}}/{{$location->slug }}/edit">
                                <x-button type="submit" secondary label="{{ __('Edit') }} {!! $location->name !!}"/>
                            </a>
                        @endif

                        <br/>
                        <br/>
                    @endauth

                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        @if (!$location->hidden || $isOwner || $isAdmin)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                        var map = L.map('location-map', { fullscreenControl: true }).setView([
                            {{ $location->latitude }},
                            {{ $location->longitude }}
                        ], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);

                        // Prepare locations data (owners see all their locations; others only the current location)
                        @php
                            if ($isOwner) {
                                $mapLocations = $location->user->locations->map(function($loc) use ($fstOffset) {
                                    return [
                                        'id' => $loc->id,
                                        'name' => $loc->name,
                                        'latitude' => $loc->latitude,
                                        'longitude' => $loc->longitude,
                                        'sqm' => $loc->getSqm($fstOffset) ?? 'Unknown',
                                        'nelm' => $loc->getNelm($fstOffset) ?? 'Unknown',
                                        'bortle' => $loc->getBortle() ?? 'Unknown',
                                    ];
                                })->toArray();
                            } else {
                                $mapLocations = [[
                                    'id' => $location->id,
                                    'name' => $location->name,
                                    'latitude' => $location->latitude,
                                    'longitude' => $location->longitude,
                                    'sqm' => $location->getSqm($fstOffset) ?? 'Unknown',
                                    'nelm' => $location->getNelm($fstOffset) ?? 'Unknown',
                                    'bortle' => $location->getBortle() ?? 'Unknown',
                                ]];
                            }
                        @endphp

                        var locations = @json($mapLocations);

                        locations.forEach(function(loc) {
                            var popupContent = `<strong>${loc.name}</strong><br>` +
                                `SQM: ${loc.sqm}<br>` +
                                `NELM: ${loc.nelm}<br>` +
                                `Bortle: ${loc.bortle}`;
                            var marker = L.marker([loc.latitude, loc.longitude]).addTo(map)
                                .bindPopup(popupContent);
                            if (loc.id === {{ $location->id }}) {
                                marker.openPopup();
                                marker.setZIndexOffset(1000);
                            }
                        });
                });
            </script>
        @endif
    @endpush
</x-app-layout>
