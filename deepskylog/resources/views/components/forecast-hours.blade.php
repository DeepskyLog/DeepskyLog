@props (["hours"])

{{-- The cloud cover of the forecast hours of a night: dark blue clear, grey overcast, a yellow line when a bright Moon is up --}}
@php
    $shade = function (int $cloud) {
        $f = max(0, min(100, $cloud)) / 100;
        return sprintf("rgb(%d, %d, %d)", 23 + $f * 133, 37 + $f * 126, 84 + $f * 91);
    };
@endphp

<div {{ $attributes->merge(["class" => "flex"]) }} role="img" aria-label="{{ __("Cloud cover per hour") }}">
    @foreach ($hours as $hour)
        <div
            class="flex-1 text-center"
            title="{{ $hour["time"] }} · {{ __("Clouds :percent% (low :low%, middle :mid%, high :high%)", ["percent" => $hour["cloud"], "low" => $hour["low"], "mid" => $hour["mid"], "high" => $hour["high"]]) }} · {{ __("Humidity :percent%", ["percent" => $hour["humidity"]]) }} · {{ __("Wind :speed km/h", ["speed" => $hour["wind"]]) }}{{ $hour["moon"] ? " · " . __("Moon up") : "" }}"
        >
            <div
                class="h-6"
                style="background-color: {{ $shade($hour["cloud"]) }}; margin-right: 1px; border-top: 3px solid {{ $hour["moon"] ? "#facc15" : "transparent" }}"
            ></div>
            <div class="text-xs text-gray-500">{{ substr($hour["time"], 0, 2) }}</div>
        </div>
    @endforeach
</div>
