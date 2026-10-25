@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item (#960): two planets, the Moon and a planet, a planet and a Messier object, or the Moon and a bright Messier object close together --}}
@php
    $bodies = $data["bodies"] ?? [];
    $isMoon = in_array(
        $item->kind,
        ["moon_conjunction", "moon_deepsky_conjunction"],
        true,
    );
    $hasObject = in_array(
        $item->kind,
        ["deepsky_conjunction", "moon_deepsky_conjunction"],
        true,
    );
    $link = fn(string $body) => $body === "moon"
        ? e(EventTexts::body($body))
        : '<a href="' .
            e(route("object.show", $body)) .
            '" class="text-blue-400 hover:underline">' .
            e(EventTexts::body($body)) .
            "</a>";
    $first = $link($bodies[0] ?? "");
    $second = $hasObject
        ? '<a href="' .
            e(route("object.show", $data["object"]["slug"])) .
            '" class="text-blue-400 hover:underline">' .
            e($data["object"]["name"]) .
            "</a>"
        : $link($bodies[1] ?? "");
    $planet =
        $item->kind === "moon_conjunction" ? $bodies[1] ?? "" : $bodies[0] ?? "";
    // What the Moon passes in front of: the planet or the Messier object
    $target = $hasObject
        ? $data["object"]["name"] ?? ""
        : EventTexts::body($planet);
@endphp
<p>
    @if ($isMoon)
        {!!
            __(
                "The Moon and :planet are only :distance° apart at :time UT, as seen from the centre of the Earth.",
                [
                    "planet" => $second,
                    "distance" => EventTexts::number($data["separation"] ?? 0, 2),
                    "time" => $item->event_at->format("H:i"),
                ],
            )
        !!}
    @else
        {!!
            __(
                ":first and :second are only :distance° apart at :time UT, as seen from the centre of the Earth.",
                [
                    "first" => $first,
                    "second" => $second,
                    "distance" => EventTexts::number($data["separation"] ?? 0, 2),
                    "time" => $item->event_at->format("H:i"),
                ],
            )
        !!}
    @endif
    @if (abs($data["elongation"] ?? 0) > 90)
        {{
            __(
                "They are :elongation° from the Sun and visible for most of the night.",
                ["elongation" => EventTexts::number(abs($data["elongation"]), 0)],
            )
        }}
    @elseif (($data["elongation"] ?? 0) > 0)
        {{
            __(
                "They are :elongation° east of the Sun, in the evening sky.",
                ["elongation" => EventTexts::number(abs($data["elongation"]), 0)],
            )
        }}
    @else
        {{
            __(
                "They are :elongation° west of the Sun, in the morning sky.",
                ["elongation" => EventTexts::number(abs($data["elongation"] ?? 0), 0)],
            )
        }}
    @endif
    @foreach ($data["magnitudes"] ?? [] as $body => $magnitude)
        @if ($magnitude !== null)
            {{
                __(":body has magnitude :magnitude.", [
                    "body" => EventTexts::body($body),
                    "magnitude" => EventTexts::number($magnitude, 1),
                ])
            }}
        @endif
    @endforeach
    @if ($isMoon)
        {{
            __(
                "The Moon is :illumination% illuminated.",
                ["illumination" => (int) round(100 * ($data["moon_illumination"] ?? 0))],
            )
        }}
        @if (!empty($data["occultation"]))
            {{
                __(
                    "Seen from parts of the Earth the Moon covers :planet.",
                    ["planet" => $target],
                )
            }}
        @endif
    @endif
    @if ($hasObject)
        @if (!empty($data["object"]["mag"]))
            {{
                __(":object has magnitude :magnitude.", [
                    "object" => $data["object"]["name"],
                    "magnitude" => EventTexts::number($data["object"]["mag"], 1),
                ])
            }}
        @endif
        {{
            __(
                "A fine sight in binoculars or in a telescope at low magnification.",
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
        $best = $local["best"] ?? null;
        $occultation = $local["occultation"] ?? null;
    @endphp
    @if ($occultation)
        <p class="font-semibold text-green-400">
            {{
                __(
                    "The Moon covers :planet from :begin to :end.",
                    [
                        "planet" => $target,
                        "begin" => EventTexts::localTime($occultation["begin"], $location),
                        "end" => EventTexts::localTime($occultation["end"], $location),
                    ],
                )
            }}
            @if (!$occultation["visible"])
                {{
                    __(
                        "The Moon is below the horizon then.",
                    )
                }}
            @elseif ($occultation["sun"] > -6)
                {{
                    __(
                        "The Moon is :altitude° above the horizon, but the Sun is up or the sky still bright.",
                        ["altitude" => EventTexts::number($occultation["altitude"], 0)],
                    )
                }}
            @else
                {{
                    __(
                        "The Moon is :altitude° above the horizon.",
                        ["altitude" => EventTexts::number($occultation["altitude"], 0)],
                    )
                }}
            @endif
        </p>
    @endif
    @if ($best)
        <p>
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
        </p>
    @else
        <p>{{
            __(
                "From :location they are not high enough in a dark sky.",
                ["location" => $location->name],
            )
        }}</p>
    @endif
@endif
