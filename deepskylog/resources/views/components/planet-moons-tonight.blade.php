@props (["night", "events"])

{{-- The events of the moons of Jupiter and Saturn still to come tonight on the home page, with a link to the page of the night --}}
@php
    $clock = fn(int $timestamp) => \Carbon\Carbon::createFromTimestamp(
        $timestamp,
        $night["timezone"],
    )->format("H:i");
@endphp
<x-home-section :title="__('Jupiter and Saturn tonight')" {{ $attributes }}>
    <ul class="space-y-1 px-2 text-sm text-gray-300">
        @foreach ($events as $event)
            <li>
                <span class="text-gray-400">{{
                    $clock(
                        $event["at"],
                    )
                }}</span>
                {{
                    \App\Services\Planets\PlanetMoonEvents::label(
                        $event,
                    )
                }}
            </li>
        @endforeach
    </ul>
    <x-slot name="footer">
        <a
            href="{{ route("planet-moons.show") }}"
            class="text-blue-400 hover:underline"
            >{{
                __(
                    "The moons of Jupiter and Saturn",
                )
            }} &rarr;</a
        >
    </x-slot>
</x-home-section>
