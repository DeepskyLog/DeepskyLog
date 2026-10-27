{{-- An observing challenge in the list of /challenges --}}
@php
    $format = fn($date) => $date->copy()->locale(app()->getLocale())->isoFormat("LL");
@endphp
<div class="rounded-lg bg-gray-800 p-4">
    <div class="flex flex-wrap items-baseline justify-between gap-x-3">
        <h3 class="text-lg font-semibold text-white">
            <a href="{{ route("challenges.show", $challenge) }}" class="hover:underline">{{ $challenge->name }}</a>
        </h3>
        <span class="text-sm text-gray-400">{{ $format($challenge->starts_on) }} &ndash; {{ $format($challenge->ends_on) }}</span>
    </div>
    <p class="text-sm text-gray-300">
        {{ $challenge->task() }}
        &middot;
        {{ trans_choice(":count participant|:count participants", $challenge->goals_count, ["count" => $challenge->goals_count]) }}
    </p>
</div>
