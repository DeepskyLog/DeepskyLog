@php use App\Models\SketchOfTheWeek; @endphp
@php use App\Models\SketchOfTheMonth; @endphp
@php use App\Models\User; @endphp
@php use App\Models\ObservationSession; @endphp
@php use Illuminate\Support\Facades\Cache; @endphp
<x-app-layout>
    <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
        <x-card>
            <div
                class="flex justify-center py-4 dark:bg-gray-800 sm:items-center sm:pt-0"
            >
                <x-application-logo class="block h-12 w-auto" />
            </div>
            @guest
                <div>
                    {{ __("Welcome to DeepskyLog!") }}
                    {!! __(
                        "DeepskyLog is an extended, comprehensive and free database for deepsky objects and has been developed by the Deepsky section of the Astronomical Society of Belgium (:linkVereniging Voor Sterrenkunde (VVS):close_link).",
                        ["link" => "<a href='https://www.vvs.be'>", "close_link" => "</a>"]
                    ) !!}
                    {{ __("The database is open for consultation and already contains tens of thousands observations and thousands of sketches and drawings made by amateur astronomers around the world.") }}
                    <br />
                    {!! __(
                        "To start recording your observations and share your observations with other observers, you are kindly requested to :register_linkregister:close_link to DeepskyLog. Your personal data will be handled in accordance with our :privacy_linkprivacy policy:close_link.",
                        [
                            "register_link" => "<a href='" . route("register") . "'>",
                            "privacy_link" => "<a href='" . route("privacy") . "'>",
                            "close_link" => "</a>",
                        ]
                    ) !!}
                    <br />
                    {{ __("You can consult and create observing lists with different deepsky objects and see suggestions for objects visible in your instrument and from your observation sites. You can create your own file with maps and DSS images of the objects and have access to an interactive and detailed free star atlas. You can also consult the observations and sketches of other observers and share your own observations and sketches with the community.") }}
                    <br />
                    {!! __(
                        "Please contact the :linkDeepskyLog developers:close_link if you encounter problems or have questions.",
                        ["link" => '<a href="mailto:deepskylog@groups.io">', "close_link" => "</a>"]
                    ) !!}
                </div>
            @endguest
        </x-card>

        {{-- Latest sketch of the month / week --}}
        <div class="flex flex-col sm:flex-row sm:gap-4">
            <div class="mt-4 rounded-xl">
                <h2 class="mb-3 ml-3 mt-3 text-xl font-semibold leading-tight">
                    {{
                        __(
                            "DeepskyLog Sketch of the Week",
                        )
                    }}
                </h2>
                <x-card>
                    <div class="flex justify-center">
                        <div class="flex flex-wrap">
                            <x-sketch
                                :sketch="
                                    Cache::remember('welcome_sketch_week', 3600, function () {
        return SketchOfTheWeek::orderBy('date', 'desc')->first();
    })
                                "
                            />
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="mt-4 rounded-xl">
                <h2 class="mb-3 ml-3 mt-3 text-xl font-semibold leading-tight">
                    {{
                        __(
                            "DeepskyLog Sketch of the Month",
                        )
                    }}
                </h2>
                <x-card>
                    <div class="flex justify-center">
                        <div class="flex flex-wrap">
                            <x-sketch
                                :sketch="
                                    Cache::remember('welcome_sketch_month', 3600, function () {
        return SketchOfTheMonth::orderBy('date', 'desc')->first();
    })
                                "
                            />
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        {{-- Five newest active sessions --}}
        @isset ($sessions)
            <div class="pt-4">
                <h2 class="ml-3 mt-3 text-xl font-semibold leading-tight">
                    {{
                        __(
                            "Newest sessions",
                        )
                    }}
                </h2>
                @php
                    // $sessions is prepared in the homepage route (SessionController::homepage)
                @endphp

                <div class="mt-2">
                    <x-card>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-2"
                        >
                            @foreach ($sessions as $session)
                                @php
                                    // Use a slightly lighter background for each session card (no borders)
                                    if ($loop->iteration === 1) {
                                        $bgClass = "bg-gray-700";
                                    } elseif ($loop->iteration === 2) {
                                        $bgClass = "bg-gray-700";
                                    } elseif ($loop->iteration === 3) {
                                        $bgClass = "bg-gray-700";
                                    } elseif ($loop->iteration === 4) {
                                        $bgClass = "bg-gray-700";
                                    } else {
                                        $bgClass = "bg-gray-700";
                                    }
                                @endphp
                                <article class="{{ $bgClass }} p-4 rounded">
                                    @if (!empty($session->preview))
                                        <div class="mb-3">
                                            @php
                                                $sessionUser =
                                                    optional($session->observer)->slug ?? ($session->observerid ?? null);
                                                $sessionParam = $session->slug ?? ($session->id ?? null);
                                            @endphp
                                            @if ($sessionUser && $sessionParam)
                                                <a
                                                    href="{{ route('session.show', [$sessionUser, $sessionParam]) }}"
                                                >
                                                    <img
                                                        src="{{ $session->preview }}"
                                                        alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}"
                                                        class="w-full h-40 object-cover rounded"
                                                    />
                                                </a>
                                            @else
                                                <img
                                                    src="{{ $session->preview }}"
                                                    alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}"
                                                    class="w-full h-40 object-cover rounded"
                                                />
                                            @endif
                                        </div>
                                    @endif

                                    <h3
                                        class="text-lg font-bold text-white mb-2"
                                    >
                                        @php
                                            // Ensure both route parameters exist before building the URL to avoid missing parameter errors
                                            $sessionUser =
                                                optional($session->observer)->slug ?? ($session->observerid ?? null);
                                            $sessionParam = $session->slug ?? ($session->id ?? null);
                                        @endphp
                                        @if ($sessionUser && $sessionParam)
                                            <a
                                                href="{{ route('session.show', [$sessionUser, $sessionParam]) }}"
                                                class="hover:underline"
                                                >{{
                                                    html_entity_decode(
                                                        $session->name ?? __("Session :id", ["id" => $session->id]),
                                                        ENT_QUOTES | ENT_HTML5,
                                                        "UTF-8",
                                                    )
                                                }}</a
                                            >
                                        @else
                                            <span class="hover:underline">{{
                                                html_entity_decode(
                                                    $session->name ?? __("Session :id", ["id" => $session->id]),
                                                    ENT_QUOTES | ENT_HTML5,
                                                    "UTF-8",
                                                )
                                            }}</span>
                                        @endif
                                    </h3>

                                    <div class="text-sm text-gray-400 mb-2">
                                        <span
                                            class="mr-2 font-medium text-gray-200"
                                            >{{ __("Owner") }}:</span
                                        >
                                        @if ($session->observer)
                                            <a
                                                href="{{ route('observer.show', optional($session->observer)->slug ?? $session->observerid) }}"
                                                class="text-gray-400 hover:underline"
                                                >{{
                                                    optional($session->observer)->name ??
                                                        $session->observerid
                                                }}</a
                                            >
                                        @else
                                            <span
                                                class="text-gray-400"
                                                >{{ $session->observerid }}</span
                                            >
                                        @endif
                                    </div>

                                    <div class="text-sm text-gray-400 mb-3">
                                        <span>{{
                                            $session->begindate
                                                ? \Carbon\Carbon::parse($session->begindate)->translatedFormat("j M Y")
                                                : __("Unknown")
                                        }}</span>
                                        <span class="mx-2">&ndash;</span>
                                        <span>{{
                                            $session->enddate
                                                ? \Carbon\Carbon::parse($session->enddate)->translatedFormat("j M Y")
                                                : __("Unknown")
                                        }}</span>
                                    </div>

                                    <div class="text-sm text-gray-400 mb-2">
                                        <span
                                            class="mr-2 font-medium text-gray-200"
                                            >{{ __("Location") }}:</span
                                        >
                                        <span class="text-gray-400">{{
                                            $session->location_name ??
                                                __("Unknown")
                                        }}</span>
                                    </div>

                                    @if (isset($session->observation_count))
                                        <div class="text-sm text-gray-300 mb-2">
                                            {{ __("Observations") }}:
                                            <strong
                                                class="text-white"
                                                >{{ $session->observation_count }}</strong
                                            >
                                        </div>
                                    @endif

                                    <p class="text-sm text-gray-300 mb-3">{{ $session->preview_text ?? \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($session->comments ?? "", ENT_QUOTES | ENT_HTML5, "UTF-8")), 180) }}</p>
                                    <div
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <div class="text-gray-400">
                                            {{ __("Observers") }}: {{
                                                $session->otherObserversCount() ??
                                                    1
                                            }}
                                        </div>
                                        @php
                                            $sessionUser =
                                                optional($session->observer)->slug ?? ($session->observerid ?? null);
                                            $sessionParam = $session->slug ?? ($session->id ?? null);
                                        @endphp
                                        @if ($sessionUser && $sessionParam)
                                            <a
                                                href="{{ route('session.show', [$sessionUser, $sessionParam]) }}"
                                                class="text-blue-500 hover:underline"
                                                >{{
                                                    __(
                                                        "Read more",
                                                    )
                                                }}</a
                                            >
                                        @else
                                            <span class="text-blue-500">{{
                                                __(
                                                    "Read more",
                                                )
                                            }}</span>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $sessions->links() }}</div>
                    </x-card>
                </div>
            </div>
        @endisset

        {{-- Latest sketches from the unified observations table --}}
        <div class="pt-4">
            <h2 class="ml-3 mt-3 text-xl font-semibold leading-tight">
                {{ __("20 newest sketches") }}
            </h2>
            @php
                $sketchType = request()->get("sketch_type", "all");
                $allowedSketchTypes = ["all", "deepsky", "comet", "solarsystem"];
                if (!in_array($sketchType, $allowedSketchTypes)) {
                    $sketchType = "all";
                }
            @endphp
            <div class="ml-3 mt-2 flex flex-wrap gap-2">
                @foreach (["all" => __("All"), "deepsky" => __("Deep-sky"), "comet" => __("Comets"), "solarsystem" => __("Solar system")] as $typeKey => $typeLabel)
                    @php
                        $params = array_merge(request()->except(["sketch_type", "sketches"]), $typeKey !== "all" ? ["sketch_type" => $typeKey] : []);
                        $url = url("/") . ($params ? "?" . http_build_query($params) : "");
                        $isActive = ($sketchType ?? 'all') === $typeKey;
                    @endphp
                    <a href="{{ $url }}"
                       class="rounded px-3 py-1 text-sm font-medium {{ $isActive ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600' }}"
                    >{{ $typeLabel }}</a>
                @endforeach
            </div>
            @php
                $page = request()->get("sketches", 1);
                $cacheKey = "welcome_sketches_page_" . $sketchType . "_" . $page;
                $sketches = Cache::remember($cacheKey, 300, function () use ($page, $sketchType) {
                    $query = \App\Models\Observation::query()
                        ->where("has_drawing", true);
                    if ($sketchType === "all") {
                        // include both legacy 'solarsystem' and normalized 'planet' and 'lunar'
                        $query->whereIn("target_type", ["deepsky", "comet", "planet", "solarsystem", "lunar"]);
                    } else {
                        if ($sketchType === 'solarsystem') {
                            $query->whereIn("target_type", ["planet", "solarsystem", "lunar"]);
                        } else {
                            $query->where("target_type", $sketchType);
                        }
                    }
                    return $query
                        ->orderByDesc("observed_on")
                        ->orderByDesc("id")
                        ->paginate(20, ["*"], "sketches", $page)
                        ->appends(request()->except("sketches"));
                });

                $observerIds = $sketches->pluck("observer_username")
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $observerUsers = User::whereIn("username", $observerIds)
                    ->get()
                    ->keyBy("username");

                $deepSkyObjectNames = $sketches->where("target_type", "deepsky")
                    ->pluck("target_name")
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $objects = empty($deepSkyObjectNames)
                    ? collect()
                    : \App\Models\Target::whereIn("name", $deepSkyObjectNames)
                        ->get()
                        ->keyBy("name");

                $cometObjectIds = $sketches->where("target_type", "comet")
                    ->pluck("target_key")
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $preloadedSketchComets = empty($cometObjectIds)
                    ? collect()
                    : \App\Models\CometObject::whereIn("id", $cometObjectIds)
                        ->get()
                        ->keyBy("id");

                $deepSkySketchIds = $sketches->where("target_type", "deepsky")
                    ->pluck("id")
                    ->all();
                $deepSkyLikesCounts = empty($deepSkySketchIds)
                    ? collect()
                    : \App\Models\ObservationLike::where("observation_type", "deepsky")
                        ->whereIn("observation_id", $deepSkySketchIds)
                        ->select("observation_id", \DB::raw("count(*) as count"))
                        ->groupBy("observation_id")
                        ->pluck("count", "observation_id");
                $deepSkyUserLikes = auth()->check() && !empty($deepSkySketchIds)
                    ? \App\Models\ObservationLike::where("observation_type", "deepsky")
                        ->whereIn("observation_id", $deepSkySketchIds)
                        ->where("user_id", auth()->id())
                        ->pluck("observation_id")
                        ->flip()
                    : collect();
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @foreach ($sketches as $sketch)
                            @php
                                $observerUser = $observerUsers[$sketch->observer_username] ?? null;
                                $observer_name = $observerUser?->name ?? $sketch->observer_username;
                                $observation_date = optional($sketch->observed_on)?->format("Y-m-d");
                            @endphp
                            @if ($sketch->target_type === "comet")
                                <div class="flex flex-col pr-4">
                                    <x-sketch-comet
                                        :observation_id="$sketch->id"
                                        :unified_observation="$sketch"
                                        :observer_name="$observer_name"
                                        :observer_username="$sketch->observer_username"
                                        :observer_slug="$observerUser?->slug"
                                        :observation_date="$observation_date"
                                        :preloaded_comet="$preloadedSketchComets[$sketch->target_key] ?? null"
                                    />
                                </div>
                            @else
                                <div class="flex flex-col pr-4">
                                    <x-sketch-deepsky
                                        :observation_id="$sketch->id"
                                        :unified_observation="$sketch"
                                        :observer_name="$observer_name"
                                        :observer_username="$sketch->observer_username"
                                        :observer_slug="$observerUser?->slug"
                                        :observation_date="$observation_date"
                                        :object="$objects[$sketch->target_name] ?? null"
                                        :likes_count="$deepSkyLikesCounts[$sketch->id] ?? 0"
                                        :liked="$deepSkyUserLikes->has($sketch->id)"
                                    />
                                </div>
                            @endif
                        @endforeach
                    </div>
                    {{ $sketches->links() }}
                </x-card>
            </div>
        </div>

        {{-- Latest observations from the unified observations table --}}
        <div class="pt-4">
            <h2 class="ml-3 mt-3 text-xl font-semibold leading-tight">
                {{ __("20 newest observations") }}
            </h2>
            @php
                $obsType = request()->get("obs_type", "all");
                $allowedObsTypes = ["all", "deepsky", "comet", "solarsystem"];
                if (!in_array($obsType, $allowedObsTypes)) {
                    $obsType = "all";
                }
            @endphp
            <div class="ml-3 mt-2 flex flex-wrap gap-2">
                @foreach (["all" => __("All"), "deepsky" => __("Deep-sky"), "comet" => __("Comets"), "solarsystem" => __("Solar system")] as $typeKey => $typeLabel)
                    @php
                        $params = array_merge(request()->except(["obs_type", "observations"]), $typeKey !== "all" ? ["obs_type" => $typeKey] : []);
                        $url = url("/") . ($params ? "?" . http_build_query($params) : "");
                        $isActive = $obsType === $typeKey;
                    @endphp
                    <a href="{{ $url }}"
                       class="rounded px-3 py-1 text-sm font-medium {{ $isActive ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600' }}"
                    >{{ $typeLabel }}</a>
                @endforeach
            </div>
            @php
                $page = request()->get("observations", 1);
                $cacheKey = "welcome_observations_page_" . $obsType . "_" . $page;
                $observations = Cache::remember($cacheKey, 300, function () use ($page, $obsType) {
                    $query = \App\Models\Observation::query();
                    if ($obsType === "all") {
                        // include both legacy 'solarsystem' and normalized 'planet' and 'lunar'
                        $query->whereIn("target_type", ["deepsky", "comet", "planet", "solarsystem", "lunar"]);
                    } else {
                        if ($obsType === 'solarsystem') {
                            $query->whereIn("target_type", ["planet", "solarsystem", "lunar"]);
                        } else {
                            $query->where("target_type", $obsType);
                        }
                    }
                    return $query
                        ->orderByDesc("observed_on")
                        ->orderByDesc("id")
                        ->paginate(20, ["*"], "observations", $page)
                        ->appends(request()->except("observations"));
                });

                $observerIds = $observations->pluck("observer_username")
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $preloadedUsers = \App\Models\User::whereIn("username", $observerIds)
                    ->get()
                    ->keyBy("username");

                $deepSkyObjectNames = $observations->where("target_type", "deepsky")
                    ->pluck("target_name")
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $preloadedObjects = empty($deepSkyObjectNames)
                    ? collect()
                    : \App\Models\Target::whereIn("name", $deepSkyObjectNames)
                        ->get()
                        ->keyBy("name");

                $allLocationIds = $observations->pluck("location_id")
                    ->filter(fn($id) => (int) $id > 0)
                    ->unique()
                    ->values()
                    ->all();
                $preloadedLocations = empty($allLocationIds)
                    ? collect()
                    : \App\Models\Location::whereIn("id", $allLocationIds)
                        ->get()
                        ->keyBy("id");

                $allInstrumentIds = $observations->pluck("instrument_id")
                    ->filter(fn($id) => (int) $id > 0)
                    ->unique()
                    ->values()
                    ->all();
                $preloadedInstruments = empty($allInstrumentIds)
                    ? collect()
                    : \App\Models\Instrument::whereIn("id", $allInstrumentIds)
                        ->get()
                        ->keyBy("id");

                $eyepieceIds = $observations->pluck("eyepiece_id")
                    ->filter(fn($id) => (int) $id > 0)
                    ->unique()
                    ->values()
                    ->all();
                $preloadedEyepieces = empty($eyepieceIds)
                    ? collect()
                    : \App\Models\Eyepiece::whereIn("id", $eyepieceIds)
                        ->get()
                        ->keyBy("id");

                $filterIds = $observations->pluck("filter_id")
                    ->filter(fn($id) => (int) $id > 0)
                    ->unique()
                    ->values()
                    ->all();
                $preloadedFilters = empty($filterIds)
                    ? collect()
                    : \App\Models\Filter::whereIn("id", $filterIds)
                        ->get()
                        ->keyBy("id");

                $lensIds = $observations->pluck("lens_id")
                    ->filter(fn($id) => (int) $id > 0)
                    ->unique()
                    ->values()
                    ->all();
                $preloadedLenses = empty($lensIds)
                    ? collect()
                    : \App\Models\Lens::whereIn("id", $lensIds)
                        ->get()
                        ->keyBy("id");

                $conIds = $preloadedObjects->pluck("con")
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $preloadedConstellations = empty($conIds)
                    ? collect()
                    : \App\Models\Constellation::whereIn("id", $conIds)
                        ->get()
                        ->keyBy("id");

                $cometObjectIds = $observations->where("target_type", "comet")
                    ->pluck("target_key")
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
                $preloadedComets = empty($cometObjectIds)
                    ? collect()
                    : \App\Models\CometObject::whereIn("id", $cometObjectIds)
                        ->get()
                        ->keyBy("id");

                $tr = null;
                if (auth()->check() && auth()->user()->translate) {
                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(
                        auth()->user()->language,
                    );
                }
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="grid-cols-1 px-5">
                        @foreach ($observations as $observation)
                            @if ($observation->target_type === "comet")
                                <x-observation-comet
                                    :observation="$observation"
                                    :preloaded_user="$preloadedUsers[$observation->observer_username] ?? null"
                                    :preloaded_comet="$preloadedComets[$observation->target_key] ?? null"
                                    :preloaded_location="$preloadedLocations[$observation->location_id] ?? null"
                                    :preloaded_instrument="$preloadedInstruments[$observation->instrument_id] ?? null"
                                />
                            @else
                                <x-observation-deepsky
                                    :observation="$observation"
                                    :translator="$tr"
                                    :preloaded_user="$preloadedUsers[$observation->observer_username] ?? null"
                                    :preloaded_object="$preloadedObjects[$observation->target_name] ?? null"
                                    :preloaded_location="$preloadedLocations[$observation->location_id] ?? null"
                                    :preloaded_instrument="$preloadedInstruments[$observation->instrument_id] ?? null"
                                    :preloaded_eyepiece="$preloadedEyepieces[$observation->eyepiece_id] ?? null"
                                    :preloaded_filter="$preloadedFilters[$observation->filter_id] ?? null"
                                    :preloaded_lens="$preloadedLenses[$observation->lens_id] ?? null"
                                    :preloaded_constellations="$preloadedConstellations"
                                />
                            @endif
                        @endforeach
                    </div>
                    {{ $observations->links() }}
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
