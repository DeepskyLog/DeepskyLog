@props (["sketch" => null])
<div>
    <div class="mt-3 max-w-xl pr-3">
        @php
            $observation = $sketch?->observation;
            // For sketch-of-week/month cards, the first link represents the selector (sketch.user).
            $observerName = $sketch?->user?->name ?? $observation?->observer_username;
            $observerUsername =
                $sketch?->user?->username ?? $observation?->observer_username;
            $observerSlug = $sketch?->user?->slug;

            $observationDate = null;
            if (!empty($observation?->observed_on)) {
                $observationDate =
                    $observation->observed_on instanceof \Carbon\Carbon
                        ? $observation->observed_on->format("Y-m-d")
                        : (string) $observation->observed_on;
            } elseif (!empty($sketch?->date)) {
                $observationDate =
                    $sketch->date instanceof \Carbon\Carbon
                        ? $sketch->date->format("Y-m-d")
                        : (string) $sketch->date;
            }

            $isComet =
                $observation?->target_type === "comet" ||
                $observation?->legacy_source === "comet";
            $legacyObservationId = $observation?->legacy_id;
            $drawingId = $observation?->legacy_id;
        @endphp

        @if ($isComet && $legacyObservationId)
            <x-sketch-comet
                :observation_id="$legacyObservationId"
                :drawing_id="$drawingId"
                :unified_observation="$observation"
                :observer_name="$observerName"
                :observer_username="$observerUsername"
                :observer_slug="$observerSlug"
                :observation_date="$observationDate"
            />
        @elseif ($legacyObservationId)
            <x-sketch-deepsky
                :observation_id="$legacyObservationId"
                :drawing_id="$drawingId"
                :unified_observation="$observation"
                :observer_name="$observerName"
                :observer_username="$observerUsername"
                :observer_slug="$observerSlug"
                :observation_date="$observationDate"
            />
        @else
            <div class="text-gray-400">
                {{
                    __(
                        "Observation details unavailable for this sketch.",
                    )
                }}
            </div>
        @endif
    </div>
</div>
