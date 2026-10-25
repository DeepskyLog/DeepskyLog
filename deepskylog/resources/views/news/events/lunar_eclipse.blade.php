@php use App\Services\News\EventTexts; @endphp
{{-- An automatic news item (#960): a lunar eclipse, with the altitude of the Moon at each contact --}}
<p>
    @switch ($data["type"] ?? "penumbral")
        @case ("total")
            {{
                __(
                    "The Moon passes completely through the umbra of the Earth and turns a dark copper red during the totality.",
                )
            }}
            @break
        @case ("partial")
            {{
                __(
                    "Part of the Moon passes through the umbra of the Earth: at the greatest eclipse :percent% of its diameter is in the umbra.",
                    ["percent" => (int) round(100 * ($data["umbral_magnitude"] ?? 0))],
                )
            }}
            @break
        @default
            {{
                __(
                    "The Moon only passes through the penumbra of the Earth: a slight darkening, best seen near the greatest eclipse.",
                )
            }}
    @endswitch
    {{
        __(
            "Umbral magnitude :umbral, penumbral magnitude :penumbral.",
            [
                "umbral" => EventTexts::number($data["umbral_magnitude"] ?? 0, 3),
                "penumbral" => EventTexts::number($data["penumbral_magnitude"] ?? 0, 3),
            ],
        )
    }} {{
        __(
            "A lunar eclipse is visible from everywhere the Moon is above the horizon.",
        )
    }}
</p>

@php
    $labels = [
        "p1" => __("Penumbral eclipse begins"),
        "u1" => __("Partial eclipse begins"),
        "u2" => __("Total eclipse begins"),
        "max" => __("Greatest eclipse"),
        "u3" => __("Total eclipse ends"),
        "u4" => __("Partial eclipse ends"),
        "p4" => __("Penumbral eclipse ends"),
    ];
    $altitudes = $local["altitudes"] ?? [];
@endphp
<div class="mt-3 overflow-x-auto">
    <table class="text-sm">
        <thead>
            <tr class="text-left text-gray-400">
                <th class="pr-4 font-normal"></th>
                <th class="pr-4 font-normal">{{ __("UT") }}</th>
                @if ($location)
                    <th class="pr-4 font-normal">
                        {{
                            __("Local time at :location", [
                                "location" => $location->name,
                            ])
                        }}
                    </th>
                    <th class="font-normal">
                        {{
                            __(
                                "Altitude of the Moon",
                            )
                        }}
                    </th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($labels as $contact => $label)
                @php $moment = $data["contacts"][$contact] ?? null; @endphp
                @if ($moment)
                    <tr>
                        <td class="pr-4">{{ $label }}</td>
                        <td class="pr-4">
                            {{
                                gmdate(
                                    "H:i",
                                    $moment,
                                )
                            }}
                        </td>
                        @if ($location)
                            <td class="pr-4">
                                {{
                                    EventTexts::localTime(
                                        $moment,
                                        $location,
                                    )
                                }}
                            </td>
                            <td>
                                @php $altitude = $altitudes[$contact] ?? null; @endphp
                                @if ($altitude === null)
                                    &ndash;
                                @elseif ($altitude < 0)
                                    <span class="text-gray-500">{{
                                        __(
                                            "below the horizon",
                                        )
                                    }}</span>
                                @else
                                    {{
                                        EventTexts::number(
                                            $altitude,
                                            0,
                                        )
                                    }}°
                                @endif
                            </td>
                        @endif
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
