<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
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
                            @if ($user == '')
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
                    // URL helpers to toggle images-only while preserving other query params
                    $imagesOnUrl = request()->fullUrlWithQuery(['images' => 1]);
                    // Back to standard view: current URL without the images query parameter
                    $queryExceptImages = request()->except('images');
                    $imagesOffUrl =
                        url()->current() .
                        (count($queryExceptImages) ? '?' . http_build_query($queryExceptImages) : '');
                @endphp

                <div class="flex space-x-2">
                    {{-- Hide the deepsky toggle when viewing a comet object page (object-scoped comet listing) --}}
                    @if ($mode !== 'deepsky' && !($mode === 'comet' && !empty($objectFilter)))
                        <x-button gray icon="eye" class="mb-2" href="{{ $deepskyUrl }}">
                            {{ __('Show deepsky observations') }}
                        </x-button>
                    @endif

                    {{-- Only show the comet toggle when listing is global or per-observer (not object-scoped) --}}
                    @if (
                        $mode !== 'comet' &&
                            empty($objectFilter) &&
                            empty($drawingsOnly) &&
                            ($user == '' || ($user->username ?? null) !== null))
                        <x-button gray icon="sparkles" class="mb-2" href="{{ $cometUrl }}">
                            {{ __('Show comet observations') }}
                        </x-button>
                    @endif
                </div>
                {{-- Images-only toggle (only for drawings listing paths) --}}
                @if ($isDrawingsPath)
                    <div class="ml-2">
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
            </div>
            <div class="mt-2">
                @php
                    // Create a single translator instance for the whole page when the
                    // authenticated user has translations enabled. This avoids
                    // instantiating a translator for every observation component which
                    // can cause large memory allocations.
                    $tr = null;
                    if (auth()->check() && auth()->user()->translate) {
                        $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(auth()->user()->language);
                    }
                @endphp
                @if (!empty($isImagesOnly) && $isImagesOnly)
                    <x-card>
                        <div class="flex flex-wrap px-5">
                            @if ($deepsky->isEmpty())
                                <div class="text-center text-gray-300">{{ __('No sketches yet...') }}</div>
                            @endif

                            @foreach ($deepsky as $observation)
                                <div class="mt-3 max-w-xl pr-3">
                                    @php
                                        $observer_name =
                                            ($preloaded_users ?? collect())->get($observation->observerid)?->name ?? 
                                            \App\Models\User::where('username', $observation->observerid)->first()
                                                ?->name ?? $observation->observerid;
                                        $observation_date =
                                            substr($observation->date, 0, 4) .
                                            '-' .
                                            substr($observation->date, 4, 2) .
                                            '-' .
                                            substr($observation->date, 6, 2);
                                    @endphp

                                    <x-sketch-deepsky :observation_id="$observation->id" :observer_name="$observer_name" :observer_username="$observation->observerid"
                                        :observation_date="$observation_date" />
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $deepsky->links() }}</div>
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
                                    @php
                                        // Pass preloaded data to comet observation component
                                        $preloaded_user = ($preloaded_users ?? collect())->get($observation->observerid);
                                        $preloaded_comet = ($preloaded_comets ?? collect())->get($observation->objectid);
                                        $preloaded_location = ($preloaded_locations ?? collect())->get($observation->locationid);
                                        $preloaded_instrument = ($preloaded_instruments ?? collect())->get($observation->instrumentid);
                                    @endphp
                                    <x-observation-comet 
                                        :observation="$observation" 
                                        :preloaded_user="$preloaded_user"
                                        :preloaded_comet="$preloaded_comet"
                                        :preloaded_location="$preloaded_location"
                                        :preloaded_instrument="$preloaded_instrument"
                                    />
                                @endforeach
                            </div>
                            <div class="mt-4">{{ $comet->links() }}</div>
                        @endif
                    </x-card>
                @else
                    <x-card>
                        @if ($deepsky->isEmpty())
                            <div class="text-center text-gray-300">{{ __('No deepsky observations yet...') }}</div>
                        @else
                            <h3 class="mb-3 text-lg font-semibold text-gray-200">{{ __('All deepsky observations') }}
                            </h3>
                            <div class="grid grid-cols-1 gap-4 px-5">
                                @foreach ($deepsky as $observation)
                                    @php
                                        // Pass preloaded data to deepsky observation component
                                        $preloaded_user = ($preloaded_users ?? collect())->get($observation->observerid);
                                        $preloaded_object = ($preloaded_objects ?? collect())->get($observation->objectname);
                                        $preloaded_location = ($preloaded_locations ?? collect())->get($observation->locationid);
                                        $preloaded_instrument = ($preloaded_instruments ?? collect())->get($observation->instrumentid);
                                        $preloaded_eyepiece = ($preloaded_eyepieces ?? collect())->get($observation->eyepieceid);
                                        $preloaded_filter = ($preloaded_filters ?? collect())->get($observation->filterid);
                                    @endphp
                                    <x-observation-deepsky 
                                        :observation="$observation" 
                                        :translator="$tr"
                                        :preloaded_user="$preloaded_user"
                                        :preloaded_object="$preloaded_object"
                                        :preloaded_location="$preloaded_location"
                                        :preloaded_instrument="$preloaded_instrument"
                                        :preloaded_eyepiece="$preloaded_eyepiece"
                                        :preloaded_filter="$preloaded_filter"
                                        :preloaded_constellations="$preloaded_constellations ?? collect()"
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
