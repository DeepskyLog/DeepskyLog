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
                                href="{{ env("OLD_APP_URL") }}/index.php?indexAction=detail_observation&observation={{ $user->firstObservationDate()[1] }}"
                            >
                                {{ $user->firstObservationDate()[0] }}
                            </a>
                            <br />
                            {{ __("Most recent observation on ") }}
                            <a
                                href="{{ env("OLD_APP_URL") }}/index.php?indexAction=detail_observation&observation={{ $user->lastObservationDate()[1] }}"
                            >
                                {{ $user->lastObservationDate()[0] }}
                            </a>
                        @else
                            {{ __("No observations added!") }}
                        @endif
                    </div>
                </div>
                @if ($user->about)
                    <div class="col-span-1 row-span-2">
                        <div class="col-span-1">
                            <x-card class="prose max-w-none dark:prose-invert">
                                {!! $user->about !!}
                            </x-card>
                        </div>
                    </div>
                @endif
            </div>

            <br />

            <div class="grid grid-cols-2">
                <div class="col-span-1">
                    {{-- TODO: Add extra information --}}
                </div>
                <div class="col-span-1">
                    {{-- Show DeepskyLog stars: Throphee Gallery --}}
                    <x-card>
                        <h2 class="mb-2 mt-2 text-xl font-bold">
                            {{ __("DeepskyLog Trophy Gallery") }}
                        </h2>
                        <div class="flex flex-wrap gap-x-6 gap-y-4">
                            @if ($user->isEarlyAdopter())
                                <x-trophy.earlyAdopter class="h-20 w-20" />
                            @endif

                            {{-- MESSIER --}}
                            @if ($user->hasMessierGold())
                                <x-trophy.messierGold class="h-20 w-20" />
                            @elseif ($user->hasMessierSilver())
                                <x-trophy.messierSilver class="h-20 w-20" />
                            @elseif ($user->hasMessierBronze())
                                <x-trophy.messierBronze class="h-20 w-20" />
                            @endif
                            @if ($user->hasMessierGoldDrawing())
                                <x-trophy.messierGoldDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasMessierSilverDrawing())
                                <x-trophy.messierSilverDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasMessierBronzeDrawing())
                                <x-trophy.messierBronzeDrawing
                                    class="h-20 w-20"
                                />
                            @endif
                            {{-- CALDWELL --}}
                            @if ($user->hasCaldwellGold())
                                <x-trophy.caldwellGold class="h-20 w-20" />
                            @elseif ($user->hasCaldwellSilver())
                                <x-trophy.caldwellSilver class="h-20 w-20" />
                            @elseif ($user->hasCaldwellBronze())
                                <x-trophy.caldwellBronze class="h-20 w-20" />
                            @endif
                            @if ($user->hasCaldwellGoldDrawing())
                                <x-trophy.caldwellGoldDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasCaldwellSilverDrawing())
                                <x-trophy.caldwellSilverDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasCaldwellBronzeDrawing())
                                <x-trophy.caldwellBronzeDrawing
                                    class="h-20 w-20"
                                />
                            @endif
                            {{-- HERSCHEL 400 --}}
                            @if ($user->hasHerschel400Platinum())
                                <x-trophy.herschel400Platinum
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschel400Diamond())
                                <x-trophy.herschel400Diamond
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschel400Gold())
                                <x-trophy.herschel400Gold class="h-20 w-20" />
                            @elseif ($user->hasHerschel400Silver())
                                <x-trophy.herschel400Silver class="h-20 w-20" />
                            @elseif ($user->hasHerschel400Bronze())
                                <x-trophy.herschel400Bronze class="h-20 w-20" />
                            @endif
                            @if ($user->hasHerschel400PlatinumDrawing())
                                <x-trophy.herschel400PlatinumDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschel400DiamondDrawing())
                                <x-trophy.herschel400DiamondDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschel400GoldDrawing())
                                <x-trophy.herschel400GoldDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschel400SilverDrawing())
                                <x-trophy.herschel400SilverDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschel400BronzeDrawing())
                                <x-trophy.herschel400BronzeDrawing
                                    class="h-20 w-20"
                                />
                            @endif
                            {{-- HERSCHEL II --}}
                            @if ($user->hasHerschelIIPlatinum())
                                <x-trophy.herschelIIPlatinum
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschelIIDiamond())
                                <x-trophy.herschelIIDiamond class="h-20 w-20" />
                            @elseif ($user->hasHerschelIIGold())
                                <x-trophy.herschelIIGold class="h-20 w-20" />
                            @elseif ($user->hasHerschelIISilver())
                                <x-trophy.herschelIISilver class="h-20 w-20" />
                            @elseif ($user->hasHerschelIIBronze())
                                <x-trophy.herschelIIBronze class="h-20 w-20" />
                            @endif
                            @if ($user->hasHerschelIIPlatinumDrawing())
                                <x-trophy.herschelIIPlatinumDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschelIIDiamondDrawing())
                                <x-trophy.herschelIIDiamondDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschelIIGoldDrawing())
                                <x-trophy.herschelIIGoldDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschelIISilverDrawing())
                                <x-trophy.herschelIISilverDrawing
                                    class="h-20 w-20"
                                />
                            @elseif ($user->hasHerschelIIBronzeDrawing())
                                <x-trophy.herschelIIBronzeDrawing
                                    class="h-20 w-20"
                                />
                            @endif
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Personal tab -->
            <div class="tab-pane active" id="info">
                <table class="table-auto">
                    <!-- Default location -->
                    <tr>
                        <td>{{ __("Default observing site") }}</td>
                        <td>
                            @if ($user->stdlocation)
                                <a
                                    href="{{ env("OLD_APP_URL") }}/index.php?indexAction=detail_location&location={{ $user->stdlocation }}"
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
                                    href="{{ env('OLD_APP_URL') }}/index.php?indexAction=detail_instrument&instrument={{ $user->stdtelescope }}"
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
                            @if ($user->id === Auth::user()->id)
                                <a href="{{ env('OLD_APP_URL') }}/index.php?indexAction=view_sites">
                                    @endif
                                    {{ \App\Models\LocationsOld::where(["observer" => $user->username])->count() }}
                                    @if ($user->id === Auth::user()->id)
                                </a>
                            @endif
                        </td>
                    </tr>

                    <!-- Number of instruments -->
                    <tr>
                        <td>{{ __("Number of instruments") }}</td>
                        <td>
                            {{--
                                @if ($user->id === Auth::user()->id)
                                <a href="{{ route('instrument.index') }}">
                                @endif
                                {{ count($user->instruments) }}
                                @if ($user->id === Auth::user()->id)
                                </a>
                                @endif
                            --}}
                        </td>
                    </tr>

                    <!-- Number of eyepieces -->
                    <tr>
                        <td>{{ __("Number of eyepieces") }}</td>
                        <td>
                            {{--
                                @if ($user->id === Auth::user()->id)
                                <a href="{{ route('eyepiece.index') }}">
                                @endif
                                {{ count($user->eyepieces) }}
                                @if ($user->id === Auth::user()->id)
                                </a>
                                @endif
                            --}}
                        </td>
                    </tr>

                    <!-- Number of filters -->
                    <tr>
                        <td>{{ __("Number of filters") }}</td>
                        <td>
                            {{--
                                @if ($user->id === Auth::user()->id)
                                <a href="{{ route('filter.index') }}">
                                @endif
                                {{ count($user->filters) }}
                                @if ($user->id === Auth::user()->id)
                                </a>
                                @endif
                            --}}
                        </td>
                    </tr>

                    <!-- Number of lenses -->
                    <tr>
                        <td>{{ __("Number of lenses") }}</td>
                        <td>
                            {{--
                                @if ($user->id === Auth::user()->id)
                                <a href="{{ route('lens.index') }}">
                                @endif
                                {{ count($user->lenses) }}
                                @if ($user->id === Auth::user()->id)
                                </a>
                                @endif
                            --}}
                        </td>
                    </tr>

                    <!-- Number of observing lists -->
                    <tr>
                        <td>{{ __("Number of observing lists") }}</td>
                        <td>
                            {{--
                                @if ($user->id === Auth::user()->id)
                                <a href="{{ route('observationList.index') }}">
                                @endif
                                {{ count($user->observingLists) }}
                                @if ($user->id === Auth::user()->id)
                                </a>
                                @endif
                            --}}
                        </td>
                    </tr>

                    <!-- Number of equipment sets -->
                    <tr>
                        <td>{{ __("Number of equipment sets") }}</td>
                        <td>
                            {{--
                                @if ($user->id === Auth::user()->id)
                                <a href="{{ route('set.index') }}">
                                @endif
                                {{ $user->sets()->count() }}
                                @if ($user->id === Auth::user()->id)
                                </a>
                                @endif
                            --}}
                        </td>
                    </tr>

                    <!-- Country of residence -->
                    <tr>
                        <td>{{ __("Country of residence") }}</td>
                        <td>
                            {{--
                                @if ($user->country != '')
                                {{ Countries::getOne($user->country, LaravelGettext::getLocaleLanguage()) }}
                                @endif
                            --}}
                        </td>
                    </tr>

                    <!-- Copyright notice -->
                    <tr>
                        <td>{{ __("Copyright notice") }}</td>
                        <td>{!! $user->getCopyright() !!}</td>
                    </tr>
                </table>

                <table class="table-striped table-sm table">
                    <tr>
                        <th></th>
                        <th>{{ __("Total") }}</th>
                        {{--
                            @foreach ($observationTypes as $type)
                            <th>{{ __($type->name) }}</th>
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("Number of observations") }}</td>
                        <td>36 / 6000 (0.06%)</td>
                        {{--
                            @foreach ($observationTypes as $type)
                            <td>6 / 1000 (0.06%)</td>
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("Observations last year") }}</td>
                        <td>30 / 300 (10.0%)</td>
                        {{--
                            @foreach ($observationTypes as $type)
                            <td>5 / 50 (10.0%)</td>
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("Number of drawings") }}</td>
                        <td>24 / 1200 (0.5%)</td>
                        {{--
                            @foreach ($observationTypes as $type)
                            <td>4 / 2000 (0.5%)</td>
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("Drawings last year") }}</td>
                        <td>6 / 60 (1.0%)</td>
                        {{--
                            @foreach ($observationTypes as $type)
                            <td>1 / 10 (1.0%)</td>
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("Different objects") }}</td>
                        {{--
                            <td>0 / {{ \App\Models\Target::count() }}
                            ({{ number_format((0 / \App\Models\Target::count()) * 100, 2) }}%)</td>
                            @foreach ($observationTypes as $type)
                            <td>0 / {{ $numberOfObjects[$type->type] }}
                            (@if ($numberOfObjects[$type->type] == 0)
                            0%)
                            @else
                            {{ number_format((0 / $numberOfObjects[$type->type]) * 100, 2) }}%) </beautify
                            end="
                            @endif">
                            </td>
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("Messier objects") }}</td>
                        <td></td>
                        {{--
                            @foreach ($observationTypes as $type)
                            @if ($type->type == 'ds')
                            <td>110 / 110 (100%)</td>
                            @else
                            <td></td>
                            @endif
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("Caldwell objects") }}</td>
                        <td></td>
                        {{--
                            @foreach ($observationTypes as $type)
                            @if ($type->type == 'ds')
                            <td>11 / 110 (10%)</td>
                            @else
                            <td></td>
                            @endif
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("H400 objects") }}</td>
                        <td></td>
                        {{--
                            @foreach ($observationTypes as $type)
                            @if ($type->type == 'ds')
                            <td>48 / 400 (1.2%)</td>
                            @else
                            <td></td>
                            @endif
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("H400-II objects") }}</td>
                        <td></td>
                        {{--
                            @foreach ($observationTypes as $type)
                            @if ($type->type == 'ds')
                            <td>24 / 400 (0.6%)</td>
                            @else
                            <td></td>
                            @endif
                            @endforeach
                        --}}
                    </tr>

                    <tr>
                        <td>{{ __("Rank") }}</td>
                        <td>17 / 255</td>
                        {{--
                            @foreach ($observationTypes as $type)
                            <td>12 / 123</td>
                            @endforeach
                        --}}
                    </tr>
                </table>

                <br />
                <a
                    class="btn btn-success"
                    href="/observations/user/{{ $user->slug }}"
                >
                    <svg
                        width="1.1em"
                        height="1.1em"
                        viewBox="0 1 16 16"
                        class="bi bi-eye-fill inline"
                        fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"
                        />
                        <path
                            fill-rule="evenodd"
                            d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"
                        />
                    </svg>
                    &nbsp;{{ __("All observations of ") . $user->name }}
                </a>

                <a
                    class="btn btn-success"
                    href="/observations/drawings/user/{{ $user->slug }}"
                >
                    <svg
                        width="1em"
                        height="1em"
                        viewBox="0 1 16 16"
                        class="bi bi-pencil inline"
                        fill="currentColor"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M11.293 1.293a1 1 0 0 1 1.414 0l2 2a1 1 0 0 1 0 1.414l-9 9a1 1 0 0 1-.39.242l-3 1a1 1 0 0 1-1.266-1.265l1-3a1 1 0 0 1 .242-.391l9-9zM12 2l2 2-9 9-3 1 1-3 9-9z"
                        />
                        <path
                            fill-rule="evenodd"
                            d="M12.146 6.354l-2.5-2.5.708-.708 2.5 2.5-.707.708zM3 10v.5a.5.5 0 0 0 .5.5H4v.5a.5.5 0 0 0 .5.5H5v.5a.5.5 0 0 0 .5.5H6v-1.5a.5.5 0 0 0-.5-.5H5v-.5a.5.5 0 0 0-.5-.5H3z"
                        />
                    </svg>
                    &nbsp;{{ __("All drawings of ") . $user->name }}
                </a>

                @if (Auth::user() && $user->id != Auth::user()->id)
                    <a
                        class="btn btn-primary"
                        href="/messages/create/{{ $user->id }}"
                    >
                        <svg
                            width="1em"
                            height="1em"
                            viewBox="0 1 16 16"
                            class="bi bi-envelope inline"
                            fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383l-4.758 2.855L15 11.114v-5.73zm-.034 6.878L9.271 8.82 8 9.583 6.728 8.82l-5.694 3.44A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.739zM1 11.114l4.758-2.876L1 5.383v5.73z"
                            />
                        </svg>
                        &nbsp;{{ __("Send message to ") . $user->name }}
                    </a>
                @endif
            </div>

            <div class="tab-pane" id="observationsPerYear">
                <div id="observationsPerYear"></div>

                {{-- {!! $observationsPerYear !!} --}}
            </div>

            <!-- The observations per month page -->
            <div class="tab-pane" id="observationsPerMonth">
                <div id="observationsPerMonth"></div>

                {{-- {!! $observationsPerMonth !!} --}}
            </div>
        </div>
    </div>
</x-app-layout>
