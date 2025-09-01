<x-app-layout>
    <div class="mx-auto max-w-screen bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-4 grid-cols-1">
            <div class="col-span-1">
                @php
                    $image = $set->picture ? asset('storage/' . $set->picture) : asset('/images/instrumentset.png');
                @endphp
                <img class="w-64 mx-auto object-cover" src="{{ $image }}" alt="{{ $set->name }}">

                @if (!empty($set->description))
                    <div class="mt-4 p-3 border border-gray-700 bg-gray-800 text-gray-100 rounded">
                        {!! $set->description !!}
                    </div>
                @endif
            </div>

            <div class="col-span-2">
                <h4 class="font-bold text-xl">{{ $set->name }}
                    @if (!$set->active)
                        <div class="text-sm">{{ __("(Not active anymore)") }}</div>
                    @endif
                </h4>

                <br/>

                <table class="table-auto w-full">
                    <tr>
                        <td class="pr-6 align-top">{{ __("Owner") }}</td>
                        <td>
                            <a href="{{ route('observer.show', $set->user->slug) }}">{{ $set->user->name }}</a>
                        </td>
                    </tr>

                    <tr>
                        <td class="pr-6 align-top">{{ __("Instruments") }}</td>
                        <td>
                            @if ($set->instruments->count())
                                {!! $set->instruments->where('active', 1)->map(fn($e) => '<a href="/instrument/'.$e->user->slug.'/'.$e->slug.'">'.$e->name.'</a>')->implode(', ') !!}
                            @else
                                -
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="pr-6 align-top">{{ __("Eyepieces") }}</td>
                        <td>
                            @if ($set->eyepieces->count())
                                {!! $set->eyepieces->where('active', 1)->sortByDesc(fn($e) => $e->focal_length_mm ?? 0)->map(fn($e) => '<a href="/eyepiece/'.$e->user->slug.'/'.$e->slug.'">'.$e->name.'</a>')->implode(', ') !!}
                            @else
                                -
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="pr-6 align-top">{{ __("Filters") }}</td>
                        <td>
                            @if ($set->filters->count())
                                {!! $set->filters->where('active', 1)->map(fn($f) => '<a href="/filter/'.$f->user->slug.'/'.$f->slug.'">'.$f->name.'</a>')->implode(', ') !!}
                            @else
                                -
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="pr-6 align-top">{{ __("Lenses") }}</td>
                        <td>
                            @if ($set->lenses->count())
                                {!! $set->lenses->where('active', 1)->map(fn($l) => '<a href="/lens/'.$l->user->slug.'/'.$l->slug.'">'.$l->name.'</a>')->implode(', ') !!}
                            @else
                                -
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="pr-6 align-top">{{ __("Locations") }}</td>
                        <td>
                            @if ($set->locations->count())
                                {!! $set->locations->where('active', 1)->map(fn($loc) => '<a href="/location/'.$loc->user->slug.'/'.$loc->slug.'">'.$loc->name.'</a>')->implode(', ') !!}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>

                <br/>

                @auth
                    @if (Auth::user()->id == $set->user_id)
                        @foreach($set->instruments->where('active', 1) as $instrument)
                            @if ($instrument->focal_length_mm > 0 && $instrument->fixedMagnification == 0)
                                <h2 class="text-xl font-bold mt-6">{{ $instrument->fullName() }}</h2>
                                <br/>

                                <h3 class="text-lg font-semibold">{{ __('Without lenses') }}</h3>
                                <table class="table-auto w-full mb-4">
                                    <thead>
                                    <tr>
                                        <th class="text-left">{{ __('Eyepiece') }}</th>
                                        <th class="text-left">{{ __('Magnification') }}</th>
                                        <th class="text-left">{{ __('Field of View') }}</th>
                                        <th class="text-left">{{ __('Exit pupil') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($set->eyepieces->where('active', 1)->sortByDesc('focal_length_mm') as $eyepiece)
                                        <tr>
                                            <td>
                                                <a href="/eyepiece/{{ $eyepiece->user->slug }}/{{ $eyepiece->slug }}">{{ $eyepiece->name }}</a>
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

                                @foreach($set->lenses->where('active', 1)->sortBy('factor') as $lens)
                                    <br/>
                                    <h3 class="text-lg font-semibold">{{ $lens->name }} ({{ $lens->factor }}x)</h3>
                                    <table class="table-auto w-full mb-4">
                                        <thead>
                                        <tr>
                                            <th class="text-left">{{ __('Eyepiece') }}</th>
                                            <th class="text-left">{{ __('Magnification') }}</th>
                                            <th class="text-left">{{ __('Field of View') }}</th>
                                            <th class="text-left">{{ __('Exit pupil') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($set->eyepieces->where('active', 1)->sortByDesc('focal_length_mm') as $eyepiece)
                                            <tr>
                                                <td>
                                                    <a href="/eyepiece/{{ $eyepiece->user->slug }}/{{ $eyepiece->slug }}">{{ $eyepiece->name }}</a>
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
                        @endforeach
                    @endif

                    @if (Auth::user()->id == $set->user_id || Auth::user()->isAdministrator())
                        <a href="/instrumentset/{{ $set->user->slug }}/{{ $set->slug }}/edit">
                            <x-button type="submit" secondary label="{{ __('Edit') }} {!! $set->name !!}"/>
                        </a>
                    @endif
                @endauth

            </div>
        </div>
    </div>
</x-app-layout>
