@extends('layouts.app')

@section('body_class', 'search-results')

{{-- Do not request deep-sky full container (removes large top/bottom padding) --}}

@section('content')
    <div class="w-full mt-6 px-2">
        @livewire('search-results', ['q' => $q])
    </div>
@endsection
