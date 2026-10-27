@props (["trophy"])

{{-- A trophy of the gallery on the observer page: an observing challenge whose target the observer reached, gold for a winner --}}
@php
    $challenge = $trophy["challenge"];
    $date = $trophy["reached_on"]->copy()->locale(app()->getLocale())->isoFormat("LL");
    $title = $trophy["winner"]
        ? __("Winner of :challenge", ["challenge" => $challenge->name])
        : __(":challenge: target reached on :date", ["challenge" => $challenge->name, "date" => $date]);
@endphp
<a
    href="{{ route("challenges.show", $challenge) }}"
    title="{{ $title }}"
    class="flex h-28 w-24 flex-col items-center justify-start rounded-lg bg-white/5 p-2 shadow hover:bg-white/10"
>
    <div class="flex w-full flex-1 items-center justify-center">
        <svg viewBox="0 0 64 64" class="{{ $trophy["winner"] ? "text-yellow-400" : "text-gray-300" }} h-16 w-16" fill="currentColor" aria-hidden="true">
            <path d="M18 6h28v4h10v6c0 8-5.5 14-13.2 15.3A14 14 0 0 1 35 39.6V48h7a3 3 0 0 1 3 3v3H19v-3a3 3 0 0 1 3-3h7v-8.4a14 14 0 0 1-7.8-8.3C13.5 30 8 24 8 16v-6h10V6Zm-6 8v2c0 5.2 3 9.3 7.4 10.8A14 14 0 0 1 18 21v-7h-6Zm40 0h-6v7c0 2-.5 4-1.4 5.8C49 25.3 52 21.2 52 16v-2Z" />
            <rect x="17" y="56" width="30" height="4" rx="1" />
        </svg>
    </div>
    <div class="two-line-truncate mt-1 w-full text-center text-xs leading-tight text-gray-300">{{ $challenge->name }}</div>
</a>
