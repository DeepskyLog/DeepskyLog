@extends("layout.master")

@section('title')
    {{ _i('Object details') . ' - ' . _i($target->target_name) }}
@endsection

@section('content')
<h4>
{{ $target->target_name }}
</h4>

@auth
    @if (auth()->user()->stdtelescope)
        {{ _i('Information about') . ' ' . $target->target_name . ' ' . _i('with') }}
        <form role="form" action="/users/{{ Auth::id() }}/settings" method="POST">
            @csrf
            @method('PATCH')

            <select class="form-control selection" name="stdinstrument" id="defaultInstrument">
                {!! App\Models\Instrument::getInstrumentOptions() !!}
            </select>
        </form>
        {{ _i('at') }}
        <form role="form" action="/users/{{ Auth::id() }}/settings" method="POST">
            @csrf
            @method('PATCH')

            <select class="form-control selection" name="stdlocation" id="defaultLocation">
                {!! App\Models\Location::getLocationOptions() !!}
            </select>
        </form>
        <br />
    @endif
@endauth

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
$('#defaultInstrument2').change(function() {
    this.form.submit();
});

$('#defaultLens').change(function() {
    this.form.submit();
});

$('#defaultEyepiece').change(function() {
    this.form.submit();
});

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
</script>

@endpush
