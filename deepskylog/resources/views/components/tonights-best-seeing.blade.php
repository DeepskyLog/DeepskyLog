@props (["best"])

{{-- What the seeing forecast of the night means for a TonightsBest result: on the home page and in the night list wizard --}}
@if ($best["seeing"] ?? null)
    <p {{ $attributes }}>
        @if ($best["seeing"]["class"] === "steady")
            {{
                __(
                    "The air is forecast to be steady (:arcsec): a night for planets, close double stars and small objects at high magnification.",
                    ["arcsec" => $best["seeing"]["arcsec"]],
                )
            }}
            @if ($best["steady_doubles"] ?? 0)
                {{
                    trans_choice(
                        "{1} :count close double star your telescope splits is added to the deep-sky objects, and all planets that are up are on the list.|[2,*] :count close double stars your telescope splits are added to the deep-sky objects, and all planets that are up are on the list.",
                        $best["steady_doubles"],
                    )
                }}
            @endif
        @elseif ($best["seeing"]["class"] === "poor")
            {{
                __(
                    "The air is forecast to be turbulent (:arcsec): planets show little detail and close double stars will not split; large objects at low magnification suffer least.",
                    ["arcsec" => $best["seeing"]["arcsec"]],
                )
            }}
        @else
            {{
                __(
                    "The seeing is forecast to be average (:arcsec).",
                    ["arcsec" => $best["seeing"]["arcsec"]],
                )
            }}
        @endif
        {!!
            __("Seeing by :link", [
                "link" =>
                    '<a href="https://www.7timer.info/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">7Timer!</a>',
            ])
        !!}
    </p>
@endif
