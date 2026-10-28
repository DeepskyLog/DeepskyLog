@extends('layouts.app')

@section('body_class', 'advanced-search-results')

@section('content')
    <div class="w-full mt-6 px-2">
        @php
            $canModifyActiveList = false;
            if (auth()->check()) {
                try {
                    $user = auth()->user();
                    $activeList = app(\App\Services\ActiveObservingListService::class)->getActiveList($user);
                    $canModifyActiveList = !$activeList || $user->can('addItem', $activeList);
                } catch (\Throwable $_) {
                    $canModifyActiveList = false;
                }
            }
        @endphp
        <div class="p-4 pb-2 w-full bg-gray-800 rounded-lg dsl-search-card">
            @php
                $exportParams = collect($filters ?? [])->reject(function ($value) {
                    if (is_array($value)) {
                        return collect($value)
                            ->flatten()
                            ->every(fn($item) => trim((string) $item) === '');
                    }

                    return $value === null || trim((string) $value) === '';
                })->all();

                $exportNamesBase = route('search.names.pdf', $exportParams);
                $exportTableBase = route('search.table.pdf', $exportParams);
                $exportArgoBase = route('search.argo', $exportParams);
                $exportSkylistBase = route('search.skylist', $exportParams);
                $exportStxtBase = route('search.stxt', $exportParams);
                $exportApdBase = route('search.apd', $exportParams);
            @endphp
            <div class="mb-3 flex items-center gap-3 flex-wrap">
                <h2 class="text-lg font-semibold text-gray-200">{{ __('Advanced search results') }}</h2>
                <a href="{{ route('search.advanced', $filters ?? []) }}"
                   class="ml-2 inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('Refine search') }}
                </a>
                <div class="ml-auto flex items-center gap-2" x-data="{ open: false }" x-cloak>
                    @auth
                        @if ($canModifyActiveList)
                        <button type="button"
                            onclick="if(window.Livewire && typeof Livewire.dispatchTo==='function'){ Livewire.dispatchTo('advanced-object-search-table', 'advanced-add-all-to-active-list'); }"
                            class="inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md bg-green-700 text-white hover:bg-green-600 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-400 transition"
                            title="{{ __('Add all visible search results to your active observing list') }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4 6h16M4 10h16M4 14h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 18h4M18 16v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            {{ __('Add all to active list') }}
                        </button>
                        @endif
                    @endauth

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
            </div>

            <div class="w-full">
                @livewire('advanced-object-search-table', ['filters' => $filters, 'showAddColumn' => $canModifyActiveList])
            </div>
        </div>
    </div>
@endsection
