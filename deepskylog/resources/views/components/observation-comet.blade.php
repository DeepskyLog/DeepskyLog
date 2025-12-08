{{-- Avoid inline `use` in Blade; use fully-qualified class names within PHP blocks --}}
@props(['observation'])
<div class="justify-left mt-5 flex">
    @php
        $date = $observation->date;
        $observation_date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        $user = \App\Models\User::where('username', html_entity_decode($observation->observerid))->first();
        // Fallbacks when user cannot be found
        if (!$user) {
            $user = (object) [
                'profile_photo_url' => asset('images/placeholder-avatar.png'),
                'name' => __('Unknown observer'),
                'username' => '',
                'slug' => '',
            ];
        }
        // Stichoza\GoogleTranslate\GoogleTranslate used conditionally
        $tr = null;
        if (auth()->check() && auth()->user()->translate) {
            $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(auth()->user()->language);
        }
    @endphp

    <div class="mr-4">
        <img src="{{ $user->profile_photo_url ?? asset('images/placeholder-avatar.png') }}"
            alt="{{ $user->name ?? __('Unknown observer') }}" class="h-20 w-20 rounded-full object-cover" />
    </div>

    <div class="max-w-[calc(100%-7rem)]">
        <a href="/observers/{{ $user->slug }}" class="font-bold hover:underline">
            {{ $user->name ?? __('Unknown observer') }}
        </a>


        @php
            $link =
                config('app.old_url') . '/index.php?indexAction=comets_detail_object&object=' . $observation->objectid;
            $cometOld = \App\Models\CometObject::where('id', $observation->objectid)->first();
            $cometName = $cometOld ? $cometOld->name : __('Unknown comet');
        @endphp

        @php
            // Attempt to resolve and compute magnitude for this comet observation.
            $computedMag = null;
            try {
                $obsDate = \Carbon\Carbon::create(
                    substr($observation->date, 0, 4),
                    substr($observation->date, 4, 2),
                    substr($observation->date, 6, 2),
                );
            } catch (\Throwable $_) {
                $obsDate = null;
            }

            try {
                // Prefer modern orbital elements table when available
                $orb = null;
                try {
                    $orb = DB::table('comets_orbital_elements')->where('name', $cometName)->first();
                } catch (\Throwable $_) {
                    $orb = null;
                }

                if ($orb && $obsDate !== null) {
                    // Determine perihelion date: epoch may be JD stored in `epoch` column
                    $peri = null;
                    try {
                        if (isset($orb->epoch) && is_numeric($orb->epoch)) {
                            $peri = \deepskylog\AstronomyLibrary\Time::fromJd(floatval($orb->epoch));
                        }
                    } catch (\Throwable $_) {
                        $peri = null;
                    }
                    if (!$peri) {
                        // Fall back to observation date as perihelion anchor
                        try {
                            $peri = $obsDate;
                        } catch (\Throwable $_) {
                            $peri = null;
                        }
                    }

                    $e = isset($orb->e) ? floatval($orb->e) : null;
                    $q = isset($orb->q) ? floatval($orb->q) : null;

                    if ($e !== null) {
                        if ($e == 1.0) {
                            $tgt = new \deepskylog\AstronomyLibrary\Targets\Parabolic();
                            $tgt->setOrbitalElements(
                                $q ?? 0.0,
                                floatval($orb->i ?? 0.0),
                                floatval($orb->w ?? 0.0),
                                floatval($orb->node ?? 0.0),
                                $peri,
                            );
                        } elseif ($e < 1.0 && isset($orb->a)) {
                            $tgt = new \deepskylog\AstronomyLibrary\Targets\Elliptic();
                            $tgt->setOrbitalElements(
                                floatval($orb->a),
                                $e,
                                floatval($orb->i ?? 0.0),
                                floatval($orb->w ?? 0.0),
                                floatval($orb->node ?? 0.0),
                                $peri,
                            );
                        } else {
                            $tgt = new \deepskylog\AstronomyLibrary\Targets\NearParabolic();
                            $tgt->setOrbitalElements(
                                $q ?? 0.0,
                                $e ?? 1.0,
                                floatval($orb->i ?? 0.0),
                                floatval($orb->w ?? 0.0),
                                floatval($orb->node ?? 0.0),
                                $peri,
                            );
                        }

                        // Apply photometry params when present
                        if (isset($orb->H) && is_numeric($orb->H)) {
                            $H = floatval($orb->H);
                            $n = isset($orb->n) && is_numeric($orb->n) ? floatval($orb->n) : 4.0;
                            $phase =
                                isset($orb->phase_coeff) && is_numeric($orb->phase_coeff)
                                    ? floatval($orb->phase_coeff)
                                    : null;
                            $npre = isset($orb->n_pre) && is_numeric($orb->n_pre) ? floatval($orb->n_pre) : null;
                            $npost = isset($orb->n_post) && is_numeric($orb->n_post) ? floatval($orb->n_post) : null;
                            if (method_exists($tgt, 'setCometParams')) {
                                $tgt->setCometParams($H, $n, $phase, $npre, $npost);
                            } elseif (method_exists($tgt, 'setHG')) {
                                // Fallback for elliptic targets: set H with default G
                                $tgt->setHG($H, 0.15);
                            }
                        }

                        try {
                            if ($obsDate !== null && method_exists($tgt, 'magnitude')) {
                                $computedMag = $tgt->magnitude($obsDate);
                            }
                        } catch (\Throwable $_) {
                            $computedMag = null;
                        }
                    }
                }
            } catch (\Throwable $_) {
                // ignore photometry failures
                $computedMag = null;
            }
        @endphp

        {!! __(' observed :object', [
            'object' => '<a href="' . $link . '" class="font-bold hover:underline">' . $cometName . '</a>',
        ]) !!}
        @if ($computedMag !== null)
            <span class="text-gray-400">&nbsp;({{ __('mag') }} {{ number_format($computedMag, 1) }})</span>
        @endif

        {{ __(' on ') }}
        {{ \Carbon\Carbon::create($observation_date)->translatedFormat('j M Y') }}
        @if ($observation->locationid > 0)
            {{ __(' from ') }}
            @php
                $location = \App\Models\Location::where('id', $observation->locationid)->first();
                $locationSlug = $location ? $location->slug : '';
                $locationName = $location ? $location->name : __('Unknown location');
            @endphp
            <a href="/location/{{ $user->slug }}/{{ $locationSlug }}" class="font-bold hover:underline">
                {{ html_entity_decode($locationName) }}
                .
            </a>
        @endif
        <br />
        @if ($observation->instrumentid > 0)
            @php
                $instrument = \App\Models\Instrument::where('id', $observation->instrumentid)->first();
                $instrumentSlug = $instrument ? $instrument->slug : '';
                $instrumentName = $instrument ? $instrument->fullName() : __('Unknown instrument');
            @endphp
            {{ __('Used instrument was ') }}
            <a href="/instrument/{{ $user->slug }}/{{ $instrumentSlug }}" class="font-bold hover:underline">
                {!! html_entity_decode($instrumentName) !!}
                .
            </a>
        @endif

        @if ($observation->description != '')
            <br />
            {{ __(' The following notes where made: ') }}
            <br />
            <div class="my-2 rounded-sm bg-gray-900 px-4 py-4">
                <div class="flex items-start space-x-4">
                    @if ($observation->hasDrawing)
                        <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('open-comet-lightbox-{{ $observation->id }}'))"
                            class="flex-shrink-0 focus:outline-none">
                            <img src="/images/cometdrawings/{{ $observation->id }}.jpg"
                                alt="comet-drawing-{{ $observation->id }}" class="w-28 rounded" />
                        </button>
                    @endif

                    <div class="flex-1">
                        @if ($tr)
                            @php
                                $translated = null;
                                try {
                                    $translated = $tr->translate(html_entity_decode($observation->description));
                                } catch (\Throwable $e) {
                                    $translated = null;
                                }
                            @endphp
                            {!! $translated === null ? html_entity_decode($observation->description) : $translated !!}
                        @else
                            {!! html_entity_decode($observation->description) !!}
                        @endif
                    </div>
                </div>

                <!-- Modal / Lightbox (listens for custom event to open) -->
                <div x-data="{ open: false }" x-on:open-comet-lightbox-{{ $observation->id }}.window="open = true">
                    <div x-cloak x-show="open" x-transition.opacity="" @click.self="open = false"
                        @keydown.escape.window="open = false"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70">
                        <div class="max-w-4xl max-h-[90vh] p-4">
                            <button type="button" @click="open = false"
                                class="absolute top-4 right-4 z-50 rounded bg-gray-800 p-2 text-white">
                                &times;
                            </button>
                            <img src="/images/cometdrawings/{{ $observation->id }}.jpg"
                                alt="comet-drawing-large-{{ $observation->id }}"
                                class="max-w-full max-h-[85vh] rounded shadow-lg" />
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex items-center space-x-3 mt-2 mb-2">
            <a href='{{ config('app.old_url') }}/index.php?indexAction=comets_detail_observation&observation={{ $observation->id }}'
                class="inline-flex items-center px-4 py-2 rounded bg-gray-700 hover:bg-gray-600 text-white align-middle">
                {{ __('More details') }}
            </a>

            {{-- DSL message button: opens internal composer with to=username and a prefilled subject --}}
            @php if (auth()->check()) { @endphp
                <a href="{{ route('messages.create', ['to' => $user->username, 'subject' => 'About your observation of ' . \App\Models\CometObject::where('id', $observation->objectid)->first()->name]) }}"
                    class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white align-middle"
                    aria-label="{{ __('Send message about this sketch') }}">
                    {{-- envelope icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                        <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                    </svg>
                </a>
            @php } @endphp

            @php
                $likesCount = \App\Models\ObservationLike::where('observation_type', 'comet')
                    ->where('observation_id', $observation->id)
                    ->count();
                $liked =
                    auth()->check() &&
                    \App\Models\ObservationLike::where('observation_type', 'comet')
                        ->where('observation_id', $observation->id)
                        ->where('user_id', auth()->id())
                        ->exists();
            @endphp

            <button data-observation-type="comet" data-observation-id="{{ $observation->id }}"
                class="like-button px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-white align-middle">
                <span class="like-icon">{!! $liked ? '❤️' : '👍' !!}</span>
                <span class="like-count">{{ $likesCount }}</span>
            </button>
        </div>
    </div>
</div>
