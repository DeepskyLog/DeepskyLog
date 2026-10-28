@props (["moon", "features"])

{{-- The Moon tonight on the home page: its phase and colongitude and the lunar features at the terminator, with a link to the Moon page --}}
@php
    $clock = fn(int $timestamp) => \Carbon\Carbon::createFromTimestamp($timestamp, $moon["timezone"])->format("H:i");
@endphp
<x-home-section :title="__('The Moon tonight')" {{ $attributes }}>
    <div class="flex gap-4 px-2 text-sm">
        <div class="w-24 flex-shrink-0">
            {!! (new \App\Services\Moon\MoonDiskRenderer($moon["ephemeris"]))->svg() !!}
        </div>
        <div>
            <p class="text-gray-300">
                {{ \App\Services\Moon\LunarTerminator::phaseLabel($moon["phase"]) }},
                {{ round($moon["ephemeris"]["fraction"] * 100) }}%,
                {{ __("colongitude :colongitude°", ["colongitude" => number_format($moon["ephemeris"]["colongitude"], 1)]) }}.
                {{ __("Up :from – :until.", ["from" => $clock($moon["up_from"]), "until" => $clock($moon["up_until"])]) }}
            </p>
            <p class="mt-1 text-gray-300">
                <span class="text-gray-400">{{ __("At the terminator:") }}</span>
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
        </div>
    </div>
    <x-slot name="footer">
        <a href="{{ route("moon.show") }}" class="text-blue-400 hover:underline">{{ __("The Moon and its terminator") }} &rarr;</a>
    </x-slot>
</x-home-section>
