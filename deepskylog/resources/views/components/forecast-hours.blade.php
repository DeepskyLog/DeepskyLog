@props (["hours"])

{{--
    The cloud cover of the forecast hours of a night: dark blue clear, grey overcast, a yellow line when a bright Moon
    is up, and under the hour a dark blue line when the dew heater is needed, a light blue one when dew forms
--}}
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
            title="{{ $hour["time"] }} · {{ __("Clouds :percent% (low :low%, middle :mid%, high :high%)", ["percent" => $hour["cloud"], "low" => $hour["low"], "mid" => $hour["mid"], "high" => $hour["high"]]) }} · {{ ($hour["temperature"] ?? null) !== null ? __(":temperature °C", ["temperature" => $hour["temperature"]]) . " · " : "" }}{{ __("Humidity :percent%", ["percent" => $hour["humidity"]]) }}{{ ($hour["dew_point"] ?? null) !== null ? " · " . __("Dew point :temperature °C", ["temperature" => $hour["dew_point"]]) : "" }}{{ ($hour["dew"] ?? false) ? " · " . __("Dew") : (($hour["heater"] ?? false) ? " · " . __("Dew heater needed") : "") }} · {{ __("Wind :speed km/h", ["speed" => $hour["wind"]]) }}{{ ($hour["seeing"] ?? null) ? " · " . __("Seeing :arcsec", ["arcsec" => \App\Services\Weather\SeeingForecast::arcsec($hour["seeing"])]) : "" }}{{ $hour["moon"] ? " · " . __("Moon up") : "" }}{{ ($hour["twilight"] ?? false) ? " · " . __("Twilight") : "" }}"
        >
            <div
                class="h-6"
                style="background-color: {{ $shade($hour["cloud"]) }}; margin-right: 1px; border-top: 3px solid {{ $hour["moon"] ? "#facc15" : "transparent" }}"
            ></div>
            <div
                class="text-xs text-gray-500"
                style="margin-right: 1px; border-top: 2px solid {{ ($hour["dew"] ?? false) ? "#38bdf8" : (($hour["heater"] ?? false) ? "#0369a1" : "transparent") }}"
            >
                {{ substr($hour["time"], 0, 2) }}
            </div>
        </div>
    @endforeach
</div>
