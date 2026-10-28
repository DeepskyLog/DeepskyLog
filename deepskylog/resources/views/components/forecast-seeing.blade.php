@props (["seeing"])

{{-- The seeing of a night (App\Services\Weather\SeeingForecast::between()) as a badge, the arcseconds in the tooltip --}}
<span
    {{
        $attributes->merge([
            "class" =>
                "rounded px-1.5 text-xs whitespace-nowrap text-white " .
                \App\Services\Weather\SeeingForecast::badgeClass($seeing["class"]),
        ])
    }}
    title="{{ __("Seeing :arcsec (7Timer index :index of 8)", ["arcsec" => $seeing["arcsec"], "index" => $seeing["index"]]) }}"
    >{{
        \App\Services\Weather\SeeingForecast::label(
            $seeing["class"],
        )
    }}</span
>
