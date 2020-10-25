@extends("layout.master")

@section('title')
{{ _i($target->target_name) }}
@endsection

@section('content')
<h4>
    {{ $target->target_name }}
</h4>

@include('layout.target.sub.detail')

@auth
@if (Auth::user()->stdlocation != 0)
@include('layout.target.sub.ephemerides')
@endif
@endauth

{{-- @if ($target->ra != null)
    @include('layout.target.sub.nearby')
@endif --}}

@endsection

@push('scripts')
{!! $dataTable->scripts() !!}

$(function () {
$('[data-toggle="tooltip"]').tooltip()
})

@endpush
