@props (["parties"])

{{-- The starparties (#315) of the coming weeks on the home page --}}
<x-home-section :title="__('Starparties')" {{ $attributes }}>
    <ul class="divide-y divide-gray-700 px-2">
        @foreach ($parties as $party)
            <li class="py-2">
                <a href="{{ route("star-parties.show", $party) }}" class="text-sm font-semibold text-white hover:underline">{{ html_entity_decode($party->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</a>
                @if ($party->isCancelled())
                    <span class="ml-1 rounded bg-red-700 px-1.5 text-xs text-white">{{ __("Cancelled") }}</span>
                @elseif ($party->isOngoing())
                    <span class="ml-1 rounded bg-green-700 px-1.5 text-xs text-white">{{ __("Now") }}</span>
                @endif
                <p class="text-sm text-gray-400">
                    {{ $party->dateRange() }} &middot; {{ $party->location_name }}@if ($party->country) ({{ $party->country }})@endif
                    &middot; {{ trans_choice("{0} Nobody has said they come yet.|{1} One observer comes.|[2,*] :count observers come.", $party->goingCount(), ["count" => $party->goingCount()]) }}
                </p>
            </li>
        @endforeach
    </ul>
    <x-slot name="footer">
        <a href="{{ route("star-parties.index") }}" class="text-blue-400 hover:underline">{{ __("All starparties") }} &rarr;</a>
        @auth
            <a href="{{ route("star-parties.create") }}" class="text-blue-400 hover:underline">{{ __("Organise a starparty") }}</a>
        @endauth
    </x-slot>
</x-home-section>
