<x-app-layout>
    <div>
        <div class="mx-auto max-w-7xl bg-gray-900 py-10 sm:px-6 lg:px-8">
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

                        <br />
                        {{-- First observation on / last observation on --}}

                        @if (! is_null($user->firstObservationDate()[0]))
                            {{ __("First observation on ") }}
                            <a
                                href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $user->firstObservationDate()[1] }}"
                            >
                                {{ $user->firstObservationDate()[0] }}
                            </a>
                            <br />
                            {{ __("Most recent observation on ") }}
                            <a
                                href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $user->lastObservationDate()[1] }}"
                            >
                                {{ $user->lastObservationDate()[0] }}
                            </a>
                        @else
                            {{ __("No observations added!") }}
                        @endif

                        <br />
                        <br />

                        <table class="table-auto">
                            <!-- Default location -->
                            <tr>
                                <td>{{ __("Default observing site") }}</td>
                                <td>
                                    @if ($user->stdlocation)
                                        <a
                                            href="{{ config("app.old_url") }}/index.php?indexAction=detail_location&location={{ $user->stdlocation }}"
                                        >
                                            {!! \App\Models\LocationsOld::where(["id" => $user->stdlocation])->first()->name !!}
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
                                            href="{{ config('app.old_url') }}/index.php?indexAction=detail_instrument&instrument={{ $user->stdtelescope }}"
                                        >
                                            {!! \App\Models\InstrumentsOld::where(["id" => $user->stdtelescope])->first()->name !!}
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of locations -->
                            <tr>
                                <td>{{ __("Number of locations") }}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="{{ config('app.old_url') }}/index.php?indexAction=view_sites">
                                            @endif
                                            {{ \App\Models\LocationsOld::where(["observer" => $user->username])->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of instruments -->
                            <tr>
                                <td>{{ __("Number of instruments") }}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="{{ config('app.old_url') }}/index.php?indexAction=view_instruments">
                                            @endif
                                            {{ \App\Models\InstrumentsOld::where(["observer" => $user->username])->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of eyepieces -->
                            <tr>
                                <td>{{ __("Number of eyepieces") }}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="{{ config('app.old_url') }}/index.php?indexAction=view_eyepieces">
                                            @endif
                                            {{ \App\Models\EyepiecesOld::where(["observer" => $user->username])->count() }}
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
                                        <a href="{{ config('app.old_url') }}/index.php?indexAction=view_filters">
                                            @endif
                                            {{ \App\Models\FiltersOld::where(["observer" => $user->username])->count() }}
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
                                        <a href="{{ config('app.old_url') }}/index.php?indexAction=view_lenses">
                                            @endif
                                            {{ \App\Models\LensesOld::where(["observer" => $user->username])->count() }}
                                            @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of observing lists -->
                            <tr>
                                <td>{{ __("Total number of observing lists") }}</td>
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
                                <td>{{ __("Public observing lists") }}</td>
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
                            {{--                            <tr>--}}
                            {{--                                <td>{{ __("Number of equipment sets") }}</td>--}}
                            {{--                                <td>--}}
                            {{--
                                @if ($user->id === Auth::user()->id)
                                <a href="{{ route('set.index') }}">
                                @endif
                                {{ $user->sets()->count() }}
                                @if ($user->id === Auth::user()->id)
                                </a>
                                @endif
                            --}}
                            {{--                                </td>--}}
                            {{--                            </tr>--}}

                            <!-- Country of residence -->
                            <tr>
                                <td>{{ __("Country of residence") }}</td>
                                <td>
                                    @if ($user->country != '')
                                        {{ Countries::getOne($user->country) }}
                                        {{--                                        {{ Countries::getOne($user->country, LaravelGettext::getLocaleLanguage()) }}--}}
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
                            <br />
                        @endif
                    </div>
                </div>
            </div>

            <br />

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
                                           width="100" height="100" />
                                </svg>
                            </div>
                        @endforeach
                    @endif
                </div>
            </x-card>
            <!-- Personal tab -->
            <div class="py-4">

                <table class="table-striped table-sm table">
                    <!-- Copyright notice -->
                    <tr>
                        <td>{{ __("Copyright notice") }}</td>
                        <td>{!! $user->getCopyright() !!}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                </table>
                <table class="table-auto">
                    <tr>
                        <th></th>
                        <th>{{ __("Total") }}</th>
                        <th>{{ __("Deepsky") }}</th>
                        <th>{{ __("Comets") }}</th>
                    </tr>

                    <tr>
                        <td>{{ __("Number of observations") }}</td>
                        <td>{{ \App\Models\ObservationsOld::where("observerid", $user->username)->count() + \App\Models\CometObservationsOld::where("observerid", $user->username)->count() }}
                            / {{ \App\Models\ObservationsOld::getTotalObservations() + \App\Models\CometObservationsOld::getTotalObservations() }}
                        </td>
                        <td>{{ \App\Models\ObservationsOld::where("observerid", $user->username)->count() }}
                            / {{ \App\Models\ObservationsOld::getTotalObservations() }}
                        </td>
                        <td>{{ \App\Models\CometObservationsOld::where("observerid", $user->username)->count() }}
                            / {{ \App\Models\CometObservationsOld::getTotalObservations() }}
                        </td>
                    </tr>

                    <tr>
                        <td>{{ __("Observations last year") }}</td>
                        <td>{{ $user->getDeepskyObservationsLastYear() + $user->getCometObservationsLastYear() }} /
                            {{ \App\Models\ObservationsOld::getTotalObservationsLastYear() + \App\Models\CometObservationsOld::getTotalObservationsLastYear()}}
                        </td>
                        <td>{{ $user->getDeepskyObservationsLastYear() }}
                            / {{ \App\Models\ObservationsOld::getTotalObservationsLastYear() }}
                        </td>
                        <td>{{ $user->getCometObservationsLastYear() }}
                            / {{ \App\Models\CometObservationsOld::getTotalObservationsLastYear() }}
                        </td>
                    </tr>

                    <tr>
                        <td>{{ __("Number of drawings") }}</td>
                        <td>{{ \App\Models\ObservationsOld::where("observerid", $user->username)->where("hasDrawing", 1)->count()
                                + \App\Models\CometObservationsOld::where("observerid", $user->username)->where("hasDrawing", 1)->count()}}
                            /
                            {{ \App\Models\ObservationsOld::where("hasDrawing", 1)->count() + \App\Models\CometObservationsOld::where("hasDrawing", 1)->count() }}
                        </td>
                        <td>{{ \App\Models\ObservationsOld::where("observerid", $user->username)->where("hasDrawing", 1)->count() }}
                            /
                            {{ \App\Models\ObservationsOld::where("hasDrawing", 1)->count() }}
                        </td>
                        <td>{{ \App\Models\CometObservationsOld::where("observerid", $user->username)->where("hasDrawing", 1)->count() }}
                            /
                            {{ \App\Models\CometObservationsOld::where("hasDrawing", 1)->count() }}
                        </td>
                    </tr>

                    <tr>
                        <td>{{ __("Drawings last year") }}</td>
                        <td>{{ $user->getDeepskyDrawingsLastYear() + $user->getCometDrawingsLastYear() }} /
                            {{ \App\Models\ObservationsOld::getTotalDrawingsLastYear() + \App\Models\CometObservationsOld::getTotalDrawingsLastYear()}}
                        </td>
                        <td>{{ $user->getDeepskyDrawingsLastYear() }}
                            / {{ \App\Models\ObservationsOld::getTotalDrawingsLastYear() }}
                        </td>
                        <td>{{ $user->getCometDrawingsLastYear() }}
                            / {{ \App\Models\CometObservationsOld::getTotalDrawingsLastYear() }}
                        </td>
                    </tr>

                    <tr>
                        <td>{{ __("Different objects") }}</td>
                        <td>{{ $user->getUniqueObjectsObservations() + $user->getUniqueCometObservations() }} /
                            {{ \App\Models\ObservationsOld::getUniqueObjectsObserved() }}</td>
                        <td>{{ $user->getUniqueObjectsObservations() }}
                            / {{ \App\Models\ObservationsOld::getUniqueObjectsObserved() }}</td>
                        <td>{{ $user->getUniqueCometObservations() }} /
                            {{ \App\Models\CometObservationsOld::getUniqueObjectsObserved() }}</td>
                    </tr>

                    <tr>
                        <td>{{ __("Messier objects") }}</td>
                        <td></td>
                        <td>{{ $user->getObservedCountFromCatalog('M') }} / 110</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{{ __("Drawings of Messier objects") }}</td>
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
                        <td>{{ __("Drawings of Caldwell objects") }}</td>
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
                        <td>{{ __("Drawings of H400 objects") }}</td>
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
                        <td>{{ __("Drawings of H400-II objects") }}</td>
                        <td></td>
                        <td>{{ $user->getDrawingCountFromCatalog('H400-II') }} / 400</td>
                        <td></td>
                    </tr>

                    <tr>
                        <td>{{ __("Rank") }}</td>
                        <td>{{ $user->getRank() }} / {{ \App\Models\User::count() }} </td>
                        <td></td>
                    </tr>
                </table>

                <br />
                <x-button gray icon="eye" class="mb-2"
                          href='{{ config("app.old_url") }}/index.php?indexAction=result_selected_observations&observer={{ $user->username }}'
                >
                    {{ __("All observations of ") . $user->name }}
                </x-button>

                <x-button gray icon="pencil" class="mb-2"
                          href="{{ config('app.old_url') }}/index.php?indexAction=show_drawings&user={{ $user->username }}"
                >

                    {{ __("All drawings of ") . $user->name }}
                </x-button>

                @if (Auth::user() && $user->id != Auth::user()->id)
                    <x-button gray icon="envelope-open" class="mb-2"
                              href="{{ config('app.old_url') }}/index.php?indexAction=new_message&receiver={{ $user->username }}"
                    >
                        {{ __("Send message to ") . $user->name }}
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
        </div>
    </div>

    <script src="{{ $observationsPerYearChart->cdn() }}"></script>
    <script src="{{ $observationsPerMonthChart->cdn() }}"></script>
    <script src="{{ $objectTypesChart->cdn() }}"></script>

    {{ $observationsPerYearChart->script() }}
    {{ $observationsPerMonthChart->script() }}
    {{ $objectTypesChart->script() }}

</x-app-layout>
