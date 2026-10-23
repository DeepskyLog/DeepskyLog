{{-- Avoid inline `use` in Blade; use fully-qualified class names within PHP blocks --}}
@props ([
    "observation",
    "unified_observation" => null,  // deprecated alias, kept for backward compat
    "preloaded_user" => null,
    "preloaded_comet" => null,
    "preloaded_location" => null,
    "preloaded_instrument" => null,
    "likes_count" => null,
    "liked" => null
])
<div class="justify-left mt-5 flex">
    @php
        // Accept either a unified App\Models\Observation or a legacy observation.
        $isUnified = $observation instanceof \App\Models\Observation;
        $obsUnified = $isUnified ? $observation : $unified_observation;
        $obs_type   = $obsUnified?->target_type ?? 'comet';

        if ($isUnified) {
            $observation_date = $observation->observed_on->format('Y-m-d');
            $observerid       = $observation->observer_username;
            $objectid         = is_numeric($observation->target_key) ? (int) $observation->target_key : null;
            $locationid       = $observation->location_id;
            $instrumentid     = $observation->instrument_id;
            $description      = $observation->description;
            $hasDrawing       = $observation->has_drawing;
            $obs_id           = $observation->id;
            $obs_mag          = null; // unified model doesn't have comet-specific mag
        } else {
            $date_raw         = $observation->date;
            $observation_date = substr($date_raw, 0, 4) . '-' . substr($date_raw, 4, 2) . '-' . substr($date_raw, 6, 2);
            $observerid       = $observation->observerid;
            $objectid         = $observation->objectid;
            $locationid       = $observation->locationid;
            $instrumentid     = $observation->instrumentid;
            $description      = $observation->description;
            $hasDrawing       = $observation->hasDrawing;
            $obs_id           = $observation->id;
            $obs_mag          = $observation->mag ?? null;
        }

        // Use preloaded data when available, otherwise the data the page preloaded
        // for its cards (ObservationCardData, which queries what is missing).
        $cards = \App\Services\Observations\ObservationCardData::instance();
        $user = $preloaded_user ?? $cards->user(html_entity_decode($observerid));
        // Fallback when user cannot be found
        if (!$user) {
            $user = (object) [
                "profile_photo_url" => asset("images/placeholder-avatar.png"),
                "name" => __("Unknown observer"),
                "username" => "",
                "slug" => "",
            ];
        }

        // Resolve drawing filename and public URL for unified and legacy comet observations
        $resolvedDrawing = null;
        if ($isUnified) {
            $resolvedDrawing = $obsUnified?->drawing_filename ?? null;
        } else {
            $resolvedDrawing = $observation->drawing_filename ?? null;
        }
        $drawingSrc = null;
        if (!empty($resolvedDrawing)) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($resolvedDrawing)) {
                    $drawingSrc = \Illuminate\Support\Facades\Storage::disk('public')->url($resolvedDrawing);
                    
                } else {
                    $drawingSrc = '/images/' . ltrim($resolvedDrawing, '/');
                }
            } catch (\Throwable $_) {
                $drawingSrc = '/images/' . ltrim($resolvedDrawing, '/');
            }
        }
        if (empty($drawingSrc) && $hasDrawing) {
            $drawingSrc = '/images/cometdrawings/' . $obs_id . '.jpg';
        }
        // Determine icon: check target name for Sun/Moon when available
        $targetName = $obsUnified?->target_name ?? null;
        $isSun = false;
        $isMoon = false;
        try {
            $isSun = $targetName ? (bool) preg_match('/\bsun\b/i', (string) $targetName) : false;
            $isMoon = $targetName ? (bool) preg_match('/\b(moon|luna|lunar|lune|mond)\b/i', (string) $targetName) : false;
        } catch (\Throwable $_) {
        }

        if ($isSun) {
            $iconEmoji = '🌞';
        } elseif ($isMoon) {
            $iconEmoji = '🌕';
        } else {
            if ($obs_type === 'deepsky') {
                $iconEmoji = '✨';
            } elseif ($obs_type === 'comet') {
                $iconEmoji = '☄️';
            } elseif (in_array($obs_type, ['planet', 'solarsystem'])) {
                $iconEmoji = '🪐';
            } else {
                $iconEmoji = '🔭';
            }
        }
    @endphp

    @if ($obsUnified)
        <div class="mr-2 mt-4 flex-shrink-0 sm:mr-3 sm:mt-6">
            <span role="img" aria-label="{{ ucfirst($obs_type) }}" title="{{ ucfirst($obs_type) }}" class="text-xl sm:text-2xl">
                {{ $iconEmoji }}
            </span>
        </div>
    @endif

    <div class="mr-3 flex-shrink-0 sm:mr-4">
        <img
            src="{{ $user->profile_photo_url ?? asset('images/placeholder-avatar.png') }}"
            alt="{{ $user->name ?? __('Unknown observer') }}"
            class="h-12 w-12 rounded-full object-cover sm:h-20 sm:w-20"
        />
    </div>

    <div class="min-w-0 flex-1 break-words">
        <a
            href="/observers/{{ $user->slug }}"
            class="font-bold hover:underline"
        >
            {{
                $user->name ??
                    __("Unknown observer")
            }}
        </a>

        @php
            // Use preloaded comet when available
            $cometOld =
                $preloaded_comet ??
                ($objectid ? $cards->comet($objectid) : null);
            $cometName = $cometOld ? $cometOld->name : ($isUnified ? ($observation->target_name ?? __("Unknown comet")) : __("Unknown comet"));
            $slug =
                $cometOld?->slug ?? \Illuminate\Support\Str::slug($cometName ?? "", "-");
            $link = route("object.show", $slug);
        @endphp

        {!!
            __(" observed :object", [
                "object" =>
                    '<a href="' .
                    $link .
                    '" class="font-bold hover:underline">' .
                    e($cometName) .
                    "</a>",
            ])
        !!}
        @if (!empty($obs_mag) && $obs_mag > -90)
            <span class="text-gray-400">&nbsp;({{ __("mag") }} {{ number_format($obs_mag, 1) }})</span>
        @endif

        {{ __(" on ") }} {{
            \Carbon\Carbon::create(
                $observation_date,
            )->translatedFormat("j M Y")
        }}
        @if ($locationid > 0)
            {{ __(" from ") }}
            @php
                $location =
                    $preloaded_location ?? $cards->location($locationid);
                $locationSlug = $location ? $location->slug : "";
                $locationName = $location ? $location->name : __("Unknown location");
            @endphp
            <a
                href="/location/{{ $user->slug }}/{{ $locationSlug }}"
                class="font-bold hover:underline"
            >
                {{
                    html_entity_decode(
                        $locationName,
                    )
                }} .
            </a>
        @endif
        <br />
        @if ($instrumentid > 0)
            @php
                $instrument =
                    $preloaded_instrument ?? $cards->instrument($instrumentid);
                $instrumentSlug = $instrument ? $instrument->slug : "";
                $instrumentName = $instrument ? $instrument->fullName() : __("Unknown instrument");
            @endphp
            {{
                __(
                    "Used instrument was ",
                )
            }}
            <a
                href="/instrument/{{ $user->slug }}/{{ $instrumentSlug }}"
                class="font-bold hover:underline"
            >
                {{
                    html_entity_decode(
                        $instrumentName,
                    )
                }} .
            </a>
        @endif

        @if ($description != "")
            <br />
            {{
                __(
                    " The following notes where made: ",
                )
            }}
            <br />
            <div class="my-2 rounded-sm bg-gray-900 px-4 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4">
                    @if ($hasDrawing && $drawingSrc)
                        <button
                            type="button"
                            onclick="window.dispatchEvent(new CustomEvent('open-comet-lightbox-{{ $obs_id }}'))"
                            class="flex-shrink-0 focus:outline-none"
                        >
                            <img
                                src="{{ $drawingSrc }}"
                                alt="comet-drawing-{{ $obs_id }}"
                                class="w-28 rounded"
                            />
                        </button>
                    @endif

                    <div class="w-full min-w-0 flex-1 break-words">
                        @if (auth()->check() && auth()->user()->translate && !empty($observation->description))
                            @php
                                // The cached translation, else the description (translated after the response)
                                $translated = \App\Services\Observations\DescriptionTranslations::get(
                                    (int) $observation->id,
                                    auth()->user()->language,
                                    \App\Jobs\TranslateObservationDescriptions::COMET,
                                );
                            @endphp
                            {!! \App\Support\Html::purify($translated ?? $observation->description, true) !!}
                        @else
                            {!! \App\Support\Html::purify($observation->description, true) !!}
                        @endif
                    </div>
                </div>

                <div
                    x-data="{ open: false }"
                    x-on:open-comet-lightbox-{{ $obs_id }}.window="open = true"
                >
                    <div
                        x-cloak
                        x-show="open"
                        x-transition.opacity=""
                        @click.self="open = false"
                        @keydown.escape.window="open = false"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70"
                    >
                        <div class="max-w-4xl max-h-[90vh] p-4">
                            <button
                                type="button"
                                @click="open = false"
                                class="absolute top-4 right-4 z-50 rounded bg-gray-800 p-2 text-white"
                            >
                                &times;
                            </button>
                            <img
                                src="{{ $drawingSrc }}"
                                alt="comet-drawing-large-{{ $obs_id }}"
                                class="max-w-full max-h-[85vh] rounded shadow-lg"
                            />
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2 mt-2 mb-2">
            @php
                $observerSegment = $user?->slug ?: $observerid;

                // With the index when the observer has several observations of the
                // object that day.
                $detailHref = $obsUnified?->detailUrl($observerSegment);

                // Likes are stored by the id in the observations table
                $likeType = \App\Models\ObservationLike::TYPE_OBSERVATION;
                $likeId   = $obsUnified?->id ?? $obs_id;

                if ($likes_count !== null && $liked !== null) {
                    $likesCount = $likes_count;
                    $isLiked    = $liked;
                } else {
                    ['count' => $likesCount, 'liked' => $isLiked] = $cards->likes($likeId);
                }
            @endphp
            <a
                href="{{ $detailHref ?? '#' }}"
                class="inline-flex items-center px-4 py-2 rounded bg-gray-700 hover:bg-gray-600 text-white align-middle"
                @if(!$detailHref) aria-disabled="true" @endif
            >
                {{ __("More details") }}
            </a>

            @auth
                <a
                    href="{{ route('messages.create', ['to' => $user->username ?? '', 'subject' => __('About your observation of :object', ['object' => $cometName]), 'about' => $detailHref]) }}"
                    class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white align-middle"
                    aria-label="{{ __('Send message about this sketch') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                        <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                    </svg>
                </a>
            @endauth

            <button
                data-observation-type="{{ $likeType }}"
                data-observation-id="{{ $likeId }}"
                class="like-button px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-white align-middle"
            >
                <span class="like-icon">{!! $isLiked ? "❤️" : "👍" !!}</span>
                <span class="like-count">{{ $likesCount }}</span>
            </button>
        </div>
    </div>
</div>
