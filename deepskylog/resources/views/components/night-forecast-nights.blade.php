@props (["nights", "listLinks" => true])

{{--
    The cloud forecast of nights (App\Services\Weather\NightForecast): per night the verdict, the dark hours,
    the Moon, the clouds and the hourly cloud cover. With listLinks, a clear night links to the night list
    wizard, which uses the observer's standard location.
--}}
<div {{ $attributes->merge(["class" => "space-y-3 text-sm text-gray-400"]) }}>
    @foreach ($nights as $night)
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-white">{{ \Carbon\Carbon::parse($night["night"])->locale(app()->getLocale())->isoFormat("ddd D MMM") }}</span>
                <x-forecast-verdict :night="$night" />
                @if ($listLinks && in_array($night["verdict"], ["clear_dark", "clear_moon"], true))
                    <a href="{{ route("observing-list.night", ["night" => $night["night"]]) }}" class="text-xs text-blue-400 hover:underline">{{ __("Make a list for this night") }}</a>
                @endif
            </div>
            <p>
                @if ($night["dark"])
                    {{
                        $night["dark"]["twilight"] === "astronomical"
                            ? __("Dark :from–:until", $night["dark"])
                            : __("Nautical twilight :from–:until", $night["dark"])
                    }}
                    &middot;
                    {{
                        $night["moon"]
                            ? __("Moon :fraction%, up :from–:until", $night["moon"])
                            : __("No Moon")
                    }}
                @endif
                @if ($night["cloud"] !== null)
                    &middot; {{ __("Clouds :percent%", ["percent" => $night["cloud"]]) }}
                @endif
                @if ($night["clear_dark"] && $night["clear_dark"]["minutes"] >= 60)
                    &middot; {{ __("Clear and dark :from–:until", $night["clear_dark"]) }}
                @elseif ($night["clear"] && $night["clear"]["minutes"] >= 60)
                    &middot; {{ __("Clear :from–:until", $night["clear"]) }}
                @endif
                @if ($night["dew"])
                    &middot; {{ __("Risk of dew") }}
                @endif
            </p>

            @if ($night["hours"] !== [])
                <x-forecast-hours :hours="$night['hours']" class="mt-1" />
            @endif
        </div>
    @endforeach

    <x-forecast-credit />
</div>
