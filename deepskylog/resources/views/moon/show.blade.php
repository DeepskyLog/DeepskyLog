<x-app-layout>
    @php
        $input =
            "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
        $timezone = $moon["timezone"] ?? "UTC";
        $clock = fn(?int $timestamp) => $timestamp !== null
            ? \Carbon\Carbon::createFromTimestamp($timestamp, $timezone)->format("H:i")
            : "—";
        $date = fn(string $night, string $format = "ddd LL") => \Carbon\Carbon::parse($night)
            ->locale(app()->getLocale())
            ->isoFormat($format);
        $degrees = fn(float $value, int $decimals = 1) => number_format($value, $decimals) . "°";
        $signed = fn(float $value) => ($value >= 0 ? "+" : "−") . number_format(abs($value), 1) . "°";
    @endphp

    <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        @if ($moon === null)
            <h1 class="text-xl font-semibold text-white">{{ __("The Moon") }}</h1>
            <p class="mt-2 text-sm text-gray-400">
                {{ __("Add a location with coordinates to see the Moon at your place.") }}
            </p>
        @else
            @php
                $ephemeris = $moon["ephemeris"];
                $upMoon = $moon["best"] !== null;
            @endphp
            <h1 class="text-xl font-semibold text-white">
                {{ __("The Moon in the night of :date", ["date" => $date($moon["night"])]) }}
            </h1>
            <p class="mt-1 text-sm text-gray-400">
                {{
                    __(
                        "On moonlit nights the Moon itself is the best object: craters, mountains and rilles stand out in relief near the terminator, the line between day and night, where the Sun is low and the shadows are long. The colongitude tells where the terminator is: the morning terminator lies at selenographic longitude 360° minus the colongitude and moves about 12° west every day.",
                    )
                }}
            </p>

            <form method="GET" action="{{ route("moon.show") }}" class="mt-4">
                <x-card>
                    <div class="grid grid-cols-1 gap-4 px-2 sm:grid-cols-2 lg:grid-cols-5">
                        <label class="block text-sm">
                            <span class="mb-1 block text-gray-400">{{ __("Night of") }}</span>
                            <input type="date" name="night" value="{{ $moon["night"] }}" class="{{ $input }} w-full" />
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block text-gray-400">{{ __("Time") }}</span>
                            <input type="time" name="time" value="{{ $time }}" class="{{ $input }} w-full" />
                            <span class="mt-1 block text-xs text-gray-500">{{ __("Empty: when the Moon is highest") }}</span>
                        </label>
                        @if ($locations)
                            <label class="block text-sm">
                                <span class="mb-1 block text-gray-400">{{ __("Location") }}</span>
                                <select name="location" class="{{ $input }} w-full">
                                    @if (! $own)
                                        <option value="">—</option>
                                    @endif
                                    @foreach ($locations as $id => $name)
                                        <option value="{{ $id }}" @selected($own && (int) $location->id === $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        <label class="block text-sm">
                            <span class="mb-1 block text-gray-400">{{ __("Orientation") }}</span>
                            <select name="view" class="{{ $input }} w-full">
                                <option value="north" @selected($orientation === "north")>{{ __("North up (naked eye, binoculars)") }}</option>
                                <option value="south" @selected($orientation === "south")>{{ __("South up (telescope)") }}</option>
                                <option value="mirror" @selected($orientation === "mirror")>{{ __("North up, mirrored (star diagonal)") }}</option>
                            </select>
                        </label>
                        <div class="flex items-end">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
                            >
                                {{ __("Show") }}
                            </button>
                        </div>
                    </div>
                </x-card>
            </form>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <figure class="flex flex-col items-center">
                    {!! $disk !!}
                    <figcaption class="mt-2 text-center text-xs text-gray-400">
                        {{
                            __("The Moon at :time, :place. The numbers are the features in the table below; the orange line is the terminator.", [
                                "time" => $clock($moon["moment"]),
                                "place" => $own
                                    ? html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, "UTF-8")
                                    : __("latitude :latitude", ["latitude" => $degrees((float) $location->latitude)]),
                            ])
                        }}
                    </figcaption>
                </figure>

                <table class="h-fit w-full text-sm text-gray-200">
                    <tbody class="divide-y divide-gray-700">
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Phase") }}</td>
                            <td>{{ \App\Services\Moon\LunarTerminator::phaseLabel($moon["phase"]) }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Illumination") }}</td>
                            <td>{{ round($ephemeris["fraction"] * 100) }}%</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Colongitude") }}</td>
                            <td>{{ $degrees($ephemeris["colongitude"]) }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Libration in longitude") }}</td>
                            <td>
                                {{ $signed($ephemeris["libration_lon"]) }}
                                <span class="text-gray-400">
                                    {{ $ephemeris["libration_lon"] >= 0 ? __("(the east limb, Mare Crisium, turned towards us)") : __("(the west limb, Grimaldi, turned towards us)") }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Libration in latitude") }}</td>
                            <td>
                                {{ $signed($ephemeris["libration_lat"]) }}
                                <span class="text-gray-400">
                                    {{ $ephemeris["libration_lat"] >= 0 ? __("(the north pole turned towards us)") : __("(the south pole turned towards us)") }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Position angle of the axis") }}</td>
                            <td>{{ $degrees($ephemeris["axis"]) }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Selenographic latitude of the Sun") }}</td>
                            <td>{{ $signed($ephemeris["sun_lat"]) }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-medium">{{ __("Moon up in the night") }}</td>
                            <td>
                                @if ($upMoon)
                                    {{ $clock($moon["up_from"]) }} – {{ $clock($moon["up_until"]) }},
                                    {{ __("highest at :time (:altitude)", ["time" => $clock($moon["best"]), "altitude" => $degrees($moon["best_altitude"], 0)]) }}
                                @else
                                    {{ __("The Moon is not up between sunset and sunrise.") }}
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-semibold text-white">
                        {{ __("At the terminator") }}
                    </h2>
                    @auth
                        @can ("create", \App\Models\ObservingList::class)
                            @if ($features)
                                <form method="POST" action="{{ route("moon.observing-list") }}">
                                    @csrf
                                    <input type="hidden" name="night" value="{{ $moon["night"] }}" />
                                    @if ($time)
                                        <input type="hidden" name="time" value="{{ $time }}" />
                                    @endif
                                    @if ($own)
                                        <input type="hidden" name="location" value="{{ $location->id }}" />
                                    @endif
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
                                    >
                                        {{ __("Make an observing list") }}
                                    </button>
                                </form>
                            @endif
                        @endcan
                    @endauth
                </div>
                <p class="mt-1 text-sm text-gray-400">
                    {{
                        __(
                            "The features with the Sun low above them, best seen in relief now: the well-known ones first, then the largest.",
                        )
                    }}
                </p>

                @if ($features)
                    @php
                        $shown = \App\Http\Controllers\MoonController::SHOWN_FEATURES;
                    @endphp
                    <div class="mt-3 overflow-x-auto" x-data="{ all: false }">
                        <table class="w-full text-left text-sm text-gray-200">
                            <thead class="text-xs text-gray-400 uppercase">
                                <tr>
                                    <th class="py-2 pr-3">#</th>
                                    <th class="py-2 pr-3">{{ __("Name") }}</th>
                                    <th class="py-2 pr-3">{{ __("Type") }}</th>
                                    <th class="py-2 pr-3 text-right">{{ __("Diameter") }}</th>
                                    <th class="py-2 pr-3 text-right">{{ __("Altitude of the Sun") }}</th>
                                    <th class="py-2 pr-3">{{ __("Sun") }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach ($features as $index => $feature)
                                    {{-- The rows after the first ones only after "Show all" --}}
                                    <tr @if ($index >= $shown) x-show="all" style="display: none" @endif>
                                        <td class="py-1 pr-3 text-gray-400">
                                            {{ $index < \App\Http\Controllers\MoonController::MARKERS ? $index + 1 : "" }}
                                        </td>
                                        <td class="py-1 pr-3">
                                            <a
                                                href="{{ route("object.show", $feature["slug"] ?: $feature["name"]) }}"
                                                class="text-blue-400 hover:underline"
                                                >{{ $feature["name"] }}</a
                                            >
                                            @if ($feature["showpiece"])
                                                <span class="text-amber-400" title="{{ __("A showpiece") }}">★</span>
                                            @endif
                                            @if ($feature["limb"])
                                                <span class="text-xs text-gray-400">({{ __("near the limb") }})</span>
                                            @endif
                                        </td>
                                        <td class="py-1 pr-3">{{ \App\Services\Moon\LunarTerminator::typeLabel($feature["type"]) }}</td>
                                        <td class="py-1 pr-3 text-right">{{ number_format($feature["diameter"], 0) }} km</td>
                                        <td class="py-1 pr-3 text-right">{{ $degrees($feature["sun_altitude"]) }}</td>
                                        <td class="py-1 pr-3">{{ $feature["rising"] ? __("Rising") : __("Setting") }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if (count($features) > $shown)
                            <button type="button" x-show="! all" x-on:click="all = true" class="mt-2 text-sm text-blue-400 hover:underline">
                                {{ __("Show all :count features", ["count" => count($features)]) }}
                            </button>
                        @endif
                    </div>
                @else
                    <p class="mt-3 text-sm text-gray-400">
                        {{ __("No lunar features are at the terminator on the side facing us now.") }}
                    </p>
                @endif
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-semibold text-white">{{ __("The coming nights") }}</h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-200">
                        <thead class="text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="py-2 pr-3">{{ __("Night of") }}</th>
                                <th class="py-2 pr-3">{{ __("Phase") }}</th>
                                <th class="py-2 pr-3 text-right">{{ __("Colongitude") }}</th>
                                <th class="py-2 pr-3">{{ __("Moon up") }}</th>
                                <th class="py-2 pr-3">{{ __("At the terminator") }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($upcoming as $night)
                                @php
                                    $nightQuery = array_filter([
                                        "night" => $night["night"],
                                        "location" => $own ? $location->id : null,
                                        "view" => $orientation === "north" ? null : $orientation,
                                    ]);
                                @endphp
                                <tr>
                                    <td class="py-1 pr-3 whitespace-nowrap">
                                        <a href="{{ route("moon.show", $nightQuery) }}" class="text-blue-400 hover:underline">{{ $date($night["night"], "ddd D MMM") }}</a>
                                    </td>
                                    <td class="py-1 pr-3 whitespace-nowrap">
                                        {{ \App\Services\Moon\LunarTerminator::phaseLabel($night["phase"]) }}
                                        ({{ round($night["ephemeris"]["fraction"] * 100) }}%)
                                    </td>
                                    <td class="py-1 pr-3 text-right">{{ $degrees($night["ephemeris"]["colongitude"], 0) }}</td>
                                    <td class="py-1 pr-3 whitespace-nowrap">
                                        @if ($night["best"] !== null)
                                            {{ $clock($night["up_from"]) }} – {{ $clock($night["up_until"]) }}
                                        @else
                                            <span class="text-gray-400">{{ __("Not at night") }}</span>
                                        @endif
                                    </td>
                                    <td class="py-1 pr-3">
                                        {{-- The names joined by commas: on separate lines Blade would put a space before each comma --}}
                                        {!!
                                            collect($night["features"])
                                                ->map(
                                                    fn(array $feature) => '<a href="' .
                                                        e(route("object.show", $feature["slug"] ?: $feature["name"])) .
                                                        '" class="text-blue-400 hover:underline">' .
                                                        e($feature["name"]) .
                                                        "</a>",
                                                )
                                                ->implode(", ")
                                        !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    {{ __("Each night at the moment the Moon is highest between sunset and sunrise.") }}
                    {{ __("Positions and diameters of the lunar features: IAU Gazetteer of Planetary Nomenclature.") }}
                </p>
            </div>
        @endif
    </div>
</x-app-layout>
