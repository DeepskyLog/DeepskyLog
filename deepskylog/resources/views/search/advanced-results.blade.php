@extends('layouts.app')

@section('body_class', 'advanced-search-results')

@section('content')
    <div class="w-full mt-6 px-2">
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
            </div>

            <div class="w-full">
                @livewire('advanced-object-search-table', ['filters' => $filters])
            </div>
        </div>
    </div>
@endsection
