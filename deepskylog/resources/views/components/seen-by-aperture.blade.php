@props (["objectName"])

{{-- How many of the observers who looked for a deep-sky object saw it, per aperture class, next to the model's prediction (object page) --}}
@php
    $seenBy = null;
    try {
        $seenBy = app(\App\Services\Observations\SeenByAperture::class)->forObject(
            $objectName,
        );
    } catch (\Throwable $_) {
        $seenBy = null;
    }
    $viewer = auth()->user();
    $inches = (bool) ($viewer?->showInches ?? false);
    $standard = $viewer?->standardInstrument;
    $viewerClass =
        $standard && (float) $standard->aperture_mm > 0
            ? \App\Services\Observations\SeenByAperture::classFor(
                $standard->aperture_mm,
                optional($standard->instrument_type)->name ===
                    \App\Services\Observations\ApertureRecords::NAKED_EYE_TYPE,
            )
            : null;
    $hasPrediction =
        $seenBy &&
        collect($seenBy["classes"])->contains(fn($c) => $c["category"] !== null);
@endphp

@if ($seenBy)
    <div class="mt-6">
        <h2 class="text-xl font-semibold text-white">
            {{
                __(
                    "Seen by how many, per aperture",
                )
            }}
        </h2>
        <p class="mt-1 text-sm text-gray-400">
            {{
                __(
                    "The share of the observers who looked for this object with each aperture that reported it seen. Observers seldom record an object they did not see, so the real share is often lower. Observations the suspect-observation check doubts are left out.",
                )
            }}
        </p>
        <table class="mt-2 w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400">
                    <th class="py-1 pr-3 font-normal">{{ __("Aperture") }}</th>
                    <th class="w-1/2 py-1 pr-3 font-normal">
                        {{ __("Seen by") }}
                    </th>
                    <th class="py-1 pr-3 font-normal">
                        {{
                            __(
                                "Observers",
                            )
                        }}
                    </th>
                    @if ($hasPrediction)
                        <th class="py-1 font-normal">
                            {{
                                __(
                                    "Predicted",
                                )
                            }}
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($seenBy["classes"] as $row)
                    @php
                        $label = \App\Services\Observations\SeenByAperture::label(
                            $row["class"],
                            $inches,
                        );
                        $mine = $row["class"] === $viewerClass;
                    @endphp

                    <tr
                        @class (["border-t border-gray-700", "bg-gray-800" => $mine])
                        title="{{ __(":seen of :total observers with :aperture saw it", ["seen" => $row["seen"], "total" => $row["observers"], "aperture" => $label]) }}"
                    >
                        <td class="py-1.5 pr-3 whitespace-nowrap text-gray-200">
                            {{ $label }}
                            @if ($mine)
                                <span class="text-xs text-indigo-300"
                                    >({{ __("yours") }})</span
                                >
                            @endif
                        </td>
                        <td class="py-1.5 pr-3">
                            @if ($row["percentage"] !== null)
                                <div class="flex items-center gap-2">
                                    <div class="h-3 flex-1 rounded bg-gray-700">
                                        <div
                                            class="h-3 rounded bg-indigo-500"
                                            style="width: {{ max(0, min(100, $row["percentage"])) }}%"
                                        ></div>
                                    </div>
                                    <span
                                        class="w-12 text-right font-semibold text-white"
                                        >{{ round($row["percentage"]) }}%</span
                                    >
                                </div>
                            @else
                                <span class="text-xs text-gray-400">{{
                                    __(
                                        "Too few observers",
                                    )
                                }}</span>
                            @endif
                        </td>
                        <td class="py-1.5 pr-3 whitespace-nowrap text-gray-300">
                            {{
                                __(":seen of :total", [
                                    "seen" => $row["seen"],
                                    "total" => $row["observers"],
                                ])
                            }}
                        </td>
                        @if ($hasPrediction)
                            <td class="py-1.5">
                                @if ($row["category"] !== null)
                                    <span
                                        @class ([
                                            "text-green-400" => $row["category"] === "very_easy",
                                            "text-green-600" => $row["category"] === "easy",
                                            "text-orange-400" => $row["category"] === "quite_difficult",
                                            "text-orange-600" => $row["category"] === "difficult",
                                            "text-gray-300" => in_array(
                                                $row["category"],
                                                ["questionable", "not_visible"],
                                                true
                                            )
                                        ])
                                        title="{{ __("Contrast reserve") }}: {{ number_format($row["contrast_reserve"], 2) }}"
                                        >{{
                                            __(
                                                "contrast.reserve.category." . $row["category"],
                                            )
                                        }}</span
                                    >
                                @else
                                    <span class="text-gray-500">–</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($hasPrediction)
            <p class="mt-1 text-xs text-gray-400">
                @if ($seenBy["sqm_from_observations"])
                    {{
                        __(
                            "Predicted: the contrast reserve of a telescope of each class under the median sky of these observations (SQM :sqm), with the object overhead.",
                            ["sqm" => number_format($seenBy["sqm"], 2)],
                        )
                    }}
                @else
                    {{
                        __(
                            "Predicted: the contrast reserve of a telescope of each class under a dark sky (SQM :sqm), with the object overhead.",
                            ["sqm" => number_format($seenBy["sqm"], 2)],
                        )
                    }}
                @endif
            </p>
        @endif
    </div>
@endif
