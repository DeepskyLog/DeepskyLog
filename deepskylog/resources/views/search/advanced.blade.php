@extends('layouts.app')

@section('body_class', 'advanced-search')

@section('content')
    <div class="w-full mt-6 px-2">
        @livewire('advanced-object-search', ['filters' => $filters ?? []])
    </div>
@endsection
