@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item: a supernova or nova bright enough for an amateur telescope, from the latest AAVSO estimate --}}
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
            "The latest estimate of :name in the AAVSO International Database is magnitude :magnitude (:band), on :date.",
            [
                "name" => $name,
                "magnitude" => EventTexts::number($data["magnitude"] ?? 0, 1),
                "band" => e($data["band"] ?? ""),
                "date" => e(EventTexts::date($item->event_at, $location)),
            ],
        )
    !!}
    @if (($data["peak"] ?? ($data["magnitude"] ?? 0)) < ($data["magnitude"] ?? 0))
        {{
            __(
                "It was as bright as magnitude :magnitude.",
                [
                    "magnitude" => EventTexts::number($data["peak"], 1),
                ],
            )
        }}
    @endif
    @if ($item->kind === "supernova")
        {{
            __(
                "A supernova this bright can be seen in an amateur telescope. Supernovae fade within weeks: observe it soon.",
            )
        }}
    @else
        {{
            __(
                "The nova is in outburst; at minimum it is magnitude :minimum. Novae fade within days to weeks: observe it soon.",
                ["minimum" => $data["minimum"] ?? "?"],
            )
        }}
    @endif
    {!!
        __(
            "Latest estimates and finder charts: :link.",
            [
                "link" =>
                    '<a href="https://vsx.aavso.org/index.php?view=detail.top&oid=' .
                    urlencode((string) ($data["oid"] ?? "")) .
                    '" class="text-blue-400 hover:underline" rel="noopener" target="_blank">' .
                    e($data["star"] ?? ($data["name"] ?? "")) .
                    " (AAVSO)</a>",
            ],
        )
    !!}
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
        <p>{{ __("It is highest in a dark sky at :time, :altitude° above the horizon.", ["time" => EventTexts::localTime($best["at"], $location), "altitude" => EventTexts::number($best["altitude"], 0)]) }}</p>
    @else
        <p>{{
            __(
                "From :location it is not high enough in a dark sky.",
                ["location" => $location->name],
            )
        }}</p>
    @endif
@endif
