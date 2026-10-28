@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item (#960): a comet at its brightest while it can be seen --}}
@php
    $name = !empty($data["slug"])
        ? '<a href="' .
            e(route("object.show", $data["slug"])) .
            '" class="text-blue-400 hover:underline">' .
            e($data["name"]) .
            "</a>"
        : e($data["name"] ?? "");
    $best = $local["best"] ?? null;
@endphp
<p>
    {!!
        __(
            "Comet :comet is predicted to reach magnitude :magnitude, bright enough for binoculars or a small telescope.",
            [
                "comet" => $name,
                "magnitude" => EventTexts::number($data["magnitude"] ?? 0, 1),
            ],
        )
    !!}
    @if (abs($data["elongation"] ?? 0) > 90)
        {{
            __(
                "It is :elongation° from the Sun and visible for most of the night.",
                ["elongation" => EventTexts::number(abs($data["elongation"]), 0)],
            )
        }}
    @elseif (($data["elongation"] ?? 0) > 0)
        {{
            __(
                "It is :elongation° east of the Sun, in the evening sky.",
                ["elongation" => EventTexts::number(abs($data["elongation"]), 0)],
            )
        }}
    @else
        {{
            __(
                "It is :elongation° west of the Sun, in the morning sky.",
                ["elongation" => EventTexts::number(abs($data["elongation"] ?? 0), 0)],
            )
        }}
    @endif
    @if (!empty($data["perihelion"]))
        {{
            __(
                "Perihelion on :date, at :distance AU from the Sun.",
                [
                    "date" => EventTexts::date(
                        \Carbon\Carbon::createFromTimestamp($data["perihelion"], "UTC"),
                        $location,
                    ),
                    "distance" => EventTexts::number($data["q"] ?? 0, 2),
                ],
            )
        }}
    @endif
    {{
        __(
            "The brightness of a comet is hard to predict: it may well be brighter or fainter.",
        )
    }}
</p>
@if ($location && $local)
    <h3 class="mt-3 font-semibold text-white">
        {{
            __("Seen from :location", [
                "location" => $location->name,
            ])
        }}
    </h3>
    @if ($best)
        <p>{{ __("The comet is highest in a dark sky at :time, :altitude° above the horizon.", ["time" => EventTexts::localTime($best["at"], $location), "altitude" => EventTexts::number($best["altitude"], 0)]) }}</p>
    @else
        <p>{{
            __(
                "From :location the comet is not high enough in a dark sky.",
                ["location" => $location->name],
            )
        }}</p>
    @endif
@endif
