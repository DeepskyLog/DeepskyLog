@php
    use App\Services\News\EventTexts;
    use App\Services\Planets\PlanetMoonEvents;
@endphp
{{-- An automatic news item (#960): two or more shadows of Galilean moons on Jupiter, or the shadow of Titan on Saturn --}}
@php
    $planet = $data["planet"] ?? "jupiter";
    $planetLink =
        '<a href="' .
        e(route("object.show", $planet)) .
        '" class="text-blue-400 hover:underline">' .
        e(EventTexts::body($planet)) .
        "</a>";
    $visible = $local["visible"] ?? null;
    $pageQuery = [
        "night" => \Carbon\Carbon::createFromTimestamp(
            $data["start"] ?? 0,
            EventTexts::timezone($location),
        )
            ->subHours(12)
            ->toDateString(),
    ];
@endphp
<p>
    @if ($item->kind === "titan_shadow_transit")
        {!!
            __(
                "The shadow of Titan, the largest moon of Saturn, crosses the disk of :planet. Titan's shadow only falls on Saturn in the years around Saturn's equinox, when we see its rings edge-on; a telescope of 15 cm shows it as a small dark dot.",
                ["planet" => $planetLink],
            )
        !!}
    @else
        {!!
            __(
                "The shadows of :moons are on the disk of :planet at the same time: small black dots on the cloud bands, easy in a telescope of 10 cm on a steady night.",
                [
                    "moons" => e(EventTexts::moons($data["moons"] ?? [])),
                    "planet" => $planetLink,
                ],
            )
        !!}
    @endif
</p>
<ul class="list-disc pl-5">
    @foreach ($data["shadows"] ?? [] as $shadow)
        <li>
            {{
                __(
                    "The shadow of :moon from :start to :end",
                    [
                        "moon" => PlanetMoonEvents::moonName($shadow["moon"]),
                        "start" => EventTexts::localTime($shadow["start"], $location),
                        "end" => EventTexts::localTime($shadow["end"], $location),
                    ],
                )
            }}
        </li>
    @endforeach
</ul>
@if ($location && $local)
    <h3 class="mt-3 font-semibold text-white">
        {{
            __("Seen from :location", [
                "location" => $location->name,
            ])
        }}
    </h3>
    @if ($visible)
        <p>
            {{
                __(
                    "Visible from :from to :until, with :planet up to :altitude° above the horizon.",
                    [
                        "from" => EventTexts::localTime($visible["from"], $location),
                        "until" => EventTexts::localTime($visible["until"], $location),
                        "planet" => EventTexts::body($planet),
                        "altitude" => EventTexts::number($visible["altitude"], 0),
                    ],
                )
            }}
        </p>
    @else
        <p>
            {{
                __(
                    "From :location :planet is not high enough in a dark sky then.",
                    [
                        "location" => $location->name,
                        "planet" => EventTexts::body($planet),
                    ],
                )
            }}
        </p>
    @endif
@endif
<p>
    <a
        href="{{ route("planet-moons.show", $pageQuery) }}"
        class="text-blue-400 hover:underline"
        >{{
            __(
                "The moons of Jupiter and Saturn that night",
            )
        }} &rarr;</a
    >
</p>
