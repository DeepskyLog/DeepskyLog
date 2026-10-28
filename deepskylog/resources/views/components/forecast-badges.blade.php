@props (["nights", "href" => null])

{{-- The verdicts of the coming nights as small badges (the location table); the details in the tooltip --}}
<a @if ($href) href="{{ $href }}" @endif class="inline-flex gap-px" aria-label="{{ __("Coming nights") }}">
    @foreach ($nights as $night)
        <span
            class="rounded px-1 text-xs text-white {{ \App\Services\Weather\NightForecast::verdictClass($night["verdict"]) }}"
            title="{{ \Carbon\Carbon::parse($night["night"])->locale(app()->getLocale())->isoFormat("ddd D MMM") }}: {{ \App\Services\Weather\NightForecast::summary($night) }}"
        >{{ \Carbon\Carbon::parse($night["night"])->locale(app()->getLocale())->isoFormat("dd") }}</span>
    @endforeach
</a>
