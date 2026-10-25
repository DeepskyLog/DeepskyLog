@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item (#960): Mercury or Venus at its greatest distance from the Sun --}}
@php
    $planet = $data["bodies"][0] ?? "";
    $name =
        '<a href="' .
        e(route("object.show", $planet)) .
        '" class="text-blue-400 hover:underline">' .
        e(EventTexts::body($planet)) .
        "</a>";
    $twilight = $local["twilight"] ?? null;
@endphp
<p>
    @if (($data["side"] ?? "east") === "east")
        {!!
            __(
                ":planet is :elongation° east of the Sun (magnitude :magnitude): look for it low in the west after sunset.",
                [
                    "planet" => $name,
                    "elongation" => EventTexts::number($data["elongation"] ?? 0, 1),
                    "magnitude" => EventTexts::number($data["magnitude"] ?? 0, 1),
                ],
            )
        !!}
    @else
        {!!
            __(
                ":planet is :elongation° west of the Sun (magnitude :magnitude): look for it low in the east before sunrise.",
                [
                    "planet" => $name,
                    "elongation" => EventTexts::number($data["elongation"] ?? 0, 1),
                    "magnitude" => EventTexts::number($data["magnitude"] ?? 0, 1),
                ],
            )
        !!}
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
    @if ($twilight && $twilight["altitude"] > 0)
        <p>{{ __("At :time, with the Sun 6° below the horizon, :planet is :altitude° above the horizon.", ["time" => EventTexts::localTime($twilight["at"], $location), "planet" => EventTexts::body($planet), "altitude" => EventTexts::number($twilight["altitude"], 0)]) }}</p>
    @else
        <p>{{
            __(
                ":planet is below the horizon in the twilight at :location.",
                ["planet" => EventTexts::body($planet), "location" => $location->name],
            )
        }}</p>
    @endif
@endif
