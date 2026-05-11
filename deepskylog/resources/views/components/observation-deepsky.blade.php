@props ([
    "observation",
    "unified_observation" => null,  // deprecated alias, kept for backward compat
    "translator" => null,
    "preloaded_user" => null,
    "preloaded_object" => null,
    "preloaded_location" => null,
    "preloaded_instrument" => null,
    "preloaded_eyepiece" => null,
    "preloaded_filter" => null,
    "preloaded_constellations" => null
])
<div class="justify-left mt-5 flex">
    @php
        use App\Models\Constellation;
        use App\Models\Eyepiece;
        use App\Models\Filter;
        use App\Models\Instrument;
        use App\Models\Location;
        use App\Models\ObjectsOld;
        use App\Models\User;
        use Carbon\Carbon;
        use Illuminate\Support\Facades\Cache;

        // Accept either a unified App\Models\Observation or a legacy-shaped object.
        $isUnified = $observation instanceof \App\Models\Observation;
        $obsUnified = $isUnified ? $observation : $unified_observation;
        $obs_type   = $obsUnified?->target_type ?? 'deepsky';

        if ($isUnified) {
            $observation_date = $observation->observed_on->format('Y-m-d');
            $observerid       = $observation->observer_username;
            $objectname       = (string) ($observation->target_name ?? '');
            $locationid       = $observation->location_id;
            $instrumentid     = $observation->instrument_id;
            $eyepieceid       = (int) ($observation->eyepiece_id ?? 0);
            $filterid         = (int) ($observation->filter_id ?? 0);
            $magnification    = $observation->magnification;
            $description      = $observation->description;
            $hasDrawing       = $observation->has_drawing;
            $obs_sqm          = $observation->sqm ?? 0;
            $obs_seeing       = (int) ($observation->seeing ?? 0);
            $obs_transparency = (int) ($observation->transparency ?? 0);
            $obs_language     = $observation->language ?? null;
            $obs_id           = $observation->id;
        } else {
            $date_raw         = $observation->date;
            $observation_date = substr($date_raw, 0, 4) . '-' . substr($date_raw, 4, 2) . '-' . substr($date_raw, 6, 2);
            $observerid       = $observation->observerid;
            $objectname       = (string) ($observation->objectname ?? '');
            $locationid       = $observation->locationid;
            $instrumentid     = $observation->instrumentid;
            $eyepieceid       = (int) ($observation->eyepieceid ?? 0);
            $filterid         = (int) ($observation->filterid ?? 0);
            $magnification    = $observation->magnification;
            $description      = $observation->description;
            $hasDrawing       = $observation->hasDrawing;
            $obs_sqm          = $observation->SQM ?? 0;
            $obs_seeing       = (int) ($observation->seeing ?? 0);
            $obs_transparency = (int) ($observation->transparency ?? 0);
            $obs_language     = $observation->language ?? null;
            $obs_id           = $observation->id;
        }

        // Use preloaded data if available, otherwise fall back to DB queries
        $user =
            $preloaded_user ??
            User::where("username", html_entity_decode($observerid))->first();
        $object =
            $preloaded_object ??
            ObjectsOld::where("name", $objectname)->first();

        if ($preloaded_constellations && $object) {
            $constellation = $preloaded_constellations[$object->con]->name ?? "";
        } else {
            $constellation = $object
                ? Constellation::where("id", $object->con)->first()?->name ?? ""
                : "";
        }

        $location   = $preloaded_location   ?? Location::where("id", $locationid)->first();
        $instrument = $preloaded_instrument ?? Instrument::where("id", $instrumentid)->first();
        $eyepiece   = $preloaded_eyepiece   ?? ($eyepieceid > 0 ? Eyepiece::where("id", $eyepieceid)->first() : null);
        $filter     = $preloaded_filter     ?? ($filterid > 0   ? Filter::where("id", $filterid)->first()   : null);

        $tr = $translator ?? null;
        if ($tr === null && auth()->check() && auth()->user()->translate) {
            $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(auth()->user()->language);
        }

        // Resolve drawing filename and public URL for unified and legacy observations
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
            $drawingSrc = '/images/drawings/' . $obs_id . '.jpg';
        }

        // Determine icon: prefer Sun and Moon when target name matches
        $targetName = $objectname ?? ($obsUnified?->target_name ?? '');
        $isSun = false;
        $isMoon = false;
        try {
            $isSun = (bool) preg_match('/\bsun\b/i', (string) $targetName);
            $isMoon = (bool) preg_match('/\b(moon|luna|lunar|lune|mond)\b/i', (string) $targetName);
        } catch (\Throwable $_) {
        }

        if ($isSun) {
            $iconEmoji = '🌞';
        } elseif ($isMoon || ($obs_type === 'lunar')) {
            // Use moon icon when target is explicitly lunar or name matches moon keywords
            $iconEmoji = '🌕';
        } elseif ($obs_type === 'deepsky') {
            $iconEmoji = '✨';
        } elseif ($obs_type === 'comet') {
            $iconEmoji = '☄️';
        } elseif (in_array($obs_type, ['planet', 'solarsystem'])) {
            $iconEmoji = '🪐';
        } else {
            $iconEmoji = '🔭';
        }
    @endphp

    @if ($obsUnified)
        <div class="mr-3 mt-6 flex-shrink-0">
            <span role="img" aria-label="{{ ucfirst($obs_type) }}" title="{{ ucfirst($obs_type) }}" class="text-2xl">
                {{ $iconEmoji }}
            </span>
        </div>
    @endif

    <div class="mr-4">
        <img
            src="{{ $user?->profile_photo_url ?? '/images/default-profile.png' }}"
            alt="{{ $user?->name ?? __('Unknown') }}"
            class="h-20 w-20 rounded-full object-cover"
        />
    </div>

    <div class="max-w-[calc(100%-7rem)]">
        @if ($user)
            <a
                href="/observers/{{ $user->slug }}"
                class="font-bold hover:underline"
            >
                {{ $user->name }}
            </a>
        @else
            <span class="font-bold">{{
                __(
                    "Unknown observer",
                )
            }}</span>
        @endif
        @php
            $slug =
                $object?->slug ??
                \Illuminate\Support\Str::slug($objectname, "-");
            $link = route("object.show", $slug);
        @endphp

        @php
            $objectLongType = $object ? $object->long_type() : null;
            $objectTypeLabel = $objectLongType
                ? Str::lower(__($objectLongType))
                : __("object");
        @endphp
        {!!
            __(" observed :object", [
                "object" =>
                    $objectTypeLabel .
                    ' <a href="' .
                    $link .
                    '" class="font-bold hover:underline">' .
                    $objectname .
                    "</a>",
            ])
        !!}

        {{
            __(" in ") .
                $constellation
        }}

        {{ __(" on ") }} {{
            Carbon::create(
                $observation_date,
            )->translatedFormat("j M Y")
        }} {{ __(" from ") }}
        @if ($location)
            <a
                href="/location/{{ $user?->slug ?? 'unknown' }}/{{ $location->slug }}"
                class="font-bold hover:underline"
            >
                {{
                    html_entity_decode(
                        $location->name,
                    )
                }}
            </a>
        @else
            <span class="font-bold">{{
                __(
                    "Unknown location",
                )
            }}</span>
        @endif

        {{-- Seeing / SQM / transparency --}}
        @if ($obs_sqm > 0)
            {{ __(" in a sky with an SQM of ") }}
            {{ $obs_sqm }}
        @endif
        @if ($obs_transparency > 0)
            {{ __(" with ") }}
            {{ __('observations.transparency.' . $obs_transparency) }}
            {{ __(" transparency") }}
        @endif
        @if ($obs_seeing > 0)
            {{ __(" under ") }}
            {{ __('observations.seeing.' . $obs_seeing) }}
            {{ __("seeing") }}
        @endif
        .<br />
        {{
            __(
                "Used instrument was ",
            )
        }}
        @if ($instrument)
            <a
                href="/instrument/{{ $user?->slug ?? 'unknown' }}/{{ $instrument->slug }}"
                class="font-bold hover:underline"
            >
                {!!
                    html_entity_decode(
                        $instrument->fullName(),
                    )
                !!}
            </a>
        @else
            <span class="font-bold">{{
                __(
                    "Unknown instrument",
                )
            }}</span>
        @endif
        @if ($magnification > 0)
            {{ __(" with a magnification of ") }}
            {{ $magnification }}x
        @endif
        @if ($eyepieceid > 0)
            {{
                __(
                    " using a ",
                )
            }}
            @if ($eyepiece)
                <a
                    href="/eyepiece/{{ $user?->slug ?? 'unknown' }}/{{ $eyepiece->slug }}"
                    class="font-bold hover:underline"
                >
                    {{
                        html_entity_decode(
                            $eyepiece->fullName(),
                        )
                    }}
                </a>
            @else
                <span class="font-bold">{{
                    __(
                        "Unknown eyepiece",
                    )
                }}</span>
            @endif
            {{
                __(
                    " eyepiece",
                )
            }}
            @if ($filterid > 0)
                {{ __(" and a ") }}
                @if ($filter)
                    <a
                        href="/filter/{{ $user?->slug ?? 'unknown' }}/{{ $filter->slug }}"
                        class="font-bold hover:underline"
                    >
                        {{ html_entity_decode($filter->name) }}
                    </a>
                @else
                    <span class="font-bold">{{ __("Unknown filter") }}</span>
                @endif
                {{ __(" filter") }}
            @endif
        @elseif ($filterid > 0)
            {{ __(" using a ") }}
            @php
                $filterModel = Filter::where("id", $filterid)->first();
                $filterName = $filterModel ? $filterModel->name : null;
            @endphp
            <a
                href="{{ config('app.old_url') }}/index.php?indexAction=detail_filter&filter={{ $observation->filterid }}"
                class="font-bold hover:underline"
            >
                {{
                    html_entity_decode(
                        $filterName ?? __("Unknown filter"),
                    )
                }}
            </a>
            {{ __(" filter") }}
        @endif
        .

        <br />
        {{
            __(
                " The following notes where made: ",
            )
        }}
        <br />
        <div class="my-2 rounded-sm bg-gray-900 px-4 py-3">
            <div class="flex items-start space-x-4">
                @if ($hasDrawing && $drawingSrc)
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-deepsky-lightbox-{{ $obs_id }}'))"
                        class="flex-shrink-0 focus:outline-none"
                    >
                        <img
                            src="{{ $drawingSrc }}"
                            alt="drawing-{{ $obs_id }}"
                            class="w-28 rounded"
                        />
                    </button>
                @endif

                <div class="flex-1">
                    @if (auth()->user() &&
                        auth()->user()->translate &&
                        ($obs_language ?? "nl") !== auth()->user()->language)
                        @php
                            $cacheKey =
                                "observation_deepsky_translation:" .
                                $obs_id .
                                ":" .
                                auth()->user()->language;
                            $translated = Cache::remember($cacheKey, now()->addDays(30), function () use (
                                $description,
                                $tr,
                            ) {
                                if ($tr === null || empty($description)) {
                                    return null;
                                }

                                try {
                                    return $tr->translate(html_entity_decode($description));
                                } catch (\Throwable $e) {
                                    return null;
                                }
                            });
                        @endphp
                        {!! $translated ?? html_entity_decode($description) !!}
                    @else
                        {!! html_entity_decode($description) !!}
                    @endif
                </div>
            </div>

            <!-- Modal / Lightbox (listens for custom event to open) -->
            <div
                x-data="{ open: false }"
                x-on:open-deepsky-lightbox-{{ $obs_id }}.window="open = true"
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
                            alt="drawing-large-{{ $obs_id }}"
                            class="max-w-full max-h-[85vh] rounded shadow-lg"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3 mt-2 mb-2">
            @php
                $observerSegment = $user?->slug ?: $observerid;
                $objectSegment   = $object?->slug ?: \Illuminate\Support\Str::slug($objectname, "-");

                if ($isUnified) {
                    $matchingIds = \App\Models\Observation::where("observer_username", $observerid)
                        ->whereDate("observed_on", $observation_date)
                        ->where("target_name", $objectname)
                        ->orderBy("observed_time")
                        ->orderBy("id")
                        ->pluck("id")
                        ->map(fn($id) => (int) $id)
                        ->values();
                } else {
                    $matchingIds = \App\Models\Observation::where("observer_username", $observerid)
                        ->where("legacy_source", "deepsky")
                        ->where("legacy_date_int", $observation->legacy_date_int ?? $observation->date)
                        ->where("target_name", $objectname)
                        ->orderBy("id")
                        ->pluck("id")
                        ->map(fn($id) => (int) $id)
                        ->values();
                }

                $position = $matchingIds->search((int) $obs_id);
                $occurrenceIndex = $position === false ? 1 : (int) $position + 1;

                $detailRouteParams = [
                    "observer" => $observerSegment,
                    "date"     => $observation_date,
                    "object"   => $objectSegment,
                ];
                if ($occurrenceIndex > 1) {
                    $detailRouteParams["index"] = $occurrenceIndex;
                }

                // Likes: use the unified observation id + target_type as type key
                $likeType   = $isUnified ? ($obs_type ?: 'deepsky') : 'deepsky';
                $likeId     = $obs_id;
                $likesCount = \App\Models\ObservationLike::where("observation_type", $likeType)
                    ->where("observation_id", $likeId)
                    ->count();
                $liked = auth()->check() && \App\Models\ObservationLike::where("observation_type", $likeType)
                    ->where("observation_id", $likeId)
                    ->where("user_id", auth()->id())
                    ->exists();
            @endphp
            @php
                $detailHref = null;
                $canBuildDetail = !empty($detailRouteParams['observer']) && !empty($detailRouteParams['date']) && !empty($detailRouteParams['object']);
                if ($canBuildDetail) {
                    try {
                        $detailHref = route('observations.detail', $detailRouteParams);
                    } catch (\Throwable $_) {
                        $detailHref = null;
                    }
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
                    href="{{ route('messages.create', ['to' => $user->username ?? '', 'subject' => 'About your observation of ' . $objectname]) }}"
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
                <span class="like-icon">{!!
                    $liked
                        ? "❤️"
                        : "👍"
                !!}</span>
                <span class="like-count">{{ $likesCount }}</span>
            </button>
        </div>
    </div>
</div>
