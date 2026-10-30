@props (["hours"])

{{-- The cloud cover of the hours of a past night (NightWeather): dark blue clear, grey overcast, as x-forecast-hours --}}
@php
    $shade = function (int $cloud) {
        $f = max(0, min(100, $cloud)) / 100;
        return sprintf("rgb(%d, %d, %d)", 23 + $f * 133, 37 + $f * 126, 84 + $f * 91);
    };
@endphp

<div {{ $attributes->merge(["class" => "flex max-w-md"]) }} role="img" aria-label="{{ __("Cloud cover per hour") }}">
    @foreach ($hours as $hour)
        <div class="flex-1 text-center" title="{{ $hour["time"] }} · {{ \App\Services\Weather\NightWeather::hourSummary($hour) }}">
            <div class="h-5" style="background-color: {{ $shade($hour["cloud"]) }}; margin-right: 1px"></div>
            <div class="text-xs text-gray-500">{{ substr($hour["time"], 0, 2) }}</div>
        </div>
    @endforeach
</div>
