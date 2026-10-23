<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl font-semibold leading-tight">
                    @php
                        $mode = $mode ?? 'deepsky';
                    @endphp

                    @php
                        // Treat any URL under /observations/drawings/* as a drawings listing
                        $isDrawingsPath = request()->is('observations/drawings*');
                    @endphp

                    @if (!empty($objectFilter))
                        {{-- Object-scoped listing (object-only or observer+object).
                             Only treat this as a drawings listing when the URL path is under /observations/drawings/*.
                             Do NOT rely on the controller-provided $drawingsOnly flag here, because some callers
                             may set that flag for other reasons. The URL should determine the visible title.
                        --}}
                        @if ($isDrawingsPath)
                            {{ __('Drawings of :object', ['object' => $objectName ?? $user->name]) }}
                        @else
                            {{ __('Observations of :object', ['object' => $objectName ?? $user->name]) }}
                        @endif
                    @elseif (!empty($drawingsOnly))
                        {{-- Drawings-only listing (per-observer or global) --}}
                        @if ($user == '')
                            {{ __('Drawings') }}
                        @else
                            {{ __('Drawings of ' . $user->name) }}
                        @endif
                    @else
                        @if ($isDrawingsPath)
                            @if ($user == '')
                                {{ __('Drawings') }}
                            @else
                                {{ __('Drawings of ' . $user->name) }}
                            @endif
                        @else
                            @if ($user == '' && !empty($following))
                                {{ __('Observations of the observers you follow') }}
                            @elseif ($user == '')
                                {{ __('Observations') }}
                            @else
                                {{ __('Observations of ' . $user->name) }}
                            @endif
                        @endif
                    @endif
                </h2>
                @php
                    $mode = $mode ?? 'deepsky';
                    $deepskyUrl = $user == '' ? url('/observations') : url('/observations/' . $user->slug);
                    $cometUrl = $user == '' ? url('/cometobservations') : url('/cometobservations/' . $user->slug);

                    // Images-only mode for drawings listings (triggered via ?images=1)
                    $isImagesOnly = $isDrawingsPath && (request()->query('images') == '1' || request()->has('images'));
                    // URL helpers to toggle images-only, keeping only the listing
                    // parameters (see App\Helpers\ListingPagination) and page 1
                    $listingQuery = \App\Helpers\ListingPagination::query();
                    $imagesOnUrl = url()->current() . '?' . http_build_query(array_merge($listingQuery, ['images' => 1]));
                    // Back to standard view: current URL without the images query parameter
                    $queryExceptImages = \App\Helpers\ListingPagination::query(['images']);
                    $imagesOffUrl =
                        url()->current() .
                        (count($queryExceptImages) ? '?' . http_build_query($queryExceptImages) : '');
                @endphp

                <div class="flex flex-wrap gap-2">
                    @if ($user == '' && empty($objectFilter) && empty($drawingsOnly) && !$isDrawingsPath)
                        {{-- Global observations page: type filter tabs (All / Deep-sky / Comets / Solar system) --}}
                        @php
                            $activeObsType = $obsType ?? 'all';
                        @endphp
                        @foreach (["all" => __("All"), "deepsky" => __("Deep-sky"), "comet" => __("Comets"), "solarsystem" => __("Solar system")] as $typeKey => $typeLabel)
                            @php
                                $params = array_merge(\App\Helpers\ListingPagination::query(['obs_type']), $typeKey !== 'all' ? ['obs_type' => $typeKey] : []);
                                $typeUrl = url('/observations') . ($params ? '?' . http_build_query($params) : '');
                                $isActive = $activeObsType === $typeKey;
                            @endphp
                            <a href="{{ $typeUrl }}"
                               class="rounded px-3 py-1 text-sm font-medium {{ $isActive ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600' }}"
                            >{{ $typeLabel }}</a>
                        @endforeach
                        @auth
                            {{-- Only the observers the user follows, keeping the type filter --}}
                            @php
                                $followingParams = \App\Helpers\ListingPagination::query(['following']);
                                if (empty($following)) {
                                    $followingParams['following'] = 1;
                                }
                                $followingUrl = url('/observations') . ($followingParams ? '?' . http_build_query($followingParams) : '');
                            @endphp
                            <a href="{{ $followingUrl }}"
                               class="rounded px-3 py-1 text-sm font-medium {{ !empty($following) ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600' }}"
                            >{{ __('Observers I follow') }}</a>
                        @endauth
                    @else
                        {{-- Non-global pages: keep the deepsky/comet toggle buttons --}}
                        @if ($mode !== 'deepsky' && !($mode === 'comet' && !empty($objectFilter)))
                            <x-button gray icon="eye" class="mb-2" href="{{ $deepskyUrl }}">
                                {{ __('Show deepsky observations') }}
                            </x-button>
                        @endif

                        @if (
                            $mode !== 'comet' &&
                                empty($objectFilter) &&
                                empty($drawingsOnly) &&
                                $user == ''
                        )
                            <x-button gray icon="sparkles" class="mb-2" href="{{ $cometUrl }}">
                                {{ __('Show comet observations') }}
                            </x-button>
                        @endif
                    @endif
                </div>
                @php
                    $currentSort = request()->query('sort', 'observed');
                    $sortObservedUrl = url()->current() . '?' . http_build_query(array_merge($listingQuery, ['sort' => 'observed']));
                    $sortIdUrl = url()->current() . '?' . http_build_query(array_merge($listingQuery, ['sort' => 'id']));
                @endphp
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $sortObservedUrl }}"
                        class="inline-flex items-center px-3 py-2 rounded {{ $currentSort === 'observed' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-white hover:bg-gray-600' }}">
                        {{ __('Sort: Observed') }}
                    </a>
                    <a href="{{ $sortIdUrl }}"
                        class="inline-flex items-center px-3 py-2 rounded {{ $currentSort === 'id' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-white hover:bg-gray-600' }}">
                        {{ __('Sort: ID') }}
                    </a>
                </div>
                {{-- Images-only toggle (only for drawings listing paths) --}}
                @if ($isDrawingsPath)
                    <div>
                        @if (!$isImagesOnly)
                            <x-button gray icon="eye" class="mb-2" href="{{ $imagesOnUrl }}">
                                {{ __('Show images only') }}
                            </x-button>
                        @else
                            <x-button gray icon="arrow-left" class="mb-2" href="{{ $imagesOffUrl }}">
                                {{ __('Back to standard view') }}
                            </x-button>
                        @endif
                    </div>
                @endif
                @if ($user == '' && empty($objectFilter) && empty($drawingsOnly) && !$isDrawingsPath)
                    {{-- The personal feed of the followed observers holds a secret token instead of a login --}}
                    <a href="{{ !empty($following) ? route('feed.following', ['token' => auth()->user()->followFeedToken()]) : route('feed.observations') }}"
                       title="{{ __('Subscribe to RSS feed') }}"
                       class="inline-flex items-center px-3 py-2 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-semibold">
                        <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19.01 7.38 20 6.18 20C4.98 20 4 19.01 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1z"/>
                        </svg>
                        RSS
                    </a>
                @endif
            </div>
            <div class="mt-2">
                @if (!empty($isImagesOnly) && $isImagesOnly)
                    <x-card>
                        <div class="flex flex-wrap px-5">
                            @if ($deepsky->isEmpty())
                                <div class="text-center text-gray-300">{{ __('No sketches yet...') }}</div>
                            @endif

                            @foreach ($deepsky as $observation)
                                <div class="mt-3 max-w-xl pr-3">
                                    @php
                                        // observerid is an accessor of Observation for observer_username
                                        $observer_key = $observation->observerid ?? $observation->observer_username ?? null;
                                        $observer_name = \App\Services\Observations\ObservationCardData::instance()->user($observer_key)?->name ?? $observer_key ?? __('Unknown');

                                        if (!empty($observation->date)) {
                                            $observation_date =
                                                substr((string) $observation->date, 0, 4) .
                                                '-' .
                                                substr((string) $observation->date, 4, 2) .
                                                '-' .
                                                substr((string) $observation->date, 6, 2);
                                        } elseif (!empty($observation->observed_on)) {
                                            $observation_date = (string) $observation->observed_on;
                                        } else {
                                            $observation_date = null;
                                        }
                                    @endphp

                                    <x-sketch-deepsky :observation_id="$observation->id" :observer_name="$observer_name" :observer_username="$observer_key"
                                        :observation="$observation" :unified_observation="$observation instanceof \App\Models\Observation ? $observation : ($observation->unified ?? null)" :drawing_id="$observation->drawing_filename ?? $observation->drawing_filename ?? null"
                                        :observation_date="$observation_date" />
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $deepsky->links() }}</div>
                    </x-card>
                @elseif (!empty($drawingsOnly) && $drawingsOnly)
                    <x-card>
                        @if ($deepsky->isEmpty())
                            <div class="text-center text-gray-300">{{ __('No drawings yet...') }}</div>
                        @else
                            <h3 class="mb-3 text-lg font-semibold text-gray-200">{{ __('All drawings') }}</h3>
                            <div class="grid grid-cols-1 gap-4 px-5">
                                @foreach ($deepsky as $observation)
                                    <x-observation-deepsky
                                        :observation="$observation"
                                    />
                                @endforeach
                            </div>
                            <div class="mt-4">{{ $deepsky->links() }}</div>
                        @endif
                    </x-card>
                @elseif ($mode === 'comet')
                    <x-card>
                        @if ($comet->isEmpty())
                            <div class="text-center text-gray-300">{{ __('No comet observations yet...') }}</div>
                        @else
                            <h3 class="mb-3 text-lg font-semibold text-gray-200">{{ __('All comet observations') }}
                            </h3>
                            <div class="grid grid-cols-1 gap-4 px-5">
                                @foreach ($comet as $observation)
                                    <x-observation-comet 
                                        :observation="$observation" 
                                    />
                                @endforeach
                            </div>
                            <div class="mt-4">{{ $comet->links() }}</div>
                        @endif
                    </x-card>
                @else
                    @if (!empty($following))
                        <div class="mb-3 flex flex-wrap items-center gap-2 text-sm text-gray-400">
                            <span>{{ __('The RSS feed of the observers you follow has a private address: anyone who knows it can read the feed.') }}</span>
                            <form method="POST" action="{{ route('feed.following.regenerate') }}">
                                @csrf
                                <button type="submit" class="underline hover:text-gray-200">{{ __('Give the feed a new address') }}</button>
                            </form>
                        </div>
                    @endif
                    <x-card>
                        @if ($deepsky->isEmpty() && !empty($following) && empty($followingCount))
                            <div class="text-center text-gray-300">{{ __('You do not follow any observers yet. Use the Follow button on the page of an observer.') }}</div>
                        @elseif ($deepsky->isEmpty())
                            <div class="text-center text-gray-300">{{ __('No observations yet.') }}</div>
                        @else
                            <h3 class="mb-3 text-lg font-semibold text-gray-200">{{ __('All observations') }}
                            </h3>
                            <div class="grid grid-cols-1 gap-4 px-5">
                                @foreach ($deepsky as $observation)
                                    <x-observation-deepsky 
                                        :observation="$observation" 
                                    />
                                @endforeach
                            </div>
                            <div class="mt-4">{{ $deepsky->links() }}</div>
                        @endif
                    </x-card>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
