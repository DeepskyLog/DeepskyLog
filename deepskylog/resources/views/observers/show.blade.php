@php use App\Models\ObservationsOld; @endphp
@php use App\Models\CometObservationsOld; @endphp
@php use App\Models\User; @endphp
<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="grids-rows-2 grid grid-cols-2">
                <div class="col-span-1">
                    <!-- Current Profile Photo -->
                    <img
                        src="{{ $user->profile_photo_url }}"
                        alt="{{ $user->name }}"
                        class="h-20 w-20 rounded-full object-cover"
                    />
                    <h2
                        class="mt-4 bg-gray-900 text-xl font-semibold leading-tight"
                    >
                        {{ $user->name }}
                    </h2>
                    <div class="mx-left">
                        {{ __("DeepskyLog observer since ") }}
                        @php
                            echo Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $user->created_at)->format("Y");
                        @endphp

                        <br/>
                        {{-- First observation on / last observation on --}}

                        @if (! is_null($user->firstObservationDate()[0]))
                            {{ __("First observation on ") }}
                            @if ($user->firstObservationDate()[1] > 0)
                                <a
                                    href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $user->firstObservationDate()[1] }}"
                                >
                                    {{ $user->firstObservationDate()[0] }}
                                </a>
                            @else
                                <a
                                    href="{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ -$user->firstObservationDate()[1] }}"
                                >
                                    {{ $user->firstObservationDate()[0] }}
                                </a>
                            @endif
                            <br/>
                            {{ __("Most recent observation on ") }}
                            @if ($user->lastObservationDate()[1] > 0)
                                <a
                                    href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $user->lastObservationDate()[1] }}"
                                >
                                    {{ $user->lastObservationDate()[0] }}
                                </a>
                            @else
                                <a
                                    href="{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ -$user->lastObservationDate()[1] }}"
                                >
                                    {{ $user->lastObservationDate()[0] }}
                                </a>
                            @endif
                        @else
                            {{ __("No observations added!") }}
                        @endif

                        <br/>
                        <br/>

                        <table class="table-auto">
                            <!-- Default location -->
                            <tr>
                                <td>{!! __("Default observing site") !!}</td>
                                <td>
                                    @if ($user->stdlocation && $user->stdlocation != null)
                                        <a
                                            href="/location/{{ $user->slug }}/{{ $user->standardLocation->slug }}"
                                        >
                                            {!! $user->standardLocation->name !!}
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Default instrument -->
                            <tr>
                                <td>{{ __("Default instrument") }}</td>
                                <td>
                                    @if ($user->stdtelescope)
                                        <a
                                            href="/instrument/{{ $user->slug }}/{{ $user->standardInstrument->slug }}"
                                        >
                                            {!! $user->standardInstrument->fullName() !!}
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of locations -->
                            <tr>
                                <td>{!! __("Number of locations") !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/location">
                                            @endif
                                            {{ $user->locations->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of instruments -->
                            <tr>
                                <td>{!! __("Number of instruments") !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/instrument">
                                            @endif
                                            {{ $user->instruments->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of eyepieces -->
                            <tr>
                                <td>{!! __("Number of eyepieces") !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/eyepiece">
                                            @endif
                                            {{ $user->eyepieces->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of filters -->
                            <tr>
                                <td>{{ __("Number of filters") }}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/filter">
                                            @endif
                                            {{ $user->filters->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of lenses -->
                            <tr>
                                <td>{{ __("Number of lenses") }}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/lens">
                                            @endif
                                            {{ $user->lenses->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of observing lists -->
                            <tr>
                                <td>{!! __("Total number of observing lists") !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="{{ config('app.old_url') }}/index.php?indexAction=view_lists">
                                            @endif
                                            {{ $user->getObservingLists()->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of observing lists -->
                            <tr>
                                <td>{!! __("Public observing lists") !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="{{ config('app.old_url') }}/index.php?indexAction=view_lists">
                                            @endif
                                            {{ $user->getPublicObservingLists()->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of equipment sets -->
                           <tr>
                           <td>{!! __("Number of equipment sets") !!}</td>
                               <td>
                                   @if (Auth::user() && $user->id === Auth::user()->id)
                                       <a href="/instrumentset">
                                   @endif
                                {{ $user->instrumentSets()->count() }}
                                @if (Auth::user() && $user->id === Auth::user()->id)
                                </a>
                                @endif
                                </td>
                            </tr>

                            <!-- Country of residence -->
                            <tr>
                                <td>{{ __("Country of residence") }}</td>
                                <td>
                                    @if ($user->country != '')
                                        {{ Countries::getOne($user->country, app()->getLocale()) }}
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-span-1 row-span-2">
                    <div class="col-span-1">
                        @if ($user->about)
                            <x-card class="prose max-w-none dark:prose-invert">
                                <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                                    {{ __("About me") }}
                                </h2>
                                <div class="px-5 ">
                                    {!! $user->about !!}
                                </div>
                            </x-card>
                            <br/>
                        @endif
                    </div>
                </div>
            </div>

            <br/>

            <x-card>
                <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                    {{ __("DeepskyLog Trophy Gallery") }}
                </h2>

                <div class="flex flex-wrap gap-x-6 gap-y-4 px-5">
                    @if ($user->achievements->count() > 0)
                        @foreach($user->achievements as $achievement)
                            <div title="{{ $achievement->description }}">
                                <svg width="100" height="100">
                                    <image xlink:href="{{ $achievement->image }}"
                                           src="{{ $achievement->image }}"
                                           width="100" height="100"/>
                                </svg>
                            </div>
                        @endforeach
                    @endif
                </div>
            </x-card>
            <!-- Personal tab -->
            <div class="py-4">

                <table>
                    <!-- Copyright notice -->
                    <tr>
                        <td>{!! __("Copyright notice") !!}</td>
                        <td>{!! $user->getCopyright() !!}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                </table>
                <table class="table-auto">
                    <thead>
                    <tr class="align-left">
                        <th></th>
                        <th>{{ __("Total") }}</th>
                        <th>{{ __("Deepsky") }}</th>
                        <th>{{ __("Comets") }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr>
                        <td>{!! __("Number of observations") !!}</td>
                        <td>{{ $observations->count() + CometObservationsOld::where("observerid", $user->username)->count() }}
                            / {{ $totalObservations + CometObservationsOld::getTotalObservations() }}
                        </td>
                        <td>{{ $observations->count() }}
                            / {{ $totalObservations }}
                        </td>
                        <td>{{ CometObservationsOld::where("observerid", $user->username)->count() }}
                            / {{ CometObservationsOld::getTotalObservations() }}
                        </td>
                    </tr>

                    <tr>
                        <td>{!! __("Observations last year") !!}</td>
                        <td>{{ $observationsLastYear + $user->getCometObservationsLastYear() }} /
                            {{ $totalObservationsLastYear + CometObservationsOld::getTotalObservationsLastYear()}}
                        </td>
                        <td>{{ $observationsLastYear }}
                            / {{ $totalObservationsLastYear }}
                        </td>
                        <td>{{ $user->getCometObservationsLastYear() }}
                            / {{ CometObservationsOld::getTotalObservationsLastYear() }}
                        </td>
                    </tr>

                    <tr>
                        <td>{{ __("Number of drawings") }}</td>
                        <td>{{ $observations->where("hasDrawing", 1)->count()
                                + CometObservationsOld::where("observerid", $user->username)->where("hasDrawing", 1)->count()}}
                            /
                            {{ $totalNumberOfDrawings + CometObservationsOld::where("hasDrawing", 1)->count() }}
                        </td>
                        <td>{{ $observations->where("hasDrawing", 1)->count() }}
                            /
                            {{ $totalNumberOfDrawings }}
                        </td>
                        <td>{{ CometObservationsOld::where("observerid", $user->username)->where("hasDrawing", 1)->count() }}
                            /
                            {{ CometObservationsOld::where("hasDrawing", 1)->count() }}
                        </td>
                    </tr>

                    <tr>
                        <td>{!! __("Drawings last year") !!}</td>
                        @php
                            $totalDrawings = ObservationsOld::getTotalDrawingsLastYear();
                        @endphp
                        <td>{{ $user->getDeepskyDrawingsLastYear() + $user->getCometDrawingsLastYear() }} /
                            {{ $totalDrawings + CometObservationsOld::getTotalDrawingsLastYear()}}
                        </td>
                        <td>{{ $user->getDeepskyDrawingsLastYear() }}
                            / {{ $totalDrawings }}
                        </td>
                        <td>{{ $user->getCometDrawingsLastYear() }}
                            / {{ CometObservationsOld::getTotalDrawingsLastYear() }}
                        </td>
                    </tr>

                    <tr>
                        <td>{{ __("Different objects") }}</td>
                        <td>{{ $user->getUniqueObjectsObservations() + $user->getUniqueCometObservations() }} /
                            {{ $totalUniqueObjects + CometObservationsOld::getUniqueObjectsObserved() }}</td>
                        <td>{{ $user->getUniqueObjectsObservations() }}
                            / {{ $totalUniqueObjects }}</td>
                        <td>{{ $user->getUniqueCometObservations() }} /
                            {{ CometObservationsOld::getUniqueObjectsObserved() }}</td>
                    </tr>

                    <tr>
                        <td>{{ __("Messier objects") }}</td>
                        <td></td>
                        <td>{{ $user->getObservedCountFromCatalog('M') }} / 110</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{!! __("Drawings of Messier objects") !!}</td>
                        <td></td>
                        <td>{{ $user->getDrawingCountFromCatalog('M') }} / 110</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{{ __("Caldwell objects") }}</td>
                        <td></td>
                        <td>{{ $user->getObservedCountFromCatalog('Caldwell') }} / 109</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{!! __("Drawings of Caldwell objects") !!}</td>
                        <td></td>
                        <td>{{ $user->getDrawingCountFromCatalog('Caldwell') }} / 109</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{{ __("H400 objects") }}</td>
                        <td></td>
                        <td>{{ $user->getObservedCountFromCatalog('H400') }} / 400</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{!! __("Drawings of H400 objects") !!}</td>
                        <td></td>
                        <td>{{ $user->getDrawingCountFromCatalog('H400') }} / 400</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{{ __("H400-II objects") }}</td>
                        <td></td>
                        <td>{{ $user->getObservedCountFromCatalog('H400-II') }} / 400</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{!! __("Drawings of H400-II objects") !!}</td>
                        <td></td>
                        <td>{{ $user->getDrawingCountFromCatalog('H400-II') }} / 400</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{{ __("Rank") }}</td>
                        <td>{{ $user->getRank() }} / {{ User::count() }} </td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>

                <br/>
                <x-button gray icon="eye" class="mb-2"
                          href='{{ url("/observations/" . $user->slug) }}'
                >
                    {{ __("All observations of ") . $user->name }}
                </x-button>

                <x-button gray icon="pencil" class="mb-2"
                          href="/drawings/{{ $user->slug }}"
                >

                    {{ __("All drawings of ") . $user->name }}
                </x-button>

                @php
                    $hasActiveSessions = \App\Models\ObservationSession::where('observerid', $user->username)->where('active', 1)->exists();
                @endphp
                @if($hasActiveSessions)
                    <x-button gray icon="calendar" class="mb-2"
                              href="{{ route('session.user', [$user->slug ?? $user->username]) }}"
                    >
                        {{ __("All sessions of ") . $user->name }}
                    </x-button>
                @endif

                @if (Auth::user() && $user->id != Auth::user()->id)
                    <x-button gray icon="envelope-open" class="mb-2"
                              href="{{ route('messages.create', ['to' => $user->username]) }}"
                    >
                        @auth
                        {{ __("Send message to ") . $user->name }}
                        @endauth
                    </x-button>
                @endif
            </div>

            <!-- The observations per year chart -->
            <div>
                {!! $observationsPerYearChart->container() !!}
            </div>

            <!-- The observations per month chart -->
            <div>
                {!! $observationsPerMonthChart->container() !!}
            </div>

            <!-- The object types seen chart -->
            <div>
                {!! $objectTypesChart->container() !!}
            </div>

            <br/>
            <!-- The countries chart -->
            <div>
                {!! $countriesChart->container() !!}
            </div>

        @if(! empty($sessions) && $sessions->isNotEmpty())
            <div class="mt-6">
                <h2 class="mb-3 ml-3 mt-3 text-xl font-semibold leading-tight">{{ __("Recent sessions") }}</h2>
                <div class="mt-2">
                    <x-card>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-2">
                            @foreach($sessions as $session)
                                <article class="bg-gray-700 p-4 rounded">
                                    @if(! empty($session->preview))
                                        <div class="mb-3">
                                            <a href="{{ route('session.show', [$session->observer->slug ?? $session->observerid, $session->slug ?? $session->id]) }}">
                                                <img src="{{ $session->preview }}" alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}" class="w-full h-28 object-cover rounded" />
                                            </a>
                                        </div>
                                    @endif

                                    <h3 class="text-lg font-bold text-white mb-2">
                                        <a href="{{ route('session.show', [$session->observer->slug ?? $session->observerid, $session->slug ?? $session->id]) }}" class="hover:underline">{{ html_entity_decode($session->name ?? __('Session :id', ['id' => $session->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                    </h3>

                                    <div class="text-sm text-gray-400 mb-2">
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
                                        <a href="{{ route('session.show', [$session->observer->slug ?? $session->observerid, $session->slug ?? $session->id]) }}" class="text-blue-500 hover:underline">{{ __('Read more') }}</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </x-card>
                </div>
            </div>
        @endif

            @if ($user->sketchOfTheWeek->count() > 0)
                <div class="mt-6">
                    <x-card>
                        <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                            {{ __("DeepskyLog sketches of the week") }}
                        </h2>
                        <div class="px-5 flex flex-wrap">
                            @foreach($user->sketchOfTheWeek as $sketch)
                                <div class="max-w-xl mt-3 pr-3">
                                    <x-sketch :sketch="$sketch"/>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </div>
            @endif

            @if ($user->sketchOfTheMonth->count() > 0)
                <div class="mt-6">
                    <x-card>
                        <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                            {{ __("DeepskyLog sketches of the month") }}
                        </h2>
                        <div class="px-5 flex flex-wrap">
                            @foreach($user->sketchOfTheMonth as $sketch)
                                <div class="max-w-xl mt-3 pr-3">
                                    {{-- Show the correct drawing --}}
                                    <x-sketch :sketch="$sketch"/>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </div>
            @endif

            @if (! empty($hasPopularObservations) && $hasPopularObservations)
                <div class="mt-6">
                    <x-card>
                        <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                            {{ __("Most liked observations") }}
                        </h2>

                        <div class="px-5">
                            {{-- Render Livewire table showing only this user's popular observations --}}
                            <livewire:user-popular-observations-table :username="$user->username" />
                        </div>
                    </x-card>
                </div>
            @endif
        </div>
    </div>

    <script src="{{ $observationsPerYearChart->cdn() }}"></script>
    <script src="{{ $observationsPerMonthChart->cdn() }}"></script>
    <script src="{{ $objectTypesChart->cdn() }}"></script>
    <script src="{{ $countriesChart->cdn() }}"></script>


    {{ $observationsPerYearChart->script() }}
    {{ $observationsPerMonthChart->script() }}
    {{ $objectTypesChart->script() }}
    {{ $countriesChart->script() }}

</x-app-layout>
