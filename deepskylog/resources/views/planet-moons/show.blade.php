<x-app-layout>
    @php
        $input =
            "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
        $timezone = $night["timezone"] ?? "UTC";
        $clock = fn(?int $timestamp) => $timestamp !== null
            ? \Carbon\Carbon::createFromTimestamp($timestamp, $timezone)->format("H:i")
            : "—";
        $date = fn(string $night, string $format = "ddd LL") => \Carbon\Carbon::parse(
            $night,
        )
            ->locale(app()->getLocale())
            ->isoFormat($format);
        $names = ["jupiter" => __("Jupiter"), "saturn" => __("Saturn")];
    @endphp

    <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        @if ($night === null)
            <h1 class="text-xl font-semibold text-white">
                {{
                    __(
                        "The moons of Jupiter and Saturn",
                    )
                }}
            </h1>
            <p class="mt-2 text-sm text-gray-400">
                {{
                    __(
                        "Add a location with coordinates to see the moons of Jupiter and Saturn at your place.",
                    )
                }}
            </p>
        @else
            <h1 class="text-xl font-semibold text-white">
                {{
                    __(
                        "The moons of Jupiter and Saturn in the night of :date",
                        ["date" => $date($night["night"])],
                    )
                }}
            </h1>
            <p class="mt-1 text-sm text-gray-400">
                {{
                    __(
                        "A small telescope shows the four Galilean moons of Jupiter changing places from hour to hour: they pass in front of the planet (a transit), cast their black shadows on its clouds, disappear in its shadow (an eclipse) or behind it (an occultation). The Great Red Spot is best seen when it crosses the central meridian. Titan, the largest moon of Saturn, is easiest to find at its greatest distance east or west of the planet.",
                    )
                }}
            </p>

            <form
                method="GET"
                action="{{ route("planet-moons.show") }}"
                class="mt-4"
            >
                <x-card>
                    <div
                        class="grid grid-cols-1 gap-4 px-2 sm:grid-cols-2 lg:grid-cols-5"
                    >
                        <label class="block text-sm">
                            <span
                                class="mb-1 block text-gray-400"
                                >{{ __("Night of") }}</span
                            >
                            <input
                                type="date"
                                name="night"
                                value="{{ $night["night"] }}"
                                class="{{ $input }} w-full"
                            />
                        </label>
                        <label class="block text-sm">
                            <span
                                class="mb-1 block text-gray-400"
                                >{{ __("Time") }}</span
                            >
                            <input
                                type="time"
                                name="time"
                                value="{{ $time }}"
                                class="{{ $input }} w-full"
                            />
                            <span class="mt-1 block text-xs text-gray-500">{{
                                __(
                                    "Empty: when the planet is highest",
                                )
                            }}</span>
                        </label>
                        @if ($locations)
                            <label class="block text-sm">
                                <span
                                    class="mb-1 block text-gray-400"
                                    >{{ __("Location") }}</span
                                >
                                <select
                                    name="location"
                                    class="{{ $input }} w-full"
                                >
                                    @if (!$own)
                                        <option value="">—</option>
                                    @endif
                                    @foreach ($locations as $id => $name)
                                        <option
                                            value="{{ $id }}"
                                            @selected ($own && (int) $location->id === $id)
                                        >
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        <label class="block text-sm">
                            <span class="mb-1 block text-gray-400">{{
                                __(
                                    "Orientation",
                                )
                            }}</span>
                            <select name="view" class="{{ $input }} w-full">
                                <option
                                    value="north"
                                    @selected ($orientation === "north")
                                >
                                    {{
                                        __(
                                            "North up (naked eye, binoculars)",
                                        )
                                    }}
                                </option>
                                <option
                                    value="south"
                                    @selected ($orientation === "south")
                                >
                                    {{
                                        __(
                                            "South up (telescope)",
                                        )
                                    }}
                                </option>
                                <option
                                    value="mirror"
                                    @selected ($orientation === "mirror")
                                >
                                    {{
                                        __(
                                            "North up, mirrored (star diagonal)",
                                        )
                                    }}
                                </option>
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

            @foreach (["jupiter", "saturn"] as $planet)
                @php
                    $window = $night["planets"][$planet];
                    $events = array_values(
                        array_filter(
                            $night["events"],
                            fn(array $event) => $event["planet"] === $planet,
                        ),
                    );
                @endphp
                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-white">
                        <a
                            href="{{ route("object.show", $planet) }}"
                            class="hover:underline"
                            >{{
                                $names[
                                    $planet
                                ]
                            }}</a
                        >
                    </h2>
                    <p class="mt-1 text-sm text-gray-400">
                        @if ($window["best"] !== null)
                            {{
                                __(
                                    "Observable from :from to :until, highest at :time (:altitude°).",
                                    [
                                        "from" => $clock($window["from"]),
                                        "until" => $clock($window["until"]),
                                        "time" => $clock($window["best"]),
                                        "altitude" => $window["best_altitude"],
                                    ],
                                )
                            }}
                        @else
                            {{
                                __(
                                    ":planet is not high enough in a dark sky this night.",
                                    ["planet" => $names[$planet]],
                                )
                            }}
                        @endif
                    </p>

                    <figure class="mt-3 rounded-md bg-gray-950 p-2">
                        <div
                            class="grid grid-cols-1 items-center gap-2 md:grid-cols-3"
                        >
                            <div class="md:col-span-2">
                                {!!
                                    $views[$planet][
                                        "svg"
                                    ]
                                !!}
                            </div>
                            <div class="mx-auto w-full max-w-xs">
                                {!!
                                    $views[$planet][
                                        "close_up"
                                    ]
                                !!}
                            </div>
                        </div>
                        <figcaption
                            class="mt-1 text-center text-xs text-gray-400"
                        >
                            {{
                                __(
                                    "At :time. The planet's equator is horizontal; black dots on the disk are shadows, a grey moon is in the planet's shadow.",
                                    [
                                        "time" => $clock($views[$planet]["moment"]),
                                    ],
                                )
                            }}
                        </figcaption>
                    </figure>

                    @if ($events)
                        <div class="mt-3 overflow-x-auto">
                            <table
                                class="w-full text-left text-sm text-gray-200"
                            >
                                <thead class="text-xs text-gray-400 uppercase">
                                    <tr>
                                        <th class="py-2 pr-3">
                                            {{ __("Time") }}
                                        </th>
                                        <th class="py-2 pr-3">
                                            {{ __("Event") }}
                                        </th>
                                        <th class="py-2 pr-3 text-right">
                                            {{ __("Altitude") }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    @foreach ($events as $event)
                                        <tr>
                                            <td
                                                class="py-1 pr-3 whitespace-nowrap"
                                            >
                                                <a
                                                    href="{{ route("planet-moons.show", array_filter(["night" => $night["night"], "time" => $clock($event["at"]), "location" => $own ? $location->id : null, "view" => $orientation === "north" ? null : $orientation])) }}"
                                                    class="text-blue-400 hover:underline"
                                                    title="{{ __("Show the moons at this moment") }}"
                                                    >{{
                                                        $clock(
                                                            $event["at"],
                                                        )
                                                    }}</a
                                                >
                                            </td>
                                            <td class="py-1 pr-3">
                                                {{
                                                    \App\Services\Planets\PlanetMoonEvents::label(
                                                        $event,
                                                    )
                                                }}
                                                @if (isset($event["distance"]))
                                                    <span class="text-gray-400"
                                                        >({{ $event["distance"] }}″)</span
                                                    >
                                                @endif
                                            </td>
                                            <td class="py-1 pr-3 text-right">
                                                {{ $event["altitude"] }}°
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($window["best"] !== null)
                        <p class="mt-3 text-sm text-gray-400">{{
                            __(
                                "No events of its moons can be seen this night.",
                            )
                        }}</p>
                    @endif
                </div>
            @endforeach

            <div class="mt-8">
                <h2 class="text-lg font-semibold text-white">
                    {{
                        __(
                            "The coming nights",
                        )
                    }}
                </h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-200">
                        <thead class="text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="py-2 pr-3">{{ __("Night of") }}</th>
                                <th class="py-2 pr-3">{{ __("Jupiter") }}</th>
                                <th class="py-2 pr-3">{{ __("Saturn") }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($upcoming as $coming)
                                <tr class="align-top">
                                    <td class="py-1 pr-3 whitespace-nowrap">
                                        <a
                                            href="{{ route("planet-moons.show", array_filter(["night" => $coming["night"], "location" => $own ? $location->id : null, "view" => $orientation === "north" ? null : $orientation])) }}"
                                            class="text-blue-400 hover:underline"
                                            >{{
                                                $date(
                                                    $coming["night"],
                                                    "ddd D MMM",
                                                )
                                            }}</a
                                        >
                                    </td>
                                    @foreach (["jupiter", "saturn"] as $planet)
                                        @php
                                            $comingEvents = array_values(
                                                array_filter(
                                                    $coming["events"],
                                                    fn(array $event) => $event["planet"] === $planet,
                                                ),
                                            );
                                        @endphp
                                        <td class="py-1 pr-3">
                                            @if ($coming["planets"][$planet]["best"] === null)
                                                <span class="text-gray-500">{{
                                                    __(
                                                        "Not at night",
                                                    )
                                                }}</span>
                                            @elseif ($comingEvents === [])
                                                <span class="text-gray-500"
                                                    >—</span
                                                >
                                            @else
                                                <ul>
                                                    @foreach ($comingEvents as $event)
                                                        <li>
                                                            <span
                                                                class="text-gray-400"
                                                                >{{
                                                                    \Carbon\Carbon::createFromTimestamp(
                                                                        $event["at"],
                                                                        $coming["timezone"],
                                                                    )->format("H:i")
                                                                }}</span
                                                            >
                                                            {{
                                                                \App\Services\Planets\PlanetMoonEvents::label(
                                                                    $event,
                                                                )
                                                            }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    {{
                        __(
                            "Only the events with the planet at least :altitude° high and the Sun at least :sun° below the horizon. The times are those of the centre of the moon or its shadow crossing the edge of the disk, good to about a minute (Meeus, Astronomical Algorithms, chapters 42, 44 and 46).",
                            [
                                "altitude" =>
                                    (int) \App\Services\Planets\PlanetMoonEvents::MIN_ALTITUDE,
                                "sun" => (int) abs(
                                    \App\Services\Planets\PlanetMoonEvents::SUN_ALTITUDE,
                                ),
                            ],
                        )
                    }} {{
                        __(
                            "The Great Red Spot is assumed at System II longitude :longitude°; it drifts, so its transits may be some minutes off.",
                            [
                                "longitude" => number_format(
                                    \App\Services\Planets\PlanetMoonEvents::grsLongitude(time()),
                                    0,
                                ),
                            ],
                        )
                    }}
                </p>
            </div>
        @endif
    </div>
</x-app-layout>
