@extends("layout.master")

@section('title')
@if ($update)
{{ $instrument->name }}
@else
{{ _i("Add a new instrument") }}
@endif
@endsection

@section('content')

<h4>
    @if ($update)
    {{ $instrument->name }}
    @else
    {{ _i("Add a new instrument") }}
    @endif
</h4>

<livewire:instrument.create :instrument="$instrument" />

@endsection
