@props (["objectName"])

{{-- The smallest aperture and the brightest sky with which a deep-sky object was reported seen (object page) --}}
@php
    $records = [];
    try {
        $records = array_filter(
            app(\App\Services\Observations\ApertureRecords::class)->forObject(
                $objectName,
            ),
        );
    } catch (\Throwable $_) {
        $records = [];
    }
    $viewer = auth()->user();
    $inches = (bool) ($viewer?->showInches ?? false);
    $cards = \App\Services\Observations\ObservationCardData::instance();
    if (!empty($records)) {
        $cards->preload(array_values($records));
    }
    $standard = $viewer?->standardInstrument;
    $smallest = $records["aperture"] ?? null;
@endphp

@if (!empty($records))
    <div class="mt-6">
        <div class="flex flex-wrap items-baseline gap-3">
            <h2 class="text-xl font-semibold text-white">
                {{ __("Records") }}
            </h2>
            <a
                href="{{ route("records.aperture") }}"
                class="text-sm text-indigo-400 hover:text-indigo-300"
            >
                {{
                    __(
                        "Faintest objects seen with small apertures",
                    )
                }}
            </a>
        </div>
        <p class="mt-1 text-sm text-gray-400">
            {{
                __(
                    "The smallest aperture and the brightest sky with which observers reported this object seen. Observations the suspect-observation check doubts are left out.",
                )
            }}
        </p>
        <dl class="mt-2 grid gap-3 sm:grid-cols-3">
            @foreach ($records as $kind => $observation)
                @php
                    $user = $cards->user($observation->observer_username);
                    $instrument = $cards->instrument($observation->instrument_id);
                    $aperture = \App\Services\Observations\ApertureRecords::apertureLabel(
                        $observation->record_aperture_mm,
                        (bool) $observation->record_naked_eye,
                        $inches,
                    );
                @endphp

                <div class="rounded bg-gray-800 p-3">
                    <dt class="text-sm text-gray-400">
                        {{
                            match ($kind) {
                                "aperture" => __("Smallest aperture"),
                                "sqm" => __("Brightest sky (SQM)"),
                                default => __("Brightest sky (NELM)"),
                            }
                        }}
                    </dt>
                    <dd class="mt-1">
                        <a
                            href="{{ $observation->detailUrl($user?->slug) }}"
                            class="text-lg font-semibold text-white hover:text-indigo-300"
                        >
                            @if ($kind === "aperture")
                                {{ $aperture }}
                            @elseif ($kind === "sqm")
                                {{
                                    number_format(
                                        (float) $observation->record_sqm,
                                        2,
                                    )
                                }}
                            @else
                                {{
                                    number_format(
                                        (float) $observation->record_nelm,
                                        1,
                                    )
                                }}
                            @endif
                        </a>
                        @if ($kind !== "aperture" && $observation->record_sky_from_location)
                            <span class="text-xs text-gray-400"
                                >({{ __("of the location") }})</span
                            >
                        @endif
                        <div class="text-sm text-gray-300">
                            {{
                                $user?->name ??
                                    $observation->observer_username
                            }} &middot; {{
                                $observation->observed_on?->translatedFormat(
                                    "j M Y",
                                )
                            }}
                        </div>
                        @if ($instrument)
                            <div class="truncate text-xs text-gray-400">
                                {{
                                    html_entity_decode(
                                        $instrument->fullName(),
                                    )
                                }}
                                @if ($kind !== "aperture" && $aperture)
                                    ({{ $aperture }})
                                @endif
                            </div>
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
        @if ($smallest && $standard && (float) $standard->aperture_mm > 0)
            <p class="mt-2 text-sm text-gray-300">
                @if ((float) $standard->aperture_mm >= (float) $smallest->record_aperture_mm)
                    {{
                        __(
                            "Your :instrument (:aperture) is at least as large as the smallest aperture with which this object was seen.",
                            [
                                "instrument" => html_entity_decode($standard->fullName()),
                                "aperture" => \App\Services\Observations\DrawingComparison::aperture(
                                    $standard->aperture_mm,
                                    $inches,
                                ),
                            ],
                        )
                    }}
                @else
                    {{
                        __(
                            "Nobody reported this object seen with an aperture as small as your :instrument (:aperture) yet.",
                            [
                                "instrument" => html_entity_decode($standard->fullName()),
                                "aperture" => \App\Services\Observations\DrawingComparison::aperture(
                                    $standard->aperture_mm,
                                    $inches,
                                ),
                            ],
                        )
                    }}
                @endif
            </p>
        @endif
    </div>
@endif
