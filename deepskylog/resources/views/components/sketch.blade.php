@props(["sketch" => null])
<div>
    <div class="mt-3 max-w-xl pr-3">
        {{-- Show the correct drawing --}}
        @php
            if ($sketch->observation_id < 0) {
                $sketch_id = -$sketch->observation_id;
            } else {
                $sketch_id = $sketch->observation_id;
            }
        @endphp

        @if ($sketch->observation_id < 0)
            <x-sketch-comet
                :observation_id="-$sketch->observation_id"
                :observer_name="$sketch->user->name"
                :observer_username="$sketch->user->username"
                :observation_date="$sketch->date"
            />
        @else
            <x-sketch-deepsky
                :observation_id="$sketch->observation_id"
                :observer_name="$sketch->user->name"
                :observer_username="$sketch->user->username"
                :observation_date="$sketch->date"
            />
        @endif
    </div>
</div>
