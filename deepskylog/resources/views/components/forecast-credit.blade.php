{{-- The legend of the hourly cloud cover and the credit Open-Meteo's licence (CC BY 4.0) asks for --}}
<p {{ $attributes->merge(["class" => "text-xs text-gray-500"]) }}>
    {{ __("Dark blue: clear, grey: cloudy, yellow line: the Moon is up.") }}
    {!! __("Weather data by :link", ["link" => '<a href="https://open-meteo.com/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">Open-Meteo.com</a>']) !!}
</p>
