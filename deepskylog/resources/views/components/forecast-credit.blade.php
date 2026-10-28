{{-- The legend of the hourly cloud cover, the credit Open-Meteo's licence (CC BY 4.0) asks for, and 7Timer's for the seeing --}}
<p {{ $attributes->merge(["class" => "text-xs text-gray-500"]) }}>
    {{ __("Dark blue: clear, grey: cloudy, yellow line: the Moon is up.") }}
    {!! __("Weather data by :link", ["link" => '<a href="https://open-meteo.com/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">Open-Meteo.com</a>']) !!}
    @if (\App\Services\Weather\SeeingForecast::enabled())
        {!! __("Seeing by :link", ["link" => '<a href="https://www.7timer.info/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">7Timer!</a>']) !!}
    @endif
</p>
