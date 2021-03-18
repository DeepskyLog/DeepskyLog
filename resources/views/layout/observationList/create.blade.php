@extends("layout.master")

@section('title')
@if ($update)
{{ $observationList->name }}
@else
{{ _i("Add a new observation list") }}
@endif
@endsection

@section('content')

<h4>
    @if ($update)
    {{ $observationList->name }}
    @else
    {{ _i("Add a new observation list") }}
    @endif
</h4>

<livewire:observationlist.create :observationList="$observationList" />

@endsection
