@php use App\Models\SketchOfTheWeek; @endphp
@php use App\Models\SketchOfTheMonth; @endphp
@php use App\Models\User; @endphp
@php use App\Models\ObservationsOld; @endphp
@php use App\Models\CometObservationsOld; @endphp
@php use App\Models\ObservationSession; @endphp
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
                                :sketch="SketchOfTheWeek::orderBy('date', 'desc')->first()"
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
                                :sketch="SketchOfTheMonth::orderBy('date', 'desc')->first()"
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
                                        <a href="{{ route('session.show', [$session->observer->slug ?? $session->observerid, $session->slug ?? $session->id]) }}">
                                            <img src="{{ $session->preview }}" alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}" class="w-full h-40 object-cover rounded" />
                                        </a>
                                    </div>
                                @endif

                                <h3 class="text-lg font-bold text-white mb-2">
                                    <a href="{{ route('session.show', [$session->observer->slug ?? $session->observerid, $session->slug ?? $session->id]) }}" class="hover:underline">{{ html_entity_decode($session->name ?? __('Session :id', ['id' => $session->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
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
                                    <a href="{{ route('session.show', [optional($session->observer)->slug ?? $session->observerid, $session->slug ?? $session->id]) }}" class="text-blue-500 hover:underline">{{ __('Read more') }}</a>
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
                $sketches = ObservationsOld::where("hasDrawing", "1")
                    ->orderBy("id", "desc")
                    ->paginate(10, $columns = ['*'], $pageName = 'sketches')
                    ->appends(request()->except('sketches'));
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @foreach ($sketches as $sketch)
                            @php
                                $observation_id = $sketch->id;
                                $observer_name = User::where("username", $sketch->observerid)->first()->name;
                                $date = $sketch->date;
                                $observation_date = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
                            @endphp

                            <div class="flex flex-col pr-4">
                                <x-sketch-deepsky
                                    :observation_id="$observation_id"
                                    :observer_name="$observer_name"
                                    :observer_username="$sketch->observerid"
                                    :observation_date="$observation_date"
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
                $sketches = CometObservationsOld::where("hasDrawing", "1")
                    ->orderBy("id", "desc")
                    ->paginate(10, $columns = ['*'], $pageName = 'cometsketches')
                    ->appends(request()->except('cometsketches'));
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @foreach ($sketches as $sketch)
                            @php
                                $observation_id = $sketch->id;
                                $observer_name = User::where("username", $sketch->observerid)->first()->name;
                                $date = $sketch->date;
                                $observation_date = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
                            @endphp

                            <div class="flex flex-col pr-4">
                                <x-sketch-comet
                                    :observation_id="$observation_id"
                                    :observer_name="$observer_name"
                                    :observer_username="$sketch->observerid"
                                    :observation_date="$observation_date"
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
                $observations = ObservationsOld::orderBy("id", "desc")
                    ->paginate(10, $columns = ['*'], $pageName = 'deepsky')
                    ->appends(request()->except('deepsky'));
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="grid-cols-1 px-5">
                        @foreach ($observations as $observation)
                            <x-observation-deepsky
                                :observation="$observation"
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
                $observations = CometObservationsOld::orderBy("id", "desc")
                    ->paginate(10, $columns = ['*'], $pageName = 'comets')
                    ->appends(request()->except('comets'));
            @endphp

            <div class="mt-2">
                <x-card>
                    <div class="grid-cols-1 px-5">
                        @foreach ($observations as $observation)
                            <x-observation-comet :observation="$observation"/>
                        @endforeach
                    </div>
                    {{ $observations->links() }}
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
