@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item (#960): the Moon covers a planet or a bright star, with the moments seen from the reader's location --}}
@php
    $isStar = ($data["target"] ?? "star") === "star";
    $name = EventTexts::occultedTarget($data);
    $slug = $isStar ? $data["star"]["slug"] ?? null : $data["target"] ?? null;
    $target = $slug
        ? '<a href="' .
            e(route("object.show", $slug)) .
            '" class="text-blue-400 hover:underline">' .
            e($name) .
            "</a>"
        : e($name);
    $waxing = ($data["elongation"] ?? 0) > 0;
    $magnitude = $isStar
        ? $data["star"]["mag"] ?? null
        : $data["magnitudes"][$data["target"] ?? ""] ?? null;
@endphp
<p>
    {!!
        __(
            "Seen from parts of the Earth the Moon passes in front of :target. As seen from the centre of the Earth they are closest at :time UT, :distance° apart.",
            [
                "target" => $target,
                "time" => $item->event_at->format("H:i"),
                "distance" => EventTexts::number($data["separation"] ?? 0, 2),
            ],
        )
    !!}
    @if ($isStar && ($data["star"]["designation"] ?? "") !== $name)
        {{
            __(
                ":star (:designation) has magnitude :magnitude.",
                [
                    "star" => $name,
                    "designation" => $data["star"]["designation"],
                    "magnitude" => EventTexts::number($magnitude ?? 0, 1),
                ],
            )
        }}
    @elseif ($magnitude !== null)
        {{
            __(":body has magnitude :magnitude.", [
                "body" => $name,
                "magnitude" => EventTexts::number($magnitude, 1),
            ])
        }}
    @endif
    {{
        __(
            "The Moon is :illumination% illuminated.",
            ["illumination" => (int) round(100 * ($data["moon_illumination"] ?? 0))],
        )
    }}
    @if ($waxing)
        {{
            __(
                "The Moon is waxing: :target disappears at the dark limb and reappears at the bright limb.",
                ["target" => $name],
            )
        }}
    @else
        {{
            __(
                "The Moon is waning: :target disappears at the bright limb and reappears at the dark limb.",
                ["target" => $name],
            )
        }}
    @endif
</p>

@if ($location && $local)
    <h3 class="mt-3 font-semibold text-white">
        {{
            __("Seen from :location", [
                "location" => $location->name,
            ])
        }}
    </h3>
    @php
        $occultation = $local["occultation"] ?? null;
        $best = $local["best"] ?? null;
    @endphp
    @if ($occultation)
        <ul class="list-inside list-disc">
            @foreach (["disappears", "reappears"] as $moment)
                @php $contact = $occultation[$moment] ?? null; @endphp
                @if ($contact)
                    <li>
                        @if ($moment === "disappears")
                            {{
                                $contact["limb"] === "dark"
                                    ? __("Disappears at the dark limb: :time", [
                                        "time" => EventTexts::localTime($contact["at"], $location),
                                    ])
                                    : __("Disappears at the bright limb: :time", [
                                        "time" => EventTexts::localTime($contact["at"], $location),
                                    ])
                            }}
                        @else
                            {{
                                $contact["limb"] === "dark"
                                    ? __("Reappears at the dark limb: :time", [
                                        "time" => EventTexts::localTime($contact["at"], $location),
                                    ])
                                    : __("Reappears at the bright limb: :time", [
                                        "time" => EventTexts::localTime($contact["at"], $location),
                                    ])
                            }}
                        @endif
                        @php
                            $note =
                                $contact["moon_altitude"] <= 0
                                    ? __("The Moon is below the horizon then.")
                                    : ($contact["sun_altitude"] > -6
                                        ? __(
                                            "The Moon is :altitude° above the horizon, but the Sun is up or the sky still bright.",
                                            [
                                                "altitude" => EventTexts::number(
                                                    $contact["moon_altitude"],
                                                    0,
                                                ),
                                            ],
                                        )
                                        : __("The Moon is :altitude° above the horizon.", [
                                            "altitude" => EventTexts::number($contact["moon_altitude"], 0),
                                        ]));
                        @endphp
                        ({{ $note }})
                    </li>
                @endif
            @endforeach
        </ul>
        @if ($occultation["visible"])
            <p>
                {{
                    $isStar
                        ? __("Start watching a few minutes early: a star disappears in an instant.")
                        : __(
                            "Start watching a few minutes early: a planet takes up to a minute or two to disappear behind the Moon.",
                        )
                }}
            </p>
        @endif
    @else
        <p>
            {{
                __(
                    "From :location the Moon does not cover :target.",
                    ["location" => $location->name, "target" => $name],
                )
            }}
            @if ($best)
                {{
                    __(
                        "The best moment is :time, when they are :altitude° above the horizon and :distance° apart.",
                        [
                            "time" => EventTexts::localTime($best["at"], $location),
                            "altitude" => EventTexts::number($best["altitude"], 0),
                            "distance" => EventTexts::number($best["separation"], 2),
                        ],
                    )
                }}
            @else
                {{
                    __(
                        "From :location they are not high enough in a dark sky.",
                        ["location" => $location->name],
                    )
                }}
            @endif
        </p>
    @endif
@endif
