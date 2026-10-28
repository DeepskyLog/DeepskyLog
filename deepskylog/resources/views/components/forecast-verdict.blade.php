@props (["night"])

{{-- The verdict of the forecast of a night (App\Services\Weather\NightForecast) as a badge --}}
<span {{ $attributes->merge(["class" => "rounded px-1.5 text-xs whitespace-nowrap text-white " . \App\Services\Weather\NightForecast::verdictClass($night["verdict"])]) }}>{{ \App\Services\Weather\NightForecast::verdictLabel($night["verdict"]) }}</span>
