<div class="p-4 pb-2 w-full bg-gray-800 rounded-lg dsl-search-card">
    <div class="mb-3 flex items-center gap-3">
        <h2 class="text-lg font-semibold text-gray-200">Search results for "{{ $q }}"</h2>

        <div class="ml-auto flex items-center gap-2" x-data="{ open: false }" x-cloak>
            @auth
                @if ($canModifyActiveList)
                <form method="POST" action="{{ route('observing-list.active.batch-add') }}">
                    @csrf
                    <input type="hidden" name="search_query" value="{{ $q }}">
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
                $exportNamesBase = route('search.names.pdf') . '?q=' . rawurlencode($q ?? '');
                $exportTableBase = route('search.table.pdf') . '?q=' . rawurlencode($q ?? '');
                $exportArgoBase = route('search.argo') . '?q=' . rawurlencode($q ?? '');
                $exportSkylistBase = route('search.skylist') . '?q=' . rawurlencode($q ?? '');
                $exportStxtBase = route('search.stxt') . '?q=' . rawurlencode($q ?? '');
                $exportApdBase = route('search.apd') . '?q=' . rawurlencode($q ?? '');
            @endphp

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
                        <a href="{{ $exportNamesBase }}" target="_blank" rel="noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export names (PDF)') }}</a>
                        <a href="{{ $exportTableBase }}" target="_blank" rel="noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export table (PDF)') }}</a>
                        <a href="{{ $exportArgoBase }}" target="_blank" rel="noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export Argo Navis') }}</a>
                        <a href="{{ $exportSkylistBase }}" target="_blank" rel="noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export SkySafari (.skylist)') }}</a>
                        <a href="{{ $exportStxtBase }}" target="_blank" rel="noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export SkyTools (.txt)') }}</a>
                        <a href="{{ $exportApdBase }}" target="_blank" rel="noopener noreferrer"
                            class="block px-4 py-2 hover:bg-gray-700" role="menuitem">{{ __('Export AstroPlanner (.apd)') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full">
        <livewire:search-results-table :q="$q" />
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

