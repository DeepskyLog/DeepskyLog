@extends("layout.master")

@section('title')
    {{ _i('Object details') . ' - ' . _i($target->name) }}
@endsection

@section('content')
<h4>
    {{ _i($target->name) }}
</h4>

@include('layout.target.sub.detail')

@auth
    @if (Auth::user()->stdlocation != 0)
        @include('layout.target.sub.ephemerides')
    @endif
@endauth

@if ($target->ra != null)
    @include('layout.target.sub.nearby')
@endif

@endsection

@push('scripts')
{!! $dataTable->scripts() !!}

<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
</script>
@endpush
