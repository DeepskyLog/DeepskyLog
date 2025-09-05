{{-- Location model is referenced via $location variable; avoid inline `use` in Blade which breaks compiled PHP --}}
<x-app-layout>
    <div>
    <div class="mx-auto max-w-7xl bg-gray-900 px-4 py-6 sm:px-4 lg:px-6">
        <header class="mb-6">
            <h1 class="text-3xl font-extrabold">{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</h1>
            <p class="text-sm flex items-center gap-2 text-gray-300">
                <span class="text-gray-400">{{ __('by') }}</span>
                <a class="text-white hover:underline font-medium" href="{{ route('observer.show', $user->slug) }}">{{ $user->name }}</a>
                @if(!empty($observerStats))
                    <span class="text-gray-500">&middot;</span>
                    <a class="text-gray-300 hover:underline" href="https://www.deepskylog.be/index.php?indexAction=result_selected_observations&sessionid={{ $session->id }}">{{ $totalObservations }} {{ __('observations') }}</a>
                @endif
            </p>

            <div class="mt-3 flex items-center gap-3">
                {{-- Share buttons using ShareButtons facade configured in app --}}
                {!! \ShareButtons::page(url()->current(), __('Observations from :session by :owner', ['session' => html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'owner' => $user->name])) !!}

                {{-- Message button moved to bottom of page --}}
            </div>
        </header>

    <div class="grid md:grid-cols-3 gap-4">
                <article class="md:col-span-2">
                    @if(!empty($image))
                    <img class="w-full rounded shadow mb-3" src="{{ $image }}" alt="{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
                @endif

                <div class="mb-4 text-gray-100">
                    <h2 class="text-xl font-semibold text-white">{{ __('Session details') }}</h2>
                    <table class="table-auto w-full text-sm text-gray-100">
                        <tr>
                            <td class="pr-4 font-medium">{{ __('Date') }}</td>
                            <td>
                                @php
                                    $begin = $session->begindate ? \Carbon\Carbon::parse($session->begindate)->translatedFormat('j M Y') : __('Unknown');
                                    $end = $session->enddate ? \Carbon\Carbon::parse($session->enddate)->translatedFormat('j M Y') : __('Unknown');
                                @endphp
                                <span>{{ $begin }}</span>
                                <span class="mx-3 text-gray-400">&ndash;</span>
                                <span>{{ $end }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="pr-4 font-medium">{{ __('Location') }}</td>
                            <td>
                                @if ($location)
                                    <a href="{{ route('location.show', [$location->user->slug, $location->slug]) }}">{{ $location->name }}</a>
                                @else
                                    {{ __('Unknown') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="py-2"></td>
                        </tr>
                        <tr>
                            <td class="pr-4 font-medium">{{ __('Weather') }}</td>
                            <td>{!! nl2br(e(html_entity_decode($session->weather ?? __('Unknown'), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) !!}</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="py-2"></td>
                        </tr>
                        <tr>
                            <td class="pr-4 font-medium">{{ __('Equipment') }}</td>
                            <td>{!! nl2br(e(html_entity_decode($session->equipment ?? __('Unknown'), ENT_QUOTES | ENT_HTML5, 'UTF-8'))) !!}</td>
                        </tr>
                        @if(false)
                            {{-- comments moved out of table --}}
                            <tr>
                                <td>{!! html_entity_decode(nl2br(e($session->comments))) !!}</td>
                            </tr>
                        @endif
                    </table>
                    @if(!empty($session->comments))
                        <div class="mt-4">
                            <x-card>
                                <div class="text-sm">{!! nl2br(e($session->comments)) !!}</div>
                            </x-card>
                        </div>
                    @endif
                </div>

                @if($location)
                    <div id="session-location-map" class="w-full h-64 rounded mb-4"></div>
                @endif

                {{-- Drawings first: show sketches (images) above textual observations --}}
                @if($drawings->count() > 0)
                    <section class="mb-6">
                        <h3 class="text-lg font-semibold text-white">{{ __('Drawings by :owner', ['owner' => $user->name]) }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            @foreach($drawings as $drawing)
                                @php
                                    $observer_name = \App\Models\User::where('username', $drawing->observerid)->first()->name ?? $drawing->observerid;
                                    $observation_date = substr($drawing->date, 0, 4) . "-" . substr($drawing->date, 4, 2) . "-" . substr($drawing->date, 6, 2);
                                @endphp

                                <div class="max-w-xl">
                                    @if(isset($drawing->objectname))
                                        <x-sketch-deepsky :observation_id="$drawing->id" :observer_name="$observer_name" :observer_username="$drawing->observerid" :observation_date="$observation_date" />
                                    @else
                                        <x-sketch-comet :observation_id="$drawing->id" :observer_name="$observer_name" :observer_username="$drawing->observerid" :observation_date="$observation_date" />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section>
                    <h3 class="text-lg font-semibold text-white">{{ __('Observations by :owner', ['owner' => $user->name]) }}</h3>
                    @if($observations->count() > 0)
                        @foreach($observations as $observation)
                            {{-- Skip observations that have drawings to avoid duplicate display --}}
                            @if(!empty($observation->hasDrawing) && $observation->hasDrawing == 1)
                                @continue
                            @endif

                            @if(isset($observation->objectname))
                                <x-observation-deepsky :observation="$observation" />
                            @else
                                <x-observation-comet :observation="$observation" />
                            @endif
                        @endforeach

                        <div class="mt-4">
                            {{ $observations->links() }}
                        </div>
                    @else
                        <p class="text-sm text-gray-600">{{ __('No observations found for this observer in this session.') }}</p>
                    @endif
                </section>
            </article>

            <aside class="md:col-span-1">
                <div class="bg-gray-800 p-3 rounded shadow text-gray-100">
                    <h4 class="font-semibold mb-2 text-white">{{ __('Observers') }}</h4>
                    <ul class="space-y-2">
                        {{-- Show primary observer and other observers with icons and counts --}}
                        @foreach($observerStats as $stat)
                            <li class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa fa-user-circle text-xl text-gray-300"></i>
                                    @if(!empty($stat['user']) && isset($stat['user']['slug']))
                                        <a href="{{ route('observer.show', $stat['user']['slug']) }}">{{ $stat['user']['name'] }}</a>
                                    @elseif($stat['username'] === $user->username)
                                        <a href="{{ route('observer.show', $user->slug) }}">{{ $user->name }}</a>
                                    @else
                                        {{ $stat['username'] }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-300">{{ $stat['count'] }} {{ __('obs') }}</div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 border-t border-gray-700 pt-3">
                        <div class="text-sm text-gray-300">{{ __('Total observations in session') }}</div>
                        <div class="text-2xl font-bold text-white">{{ $totalObservations }}</div>
                    </div>
                </div>
                    
                <div class="mt-3 text-right">
                    <div class="flex items-center justify-end gap-3">
                        {{-- Share buttons for session (same options as sketches) --}}
                        <div>
                            {!!
                                ShareButtons::page(url(route('session.show', [$user->slug, $session->slug])), __('Look at this session :session by :owner', ['session' => $session->name, 'owner' => $user->name]), [
                                    'title' => __('Share this session'),
                                    'class' => 'text-gray-500 hover:text-gray-700',
                                    'rel' => 'nofollow noopener noreferrer',
                                ])
                                    ->facebook(['class' => 'hover', 'rel' => 'follow'])
                                    ->twitter(['class' => 'hover', 'rel' => 'follow'])
                                    ->copylink()
                                    ->mailto(['class' => 'hover', 'rel' => 'nofollow'])
                                    ->whatsapp()
                                    ->bluesky(['class' => 'hover', 'rel' => 'follow'])
                                    ->render()
                            !!}
                        </div>

                        @php
                            $sessionLikes = \App\Models\ObservationLike::where('observation_type', 'session')->where('observation_id', $session->id)->count();
                            $sessionLiked = auth()->check() && \App\Models\ObservationLike::where('observation_type', 'session')->where('observation_id', $session->id)->where('user_id', auth()->id())->exists();
                        @endphp

                        <button data-observation-type="session" data-observation-id="{{ $session->id }}" class="like-button px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-white">
                            <span class="like-icon">{!! $sessionLiked ? '❤️' : '👍' !!}</span>
                            <span class="like-count">{{ $sessionLikes }}</span>
                        </button>

                        {{-- Message about session (link to internal messaging) --}}
                        <a href="{{ route('messages.create', ['to' => $user->username ?? $user->name, 'subject' => __('About your session :session', ['session' => $session->name])]) }}" class="inline-flex items-center p-2 rounded bg-blue-600 hover:bg-blue-700 text-white" aria-label="{{ __('Send message about this session') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                                <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
        @if ($location)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                        var map = L.map('session-location-map', { fullscreenControl: true }).setView([
                            {{ $location->latitude }},
                            {{ $location->longitude }}
                        ], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);

                        var marker = L.marker([{{ $location->latitude }}, {{ $location->longitude }}]).addTo(map)
                            .bindPopup('<strong>{{ addslashes($location->name ?? '') }}</strong>');
                        marker.openPopup();
                });
            </script>
        @endif
    @endpush
    
</x-app-layout>
