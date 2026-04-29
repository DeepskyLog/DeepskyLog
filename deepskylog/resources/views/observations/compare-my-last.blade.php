<x-app-layout>
    @php
        $current = $currentObservation;
        $previous = $myLastObservation;

        $currentObserverUser = ($preloaded_users ?? collect())->get(
            $current->observerid,
        );
        $myUser = auth()->user();

        $currentDateInt = (string) ($current->date ?? "");
        $currentDate =
            strlen($currentDateInt) === 8
                ? substr($currentDateInt, 0, 4) .
                    "-" .
                    substr($currentDateInt, 4, 2) .
                    "-" .
                    substr($currentDateInt, 6, 2)
                : $currentDateInt;
        $currentTime = !empty($current->time)
            ? sprintf(
                "%02d:%02d",
                intdiv((int) $current->time, 100),
                (int) $current->time % 100,
            )
            : null;

        $previousDate = null;
        $previousTime = null;
        if ($previous) {
            $previousDateInt = (string) ($previous->date ?? "");
            $previousDate =
                strlen($previousDateInt) === 8
                    ? substr($previousDateInt, 0, 4) .
                        "-" .
                        substr($previousDateInt, 4, 2) .
                        "-" .
                        substr($previousDateInt, 6, 2)
                    : $previousDateInt;
            $previousTime = !empty($previous->time)
                ? sprintf(
                    "%02d:%02d",
                    intdiv((int) $previous->time, 100),
                    (int) $previous->time % 100,
                )
                : null;
        }

        $currentLocation = ($preloaded_locations ?? collect())->get(
            $current->locationid,
        );
        $currentInstrument = ($preloaded_instruments ?? collect())->get(
            $current->instrumentid,
        );

        $previousLocation = $previous
            ? ($preloaded_locations ?? collect())->get($previous->locationid)
            : null;
        $previousInstrument = $previous
            ? ($preloaded_instruments ?? collect())->get($previous->instrumentid)
            : null;

        $backUrl = $currentDetailUrl ?? null;
    @endphp

    <div class="max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-center gap-3">
            @if ($backUrl)
                <x-button gray icon="arrow-left" href="{{ $backUrl }}">
                    {{
                        __(
                            "Back to this observation",
                        )
                    }}
                </x-button>
            @endif
            <a
                href="{{ route('object.show', $objectSlug) }}"
                class="text-blue-300 hover:underline text-sm"
            >
                {{
                    __(
                        "Object page",
                    )
                }}
            </a>
        </div>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white">
                {{
                    __("Comparison for :object", [
                        "object" => $objectName,
                    ])
                }}
            </h1>
            <p class="text-sm text-gray-400 mt-1">
                {{
                    __(
                        "Current observation by :observer compared with your latest earlier observation of this object.",
                        [
                            "observer" =>
                                $currentObserverUser?->name ??
                                ($current->observerid ?? __("Unknown observer")),
                        ],
                    )
                }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <section class="rounded-xl bg-gray-800 p-6">
                <h2 class="text-sm uppercase tracking-wide text-gray-400 mb-3">
                    {{
                        __(
                            "Current observation",
                        )
                    }}
                </h2>
                <div class="space-y-2 text-sm text-gray-200">
                    <div>
                        <span class="text-gray-400">{{ __("Observer") }}:</span>
                        <span class="font-semibold">{{
                            $currentObserverUser?->name ??
                                $current->observerid
                        }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">{{ __("Date") }}:</span>
                        <span class="font-semibold">{{
                            $currentDate
                                ? \Carbon\Carbon::parse($currentDate)->translatedFormat("j M Y")
                                : "-"
                        }}</span>
                        @if ($currentTime)
                            <span class="text-gray-400">{{ __("at") }}</span>
                            <span
                                class="font-semibold"
                                >{{ $currentTime }}</span
                            >
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-400">{{ __("Location") }}:</span>
                        <span class="font-semibold">{{
                            $currentLocation?->name
                                ? html_entity_decode($currentLocation->name)
                                : "-"
                        }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400"
                            >{{ __("Instrument") }}:</span
                        >
                        <span class="font-semibold">{!!
                            $currentInstrument?->fullName()
                                ? html_entity_decode($currentInstrument->fullName())
                                : "-"
                        !!}</span>
                    </div>
                </div>

                @if (!empty($current->description))
                    <div class="mt-4 border-t border-gray-700 pt-4">
                        <div
                            class="text-xs uppercase tracking-wide text-gray-400 mb-2"
                        >
                            {{ __("Notes") }}
                        </div>
                        <div
                            class="text-sm leading-relaxed text-gray-200 rounded bg-gray-900 p-3"
                        >
                            {!!
                                html_entity_decode(
                                    $current->description,
                                )
                            !!}
                        </div>
                    </div>
                @endif
            </section>

            <section class="rounded-xl bg-gray-800 p-6">
                <h2 class="text-sm uppercase tracking-wide text-gray-400 mb-3">
                    {{
                        __(
                            "Your last observation",
                        )
                    }}
                </h2>

                @if ($previous)
                    <div class="space-y-2 text-sm text-gray-200">
                        <div>
                            <span class="text-gray-400"
                                >{{ __("Observer") }}:</span
                            >
                            <span class="font-semibold">{{
                                $myUser?->name ??
                                    $previous->observerid
                            }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">{{ __("Date") }}:</span>
                            <span class="font-semibold">{{
                                $previousDate
                                    ? \Carbon\Carbon::parse($previousDate)->translatedFormat("j M Y")
                                    : "-"
                            }}</span>
                            @if ($previousTime)
                                <span
                                    class="text-gray-400"
                                    >{{ __("at") }}</span
                                >
                                <span
                                    class="font-semibold"
                                    >{{ $previousTime }}</span
                                >
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400"
                                >{{ __("Location") }}:</span
                            >
                            <span class="font-semibold">{{
                                $previousLocation?->name
                                    ? html_entity_decode($previousLocation->name)
                                    : "-"
                            }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400"
                                >{{ __("Instrument") }}:</span
                            >
                            <span class="font-semibold">{!!
                                $previousInstrument?->fullName()
                                    ? html_entity_decode($previousInstrument->fullName())
                                    : "-"
                            !!}</span>
                        </div>
                    </div>
                    @if (!empty($previous->description))
                        <div class="mt-4 border-t border-gray-700 pt-4">
                            <div
                                class="text-xs uppercase tracking-wide text-gray-400 mb-2"
                            >
                                {{ __("Notes") }}
                            </div>
                            <div
                                class="text-sm leading-relaxed text-gray-200 rounded bg-gray-900 p-3"
                            >
                                {!!
                                    html_entity_decode(
                                        $previous->description,
                                    )
                                !!}
                            </div>
                        </div>
                    @endif
                    @if ($myLastDetailUrl)
                        <div class="mt-4">
                            <a
                                href="{{ $myLastDetailUrl }}"
                                class="text-blue-300 hover:underline text-sm"
                            >
                                {{
                                    __(
                                        "Open my compared observation",
                                    )
                                }}
                            </a>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-300">{{
                        __(
                            "You have no earlier observation of this object to compare yet.",
                        )
                    }}</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
