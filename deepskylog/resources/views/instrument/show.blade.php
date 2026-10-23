@php use App\Models\Eyepiece;use App\Models\Lens; @endphp
<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-4 grid-cols-1">
                <div class="col-span-1">
                    <img class="w-64 mx-auto object-cover" src="{{ $image }}"
                         alt="{{ $instrument->fullName() }}">
                    @if (!empty($instrument->description))
                        <div class="mt-4 p-3 border border-gray-700 bg-gray-800 text-gray-100 rounded prose prose-invert">
                            {!! \App\Support\Html::purify($instrument->description, true) !!}
                        </div>
                    @endif
                </div>

                <div class="col-span-2">
                    <h4 class="font-bold text-xl">{{ $instrument->fullName() }}
                        @if (!$instrument->instrumentactive)
                            <div class="text-sm">{{ __("(Not active anymore)") }}</div>
                        @endif
                    </h4>
                    <br/>
                    <table class="table-auto w-full">
                        <tr>
                            <td>{{ __("Instrument Type") }}</td>
                            <td>{{ __($instrument->instrument_type->name) }}
                                @if ($instrument->instrument_type->name != "Naked Eye" && $instrument->instrument_type->name != "Binoculars" && $instrument->instrument_type->name != "Finderscope")
                                    {{ __("on") }} {{ __($instrument->mount_type->name) }} {{ __("mount") }}
                                @endif
                            </td>

                        </tr>

                        <tr>
                            <td>{{ __("Diameter") }}</td>
                            @auth
                                <td>{{ Auth::user()->showInches ? (number_format($instrument->diameter / 25.4, 2, '.', ',')) . ' ' . __('inch') : $instrument->diameter . ' ' . __('mm')}}
                                </td>
                            @endauth
                            @guest
                                <td>{{ $instrument->diameter . ' ' . __('mm')}}</td>
                            @endguest
                        </tr>

                        @if ($instrument->fd  > 0 && $instrument->instrument_type->name != "Naked Eye")
                            <tr>
                                <td>{{ __("Focal Length") }}</td>
                                @auth
                                    <td>{{ Auth::user()->showInches ? (number_format($instrument->focal_length_mm / 25.4, 2, '.' ,',')) . ' ' . __('inch') : round($instrument->focal_length_mm, 1) . ' ' . __('mm')}}
                                        (F/{{ $instrument->fd }})
                                    </td>
                                @endauth
                                @guest
                                    <td>{{ round($instrument->focal_length_mm, 1) . ' ' . __('mm')}}</td>
                                @endguest
                            </tr>
                        @endif

                        @if ($instrument->fixedMagnification)
                            <tr>
                                <td>{{ __("Fixed Magnification") }}</td>
                                <td>{{ $instrument->fixedMagnification }}</td>
                            </tr>
                        @endif

                        @if ((float) $instrument->fixedMagnification > 0)
                            <tr>
                                <td>{{ __("Field of view") }}</td>
                                <td>
                                    {{ \App\Services\FieldViews\FieldViewBuilder::formatAngle($instrument->fixedFieldOfViewDeg()) }}
                                    @if ($instrument->hasEstimatedFieldOfView())
                                        ({{ __('estimated') }})
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @if ($instrument->lens)
                            <tr>
                                <td>{{ __("Lens") }}</td>
                                <td>
                                    <a href="{{ url('/lens/' . $instrument->lens->user->slug . '/' . $instrument->lens->slug) }}">{{ html_entity_decode($instrument->lens->name) }}</a>
                                </td>
                            </tr>
                        @endif

                        @if ($instrument->finder)
                            <tr>
                                <td>{{ __("Finderscope") }}</td>
                                <td>
                                    <a href="{{ url('/instrument/' . $instrument->finder->user->slug . '/' . $instrument->finder->slug) }}">{{ html_entity_decode($instrument->finder->fullName()) }}</a>
                                </td>
                            </tr>
                        @endif

                        @if ($instrument->obstruction_perc)
                            <tr>
                                <td>{{ __("Central obstruction") }}</td>
                                <td>{{ $instrument->obstruction_perc }}%</td>
                            </tr>
                        @endif

                        <tr>
                            <td>{{ __("Owner") }}</td>
                            <td>
                                <a href="{{ route('observer.show', $instrument->user->slug) }}">{{  $instrument->user->name }}</a>
                            </td>
                        </tr>

                        <tr>
                            <td>{{ __("Number of observations") }}</td>
                            <td>
                                {{--                                    <a href="/observation/instrument/{{ $instrument->id }}">--}}
                                {{  $instrument->observations }}
                                {{--                                    </a>--}}
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __("First light") }}</td>
                            <td>
                                @php
                                    $first_observation_date = $instrument->first_observation_date();
                                    $last_observation_date = $instrument->last_observation_date();
                                @endphp
                                @if (! is_null($first_observation_date[0]) && $first_observation_date[1])
                                    <a href="{{ $first_observation_date[1]->detailUrl() }}">
                                        {{ $first_observation_date[0] }}
                                    </a>
                                @else
                                    {{ __("No observations added!") }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>{{ __("Last used on") }}</td>
                            <td>
                                @if (! is_null($last_observation_date[0]) && $last_observation_date[1])
                                    <a href="{{ $last_observation_date[1]->detailUrl() }}">
                                        {{ $last_observation_date[0] }}
                                    </a>
                                @else
                                    {{ __("No observations added!") }}
                                @endif
                            </td>
                        </tr>
                        @auth
                            @if ($instrument->user_id == Auth::user()->id)
                                <tr>
                                    <td>{{ __("Used eyepieces") }}</td>
                                    <td>{!! $instrument->get_used_eyepieces_as_string() !!}</td>
                                </tr>

                                <tr>
                                    <td>{{ __("Used filters") }}</td>
                                    <td>{!! $instrument->get_used_filters_as_string() !!}</td>
                                </tr>

                                <tr>
                                    <td>{{ __("Used lenses") }}</td>
                                    <td>{!! $instrument->get_used_lenses_as_string() !!}</td>
                                </tr>

                                <tr>
                                    <td>{{ __("Observed in the following locations") }}</td>
                                    <td>{!! $instrument->get_used_locations_as_string() !!}</td>
                                </tr>
                                @php($mapLocations = $instrument->usedLocationsForMap(Auth::user()))
                                @if (!empty($mapLocations))
                                    <tr>
                                        <td colspan="2">
                                            <div id="instrument-locations-map" class="w-full mt-2 rounded overflow-hidden" style="height: 20rem; z-index:1;"
                                                 data-locations='@json($mapLocations)'
                                                 data-label="{{ __(':count observation(s) with this instrument') }}"></div>
                                        </td>
                                    </tr>
                                @endif

                            @endif
                        @endauth
                        <tr>
                            <td>{{ __("Image orientation") }}</td>
                            <td>
                                @if ($instrument->flip_image == 0 && $instrument->flop_image == 0)
                                    <img class="w-64 mx-left object-cover" src="/images/unmirrored.png"
                                         alt="{{ __("Unmirrored image") }}">
                                @elseif ($instrument->flip_image == 1 && $instrument->flop_image == 0)
                                    <img class="w-64 mx-left object-cover" src="/images/flip.png"
                                         alt="{{ __("Flipped image") }}">
                                @elseif ($instrument->flip_image == 0 && $instrument->flop_image == 1)
                                    <img class="w-64 mx-left object-cover" src="/images/flop.png"
                                         alt="{{ __("Flopped image") }}">
                                @elseif ($instrument->flip_image == 1 && $instrument->flop_image == 1)
                                    <img class="w-64 mx-left object-cover" src="/images/flipflop.png"
                                         alt="{{ __("Flipped and flopped image") }}">
                                @endif
                            </td>
                        </tr>
                    </table>

                    @auth
                        @if (Auth::user()->id == $instrument->user_id || Auth::user()->isAdministrator())
                            <a href="/instrument/{{$instrument->user->slug}}/{{$instrument->slug }}/edit">
                                <x-button type="submit" secondary label="{{ __('Edit') }} {!! $instrument->name !!}"/>
                            </a>
                        @endif

                        <br/>
                        <br/>
                        @if ($instrument->user_id == Auth::user()->id)
                            @if ($instrument->focal_length_mm > 0 && $instrument->fixedMagnification == 0)
                                <h2 class="text-xl text-bold">{{ __("Without lenses") }}</h2>
                                <table>
                                    <thead>
                                    <tr>
                                        <th class="text-left">{{ __("Eyepiece") }}</th>
                                        <th class="text-left">{{ __("Magnification") }}</th>
                                        <th class="text-left">{{ __("Field of View") }}</th>
                                        <th class="text-left">{{ __("Exit pupil") }}</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach(Eyepiece::where('user_id', Auth::user()->id)->where('active', 1)->orderByFocalLength('desc')->get() as $eyepiece)
                                        <tr>
                                            <td>
                                                <a href="/eyepiece/{{ Auth::user()->slug }}/{{ $eyepiece->slug }}">{{ $eyepiece->name }}</a>
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
                                            <th class="text-left">{{ __("Eyepiece") }}</th>
                                            <th class="text-left">{{ __("Magnification") }}</th>
                                            <th class="text-left">{{ __("Field of View") }}</th>
                                            <th class="text-left">{{ __("Exit pupil") }}</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach(Eyepiece::where('user_id', Auth::user()->id)->where('active', 1)->orderByFocalLength('desc')->get() as $eyepiece)
                                            <tr>
                                                <td>
                                                    <a href="/eyepiece/{{ Auth::user()->slug }}/{{ $eyepiece->slug }}">{{ $eyepiece->name }}</a>
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
                        @endif
                    @endauth

                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var mapEl = document.getElementById('instrument-locations-map');
                if (!mapEl) return;

                var locations = JSON.parse(mapEl.getAttribute('data-locations') || '[]');
                var label = mapEl.getAttribute('data-label') || '';

                function escapeHtml(str) {
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                }

                var map = L.map('instrument-locations-map', { fullscreenControl: true });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                var bounds = [];
                locations.forEach(function (loc) {
                    var coords = [loc.latitude, loc.longitude];
                    bounds.push(coords);
                    L.marker(coords).addTo(map).bindPopup(
                        '<a href="' + escapeHtml(loc.url) + '"><strong>' + escapeHtml(loc.name) + '</strong></a><br>'
                        + escapeHtml(label.replace(':count', loc.observations))
                    );
                });

                if (bounds.length === 1) {
                    map.setView(bounds[0], 10);
                } else {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 10 });
                }
            });
        </script>
    @endpush
</x-app-layout>
