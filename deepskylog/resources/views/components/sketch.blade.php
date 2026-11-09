@props (["sketch" => null, "use_selection_date" => false, "unreleased" => false])
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

            // Administrators and database experts share the announcement of the
            // sketch of the week/month, the others the card's own text.
            $shareText = null;
            if ($observation && auth()->user()?->hasModeratorRole()) {
                $shareText = $sketch instanceof \App\Models\SketchOfTheMonth
                    ? \App\Services\SketchShareText::forMonth($observation, \Carbon\Carbon::parse($sketch->date))
                    : \App\Services\SketchShareText::forWeek($observation);
            }
        @endphp

        @if ($unreleased && $sketch_date)
            <div class="mb-1 inline-block rounded bg-amber-600 px-2 py-1 text-xs font-semibold text-white">
                {{ __("Not yet published: shown from :date", ["date" => \Carbon\Carbon::parse($sketch_date)->translatedFormat("j M Y")]) }}
            </div>
        @endif

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
                :share_text="$shareText"
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
                :share_text="$shareText"
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
