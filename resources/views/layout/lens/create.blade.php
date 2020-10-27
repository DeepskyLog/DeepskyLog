@extends("layout.master")

@section('title')
@if ($update)
{{ $lens->name }}
@else
{{ _i("Add a new lens") }}
@endif
@endsection

@section('content')

<h4>
    @if ($update)
    {{ $lens->name }}
    @else
    {{ _i("Add a new lens") }}
    @endif
</h4>

<livewire:lens.create :lens="$lens" />

@endsection
