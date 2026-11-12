<div class="p-4 pb-2 w-full bg-gray-800 rounded-lg dsl-search-card">
    @php
        $heading = $title ?: ($q !== ''
            ? __('Search results for ":q"', ['q' => $q])
            : __('Search results'));
    @endphp
    <div class="mb-3 flex items-center gap-3">
        <h2 class="text-lg font-semibold text-gray-200">{{ $heading }}</h2>

        @if (! $fixedResultsMode)
        <div class="ml-auto flex items-center gap-2" x-data="{ open: false }" x-cloak>
            @auth
                <a href="{{ route('search.results', array_filter(['q' => $q, 'unseen' => $unseenByMe ? null : 1], fn ($v) => $v !== null && $v !== '')) }}"
                    class="inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md border transition focus:outline-none focus:ring-2 focus:ring-indigo-400 {{ $unseenByMe ? 'bg-indigo-600 border-indigo-500 text-white hover:bg-indigo-700' : 'border-gray-600 text-gray-200 hover:bg-gray-700' }}"
                    role="switch" aria-checked="{{ $unseenByMe ? 'true' : 'false' }}"
                    title="{{ __('Only show the objects you have not observed yet') }}">
                    <span class="inline-flex h-4 w-4 items-center justify-center rounded border {{ $unseenByMe ? 'border-white bg-white text-indigo-600' : 'border-gray-400' }}" aria-hidden="true">
                        @if ($unseenByMe)
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 111.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </span>
                    <span>{{ __('Not yet seen by me') }}</span>
                </a>
                @if ($canModifyActiveList)
                <form method="POST" action="{{ route('observing-list.active.batch-add') }}">
                    @csrf
                    <input type="hidden" name="search_query" value="{{ $q }}">
                    @if ($unseenByMe)
                        <input type="hidden" name="unseen" value="1">
                    @endif
                    <button type="submit"
                        class="inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md bg-green-700 text-white hover:bg-green-600 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-400 transition"
                        title="{{ __('Add all search results to your active observing list') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4 6h16M4 10h16M4 14h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 18h4M18 16v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <span>{{ __('Add all to active list') }}</span>
                    </button>
                </form>
                @endif
            @endauth
            @php
                $exportParams = [];
                if (($q ?? '') !== '') {
                    $exportParams['q'] = $q;
                }
                if (!empty($objectRefs ?? [])) {
                    $exportParams['object_refs'] = $objectRefs;
                }
                if ($unseenByMe) {
                    $exportParams['unseen'] = 1;
                }

                $exportNamesBase = route('search.names.pdf', $exportParams);
                $exportTableBase = route('search.table.pdf', $exportParams);
                $exportArgoBase = route('search.argo', $exportParams);
                $exportSkylistBase = route('search.skylist', $exportParams);
                $exportStxtBase = route('search.stxt', $exportParams);
                $exportApdBase = route('search.apd', $exportParams);
            @endphp

            @auth
                @include('object-sets.link', ['params' => ['source' => 'search'] + $exportParams])
                @include('object-report.link', ['params' => ['source' => 'search'] + $exportParams])
            @endauth

            <div class="relative inline-block text-left">
                <button type="button" @click="open = !open" @keydown.escape="open = false"
                    class="inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                    aria-haspopup="true" :aria-expanded="open.toString()">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>{{ __('Export') }}</span>
                </button>

                <div x-show="open" x-transition @click.outside="open = false"
                    class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                    style="display:none;">
                    <div class="py-1 text-sm text-gray-100" role="menu" aria-orientation="vertical">
                        <a href="{{ $exportNamesBase }}" target="_blank" rel="nofollow noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export names (PDF)') }}</a>
                        <a href="{{ $exportTableBase }}" target="_blank" rel="nofollow noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export table (PDF)') }}</a>
                        <a href="{{ $exportArgoBase }}" target="_blank" rel="nofollow noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export Argo Navis') }}</a>
                        <a href="{{ $exportSkylistBase }}" target="_blank" rel="nofollow noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export SkySafari (.skylist)') }}</a>
                        <a href="{{ $exportStxtBase }}" target="_blank" rel="nofollow noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export SkyTools (.txt)') }}</a>
                        <a href="{{ $exportApdBase }}" target="_blank" rel="nofollow noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export AstroPlanner (.apd)') }}</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="w-full">
        <livewire:search-results-table :q="$q" :object-refs="$objectRefs" :observation-search="$observationSearch" :unseen-by-me="$unseenByMe" />
    </div>
</div>

@push('scripts')
<script>
    (function(){
        let pollInterval = null;
        let retryCount = 0;
        const maxRetries = 10;
        let pollStartTime = null;
        const maxPollDuration = 300000; // 5 minutes max
        let hasPendingCalculations = false;

        document.addEventListener('hasPendingCalculationsUpdated', function(event) {
            const detail = event.detail || event;
            const newValue = detail.hasPending || detail[0]?.hasPending || detail.hasPending || false;
            hasPendingCalculations = newValue;
            checkAndPoll();
        });

        function getComponent() {
            try {
                const element = document.querySelector('[wire\\:id*="search-results-table"]') || document.querySelector('[wire\\:id]');
                if (!element) return null;
                const wireId = element.getAttribute('wire:id');
                if (!wireId) return null;
                return window.Livewire?.find(wireId);
            } catch (e) { return null; }
        }

        function checkAndPoll() {
            try {
                const component = getComponent();
                if (!component) {
                    if (retryCount < maxRetries) {
                        retryCount++;
                        setTimeout(checkAndPoll, 1000);
                    }
                    return;
                }
                retryCount = 0;

                if (pollStartTime && (Date.now() - pollStartTime) > maxPollDuration) {
                    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; pollStartTime = null; }
                    return;
                }

                if (hasPendingCalculations) {
                    if (!pollInterval) {
                        pollStartTime = Date.now();
                        pollInterval = setInterval(() => {
                            try {
                                if (window.Livewire) {
                                    Livewire.dispatch('pg:eventRefresh-search-results-table');
                                }
                            } catch (e) {
                                console.error('[SearchResults] Refresh error:', e);
                            }
                        }, 5000);
                    }
                } else {
                    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; pollStartTime = null; }
                }
            } catch (e) { console.error('[SearchResults] Poll check error:', e); }
        }

        setTimeout(() => { checkAndPoll(); }, 3000);

        if (window.Livewire) {
            Livewire.hook('commit', ({ component, succeed }) => {
                succeed(() => { setTimeout(checkAndPoll, 500); });
            });
        }

        window.addEventListener('beforeunload', () => { if (pollInterval) clearInterval(pollInterval); });
    })();
</script>
@endpush

