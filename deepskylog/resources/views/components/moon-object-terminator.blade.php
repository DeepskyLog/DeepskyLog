{{-- On the page of the Moon: its phase tonight and the lunar features at the terminator, with a link to the Moon page --}}
@php
    $location = \App\Models\Location::forEphemerides(
        \Illuminate\Support\Facades\Auth::user(),
    );
    if ($location && $location->hasValidCoordinates()) {
        $timezone = \App\Support\EphemerisDate::timezone($location);
        $moon = \App\Services\Moon\LunarTerminator::nightAt(
            $location,
            \App\Support\EphemerisDate::current($timezone),
            $timezone,
        );
        $features =
            $moon["best"] !== null
                ? \App\Services\Moon\LunarTerminator::features(
                    $moon["ephemeris"],
                    \App\Services\Moon\LunarTerminator::HOMEPAGE_FEATURES,
                )
                : [];
        $clock = fn(int $timestamp) => \Carbon\Carbon::createFromTimestamp(
            $timestamp,
            $timezone,
        )->format("H:i");
    }
@endphp
<div class="mt-6">
    <h2 class="text-xl font-semibold text-white">
        {{
            __(
                "At the terminator",
            )
        }}
    </h2>
    @isset ($moon)
        <div class="mt-2 flex gap-4 text-sm">
            <div class="w-32 flex-shrink-0">
                {!!
                    (new \App\Services\Moon\MoonDiskRenderer(
                        $moon["ephemeris"],
                    ))->svg()
                !!}
            </div>
            <div>
                <p class="text-gray-300">
                    {{
                        \App\Services\Moon\LunarTerminator::phaseLabel(
                            \App\Services\Moon\LunarTerminator::phase($moon["ephemeris"]),
                        )
                    }}, {{
                        round(
                            $moon["ephemeris"]["fraction"] * 100,
                        )
                    }}%, {{
                        __("colongitude :colongitude°", [
                            "colongitude" => number_format($moon["ephemeris"]["colongitude"], 1),
                        ])
                    }}.
                    @if ($moon["best"] !== null)
                        {{
                            __("Up :from – :until.", [
                                "from" => $clock($moon["up_from"]),
                                "until" => $clock($moon["up_until"]),
                            ])
                        }}
                    @endif
                </p>
                @if ($features !== [])
                    <p class="mt-1 text-gray-300">
                        <span class="text-gray-400">{{
                            __(
                                "At the terminator:",
                            )
                        }}</span>
                        {{-- The names joined by commas: on separate lines Blade would put a space before each comma --}}
                        {!!
                            collect($features)
                                ->map(
                                    fn(array $feature) => '<a href="' .
                                        e(route("object.show", $feature["slug"] ?: $feature["name"])) .
                                        '" class="text-blue-400 hover:underline">' .
                                        e($feature["name"]) .
                                        "</a>",
                                )
                                ->implode(", ")
                        !!}
                    </p>
                @endif
                <p class="mt-2">
                    <a
                        href="{{ route("moon.show") }}"
                        class="text-blue-400 hover:underline"
                        >{{
                            __(
                                "The Moon and its terminator",
                            )
                        }} &rarr;</a
                    >
                </p>
            </div>
        </div>
    @else
        <p class="mt-2 text-sm">
            <a
                href="{{ route("moon.show") }}"
                class="text-blue-400 hover:underline"
                >{{
                    __(
                        "The Moon and its terminator",
                    )
                }} &rarr;</a
            >
        </p>
    @endisset
</div>
