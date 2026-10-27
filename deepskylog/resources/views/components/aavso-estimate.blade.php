@props (["variable"])

{{-- The latest AAVSO estimate of a variable star, nova or supernova (App\Services\AavsoVariableStars), a row of the object page --}}
@php
    $star = $variable["star"];
    $latest = $variable["latest"];
    $kind = \App\Services\AavsoVariableStars::kind($star);
@endphp
<tr>
    <td class="pr-4 align-top font-medium">{{ __("AAVSO") }}</td>
    <td id="dsl-aavso">
        @if ($latest)
            <div>
                {{
                    __(
                        "Magnitude :magnitude (:band) on :date",
                        [
                            "magnitude" => number_format($latest["magnitude"], 1),
                            "band" => $latest["band"],
                            "date" =>
                                \Carbon\Carbon::createFromTimestamp($latest["at"], "UTC")
                                    ->locale(app()->getLocale())
                                    ->translatedFormat("j M Y H:i") . " UT",
                        ],
                    )
                }}
            </div>
            @if ($latest["count"] > 1)
                <div class="text-xs text-gray-400">
                    {{
                        __(
                            ":count estimates in the last :days days, from :brightest to :faintest",
                            [
                                "count" => $latest["count"],
                                "days" => \App\Services\AavsoVariableStars::LOOKBACK_DAYS,
                                "brightest" => number_format($latest["brightest"], 1),
                                "faintest" => number_format($latest["faintest"], 1),
                            ],
                        )
                    }}
                </div>
            @endif
        @else
            <div class="text-gray-400">
                {{
                    __(
                        "No estimates in the last :days days",
                        ["days" => \App\Services\AavsoVariableStars::LOOKBACK_DAYS],
                    )
                }}
            </div>
        @endif
        <div class="text-xs text-gray-400">
            <a
                href="{{ \App\Services\AavsoVariableStars::url($star) }}"
                class="text-blue-400 hover:underline"
                rel="noopener"
                target="_blank"
                >{{ $star["name"] }}</a
            >
            @if ($kind === "supernova")
                · {{
                    __("Supernova of type :type", [
                        "type" => trim(substr($star["type"], 2)) ?: "?",
                    ])
                }}
            @elseif ($kind === "nova")
                · {{ __("Nova") }} ({{ $star["type"] }})
            @elseif ($star["type"] !== "")
                · {{
                    __("Type :type", [
                        "type" => $star["type"],
                    ])
                }}
            @endif
            @if ($star["max"] !== "" && $star["min"] !== "")
                · {{ $star["max"] }} – {{ $star["min"] }}
            @endif
            @if ($star["period"])
                · {{
                    __("period :days days", [
                        "days" => rtrim(
                            rtrim(number_format($star["period"], 3, ".", ""), "0"),
                            ".",
                        ),
                    ])
                }}
            @endif
        </div>
        <div class="text-xs text-gray-500">
            {!!
                __("Data of the :link", [
                    "link" =>
                        '<a href="https://www.aavso.org/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">AAVSO International Database</a>',
                ])
            !!}
        </div>
    </td>
</tr>
