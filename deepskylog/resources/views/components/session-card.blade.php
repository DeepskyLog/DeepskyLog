@props (["session"])

{{-- A session as a card (the home page and /sessions), prepared by SessionController::prepareSessionCards() --}}
@php
    $observerSlug = $session->observer?->slug;
    $sessionParam = $session->slug ?? $session->id;
    $url =
        ($observerSlug ?? $session->observerid) && $sessionParam
            ? route("session.show", [
                $observerSlug ?? $session->observerid,
                $sessionParam,
            ])
            : null;
    $name = html_entity_decode(
        $session->name ?? __("Session :id", ["id" => $session->id]),
        ENT_QUOTES | ENT_HTML5,
        "UTF-8",
    );
    $date = fn($value) => $value
        ? \Carbon\Carbon::parse($value)->translatedFormat("j M Y")
        : __("Unknown");
@endphp

<article class="flex flex-col rounded bg-gray-800 p-4">
    @if (!empty($session->preview))
        <a @if ($url) href="{{ $url }}" @endif class="mb-3 block">
            <img
                src="{{ $session->preview }}"
                alt="{{ $name }}"
                loading="lazy"
                class="h-40 w-full rounded object-cover"
            />
        </a>
    @endif

    <h3 class="mb-2 text-lg font-bold text-white">
        @if ($url)
            <a href="{{ $url }}" class="hover:underline">{{ $name }}</a>
        @else
            {{ $name }}
        @endif
    </h3>

    <div class="mb-1 text-sm text-gray-400">
        <span class="mr-1 font-medium text-gray-200">{{ __("Owner") }}:</span>
        @if ($observerSlug)
            <a
                href="{{ route("observer.show", $observerSlug) }}"
                class="hover:underline"
                >{{
                    $session->observer->name ??
                        $session->observerid
                }}</a
            >
        @else
            {{
                $session->observer?->name ??
                    $session->observerid
            }}
        @endif
    </div>

    <div class="mb-1 text-sm text-gray-400">
        {{ $date($session->begindate) }} &ndash; {{ $date($session->enddate) }}
    </div>

    <div class="mb-1 text-sm text-gray-400">
        <span class="mr-1 font-medium text-gray-200"
            >{{ __("Location") }}:</span
        >
        {{
            $session->location_name ??
                __("Unknown")
        }}
    </div>

    @if (isset($session->observation_count))
        <div class="mb-2 text-sm text-gray-300">
            {{ __("Observations") }}:
            <strong
                class="text-white"
                >{{ $session->observation_count }}</strong
            >
        </div>
    @endif

    <p class="mb-3 line-clamp-3 flex-1 text-sm text-gray-300">{{ $session->preview_text ?? \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($session->comments ?? "", ENT_QUOTES | ENT_HTML5, "UTF-8")), 180) }}</p>

    <div class="flex items-center justify-between text-sm">
        <div class="text-gray-400">
            {{ __("Observers") }}: {{
                $session->otherObserversCount() ??
                    1
            }}
        </div>
        <div class="flex items-center gap-3">
            <button
                type="button"
                data-observation-type="{{ \App\Models\ObservationLike::TYPE_SESSION }}"
                data-observation-id="{{ $session->id }}"
                class="like-button rounded bg-gray-700 px-2 py-1 text-white hover:bg-gray-600"
                aria-label="{{ __("Like") }}"
            >
                <span class="like-icon">{{
                    $session->liked ?? false
                        ? "❤️"
                        : "👍"
                }}</span>
                <span class="like-count">{{
                    $session->likes_count ??
                        0
                }}</span>
            </button>
            @if ($url)
                <a href="{{ $url }}" class="text-blue-500 hover:underline">{{
                    __(
                        "Read more",
                    )
                }}</a>
            @endif
        </div>
    </div>
</article>
