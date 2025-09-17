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
                    <a class="text-gray-300 hover:underline" href="{{ route('session.show', [$user->slug, $session->slug]) }}?observer={{ urlencode($session->observerid) }}">{{ $totalObservations }} {{ __('observations') }}</a>
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
                        @php
                            // weather, equipment and comments are translated/cached in the controller if needed
                            $rawWeather = html_entity_decode($session->weather ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $weatherTranslated = $session->weather_translated ?? $rawWeather;
                            $weatherVisible = trim(strip_tags($weatherTranslated)) !== '';
                        @endphp
                        @if($weatherVisible)
                            <tr>
                                <td class="pr-4 font-medium">{{ __('Weather') }}</td>
                                <td>{!! strip_tags($weatherTranslated, '<p><br><em><strong><b><i><u><ul><ol><li>') !!}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="2" class="py-2"></td>
                        </tr>
                        @php
                            $rawEquipment = html_entity_decode($session->equipment ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $equipmentTranslated = $session->equipment_translated ?? $rawEquipment;
                            $equipmentVisible = trim(strip_tags($equipmentTranslated)) !== '';
                        @endphp
                        @if($equipmentVisible)
                            <tr>
                                <td class="pr-4 font-medium">{{ __('Equipment') }}</td>
                                <td>{!! strip_tags($equipmentTranslated, '<p><br><em><strong><b><i><u><ul><ol><li>') !!}</td>
                            </tr>
                        @endif
                        @if(false)
                            {{-- comments moved out of table --}}
                            <tr>
                                <td>{!! html_entity_decode(nl2br(e($session->comments))) !!}</td>
                            </tr>
                        @endif
                    </table>
                    @if(!empty($session->comments))
                        @php
                            $rawComments = html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $commentsTranslated = $session->comments_translated ?? $rawComments;
                        @endphp
                        <div class="mt-4">
                            <x-card>
                                <div class="text-sm">{!! strip_tags($commentsTranslated, '<p><br><em><strong><b><i><u><ul><ol><li>') !!}</div>
                            </x-card>
                        </div>
                    @endif
                    @php
                        $viewer = auth()->user();
                        $allowAdmin = config('sessions.allow_admin_override', false);
                        $viewerIsOwner = $viewer && ($viewer->username === $session->observerid);
                        $viewerIsAdmin = $viewer && method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges();
                        $showOwnerActions = $viewerIsOwner || ($allowAdmin && $viewerIsAdmin);
                    @endphp

                    @if($showOwnerActions)
                        <div class="mt-3 flex items-center justify-end gap-3">
                            @if($viewerIsAdmin && ! $viewerIsOwner && $allowAdmin)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-600 text-white rounded" title="{{ __('Administrator override enabled: actions performed will be executed as an administrator on behalf of the owner') }}">{{ __('Admin override') }}</span>
                            @endif

                            <a href="{{ route('session.adapt', $session->id) }}" class="inline-flex items-center p-2 rounded bg-yellow-600 hover:bg-yellow-700 text-white" aria-label="{{ __('Adapt this session') }}">
                                {{ __('Adapt') }}
                            </a>

                            @php
                                $deleteConfirm = $viewerIsAdmin && ! $viewerIsOwner && $allowAdmin
                                    ? __('You are performing this action as an administrator on behalf of the owner. Are you sure you want to delete this session?')
                                    : __('Are you sure you want to delete this session?');
                            @endphp
                            <form method="POST" action="{{ route('session.destroy', $session->id) }}" data-confirm="{{ e($deleteConfirm) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center p-2 rounded bg-red-600 hover:bg-red-700 text-white">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    @endif
                </div>

                @if ($location)
                    <div id="session-location-map" class="w-full h-64 rounded mb-4"
                         data-lat="{{ $location->latitude }}"
                         data-lng="{{ $location->longitude }}"
                         data-name="{{ $location->name ?? '' }}">
                    </div>
                @endif

                {{-- Drawings first: show sketches (images) above textual observations --}}
                @if(isset($drawings) && $drawings->count() > 0)
                    <section class="mb-6">
                        <h3 class="text-lg font-semibold text-white">{{ __('Drawings by :owner', ['owner' => $selectedObserverName ?? $user->name]) }}</h3>
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

                        <div class="mt-4">
                            {{-- Render pagination links for drawings using the separate page name --}}
                            {{ $drawings->withQueryString()->links() }}
                        </div>
                    </section>
                @else
                    {{-- No drawings found for this session --}}
                @endif

                <section>
                    <h3 class="text-lg font-semibold text-white">{{ __('Observations by :owner', ['owner' => $selectedObserverName ?? $user->name]) }}</h3>
                    @if($observations->count() > 0)
                        @foreach($observations as $observation)
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
                            @php
                                $observerUsername = $stat['username'];
                                $observerSlug = $stat['user']['slug'] ?? null;
                                $isSelected = isset($selectedObserverUsername) && $selectedObserverUsername === $observerUsername;
                                // Merge current query parameters but force observer to this username so pagination is preserved when possible
                                $qs = array_merge(request()->query(), ['observer' => $observerUsername]);
                                $link = route('session.show', [$user->slug, $session->slug]) . '?' . http_build_query($qs);
                            @endphp

                            <li class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fa fa-user-circle text-xl {{ $isSelected ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                    @if($observerSlug)
                                        <a href="{{ $link }}" class="{{ $isSelected ? 'font-semibold text-white' : 'text-gray-200 hover:underline' }}">{{ $stat['user']['name'] }}</a>
                                    @elseif($observerUsername === $user->username)
                                        <a href="{{ $link }}" class="{{ $isSelected ? 'font-semibold text-white' : 'text-gray-200 hover:underline' }}">{{ $user->name }}</a>
                                    @else
                                        <a href="{{ $link }}" class="{{ $isSelected ? 'font-semibold text-white' : 'text-gray-200 hover:underline' }}">{{ $observerUsername }}</a>
                                    @endif
                                </div>
                                <div class="text-sm {{ $isSelected ? 'text-white' : 'text-gray-300' }}"> <a href="{{ $link }}">{{ $stat['count'] }} {{ __('obs') }}</a></div>
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
                        @auth
                        <a href="{{ route('messages.create', ['to' => $user->username ?? $user->name, 'subject' => __('About your session :session', ['session' => $session->name])]) }}" class="inline-flex items-center p-2 rounded bg-blue-600 hover:bg-blue-700 text-white" aria-label="{{ __('Send message about this session') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                                <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                            </svg>
                        </a>
                        @endauth
                        {{-- owner-only actions moved above the map --}}
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
        @if ($location)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Attach confirm handler to forms with data-confirm to avoid inline JS
                    document.querySelectorAll('form[data-confirm]').forEach(function(form){
                        form.addEventListener('submit', function(e){
                            var msg = form.getAttribute('data-confirm') || '';
                            if(!confirm(msg)){
                                e.preventDefault();
                            }
                        });
                    });

                    var mapEl = document.getElementById('session-location-map');
                    if(!mapEl) return;

                    var lat = parseFloat(mapEl.getAttribute('data-lat'));
                    var lng = parseFloat(mapEl.getAttribute('data-lng'));
                    var name = mapEl.getAttribute('data-name') || '';

                    function escapeHtml(str) {
                        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                    }

                    var popupHtml = '<strong>' + escapeHtml(name) + '</strong>';
                    var coords = [lat, lng];

                    var map = L.map('session-location-map', { fullscreenControl: true }).setView(coords, 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);

                    var marker = L.marker(coords).addTo(map)
                        .bindPopup(popupHtml);
                    marker.openPopup();
                });
            </script>
        @endif
    @endpush
    
</x-app-layout>
