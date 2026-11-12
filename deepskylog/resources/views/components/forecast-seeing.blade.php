@props (["seeing"])

{{-- The seeing of a night (App\Services\Weather\SeeingForecast::between()) as a badge with the arcseconds, the source in the tooltip --}}
<span
    {{
        $attributes->merge([
            "class" =>
                "rounded px-1.5 text-xs whitespace-nowrap text-white " .
                \App\Services\Weather\SeeingForecast::badgeClass($seeing["class"]),
        ])
    }}
    title="{{ ($seeing["source"] ?? "7timer") === "astrarome" ? __("Seeing :arcsec (AstrArome, AROME model of Météo-France)", ["arcsec" => $seeing["arcsec"]]) : __("Seeing :arcsec (7Timer index :index of 8)", ["arcsec" => $seeing["arcsec"], "index" => $seeing["index"]]) }}"
    >{{
        \App\Services\Weather\SeeingForecast::label(
            $seeing["class"],
        )
    }} &middot; {{ $seeing["arcsec"] }}</span
>
