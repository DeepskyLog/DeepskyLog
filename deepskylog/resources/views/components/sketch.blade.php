@props (["sketch" => null, "use_selection_date" => false])
<div>
    <div class="mt-3 max-w-xl pr-3">
        @php
            $observation = $sketch?->observation;
            // For sketch-of-week/month cards, the first link represents the selector (sketch.user).
            $observerName = $sketch?->user?->name ?? $observation?->observer_username;
            $observerUsername =
                $sketch?->user?->username ?? $observation?->observer_username;
            $observerSlug = $sketch?->user?->slug;

            // Dates: selection date (sketch->date) and original observation date
            $sketch_date = null;
            if (!empty($sketch?->date)) {
                $sketch_date =
                    $sketch->date instanceof \Carbon\Carbon
                        ? $sketch->date->format("Y-m-d")
                        : (string) $sketch->date;
            }

            $observationDate = null;
            if (!empty($observation?->observed_on)) {
                $observationDate =
                    $observation->observed_on instanceof \Carbon\Carbon
                        ? $observation->observed_on->format("Y-m-d")
                        : (string) $observation->observed_on;
            }

            $isComet = $observation?->target_type === \App\Models\Observation::TYPE_COMET;
            $legacyObservationId = $observation?->legacy_id;
            $drawingId = $observation?->drawing_filename
                ?? ($observation?->legacy_id
                    ? ($isComet ? 'cometdrawings/' : 'drawings/') . $observation->legacy_id . '.jpg'
                    : null);
        @endphp

        @if ($isComet && ($legacyObservationId || $observation))
            <x-sketch-comet
                :observation_id="$legacyObservationId"
                :drawing_id="$drawingId"
                :unified_observation="$observation"
                :observer_name="$observerName"
                :observer_username="$observerUsername"
                :observer_slug="$observerSlug"
                :observation_date="$observationDate"
                :sketch_date="$sketch_date"
                :use_sketch_date="$use_selection_date"
            />
        @elseif ($legacyObservationId || $observation)
            <x-sketch-deepsky
                :observation_id="$legacyObservationId"
                :drawing_id="$drawingId"
                :unified_observation="$observation"
                :observer_name="$observerName"
                :observer_username="$observerUsername"
                :observer_slug="$observerSlug"
                :observation_date="$observationDate"
                :sketch_date="$sketch_date"
                :use_sketch_date="$use_selection_date"
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
