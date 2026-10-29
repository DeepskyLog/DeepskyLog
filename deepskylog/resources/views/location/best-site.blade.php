@use ("App\Services\Weather\SiteComparison")

<x-app-layout>
    @php
        $query = fn(array $change) => route(
            "location.best-site",
            array_filter(
                ["mode" => $mode, "radius" => $radius, "night" => $night, ...$change],
                fn($value) => $value !== null,
            ),
        );
        $options = [
            "mode" => [
                SiteComparison::MODE_DEEPSKY => __("Deep-sky objects"),
                SiteComparison::MODE_PLANETS => __("Planets and double stars"),
            ],
            "radius" =>
                [null => __("All sites")] +
                collect(SiteComparison::RADII)
                    ->mapWithKeys(
                        fn($km) => [$km => __("Within :km km", ["km" => $km])],
                    )
                    ->all(),
        ];
        $date = fn(string $night) => \Carbon\Carbon::parse($night)
            ->locale(app()->getLocale())
            ->isoFormat("ddd D MMM");
        $skySource = [
            "measured" => __(
                "Median SQM of your observations here in the last :years years",
                ["years" => SiteComparison::RECENT_YEARS],
            ),
            "location" => __("The sky of the location"),
            "unknown" => __("No sky known: counted as SQM :sqm", [
                "sqm" => SiteComparison::DEFAULT_SQM,
            ]),
        ];
        $best = $ranked[0] ?? null;
    @endphp

    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h2 class="text-xl leading-tight font-semibold text-gray-200">
                {{
                    __(
                        "Which of my sites tonight?",
                    )
                }}
            </h2>

            <div class="flex flex-wrap gap-2">
                @foreach ($options as $parameter => $choices)
                    <nav
                        class="inline-flex flex-wrap rounded-lg border border-gray-700 bg-gray-950 p-1"
                        aria-label="{{ $parameter === "mode" ? __("Objects") : __("Distance") }}"
                    >
                        @foreach ($choices as $value => $label)
                            @php
                                $value = $value === "" ? null : $value;
                                $current = $parameter === "mode" ? $mode : $radius;
                                $isCurrent = (string) $value === (string) $current;
                            @endphp

                            <a
                                href="{{ $query([$parameter => $value]) }}"
                                class="{{ $isCurrent ? "bg-sky-500 text-gray-950" : "text-gray-300 hover:bg-gray-800 hover:text-white" }} rounded-md px-3 py-2 text-sm font-medium transition"
                                @if ($isCurrent) aria-current="page" @endif
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </nav>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div
        class="mx-auto max-w-7xl space-y-6 bg-gray-900 px-4 py-6 sm:px-6 lg:px-8"
    >
        <p class="text-sm text-gray-400">
            @if ($mode === SiteComparison::MODE_PLANETS)
                {{
                    __(
                        "Your active locations compared for the coming nights. For planets and double stars the score (0–100) is the clear hours of the night, up to :hours, times the seeing; the Moon and the sky brightness do not matter.",
                        ["hours" => SiteComparison::FULL_HOURS],
                    )
                }}
            @else
                {{
                    __(
                        "Your active locations compared for the coming nights. For deep-sky objects the score (0–100) is the clear hours of the night, up to :hours (an hour with a bright Moon counts for a third), times the darkness of the sky of the site, from SQM :poor to :dark.",
                        [
                            "hours" => SiteComparison::FULL_HOURS,
                            "poor" => SiteComparison::SQM_POOR,
                            "dark" => SiteComparison::SQM_DARK,
                        ],
                    )
                }}
            @endif
        </p>

        @if (!$enabled)
            <p class="text-gray-400">{{
                __(
                    "The weather forecast is turned off.",
                )
            }}</p>
        @elseif ($sites === [])
            <p class="text-gray-400">
                {{
                    __(
                        "You have no active locations with coordinates here.",
                    )
                }}
                <a
                    href="{{ route("location.create") }}"
                    class="text-blue-400 hover:underline"
                    >{{
                        __(
                            "Add Location",
                        )
                    }}</a
                >
            </p>
        @else
            @if ($pending > 0)
                <p class="rounded border border-gray-700 bg-gray-800 p-3 text-sm text-gray-300">
                    {{
                        trans_choice(
                            "The forecast of :count site is being fetched.|The forecast of :count sites is being fetched.",
                            $pending,
                            ["count" => $pending],
                        )
                    }}
                    <a
                        href="{{ $query([]) }}"
                        class="text-blue-400 hover:underline"
                        >{{ __("Reload") }}</a
                    >
                </p>
                @if (!request()->boolean("reloaded"))
                    <script>
                        setTimeout(() => {
                            const url = new URL(window.location.href);
                            url.searchParams.set("reloaded", "1");
                            window.location.replace(url);
                        }, 5000);
                    </script>
                @endif
            @endif

            {{-- All sites and nights at a glance --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-400">
                            <th class="py-2 pr-4 font-medium">
                                {{ __("Location") }}
                            </th>
                            <th class="py-2 pr-4 font-medium">
                                {{ __("Distance") }}
                            </th>
                            <th class="py-2 pr-4 font-medium">
                                {{ __("Sky") }}
                            </th>
                            @foreach ($nights as $n)
                                <th class="px-1 py-2 text-center font-medium">
                                    <a
                                        href="{{ $query(["night" => $n]) }}"
                                        class="{{ $n === $night ? "text-white underline" : "text-blue-400 hover:underline" }}"
                                        >{{ $date($n) }}</a
                                    >
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sites as $site)
                            <tr class="border-t border-gray-800">
                                <td class="py-2 pr-4">
                                    <a
                                        href="{{ route("location.show", ["user" => auth()->user()->slug, "location" => $site["location"]->slug]) }}"
                                        class="text-blue-400 hover:underline"
                                        >{{
                                            html_entity_decode(
                                                $site["location"]->name,
                                                ENT_QUOTES | ENT_HTML5,
                                                "UTF-8",
                                            )
                                        }}</a
                                    >
                                    @if ($home && $site["location"]->id === $home->id)
                                        <span class="ml-1 text-xs text-gray-500"
                                            >({{ __("standard") }})</span
                                        >
                                    @endif
                                </td>
                                <td
                                    class="py-2 pr-4 whitespace-nowrap text-gray-300"
                                >
                                    {{
                                        $site["distance"] === null
                                            ? "–"
                                            : __(":km km", [
                                                "km" => number_format($site["distance"], 0),
                                            ])
                                    }}
                                </td>
                                <td
                                    class="py-2 pr-4 whitespace-nowrap text-gray-300"
                                    title="{{ $skySource[$site["sky"]["source"]] }}"
                                >
                                    {{
                                        $site["sky"]["source"] === "unknown"
                                            ? "?"
                                            : ($site["sky"]["source"] === "location" ? "" : "≈ ") .
                                                number_format($site["sky"]["sqm"], 2)
                                    }}
                                </td>
                                @foreach ($nights as $n)
                                    @php
                                        $score = $site["nights"][$n]["score"] ?? null;
                                    @endphp
                                    <td class="px-1 py-2 text-center">
                                        <a
                                            href="{{ $query(["night" => $n]) }}"
                                            class="{{ SiteComparison::scoreClass($score) }} {{ $n === $night ? "ring-2 ring-blue-400" : "" }} inline-block w-10 rounded px-2 py-1 font-semibold"
                                            >{{ $score ?? "–" }}</a
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($night)
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-white">
                        {{
                            __("The night of :date", [
                                "date" => $date($night),
                            ])
                        }}
                    </h3>

                    @if ($best && $best["nights"][$night]["score"] > 0)
                        <p class="text-gray-300">
                            {!!
                                __("Best site: :location", [
                                    "location" =>
                                        '<span class="font-semibold text-white">' .
                                        e(
                                            html_entity_decode(
                                                $best["location"]->name,
                                                ENT_QUOTES | ENT_HTML5,
                                                "UTF-8",
                                            ),
                                        ) .
                                        "</span>",
                                ])
                            !!}
                        </p>
                    @elseif ($ranked !== [])
                        <p class="text-gray-400">{{
                            __(
                                "None of your sites is forecast to be clear this night.",
                            )
                        }}</p>
                    @endif

                    <div class="space-y-4">
                        @foreach ($ranked as $site)
                            @php
                                $forecast = $site["nights"][$night]["forecast"];
                            @endphp
                            <div
                                class="rounded border border-gray-800 p-3 text-sm text-gray-400"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="{{ SiteComparison::scoreClass($site["nights"][$night]["score"]) }} rounded px-2 py-0.5 font-semibold"
                                        >{{
                                            $site["nights"][$night][
                                                "score"
                                            ]
                                        }}</span
                                    >
                                    <span class="font-semibold text-white">{{
                                        html_entity_decode(
                                            $site["location"]->name,
                                            ENT_QUOTES | ENT_HTML5,
                                            "UTF-8",
                                        )
                                    }}</span>
                                    <x-forecast-verdict :night="$forecast" />
                                    @if ($forecast["seeing"] ?? null)
                                        <x-forecast-seeing
                                            :seeing="$forecast['seeing']"
                                        />
                                    @endif
                                    @if ($home &&
                                        $site["location"]->id === $home->id &&
                                        in_array(
                                            $forecast["verdict"],
                                            ["clear_dark", "clear_twilight", "clear_moon"],
                                            true
                                        ))
                                        <a
                                            href="{{ route("observing-list.night", ["night" => $night]) }}"
                                            class="text-xs text-blue-400 hover:underline"
                                            >{{
                                                __(
                                                    "Make a list for this night",
                                                )
                                            }}</a
                                        >
                                    @endif
                                </div>
                                <p class="mt-1">
                                    @if ($forecast["dark"])
                                        {{
                                            $forecast["dark"]["twilight"] ===
                                            "astronomical"
                                                ? __("Dark :from–:until", $forecast["dark"])
                                                : (isset($forecast["dark"]["dark_from"])
                                                    ? __(
                                                        "Nautical twilight :from–:until, completely dark :dark_from–:dark_until",
                                                        $forecast["dark"],
                                                    )
                                                    : __(
                                                        "Nautical twilight :from–:until",
                                                        $forecast["dark"],
                                                    ))
                                        }}
                                        &middot;
                                        {{
                                            $forecast["moon"]
                                                ? __("Moon :fraction%, up :from–:until", $forecast["moon"])
                                                : __("No Moon")
                                        }}
                                    @endif
                                    @if ($forecast["cloud"] !== null)
                                        &middot; {{
                                            __("Clouds :percent%", [
                                                "percent" => $forecast["cloud"],
                                            ])
                                        }}
                                        @php
                                            $clearHours = [
                                                "hours" => number_format($forecast["clear_minutes"] / 60, 1),
                                                "dark" => number_format($forecast["clear_dark_minutes"] / 60, 1),
                                            ];
                                        @endphp
                                        &middot;
                                        {{
                                            ($forecast["dark"]["twilight"] ??
                                                "astronomical") ===
                                            "astronomical"
                                                ? __("Clear :hours h, without bright Moon :dark h", $clearHours)
                                                : __(
                                                    "Clear :hours h, completely dark without bright Moon :dark h",
                                                    $clearHours,
                                                )
                                        }}
                                    @endif
                                    @if ($forecast["dew"])
                                        &middot; {{
                                            __(
                                                "Risk of dew",
                                            )
                                        }}
                                    @endif
                                </p>
                                @if ($forecast["hours"] !== [])
                                    <x-forecast-hours
                                        :hours="$forecast['hours']"
                                        class="mt-1"
                                    />
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <x-forecast-credit />
        @endif
    </div>
</x-app-layout>
