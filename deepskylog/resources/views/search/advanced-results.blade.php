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
            <div class="mb-3 flex items-center gap-3 flex-wrap">
                <h2 class="text-lg font-semibold text-gray-200">{{ __('Advanced search results') }}</h2>
                <a href="{{ route('search.advanced', $filters ?? []) }}"
                   class="ml-2 inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('Refine search') }}
                </a>
                @auth
                    @if ($canModifyActiveList)
                    <button type="button"
                        onclick="if(window.Livewire && typeof Livewire.dispatchTo==='function'){ Livewire.dispatchTo('advanced-object-search-table', 'advanced-add-all-to-active-list'); }"
                        class="ml-auto inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md bg-green-700 text-white hover:bg-green-600 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-400 transition"
                        title="{{ __('Add all visible search results to your active observing list') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4 6h16M4 10h16M4 14h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 18h4M18 16v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        {{ __('Add all to active list') }}
                    </button>
                    @endif
                @endauth
            </div>

            <div class="w-full">
                @livewire('advanced-object-search-table', ['filters' => $filters, 'showAddColumn' => $canModifyActiveList])
            </div>
        </div>
    </div>
@endsection
