@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item (#960): an outer planet at opposition --}}
@php $planet = $data["bodies"][0] ?? ""; @endphp
<p>
    {!!
        __(
            ":planet is opposite the Sun: it rises at sunset, is visible all night and is at its closest to the Earth (:distance AU) and brightest (magnitude :magnitude).",
            [
                "planet" =>
                    '<a href="' .
                    e(route("object.show", $planet)) .
                    '" class="text-blue-400 hover:underline">' .
                    e(EventTexts::body($planet)) .
                    "</a>",
                "distance" => EventTexts::number($data["distance"] ?? 0, 2),
                "magnitude" => EventTexts::number($data["magnitude"] ?? 0, 1),
            ],
        )
    !!} {{
        __(
            "The weeks around the opposition are the best time of the year to observe it.",
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
    @if ($local["altitude"] > 0)
        <p>{{ __(":planet reaches :altitude° above the horizon around midnight.", ["planet" => EventTexts::body($planet), "altitude" => EventTexts::number($local["altitude"], 0)]) }}</p>
    @else
        <p>{{
            __(
                ":planet does not rise above the horizon from :location.",
                ["planet" => EventTexts::body($planet), "location" => $location->name],
            )
        }}</p>
    @endif
@endif
