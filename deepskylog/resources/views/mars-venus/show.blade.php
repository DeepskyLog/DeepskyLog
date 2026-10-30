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
        $moment = fn(
            int $timestamp,
            string $format = "ddd LL, HH:mm",
        ) => \Carbon\Carbon::createFromTimestamp($timestamp, $timezone)
            ->locale(app()->getLocale())
            ->isoFormat($format);
        $names = ["mars" => __("Mars"), "venus" => __("Venus")];
        $seasons = [
            "spring" => __("spring in the northern hemisphere, autumn in the southern"),
            "summer" => __("summer in the northern hemisphere, winter in the southern"),
            "autumn" => __("autumn in the northern hemisphere, spring in the southern"),
            "winter" => __("winter in the northern hemisphere, summer in the southern"),
        ];
        $eventNames = [
            "opposition" => __("Opposition"),
            "closest_approach" => __("Closest to the Earth"),
            "greatest_elongation_east" => __("Greatest elongation east (evening sky)"),
            "greatest_elongation_west" => __("Greatest elongation west (morning sky)"),
            "dichotomy" => __("Dichotomy: half the disk lit"),
            "inferior_conjunction" => __(
                "Inferior conjunction (between the Earth and the Sun)",
            ),
            "superior_conjunction" => __("Superior conjunction (behind the Sun)"),
        ];
        $query = fn(array $extra = []) => array_filter(
            array_merge(
                [
                    "night" => $night["night"] ?? null,
                    "location" => isset($own) && $own ? $location->id : null,
                    "view" =>
                        isset($orientation) && $orientation !== "north"
                            ? $orientation
                            : null,
                    "feature" => $feature ?? null,
                ],
                $extra,
            ),
            fn($value) => $value !== null,
        );
    @endphp

    <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        @if ($night === null)
            <h1 class="text-xl font-semibold text-white">
                {{
                    __(
                        "Mars and Venus",
                    )
                }}
            </h1>
            <p class="mt-2 text-sm text-gray-400">
                {{
                    __(
                        "Add a location with coordinates to see Mars and Venus at your place.",
                    )
                }}
            </p>
        @else
            <h1 class="text-xl font-semibold text-white">
                {{
                    __(
                        "Mars and Venus in the night of :date",
                        ["date" => $date($night["night"])],
                    )
                }}
            </h1>
            <p class="mt-1 text-sm text-gray-400">
                {{
                    __(
                        "Mars turns once in 24 hours 37 minutes, so the face it shows changes through the night and comes back at the same hour only after some five weeks. Its dark markings, polar caps and bright basins show best in the months around opposition, when the disk is largest. Venus shows no markings in visual light, but its phase changes from a small round disk behind the Sun to a large thin crescent before it passes between us and the Sun.",
                    )
                }}
            </p>

            <form
                method="GET"
                action="{{ route("mars-venus.show") }}"
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
                        <div class="flex items-center">
                            @if ($feature)
                                <input
                                    type="hidden"
                                    name="feature"
                                    value="{{ $feature }}"
                                />
                            @endif

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

            @foreach (["mars", "venus"] as $planet)
                @php
                    $window = $night["planets"][$planet];
                    $disk = $planets[$planet]["disk"];
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
                        @elseif ($planet === "venus")
                            {{
                                __(
                                    "Venus is too close to the Sun to be seen after sunset or before sunrise this night.",
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

                        @if ($planet === "venus" && $window["best"] !== null)
                            {{
                                __(
                                    "Venus is seen best high in a bright sky: in the twilight, or by day with a telescope that is kept well away from the Sun.",
                                )
                            }}
                        @endif
                    </p>

                    <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <figure
                            class="mx-auto w-full max-w-xs rounded-md bg-gray-950 p-2"
                        >
                            {!!
                                $planets[$planet][
                                    "svg"
                                ]
                            !!}
                            <figcaption
                                class="mt-1 text-center text-xs text-gray-400"
                            >
                                {{
                                    __("At :time.", [
                                        "time" => $moment($disk["at"]),
                                    ])
                                }}
                                @if ($planet === "mars")
                                    {{
                                        __(
                                            "The features and polar caps are schematic; the dashed line is the central meridian.",
                                        )
                                    }}
                                @endif
                            </figcaption>
                        </figure>

                        <div class="md:col-span-2">
                            <dl
                                class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm sm:grid-cols-3"
                            >
                                <div>
                                    <dt class="text-gray-400">
                                        {{ __("Diameter") }}
                                    </dt>
                                    <dd class="text-gray-200">
                                        {{
                                            number_format(
                                                $disk["diameter"],
                                                1,
                                            )
                                        }}″
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">
                                        {{
                                            __(
                                                "Magnitude",
                                            )
                                        }}
                                    </dt>
                                    <dd class="text-gray-200">
                                        {{
                                            $disk["magnitude"] === null
                                                ? "—"
                                                : number_format($disk["magnitude"], 1)
                                        }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">
                                        {{
                                            __(
                                                "Illuminated",
                                            )
                                        }}
                                    </dt>
                                    <dd class="text-gray-200">
                                        {{
                                            round(
                                                $disk["illuminated"] * 100,
                                            )
                                        }}%
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">
                                        {{
                                            __(
                                                "Elongation",
                                            )
                                        }}
                                    </dt>
                                    <dd class="text-gray-200">
                                        {{
                                            round(
                                                abs($disk["elongation"]),
                                            )
                                        }}° {{
                                            $disk["elongation"] >= 0
                                                ? __("east of the Sun (evening sky)")
                                                : __("west of the Sun (morning sky)")
                                        }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">
                                        {{ __("Distance") }}
                                    </dt>
                                    <dd class="text-gray-200">
                                        {{
                                            number_format(
                                                $disk["distance"],
                                                3,
                                            )
                                        }} {{ __("AU") }}
                                    </dd>
                                </div>
                                @if ($planet === "mars")
                                    <div>
                                        <dt class="text-gray-400">
                                            {{
                                                __(
                                                    "Central meridian",
                                                )
                                            }}
                                        </dt>
                                        <dd class="text-gray-200">
                                            {{ round($disk["cm"]) }}° {{ __("W") }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-400">
                                            {{
                                                __(
                                                    "Tilt towards us",
                                                )
                                            }}
                                        </dt>
                                        <dd class="text-gray-200">
                                            {{
                                                $disk["de"] >= 0
                                                    ? __("north pole, :degrees°", ["degrees" => round($disk["de"])])
                                                    : __("south pole, :degrees°", [
                                                        "degrees" => round(-$disk["de"]),
                                                    ])
                                            }}
                                        </dd>
                                    </div>
                                    <div class="col-span-2">
                                        <dt class="text-gray-400">
                                            {{ __("Season") }}
                                        </dt>
                                        <dd class="text-gray-200">
                                            Ls {{ round($disk["ls"]) }}°: {{
                                                $seasons[
                                                    \App\Services\Planets\MarsFeatures::season($disk["ls"])
                                                ]
                                            }}
                                            @if (\App\Services\Planets\MarsFeatures::dustStormSeason( $disk["ls"] ))
                                                <span class="text-amber-400">{{
                                                    __(
                                                        "(the season of the dust storms)",
                                                    )
                                                }}</span>
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            @if ($planet === "mars")
                                <h3
                                    class="mt-4 text-sm font-semibold text-white"
                                >
                                    {{
                                        __(
                                            "Turned towards us",
                                        )
                                    }}
                                </h3>
                                @if ($planets["mars"]["facing"])
                                    <ul
                                        class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-200"
                                    >
                                        @foreach ($planets["mars"]["facing"] as $item)
                                            <li>
                                                <a
                                                    href="{{ route("mars-venus.show", $query(["feature" => $item["key"], "time" => $time])) }}#feature"
                                                    class="hover:underline {{ $item["on_meridian"] ? "font-semibold text-amber-300" : "" }}"
                                                    >{{ $item["name"] }}</a
                                                >
                                                @if ($item["on_meridian"])
                                                    <span
                                                        class="text-xs text-gray-400"
                                                        >({{
                                                            __(
                                                                "on the central meridian",
                                                            )
                                                        }})</span
                                                    >
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($disk["diameter"] < 6)
                                    <p class="mt-2 text-sm text-gray-400">
                                        {{
                                            __(
                                                "The disk is small now: only the polar caps and Syrtis Major show in a large telescope. Mars is best observed in the months around its opposition.",
                                            )
                                        }}
                                    </p>
                                @endif
                            @else
                                <p class="mt-4 text-sm text-gray-400">
                                    {{
                                        __(
                                            "A yellow or blue filter shows the faint shadings of the clouds better and dims the glare. Around dichotomy the observed phase differs from the theory: the Schröter effect makes half the disk lit days early in the evening sky and late in the morning sky.",
                                        )
                                    }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($planet === "mars" && $hours)
                        <div class="mt-4 overflow-x-auto">
                            <table
                                class="w-full text-left text-sm text-gray-200"
                            >
                                <thead class="text-xs text-gray-400 uppercase">
                                    <tr>
                                        <th class="py-2 pr-3">
                                            {{ __("Time") }}
                                        </th>
                                        <th class="py-2 pr-3">
                                            {{
                                                __(
                                                    "Central meridian",
                                                )
                                            }}
                                        </th>
                                        <th class="py-2 pr-3">
                                            {{
                                                __(
                                                    "On the central meridian",
                                                )
                                            }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    @foreach ($hours as $hour)
                                        <tr>
                                            <td
                                                class="py-1 pr-3 whitespace-nowrap"
                                            >
                                                <a
                                                    href="{{ route("mars-venus.show", $query(["time" => $clock($hour["at"])])) }}"
                                                    class="text-blue-400 hover:underline"
                                                    title="{{ __("Show Mars at this moment") }}"
                                                    >{{
                                                        $clock(
                                                            $hour["at"],
                                                        )
                                                    }}</a
                                                >
                                            </td>
                                            <td class="py-1 pr-3">
                                                {{ round($hour["cm"]) }}°
                                            </td>
                                            <td class="py-1 pr-3">
                                                {{
                                                    $hour["features"]
                                                        ? implode(", ", $hour["features"])
                                                        : "—"
                                                }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($planet === "mars")
                        <div id="feature" class="mt-6">
                            <h3 class="text-base font-semibold text-white">
                                {{
                                    __(
                                        "When can I see…?",
                                    )
                                }}
                            </h3>
                            <form
                                method="GET"
                                action="{{ route("mars-venus.show") }}#feature"
                                class="mt-2 flex flex-wrap items-end gap-3"
                            >
                                @foreach ($query(["feature" => null]) as $name => $value)
                                    <input
                                        type="hidden"
                                        name="{{ $name }}"
                                        value="{{ $value }}"
                                    />
                                @endforeach

                                <label class="block text-sm">
                                    <span class="mb-1 block text-gray-400">{{
                                        __(
                                            "Feature on Mars",
                                        )
                                    }}</span>
                                    <select name="feature" class="{{ $input }}">
                                        <option value="">—</option>
                                        @foreach (collect(\App\Services\Planets\MarsFeatures::FEATURES)->sortBy(
                                                "name"
                                            )
                                            as $key => $item)
                                            <option
                                                value="{{ $key }}"
                                                @selected ($feature === $key)
                                            >
                                                {{ $item["name"] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
                                >
                                    {{ __("Show") }}
                                </button>
                            </form>
                            @if ($feature)
                                @if ($transits)
                                    <p class="mt-2 text-sm text-gray-400">
                                        {{
                                            __(
                                                ":feature crosses the central meridian, where it is seen best, while Mars can be observed:",
                                                [
                                                    "feature" =>
                                                        \App\Services\Planets\MarsFeatures::FEATURES[$feature]["name"],
                                                ],
                                            )
                                        }}
                                    </p>
                                    <ul class="mt-1 text-sm text-gray-200">
                                        @foreach ($transits as $transit)
                                            <li>
                                                <a
                                                    href="{{ route("mars-venus.show", $query(["night" => $transit["night"], "time" => $clock($transit["at"])])) }}"
                                                    class="text-blue-400 hover:underline"
                                                    >{{
                                                        $moment(
                                                            $transit["at"],
                                                        )
                                                    }}</a
                                                >
                                                <span class="text-gray-400"
                                                    >({{
                                                        __("altitude :altitude°", [
                                                            "altitude" => $transit["altitude"],
                                                        ])
                                                    }})</span
                                                >
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-2 text-sm text-gray-400">
                                        {{
                                            __(
                                                ":feature does not cross the central meridian while Mars can be observed in the coming :nights nights.",
                                                [
                                                    "feature" =>
                                                        \App\Services\Planets\MarsFeatures::FEATURES[$feature]["name"],
                                                    "nights" => \App\Services\Planets\MarsVenus::FEATURE_NIGHTS,
                                                ],
                                            )
                                        }}
                                    </p>
                                @endif
                            @endif
                        </div>
                    @endif

                    @if ($events[$planet])
                        <h3 class="mt-6 text-base font-semibold text-white">
                            {{ __("Coming") }}
                        </h3>
                        <ul class="mt-1 text-sm text-gray-200">
                            @foreach ($events[$planet] as $event)
                                <li>
                                    <span class="text-gray-400">{{
                                        $moment(
                                            $event["at"],
                                            "LL",
                                        )
                                    }}</span>
                                    {{
                                        $eventNames[
                                            $event["kind"]
                                        ]
                                    }}
                                    @if (isset($event["elongation"]))
                                        ({{
                                            $event[
                                                "elongation"
                                            ]
                                        }}° {{
                                            __(
                                                "from the Sun",
                                            )
                                        }})
                                    @endif

                                    @if (isset($event["diameter"]))
                                        <span class="text-gray-400"
                                            >{{
                                                number_format(
                                                    $event["diameter"],
                                                    1,
                                                )
                                            }}″</span
                                        >
                                    @endif

                                    @if (isset($event["magnitude"]))
                                        <span class="text-gray-400"
                                            >{{
                                                __(
                                                    "magnitude",
                                                )
                                            }} {{
                                                number_format(
                                                    $event["magnitude"],
                                                    1,
                                                )
                                            }}</span
                                        >
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

            <div class="mt-8">
                <h2 class="text-lg font-semibold text-white">
                    {{
                        __(
                            "The coming weeks",
                        )
                    }}
                </h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-200">
                        <thead class="text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="py-2 pr-3" rowspan="2">
                                    {{ __("Date") }}
                                </th>
                                <th class="py-1 pr-3" colspan="4">
                                    {{ __("Mars") }}
                                </th>
                                <th class="py-1 pr-3" colspan="4">
                                    {{ __("Venus") }}
                                </th>
                            </tr>
                            <tr>
                                <th class="py-1 pr-3 text-right">
                                    {{ __("Diameter") }}
                                </th>
                                <th class="py-1 pr-3 text-right">
                                    {{ __("Mag.") }}
                                </th>
                                <th class="py-1 pr-3 text-right">
                                    {{ __("Lit") }}
                                </th>
                                <th class="py-1 pr-3 text-right">
                                    {{ __("Elong.") }}
                                </th>
                                <th class="py-1 pr-3 text-right">
                                    {{ __("Diameter") }}
                                </th>
                                <th class="py-1 pr-3 text-right">
                                    {{ __("Mag.") }}
                                </th>
                                <th class="py-1 pr-3 text-right">
                                    {{ __("Lit") }}
                                </th>
                                <th class="py-1 pr-3 text-right">
                                    {{ __("Elong.") }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($weeks as $week)
                                <tr>
                                    <td class="py-1 pr-3 whitespace-nowrap">
                                        <a
                                            href="{{ route("mars-venus.show", $query(["night" => $week["date"]])) }}"
                                            class="text-blue-400 hover:underline"
                                            >{{
                                                $date(
                                                    $week["date"],
                                                    "D MMM YYYY",
                                                )
                                            }}</a
                                        >
                                    </td>
                                    @foreach (["mars", "venus"] as $planet)
                                        @php
                                            $row = $week[$planet];
                                        @endphp

                                        <td class="py-1 pr-3 text-right">
                                            {{
                                                number_format(
                                                    $row["diameter"],
                                                    1,
                                                )
                                            }}″
                                        </td>
                                        <td class="py-1 pr-3 text-right">
                                            {{
                                                $row["magnitude"] === null
                                                    ? "—"
                                                    : number_format($row["magnitude"], 1)
                                            }}
                                        </td>
                                        <td class="py-1 pr-3 text-right">
                                            {{
                                                round(
                                                    $row["illuminated"] * 100,
                                                )
                                            }}%
                                        </td>
                                        <td
                                            class="py-1 pr-3 text-right whitespace-nowrap"
                                        >
                                            {{
                                                round(
                                                    abs($row["elongation"]),
                                                )
                                            }}° {{
                                                $row["elongation"] >= 0
                                                    ? __("E")
                                                    : __("W")
                                            }}
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
                            "At 0h UT. Elongation E: east of the Sun, in the evening sky; W: west of the Sun, in the morning sky. Mars is observable when it is at least :altitude° high with the Sun :sun° below the horizon, Venus from sunset to sunrise. The physical ephemeris of Mars after Meeus, Astronomical Algorithms, chapter 42; the central meridian in west longitude, as on the maps of the ALPO and the BAA.",
                            [
                                "altitude" => (int) \App\Services\Planets\MarsVenus::MIN_ALTITUDE,
                                "sun" => (int) abs(
                                    \App\Services\Planets\MarsVenus::SUN_ALTITUDE["mars"],
                                ),
                            ],
                        )
                    }}
                </p>
            </div>
        @endif
    </div>
</x-app-layout>
