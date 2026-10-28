@props (["observation", "inches" => false])

{{-- A drawing on the comparison page: the drawing whole, with observer, date and equipment, linking to the observation --}}
@php
    $cards = \App\Services\Observations\ObservationCardData::instance();
    $user = $cards->user($observation->observer_username);
    $instrument = $cards->instrument($observation->instrument_id);
    $aperture = \App\Services\Observations\DrawingComparison::aperture(
        $observation->drawing_aperture_mm,
        $inches,
    );
    $magnification = $observation->magnification;
@endphp

<figure class="min-w-0">
    <a href="{{ $observation->detailUrl($user?->slug) }}" class="group block">
        <img
            src="{{ $observation->drawingUrl() }}"
            alt="{{ __("Drawing") }}: {{ $observation->target_name }}"
            loading="lazy"
            class="aspect-square w-full rounded bg-black object-contain"
        />
    </a>
    <figcaption class="mt-1 text-xs text-gray-400">
        <div class="truncate text-sm font-semibold text-white">
            {{ $user?->name ?? $observation->observer_username }}
        </div>
        <div class="truncate">
            {{ $observation->observed_on?->translatedFormat("j M Y") }}
            @if ($magnification)
                &middot; {{ $magnification }}&times;
            @endif
        </div>
        @if ($instrument || $aperture)
            <div class="truncate">
                {{ $instrument ? html_entity_decode($instrument->fullName()) : "" }}
                @if ($aperture)
                    ({{ $aperture }})
                @endif
            </div>
        @endif
    </figcaption>
</figure>
