@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item (#960): a solar eclipse, with its local circumstances --}}
<p>
    @switch ($data["type"] ?? "partial")
        @case ("total")
            {{
                __(
                    "The Moon covers the Sun completely along a narrow path on the Earth; on both sides of it the eclipse is partial.",
                )
            }}
            @break
        @case ("annular")
            {{
                __(
                    "The Moon is too far from the Earth to cover the Sun completely: along a narrow path a ring of sunlight stays around it; on both sides of it the eclipse is partial.",
                )
            }}
            @break
        @case ("hybrid")
            {{
                __(
                    "Along a narrow path the eclipse is total in some places and annular in others; on both sides of it the eclipse is partial.",
                )
            }}
            @break
        @default
            {{
                __(
                    "Only the penumbra of the Moon reaches the Earth: the eclipse is partial everywhere, with a magnitude of :magnitude at the greatest eclipse.",
                    ["magnitude" => EventTexts::number($data["magnitude"] ?? 0, 3)],
                )
            }}
    @endswitch
    {{
        __(
            "Greatest eclipse at :time UT, at latitude :latitude° and longitude :longitude°.",
            [
                "time" => $item->event_at->format("H:i"),
                "latitude" => EventTexts::number($data["latitude"] ?? 0, 1),
                "longitude" => EventTexts::number($data["longitude"] ?? 0, 1),
            ],
        )
    }}
</p>

@if ($location && $local)
    @php $eclipse = $local["eclipse"] ?? null; @endphp
    <h3 class="mt-3 font-semibold text-white">
        {{
            __("Seen from :location", [
                "location" => $location->name,
            ])
        }}
    </h3>
    @if ($eclipse === null)
        <p>{{
            __(
                "The eclipse is not visible from :location.",
                ["location" => $location->name],
            )
        }}</p>
    @elseif (!$eclipse["visible"])
        <p>{{
            __(
                "At :location the eclipse takes place while the Sun is below the horizon.",
                ["location" => $location->name],
            )
        }}</p>
    @else
        <ul class="list-inside list-disc">
            <li>
                {{
                    __("Begins: :time", [
                        "time" => EventTexts::localTime($eclipse["begin"], $location),
                    ])
                }}
            </li>
            <li>
                {{
                    __(
                        "Greatest eclipse: :time, with the Sun :altitude° above the horizon",
                        [
                            "time" => EventTexts::localTime($eclipse["max"], $location),
                            "altitude" => EventTexts::number($eclipse["altitude"], 0),
                        ],
                    )
                }}
                @if (!$eclipse["visible_max"])
                    ({{
                        __(
                            "below the horizon",
                        )
                    }})
                @endif
            </li>
            <li>
                {{
                    __("Ends: :time", [
                        "time" => EventTexts::localTime($eclipse["end"], $location),
                    ])
                }}
            </li>
            <li>
                {{
                    __(
                        "Magnitude :magnitude: :obscuration% of the Sun's surface is covered.",
                        [
                            "magnitude" => EventTexts::number($eclipse["magnitude"], 3),
                            "obscuration" => (int) round(100 * $eclipse["obscuration"]),
                        ],
                    )
                }}
            </li>
        </ul>
        @if ($eclipse["type"] === "total")
            <p class="font-semibold text-green-400">{{
                __(
                    "The eclipse is total at :location!",
                    ["location" => $location->name],
                )
            }}</p>
        @elseif ($eclipse["type"] === "annular")
            <p class="font-semibold text-green-400">{{
                __(
                    "The eclipse is annular at :location!",
                    ["location" => $location->name],
                )
            }}</p>
        @endif
    @endif
@endif

<p class="mt-3 font-semibold text-yellow-400">
    {{
        __(
            "Never look at the Sun without a certified solar filter, not even when it is partly covered.",
        )
    }}
</p>
