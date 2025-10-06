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
                                                // very_easy (>1.0) -> bright green
                                                // easy (0.5-1.0) -> dark green
                                                // quite_difficult (0.35-0.5) -> bright orange
                                                // difficult (0.10-0.35) -> dark orange
                                                // questionable (-0.2-0.10) -> bright gray
                                                // not_visible (< -0.2) -> dark gray
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

                                @if(isset($session->optimum_detection_magnification))
                                    <tr>
                                        <td class="pr-4 font-medium">{{ __('Optimum detection magnification') }}</td>
                                        <td>
                                            @if($session->optimum_detection_magnification === null)
                                                {{ __('Unknown') }}
                                            @else
                                                @if(!empty($session->optimum_eyepieces) && is_array($session->optimum_eyepieces))
                                                    @php
                                                        // Show only the primary/used eyepiece for Optimum Detection magnification.
                                                        $primaryEp = $session->optimum_eyepieces[0] ?? null;
                                                    @endphp
                                                @endif

                                                <div x-data="{ openOpt: false }" class="inline-block relative">
                                                    <button @click.prevent="openOpt = !openOpt" @keydown.escape="openOpt = false" :aria-expanded="openOpt.toString()" aria-haspopup="true" type="button" class="focus:outline-none text-white font-medium">
                                                        {{ $session->optimum_detection_magnification }}x - {{ $primaryEp['name'] ?? ($primaryEp['focal'] . 'mm') }}
                                                    </button>

                                                    <div x-show="openOpt" x-cloak @click.outside="openOpt = false" x-transition class="absolute z-10 left-0 mt-2 w-80 p-3 bg-gray-800 text-sm text-gray-100 rounded shadow-lg">
                                                        <div class="text-sm mb-2">{{ __('Estimated best magnification using your instrument and eyepieces') }}</div>
                                                        <div class="text-xs text-gray-300 mb-1"><strong>{{ __('Location') }}:</strong> {{ $session->contrast_used_location ?? __('Unknown') }}</div>
                                                        <div class="text-xs text-gray-300 mb-1"><strong>{{ __('Instrument') }}:</strong> {{ $session->contrast_used_instrument ?? __('Unknown') }}</div>
                                                        @if($primaryEp)
                                                            <div class="text-xs text-gray-300">
                                                                <strong>{{ __('Eyepiece') }}:</strong>
                                                                <span class="ml-1">{{ $primaryEp['name'] ?? ($primaryEp['focal'] . 'mm') }} — {{ $primaryEp['focal'] }}mm</span>
                                                            </div>
                                                        @endif

                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endif

                            @if((!empty($session->diam1)))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Size') }}</td>
                                    <td>
                                        @if(!empty($session->diam1) && !empty($session->diam2))
                                            {{ $session->diam1 }}' x {{ $session->diam2 }}'
                                        @else
                                            {{ $session->diam1 }}'
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            @if(!empty($session->pa))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Position angle') }}</td>
                                    <td>{{ $session->pa }}°</td>
                                </tr>
                            @endif
                        </table>
                    </div>

                    <section>
                        <h3 class="text-lg font-semibold text-white">{{ __('Observations') }}</h3>
                        <p class="text-sm text-gray-600">{{ __('No observations listed for this object here. Use the search or check user pages for observations.') }}</p>
                    </section>
                </article>

                <aside class="md:col-span-1">
                    <div class="bg-gray-800 p-3 rounded shadow text-gray-100">
                        <h4 class="font-semibold mb-2 text-white">{{ __('Quick links') }}</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('session.all') }}" class="text-gray-300 hover:underline">{{ __('All sessions') }}</a></li>
                            <li><a href="{{ route('observations.index') }}" class="text-gray-300 hover:underline">{{ __('All observations') }}</a></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
