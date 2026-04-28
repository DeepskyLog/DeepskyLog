@props (["sketch" => null])
<div>
    <div class="mt-3 max-w-xl pr-3">
        @php
            $observation = $sketch?->observation;
            $isComet =
                $observation?->target_type === "comet" ||
                $observation?->legacy_source === "comet";
            $legacyObservationId = $observation?->legacy_id;
        @endphp

        @if ($isComet && $legacyObservationId)
            <x-sketch-comet
                :observation_id="$legacyObservationId"
                :observer_name="$sketch->user->name"
                :observer_username="$sketch->user->username"
                :observation_date="$sketch->date"
            />
        @elseif ($legacyObservationId)
            <x-sketch-deepsky
                :observation_id="$legacyObservationId"
                :observer_name="$sketch->user->name"
                :observer_username="$sketch->user->username"
                :observation_date="$sketch->date"
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
