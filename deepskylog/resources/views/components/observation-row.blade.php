@props (["observation"])

{{--
    An observation on one line, for short lists (the home page): object,
    class, observer, date, instrument and the start of the description. The
    page preloads the observations with ObservationCardData::preload().
--}}
@php
    $cards = \App\Services\Observations\ObservationCardData::instance();
    $user = $cards->user($observation->observer_username);
    $isComet = $observation->target_type === \App\Models\Observation::TYPE_COMET;
    $comet =
        $isComet && is_numeric($observation->target_key)
            ? $cards->comet($observation->target_key)
            : null;
    $objectName = $comet?->name ?? (string) $observation->target_name;
    $objectSlug =
        $comet?->slug ??
        ($cards->target($observation->target_name)?->slug ??
            \Illuminate\Support\Str::slug($objectName));
    $instrument = $cards->instrument($observation->instrument_id);
    $description =
        \App\Services\Observations\DescriptionTranslations::forViewer(
            $observation,
        ) ?? $observation->description;
    $excerpt = \Illuminate\Support\Str::limit(
        trim(
            preg_replace(
                "/\s+/u",
                " ",
                strip_tags(
                    html_entity_decode(
                        (string) $description,
                        ENT_QUOTES | ENT_HTML5,
                        "UTF-8",
                    ),
                ),
            ),
        ),
        200,
    );
    $icon = match (true) {
        $isComet => "☄️",
        in_array(
            $observation->target_type,
            ["planet", "solarsystem", "lunar"],
            true,
        )
            => "🪐",
        default => "✨",
    };
@endphp

<li class="flex gap-3 py-3">
    <img
        src="{{ $user?->profile_photo_url ?? "/images/default-profile.png" }}"
        alt=""
        class="h-10 w-10 flex-shrink-0 rounded-full object-cover"
    />
    <div class="min-w-0 flex-1 text-sm">
        <div class="flex flex-wrap items-center gap-x-2">
            <span aria-hidden="true">{{ $icon }}</span>
            <a
                href="{{ $observation->detailUrl($user?->slug) ?? route("object.show", $objectSlug) }}"
                class="font-semibold text-white hover:underline"
                >{{ $objectName }}</a
            >
            <x-observation-class :value="$observation->observer_class" />
            @if ($observation->has_drawing)
                <span
                    title="{{ __("Drawing") }}"
                    aria-label="{{ __("Drawing") }}"
                    >✏️</span
                >
            @endif
            <span class="text-gray-400">
                {{ __("by") }}
                @if ($user)
                    <a
                        href="{{ route("observer.show", $user->slug) }}"
                        class="text-gray-300 hover:underline"
                        >{{ $user->name }}</a
                    >
                @else
                    {{ $observation->observer_username }}
                @endif
                &middot; {{
                    $observation->observed_on?->translatedFormat(
                        "j M Y",
                    )
                }}
                @if ($instrument)
                    &middot; {{ $instrument->fullName() }}
                @endif
            </span>
        </div>
        @if ($excerpt !== "")
            <p class="mt-0.5 line-clamp-2 text-gray-400">{{ $excerpt }}</p>
        @endif
    </div>
</li>
