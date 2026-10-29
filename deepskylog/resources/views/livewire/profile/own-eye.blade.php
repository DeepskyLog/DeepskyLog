{{-- "Your own eye": the observer's own detection threshold (App\Services\DetectionThreshold) --}}
@php
    $threshold = $fit["threshold"] ?? null;
    $error = $fit["standard_error"] ?? null;
    $reliable = (bool) ($fit["reliable"] ?? false);
    $signed = fn($value) => ($value > 0 ? "+" : ($value < 0 ? "−" : "")) .
        number_format(abs($value), 2);

    // The chart: the share of the seen and of the not seen observations per bin of contrast reserve
    [$min, $max] = $range;
    [$width, $height, $left, $right, $top, $bottom] = [480, 170, 28, 28, 22, 26];
    $peak = max([
        0.01,
        ...array_column($distributions, "seen"),
        ...array_column($distributions, "not_seen"),
    ]);
    $x = fn($reserve) => $left +
        (($reserve - $min) / ($max - $min)) * ($width - $left - $right);
    $y = fn($share) => $top + (1 - $share / $peak) * ($height - $top - $bottom);
    $line = fn($key) => collect($distributions)
        ->map(
            fn($bin) => round($x(($bin["from"] + $bin["to"]) / 2), 1) .
                "," .
                round($y($bin[$key]), 1),
        )
        ->implode(" ");
@endphp

<x-action-section>
    <x-slot name="title">
        {{
            __(
                "Your own eye",
            )
        }}
    </x-slot>

    <x-slot name="description">
        {{
            __(
                "The contrast reserve describes a model observer, who sees an object from a contrast reserve of 0. From your deep-sky observations, the ones you saw and the ones you did not see, DeepskyLog learns your own threshold: the contrast reserve at which you see an object half the time.",
            )
        }}
    </x-slot>

    <x-slot name="content">
        <div
            wire:init="refresh"
            @if ($pending) wire:poll.3s @endif
            class="space-y-4 text-sm text-gray-300"
        >
            @if ($pending && $fit === null)
                <p class="text-gray-400">
                    {{
                        __(
                            "Calculating your threshold…",
                        )
                    }}
                </p>
            @elseif ($threshold === null)
                @if ($fit && $fit["reason"])
                    <p>{{
                        __(
                            $fit["reason"],
                        )
                    }}</p>
                @endif

                <p>
                    {{
                        __(
                            "Record the objects you looked for but did not see as \"Object definitely not seen\": from :min of them (a very questionable perception counts half) and :seen seen objects, DeepskyLog learns your own threshold.",
                            [
                                "min" => \App\Services\DetectionThreshold::MIN_NOT_SEEN,
                                "seen" => \App\Services\DetectionThreshold::MIN_SEEN,
                            ],
                        )
                    }}
                </p>
                @if ($fit)
                    <p class="text-gray-400">
                        {{
                            __(
                                "So far: :seen seen, :not_seen not seen and :questionable very questionable.",
                                [
                                    "seen" => $fit["seen"],
                                    "not_seen" => $fit["not_seen"],
                                    "questionable" => $fit["questionable"],
                                ],
                            )
                        }}
                    </p>
                @endif
            @else
                <p class="text-base text-white">
                    {{
                        __(
                            "You see half of the objects with a contrast reserve of :threshold; the model assumes 0.",
                            ["threshold" => $signed($threshold)],
                        )
                    }}
                </p>
                <p>
                    @if (abs($threshold) <= max(0.05, (float) $error))
                        {{
                            __(
                                "That is about what the model assumes.",
                            )
                        }}
                    @elseif ($threshold < 0)
                        {{
                            __(
                                "You see fainter objects than the model expects.",
                            )
                        }}
                    @else
                        {{
                            __(
                                "You need a little more contrast than the model expects: objects close to its limit are often too faint for you.",
                            )
                        }}
                    @endif
                </p>
                <p class="text-gray-400">
                    {{
                        __(
                            "Uncertainty ± :error, from :seen observations seen, :not_seen not seen and :questionable very questionable.",
                            [
                                "error" => number_format((float) $error, 2),
                                "seen" => $fit["seen"],
                                "not_seen" => $fit["not_seen"],
                                "questionable" => $fit["questionable"],
                            ],
                        )
                    }}
                </p>

                @if (count($distributions) > 1)
                    <figure class="space-y-2">
                        <div class="flex flex-wrap gap-4 text-xs text-gray-300">
                            <span class="flex items-center gap-1">
                                <span
                                    class="inline-block h-0.5 w-4"
                                    style="background: #3b82f6"
                                ></span>
                                {{ __("Seen") }}
                            </span>
                            <span class="flex items-center gap-1">
                                <span
                                    class="inline-block h-0.5 w-4"
                                    style="background: #ea580c"
                                ></span>
                                {{ __("Not seen") }}
                            </span>
                        </div>
                        <svg
                            viewBox="0 0 {{ $width }} {{ $height }}"
                            class="w-full max-w-xl"
                            role="img"
                            aria-label="{{ __("The contrast reserves of your observations seen and not seen") }}"
                        >
                            {{-- baseline and ticks --}}
                            <line
                                x1="{{ $left }}"
                                x2="{{ $width - $right }}"
                                y1="{{ $height - $bottom }}"
                                y2="{{ $height - $bottom }}"
                                stroke="#4b5563"
                                stroke-width="1"
                            />
                            @for ($tick = $min; $tick <= $max + 1e-9; $tick += 0.5)
                                <text
                                    x="{{ $x($tick) }}"
                                    y="{{ $height - $bottom + 16 }}"
                                    fill="#9ca3af"
                                    font-size="11"
                                    text-anchor="middle"
                                >
                                    {{
                                        ($tick > 0
                                            ? "+"
                                            : ($tick < 0
                                                ? "−"
                                                : "")) . number_format(abs($tick), 1)
                                    }}
                                </text>
                            @endfor

                            {{-- the model's threshold and the observer's --}}
                            <line
                                x1="{{ $x(0) }}"
                                x2="{{ $x(0) }}"
                                y1="{{ $top - 6 }}"
                                y2="{{ $height - $bottom }}"
                                stroke="#9ca3af"
                                stroke-width="1"
                                stroke-dasharray="3 3"
                            />
                            <text
                                x="{{ $x(0) + ($threshold < 0 ? 4 : -4) }}"
                                y="{{ $top - 10 }}"
                                fill="#9ca3af"
                                font-size="11"
                                text-anchor="{{ $threshold < 0 ? "start" : "end" }}"
                            >
                                {{ __("Model") }}
                            </text>
                            @php
                                $own = max($min, min($max, $threshold));
                            @endphp

                            <line
                                x1="{{ $x($own) }}"
                                x2="{{ $x($own) }}"
                                y1="{{ $top - 6 }}"
                                y2="{{ $height - $bottom }}"
                                stroke="#f3f4f6"
                                stroke-width="1.5"
                            />
                            <text
                                x="{{ $x($own) + ($threshold < 0 ? -4 : 4) }}"
                                y="{{ $top - 10 }}"
                                fill="#f3f4f6"
                                font-size="11"
                                text-anchor="{{ $threshold < 0 ? "end" : "start" }}"
                            >
                                {{ __("You") }}
                            </text>

                            <polyline
                                points="{{ $line("seen") }}"
                                fill="none"
                                stroke="#3b82f6"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />
                            <polyline
                                points="{{ $line("not_seen") }}"
                                fill="none"
                                stroke="#ea580c"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />

                            {{-- hover targets per bin --}}
                            @foreach ($distributions as $bin)
                                <rect
                                    x="{{ $x($bin["from"]) }}"
                                    y="{{ $top }}"
                                    width="{{ $x($bin["to"]) - $x($bin["from"]) }}"
                                    height="{{ $height - $top - $bottom }}"
                                    fill="transparent"
                                >
                                    <title>
                                        {{
                                            $signed(
                                                $bin["from"],
                                            )
                                        }} – {{
                                            $signed(
                                                $bin["to"],
                                            )
                                        }}: {{ __("Seen") }} {{
                                            round(
                                                $bin["seen"] * 100,
                                            )
                                        }}%, {{ __("Not seen") }} {{
                                            round(
                                                $bin["not_seen"] * 100,
                                            )
                                        }}%
                                    </title>
                                </rect>
                            @endforeach
                        </svg>
                        <figcaption class="text-xs text-gray-400">
                            {{
                                __(
                                    "How your observations seen and not seen are spread over the contrast reserve the model gives them, each as a share of its own total. Your threshold lies where both are about as frequent: seen and not seen weigh the same, as observers record far fewer of the objects they did not see.",
                                )
                            }}
                        </figcaption>
                        <details class="text-xs text-gray-400">
                            <summary class="cursor-pointer">
                                {{
                                    __(
                                        "Show as a table",
                                    )
                                }}
                            </summary>
                            <table class="mt-2">
                                <thead>
                                    <tr>
                                        <th class="pr-4 text-left">
                                            {{
                                                __(
                                                    "Contrast reserve",
                                                )
                                            }}
                                        </th>
                                        <th class="pr-4 text-right">
                                            {{ __("Seen") }}
                                        </th>
                                        <th class="text-right">
                                            {{ __("Not seen") }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($distributions as $bin)
                                        <tr>
                                            <td class="pr-4">
                                                {{ $signed($bin["from"]) }} – {{ $signed($bin["to"]) }}
                                            </td>
                                            <td class="pr-4 text-right">
                                                {{ round($bin["seen"] * 100) }}%
                                            </td>
                                            <td class="text-right">
                                                {{
                                                    round(
                                                        $bin["not_seen"] * 100,
                                                    )
                                                }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </details>
                    </figure>
                @endif

                @if ($reliable)
                    <x-toggle
                        label="{!! __('Use my own threshold in Tonight\'s best, Next for you and What\'s new for me') !!}"
                        id="useThreshold"
                        wire:model.live="useThreshold"
                    />
                    @if ($useThreshold)
                        <p class="text-gray-400">
                            {{
                                __(
                                    "There, :offset is added to every contrast reserve.",
                                    [
                                        "offset" => $signed($offset),
                                    ],
                                )
                            }}
                        </p>
                    @endif
                @else
                    <p class="text-yellow-400">
                        {{
                            __(
                                "Not reliable enough yet to use. Record more of the objects you looked for but did not see.",
                            )
                        }}
                    </p>
                @endif
            @endif

            @if ($pending && $fit !== null)
                <p class="text-xs text-gray-500">
                    {{
                        __(
                            "Your observations changed: recalculating…",
                        )
                    }}
                </p>
            @endif
        </div>
    </x-slot>
</x-action-section>
