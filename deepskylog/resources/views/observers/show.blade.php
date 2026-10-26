@php use App\Models\Observation; @endphp
@php use App\Models\User; @endphp

@extends('layouts.app')

@section('content')
    <div>
        <div class="mx-auto max-w-screen bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="grids-rows-2 grid grid-cols-2">
                <div class="col-span-1">
                    <!-- Current Profile Photo -->
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                        class="h-20 w-20 rounded-full object-cover" />
                    <h2 class="mt-4 bg-gray-900 text-xl font-semibold leading-tight">
                        {{ $user->name }}
                    </h2>
                    <div class="mb-2 mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-400">
                        <span>{{ trans_choice(':count follower|:count followers', $followersCount, ['count' => $followersCount]) }}</span>
                        @auth
                            @if ($isFollowing)
                                <form method="POST" action="{{ route('observer.unfollow', ['observer' => $user->slug]) }}">
                                    @csrf
                                    <x-button type="submit" xs gray icon="user-minus" label="{{ __('Unfollow') }}" />
                                </form>
                            @elseif ($canFollow)
                                <form method="POST" action="{{ route('observer.follow', ['observer' => $user->slug]) }}">
                                    @csrf
                                    <x-button type="submit" xs primary icon="user-plus" label="{{ __('Follow') }}" />
                                </form>
                            @endif
                        @endauth
                    </div>
                    @if ($followerList !== null)
                        {{-- Only on one's own page: who follows you, and whom you follow --}}
                        @foreach ([[__('Your followers'), $followerList], [__('Observers you follow'), $followingList]] as [$followLabel, $followUsers])
                            @if ($followUsers->isNotEmpty())
                                <div class="mb-2 text-sm">
                                    <span class="text-gray-400">{{ $followLabel }}:</span>
                                    @foreach ($followUsers as $followUser)
                                        <a class="hover:underline" href="{{ route('observer.show', ['observer' => $followUser->slug]) }}">{{ $followUser->name }}</a>@if (!$loop->last), @endif
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                        @if ($followingList->isNotEmpty())
                            <a class="mb-2 inline-block text-sm hover:underline" href="{{ url('/observations?following=1') }}">{{ __('Observations of the observers you follow') }}</a>
                        @endif
                    @endif
                    <div class="mx-left">
                        {{ __('DeepskyLog observer since ') }}
                          {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $user->created_at)->format('Y') }}

                        <br />
                        {{-- First observation on / last observation on --}}
                        @php
                            [$firstDate, $firstObs] = $user->firstObservationDate();
                            [$lastDate, $lastObs] = $user->lastObservationDate();
                        @endphp

                        @if (!is_null($firstDate))
                            {{ __('First observation on ') }}
                            @if ($firstObs)
                                <a href="{{ $firstObs->detailUrl($user->slug ?: $user->username) }}">
                                    {{ $firstDate }}
                                </a>
                            @else
                                {{ $firstDate }}
                            @endif
                            <br />
                            {{ __('Most recent observation on ') }}
                            @if ($lastObs)
                                <a href="{{ $lastObs->detailUrl($user->slug ?: $user->username) }}">
                                    {{ $lastDate }}
                                </a>
                            @else
                                {{ $lastDate }}
                            @endif
                        @else
                            {{ __('No observations added!') }}
                        @endif

                        <br />
                        <br />

                        <table class="table-auto">
                            <!-- Default location -->
                            <tr>
                                <td>{!! __('Default observing site') !!}</td>
                                <td>
                                    @if ($user->stdlocation && $user->stdlocation != null)
                                        <a href="/location/{{ $user->slug }}/{{ $user->standardLocation->slug }}">
                                            {{ html_entity_decode($user->standardLocation->name) }}
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Default instrument -->
                            <tr>
                                <td>{{ __('Default instrument') }}</td>
                                <td>
                                    @if ($user->stdtelescope)
                                        <a
                                            href="/instrument/{{ $user->slug }}/{{ $user->standardInstrument->slug }}">
                                            {{ html_entity_decode($user->standardInstrument->fullName()) }}
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of locations -->
                            <tr>
                                <td>{!! __('Number of locations') !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/location">
                                    @endif
                                    {{ $user->locations_count }}
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of instruments -->
                            <tr>
                                <td>{!! __('Number of instruments') !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/instrument">
                                    @endif
                                    {{ $user->instruments_count }}
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of eyepieces -->
                            <tr>
                                <td>{!! __('Number of eyepieces') !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/eyepiece">
                                    @endif
                                    {{ $user->eyepieces_count }}
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of filters -->
                            <tr>
                                <td>{{ __('Number of filters') }}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/filter">
                                    @endif
                                    {{ $user->filters_count }}
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of lenses -->
                            <tr>
                                <td>{{ __('Number of lenses') }}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="/lens">
                                    @endif
                                    {{ $user->lenses_count }}
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of observing lists -->
                            <tr>
                                <td>{!! __('Total number of observing lists') !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="{{ route('observing-lists.index') }}">
                                    @endif
                                    {{ $user->getObservingLists()->count() }}
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of observing lists -->
                            <tr>
                                <td>{!! __('Public observing lists') !!}</td>
                                <td>
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        <a href="{{ route('observing-lists.index') }}">
                                    @endif
                                    {{ $user->getPublicObservingLists()->count() }}
                                    @if (Auth::user() && $user->id === Auth::user()->id)
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            <!-- Number of equipment sets -->
                            <tr>
                                <td>{!! __('Number of equipment sets') !!}</td>
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
                                <td>{{ __('Country of residence') }}</td>
                                <td>
                                    {{ $countryLabel }}
                                </td>
                            </tr>

                            <!-- Countries observed in -->
                            @if ($observedCountries['countries'] > 0)
                                <tr>
                                    <td>{{ __('Countries observed in') }}</td>
                                    <td>
                                        {{ $observedCountries['countries'] }}
                                        @if ($observedCountries['abroad_percentage'] !== null)
                                            ({{ __(':percentage% of the observations abroad', ['percentage' => number_format($observedCountries['abroad_percentage'], 1)]) }})
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
                <div class="col-span-1 row-span-2">
                    <div class="col-span-1">
                        @if ($user->about)
                            <x-card class="prose max-w-none dark:prose-invert">
                                <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                                    {{ __('About me') }}
                                </h2>
                                <div class="px-5 ">
                                    {!! \App\Support\Html::purify($user->about, true) !!}
                                </div>
                            </x-card>
                            <br />
                        @endif
                    </div>
                </div>
            </div>

            <br />

            <style>
                .inline-svg svg { width: 100%; height: 100%; display: block; }
                .inline-svg { width: 100%; height: 100%; }
                .two-line-truncate {
                    display: -webkit-box;
                    -webkit-box-orient: vertical;
                    -webkit-line-clamp: 2;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            </style>

            <livewire:observing-goals :observer="$user" />

            <x-card>
                <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                    {{ __('DeepskyLog Trophy Gallery') }}
                </h2>

                <div class="flex flex-wrap gap-x-6 gap-y-4 px-5">
                    @if ($user->achievements->count() > 0)
                        @foreach ($user->achievements as $achievement)
                            <div title="{{ $achievement->description }}" class="w-24 h-28 p-2 bg-white/5 rounded-lg flex flex-col items-center justify-start shadow">
                                @php
                                    $raw = $achievement->name;
                                    // Remove any 'DeepskyLog' prefix/mention for display and processing
                                    $raw = preg_replace('/\bdeepskylog\b[:\-\s]*/i', '', $raw);
                                    // Display name without the site prefix
                                    $displayName = trim(preg_replace('/\bdeepskylog\b[:\-\s]*/i', '', $achievement->name));
                                    $noDrawing = preg_replace('/\s+(Drawings|Drawing)$/i', '', $raw);
                                    $count = '';
                                    if (preg_match('/(\d+)/', $noDrawing, $m)) {
                                        $count = $m[1];
                                    }
                                    // If no numeric suffix exists, compute sketch-of-the-week/month counts from DB
                                    if (!$count && preg_match('/sketch.*week/i', $raw)) {
                                        $count = \App\Models\SketchOfTheWeek::published()->where('user_id', $user->id)->count();
                                    }
                                    if (!$count && preg_match('/sketch.*month/i', $raw)) {
                                        $count = \App\Models\SketchOfTheMonth::published()->where('user_id', $user->id)->count();
                                    }

                                    $isDrawing = preg_match('/\bDrawings?\b/i', $raw);

                                    // Catalog counts for Messier / Caldwell / Herschel (objectnames table)
                                    $achievementCatalog = null;
                                    if (preg_match('/messier/i', $raw)) {
                                        $achievementCatalog = 'M';
                                    } elseif (preg_match('/caldwell/i', $raw)) {
                                        $achievementCatalog = 'Caldwell';
                                    } elseif (preg_match('/herschel/i', $raw)) {
                                        // Distinguish Herschel 400 vs Herschel II
                                        $achievementCatalog = preg_match('/400\s*ii|h400-?ii|herschel\s*ii/i', $raw) ? 'H400-II' : 'H400';
                                    }
                                    if (!$count && $achievementCatalog !== null) {
                                        $count = $catalogCounts[$achievementCatalog][$isDrawing ? 'drawn' : 'observed'] ?? 0;
                                    }
                                    // Top-ten observer: show the observer's rank (1-10) when applicable
                                    if (!$count && preg_match('/top\s*ten/i', $raw)) {
                                        if ($rank >= 1 && $rank <= 10) {
                                            $count = $rank;
                                        }
                                    }
                                    $base = trim(preg_replace('/\d+$/', '', $noDrawing));
                                    $lbase = strtolower($base);
                                    $special = [
                                        'planet' => 'Planets', 'planets' => 'Planets',
                                        'moon' => 'Moon', 'moons' => 'Moon',
                                        'sun' => 'Sun', 'suns' => 'Sun',
                                        'double star' => 'Double Stars', 'double stars' => 'Double Stars',
                                        'object' => 'Objects', 'objects' => 'Objects',
                                        'comet' => 'Comets', 'comets' => 'Comets',
                                        'nebula' => 'Nebulae', 'nebulae' => 'Nebulae',
                                        'galaxy' => 'Galaxies', 'galaxies' => 'Galaxies',
                                        'open cluster' => 'Open Clusters', 'open clusters' => 'Open Clusters',
                                        'globular cluster' => 'Globular Clusters', 'globular clusters' => 'Globular Clusters',
                                        'planetary nebula' => 'Planetary Nebulae', 'planetary nebulae' => 'Planetary Nebulae',
                                    ];
                                    $label = $special[$lbase] ?? ucwords(str_replace(['_', '-'], ' ', $base));
                                    // overlay styling defaults; adjusted per-category for readability
                                    $overlayBg = 'bg-black/70';
                                    $overlayText = 'text-white';
                                    $overlaySizeClass = 'w-9 h-9 md:w-12 md:h-12';
                                    $overlayTextSize = 'text-3xl md:text-4xl';
                                    $overlayRing = 'ring-0';
                                    $overlayWrapperClass = 'absolute inset-0 flex items-center justify-center pointer-events-none z-20';
                                    if (str_contains($lbase, 'moon')) {
                                        $overlayBg = 'bg-blue-600';
                                        $overlayText = 'text-white';
                                        $overlaySizeClass = 'w-10 h-10 md:w-14 md:h-14';
                                        $overlayTextSize = 'text-3xl md:text-5xl';
                                    }
                                    if (str_contains($lbase, 'sun')) {
                                        $overlayBg = 'bg-yellow-300';
                                        $overlayText = 'text-gray-900';
                                    }
                                    // Improve contrast for double-star trophies: use opaque background + subtle ring
                                    if (str_contains($lbase, 'double')) {
                                        // place a smaller badge at the top-right so the icon remains readable
                                        $overlayBg = 'bg-white';
                                        $overlayText = 'text-gray-900';
                                        $overlaySizeClass = 'w-8 h-8 md:w-10 md:h-10';
                                        $overlayTextSize = 'text-2xl md:text-3xl';
                                        $overlayRing = 'ring-2 ring-black/20';
                                        $overlayWrapperClass = 'absolute top-0 right-0 pointer-events-none z-20 transform translate-x-2 -translate-y-2';
                                    }
                                @endphp

                                <div class="flex-1 flex items-center justify-center w-full">
                                    <div class="relative w-16 h-16 flex items-center justify-center">
                                        @php
                                            // Prefer dedicated SVGs for certain categories
                                            if (preg_match('/galax/i', $lbase)) {
                                                $svgPath = public_path($isDrawing ? 'images/achievements/galaxy_drawings.svg' : 'images/achievements/galaxy.svg');
                                            } elseif (str_contains($lbase, 'comet')) {
                                                $svgPath = public_path('images/achievements/comet.svg');
                                            } elseif (str_contains($lbase, 'globular')) {
                                                $svgPath = public_path($isDrawing ? 'images/achievements/globular_drawings.svg' : 'images/achievements/globular.svg');
                                            } elseif (str_contains($lbase, 'open cluster')) {
                                                $svgPath = public_path($isDrawing ? 'images/achievements/open_cluster_drawings.svg' : 'images/achievements/open_cluster.svg');
                                            } elseif (str_contains($lbase, 'planetary')) {
                                                $svgPath = public_path($isDrawing ? 'images/achievements/planetary_nebula_drawings.svg' : 'images/achievements/planetary_nebula.svg');
                                            } else {
                                                $svgPath = public_path(ltrim($achievement->image, '/'));
                                            }
                                            $svg = '';
                                            if (file_exists($svgPath)) {
                                                $svg = file_get_contents($svgPath);
                                                // remove embedded <text> elements to avoid duplicate labels
                                                $svg = preg_replace('/<text[\s\S]*?<\/text>/i', '', $svg);
                                                // remove width/height attributes so SVG scales to container
                                                $svg = preg_replace('/\s*(width|height)="[^"]+"/i', '', $svg);
                                            }
                                        @endphp
                                        <div class="w-full h-full inline-svg">{!! $svg !!}</div>
                                        @if ($count)
                                            <div class="{{ $overlayWrapperClass }}">
                                                <div class="{{ $overlayBg }} rounded-full {{ $overlaySizeClass }} flex items-center justify-center {{ $overlayRing }}">
                                                    <span class="{{ $overlayText }} {{ $overlayTextSize }} font-extrabold tracking-tight leading-none drop-shadow-md">{{ $count }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if ($count)
                                    <div class="mt-1 text-xs text-center text-gray-300 leading-tight two-line-truncate w-full">
                                        <div>{{ $label }}</div>
                                    </div>
                                @else
                                    <div class="mt-1 text-xs text-center text-gray-300 leading-tight two-line-truncate w-full">{{ $displayName }}</div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500">No trophies yet.</p>
                    @endif
                </div>
            </x-card>
            <!-- Personal tab -->
            <div class="py-4">

                <table class="mb-4">
                    <!-- Copyright notice -->
                    <tr>
                        <td class="pr-4">{!! __('Copyright notice') !!}</td>
                        <td>{!! $user->getCopyright() !!}</td>
                    </tr>
                </table>
                
                <div class="max-w-full overflow-x-auto">
                <table class="w-auto">
                    <thead>
                        <tr class="text-left">
                            <th class="pr-8"></th>
                            <th class="px-4">{{ __('Total') }}</th>
                            <th class="px-4">{{ __('Deepsky') }}</th>
                            <th class="px-4">{{ __('Comets') }}</th>
                            <th class="px-4">{{ __('Double stars') }}</th>
                            <th class="px-4">{{ __('Sun') }}</th>
                            <th class="px-4">{{ __('Moon') }}</th>
                            <th class="px-4">{{ __('Planets') }}</th>
                            <th class="px-4">{{ __('Asteroids') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="pr-8">{!! __('Number of observations') !!}</td>
                            <td class="px-4">{{ $allYourObservations ?? 0 }} / {{ $allTotalObservations ?? 0 }}</td>
                            <td class="px-4">{{ $deepskyYourObservations ?? 0 }} / {{ $deepskyTotalObservations ?? 0 }}</td>
                            <td class="px-4">{{ $cometObservations }}
                                / {{ $totalCometObservations }}
                            </td>
                            <td class="px-4">{{ $doubleYourObservations ?? 0 }} / {{ $doubleTotalObservations ?? 0 }}</td>
                            <td class="px-4">{{ $sunYourObservations ?? 0 }} / {{ $sunTotalObservations ?? 0 }}</td>
                            <td class="px-4">{{ $moonYourObservations ?? 0 }} / {{ $moonTotalObservations ?? 0 }}</td>
                            <td class="px-4">{{ $planetsYourObservations ?? 0 }} / {{ $planetsTotalObservations ?? 0 }}</td>
                            <td class="px-4">{{ $asteroidCounts['Observations'][0] }} / {{ $asteroidCounts['Observations'][1] }}</td>
                        </tr>

                        <tr>
                            <td class="pr-8">{!! __('Observations last year') !!}</td>
                            <td class="px-4">{{ $allYourObservationsLastYear ?? 0 }} / {{ $allTotalObservationsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $deepskyYourObservationsLastYear ?? 0 }} / {{ $deepskyTotalObservationsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $cometObservationsLastYear }}
                                / {{ $totalCometObservationsLastYear }}
                            </td>
                            <td class="px-4">{{ $doubleYourObservationsLastYear ?? 0 }} / {{ $doubleTotalObservationsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $sunYourObservationsLastYear ?? 0 }} / {{ $sunTotalObservationsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $moonYourObservationsLastYear ?? 0 }} / {{ $moonTotalObservationsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $planetsYourObservationsLastYear ?? 0 }} / {{ $planetsTotalObservationsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $asteroidCounts['ObservationsLastYear'][0] }} / {{ $asteroidCounts['ObservationsLastYear'][1] }}</td>
                        </tr>

                        <tr>
                            <td class="pr-8">{{ __('Number of drawings') }}</td>
                            <td class="px-4">{{ $allYourDrawings ?? 0 }} / {{ $allTotalDrawings ?? 0 }}</td>
                            <td class="px-4">{{ $deepskyYourDrawings ?? 0 }} / {{ $deepskyTotalDrawings ?? 0 }}</td>
                            <td class="px-4">{{ $cometDrawings }}
                                /
                                {{ $totalCometDrawings }}
                            </td>
                            <td class="px-4">{{ $doubleYourDrawings ?? 0 }} / {{ $doubleTotalDrawings ?? 0 }}</td>
                            <td class="px-4">{{ $sunYourDrawings ?? 0 }} / {{ $sunTotalDrawings ?? 0 }}</td>
                            <td class="px-4">{{ $moonYourDrawings ?? 0 }} / {{ $moonTotalDrawings ?? 0 }}</td>
                            <td class="px-4">{{ $planetsYourDrawings ?? 0 }} / {{ $planetsTotalDrawings ?? 0 }}</td>
                            <td class="px-4">{{ $asteroidCounts['Drawings'][0] }} / {{ $asteroidCounts['Drawings'][1] }}</td>
                        </tr>

                        <tr>
                            <td class="pr-8">{!! __('Drawings last year') !!}</td>
                            <td class="px-4">{{ $allYourDrawingsLastYear ?? 0 }} / {{ $allTotalDrawingsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $deepskyYourDrawingsLastYear ?? 0 }} / {{ $deepskyTotalDrawingsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $cometYourDrawingsLastYear ?? 0 }}
                                / {{ $cometTotalDrawingsLastYear ?? 0 }}
                            </td>
                            <td class="px-4">{{ $doubleYourDrawingsLastYear ?? 0 }} / {{ $doubleTotalDrawingsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $sunYourDrawingsLastYear ?? 0 }} / {{ $sunTotalDrawingsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $moonYourDrawingsLastYear ?? 0 }} / {{ $moonTotalDrawingsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $planetsYourDrawingsLastYear ?? 0 }} / {{ $planetsTotalDrawingsLastYear ?? 0 }}</td>
                            <td class="px-4">{{ $asteroidCounts['DrawingsLastYear'][0] }} / {{ $asteroidCounts['DrawingsLastYear'][1] }}</td>
                        </tr>

                        <tr>
                            <td class="pr-8">{{ __('Different objects') }}</td>
                            <td class="px-4">{{ $allYourUnique ?? 0 }} / {{ $allTotalUnique ?? 0 }}</td>
                            <td class="px-4">{{ $deepskyYourUnique ?? 0 }} / {{ $deepskyTotalUnique ?? 0 }}</td>
                            <td class="px-4">{{ $cometYourUnique ?? 0 }} /
                                {{ $cometTotalUnique ?? 0 }}</td>
                            <td class="px-4">{{ $doubleYourUnique ?? 0 }} / {{ $doubleTotalUnique ?? 0 }}</td>
                            <td class="px-4">{{ $sunYourUnique ?? 0 }} / {{ $sunTotalUnique ?? 0 }}</td>
                            <td class="px-4">{{ $moonYourUnique ?? 0 }} / {{ $moonTotalUnique ?? 0 }}</td>
                            <td class="px-4">{{ $planetsYourUnique ?? 0 }} / {{ $planetsTotalUnique ?? 0 }}</td>
                            <td class="px-4">{{ $asteroidCounts['Unique'][0] }} / {{ $asteroidCounts['Unique'][1] }}</td>
                        </tr>

                        <tr>
                            <td class="pr-8">{{ __('Messier objects') }}</td>
                            <td class="px-4"></td>
                            <td class="px-4">{{ $catalogCounts['M']['observed'] }} / 110</td>
                            <td class="px-4"></td>
                        </tr>

                        <tr>
                            <td class="pr-8">{!! __('Drawings of Messier objects') !!}</td>
                            <td class="px-4"></td>
                            <td class="px-4">{{ $catalogCounts['M']['drawn'] }} / 110</td>
                            <td class="px-4"></td>
                        </tr>

                        <tr>
                            <td class="pr-8">{{ __('Caldwell objects') }}</td>
                            <td class="px-4"></td>
                            <td class="px-4">{{ $catalogCounts['Caldwell']['observed'] }} / 109</td>
                            <td class="px-4"></td>
                        </tr>

                        <tr>
                            <td class="pr-8">{!! __('Drawings of Caldwell objects') !!}</td>
                            <td class="px-4"></td>
                            <td class="px-4">{{ $catalogCounts['Caldwell']['drawn'] }} / 109</td>
                            <td class="px-4"></td>
                        </tr>

                        <tr>
                            <td class="pr-8">{{ __('H400 objects') }}</td>
                            <td class="px-4"></td>
                            <td class="px-4">{{ $catalogCounts['H400']['observed'] }} / 400</td>
                            <td class="px-4"></td>
                        </tr>

                        <tr>
                            <td class="pr-8">{!! __('Drawings of H400 objects') !!}</td>
                            <td class="px-4"></td>
                            <td class="px-4">{{ $catalogCounts['H400']['drawn'] }} / 400</td>
                            <td class="px-4"></td>
                        </tr>

                        <tr>
                            <td class="pr-8">{{ __('H400-II objects') }}</td>
                            <td class="px-4"></td>
                            <td class="px-4">{{ $catalogCounts['H400-II']['observed'] }} / 400</td>
                            <td class="px-4"></td>
                        </tr>

                        <tr>
                            <td class="pr-8">{!! __('Drawings of H400-II objects') !!}</td>
                            <td class="px-4"></td>
                            <td class="px-4">{{ $catalogCounts['H400-II']['drawn'] }} / 400</td>
                            <td class="px-4"></td>
                        </tr>

                        <tr>
                            <td class="pr-8">{{ __('Rank') }}</td>
                            <td class="px-4">{{ $rank ?: '-' }} / {{ $rankedObserverCount }} </td>
                            @foreach (['deepsky', 'comet', 'double', 'sun', 'moon', 'planets'] as $category)
                                <td class="px-4">{{ ($categoryRanks[$category]['rank'] ?? 0) ?: '-' }} / {{ $categoryRanks[$category]['observers'] ?? 0 }}</td>
                            @endforeach
                        </tr>

                        @if ($countryRanks)
                            <tr>
                                <td class="pr-8">{{ __('Rank in :country', ['country' => $countryLabel]) }}</td>
                                <td class="px-4">
                                    <a href="{{ route('observers.index', ['country' => $user->country]) }}" class="hover:underline">
                                        {{ $countryRanks['all']['rank'] ?: '-' }} / {{ $countryRanks['all']['observers'] }}
                                    </a>
                                </td>
                                @foreach (['deepsky', 'comet', 'double', 'sun', 'moon', 'planets'] as $category)
                                    <td class="px-4">{{ ($countryRanks[$category]['rank'] ?? 0) ?: '-' }} / {{ $countryRanks[$category]['observers'] ?? 0 }}</td>
                                @endforeach
                            </tr>
                        @endif
                    </tbody>
                </table>
                </div>

                <br />
                  <x-button gray icon="eye" class="mb-2" href="{{ url('/observations/' . $user->slug) }}">
                    {{ __('All observations of ') . $user->name }}
                </x-button>

                <x-button gray icon="pencil" class="mb-2" href="/drawings/{{ $user->slug }}">

                    {{ __('All drawings of ') . $user->name }}
                </x-button>

                <x-button gray icon="newspaper" class="mb-2" href="{{ route('observer.blog', ['observer' => $user->slug]) }}">
                    {{ __('Blog of :name', ['name' => $user->name]) }}
                </x-button>

                  @if (\App\Models\ObservationSession::where('observerid', $user->username)->where('active', 1)->exists())
                    <x-button gray icon="calendar" class="mb-2"
                        href="{{ route('session.user', [$user->slug ?? $user->username]) }}">
                        {{ __('All sessions of ') . $user->name }}
                    </x-button>
                @endif

                @if (Auth::user() && $user->id != Auth::user()->id)
                    <x-button gray icon="envelope-open" class="mb-2"
                        href="{{ route('messages.create', ['to' => $user->username]) }}">
                        @auth
                            {{ __('Send message to ') . $user->name }}
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

            <br />
            <!-- The countries chart -->
            <div>
                {!! $countriesChart->container() !!}
            </div>

            @if (!empty($sessions) && $sessions->isNotEmpty())
                <div class="mt-6">
                    <h2 class="mb-3 ml-3 mt-3 text-xl font-semibold leading-tight">{{ __('Recent sessions') }}</h2>
                    <div class="mt-2">
                        <x-card>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-2">
                                @foreach ($sessions as $session)
                                    @php
                                        $sessionUser = optional($session->observer)->slug ?? $session->observerid;
                                        $sessionParam = $session->slug ?? ($session->id ?? null);
                                        $sessionUrl = $sessionParam
                                            ? route('session.show', [
                                                'user' => $sessionUser,
                                                'session' => $sessionParam,
                                            ])
                                            : null;
                                    @endphp
                                    <article class="bg-gray-700 p-4 rounded">
                                        @if (!empty($session->preview))
                                            <div class="mb-3">
                                                <a href="{{ $sessionUrl ?? '#' }}">
                                                    <img src="{{ $session->preview }}"
                                                        alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}"
                                                        class="w-full h-28 object-cover rounded" />
                                                </a>
                                            </div>
                                        @endif

                                        <h3 class="text-lg font-bold text-white mb-2">
                                            <a href="{{ $sessionUrl ?? '#' }}"
                                                class="hover:underline">{{ html_entity_decode($session->name ?? __('Session :id', ['id' => $session->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                        </h3>

                                        <div class="text-sm text-gray-400 mb-2">
                                            <span>{{ $session->begindate ? \Carbon\Carbon::parse($session->begindate)->translatedFormat('j M Y') : __('Unknown') }}</span>
                                            <span class="mx-2">&ndash;</span>
                                            <span>{{ $session->enddate ? \Carbon\Carbon::parse($session->enddate)->translatedFormat('j M Y') : __('Unknown') }}</span>
                                        </div>

                                        <div class="text-sm text-gray-400 mb-2">
                                            <span class="mr-2 font-medium text-gray-200">{{ __('Location') }}:</span>
                                            <span
                                                class="text-gray-400">{{ $session->location_name ?? __('Unknown') }}</span>
                                        </div>

                                        @if (isset($session->observation_count))
                                            <div class="text-sm text-gray-300 mb-2">{{ __('Observations') }}: <strong
                                                    class="text-white">{{ $session->observation_count }}</strong>
                                            </div>
                                        @endif

                                        <p class="text-sm text-gray-300 mb-3">
                                            {{ $session->preview_text ?? \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')), 180) }}
                                        </p>
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="text-gray-400">{{ __('Observers') }}:
                                                {{ $session->otherObserversCount() ?? 1 }}</div>
                                            <a href="{{ $sessionUrl ?? '#' }}"
                                                class="text-blue-500 hover:underline">{{ __('Read more') }}</a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </x-card>
                    </div>
                </div>
            @endif

            @php
                // A sketch of the week chosen for a later Sunday is not shown yet.
                $publishedSketchesOfTheWeek = $user->sketchOfTheWeek->filter->isPublished();
            @endphp
            @if ($publishedSketchesOfTheWeek->isNotEmpty())
                <div class="mt-6">
                    <x-card>
                        <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                            {{ __('DeepskyLog sketches of the week') }}
                        </h2>
                        <div class="px-5 flex flex-wrap">
                            @php
                                // The observations of the cards and their observers, likes, ... in grouped queries.
                                (new \Illuminate\Database\Eloquent\Collection($publishedSketchesOfTheWeek->all()))->loadMissing(['observation', 'user']);
                                \App\Services\Observations\ObservationCardData::instance()->preload($publishedSketchesOfTheWeek->pluck('observation'));
                            @endphp
                            @foreach ($publishedSketchesOfTheWeek as $sketch)
                                <div class="max-w-xl mt-3 pr-3">
                                    <x-sketch :sketch="$sketch" />
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </div>
            @endif

            @php
                // A sketch of the month chosen for a later month is not shown yet.
                $publishedSketchesOfTheMonth = $user->sketchOfTheMonth->filter->isPublished();
            @endphp
            @if ($publishedSketchesOfTheMonth->isNotEmpty())
                <div class="mt-6">
                    <x-card>
                        <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                            {{ __('DeepskyLog sketches of the month') }}
                        </h2>
                        <div class="px-5 flex flex-wrap">
                            @php
                                // The observations of the cards and their observers, likes, ... in grouped queries.
                                (new \Illuminate\Database\Eloquent\Collection($publishedSketchesOfTheMonth->all()))->loadMissing(['observation', 'user']);
                                \App\Services\Observations\ObservationCardData::instance()->preload($publishedSketchesOfTheMonth->pluck('observation'));
                            @endphp
                            @foreach ($publishedSketchesOfTheMonth as $sketch)
                                <div class="max-w-xl mt-3 pr-3">
                                    {{-- Show the correct drawing --}}
                                    <x-sketch :sketch="$sketch" />
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </div>
            @endif

            @if (!empty($hasPopularObservations) && $hasPopularObservations)
                <div class="mt-6">
                    <x-card>
                        <h2 class="mb-2 mt-2 text-xl px-5 font-bold">
                            {{ __('Most liked observations') }}
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

@endsection
