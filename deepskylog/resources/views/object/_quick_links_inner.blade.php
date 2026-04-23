<div class="bg-gray-800 p-3 rounded shadow text-gray-100">
    <h4 class="font-semibold mb-2 text-white">{{ __('Quick links') }}</h4>
    @php $isCometLocal = strtolower(trim((string) ($session->source_type_raw ?? '')) ) === 'comet'; @endphp
    <ul class="space-y-2 text-sm">
        <li>
            @if (!empty($canonicalSlug))
                @if ($isCometLocal)
                    <a href="{{ url('/cometobservations/' . $canonicalSlug) }}"
                        class="text-gray-300 hover:underline">{{ __('All observations') }}</a>
                @else
                    <a href="{{ url('/observations/' . $canonicalSlug) }}"
                        class="text-gray-300 hover:underline">{{ __('All observations') }}</a>
                @endif
            @else
                @if ($isCometLocal)
                    <a href="{{ route('observations.comet.index') }}"
                        class="text-gray-300 hover:underline">{{ __('All observations') }}</a>
                @else
                    <a href="{{ route('observations.index') }}"
                        class="text-gray-300 hover:underline">{{ __('All observations') }}</a>
                @endif
            @endif
        </li>
        <li>
            @if (!empty($canonicalSlug))
                @if ($isCometLocal)
                    <a href="{{ route('cometdrawings.index') }}"
                        class="text-gray-300 hover:underline">{{ __('All drawings') }}</a>
                @else
                    <a href="{{ route('observations.drawings.show', ['slug' => $canonicalSlug]) }}"
                        class="text-gray-300 hover:underline">{{ __('All drawings') }}</a>
                @endif
            @else
                <a href="{{ route('drawings.index') }}"
                    class="text-gray-300 hover:underline">{{ __('All drawings') }}</a>
            @endif
        </li>
        @auth
            @if (!empty($canonicalSlug) && auth()->user()->slug)
                @if ($isCometLocal)
                    <li><a href="{{ route('observations.comet.user.object', ['observer' => auth()->user()->slug, 'object' => $canonicalSlug]) }}"
                            class="text-gray-300 hover:underline">{{ __('My observations') }}</a></li>
                    <li><a href="{{ route('cometdrawings.user.object', ['observer' => auth()->user()->slug, 'object' => $canonicalSlug]) }}"
                            class="text-gray-300 hover:underline">{{ __('My drawings') }}</a></li>
                @else
                    <li><a href="{{ route('observations.user.object', ['observer' => auth()->user()->slug, 'object' => $canonicalSlug]) }}"
                            class="text-gray-300 hover:underline">{{ __('My observations') }}</a></li>
                    <li><a href="{{ route('observations.drawings.user.object', ['observer' => auth()->user()->slug, 'object' => $canonicalSlug]) }}"
                            class="text-gray-300 hover:underline">{{ __('My drawings') }}</a></li>
                @endif
            @else
                @if ($isCometLocal)
                    <li><a href="{{ route('observations.comet.show', ['observer' => auth()->user()->slug]) }}"
                            class="text-gray-300 hover:underline">{{ __('My observations') }}</a></li>
                    <li><a href="{{ route('cometdrawings.show', ['observer' => auth()->user()->slug]) }}"
                            class="text-gray-300 hover:underline">{{ __('My drawings') }}</a></li>
                @else
                    <li><a href="{{ route('observations.show', ['observer' => auth()->user()->slug]) }}"
                            class="text-gray-300 hover:underline">{{ __('My observations') }}</a></li>
                    <li><a href="{{ route('drawings.show', ['observer' => auth()->user()->slug]) }}"
                            class="text-gray-300 hover:underline">{{ __('My drawings') }}</a></li>
                @endif
            @endif

            {{-- Active observing list toggle --}}
            @if (!empty($session->name))
                @livewire('observing-list-toggle', ['objectName' => $session->name], key('ol-toggle-' . $session->name))
            @endif
        @endauth

        @php
            $objectName = $session->name ?? null;
            $hasCoords = isset($session->ra) && isset($session->decl) && !empty($session->ra) && !empty($session->decl);
            $simbadUrl = null;
            $nedUrl = null;
            $wikipediaUrl = null;
            $aladinUrl = null;

            if ($objectName) {
                $encName = rawurlencode($objectName);
                $simbadUrl = "https://simbad.cds.unistra.fr/simbad/sim-id?Ident=$encName";
                $nedUrl = "https://ned.ipac.caltech.edu/byname?objname=$encName";
                $wikipediaUrl = "https://en.wikipedia.org/wiki/Special:Search?search=$encName";
                $aladinUrl = "https://aladin.u-strasbg.fr/AladinLite/?target=$encName";
            } elseif ($hasCoords) {
                $raParam = rawurlencode($session->ra ?? '');
                $decParam = rawurlencode($session->decl ?? '');
                if (!empty($raParam) && !empty($decParam)) {
                    $aladinUrl = "https://aladin.u-strasbg.fr/AladinLite/?ra={$raParam}&dec={$decParam}";
                }
            }
        @endphp

        @if ($simbadUrl || $nedUrl || $wikipediaUrl || $aladinUrl)
            <li class="pt-2 border-t border-gray-700 text-xs text-gray-400">{{ __('External databases') }}</li>
            @if ($simbadUrl)
                <li>
                    <a href="{{ $simbadUrl }}" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-2 text-gray-300 hover:text-white">
                        <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path
                                d="M12 2l2.39 4.85L19 8.27l-3.5 3.41L16.18 19 12 16.27 7.82 19l.68-7.32L4.999 8.27l4.61-.42L12 2z"
                                fill="currentColor" />
                        </svg>
                        <span>SIMBAD</span>
                    </a>
                </li>
            @endif
            @if ($nedUrl)
                <li>
                    <a href="{{ $nedUrl }}" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-2 text-gray-300 hover:text-white">
                        <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path
                                d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 2.06v2.04a6.002 6.002 0 013.364 3.364H17A8 8 0 0013 4.06zM6.636 7.48A6.002 6.002 0 0111 4.1V2.06A8 8 0 006.636 7.48zM4.06 11H6.1a6.002 6.002 0 010 2H4.06A8 8 0 004.06 11zM6.636 16.52A8 8 0 0011 21.94v-2.04a6.002 6.002 0 01-4.364-3.38zM13 19.94v-2.04a6.002 6.002 0 01-3.364-3.364H11a8 8 0 002 5.404z"
                                fill="currentColor" />
                        </svg>
                        <span>NED</span>
                    </a>
                </li>
            @endif
            @if ($wikipediaUrl)
                <li>
                    <a href="{{ $wikipediaUrl }}" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-2 text-gray-300 hover:text-white">
                        <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 2l2.5 4.9L19 8l-4 3.6L16 19 12 16.2 8 19l1-7.4L5 8l4.5-.9L12 2z"
                                fill="currentColor" />
                        </svg>
                        <span>Wikipedia</span>
                    </a>
                </li>
            @endif
            @if ($aladinUrl)
                <li>
                    <a href="{{ $aladinUrl }}" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-2 text-gray-300 hover:text-white">
                        <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path
                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6.5a2.5 2.5 0 010 5z"
                                fill="currentColor" />
                        </svg>
                        <span>{{ __('aladin.lite') }}</span>
                    </a>
                </li>
            @endif
        @endif
    </ul>
</div>
