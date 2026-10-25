@props (["best"])

{{-- Tonight's best (App\Services\TonightsBest): the objects worth observing in the coming night --}}
@php
    $night = \Carbon\Carbon::parse($best["night"])->isoFormat("LL");
    $moon = $best["moon"]
        ? [
            "fraction" => (int) round($best["moon"]["fraction"] * 100),
            "from" => $best["moon"]["from"],
            "until" => $best["moon"]["until"],
        ]
        : null;
@endphp

<x-home-section :title="__('Tonight\'s best')" {{ $attributes }}>
    <div class="space-y-1 px-2 text-sm text-gray-400">
        {{-- The night at a glance; the full explanation is folded away --}}
        <p>
            {{
                __("Night of :date", [
                    "date" => $night,
                ])
            }}
            @if ($best["mode"] === "personal")
                &middot; {{
                    $best[
                        "instrument"
                    ]
                }} &middot; {{
                    $best[
                        "location"
                    ]
                }}
            @endif
            @if ($best["dark"])
                &middot;
                {{
                    $best["dark"]["twilight"] ===
                    "astronomical"
                        ? __("Dark :from–:until", $best["dark"])
                        : __(
                            "Nautical twilight :from–:until",
                            $best["dark"],
                        )
                }}
                &middot;
                {{
                    $moon
                        ? __("Moon :fraction%, up :from–:until", $moon)
                        : __("No Moon")
                }}
            @endif
        </p>

        @if ($best["mode"] !== "personal")
            <p>
                @guest
                    {!!
                        __(
                            "To get a list for your own telescope and location, :linkregister:close_link and set your standard instrument and location.",
                            [
                                "link" =>
                                    '<a href="' .
                                    route("register") .
                                    '" class="text-blue-400 hover:underline">',
                                "close_link" => "</a>",
                            ],
                        )
                    !!}
                @else
                    {!!
                        __(
                            "Set your standard :instrument_linkinstrument:close_link and :location_linklocation:close_link to get a list for your own telescope.",
                            [
                                "instrument_link" =>
                                    '<a href="' .
                                    route("instrument.index") .
                                    '" class="text-blue-400 hover:underline">',
                                "location_link" =>
                                    '<a href="' .
                                    route("location.index") .
                                    '" class="text-blue-400 hover:underline">',
                                "close_link" => "</a>",
                            ],
                        )
                    !!}
                @endguest
            </p>
        @endif

        @if ($best["pending"])
            <p class="text-yellow-400">
                {{
                    __(
                        "The visibility of more objects is being calculated for your equipment; the list will be completed later.",
                    )
                }}
            </p>
        @endif

        <details>
            <summary
                class="cursor-pointer text-xs text-gray-500 select-none hover:text-gray-300"
            >
                {{
                    __(
                        "How is this list made?",
                    )
                }}
            </summary>
            <div class="mt-1 space-y-1 text-xs">
                <p>
                    @if ($best["mode"] === "personal")
                        {{
                            __(
                                "Objects for your :instrument from :location, well placed during the dark hours of the night of :date.",
                                [
                                    "instrument" => $best["instrument"],
                                    "location" => $best["location"],
                                    "date" => $night,
                                ],
                            )
                        }}
                    @else
                        {{
                            __(
                                "The most observed objects that are well placed during the dark hours of the night of :date, for a latitude of :latitude°.",
                                ["date" => $night, "latitude" => $best["latitude"]],
                            )
                        }}
                    @endif
                </p>
                @if ($best["dark"])
                    <p>
                        @if ($best["dark"]["twilight"] === "astronomical")
                            {{
                                __(
                                    "Dark from :from to :until.",
                                    $best["dark"],
                                )
                            }}
                        @else
                            {{
                                __(
                                    "It does not get completely dark tonight: from :from to :until the sky stays in nautical twilight.",
                                    $best["dark"],
                                )
                            }}
                        @endif
                        @if ($moon)
                            {{
                                __(
                                    "The Moon (:fraction% illuminated) is above the horizon from :from to :until; the times and the visibility take the brighter sky into account.",
                                    $moon,
                                )
                            }}
                        @else
                            {{
                                __(
                                    "The Moon stays below the horizon during the dark hours.",
                                )
                            }}
                        @endif
                    </p>
                @endif
            </div>
        </details>
    </div>

    @if (!$best["dark"])
        <p class="mt-4 px-2 text-gray-300">
            {{
                __(
                    "It does not get dark tonight at this location.",
                )
            }}
        </p>
    @elseif (empty($best["objects"]))
        <p class="mt-4 px-2 text-gray-300">
            {{
                __(
                    "No objects found for tonight.",
                )
            }}
        </p>
    @else
        <x-tonights-best-table :best="$best" />

        @auth
            @can ("create", \App\Models\ObservingList::class)
                <div class="mt-4 flex flex-wrap items-center gap-3 px-2">
                    <form
                        method="POST"
                        action="{{ route("observing-list.store-tonight") }}"
                    >
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
                        >
                            {{
                                __(
                                    "Make an observing list",
                                )
                            }}
                        </button>
                    </form>
                    <a
                        href="{{ route("observing-list.night") }}"
                        class="text-sm text-blue-400 hover:underline"
                        >{{
                            __(
                                "Make a list for a night of your own choice",
                            )
                        }}</a
                    >
                </div>
            @endcan
        @endauth
    @endif
</x-home-section>
