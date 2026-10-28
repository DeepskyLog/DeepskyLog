{{-- A starparty in a list: name, nights, site, organiser and the number of observers who come --}}
@php
    $status = $party->statusOf(Auth::user());
@endphp
<li class="py-3">
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <a href="{{ route("star-parties.show", $party) }}" class="font-semibold text-white hover:underline">{{ html_entity_decode($party->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</a>
        <span class="text-xs">
            @if ($party->isCancelled())
                <span class="rounded bg-red-700 px-2 py-0.5 text-white">{{ __("Cancelled") }}</span>
            @elseif ($party->isOngoing())
                <span class="rounded bg-green-700 px-2 py-0.5 text-white">{{ __("Now") }}</span>
            @endif
            @if (! $party->isPublic())
                <span class="rounded bg-gray-700 px-2 py-0.5 text-gray-200">{{ __("Private") }}</span>
            @endif
            @if ((int) $party->user_id === (int) Auth::id())
                <span class="rounded bg-blue-800 px-2 py-0.5 text-white">{{ __("Organiser") }}</span>
            @elseif ($status === \App\Models\StarPartyParticipant::GOING)
                <span class="rounded bg-green-800 px-2 py-0.5 text-white">{{ __("Coming") }}</span>
            @elseif ($status === \App\Models\StarPartyParticipant::MAYBE)
                <span class="rounded bg-blue-800 px-2 py-0.5 text-white">{{ __("Maybe") }}</span>
            @elseif ($status === \App\Models\StarPartyParticipant::INVITED)
                <span class="rounded bg-yellow-700 px-2 py-0.5 text-white">{{ __("Invited") }}</span>
            @endif
        </span>
    </div>
    <p class="text-sm text-gray-300">
        {{ $party->dateRange() }} &middot; {{ $party->location_name }}@if ($party->country) ({{ $party->country }})@endif
    </p>
    <p class="text-xs text-gray-400">
        @if ($party->organiser)
            {{ __("Organised by :name", ["name" => html_entity_decode($party->organiser->name, ENT_QUOTES | ENT_HTML5, "UTF-8")]) }} &middot;
        @endif
        {{ trans_choice("{0} Nobody has said they come yet.|{1} One observer comes.|[2,*] :count observers come.", $party->goingCount(), ["count" => $party->goingCount()]) }}
    </p>
</li>
