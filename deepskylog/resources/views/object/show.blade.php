<x-app-layout>
    <div>
        <div class="mx-auto max-w-7xl bg-gray-900 px-4 py-6 sm:px-4 lg:px-6">
            <header class="mb-6">
                <h1 class="text-3xl font-extrabold">{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</h1>
                <p class="text-sm flex items-center gap-2 text-gray-300 mt-2">
                    <span class="text-gray-400">{{ __('Object type') }}</span>
                    <span class="text-white font-medium ml-2">{{ $session->source_type ?? __('Unknown') }}</span>
                    @if(!empty($session->constellation))
                        <span class="text-gray-400 ml-4">{{ __('Constellation') }}:</span>
                        <span class="text-white font-medium ml-2">{{ $session->constellation }}</span>
                    @endif
                </p>
            </header>

            <div class="grid md:grid-cols-3 gap-4">
                <article class="md:col-span-2">
                    @if(!empty($image))
                        <img class="w-full rounded shadow mb-3" src="{{ $image }}" alt="{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
                    @endif

                    <div class="mb-4 text-gray-100">
                        <h2 class="text-xl font-semibold text-white">{{ __('Object details') }}</h2>
                        <table class="table-auto w-full text-sm text-gray-100">
                            <tr>
                                <td class="pr-4 font-medium">{{ __('Name') }}</td>
                                <td>
                                    @php
                                        // Prefer canonicalSlug provided by controller, fall back to session.slug or slugified name
                                        $primarySlug = $canonicalSlug ?? ($session->slug ? $session->slug : \Illuminate\Support\Str::slug($session->name ?? '', '-'));
                                    @endphp
                                    <a href="{{ route('object.show', ['slug' => $primarySlug]) }}" class="font-bold text-white hover:underline">{{ $session->name }}</a>
                                </td>
                            </tr>
                            @if(!empty($alternatives) && is_array($alternatives) && count($alternatives) > 0)
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Also known as') }}</td>
                                    <td>
                                        @php
                                            // Render alternatives as a comma-separated list with exactly one space after comma.
                                            $altLinks = [];
                                            foreach ($alternatives as $alt) {
                                                $altSlug = \Illuminate\Support\Str::slug($alt, '-');
                                                $url = route('object.show', ['slug' => $altSlug]);
                                                $altLinks[] = '<a href="'.e($url).'" class="text-gray-300 hover:underline">'.e($alt).'</a>';
                                            }
                                        @endphp
                                        {!! implode(', ', $altLinks) !!}
                                    </td>
                                </tr>
                            @endif
                            @if(isset($session->ra) && isset($session->decl))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('RA / Dec') }}</td>
                                    <td>{{ $session->ra }} / {{ $session->decl }}</td>
                                </tr>
                                @if(!empty($atlasName) || !empty($atlasPage))
                                    <tr>
                                        <td class="pr-4 font-medium">
                                            @if(!empty($atlasName))
                                                {{ $atlasName }}
                                            @endif
                                            @if(!empty($atlasPage))
                                                @if(!empty($atlasName))
                                                    {{ ' ' }}
                                                @endif
                                                {{ __('page:') }}
                                            @endif                                            
                                        </td>
                                        <td>
                                            @if(!empty($atlasPage))
                                                {{ $atlasPage }}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <td class="pr-4 font-medium">{{ __('Description') }}</td>
                                <td>{!! nl2br(e($session->comments ?? '')) !!}</td>
                            </tr>
                            @if(!empty($session->mag))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Magnitude') }}</td>
                                    <td>{{ $session->mag ?? '' }}</td>
                                </tr>
                            @endif

                            @if(!empty($session->subr))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Surface brightness') }}</td>
                                    <td>{{ $session->subr ?? '' }}</td>
                                </tr>
                            @endif

                            @if(isset($session->contrast_reserve))
                                <tr>
                                    <td class="pr-4 font-medium">
                                        <span>{{ __('Contrast reserve') }}</span>
                                        <div x-data="{ open: false }" class="inline-block relative">
                                            <button @click.prevent="open = !open" @keydown.escape="open = false" :aria-expanded="open.toString()" aria-haspopup="true" class="ml-2 text-gray-400 hover:text-gray-200 focus:outline-none" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-8 4a1 1 0 100 2 1 1 0 000-2zm.75-6.75a.75.75 0 00-1.5 0V10a.75.75 0 001.5 0V7.25z" clip-rule="evenodd"/></svg>
                                            </button>

                                            <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute left-0 mt-2 w-64 p-3 bg-gray-800 text-sm text-gray-100 rounded shadow-lg">
                                                <div class="font-semibold mb-2">{{ __('contrast.reserve.tooltip_title') }}</div>
                                                <ul class="text-xs space-y-1">
                                                    <li>{{ __('contrast.reserve.category.very_easy') }}</li>
                                                    <li>{{ __('contrast.reserve.category.easy') }}</li>
                                                    <li>{{ __('contrast.reserve.category.quite_difficult') }}</li>
                                                    <li>{{ __('contrast.reserve.category.difficult') }}</li>
                                                    <li>{{ __('contrast.reserve.category.questionable') }}</li>
                                                    <li>{{ __('contrast.reserve.category.not_visible') }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->contrast_reserve === null)
                                            {{ __('Unknown') }}
                                        @else
                                            @php
                                                $crCat = $session->contrast_reserve_category ?? null;
                                                $crClass = 'text-white';
                                                // Map categories to requested colors:
                                                if ($crCat === 'very_easy') {
                                                    $crClass = 'text-green-400';
                                                } elseif ($crCat === 'easy') {
                                                    $crClass = 'text-green-700';
                                                } elseif ($crCat === 'quite_difficult') {
                                                    $crClass = 'text-orange-400';
                                                } elseif ($crCat === 'difficult') {
                                                    $crClass = 'text-orange-700';
                                                } elseif ($crCat === 'questionable') {
                                                    $crClass = 'text-gray-300';
                                                } elseif ($crCat === 'not_visible') {
                                                    $crClass = 'text-gray-600';
                                                }

                                                $categoryText = $crCat ? __('contrast.reserve.category.' . $crCat) : __('Unknown');
                                            @endphp

                                            <div x-data="{ openCR: false }" class="inline-block relative">
                                                <button @click.prevent="openCR = !openCR" @keydown.escape="openCR = false" :aria-expanded="openCR.toString()" aria-haspopup="true" type="button" class="focus:outline-none {{ $crClass }} font-medium">
                                                    {{ $session->contrast_reserve }}
                                                </button>

                                                <div x-show="openCR" x-cloak @click.outside="openCR = false" x-transition class="absolute z-10 left-0 mt-2 w-80 p-3 bg-gray-800 text-sm text-gray-100 rounded shadow-lg">
                                                    <div class="text-sm mb-2">{{ __('contrast.reserve.summary', ['value' => $session->contrast_reserve, 'category' => $categoryText]) }}</div>
                                                    <div class="text-xs text-gray-300 mb-1"><strong>{{ __('Location') }}:</strong> {{ $session->contrast_used_location ?? __('Unknown') }}</div>
                                                    <div class="text-xs text-gray-300"><strong>{{ __('Instrument') }}:</strong> {{ $session->contrast_used_instrument ?? __('Unknown') }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            {{-- Additional object fields that were present previously: optimum magnification, eyepieces, size and position angle --}}
                            @if(!empty($session->optimum_detection_magnification))
                                @php
                                    $eps = [];
                                    foreach ($session->optimum_eyepieces as $ep) {
                                        $name = $ep['name'] ?? ($ep['label'] ?? null);
                                        if (! empty($name)) { $parts[] = e($name); }
                                        if (! empty($parts)) { $eps[] = implode(' — ', $parts); }
                                    }
                                @endphp
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Optimum detection magnification') }}</td>
                                    <td>{{ $session->optimum_detection_magnification }}x - {!! implode(', ', $eps) !!}</td>
                                </tr>
                            @endif


                            @if(isset($session->diam1) || isset($session->diam2))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Size') }}</td>
                                    <td>
                                        @php
                                            $d1 = $session->diam1 ?? '';
                                            $d2 = $session->diam2 ?? '';
                                        @endphp
                                        {{ $d1 }}' @if(!empty($d1) && !empty($d2)) x @endif {{ $d2 }}'
                                    </td>
                                </tr>
                            @endif

                            @if(!empty($session->pa))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Position angle') }}</td>
                                    <td>{{ $session->pa }}</td>
                                </tr>
                            @endif

                        </table>

                    </div>

                </article>

                                        <aside class="md:col-span-1">
                    <div class="bg-gray-800 p-3 rounded shadow text-gray-100">
                        <h4 class="font-semibold mb-2 text-white">{{ __('Quick links') }}</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('session.all') }}" class="text-gray-300 hover:underline">{{ __('All sessions') }}</a></li>
                            <li><a href="{{ route('observations.index') }}" class="text-gray-300 hover:underline">{{ __('All observations') }}</a></li>
                            @php
                                // Prepare name and coordinates for external links
                                $objectName = $session->name ?? null;
                                $hasCoords = isset($session->ra) && isset($session->decl) && !empty($session->ra) && !empty($session->decl);
                                // SIMBAD: prefer name search, otherwise use coordinates (format: %2B12+34+56+%2B12+34+56 not necessary here, use basic coords)
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
                                }

                                        
                            @endphp

                            @if($simbadUrl || $nedUrl || $wikipediaUrl || $aladinUrl)
                                <li class="pt-2 border-t border-gray-700 text-xs text-gray-400">{{ __("External databases") }}</li>
                                @if($simbadUrl)
                                    <li>
                                        <a href="{{ $simbadUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-300 hover:text-white">
                                            <!-- SIMBAD icon (simple star) -->
                                            <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2l2.39 4.85L19 8.27l-3.5 3.41L16.18 19 12 16.27 7.82 19l.68-7.32L4.999 8.27l4.61-.42L12 2z" fill="currentColor"/></svg>
                                            <span>SIMBAD</span>
                                        </a>
                                    </li>
                                @endif
                                @if($nedUrl)
                                    <li>
                                        <a href="{{ $nedUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-300 hover:text-white">
                                            <!-- NED icon (globe) -->
                                            <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 2.06v2.04a6.002 6.002 0 013.364 3.364H17A8 8 0 0013 4.06zM6.636 7.48A6.002 6.002 0 0111 4.1V2.06A8 8 0 006.636 7.48zM4.06 11H6.1a6.002 6.002 0 010 2H4.06A8 8 0 004.06 11zM6.636 16.52A8 8 0 0011 21.94v-2.04a6.002 6.002 0 01-4.364-3.38zM13 19.94v-2.04a6.002 6.002 0 01-3.364-3.364H11a8 8 0 002 5.404z" fill="currentColor"/></svg>
                                            <span>NED</span>
                                        </a>
                                    </li>
                                @endif
                                @if($wikipediaUrl)
                                    <li>
                                        <a href="{{ $wikipediaUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-300 hover:text-white">
                                            <!-- Wikipedia icon (W) -->
                                            <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2l2.5 4.9L19 8l-4 3.6L16 19 12 16.2 8 19l1-7.4L5 8l4.5-.9L12 2z" fill="currentColor"/></svg>
                                            <span>Wikipedia</span>
                                        </a>
                                    </li>
                                @endif
                                @if($aladinUrl)
                                    <li>
                                        <a href="{{ $aladinUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-300 hover:text-white">
                                            <!-- Aladin icon (map/target) -->
                                            <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.2" fill="none"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                                            <span>Aladin Lite</span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
