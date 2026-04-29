{{-- Avoid inline `use` in Blade views; use fully-qualified class names instead --}}
@props ([
    "observation_id" => null,
    "drawing_id" => null,
    "unified_observation" => null,
    "observer_name" => null,
    "observer_username" => null,
    "observer_slug" => null,
    "observation_date" => null,
    "sketch_date" => null,
    "use_sketch_date" => false,
    "observation" => null,
    "object" => null,
    "likes_count" => null,
    "liked" => null
])
<div>
    @php
        // Use preloaded data if available, otherwise fetch from DB (fallback for backward compatibility)
        $obsUnified = $unified_observation;
        $obsLegacy = $observation ?? \App\Models\ObservationsOld::find($observation_id);

        $objectName =
            $obsUnified?->target_name ?? ($obsLegacy?->objectname ?? __("Unknown"));
        $objectModel =
            $object ??
            ($objectName
                ? \App\Models\ObjectsOld::where("name", $objectName)->first()
                : null);
        $slug =
            $objectModel?->slug ??
            \Illuminate\Support\Str::slug($objectName ?? "", "-");
        $objectUrl = route("object.show", $slug);

        $resolvedObserverUsername =
            $obsUnified?->observer_username ?:
            ($observer_username ?:
            $obsLegacy?->observerid ?? "");
        $resolvedObserverSlug = $observer_slug;
        if (!$resolvedObserverSlug && $resolvedObserverUsername) {
            $resolvedObserver = \App\Models\User::whereRaw("LOWER(username) = ?", [
                mb_strtolower((string) $resolvedObserverUsername),
            ])->first();
            $resolvedObserverSlug = $resolvedObserver?->slug;
        }
        $observerUrl = route(
            "observer.show",
            $resolvedObserverSlug ?: $resolvedObserverUsername,
        );

        $detailUrl = null;
        // Base detailDate used for routing (defaults to supplied observation_date)
        $detailDate = $observation_date;
        // displayDate is shown to the user; may be overridden with sketch selection date
        $displayDate = !empty($detailDate)
            ? \Carbon\Carbon::parse($detailDate)->translatedFormat("j M Y")
            : "";

        if (!empty($use_sketch_date) && !empty($sketch_date)) {
            try {
                $displayDate = \Carbon\Carbon::parse($sketch_date)->translatedFormat("j M Y");
            } catch (\Throwable $_) {
            }
        }

        if ($obsUnified) {
            // For routing we still need the observation's actual observed_on date
            $detailDate =
                $obsUnified->observed_on instanceof \Carbon\Carbon
                    ? $obsUnified->observed_on->format("Y-m-d")
                    : (string) $obsUnified->observed_on;
            // Only update displayDate with the observation date when not explicitly showing sketch selection date
            if (empty($use_sketch_date) || empty($sketch_date)) {
                $displayDate = !empty($detailDate)
                    ? \Carbon\Carbon::parse($detailDate)->translatedFormat("j M Y")
                    : $displayDate;
            }

            $observerSegment =
                $resolvedObserverSlug ?: ((string) $obsUnified->observer_username);
            $objectSegment =
                $objectModel?->slug ??
                \Illuminate\Support\Str::slug(
                    (string) ($obsUnified->target_name ?? $objectName),
                    "-",
                );

            $matchingIds = \App\Models\Observation::where(
                "observer_username",
                $obsUnified->observer_username,
            )
                ->whereDate("observed_on", $detailDate)
                ->where("target_name", $obsUnified->target_name)
                ->orderBy("observed_time")
                ->orderBy("id")
                ->pluck("id")
                ->map(fn($id) => (int) $id)
                ->values();

            $position = $matchingIds->search((int) $obsUnified->id);
            $occurrenceIndex = $position === false ? 1 : (int) $position + 1;

            $detailRouteParams = [
                "observer" => $observerSegment,
                "date" => $detailDate,
                "object" => $objectSegment,
            ];
            if ($occurrenceIndex > 1) {
                $detailRouteParams["index"] = $occurrenceIndex;
            }

            $detailUrl = route("observations.detail", $detailRouteParams);
        } elseif ($obsLegacy) {
            $detailDate =
                substr((string) $obsLegacy->date, 0, 4) .
                "-" .
                substr((string) $obsLegacy->date, 4, 2) .
                "-" .
                substr((string) $obsLegacy->date, 6, 2);
            $displayDate = \Carbon\Carbon::create($detailDate)->translatedFormat(
                "j M Y",
            );
            $observerSegment =
                $resolvedObserverSlug ?: ((string) $obsLegacy->observerid);
            $objectSegment =
                $objectModel?->slug ??
                \Illuminate\Support\Str::slug((string) $obsLegacy->objectname, "-");

            $matchingIds = \App\Models\ObservationsOld::where(
                "observerid",
                $obsLegacy->observerid,
            )
                ->where("date", $obsLegacy->date)
                ->where("objectname", $obsLegacy->objectname)
                ->orderBy("time")
                ->orderBy("id")
                ->pluck("id")
                ->map(fn($id) => (int) $id)
                ->values();

            $position = $matchingIds->search((int) $obsLegacy->id);
            $occurrenceIndex = $position === false ? 1 : (int) $position + 1;

            $detailRouteParams = [
                "observer" => $observerSegment,
                "date" => $detailDate,
                "object" => $objectSegment,
            ];
            if ($occurrenceIndex > 1) {
                $detailRouteParams["index"] = $occurrenceIndex;
            }

            $detailUrl = route("observations.detail", $detailRouteParams);
        }

        // Use preloaded likes data if available, otherwise query (fallback)
        if ($likes_count !== null && $liked !== null) {
            $likesCount = $likes_count;
            $isLiked = $liked;
        } else {
            $likesCount = \App\Models\ObservationLike::where(
                "observation_type",
                "deepsky",
            )
                ->where("observation_id", $observation_id)
                ->count();
            $isLiked =
                auth()->check() &&
                \App\Models\ObservationLike::where("observation_type", "deepsky")
                    ->where("observation_id", $observation_id)
                    ->where("user_id", auth()->id())
                    ->exists();
        }
    @endphp
    <a href="{{ $objectUrl }}">
        <img
            width="400"
            src="/images/{{ $drawing_id ?: ('drawings/' . $observation_id . '.jpg') }}"
        />
    </a>

    <div class="text-center mt-1">
        <a
            class="font-semibold hover:underline"
            href="{{ $observerUrl }}"
            >{{ $observer_name }}</a
        >
        -
        <a
            class="font-semibold hover:underline"
            href="{{ $objectUrl }}"
            >{{ $objectName }}</a
        >
        -
        @if ($detailUrl)
            <a class="font-semibold hover:underline" href="{{ $detailUrl }}">
                {{ $displayDate }}
            </a>
        @else
            {{ $displayDate }}
        @endif
    </div>

    <div class="text-center mt-2 mb-3 flex items-center justify-center gap-3">
        <div>
            {!!
                ShareButtons::page(
                    $objectUrl,
                    __("Look at this sketch of ") .
                        ($objectName ?? __("Unknown")) .
                        __(" by ") .
                        $observer_name .
                        __(" on #deepskylog"),
                    [
                        "title" => __("Share this sketch"),
                        "class" => "text-gray-500 hover:text-gray-700",
                        "rel" => "nofollow noopener noreferrer",
                    ],
                )
                    ->facebook(["class" => "hover", "rel" => "follow"])
                    ->twitter(["class" => "hover", "rel" => "follow"])
                    ->copylink()
                    ->mailto(["class" => "hover", "rel" => "nofollow"])
                    ->whatsapp()
                    ->render()
            !!}
        </div>

        <button
            data-observation-type="deepsky"
            data-observation-id="{{ $observation_id }}"
            class="like-button px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-white"
        >
            <span class="like-icon">{!!
                $isLiked
                    ? "❤️"
                    : "👍"
            !!}</span>
            <span class="like-count">{{ $likesCount }}</span>
        </button>

        @php
            $messageSubject = __("About your sketch of :object", ["object" => $objectName]);
        @endphp

        @auth
            <a
                href="{{ route('messages.create', ['to' => $observer_username ?? $observer_name, 'subject' => $messageSubject]) }}"
                class="inline-flex items-center p-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                aria-label="{{ __('Send message about this sketch') }}"
            >
                {{-- envelope icon --}}
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                >
                    <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                    <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                </svg>
            </a>
        @endauth
    </div>
</div>
