@extends("layout.master")

@section('title')
@if ($update)
{{ $filter->name }}
@else
{{ _i("Add a new filter") }}
@endif
@endsection

@section('content')

<h4>
    @if ($update)
    {{ $filter->name }}
    @else
    {{ _i("Add a new filter") }}
    @endif
</h4>


<livewire:filter.create :filter="$filter" />

@endsection
