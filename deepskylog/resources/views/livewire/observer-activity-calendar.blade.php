<div>
    @if ($calendar !== null)
        @php
            $cell = 11;
            $step = 14;
            $left = 30;
            $top = 16;
            $width = $left + count($calendar["weeks"]) * $step;
            $height = $top + 7 * $step;
            $colours = ["#1f2937", "#0e4429", "#006d32", "#26a641", "#39d353"];
            $locale = app()->getLocale();
            $monday = \Carbon\Carbon::create(2024, 1, 1)->locale($locale);
        @endphp

        <div class="mb-6">
            <x-card>
                <div
                    class="flex flex-wrap items-center justify-between gap-2 px-5"
                >
                    <h2 class="mt-2 mb-2 text-xl font-bold">
                        {{
                            __(
                                "Observing activity",
                            )
                        }}
                    </h2>
                    @if (count($years) > 1)
                        <select
                            wire:model.live="year"
                            aria-label="{{ __("Year") }}"
                            class="rounded border-gray-600 bg-gray-800 py-1 pr-8 pl-2 text-sm text-gray-200"
                        >
                            @foreach ($years as $option)
                                <option value="{{ $option }}">
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="px-5 pb-3">
                    <p class="mb-2 text-sm text-gray-400">
                        {{ $year }}: {{
                            trans_choice(
                                ":count night|:count nights",
                                $calendar["nights"],
                                ["count" => $calendar["nights"]],
                            )
                        }}, {{
                            trans_choice(
                                ":count observation|:count observations",
                                $calendar["observations"],
                                ["count" => $calendar["observations"]],
                            )
                        }}
                    </p>

                    <div
                        class="max-w-full overflow-x-auto"
                        wire:loading.class="opacity-50"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="{{ $width }}"
                            height="{{ $height }}"
                            viewBox="0 0 {{ $width }} {{ $height }}"
                            role="img"
                            aria-label="{{ __("Observing activity") }} {{ $year }}"
                            class="text-gray-400"
                        >
                            @foreach ($calendar["months"] as $month)
                                <text
                                    x="{{ $left + $month["week"] * $step }}"
                                    y="10"
                                    font-size="10"
                                    fill="currentColor"
                                >
                                    {{
                                        \Carbon\Carbon::create(
                                            $year,
                                            $month["month"],
                                            1,
                                        )
                                            ->locale($locale)
                                            ->isoFormat("MMM")
                                    }}
                                </text>
                            @endforeach

                            @foreach ([0, 2, 4] as $weekday)
                                <text
                                    x="0"
                                    y="{{ $top + $weekday * $step + $cell - 1 }}"
                                    font-size="10"
                                    fill="currentColor"
                                >
                                    {{
                                        $monday
                                            ->copy()
                                            ->addDays($weekday)
                                            ->isoFormat("ddd")
                                    }}
                                </text>
                            @endforeach

                            @foreach ($calendar["weeks"] as $w => $week)
                                @foreach ($week as $d => $day)
                                    @continue ($day === null)
                                    @php
                                        $x = $left + $w * $step;
                                        $y = $top + $d * $step;
                                        $label = \Carbon\Carbon::parse($day["date"])->locale($locale)->isoFormat("LL");
                                        if ($day["level"] > 0) {
                                            $label .=
                                                ": " .
                                                trans_choice(":count object|:count objects", $day["objects"], [
                                                    "count" => $day["objects"],
                                                ]) .
                                                ", " .
                                                trans_choice(
                                                    ":count observation|:count observations",
                                                    $day["observations"],
                                                    ["count" => $day["observations"]],
                                                );
                                        }
                                        if ($day["new_moon"]) {
                                            $label .= " (" . __("New moon") . ")";
                                        }
                                    @endphp

                                    @if ($day["level"] > 0)
                                        <a
                                            href="{{ $activity->blogUrl($day["date"]) }}"
                                        >

                                    @endif
                                    <rect
                                        x="{{ $x }}"
                                        y="{{ $y }}"
                                        width="{{ $cell }}"
                                        height="{{ $cell }}"
                                        rx="2"
                                        fill="{{ $colours[$day["level"]] }}"
                                    >
                                        <title>{{ $label }}</title>
                                    </rect>
                                    @if ($day["new_moon"])
                                        <circle
                                            cx="{{ $x + $cell / 2 }}"
                                            cy="{{ $y + $cell / 2 }}"
                                            r="3"
                                            fill="none"
                                            stroke="#e5e7eb"
                                            stroke-width="1.2"
                                            pointer-events="none"
                                        />
                                    @endif
                                    @if ($day["level"] > 0)
                                        </a>
                                    @endif
                                @endforeach
                            @endforeach
                        </svg>
                    </div>

                    <div
                        class="mt-2 flex flex-wrap items-center gap-x-6 gap-y-1 text-xs text-gray-400"
                    >
                        <span class="flex items-center gap-1">
                            {{
                                __(
                                    "Fewer objects",
                                )
                            }}
                            @foreach ($colours as $level => $colour)
                                <span
                                    class="inline-block rounded-sm"
                                    style="width: 11px; height: 11px; background-color: {{ $colour }}"
                                    title="{{ $level === 0 ? __("No observations") : ($level === 1 ? "< " . \App\Services\ObserverActivity::LEVELS[0] : "≥ " . \App\Services\ObserverActivity::LEVELS[$level - 2]) }}"
                                ></span>
                            @endforeach
                            {{
                                __(
                                    "More objects",
                                )
                            }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg width="11" height="11" viewBox="0 0 11 11" aria-hidden="true">
                                <rect
                                    width="11"
                                    height="11"
                                    rx="2"
                                    fill="{{ $colours[0] }}"
                                />
                                <circle cx="5.5" cy="5.5" r="3" fill="none" stroke="#e5e7eb" stroke-width="1.2" />
                            </svg>
                            {{ __("New moon") }}
                        </span>
                        <span>{{
                            __(
                                "Click a night to read it in the blog.",
                            )
                        }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    @endif
</div>
