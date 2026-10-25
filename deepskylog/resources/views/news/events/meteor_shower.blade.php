@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item (#960): the maximum of a major meteor shower --}}
@php $illumination = (int) round(100 * ($data["moon_illumination"] ?? 0)); @endphp
<p>
    {{
        __(
            "The :shower reach their maximum at about :time UT (:local at :location), with up to :zhr meteors an hour when the radiant is high in a dark sky; fewer are seen in practice.",
            [
                "shower" => EventTexts::shower($data["shower"] ?? ""),
                "time" => $item->event_at->format("H:i"),
                "local" => $location
                    ? EventTexts::localTime($item->event_at->timestamp, $location)
                    : "",
                "location" => $location?->name ?? "",
                "zhr" => $data["zhr"] ?? 0,
            ],
        )
    }}
    @if ($illumination < 25)
        {{
            __(
                "The Moon is :illumination% illuminated and hardly disturbs.",
                ["illumination" => $illumination],
            )
        }}
    @else
        {{
            __(
                "The Moon is :illumination% illuminated and brightens the sky.",
                ["illumination" => $illumination],
            )
        }}
    @endif
    {{
        __(
            "The nights before and after the maximum are worth watching too.",
        )
    }}
</p>
