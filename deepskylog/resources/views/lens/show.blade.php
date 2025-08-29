@php use App\Models\Lens; @endphp
<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-4 grid-cols-1">
                <div class="col-span-1">
                    <img class="w-64 mx-auto object-cover" src="{{ $image }}"
                         alt="{{ $lens->name }}">
                    @if (!empty($lens->description))
                        <div class="mt-4 p-3 border border-gray-700 bg-gray-800 text-gray-100 rounded">
                            {!! $lens->description !!}
                        </div>
                    @endif
                </div>

                <div class="col-span-2">
                    <h4 class="font-bold text-xl">{{ $lens->name }}
                        @if (!$lens->active)
                            <div class="text-sm">{{ __("(Not active anymore)") }}</div>
                        @endif
                    </h4>
                    <br/>
                    <table class="table-auto w-full">
                        <tr>
                            <td>{{ __("Lens Make") }}</td>
                            <td>{{ __($lens->lens_make->name) }}
                            </td>
                        </tr>

                        <tr>
                            <td>{{ __("Factor") }}</td>
                            <td>{{ $lens->factor . __('x')}}</td>
                        </tr>


                        <tr>
                            <td>{{ __("Owner") }}</td>
                            <td>
                                <a href="{{ route('observer.show', $lens->user->slug) }}">{{  $lens->user->name }}</a>
                            </td>
                        </tr>

                        @auth
                            @if ($lens->user_id == Auth::user()->id)
                                {{--                                @if($instrument->sets()->count())--}}
                                {{--                                    <tr>--}}
                                {{--                                        <td>{{ __('In equipment sets') }}</td>--}}
                                {{--                                        <td>--}}
                                {{--                                            <div class="trix-content">--}}
                                {{--                                                <ul>--}}
                                {{--                                                    @foreach($instrument->sets()->get() as $set)--}}
                                {{--                                                        <li><a href="/set/{{ $set->id }}">{{ $set->name }}</a></li>--}}
                                {{--                                                    @endforeach--}}
                                {{--                                                </ul>--}}
                                {{--                                            </div>--}}
                                {{--                                        </td>--}}
                                {{--                                    </tr>--}}
                                {{--                                @endif--}}
                            @endif
                        @endauth

                        <tr>
                            <td>{{ __("Number of observations") }}</td>
                            <td>
                                {{--                                    <a href="/observation/instrument/{{ $instrument->id }}">--}}
                                {{  $lens->observations }}
                                {{--                                    </a>--}}
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __("First light") }}</td>
                            <td>
                                @php
                                    $first_observation_date = $lens->first_observation_date();
                                    $last_observation_date = $lens->last_observation_date();
                                @endphp
                                @if (! is_null($first_observation_date[0]))
                                    <a
                                        href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $first_observation_date[1] }}"
                                    >
                                        {{$first_observation_date[0] }}
                                    </a>
                                @else
                                    {{ __("No observations added!") }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td>{{ __("Last used on") }}</td>
                            <td>
                                @if (! is_null($last_observation_date[0]))
                                    <a
                                        href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $last_observation_date[1] }}"
                                    >
                                        {{ $last_observation_date[0] }}
                                    </a>
                                @else
                                    {{ __("No observations added!") }}
                                @endif
                            </td>
                        </tr>
                    </table>

                    @auth
                        @if (Auth::user()->id == $lens->user_id || Auth::user()->isAdministrator())
                            <br/>
                            <a href="/lens/{{$lens->user->slug}}/{{$lens->slug }}/edit">
                                <x-button type="submit" secondary label="{{ __('Edit') }} {!! $lens->name !!}"/>
                            </a>
                        @endif

                        <br/>
                        <br/>
                    @endauth

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
