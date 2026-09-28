<x-app-layout>
    @php
        $obs = $observation;
        $user = ($preloaded_users ?? collect())->get($obs->observerid);
        $dateInt = (string) $obs->date;
        $obsDate =
            strlen($dateInt) === 8
                ? substr($dateInt, 0, 4) .
                    "-" .
                    substr($dateInt, 4, 2) .
                    "-" .
                    substr($dateInt, 6, 2)
                : $dateInt;
        $obsTimeInt = (int) $obs->time;
        $obsTime =
            isset($obs->time) &&
            $obs->time !== null &&
            $obs->time !== "" &&
            $obsTimeInt > 0 &&
            $obsTimeInt <= 2359 &&
            $obsTimeInt % 100 < 60
                ? sprintf(
                    "%02d:%02d",
                    intdiv($obsTimeInt, 100),
                    $obsTimeInt % 100,
                )
                : null;

        $backUrl =
            $observationType === "comet"
                ? route("observations.comet.index")
                : route("observations.index");
        if ($user && !empty($user->slug)) {
            $backUrl =
                $observationType === "comet"
                    ? route("observations.comet.show", $user->slug)
                    : route("observations.show", $user->slug);
        }

        // Translation
        $tr = null;
        if (auth()->check() && auth()->user()->translate) {
            $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(
                auth()->user()->language,
            );
        }

        if ($observationType === "deepsky") {
            $object = ($preloaded_objects ?? collect())->get($obs->objectname);
            // If not found by name, try by slug (handles slug-stored target_name)
                if (!$object) {
                $object = ($preloaded_objects ?? collect())->get(
                    \Illuminate\Support\Str::slug((string) ($obs->objectname ?? ""), "-")
                );
            }
            // Last-resort: live lookup by slug or name
            if (!$object) {
                $object = \App\Models\Target::where('slug', \Illuminate\Support\Str::slug((string) ($obs->objectname ?? ""), "-"))
                    ->orWhere('name', $obs->objectname)
                    ->first();
            }
            $objectTypeRaw = strtoupper(trim((string) ($object?->type ?? '')));
            $isOpenCluster = in_array($objectTypeRaw, ['OPNCL', 'LMCOC', 'SMCOC']);
            $location = ($preloaded_locations ?? collect())->get($obs->locationid);
            $instrument = ($preloaded_instruments ?? collect())->get(
                $obs->instrumentid,
            );
            $eyepiece = ($preloaded_eyepieces ?? collect())->get($obs->eyepieceid);
            $filterObj = ($preloaded_filters ?? collect())->get($obs->filterid);
            $constellations = $preloaded_constellations ?? collect();
            $constellation = $object
                ? $constellations[$object->con]->name ?? null
                : null;
            $objectSlug =
                $object?->slug ??
                \Illuminate\Support\Str::slug((string) ($obs->objectname ?? ""), "-");
        } else {
            $comet = ($preloaded_comets ?? collect())->get($obs->objectid);
            $location = ($preloaded_locations ?? collect())->get($obs->locationid);
            $instrument = ($preloaded_instruments ?? collect())->get(
                $obs->instrumentid,
            );
            $cometName =
                $comet?->name ?? ($obs->resolved_comet_name ?? __("Unknown comet"));
            $cometSlug =
                $comet?->slug ??
                \Illuminate\Support\Str::slug((string) $cometName, "-");
        }

        // Prefer storage-backed drawing when present in unified observation (new uploads stored in storage/app/public/sketches)
        $drawingPath = null;
        if (!empty($drawing_filename)) {
            try {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($drawing_filename)) {
                    $drawingPath = \Illuminate\Support\Facades\Storage::disk('public')->url($drawing_filename);
                } else {
                    $drawingPath = "/images/" . $drawing_filename;
                }
            } catch (\Throwable $_) {
                $drawingPath = "/images/" . $drawing_filename;
            }
        } else {
            $drawingPath = "/images/" . (($observationType === "comet") ? "cometdrawings/" : "drawings/") . $obs->id . '.jpg';
        }

        $socialType = $observationType === "comet" ? "comet" : "deepsky";
        $socialId = (int) $obs->id;
        $likesCount = $likesCount ?? 0;
        $liked = $liked ?? false;
        $comments = $comments ?? collect();
        $commentsCount = $commentsCount ?? $comments->count();

        $objectDisplayName =
            $observationType === "comet"
                ? $cometName
                : (string) ($object?->name ?? $obs->objectname ?? "");
        $objectLinkSlug = $observationType === "comet" ? $cometSlug : $objectSlug;
        $objectUrl = route("object.show", $objectLinkSlug);
        $allObservationsUrl =
            $observationType === "comet"
                ? route("observations.comet.object", ["object" => $objectLinkSlug])
                : url("/observations/" . $objectLinkSlug);

        $myObservationsUrl = null;
        if (auth()->check() && !empty(auth()->user()->slug)) {
            $myObservationsUrl =
                $observationType === "comet"
                    ? route("observations.comet.user.object", [
                        "observer" => auth()->user()->slug,
                        "object" => $objectLinkSlug,
                    ])
                    : route("observations.user.object", [
                        "observer" => auth()->user()->slug,
                        "object" => $objectLinkSlug,
                    ]);
        }

        $compareMyLastUrl = null;
        if (auth()->check()) {
            $currentIndex = max(1, (int) (request()->route("index") ?? 1));
            $compareParams = [
                "observer" =>
                    request()->route("observer") ?: $user?->slug ?? $obs->observerid,
                "date" => $obsDate,
                "object" => $objectLinkSlug,
                // Always given: the index sits in the middle of the compare URL.
                "index" => $currentIndex,
            ];

            $compareMyLastUrl = route("observations.compare.my-last", $compareParams);
        }

        $moon = $moonContext ?? null;
                    @endphp
                    @push('head')
                        @php
                            $ogTitle = __("Look at this sketch of ") . $objectDisplayName . __(" by ") . ($user?->name ?? $obs->observerid ?? '');
                            $ogUrl = url()->current();
                            $ogImageAbsolute = str_starts_with($drawingPath, 'http') ? $drawingPath : url($drawingPath);
                        @endphp
                        <meta property="og:type" content="article" />
                        <meta property="og:title" content="{{ $ogTitle }}" />
                        <meta property="og:url" content="{{ $ogUrl }}" />
                        <meta property="og:image" content="{{ $ogImageAbsolute }}" />
                        <meta property="og:image:alt" content="{{ $objectDisplayName }}" />
                        <meta property="og:site_name" content="DeepskyLog" />
                        <meta name="twitter:card" content="summary_large_image" />
                        <meta name="twitter:title" content="{{ $ogTitle }}" />
                        <meta name="twitter:image" content="{{ $ogImageAbsolute }}" />
                        <meta name="twitter:image:alt" content="{{ $objectDisplayName }}" />
                    @endpush
                    @if (!empty($sketchOfTheWeek))
                        <div
                            class="mb-4 flex items-center gap-3 rounded-lg border border-yellow-400 bg-yellow-900/30 px-5 py-3"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 flex-shrink-0 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.286 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.175 0l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.364-1.118L2.062 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69L9.049 2.927z" />
                            </svg>
                            <div>
                                <span
                                    class="font-bold text-yellow-300 text-lg"
                                    >{{ __("DeepskyLog Sketch of the Week") }}</span
                                >
                                <span class="ml-2 text-yellow-200 text-sm"
                                    >&mdash; {{ \Carbon\Carbon::parse($sketchOfTheWeek->date)->translatedFormat("j M Y") }}</span
                                >
                            </div>

                            @if(!empty($isOpenCluster) && (!empty($obs->clusterType) || !empty($obs->cluster_type)))
                                <div>
                                    <dt class="text-xs uppercase text-gray-400">{{ __('Cluster type') }}</dt>
                                    <dd class="mt-1 font-semibold text-gray-100">
                                        @php
                                            $ct = strtoupper(trim((string) ($obs->clusterType ?? $obs->cluster_type ?? '')));
                                            $ctLabel = __("observations.cluster_types.$ct");
                                        @endphp
                                        @if($ctLabel === "observations.cluster_types.$ct")
                                            {{ $ct }}
                                        @else
                                            {{ $ctLabel }}
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if (!empty($sketchOfTheMonth))
                        <div
                            class="mb-4 flex items-center gap-3 rounded-lg border border-amber-400 bg-amber-900/30 px-5 py-3"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 flex-shrink-0 text-amber-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.286 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.175 0l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.364-1.118L2.062 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69L9.049 2.927z" />
                            </svg>
                            <div>
                                <span
                                    class="font-bold text-amber-300 text-lg"
                                    >{{
                                        __(
                                            "DeepskyLog Sketch of the Month",
                                        )
                                    }}</span
                                >
                                <span class="ml-2 text-amber-200 text-sm"
                                    >&mdash; {{
                                        \Carbon\Carbon::parse(
                                            $sketchOfTheMonth->date,
                                        )->translatedFormat("j M Y")
                                    }}</span
                                >
                            </div>
                        </div>
                    @endif

                    {{-- Drawing (large, at top if present) --}}
                    @if ($obs->hasDrawing)
                        <div x-data="{ open: false }" class="mb-8">
                            <button
                                type="button"
                                @click="open = true"
                                class="block w-full focus:outline-none"
                            >
                                <img
                                    src="{{ $drawingPath }}"
                                    alt="{{ __('Observation drawing') }}"
                                    class="mx-auto max-h-[70vh] rounded-lg shadow-xl cursor-zoom-in"
                                />
                            </button>
                            {{-- Lightbox --}}
                            <div
                                x-cloak
                                x-show="open"
                                x-transition.opacity
                                @click.self="open = false"
                                @keydown.escape.window="open = false"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                            >
                                <button
                                    type="button"
                                    @click="open = false"
                                    class="absolute top-4 right-4 z-50 rounded bg-gray-800 p-2 text-white text-xl leading-none"
                                >
                                    &times;
                                </button>
                                <img
                                    src="{{ $drawingPath }}"
                                    alt="{{ __('Observation drawing') }}"
                                    class="max-w-full max-h-[90vh] rounded shadow-2xl"
                                />
                            </div>
                        </div>
                        {{-- License notice below drawing --}}
                        @if ($user && !empty($user->copyright) && $user->copyright !== 'No license (Not recommended)')
                            <div class="mt-3 text-sm text-center">
                                {!! $user->getCopyright() !!}
                            </div>
                        @endif
                    @endif

                    {{-- Header: observer + object --}}
                    <div class="flex items-start gap-5 mb-6 mt-4">
                        <img
                            src="{{ $user?->profile_photo_url ?? asset('images/placeholder-avatar.png') }}"
                            alt="{{ $user?->name ?? __('Unknown observer') }}"
                            class="h-16 w-16 rounded-full object-cover flex-shrink-0"
                        />
                        <div>
                            <div class="text-2xl font-bold">
                                    {{ $objectDisplayName }}
                            </div>
                            <div class="text-gray-400 text-sm mt-1">
                                {{
                                    __(
                                        "Observed by",
                                    )
                                }}
                                @if ($user)
                                    <a
                                        href="/observers/{{ $user->slug }}"
                                        class="text-white font-semibold hover:underline"
                                        >{{ $user->name }}</a
                                    >
                                @else
                                    <span
                                        class="text-white font-semibold"
                                        >{{ $obs->observerid }}</span
                                    >
                                @endif
                                {{ __("on") }}
                                <span class="text-white font-semibold">{{
                                    \Carbon\Carbon::create(
                                        $obsDate,
                                    )->translatedFormat("j M Y")
                                }}</span>
                                @if ($obsTime)
                                    {{ __("at") }}
                                    <span
                                        class="text-white font-semibold"
                                        >{{ $obsTime }}</span
                                    >
                                @endif
                            </div>
                        </div>
                    </div>

                        @auth
                            @php
                                $viewer = auth()->user();
                                $viewerIsOwner = ($viewer->username ?? '') === ($obs->observerid ?? $obs->observer_username ?? '');
                                $viewerIsAdmin = $viewer?->isAdministrator() || (method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges());
                            @endphp
                            @if ($viewerIsOwner || $viewerIsAdmin)
                                <div class="flex items-center gap-2 mb-6">
                                    <a href="{{ route('observations.edit', ['observer' => request()->route('observer') ?: ($user?->slug ?? ''), 'date' => request()->route('date') ?: $obsDate, 'object' => request()->route('object') ?: ($objectLinkSlug ?? ''), 'index' => request()->route('index') ?? 1]) }}" class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-500 text-white text-sm">{{ __('Update') }}</a>
                                    <form method="POST" action="{{ route('observations.destroy', ['observer' => request()->route('observer') ?: ($user?->slug ?? ''), 'date' => request()->route('date') ?: $obsDate, 'object' => request()->route('object') ?: ($objectLinkSlug ?? ''), 'index' => request()->route('index') ?? 1]) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this observation?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-500 text-white text-sm">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            @endif
                        @endauth

                        {{-- Engagement: likes and comments --}}
                    <div class="rounded-xl bg-gray-800 p-6 mb-6">
                        @php
                            $detailShareUrl  = url()->current();
                            $detailObserver  = $user?->name ?? $obs->observerid ?? '';
                            $detailHasSketch = (bool) ($obs->hasDrawing ?? $obs->has_drawing ?? false);
                            $detailVerb      = $detailHasSketch ? __("Look at this sketch of ") : __("Look at this observation of ");
                            $detailShareDesc = $detailVerb . $objectDisplayName . __(" by ") . $detailObserver . __(" on #deepskylog");
                            $detailShareTags = $observationType === 'comet'
                                ? ($detailHasSketch ? '#sketch #astronomy #cometwatch' : '#astronomy #cometwatch')
                                : ($detailHasSketch ? '#sketch #astronomy #astrosketching' : '#astronomy #deepsky');
                            $detailShareText  = $detailShareDesc . "\n\n" . $detailShareUrl . "\n\n" . $detailShareTags;
                            $detailTwitter    = "https://twitter.com/intent/tweet?text=" . rawurlencode($detailShareText);
                            $detailFacebook   = "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($detailShareUrl);
                            $detailWhatsapp   = "https://wa.me/?text=" . rawurlencode($detailShareText);
                            $detailMailto     = "mailto:?subject=" . rawurlencode($detailShareDesc) . "&body=" . rawurlencode($detailShareText);
                        @endphp
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <button
                                data-observation-type="{{ $socialType }}"
                                data-observation-id="{{ $socialId }}"
                                class="like-button px-3 py-1.5 rounded bg-gray-700 hover:bg-gray-600 text-white inline-flex items-center gap-2"
                            >
                                <span class="like-icon">{!!
                                    $liked
                                        ? "❤️"
                                        : "👍"
                                !!}</span>
                                <span>{{ __("Like") }}</span>
                                <span
                                    class="like-count"
                                    >{{ $likesCount }}</span
                                >
                            </button>
                            <span class="text-sm text-gray-400">
                                {{ $commentsCount }} {{
                                    \Illuminate\Support\Str::plural(
                                        __("comment"),
                                        $commentsCount,
                                    )
                                }}
                            </span>
                            {{-- Share buttons --}}
                            <div id="social-buttons" class="flex items-center gap-1 ml-auto">
                                <a href="{{ $detailFacebook }}" class="social-button hover" rel="follow" title="{{ __('Share on Facebook') }}"><span class="fab fa-facebook-square"></span></a>
                                <a href="{{ $detailTwitter }}" class="social-button hover" rel="follow" title="{{ __('Share on X') }}"><span class="fab fa-square-x-twitter"></span></a>
                                <a href="{{ $detailShareUrl }}" class="social-button hover" id="clip" title="{{ __('Copy link') }}" rel="nofollow noopener noreferrer"><span class="fas fa-share"></span></a>
                                <a href="{{ $detailMailto }}" class="social-button hover" rel="nofollow" title="{{ __('Share by email') }}"><span class="fas fa-envelope"></span></a>
                                <a href="{{ $detailWhatsapp }}" class="social-button hover" target="_blank" rel="nofollow" title="{{ __('Share on WhatsApp') }}"><span class="fab fa-square-whatsapp"></span></a>
                            </div>
                        </div>

                        <section>
                            <h2 class="text-lg font-semibold text-white mb-3">
                                {{ __("Comments") }}
                            </h2>

                            @if ($comments->isEmpty())
                                <p class="text-gray-400 text-sm mb-4">{{
                                    __(
                                        "No comments yet.",
                                    )
                                }}</p>
                            @else
                                <div class="space-y-3 mb-4">
                                    @foreach ($comments as $comment)
                                        <div
                                            class="bg-gray-700/60 p-3 rounded-lg border border-gray-600/60"
                                        >
                                            <div
                                                class="flex items-center justify-between mb-1"
                                            >
                                                <span
                                                    class="text-xs font-medium text-gray-300"
                                                >
                                                    {{
                                                        $comment->user->name ??
                                                            ($comment->user->username ?? __("Unknown user"))
                                                    }}
                                                </span>
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <span
                                                        class="text-xs text-gray-400"
                                                        >{{ $comment->created_at?->diffForHumans() }}</span
                                                    >
                                                    @auth
                                                        @if ($comment->canBeDeletedBy(auth()->user(), $user))
                                                            <form
                                                                method="POST"
                                                                action="{{ route('observation.comments.destroy', $comment) }}"
                                                            >
                                                                @csrf
                                                                @method ("DELETE")
                                                                <button
                                                                    type="submit"
                                                                    class="text-gray-500 hover:text-red-400"
                                                                    title="{{ __('Delete') }}"
                                                                >
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endauth
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-200 whitespace-pre-line">{{ $comment->body }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @auth
                                <form
                                    method="POST"
                                    action="{{ route('observation.comments.store') }}"
                                >
                                    @csrf
                                    <input
                                        type="hidden"
                                        name="observation_type"
                                        value="{{ $socialType }}"
                                    />
                                    <input
                                        type="hidden"
                                        name="observation_id"
                                        value="{{ $socialId }}"
                                    />
                                    <textarea
                                        name="body"
                                        rows="3"
                                        required
                                        maxlength="2000"
                                        placeholder="{{ __('Add a comment…') }}"
                                        class="w-full bg-gray-700 border border-gray-600 rounded-md text-gray-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                        >{{ old("body") }}</textarea
                                    >
                                    @error ("body")
                                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    <button
                                        type="submit"
                                        class="mt-2 text-xs px-3 py-1.5 bg-blue-700 hover:bg-blue-600 text-white rounded font-semibold"
                                    >
                                        {{
                                            __(
                                                "Post comment",
                                            )
                                        }}
                                    </button>
                                </form>
                            @else
                                <p class="text-sm text-gray-400">{{
                                    __(
                                        "Log in to add a comment.",
                                    )
                                }}</p>
                            @endauth
                        </section>
                    </div>

                    {{-- Main details card --}}
                    <div class="rounded-xl bg-gray-800 p-6 space-y-5">
                        @if ($observationType === "deepsky")
                            {{-- Deep-sky object details section --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {{-- Object --}}
                                <div>
                                    <dt
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        {{ __("Object") }}
                                    </dt>
                                    <dd class="mt-1 font-semibold">
                                        <a
                                            href="{{ route('object.show', $objectSlug) }}"
                                            class="hover:underline text-blue-300"
                                        >
                                            {{ $objectDisplayName }}
                                        </a>
                                        @if ($object)
                                            <span
                                                class="text-gray-400 text-sm ml-1"
                                                >({{
                                                    __(
                                                        $object->long_type() ?? "",
                                                    )
                                                }})</span
                                            >
                                        @endif
                                    </dd>
                                </div>

                                {{-- Constellation --}}
                                @if ($constellation)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Constellation",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ $constellation }}
                                        </dd>
                                    </div>
                                @endif

                                {{-- Location --}}
                                <div>
                                    <dt
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        {{ __("Location") }}
                                    </dt>
                                    <dd class="mt-1 font-semibold">
                                        @if ($location)
                                            <a
                                                href="/location/{{ $user?->slug ?? 'unknown' }}/{{ $location->slug }}"
                                                class="hover:underline"
                                            >
                                                {{
                                                    html_entity_decode(
                                                        $location->name,
                                                    )
                                                }}
                                            </a>
                                        @else
                                            &mdash;
                                        @endif
                                    </dd>
                                </div>

                                {{-- Instrument --}}
                                <div>
                                    <dt
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        {{
                                            __(
                                                "Instrument",
                                            )
                                        }}
                                    </dt>
                                    <dd class="mt-1 font-semibold">
                                        @if ($instrument)
                                            <a
                                                href="/instrument/{{ $user?->slug ?? 'unknown' }}/{{ $instrument->slug }}"
                                                class="hover:underline"
                                            >
                                                {!!
                                                    html_entity_decode(
                                                        $instrument->fullName(),
                                                    )
                                                !!}
                                            </a>
                                        @else
                                            &mdash;
                                        @endif
                                    </dd>
                                </div>

                                {{-- Eyepiece --}}
                                @if ($eyepiece)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{ __("Eyepiece") }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            <a
                                                href="/eyepiece/{{ $user?->slug ?? 'unknown' }}/{{ $eyepiece->slug }}"
                                                class="hover:underline"
                                            >
                                                {{
                                                    html_entity_decode(
                                                        $eyepiece->fullName(),
                                                    )
                                                }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif

                                {{-- Filter --}}
                                @if ($filterObj)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{ __("Filter") }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            <a
                                                href="/filter/{{ $user?->slug ?? 'unknown' }}/{{ $filterObj->slug }}"
                                                class="hover:underline"
                                            >
                                                {{
                                                    html_entity_decode(
                                                        $filterObj->fullName(),
                                                    )
                                                }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif

                                {{-- Lens --}}
                                @if (!empty($lens))
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{ __("Lens") }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            <a
                                                href="/lens/{{ $user?->slug ?? 'unknown' }}/{{ $lens->slug }}"
                                                class="hover:underline"
                                            >
                                                {{
                                                    html_entity_decode(
                                                        $lens->fullName(),
                                                    )
                                                }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif

                                {{-- Magnification --}}
                                @if (!empty($obs->magnification) &&
                                    $obs->magnification !== "" &&
                                    $obs->magnification !== "0")
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Magnification",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ $obs->magnification }}&times;
                                        </dd>
                                    </div>
                                @endif

                                {{-- SQM --}}
                                @if ($obs->SQM > 0)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Sky quality (SQM)",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{
                                                number_format(
                                                    $obs->SQM,
                                                    2,
                                                )
                                            }}
                                        </dd>
                                    </div>
                                @endif

                                {{-- Limiting magnitude --}}
                                @if (!empty($obs->limmag) && $obs->limmag > 0)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Limiting magnitude",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{
                                                number_format(
                                                    $obs->limmag,
                                                    1,
                                                )
                                            }}
                                        </dd>
                                    </div>
                                @endif

                                {{-- Seeing --}}
                                @if ($obs->seeing > 0)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{ __("Seeing") }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ __('observations.seeing.' . $obs->seeing) }}
                                        </dd>
                                    </div>
                                @endif

                                {{-- Transparency --}}
                                @if (!empty($obs->transparency) && $obs->transparency > 0)
                                    <div>
                                        <dt class="text-xs uppercase tracking-wide text-gray-400">{{ __("Transparency") }}</dt>
                                        <dd class="mt-1 font-semibold">{{ __('observations.transparency.' . $obs->transparency) }}</dd>
                                    </div>
                                @endif

                                {{-- Visibility --}}
                                @if (!empty($obs->visibility) && $obs->visibility > 0)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{ __("Object visibility") }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            @php
                                                $visKey = (string) ($obs->visibility ?? '');
                                                $vis = __("observations.visibility.$visKey");
                                            @endphp
                                            @if ($vis === "observations.visibility.$visKey")
                                                {{ $obs->visibility }}
                                            @else
                                                {{ $vis }}
                                            @endif
                                        </dd>
                                    </div>
                                @endif

                                {{-- Apparent size --}}
                                @if (!empty($obs->smallDiameter) && $obs->smallDiameter > 0)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Apparent size",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            @if (!empty($obs->largeDiameter) &&
                                                $obs->largeDiameter > 0 &&
                                                $obs->largeDiameter != $obs->smallDiameter)
                                                {{
                                                    number_format(
                                                        $obs->largeDiameter,
                                                        1,
                                                    )
                                                }}' &times; {{
                                                    number_format(
                                                        $obs->smallDiameter,
                                                        1,
                                                    )
                                                }}'
                                            @else
                                                {{
                                                    number_format(
                                                        $obs->smallDiameter,
                                                        1,
                                                    )
                                                }}'
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </div>
                            {{-- Object characteristics (boolean flags) --}}
                            @php
                                $chars = [];
                                if ((int) ($obs->stellar ?? -1) === 1) {
                                    $chars[] = __("Stellar");
                                }
                                if ((int) ($obs->extended ?? -1) === 1) {
                                    $chars[] = __("Extended");
                                }
                                if ((int) ($obs->resolved ?? -1) === 1) {
                                    $chars[] = __("Resolved");
                                }
                                if ((int) ($obs->mottled ?? -1) === 1) {
                                    $chars[] = __("Mottled");
                                }
                                if ((int) ($obs->unusualShape ?? -1) === 1) {
                                    $chars[] = __("Unusual shape");
                                }
                                if ((int) ($obs->partlyUnresolved ?? -1) === 1) {
                                    $chars[] = __("Partly unresolved");
                                }
                                if ((int) ($obs->colorContrasts ?? -1) === 1) {
                                    $chars[] = __("Color contrasts");
                                }
                                if ((int) ($obs->equalBrightness ?? -1) === 1) {
                                    $chars[] = __("Equal brightness");
                                }
                                if ((int) ($obs->niceField ?? -1) === 1) {
                                    $chars[] = __("Nice field");
                                }
                                if ((int) ($obs->component1 ?? -1) > 0) {
                                    $c1 = __('observations.component_colors.' . (int) $obs->component1);
                                    $chars[] = __("Component 1") . ($c1 ? ': ' . $c1 : '');
                                }
                                if ((int) ($obs->component2 ?? -1) > 0) {
                                    $c2 = __('observations.component_colors.' . (int) $obs->component2);
                                    $chars[] = __("Component 2") . ($c2 ? ': ' . $c2 : '');
                                }
                            @endphp
                            @if (!empty($chars))
                                <div>
                                    <dt
                                        class="text-xs uppercase tracking-wide text-gray-400 mb-2"
                                    >
                                        {{
                                            __(
                                                "Characteristics",
                                            )
                                        }}
                                    </dt>
                                    <dd class="flex flex-wrap gap-2">
                                        @foreach ($chars as $char)
                                            <span
                                                class="inline-block rounded-full bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200"
                                                >{{ $char }}</span
                                            >
                                        @endforeach
                                    </dd>
                                </div>
                            @endif

                        @else
                            {{-- Comet observation details section --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {{-- Comet name --}}
                                <div>
                                    <dt
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        {{ __("Comet") }}
                                    </dt>
                                    <dd class="mt-1 font-semibold">
                                        <a
                                            href="{{ route('object.show', $cometSlug) }}"
                                            class="hover:underline text-blue-300"
                                        >
                                            {{ $cometName }}
                                        </a>
                                    </dd>
                                </div>

                                {{-- Magnitude --}}
                                @if (!empty($obs->mag) && $obs->mag > -90)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Magnitude",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{
                                                number_format(
                                                    $obs->mag,
                                                    1,
                                                )
                                            }}
                                            @if ($obs->maguncertain)
                                                <span
                                                    class="text-gray-400 text-sm"
                                                    >({{ __("uncertain") }})</span
                                                >
                                            @endif
                                            @if ($obs->lessmagnitude)
                                                <span
                                                    class="text-gray-400 text-sm"
                                                    >(&lt;)</span
                                                >
                                            @endif
                                        </dd>
                                    </div>
                                @endif

                                {{-- Location --}}
                                <div>
                                    <dt
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        {{ __("Location") }}
                                    </dt>
                                    <dd class="mt-1 font-semibold">
                                        @if ($location)
                                            <a
                                                href="/location/{{ $user?->slug ?? 'unknown' }}/{{ $location->slug }}"
                                                class="hover:underline"
                                            >
                                                {{
                                                    html_entity_decode(
                                                        $location->name,
                                                    )
                                                }}
                                            </a>
                                        @else
                                            &mdash;
                                        @endif
                                    </dd>
                                </div>

                                {{-- Instrument --}}
                                <div>
                                    <dt
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        {{
                                            __(
                                                "Instrument",
                                            )
                                        }}
                                    </dt>
                                    <dd class="mt-1 font-semibold">
                                        @if ($instrument)
                                            <a
                                                href="/instrument/{{ $user?->slug ?? 'unknown' }}/{{ $instrument->slug }}"
                                                class="hover:underline"
                                            >
                                                {!!
                                                    html_entity_decode(
                                                        $instrument->fullName(),
                                                    )
                                                !!}
                                            </a>
                                        @else
                                            &mdash;
                                        @endif
                                    </dd>
                                </div>

                                {{-- Magnification --}}
                                @if (!empty($obs->magnification) &&
                                    $obs->magnification !== "" &&
                                    $obs->magnification !== "0")
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Magnification",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ $obs->magnification }}&times;
                                        </dd>
                                    </div>
                                @endif

                                {{-- Degree of condensation --}}
                                @if (!empty($obs->dc))
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Degree of condensation (DC)",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ $obs->dc }}
                                        </dd>
                                    </div>
                                @endif

                                {{-- Coma --}}
                                @if (!empty($obs->coma) && $obs->coma > -90)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Coma diameter",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ $obs->coma }}'
                                        </dd>
                                    </div>
                                @endif

                                {{-- Tail --}}
                                @if (!empty($obs->tail) && $obs->tail > -90)
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{
                                                __(
                                                    "Tail length",
                                                )
                                            }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ $obs->tail }}'
                                            @if (!empty($obs->pa) && $obs->pa > -90)
                                                &nbsp;/ {{ __("PA") }} {{ $obs->pa }}&deg;
                                            @endif
                                        </dd>
                                    </div>
                                @endif

                                {{-- Method / chart --}}
                                @if (!empty($obs->methode))
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{ __("Method") }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ $obs->methode }}
                                        </dd>
                                    </div>
                                @endif

                                @if (!empty($obs->chart))
                                    <div>
                                        <dt
                                            class="text-xs uppercase tracking-wide text-gray-400"
                                        >
                                            {{ __("Chart") }}
                                        </dt>
                                        <dd class="mt-1 font-semibold">
                                            {{ $obs->chart }}
                                        </dd>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Moon during observation (shared: deepsky + comet) --}}
                        @if ($moon)
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 border-t border-gray-700 pt-5">
                                <div>
                                    <dt
                                        class="text-xs uppercase tracking-wide text-gray-400"
                                    >
                                        {{ __('Moon during observation') }}
                                    </dt>
                                    <dd class="mt-1">
                                        <div class="flex items-start gap-3">
                                            @if (!empty($moon['frame_path']) && !is_null($moon['frame_index'] ?? null) && file_exists(public_path('images/moon/m' . $moon['frame_index'] . '.gif')))
                                                <img
                                                    src="{{ $moon['frame_path'] }}"
                                                    alt="{{ __('Moon phase') }}"
                                                    class="h-14 w-14 flex-shrink-0 object-contain"
                                                    loading="lazy"
                                                />
                                            @endif

                                            <div class="min-w-0 text-sm">
                                                <div class="font-semibold text-gray-100">
                                                    {{ $moon['phase_name'] ?? __('Moon phase') }}
                                                </div>
                                                <div class="text-gray-300">
                                                    @if (!is_null($moon['illumination_percent'] ?? null))
                                                        {{ $moon['illumination_percent'] }}% {{ __('illuminated') }}
                                                    @else
                                                        {{ __('Illumination unavailable') }}
                                                    @endif
                                                </div>

                                                @if (($moon['has_specific_time'] ?? false) && ($moon['horizon_status'] ?? 'unknown') === 'above')
                                                    <div class="mt-1 text-emerald-300">
                                                        {{ __('Above the horizon') }}
                                                        @if (!is_null($moon['altitude_degrees'] ?? null))
                                                            ({{ number_format($moon['altitude_degrees'], 1) }}&deg;)
                                                        @endif
                                                    </div>
                                                @elseif (($moon['has_specific_time'] ?? false) && ($moon['horizon_status'] ?? 'unknown') === 'below')
                                                    <div class="mt-1 text-amber-300">
                                                        {{ __('Not above the horizon') }}
                                                        @if (!is_null($moon['altitude_degrees'] ?? null))
                                                            ({{ number_format($moon['altitude_degrees'], 1) }}&deg;)
                                                        @endif
                                                    </div>
                                                @elseif ($moon['has_specific_time'] ?? false)
                                                    <div class="mt-1 text-gray-400">
                                                        {{ __('Moon altitude unavailable') }}
                                                    </div>
                                                @else
                                                    <div class="mt-1 text-gray-400">
                                                        {{ __('No observation time recorded') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                            </div>
                        @endif

                        {{-- Description / notes --}}
                        @if (!empty($obs->description) && $obs->description !== "")
                            <div class="border-t border-gray-700 pt-5">
                                <dt
                                    class="text-xs uppercase tracking-wide text-gray-400 mb-2"
                                >
                                    {{ __("Notes") }}
                                </dt>
                                <div
                                    class="rounded-md bg-gray-900 px-4 py-4 text-sm leading-relaxed"
                                >
                                    @if ($tr)
                                        @php
                                            $cacheKey =
                                                "obs_detail_translation:" .
                                                $observationType .
                                                ":" .
                                                $obs->id .
                                                ":" .
                                                auth()->user()->language;
                                            $translatedDesc = \Illuminate\Support\Facades\Cache::remember(
                                                $cacheKey,
                                                60 * 24 * 30,
                                                function () use ($obs, $tr) {
                                                    try {
                                                        return $tr->translate(html_entity_decode($obs->description));
                                                    } catch (\Throwable $e) {
                                                        return null;
                                                    }
                                                },
                                            );
                                        @endphp
                                        {!!
                                            $translatedDesc ??
                                                html_entity_decode($obs->description)
                                        !!}
                                    @else
                                        {!!
                                            html_entity_decode(
                                                $obs->description,
                                            )
                                        !!}
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Action row --}}
                    <div class="flex items-center gap-3 mt-6">
                        @if (auth()->check())
                            @php
                                $msgSubject =
                                    $observationType === "deepsky"
                                        ? "About your observation of " . ($obs->objectname ?? "")
                                        : "About your observation of " . ($cometName ?? "");
                                $toUser = $user?->username ?? "";
                            @endphp
                            @if ($toUser)
                                <a
                                    href="{{ route('messages.create', ['to' => $toUser, 'subject' => $msgSubject]) }}"
                                    class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                                        <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                                    </svg>
                                    {{
                                        __(
                                            "Send message",
                                        )
                                    }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <aside class="mt-8 lg:mt-0">
                    <div class="rounded-xl bg-gray-800 p-4 lg:sticky lg:top-6">
                        <div class="mt-2 mb-3">
                            <div class="text-sm font-medium text-gray-300">{{ $objectDisplayName }}</div>
                        </div>

                        <h2
                            class="text-sm font-semibold uppercase tracking-wide text-gray-300 mb-3"
                        >
                            {{
                                __(
                                    "Quick links",
                                )
                            }}
                        </h2>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <a
                                    href="{{ $objectUrl }}"
                                    class="text-blue-300 hover:underline"
                                >
                                    {{
                                        __(
                                            "Object page",
                                        )
                                    }}
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ $allObservationsUrl }}"
                                    class="text-gray-200 hover:underline"
                                >
                                    {{
                                        __(
                                            "All observations",
                                        )
                                    }}
                                </a>
                            </li>
                            @if ($myObservationsUrl)
                                <li>
                                    <a
                                        href="{{ $myObservationsUrl }}"
                                        class="text-gray-200 hover:underline"
                                    >
                                        {{
                                            __(
                                                "My observations",
                                            )
                                        }}
                                    </a>
                                </li>
                            @endif
                            @if ($compareMyLastUrl)
                                <li>
                                    <a
                                        href="{{ $compareMyLastUrl }}"
                                        class="text-gray-200 hover:underline"
                                    >
                                        {{
                                            __(
                                                "Compare with my last observation",
                                            )
                                        }}
                                    </a>
                                </li>
                            @endif
                        </ul>

                        {{-- object name moved above quick links; duplicate removed --}}

                        {{-- Observing list actions --}}
                        @auth
                            <ul class="mt-4 pt-3 border-t border-gray-700 space-y-2 text-sm">
                                @livewire(
                                    'observing-list-toggle',
                                    [
                                        'objectName'             => $objectDisplayName,
                                        'observationDescription' => $obs->description ?? null,
                                        'observationId'          => (int) $obs->id,
                                        'showToggle'             => true,
                                        'showNote'               => false,
                                    ],
                                    key('ol-toggle-obs-' . $obs->id)
                                )
                            </ul>
                        @endauth

                        {{-- Admin actions: allow adding observation as sketch of week/month --}}
                        @if (
                            auth()->check() &&
                            (
                                auth()->user()->isAdministrator() ||
                                auth()->user()->isDatabaseExpert()
                            )
                        )
                            <div class="mt-4 border-t border-gray-700 pt-3">
                                <h3 class="text-sm font-semibold text-gray-300 mb-2">{{ __('Admin actions') }}</h3>
                                @php
                                    $unifiedId = $unified_observation_id ?? null;
                                @endphp

                                @if ($unifiedId && ($obs->hasDrawing ?? $obs->has_drawing ?? false))
                                    @if (empty($sketchOfTheWeek))
                                        <form method="POST" action="{{ route('sketch-of-the-week.store') }}" class="mb-2">
                                            @csrf
                                            <input type="hidden" name="observation_id" value="{{ $unifiedId }}" />
                                            <input type="hidden" name="date" value="{{ \Carbon\Carbon::now()->toDateString() }}" />
                                            <button type="submit" class="w-full text-left px-3 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded text-sm">{{ __('Add as sketch of the week') }}</button>
                                        </form>
                                    @elseif (auth()->user()->isAdministrator())
                                        <form method="POST" action="{{ route('sketch-of-the-week.destroy', $sketchOfTheWeek->id) }}" class="mb-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left px-3 py-2 bg-red-700 hover:bg-red-600 text-white rounded text-sm">{{ __('Remove as sketch of the week') }}</button>
                                        </form>
                                    @endif

                                    @if (empty($sketchOfTheMonth))
                                        <form method="POST" action="{{ route('sketch-of-the-month.store') }}">
                                            @csrf
                                            <input type="hidden" name="observation_id" value="{{ $unifiedId }}" />
                                            <input type="hidden" name="date" value="{{ \Carbon\Carbon::now()->toDateString() }}" />
                                            <button type="submit" class="w-full text-left px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded text-sm">{{ __('Add as sketch of the month') }}</button>
                                        </form>
                                    @elseif (auth()->user()->isAdministrator())
                                        <form method="POST" action="{{ route('sketch-of-the-month.destroy', $sketchOfTheMonth->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left px-3 py-2 bg-red-700 hover:bg-red-600 text-white rounded text-sm">{{ __('Remove as sketch of the month') }}</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
