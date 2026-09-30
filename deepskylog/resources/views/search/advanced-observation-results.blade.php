@extends('layouts.app')

@section('body_class', 'advanced-observation-search-results')

@section('content')
    <div class="w-full mt-6 px-2">
        <div class="p-4 pb-2 w-full bg-gray-800 rounded-lg dsl-search-card"
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
                         Livewire.dispatch('pg-toggle-detail-observation-search-table-hidden-all');
                         window.dispatchEvent(new CustomEvent('pg-toggle-detail-observation-search-table-hidden-all'));
                     }
                 }
             }">
            @php
                $exportParams = collect($filters ?? [])->reject(function ($value) {
                    if (is_array($value)) {
                        return collect($value)
                            ->flatten()
                            ->every(fn($item) => trim((string) $item) === '');
                    }

                    return $value === null || trim((string) $value) === '';
                })->all();

                $exportPdfUrl = route('observations.search.pdf', $exportParams);
                $exportSkylistUrl = route('observations.search.skylist', $exportParams);
                $exportObsUrl = route('observations.search.obs', $exportParams);
                $exportOalUrl = route('observations.search.oal', $exportParams);
                $extractObjectsUrl = route('observations.search.objects', $exportParams);
            @endphp
            <div class="mb-3 flex items-center gap-3 flex-wrap">
                <h2 class="text-lg font-semibold text-gray-200">{{ __('Advanced observation search results') }}</h2>
                <a href="{{ route('search.observations.advanced', $filters ?? []) }}"
                   class="ml-2 inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('Refine search') }}
                </a>
                <div class="ml-auto flex items-center gap-3">
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
            </div>

            <div class="w-full">
                @livewire('observation-search-table', ['filters' => $filters ?? []], key('advanced-observation-search-table'))
            </div>
        </div>
    </div>
@endsection