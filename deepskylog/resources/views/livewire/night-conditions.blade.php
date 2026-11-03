{{-- The weather of the night of an observation or of the nights of a session (App\Livewire\NightConditions) --}}
<div>
    @if ($observation)
        @php($night = $observation["night"])
        <div class="border-t border-gray-700 pt-5">
            <dt class="text-xs uppercase tracking-wide text-gray-400">{{ __("Conditions that night") }}</dt>
            <dd class="mt-1 space-y-1 text-sm">
                @if ($observation["at"])
                    <div class="font-semibold text-gray-100">
                        {{ __("At :time", ["time" => $observation["at"]["time"]]) }}:
                        {{ \App\Services\Weather\NightWeather::hourSummary($observation["at"]) }}
                    </div>
                @endif
                <div class="text-gray-300">
                    @php($span = ["from" => $night["from"], "until" => $night["until"]])
                    {{ $night["kind"] === "none" ? __("Night :from–:until", $span) : __("Dark hours :from–:until", $span) }}:
                    {{ \App\Services\Weather\NightWeather::summary($night) }}
                </div>
                <x-night-weather-hours :hours="$night['hours']" class="pt-1" />
                <p class="text-xs text-gray-500">
                    {{ __("From the weather model for an area of about 25 km, not measured at the site.") }}
                    {!! __("Weather data by :link", ["link" => '<a href="https://open-meteo.com/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">Open-Meteo.com</a>']) !!}
                </p>
            </dd>
        </div>
    @elseif ($nights)
        <div class="space-y-2">
            @foreach ($nights as $night)
                <div>
                    <div class="text-gray-300">
                        @if (count($nights) > 1)
                            {{ \Carbon\Carbon::parse($night["night"])->translatedFormat("j M") }},
                        @endif
                        {{ $night["from"] }}–{{ $night["until"] }}:
                        {{ \App\Services\Weather\NightWeather::summary($night) }}
                    </div>
                    <x-night-weather-hours :hours="$night['hours']" class="pt-1" />
                </div>
            @endforeach
            <p class="text-xs text-gray-500">
                {{ __("Not written by the observer: the weather model for an area of about 25 km.") }}
                {!! __("Weather data by :link", ["link" => '<a href="https://open-meteo.com/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">Open-Meteo.com</a>']) !!}
            </p>
        </div>
    @endif
</div>
