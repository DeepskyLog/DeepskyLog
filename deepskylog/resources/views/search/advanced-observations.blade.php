@extends('layouts.app')

@section('body_class', 'advanced-observation-search')

@section('content')
    <div class="w-full mt-6 px-2">
        @livewire('advanced-observation-search', ['filters' => $filters ?? []])
    </div>
@endsection