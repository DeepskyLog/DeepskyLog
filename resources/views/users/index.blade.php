@extends("layout.master")

@section('title', _i('User Administration'))

@section('content')

<div class="col-lg-10 col-lg-offset-1">
    <h3>
        <i class="fa fa-users"></i> {{ _i('User Administration') }}
    </h3>
    <hr>

    {!! $dataTable->table(['class' => 'table table-sm table-striped table-hover']) !!}
</div>

@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
@endpush
