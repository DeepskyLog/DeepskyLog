@props (["observation"])

{{-- A drawing as a small square with the object, observer and date, linking to the observation (the home page) --}}
@php
    $cards = \App\Services\Observations\ObservationCardData::instance();
    $user = $cards->user($observation->observer_username);
    $comet =
        $observation->target_type === \App\Models\Observation::TYPE_COMET &&
        is_numeric($observation->target_key)
            ? $cards->comet($observation->target_key)
            : null;
    $objectName = $comet?->name ?? (string) $observation->target_name;
@endphp

<a
    href="{{ $observation->detailUrl($user?->slug) }}"
    class="group block min-w-0"
>
    <img
        src="{{ $observation->drawingUrl() }}"
        alt="{{ __("Drawing") }}: {{ $objectName }}"
        loading="lazy"
        class="aspect-square w-full rounded bg-black object-cover"
    />
    <div
        class="mt-1 truncate text-sm font-semibold text-white group-hover:underline"
    >
        {{ $objectName }}
    </div>
    <div class="truncate text-xs text-gray-400">
        {{
            $user?->name ??
                $observation->observer_username
        }} &middot; {{
            $observation->observed_on?->translatedFormat(
                "j M Y",
            )
        }}
    </div>
</a>
