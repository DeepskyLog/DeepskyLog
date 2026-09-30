<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Search observations') }}
            @if(!empty($q))
                <span class="text-gray-400 text-base font-normal ml-2">— {{ $q }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-4 w-full">
        {{-- Search form --}}
        <div class="mb-4 bg-gray-900 p-4 rounded shadow">
            <form method="GET" action="{{ route('observations.search') }}" class="flex flex-wrap gap-2">
                <input type="text"
                       name="q"
                       value="{{ $q }}"
                       placeholder="{{ __('Enter object name (e.g. M 42, NGC 7000, %)') }}"
                       class="min-w-0 flex-1 basis-40 rounded bg-gray-800 border border-gray-700 px-3 py-2 text-gray-100 focus:outline-none focus:ring" />
                <button type="submit"
                        class="bg-blue-700 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                    {{ __('Search') }}
                </button>
                @if(!empty($q))
                    <a href="{{ route('observations.search') }}"
                       class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">
                        {{ __('Clear') }}
                    </a>
                @endif
            </form>
        </div>

        {{-- Results table --}}
        <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4"
             x-data="{
                 compareMode: false,
                 toggleCompare() {
                     this.compareMode = !this.compareMode;
                     if (this.compareMode) {
                         window.dispatchEvent(new CustomEvent('pg-toggle-detail-observation-search-table-hidden-all'));

                         requestAnimationFrame(() => {
                             document.querySelectorAll('td[x-data]').forEach(td => {
                                 const xd = td.getAttribute('x-data');
                                 if (xd && xd.includes('pg-toggle-detail-observation-search-table-')) {
                                     const toggle = td.querySelector('div.cursor-pointer');
                                     if (toggle instanceof HTMLElement) {
                                         toggle.click();
                                     }
                                 }
                             });
                         });
                     } else {
                         // Collapse all — Livewire event reaches #[On('...hidden-all')],
                         // DOM event reaches the Alpine x-on listener in the toggle TD
                         Livewire.dispatch('pg-toggle-detail-observation-search-table-hidden-all');
                         window.dispatchEvent(new CustomEvent('pg-toggle-detail-observation-search-table-hidden-all'));
                     }
                 }
             }">
            @php
                $exportPdfUrl = route('observations.search.pdf', array_filter([
                    'q' => $q !== '' ? $q : null,
                ]));
                $exportSkylistUrl = route('observations.search.skylist', array_filter([
                    'q' => $q !== '' ? $q : null,
                ]));
                $exportObsUrl = route('observations.search.obs', array_filter([
                    'q' => $q !== '' ? $q : null,
                ]));
                $exportOalUrl = route('observations.search.oal', array_filter([
                    'q' => $q !== '' ? $q : null,
                ]));
                $extractObjectsUrl = route('observations.search.objects', array_filter([
                    'q' => $q !== '' ? $q : null,
                ]));
            @endphp
            <div class="flex flex-wrap justify-end gap-2 mb-3">
                <a href="{{ $extractObjectsUrl }}"
                   class="px-4 py-1.5 rounded text-sm font-medium bg-indigo-700 hover:bg-indigo-600 text-white transition-colors">
                    {{ __('Extract objects') }}
                </a>
                <x-menu.dropdown :width="72">
                    <x-slot name="trigger">
                        <button type="button"
                                x-ref="trigger"
                                @click="open = !open"
                                aria-haspopup="true"
                                :aria-expanded="open.toString()"
                                class="inline-flex items-center gap-2 rounded-md border border-indigo-400/50 bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white shadow-[0_0_0_1px_rgba(255,255,255,0.08)] transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ __('Export') }}</span>
                        </button>
                    </x-slot>

                    <x-menu.item href="{{ $exportPdfUrl }}" target="_blank" rel="nofollow noopener noreferrer" data-first>
                        {{ __('Export PDF') }}
                    </x-menu.item>
                    <x-menu.item href="{{ $exportSkylistUrl }}" target="_blank" rel="nofollow noopener noreferrer">
                        {{ __('Export SkySafari') }}
                    </x-menu.item>
                    <x-menu.item href="{{ $exportObsUrl }}" target="_blank" rel="nofollow noopener noreferrer">
                        {{ __('Export AstroPlanner (.obs)') }}
                    </x-menu.item>
                    <x-menu.item href="{{ $exportOalUrl }}" target="_blank" rel="nofollow noopener noreferrer" data-last>
                        {{ __('Export OAL') }}
                    </x-menu.item>
                </x-menu.dropdown>
                <button type="button"
                        @click="toggleCompare()"
                        :class="compareMode
                            ? 'bg-amber-600 hover:bg-amber-500 text-white'
                            : 'bg-indigo-600 hover:bg-indigo-500 text-white'"
                        class="px-4 py-1.5 rounded text-sm font-medium transition-colors">
                    <span x-text="compareMode ? '{{ __('Collapse all') }}' : '{{ __('Compare') }}'"></span>
                </button>
            </div>
            @livewire('observation-search-table', ['objectName' => $q, 'filters' => []], key('observation-search-table-search-page'))
        </div>
    </div>

    {{-- Drawing lightbox modal --}}
    <div id="obs-drawing-modal"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80"
         onclick="if(event.target===this){this.classList.add('hidden');document.getElementById('obs-drawing-img').src='';}">
        <div class="relative max-w-3xl w-full mx-4">
            <button type="button"
                    onclick="document.getElementById('obs-drawing-modal').classList.add('hidden');document.getElementById('obs-drawing-img').src='';"
                    class="absolute -top-8 right-0 text-white text-2xl leading-none hover:text-gray-300 focus:outline-none"
                    aria-label="{{ __('Close') }}">&#x2715;</button>
            <img id="obs-drawing-img" src="" alt="{{ __('Drawing') }}"
                 class="max-h-[80vh] w-auto mx-auto rounded shadow-lg" />
        </div>
    </div>
</x-app-layout>
