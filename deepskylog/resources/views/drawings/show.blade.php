<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl font-semibold leading-tight">
                    @if ($user == "")
                        {{ __("Sketches") }}
                    @else
                        {{ __("Sketches of " . $user->name) }}
                    @endif
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($user == "")
                        <a href="{{ route('feed.drawings') }}"
                           title="{{ __('Subscribe to RSS feed') }}"
                           class="inline-flex items-center px-3 py-2 rounded bg-orange-600 hover:bg-orange-500 text-white text-xs font-semibold">
                            <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19.01 7.38 20 6.18 20C4.98 20 4 19.01 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1z"/>
                            </svg>
                            RSS
                        </a>
                    @endif
                    @php
                        $activeObsType = $obsType ?? 'all';
                        $baseDrawingsUrl = $user == "" ? url('/drawings') : url('/drawings/' . $user->slug);
                    @endphp
                    @foreach (["all" => __("All"), "deepsky" => __("Deep-sky"), "comet" => __("Comets"), "solarsystem" => __("Solar system")] as $typeKey => $typeLabel)
                        @php
                            $params = $typeKey !== 'all' ? ['obs_type' => $typeKey] : [];
                            $typeUrl = $baseDrawingsUrl . ($params ? '?' . http_build_query($params) : '');
                            $isActive = $activeObsType === $typeKey;
                        @endphp
                        <a href="{{ $typeUrl }}"
                           class="rounded px-3 py-1 text-sm font-medium {{ $isActive ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200 hover:bg-gray-600' }}"
                        >{{ $typeLabel }}</a>
                    @endforeach
                </div>
            </div>
            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @if ($sketches->isEmpty())
                            <div class="text-center">
                                {{ __("No sketches yet...") }}
                            </div>
                        @endif

                        @foreach ($sketches as $sketch)
                            <div class="mt-3 max-w-xl pr-3">
                                @php
                                    // Use preloaded user data to avoid N+1 query
                                    $sketchUser = $sketchUsers[$sketch->observerid] ?? null;
                                    $observer_name = $sketchUser ? $sketchUser->name : $sketch->observerid;
                                    $observation_date = substr($sketch->date, 0, 4) . "-" . substr($sketch->date, 4, 2) . "-" . substr($sketch->date, 6, 2);

                                    // Resolve the correct drawing path so the component doesn't fall back
                                    // to /images/drawings/{new-table-id}.jpg (which never matches any file).
                                    // Priority: drawing_filename in DB → legacy path by legacy_id → leave null.
                                    if (!empty($sketch->drawing_filename)) {
                                        $sketchDrawingId = $sketch->drawing_filename;
                                    } elseif ($sketch->legacy_source === 'deepsky' && $sketch->legacy_id) {
                                        $sketchDrawingId = 'drawings/' . $sketch->legacy_id . '.jpg';
                                    } elseif ($sketch->legacy_source === 'comet' && $sketch->legacy_id) {
                                        $sketchDrawingId = 'cometdrawings/' . $sketch->legacy_id . '.jpg';
                                    } else {
                                        $sketchDrawingId = null;
                                    }
                                @endphp

                                {{-- Pass unified_observation so the component reads drawing_filename directly.
                                     drawing_id provides the legacy fallback path when drawing_filename is null. --}}
                                <x-sketch-deepsky
                                    :observation_id="$sketch->id"
                                    :unified_observation="$sketch"
                                    :drawing_id="$sketchDrawingId"
                                    :observer_name="$observer_name"
                                    :observer_username="$sketch->observerid"
                                    :observation_date="$observation_date"
                                />
                            </div>
                        @endforeach
                    </div>
                    {{ $sketches->links() }}
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
