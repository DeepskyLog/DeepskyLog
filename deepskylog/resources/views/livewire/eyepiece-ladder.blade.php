@php
    use App\Services\FieldViews\FieldViewBuilder;

    // the exit pupils of the strip, low magnifications on the left, on a logarithmic scale
    $widest = 8.0;
    $narrowest = 0.4;
    $position = fn(float $pupil) => round(
        max(0, min(100, (log($widest / $pupil) / log($widest / $narrowest)) * 100)),
        2,
    );
    $pupil = fn(float $value) => rtrim(
        rtrim(number_format($value, 1, ".", ""), "0"),
        ".",
    );
    $focal = fn(float $value) => rtrim(
        rtrim(number_format($value, 1, ".", ""), "0"),
        ".",
    );
@endphp

<div class="space-y-4" @if ($waiting) wire:poll.5s @endif>
    <h2 class="text-xl font-bold">
        {{
            __(
                "Eyepiece ladder",
            )
        }}
    </h2>
    <p class="text-sm text-gray-400">
        {{
            __(
                "The magnifications your active eyepieces and lenses give with this instrument, from low to high, with the exit pupil and true field of each.",
            )
        }}
    </p>

    @if ($sets)
        <label class="block max-w-sm text-sm">
            <span class="mb-1 block text-gray-400">{{
                __(
                    "Eyepieces and lenses",
                )
            }}</span>
            <select
                wire:model.live="instrumentSet"
                class="w-full rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">
                    {{
                        __(
                            "All my eyepieces and lenses",
                        )
                    }}
                </option>
                @foreach ($sets as $id => $label)
                    <option value="{{ $id }}">
                        {{
                            __("Only those of :set", [
                                "set" => $label,
                            ])
                        }}
                    </option>
                @endforeach
            </select>
        </label>
    @endif

    @if ($steps || $gaps)
        {{-- the exit pupils on a strip: steps in blue, gaps in orange, suggestions dashed --}}
        <div class="max-w-2xl">
            <div
                class="relative h-8 rounded"
                style="background-color: #1f2937"
                role="img"
                aria-label="{{ __("Exit pupils of the eyepiece ladder") }}"
            >
                @foreach ($gaps as $gap)
                    @if ($gap["kind"] === "between")
                        <div
                            class="absolute top-0 h-full"
                            style="left: {{ $position($gap["from"]) }}%; width: {{ $position($gap["to"]) - $position($gap["from"]) }}%; background-color: rgba(249, 115, 22, 0.25)"
                        ></div>
                    @endif

                    @foreach ($gap["suggestions"] as $suggestion)
                        <div
                            class="absolute top-0 h-full"
                            style="left: {{ $position($suggestion["exit_pupil"]) }}%; border-left: 2px dashed #f97316"
                            title="{{ $focal($suggestion["focal_length"]) }} mm · {{ $pupil($suggestion["exit_pupil"]) }} mm"
                        ></div>
                    @endforeach
                @endforeach

                @foreach ($steps as $step)
                    <div
                        class="absolute rounded"
                        style="top: 0.25rem; left: {{ $position($step["exit_pupil"]) }}%; width: {{ max(0.6, $position($step["min_exit_pupil"]) - $position($step["exit_pupil"])) }}%; height: 1.5rem; background-color: #60a5fa"
                        title="{{ $step["magnification"] }}{{ $step["max_magnification"] ? "–" . $step["max_magnification"] : "" }}× · {{ $pupil($step["exit_pupil"]) }} mm"
                    ></div>
                @endforeach
            </div>
            <div class="mt-1 flex justify-between text-xs text-gray-500">
                <span>{{ $pupil($widest) }} mm</span>
                <span>{{
                    __(
                        "Exit pupil",
                    )
                }}</span>
                <span>{{ $pupil($narrowest) }} mm</span>
            </div>
        </div>
    @endif

    @if ($steps)
        @php
            // rows only given with a Barlow or reducer are shown on request
            $throughLens = fn(array $step) => collect($step["combinations"])->every(
                fn($combination) => $combination["lens"] !== null,
            );
            $lensRows = count(array_filter($steps, $throughLens));
        @endphp

        <div class="overflow-x-auto" x-data="{ lenses: false }">
            @if ($lensRows)
                <label
                    class="mb-2 flex cursor-pointer items-center gap-2 text-sm text-gray-300"
                >
                    <input
                        type="checkbox"
                        x-model="lenses"
                        class="rounded border-gray-600 bg-gray-900"
                    />
                    {{
                        trans_choice(
                            "{1} Show the magnification given with a Barlow lens or reducer|[2,*] Show the :count magnifications given with a Barlow lens or reducer",
                            $lensRows,
                            ["count" => $lensRows],
                        )
                    }}
                </label>
            @endif
            <table class="w-full max-w-3xl text-left text-sm">
                <thead class="text-xs text-gray-400 uppercase">
                    <tr>
                        <th class="px-2 py-1 text-right">
                            {{
                                __(
                                    "Magnification",
                                )
                            }}
                        </th>
                        <th class="px-2 py-1">{{ __("Eyepiece") }}</th>
                        <th class="px-2 py-1 text-right">
                            {{
                                __(
                                    "Exit pupil",
                                )
                            }}
                        </th>
                        <th class="px-2 py-1 text-right">
                            {{
                                __(
                                    "Field of View",
                                )
                            }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach ($steps as $step)
                        <tr
                            wire:key="step-{{ $step["magnification"] }}-{{ $step["max_magnification"] }}"
                            @if ($throughLens($step))
                                x-show="lenses"
                                x-cloak
                            @endif
                        >
                            <td class="px-2 py-1 text-right whitespace-nowrap">
                                {{
                                    $step[
                                        "magnification"
                                    ]
                                }}{{
                                    $step["max_magnification"]
                                        ? "–" . $step["max_magnification"]
                                        : ""
                                }}×
                            </td>
                            <td class="px-2 py-1">
                                @foreach ($step["combinations"] as $combination)
                                    <div>
                                        <a
                                            href="/eyepiece/{{ Auth::user()->slug }}/{{ $combination["eyepiece"]->slug }}"
                                            class="text-blue-400 hover:underline"
                                            >{{
                                                html_entity_decode(
                                                    $combination["eyepiece"]->name,
                                                    ENT_QUOTES | ENT_HTML5,
                                                    "UTF-8",
                                                )
                                            }}</a
                                        >
                                        @if ($combination["lens"])
                                            +
                                            <a
                                                href="/lens/{{ Auth::user()->slug }}/{{ $combination["lens"]->slug }}"
                                                class="text-blue-400 hover:underline"
                                                >{{
                                                    html_entity_decode(
                                                        $combination["lens"]->name,
                                                        ENT_QUOTES | ENT_HTML5,
                                                        "UTF-8",
                                                    )
                                                }}</a
                                            >
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-2 py-1 text-right whitespace-nowrap">
                                {{
                                    $pupil(
                                        $step["exit_pupil"],
                                    )
                                }}{{
                                    $step["max_magnification"]
                                        ? "–" . $pupil($step["min_exit_pupil"])
                                        : ""
                                }} mm
                                @if ($step["exit_pupil"] >
                                    \App\Services\ContrastReserveCalculator::EYE_PUPIL_MM)
                                    <span
                                        class="text-yellow-400"
                                        title="{{ __("Larger than the pupil of the eye: part of the light is lost") }}"
                                        >!</span
                                    >
                                @endif
                            </td>
                            <td class="px-2 py-1 text-right whitespace-nowrap">
                                {{
                                    $step["field"] !== null
                                        ? FieldViewBuilder::formatAngle($step["field"])
                                        : __("Unknown")
                                }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-yellow-400">
            {{
                __(
                    "You have no active eyepieces for this instrument.",
                )
            }}
            <a
                href="{{ route("eyepiece.create") }}"
                class="text-blue-400 hover:underline"
                >{{
                    __(
                        "Add an eyepiece",
                    )
                }}</a
            >
        </p>
    @endif

    @if ($gaps)
        <div class="max-w-3xl space-y-3">
            <h3 class="text-lg font-bold">
                {{
                    __(
                        "Gaps in the ladder",
                    )
                }}
            </h3>
            @foreach ($gaps as $gap)
                <div
                    class="rounded border border-gray-700 bg-gray-800 p-3 text-sm"
                    wire:key="gap-{{ $gap["kind"] }}-{{ $gap["from"] }}-{{ $gap["to"] }}"
                >
                    <p class="text-gray-200">
                        @if ($gap["kind"] === "low")
                            @if ($gap["field"] !== null)
                                {{
                                    __(
                                        "Your widest field is :field (:magnification×, exit pupil :pupil mm): a wide 2\" eyepiece shows large objects whole.",
                                        [
                                            "field" => FieldViewBuilder::formatAngle($gap["field"]),
                                            "magnification" => $gap["magnification"],
                                            "pupil" => $pupil($gap["from"]),
                                        ],
                                    )
                                }}
                            @elseif ($gap["from"] !== null)
                                {{
                                    __(
                                        "Your lowest magnification is :magnification× (exit pupil :pupil mm): a wide 2\" eyepiece shows large objects whole.",
                                        [
                                            "magnification" => $gap["magnification"],
                                            "pupil" => $pupil($gap["from"]),
                                        ],
                                    )
                                }}
                            @else
                                {{
                                    __(
                                        "A low magnification with a wide field, to find objects and see large ones whole.",
                                    )
                                }}
                            @endif
                        @elseif ($gap["kind"] === "between")
                            {{
                                __(
                                    "Nothing between :from mm and :to mm exit pupil (:low× to :high×).",
                                    [
                                        "from" => $pupil($gap["from"]),
                                        "to" => $pupil($gap["to"]),
                                        "low" => $gap["magnification"],
                                        "high" => $gap["to_magnification"],
                                    ],
                                )
                            }}
                        @elseif ($gap["from"] !== null)
                            {{
                                __(
                                    "Your highest magnification is :magnification× (exit pupil :pupil mm): planets, small planetary nebulae and close double stars want more on steady nights.",
                                    [
                                        "magnification" => $gap["magnification"],
                                        "pupil" => $pupil($gap["from"]),
                                    ],
                                )
                            }}
                        @else
                            {{
                                __(
                                    "A high magnification, for planets, small planetary nebulae and close double stars on steady nights.",
                                )
                            }}
                        @endif
                    </p>
                    <ul class="mt-2 space-y-1">
                        @foreach ($gap["suggestions"] as $suggestion)
                            <li class="text-gray-300">
                                <span class="font-semibold text-orange-400">
                                    {{
                                        __(":focal_length mm eyepiece", [
                                            "focal_length" => $focal($suggestion["focal_length"]),
                                        ])
                                    }}
                                </span>
                                · {{ $suggestion["magnification"] }}× · {{
                                    __("exit pupil :pupil mm", [
                                        "pupil" => $pupil($suggestion["exit_pupil"]),
                                    ])
                                }} · {{
                                    __("field :field", [
                                        "field" => FieldViewBuilder::formatAngle($suggestion["field"]),
                                    ])
                                }} ·
                                <a
                                    href="{{ $suggestion["url"] }}"
                                    class="text-blue-400 hover:underline"
                                >
                                    @if ($suggestion["objects"] === null)
                                        {{
                                            __(
                                                "Which objects would it show?",
                                            )
                                        }}
                                    @else
                                        {{
                                            trans_choice(
                                                "{0} no objects you have not seen|{1} one object you have not seen|[2,*] :count objects you have not seen",
                                                $suggestion["objects"],
                                                ["count" => number_format($suggestion["objects"])],
                                            )
                                        }}
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <details>
                <summary
                    class="cursor-pointer text-xs text-gray-500 select-none hover:text-gray-300"
                >
                    {{
                        __(
                            "How are the gaps found?",
                        )
                    }}
                </summary>
                <div class="mt-1 space-y-1 text-xs text-gray-400">
                    <p>
                        {{
                            __(
                                "Two steps of the ladder whose exit pupils differ by more than a factor :ratio leave a gap; the suggested eyepieces divide it in steps of about :step.",
                                [
                                    "ratio" => \App\Services\EyepieceGaps::GAP_RATIO,
                                    "step" => \App\Services\EyepieceGaps::STEP_RATIO,
                                ],
                            )
                        }}
                    </p>
                    <p>
                        {{
                            __(
                                "The lowest magnification suggested gives an exit pupil of :low mm, or the widest field of a 2\" eyepiece (field stop :stop mm); the highest an exit pupil of :high mm, at most :max×. The suggested eyepieces have an apparent field of :afov°.",
                                [
                                    "low" => $pupil(\App\Services\EyepieceGaps::LOW_EXIT_PUPIL_MM),
                                    "stop" => $pupil(\App\Services\EyepieceGaps::FIELD_STOP_MM),
                                    "high" => $pupil(\App\Services\EyepieceGaps::HIGH_EXIT_PUPIL_MM),
                                    "max" => \App\Services\EyepieceGaps::MAX_MAGNIFICATION,
                                    "afov" => (int) \App\Services\EyepieceGaps::SUGGESTED_APPARENT_FOV,
                                ],
                            )
                        }}
                    </p>
                    @if ($location)
                        <p>
                            {{
                                __(
                                    "The objects are the deep-sky objects you have not seen that the eyepiece lets you see at :location and none of your eyepieces does, calculated from their contrast reserve.",
                                    [
                                        "location" => html_entity_decode(
                                            (string) $location->name,
                                            ENT_QUOTES | ENT_HTML5,
                                            "UTF-8",
                                        ),
                                    ],
                                )
                            }}
                        </p>
                    @endif
                </div>
            </details>
        </div>
    @elseif ($steps)
        <p class="text-sm text-green-400">
            {{
                __(
                    "No gaps: your eyepieces cover this instrument from a wide field to its highest useful magnification.",
                )
            }}
        </p>
    @endif
</div>
