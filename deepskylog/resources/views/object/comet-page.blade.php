<x-app-layout>
    <div>
        @php
            $canonicalSlug = $session->slug ?? null;
        @endphp

        <div class="mx-auto max-w-screen-xl xl:max-w-full bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <header class="mb-6">
                @php
                    $objSlugTop =
                        $canonicalSlug ?? ($session->slug ?? \Illuminate\Support\Str::slug($session->name ?? ''));
                @endphp
                <h1 class="text-3xl font-extrabold">
                    <a href="{{ route('object.show', ['slug' => $objSlugTop]) }}"
                        class="hover:underline">{{ html_entity_decode($session->name ?? __('Comet'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                </h1>
            </header>

            @include('object._summary')

            <div class="flex flex-col lg:flex-row gap-4 w-full items-stretch">
                <div class="flex-1" data-dsl-main-content>
                    <div class="mt-3 text-gray-100">
                        <h2 class="text-xl font-semibold text-white">{{ __('Object details') }}</h2>
                        <table class="table-auto w-full text-sm text-gray-100">
                            @php
                                // Do not pass cached `ephemerides` into the comet Livewire
                                // components so the server-side `object-ephemerides`
                                // recomputes authoritative coordinates on every page
                                // load. This prevents stale coordinates from being
                                // embedded in the initial response.
                                $cdInitial = [
                                    'magnitudes' => $comet_magnitudes ?? [],
                                    'sourceTypeRaw' => $session->source_type_raw ?? null,
                                ];
                            @endphp
                            @livewire('comet-details', ['objectId' => (string) ($session->id ?? ''), 'initial' => $cdInitial])
                        </table>

                        {{-- Mount a hidden ObjectEphemerides Livewire component so the aside date
                             selector triggers server-side ephemeris recalculation for comets.
                             Suppress rendering to avoid duplicate rows; it will emit
                             `objectEphemeridesUpdated` for `comet-details`. --}}
                        <div style="display:none;">
                            {{-- Do not pass cached initial payload to `object-ephemerides` so
                                     it performs a fresh server-side recompute on mount and
                                     emits `objectEphemeridesUpdated` for `comet-details`. --}}
                            @livewire('object-ephemerides', [
                                'objectId' => (string) ($session->id ?? ''),
                                'objectName' => $session->name ?? null,
                                'sourceTypeRaw' => 'comet',
                                // Suppress ephemerides here so the aside's server-side
                                // recompute is the single authoritative emitter of
                                // `objectEphemeridesUpdated`. This avoids race
                                // conditions where two components emit different
                                // ephemerides and causes off-by-one date results.
                                'suppressTopRaDec' => true,
                                'suppressEphemerides' => true,
                            ])
                        </div>

                        {{-- Sketches that were DeepskyLog sketch(s) of the week for this comet --}}
                        @php
                            $objectSketches = collect();
                            try {
                                $objName = $session->name ?? '';
                                if (!empty($objName)) {
                                    $obsIds = \Illuminate\Support\Facades\DB::connection('mysqlOld')
                                        ->table('observations')
                                        ->where('objectname', $objName)
                                        ->pluck('id')
                                        ->toArray();

                                    if (!empty($obsIds)) {
                                        $objectSketches = \App\Models\SketchOfTheWeek::whereIn('observation_id', $obsIds)
                                            ->orderByDesc('date')
                                            ->get();
                                    } else {
                                        try {
                                            $simple = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $objName);
                                            $tokens = array_filter(array_map('trim', preg_split('/\s+/', $simple)));
                                            if (!empty($tokens)) {
                                                $q = \Illuminate\Support\Facades\DB::connection('mysqlOld')->table('observations');
                                                $first = array_shift($tokens);
                                                $q->where('objectname', 'like', '%' . $first . '%');
                                                foreach ($tokens as $t) {
                                                    $q->orWhere('objectname', 'like', '%' . $t . '%');
                                                }
                                                $altIds = $q->pluck('id')->toArray();
                                                if (!empty($altIds)) {
                                                    $objectSketches = \App\Models\SketchOfTheWeek::whereIn('observation_id', $altIds)
                                                        ->orderByDesc('date')
                                                        ->get();
                                                }
                                            }
                                        } catch (\Throwable $_) {
                                            // ignore fallback failures
                                        }
                                    }

                                    // Also attempt to find sketches that reference cometobservations (negative observation_id)
                                    try {
                                        $tokensForLike = [];
                                        if (!empty($tokens)) {
                                            foreach ($tokens as $t) {
                                                $tokensForLike[] = '%' . $t . '%';
                                            }
                                        } else {
                                            $tokensForLike[] = '%' . $objName . '%';
                                        }
                                        $likes = implode(' OR ', array_fill(0, count($tokensForLike), 'coo.name LIKE ?'));
                                        $sql = 'SELECT s.* FROM sketch_of_the_week s JOIN deepskylog.cometobservations co ON co.id = -s.observation_id JOIN deepskylog.cometobjects coo ON coo.id = co.objectid WHERE ' . $likes . ' ORDER BY s.date DESC';
                                        $cometRows = \Illuminate\Support\Facades\DB::select($sql, $tokensForLike);
                                        if (!empty($cometRows)) {
                                            foreach ($cometRows as $r) {
                                                $objectSketches->push(\App\Models\SketchOfTheWeek::find($r->id));
                                            }
                                        }
                                    } catch (\Throwable $_) {
                                        // ignore
                                    }
                                }
                            } catch (\Throwable $_) {
                                $objectSketches = collect();
                            }
                        @endphp

                        @if ($objectSketches->isNotEmpty())
                            <div class="mt-6">
                                <h2 class="text-xl font-semibold text-white">{{ __('Sketch of the Week') }}</h2>
                                <div class="mt-2">
                                    <x-card>
                                        <div class="flex flex-wrap px-5">
                                            @foreach ($objectSketches as $sketch)
                                                <x-sketch :sketch="$sketch" />
                                            @endforeach
                                        </div>
                                    </x-card>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @include('object._quick_links')
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Comet page is Livewire-driven; Livewire handles charts/ephemerides rendering. --}}
