@php use App\Models\SketchOfTheWeek; @endphp
@php use App\Models\SketchOfTheMonth; @endphp
@php use App\Models\User; @endphp
@php use App\Models\ObservationsOld; @endphp
@php use App\Models\CometObservationsOld; @endphp
@php use App\Models\ObservationSession; @endphp
@php use Illuminate\Support\Facades\Cache; @endphp
<x-app-layout>
    <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
        <x-card>
            <div
                class="flex justify-center py-4 dark:bg-gray-800 sm:items-center sm:pt-0"
            >
                <x-application-logo class="block h-12 w-auto"/>
            </div>
            @guest
                <div>
                    {{ __("Welcome to DeepskyLog!") }}
                    {!! __("DeepskyLog is an extended, comprehensive and free database for deepsky objects and has been developed by the Deepsky section of the Astronomical Society of Belgium (:linkVereniging Voor Sterrenkunde (VVS):close_link).", ["link" => "<a href='https://www.vvs.be'>", "close_link" => "</a>"]) !!}
                    {{ __("The database is open for consultation and already contains tens of thousands observations and thousands of sketches and drawings made by amateur astronomers around the world.") }}
                    <br/>
                    {!! __("To start recording your observations and share your observations with other observers, you are kindly requested to :linkregister:close_link to DeepskyLog.", ["link" => '<a href="/register">', "close_link" => "</a>"]) !!}
                    {!!
                        __("Your personal data will be handled in accordance with our :linkprivacy policy:close_link.", [
                            "link" => '<a href="/privacy">',
                            "close_link" => "</a>",
                        ])
                    !!}
                    {{ __("This registration allows access to a variety of useful tools, including information on the objects observed and sketches made.") }}
                    <br/>
                    {{ __("You can consult and create observing lists with different deepsky objects and see suggestions for objects visible in your instrument and from your observation sites. ") }}
                    {{ __("You can create your own file with maps and DSS images of the objects and have access to an interactive and detailed free star atlas. ") }}
                    {{ __("You can also consult the observations and sketches of other observers and share your own observations and sketches with the community.") }}
                    <br/>
                    {!!
                        __("Please contact the :linkDeepskyLog developers:close_link if you encounter problems or have questions.", [
                            "link" => '<a href="mailto:deepskylog@groups.io">',
                            "close_link" => "</a>",
                        ])
                    !!}
                </div>
            @endguest
        </x-card>

        {{-- Latest sketch of the month / week --}}
        <div class="flex">
            <div class="mt-4 rounded-xl">
                <h2 class="mb-3 ml-3 mt-3 text-xl font-semibold leading-tight">
                    {{ __("DeepskyLog Sketch of the Week") }}
                </h2>
                <x-card>
                    <div class="flex justify-center">
                        <div class="flex flex-wrap">
                            <x-sketch
                                :sketch="Cache::remember('welcome_sketch_week', 3600, function() { return SketchOfTheWeek::orderBy('date', 'desc')->first(); })"
                            />
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="ml-4 mt-4 rounded-xl">
                <h2 class="mb-3 ml-3 mt-3 text-xl font-semibold leading-tight">
                    {{ __("DeepskyLog Sketch of the Month") }}
                </h2>
                <x-card>
                    <div class="flex justify-center">
                        <div class="flex flex-wrap">
                            <x-sketch
                                :sketch="Cache::remember('welcome_sketch_month', 3600, function() { return SketchOfTheMonth::orderBy('date', 'desc')->first(); })"
                            />
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        {{-- Five newest active sessions --}}
        @isset($sessions)
            <div class="pt-4">
            <h2 class="ml-3 mt-3 text-xl font-semibold leading-tight">
                {{ __("Newest sessions") }}
            </h2>
            @php
                // $sessions is prepared in the homepage route (SessionController::homepage)
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-2">
                        @foreach($sessions as $session)
                            @php
                                // Use a slightly lighter background for each session card (no borders)
                                if ($loop->iteration === 1) {
                                    $bgClass = 'bg-gray-700';
                                } elseif ($loop->iteration === 2) {
                                    $bgClass = 'bg-gray-700';
                                } elseif ($loop->iteration === 3) {
                                    $bgClass = 'bg-gray-700';
                                } elseif ($loop->iteration === 4) {
                                    $bgClass = 'bg-gray-700';
                                } else {
                                    $bgClass = 'bg-gray-700';
                                }
                            @endphp

                            <article class="{{ $bgClass }} p-4 rounded">
                                @if(! empty($session->preview))
                                    <div class="mb-3">
                                        @php
                                            $sessionUser = optional($session->observer)->slug ?? $session->observerid ?? null;
                                            $sessionParam = $session->slug ?? $session->id ?? null;
                                        @endphp
                                        @if($sessionUser && $sessionParam)
                                            <a href="{{ route('session.show', [$sessionUser, $sessionParam]) }}">
                                                <img src="{{ $session->preview }}" alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}" class="w-full h-40 object-cover rounded" />
                                            </a>
                                        @else
                                            <img src="{{ $session->preview }}" alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}" class="w-full h-40 object-cover rounded" />
                                        @endif
                                    </div>
                                @endif

                                <h3 class="text-lg font-bold text-white mb-2">
                                    @php
                                        // Ensure both route parameters exist before building the URL to avoid missing parameter errors
                                        $sessionUser = optional($session->observer)->slug ?? $session->observerid ?? null;
                                        $sessionParam = $session->slug ?? $session->id ?? null;
                                    @endphp
                                    @if($sessionUser && $sessionParam)
                                        <a href="{{ route('session.show', [$sessionUser, $sessionParam]) }}" class="hover:underline">{{ html_entity_decode($session->name ?? __('Session :id', ['id' => $session->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                    @else
                                        <span class="hover:underline">{{ html_entity_decode($session->name ?? __('Session :id', ['id' => $session->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</span>
                                    @endif
                                </h3>

                                <div class="text-sm text-gray-400 mb-2">
                                    <span class="mr-2 font-medium text-gray-200">{{ __('Owner') }}:</span>
                                    @if($session->observer)
                                        <a href="{{ route('observer.show', optional($session->observer)->slug ?? $session->observerid) }}" class="text-gray-400 hover:underline">{{ optional($session->observer)->name ?? $session->observerid }}</a>
                                    @else
                                        <span class="text-gray-400">{{ $session->observerid }}</span>
                                    @endif
                                </div>

                                <div class="text-sm text-gray-400 mb-3">
                                    <span>{{ $session->begindate ? \Carbon\Carbon::parse($session->begindate)->translatedFormat('j M Y') : __('Unknown') }}</span>
                                    <span class="mx-2">&ndash;</span>
                                    <span>{{ $session->enddate ? \Carbon\Carbon::parse($session->enddate)->translatedFormat('j M Y') : __('Unknown') }}</span>
                                </div>

                                <div class="text-sm text-gray-400 mb-2">
                                    <span class="mr-2 font-medium text-gray-200">{{ __('Location') }}:</span>
                                    <span class="text-gray-400">{{ $session->location_name ?? __('Unknown') }}</span>
                                </div>

                                @if(isset($session->observation_count))
                                    <div class="text-sm text-gray-300 mb-2">{{ __('Observations') }}: <strong class="text-white">{{ $session->observation_count }}</strong></div>
                                @endif

                                <p class="text-sm text-gray-300 mb-3">{{ $session->preview_text ?? \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')), 180) }}</p>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="text-gray-400">{{ __('Observers') }}: {{ $session->otherObserversCount() ?? 1 }}</div>
                                    @php
                                        $sessionUser = optional($session->observer)->slug ?? $session->observerid ?? null;
                                        $sessionParam = $session->slug ?? $session->id ?? null;
                                    @endphp
                                    @if($sessionUser && $sessionParam)
                                        <a href="{{ route('session.show', [$sessionUser, $sessionParam]) }}" class="text-blue-500 hover:underline">{{ __('Read more') }}</a>
                                    @else
                                        <span class="text-blue-500">{{ __('Read more') }}</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $sessions->links() }}
                    </div>
                </x-card>
            </div>
        </div>
        @endisset

        {{-- Ten latest sketches --}}
        <div class="pt-4">
            <h2 class="ml-3 mt-3 text-xl font-semibold leading-tight">
                {{ __("10 newest deep-sky Sketches") }}
            </h2>
            @php
                $page = request()->get('sketches', 1);
                $cacheKey = 'welcome_sketches_page_' . $page;
                $sketches = Cache::remember($cacheKey, 300, function() use ($page) {
                    return ObservationsOld::where('hasDrawing', '1')
                        ->orderBy('id', 'desc')
                        ->paginate(10, $columns = ['*'], $pageName = 'sketches', page: $page)
                        ->appends(request()->except('sketches'));
                });

                $observerIds = $sketches->pluck('observerid')->unique()->values()->all();
                $observerUsers = User::whereIn('username', $observerIds)->get()->keyBy('username');
                
                // Preload objects to avoid N+1 in sketch-deepsky component
                $objectNames = $sketches->pluck('objectname')->unique()->values()->all();
                $objects = \App\Models\ObjectsOld::whereIn('name', $objectNames)->get()->keyBy('name');
                
                // Preload likes data in batch
                $sketchIds = $sketches->pluck('id')->all();
                $likesCounts = \App\Models\ObservationLike::where('observation_type', 'deepsky')
                    ->whereIn('observation_id', $sketchIds)
                    ->select('observation_id', \DB::raw('count(*) as count'))
                    ->groupBy('observation_id')
                    ->pluck('count', 'observation_id');
                    
                $userLikes = auth()->check() 
                    ? \App\Models\ObservationLike::where('observation_type', 'deepsky')
                        ->whereIn('observation_id', $sketchIds)
                        ->where('user_id', auth()->id())
                        ->pluck('observation_id')
                        ->flip()
                    : collect();
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @foreach ($sketches as $sketch)
                            @php
                                $observation_id = $sketch->id;
                                $observerUser = $observerUsers[$sketch->observerid] ?? null;
                                $observer_name = $observerUser ? $observerUser->name : $sketch->observerid;
                                $date = $sketch->date;
                                $observation_date = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
                                $object = $objects[$sketch->objectname] ?? null;
                                $likesCount = $likesCounts[$observation_id] ?? 0;
                                $liked = $userLikes->has($observation_id);
                            @endphp

                            <div class="flex flex-col pr-4">
                                <x-sketch-deepsky
                                    :observation_id="$observation_id"
                                    :observer_name="$observer_name"
                                    :observer_username="$sketch->observerid"
                                    :observation_date="$observation_date"
                                    :observation="$sketch"
                                    :object="$object"
                                    :likes_count="$likesCount"
                                    :liked="$liked"
                                />
                            </div>
                        @endforeach
                    </div>
                    {{ $sketches->links() }}
                </x-card>
            </div>
        </div>

        {{-- Ten latest sketches --}}
        <div class="pt-4">
            <h2 class="ml-3 mt-3 text-xl font-semibold leading-tight">
                {{ __("10 newest comet Sketches") }}
            </h2>
            @php
                $page = request()->get('cometsketches', 1);
                $cacheKey = 'welcome_comet_sketches_page_' . $page;
                $sketches = Cache::remember($cacheKey, 300, function() use ($page) {
                    return CometObservationsOld::where('hasDrawing', '1')
                        ->orderBy('id', 'desc')
                        ->paginate(10, $columns = ['*'], $pageName = 'cometsketches', page: $page)
                        ->appends(request()->except('cometsketches'));
                });

                $observerIds = $sketches->pluck('observerid')->unique()->values()->all();
                $observerUsers = User::whereIn('username', $observerIds)->get()->keyBy('username');

                $sketchObjectIds = $sketches->pluck('objectid')->unique()->filter()->values()->all();
                $preloadedSketchComets = \App\Models\CometObject::whereIn('id', $sketchObjectIds)->get()->keyBy('id');
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @foreach ($sketches as $sketch)
                            @php
                                $observation_id = $sketch->id;
                                $observerUser = $observerUsers[$sketch->observerid] ?? null;
                                $observer_name = $observerUser ? $observerUser->name : $sketch->observerid;
                                $date = $sketch->date;
                                $observation_date = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
                            @endphp

                            <div class="flex flex-col pr-4">
                                <x-sketch-comet
                                    :observation_id="$observation_id"
                                    :observer_name="$observer_name"
                                    :observer_username="$sketch->observerid"
                                    :observation_date="$observation_date"
                                    :preloaded_comet="$preloadedSketchComets[$sketch->objectid] ?? null"
                                />
                            </div>
                        @endforeach
                    </div>
                    {{ $sketches->links() }}
                </x-card>
            </div>
        </div>

        {{-- Ten latest observations --}}
        <div class="pt-4">
            <h2 class="ml-3 mt-3 text-xl font-semibold leading-tight">
                {{ __("10 newest deep-sky observations") }}
            </h2>
            @php
                $page = request()->get('deepsky', 1);
                $cacheKey = 'welcome_deepsky_page_' . $page;
                $observations = Cache::remember($cacheKey, 300, function() use ($page) {
                    return ObservationsOld::orderBy('id', 'desc')
                        ->paginate(10, $columns = ['*'], pageName: 'deepsky', page: $page)
                        ->appends(request()->except('deepsky'));
                });
                
                // Preload all related data to avoid N+1 queries in observation-deepsky component
                $observerIds = $observations->pluck('observerid')->unique()->filter()->all();
                $preloadedUsers = \App\Models\User::whereIn('username', $observerIds)->get()->keyBy('username');
                
                $objectNames = $observations->pluck('objectname')->unique()->filter()->all();
                $preloadedObjects = \App\Models\ObjectsOld::whereIn('name', $objectNames)->get()->keyBy('name');
                
                $locationIds = $observations->pluck('locationid')->unique()->filter()->all();
                $preloadedLocations = \App\Models\Location::whereIn('id', $locationIds)->get()->keyBy('id');
                
                $instrumentIds = $observations->pluck('instrumentid')->unique()->filter()->all();
                $preloadedInstruments = \App\Models\Instrument::whereIn('id', $instrumentIds)->get()->keyBy('id');
                
                $eyepieceIds = $observations->pluck('eyepieceid')->filter(fn($id) => $id > 0)->unique()->all();
                $preloadedEyepieces = \App\Models\Eyepiece::whereIn('id', $eyepieceIds)->get()->keyBy('id');
                
                $filterIds = $observations->pluck('filterid')->filter(fn($id) => $id > 0)->unique()->all();
                $preloadedFilters = \App\Models\Filter::whereIn('id', $filterIds)->get()->keyBy('id');
                
                $conIds = $preloadedObjects->pluck('con')->unique()->filter()->all();
                $preloadedConstellations = \App\Models\Constellation::whereIn('id', $conIds)->get()->keyBy('id');
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="grid-cols-1 px-5">
                        @php
                            $tr = null;
                            if (auth()->check() && auth()->user()->translate) {
                                $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(auth()->user()->language);
                            }
                        @endphp
                        @foreach ($observations as $observation)
                            <x-observation-deepsky
                                :observation="$observation"
                                :translator="$tr"
                                :preloaded_user="$preloadedUsers[$observation->observerid] ?? null"
                                :preloaded_object="$preloadedObjects[$observation->objectname] ?? null"
                                :preloaded_location="$preloadedLocations[$observation->locationid] ?? null"
                                :preloaded_instrument="$preloadedInstruments[$observation->instrumentid] ?? null"
                                :preloaded_eyepiece="$preloadedEyepieces[$observation->eyepieceid] ?? null"
                                :preloaded_filter="$preloadedFilters[$observation->filterid] ?? null"
                                :preloaded_constellations="$preloadedConstellations"
                            />
                        @endforeach
                    </div>
                    {{ $observations->links() }}
                </x-card>
            </div>
        </div>

        {{-- Ten latest comet observations --}}
        <div class="pt-4">
            <h2 class="ml-3 mt-3 text-xl font-semibold leading-tight">
                {{ __("10 newest comet observations") }}
            </h2>
            @php
                $page = request()->get('comets', 1);
                $cacheKey = 'welcome_comets_page_' . $page;
                $observations = Cache::remember($cacheKey, 300, function() use ($page) {
                    return CometObservationsOld::orderBy('id', 'desc')
                        ->paginate(10, $columns = ['*'], pageName: 'comets', page: $page)
                        ->appends(request()->except('comets'));
                });
                
                // Preload all related data to avoid N+1 queries in observation-comet component
                $observerIds = $observations->pluck('observerid')->unique()->filter()->all();
                $preloadedUsers = \App\Models\User::whereIn('username', $observerIds)->get()->keyBy('username');
                
                $objectIds = $observations->pluck('objectid')->unique()->filter()->all();
                $preloadedComets = \App\Models\CometObject::whereIn('id', $objectIds)->get()->keyBy('id');
                
                $locationIds = $observations->pluck('locationid')->filter(fn($id) => $id > 0)->unique()->all();
                $preloadedLocations = \App\Models\Location::whereIn('id', $locationIds)->get()->keyBy('id');
                
                $instrumentIds = $observations->pluck('instrumentid')->filter(fn($id) => $id > 0)->unique()->all();
                $preloadedInstruments = \App\Models\Instrument::whereIn('id', $instrumentIds)->get()->keyBy('id');
                
                // Preload likes data in batch
                $observationIds = $observations->pluck('id')->all();
                $likesCounts = \App\Models\ObservationLike::where('observation_type', 'comet')
                    ->whereIn('observation_id', $observationIds)
                    ->select('observation_id', \DB::raw('count(*) as count'))
                    ->groupBy('observation_id')
                    ->pluck('count', 'observation_id');
                    
                $userLikes = auth()->check() 
                    ? \App\Models\ObservationLike::where('observation_type', 'comet')
                        ->whereIn('observation_id', $observationIds)
                        ->where('user_id', auth()->id())
                        ->pluck('observation_id')
                        ->flip()
                    : collect();
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="grid-cols-1 px-5">
                        @foreach ($observations as $observation)
                            <x-observation-comet
                                :observation="$observation"
                                :preloaded_user="$preloadedUsers[$observation->observerid] ?? null"
                                :preloaded_comet="$preloadedComets[$observation->objectid] ?? null"
                                :preloaded_location="$preloadedLocations[$observation->locationid] ?? null"
                                :preloaded_instrument="$preloadedInstruments[$observation->instrumentid] ?? null"
                                :likes_count="$likesCounts[$observation->id] ?? 0"
                                :liked="$userLikes->has($observation->id)"
                            />
                        @endforeach
                    </div>
                    {{ $observations->links() }}
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
