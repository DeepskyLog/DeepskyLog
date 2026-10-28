@props (["slug", "name"])

{{-- On the page of a lunar feature: where it is, the colongitudes of its sunrise and sunset, the Sun above it tonight and the coming nights it is at the terminator --}}
@php
    $row = \Illuminate\Support\Facades\DB::table("lunar_features")
        ->where("slug", $slug)
        ->first() ?? \Illuminate\Support\Facades\DB::table("lunar_features")->where("name", $name)->first();
    $feature = $row && $row->lat !== null && $row->lon !== null && $row->diameter !== null
        ? ["name" => (string) $row->name, "lat" => (float) $row->lat, "lon" => (float) $row->lon, "diameter" => (float) $row->diameter]
        : null;
    $location = \App\Models\Location::forEphemerides(\Illuminate\Support\Facades\Auth::user());
    if ($feature && $location && $location->hasValidCoordinates()) {
        $timezone = \App\Support\EphemerisDate::timezone($location);
        $night = \App\Support\EphemerisDate::current($timezone);
        $moon = \App\Services\Moon\LunarTerminator::nightAt($location, $night, $timezone);
        $placement = \App\Services\Moon\LunarTerminator::placement($moon["ephemeris"], $feature);
        $nights = array_slice(\App\Services\Moon\LunarTerminator::nightsFor($feature, $location, $night, $timezone), 0, 8);
    }
    $coordinate = fn(float $value, string $positive, string $negative) => number_format(abs($value), 1) . "° " . ($value >= 0 ? $positive : $negative);
@endphp
@if ($feature)
    <div class="mt-6">
        <h2 class="text-xl font-semibold text-white">{{ __("At the terminator") }}</h2>
        <div class="mt-2 flex flex-col gap-4 sm:flex-row">
            @isset ($moon)
                <div class="w-40 flex-shrink-0">
                    {!! (new \App\Services\Moon\MoonDiskRenderer($moon["ephemeris"]))->svg([$feature]) !!}
                </div>
            @endisset
            <table class="h-fit w-full text-sm text-gray-200">
                <tbody class="divide-y divide-gray-700">
                    <tr>
                        <td class="py-1 pr-4 font-medium">{{ __("Selenographic position") }}</td>
                        <td>{{ $coordinate($feature["lat"], __("N"), __("S")) }}, {{ $coordinate($feature["lon"], __("E"), __("W")) }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 pr-4 font-medium">{{ __("Diameter") }}</td>
                        <td>{{ number_format($feature["diameter"], 0) }} km</td>
                    </tr>
                    <tr>
                        <td class="py-1 pr-4 font-medium">{{ __("Sunrise and sunset") }}</td>
                        <td>
                            {{
                                __("at colongitude :sunrise° and :sunset°", [
                                    "sunrise" => number_format(\App\Services\Moon\LunarTerminator::sunriseColongitude($feature["lon"]), 0),
                                    "sunset" => number_format(\App\Services\Moon\LunarTerminator::sunriseColongitude($feature["lon"] - 180), 0),
                                ])
                            }}
                        </td>
                    </tr>
                    @isset ($moon)
                        <tr>
                            <td class="py-1 pr-4 font-medium">
                                @php
                                    $at = \Carbon\Carbon::createFromTimestamp($moon["moment"], $timezone)->format("H:i");
                                @endphp
                                {{ $moon["best"] !== null ? __("Tonight at :time", ["time" => $at]) : __("At :time", ["time" => $at]) }}
                            </td>
                            <td>
                                @if ($placement["centre_distance"] > 90)
                                    {{ __("On the far side of the Moon.") }}
                                @elseif ($placement["sun_altitude"] <= 0)
                                    {{ __("In the lunar night.") }}
                                @else
                                    {{
                                        __("The Sun stands :altitude° above it (:direction).", [
                                            "altitude" => number_format($placement["sun_altitude"], 1),
                                            "direction" => $placement["rising"] ? __("rising") : __("setting"),
                                        ])
                                    }}
                                    @if ($placement["well_placed"] && $moon["best"] !== null)
                                        <span class="font-semibold text-amber-400">{{ __("Well placed!") }}</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Well placed in the coming nights") }}</td>
                            <td>
                                @forelse ($nights as $entry)
                                    <a
                                        href="{{ route("moon.show", ["night" => $entry["night"]]) }}"
                                        class="text-blue-400 hover:underline"
                                        >{{ \Carbon\Carbon::parse($entry["night"])->locale(app()->getLocale())->isoFormat("ddd D MMM") }}</a
                                    ><span class="text-gray-400"> ({{ $entry["rising"] ? __("rising") : __("setting") }})</span>@if (! $loop->last), @endif
                                @empty
                                    <span class="text-gray-400">{{ __("Not in the coming 30 nights.") }}</span>
                                @endforelse
                            </td>
                        </tr>
                    @endisset
                </tbody>
            </table>
        </div>
    </div>
@endif
