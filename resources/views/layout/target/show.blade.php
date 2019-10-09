@extends("layout.master")

@section('title')
    {{ _i('Object details') . ' - ' . _i($target->name) }}
@endsection

@section('content')
<h4>
    {{ _i($target->name) }}{{-- - {{ _i("Seen") }}: TODO: Add Seen information --}}
</h4>

@include('layout.target.sub.detail')

@auth
    @if (Auth::user()->stdlocation != 0)
        @include('layout.target.sub.ephemerides')
    @endif
@endauth

@endsection

@push('scripts')
<script>
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
</script>
@endpush
