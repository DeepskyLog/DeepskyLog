@extends("layout.master")

@section('title')
@if ($update)
{{ $eyepiece->name }}
@else
{{ _i("Add a new eyepiece") }}
@endif
@endsection

@section('content')

<h4>
    @if ($update)
    {{ $eyepiece->name }}
    @else
    {{ _i("Add a new eyepiece") }}
    @endif
</h4>

<livewire:eyepiece.create :eyepiece="$eyepiece" />

@endsection
