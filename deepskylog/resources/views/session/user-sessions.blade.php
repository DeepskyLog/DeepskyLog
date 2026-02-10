<x-app-layout>
    <div class="pb-6">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="bg-gray-900 shadow-sm sm:rounded-lg p-6">
                {{-- Page header inside the dark container --}}
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-semibold text-xl text-gray-200 leading-tight">{{ $u->name ?? $userSlug }} - {{ __('Sessions') }}</h2>
                    @php
                        $viewer = $user ?? null;
                        $pageOwner = $u ?? null;
                        $ownerMatch = false;
                        if ($viewer) {
                            if ($pageOwner && isset($pageOwner->id) && $viewer->id === $pageOwner->id) {
                                $ownerMatch = true;
                            } elseif ($pageOwner && isset($pageOwner->slug) && ($viewer->slug ?? $viewer->username) === $pageOwner->slug) {
                                $ownerMatch = true;
                            } elseif (($viewer->username ?? null) === ($pageOwner->username ?? $userSlug)) {
                                $ownerMatch = true;
                            }
                        }
                    @endphp
                    @if($ownerMatch)
                        <div>
                            <a href="{{ route('session.create') }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('Add session') }}</a>
                        </div>
                    @endif
                </div>
                {{-- preview_text and preview are prepared in the controller; views stay simple --}}

                @if(isset($inactiveSessions) && $inactiveSessions->isNotEmpty())
                    <div class="mb-6">
                        <div class="bg-gray-800 p-4 rounded">
                            <h3 class="text-lg font-semibold mb-3 text-gray-100">{{ __('Draft sessions') }}</h3>
                            <div class="space-y-3">
                                @foreach($inactiveSessions as $s)
                                    @php $observerSlug = optional($s->observer)->slug ?? $s->observerid; @endphp
                                    @php
                                        $viewer = auth()->user();
                                        $allowAdmin = config('sessions.allow_admin_override', false);
                                        $viewerIsOwner = $viewer && ($viewer->username === ($u->username ?? $u->slug ?? null));
                                        $viewerIsAdmin = $viewer && method_exists($viewer, 'hasAdministratorPrivileges') && $viewer->hasAdministratorPrivileges();
                                        $showDraftActions = $viewerIsOwner || ($allowAdmin && $viewerIsAdmin);
                                    @endphp

                                    <div class="bg-gray-700 p-3 rounded flex items-center justify-between">
                                        <div>
                                            <div class="font-semibold text-white">{{ html_entity_decode($s->name ?? __('Session :id', ['id' => $s->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</div>
                                            @if(! empty($s->otherObserversDisplay))
                                                <div class="text-sm text-gray-300">{{ __('Observers') }}: {{ $s->otherObserversDisplay }}</div>
                                            @endif
                                            <div class="text-sm text-gray-400">{{ $s->begindate ? \Carbon\Carbon::parse($s->begindate)->translatedFormat('j M Y') : __('Unknown') }} &ndash; {{ $s->enddate ? \Carbon\Carbon::parse($s->enddate)->translatedFormat('j M Y') : __('Unknown') }}</div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if($showDraftActions)
                                                @if($viewerIsAdmin && ! $viewerIsOwner && $allowAdmin)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-600 text-white rounded" title="{{ __('Administrator override enabled: actions performed will be executed as an administrator on behalf of the owner') }}">{{ __('Admin override') }}</span>
                                                @endif
                                                <a href="{{ route('session.adapt', $s->id) }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-500">{{ __('Use') }}</a>
                                                <form method="POST" action="{{ route('session.destroy', $s->id) }}" onsubmit="return confirm('{{ $viewerIsAdmin && ! $viewerIsOwner && $allowAdmin ? __('You are performing this action as an administrator on behalf of the owner. Are you sure you want to delete this draft session?') : __('Are you sure you want to delete this draft session?') }}');">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-500">{{ __('Delete') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if(! empty($inactiveMore))
                                <div class="mt-3">
                                    <a href="{{ route('session.user', [$u->slug ?? $userSlug]) }}?show_drafts=1" class="text-sm text-blue-400 hover:underline">{{ __('Show more') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                    @foreach($sessions as $session)
                        @php
                            // Ensure we safely compute URL parts for session.show links. Some legacy/malformed
                            // session records might lack a slug or id; guard against generating route() with
                            // missing parameters which throws a UrlGenerationException.
                            $sessionUser = trim(optional($session->observer)->slug ?: '') ?: ($session->observerid ?? null);
                            $sessionParam = trim($session->slug ?: '') ?: ($session->id ?? null);
                        @endphp
                        <article class="bg-gray-800 p-4 rounded">
                                    @if(! empty($session->preview))
                                <div class="mb-3">
                                    @if(! empty($sessionParam) && ! empty($sessionUser))
                                        <a href="{{ route('session.show', [$sessionUser, $sessionParam]) }}">
                                            <img src="{{ $session->preview }}" alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}" class="w-full h-40 object-cover rounded" />
                                        </a>
                                    @else
                                        <img src="{{ $session->preview }}" alt="{{ html_entity_decode($session->name ?? __('Session'), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}" class="w-full h-40 object-cover rounded" />
                                    @endif
                                </div>
                            @endif

                            <h3 class="text-lg font-bold text-white mb-2">
                                @if(! empty($sessionParam) && ! empty($sessionUser))
                                    <a href="{{ route('session.show', [$sessionUser, $sessionParam]) }}" class="hover:underline">{{ html_entity_decode($session->name ?? __('Session :id', ['id' => $session->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                @else
                                    <span class="hover:underline">{{ html_entity_decode($session->name ?? __('Session :id', ['id' => $session->id]), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</span>
                                @endif
                            </h3>

                            <div class="text-sm text-gray-400 mb-3">
                                <span>{{ $session->begindate ? \Carbon\Carbon::parse($session->begindate)->translatedFormat('j M Y') : __('Unknown') }}</span>
                                <span class="mx-2">&ndash;</span>
                                <span>{{ $session->enddate ? \Carbon\Carbon::parse($session->enddate)->translatedFormat('j M Y') : __('Unknown') }}</span>
                            </div>

                            <div class="text-sm text-gray-400 mb-2">
                                <span class="mr-2 font-medium text-gray-200">{{ __('Location') }}:</span>
                                <span class="text-gray-400">{{ $session->location_name ?? __('Unknown') }}</span>
                            </div>

                            @if(isset($session->observation_count))
                                <div class="text-sm text-gray-300 mb-2">{{ __('Observations') }}: <strong class="text-white">{{ $session->observation_count }}</strong></div>
                            @endif

                            <p class="text-sm text-gray-300 mb-3">{{ $session->preview_text ?? Str::limit(strip_tags(html_entity_decode($session->comments ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')), 180) }}</p>
                            <div class="flex items-center justify-between text-sm">
                                <div class="text-gray-400">{{ __('Observers') }}: {{ $session->otherObserversCount() ?? 1 }}</div>
                                @if(! empty($sessionParam) && ! empty($sessionUser))
                                    <a href="{{ route('session.show', [$sessionUser, $sessionParam]) }}" class="text-blue-500 hover:underline">{{ __('Read more') }}</a>
                                @else
                                    <span class="text-gray-500">{{ __('Read more') }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $sessions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
