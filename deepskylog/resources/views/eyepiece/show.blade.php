@php use App\Models\Instrument;use App\MOdels\Lens; @endphp
<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-4 grid-cols-1">
                <div class="col-span-1">
                    <img class="w-64 mx-auto object-cover" src="{{ $image }}"
                         alt="{{ $eyepiece->name }}">
                    @if (!empty($eyepiece->description))
                        <div class="mt-4 p-3 border border-gray-700 bg-gray-800 text-gray-100 rounded">
                            {!! $eyepiece->description !!}
                        </div>
                    @endif
                </div>

                <div class="col-span-2">
                    <h4 class="font-bold text-xl">{{ $eyepiece->fullName() }}
                        @if (!$eyepiece->active)
                            <div class="text-sm">{{ __("(Not active anymore)") }}</div>
                        @endif
                    </h4>
                    <br/>
                    <table class="table-auto w-full">
                        <tr>
                            <td>{{ __("Eyepiece Make") }}</td>
                            <td>{{ __($eyepiece->eyepiece_make->name) }}
                            </td>

                        </tr>

                        <tr>
                            <td>{{ __("Eyepiece Type") }}</td>
                            <td>{{ __($eyepiece->eyepiece_type->name) }}
                            </td>

                        </tr>

                        <tr>
                            <td>{{ __("Focal Length") }}</td>
                            <td>{{ $eyepiece->focal_length_mm }} mm
                            </td>
                        </tr>

                        @if ($eyepiece->max_focal_length_mm > 0)
                            <tr>
                                <td>{{ __("Max focal length (zoom eyepiece)") }}</td>
                                <td>{{ $eyepiece->max_focal_length_mm }} mm</td>
                            </tr>
                        @endif

                        <tr>
                            <td>{{ __("Apparent Field of View") }}</td>
                            <td>{{ $eyepiece->apparentFOV }}°</td>
                        </tr>

                        @if ($eyepiece->field_stop_mm)
                            <tr>
                                <td>{{ __("Field stop") }}</td>
                                <td>{{ $eyepiece->field_stop_mm }} mm</td>
                            </tr>
                        @endif

                        <tr>
                            <td>{{ __("Owner") }}</td>
                            <td>
                                <a href="{{ route('observer.show', $eyepiece->user->slug) }}">{{  $eyepiece->user->name }}</a>
                            </td>
                        </tr>

                        <tr>
                            <td>{{ __("Number of observations") }}</td>
                            <td>
                                {{--                                    <a href="/observation/instrument/{{ $eyepiece->id }}">--}}
                                {{  $eyepiece->observations }}
                                {{--                                    </a>--}}
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __("First light") }}</td>
                            <td>
                                @php
                                    $first_observation_date = $eyepiece->first_observation_date();
                                    $last_observation_date = $eyepiece->last_observation_date();
                                @endphp
                                @if (! is_null($first_observation_date[0]))
                                    @if ($first_observation_date[1] > 0)
                                        <a
                                            href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $first_observation_date[1] }}"
                                        >
                                            {{$first_observation_date[0] }}
                                        </a>
                                    @else
                                        <a
                                            href="{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ -$first_observation_date[1] }}"
                                        >
                                            {{ $first_observation_date[0] }}
                                        </a>
                                    @endif
                                @else
                                    {{ __("No observations added!") }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>{{ __("Last used on") }}</td>
                            <td>
                                @if (! is_null($last_observation_date[0]))
                                    @if ($last_observation_date[1] > 0)
                                        <a
                                            href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $last_observation_date[1] }}"
                                        >
                                            {{ $last_observation_date[0] }}
                                        </a>
                                    @else
                                        <a
                                            href="{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ -$last_observation_date[1] }}"
                                        >
                                            {{ $last_observation_date[0] }}
                                        </a>
                                    @endif
                                @else
                                    {{ __("No observations added!") }}
                                @endif
                            </td>
                        </tr>
                        @auth
                            @if ($eyepiece->user_id == Auth::user()->id)
                                <tr>
                                    <td>{{ __("Used with the following telescopes") }}</td>
                                    <td>{!! $eyepiece->get_used_instruments_as_string() !!}</td>
                                </tr>
                            @endif
                        @endauth
                    </table>

                    @auth
                        <br/>
                        @if (Auth::user()->id == $eyepiece->user_id || Auth::user()->isAdministrator())
                            <a href="/eyepiece/{{$eyepiece->user->slug}}/{{$eyepiece->slug }}/edit">
                                <x-button type="submit" secondary label="{{ __('Edit') }} {!! $eyepiece->name !!}"/>
                            </a>
                        @endif

                        <br/>
                        <br/>
                        @if ($eyepiece->user_id == Auth::user()->id)
                            <h2 class="text-xl text-bold">{{ __("Without lenses") }}</h2>
                            <table>
                                <thead>
                                <tr>
                                    <th class="text-left">{{ __("Instrument") }}</th>
                                    <th class="text-left">{{ __("Magnification") }}</th>
                                    <th class="text-left">{{ __("Field of View") }}</th>
                                    <th class="text-left">{{ __("Exit pupil") }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach(Instrument::where('user_id', Auth::user()->id)->where('active', 1)->where('focal_length_mm', '>', 1)->get()->sortBy('aperture_mm', SORT_NATURAL, true) as $instrument)
                                    <tr>
                                        <td>
                                            <a href="/instrument/{{ Auth::user()->slug }}/{{ $instrument->slug }}">{{ $instrument->name }}</a>
                                        </td>
                                        <td>
                                            {{ $instrument->magnification($eyepiece) }}
                                        </td>
                                        <td>
                                            {{ $instrument->field_of_view($eyepiece) }}
                                        </td>
                                        <td>
                                            {{ $instrument->exit_pupil($eyepiece) }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            @foreach(Lens::where('user_id', Auth::user()->id)->where('active', 1)->get()->sortBy('factor', SORT_NATURAL) as $lens)
                                <br/>
                                <h2 class="text-xl text-bold">{{ $lens->name }} ({{ $lens->factor }}x)</h2>
                                <table>
                                    <thead>
                                    <tr>
                                        <th class="text-left">{{ __("Instrument") }}</th>
                                        <th class="text-left">{{ __("Magnification") }}</th>
                                        <th class="text-left">{{ __("Field of View") }}</th>
                                        <th class="text-left">{{ __("Exit pupil") }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach(Instrument::where('user_id', Auth::user()->id)->where('active', 1)->where('focal_length_mm', '>', 1)->get()->sortBy('aperture_mm', SORT_NATURAL, true) as $instrument)
                                        <tr>
                                            <td>
                                                <a href="/instrument/{{ Auth::user()->slug }}/{{ $instrument->slug }}">{{ $instrument->name }}</a>
                                            </td>
                                            <td>
                                                {{ $instrument->magnification($eyepiece, $lens) }}
                                            </td>
                                            <td>
                                                {{ $instrument->field_of_view($eyepiece, $lens) }}
                                            </td>
                                            <td>
                                                {{ $instrument->exit_pupil($eyepiece, $lens) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                        @endif
                    @endauth

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
