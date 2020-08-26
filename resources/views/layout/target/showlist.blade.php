@extends("layout.master")

@section('title')
    Overzicht geselecteerde objecten
@endsection

@section('content')
<h4>
    Overzicht geselecteerde objecten
</h4>

<br /><hr />
{!! $dataTable->table(['class' => 'table table-sm table-striped table-hover']) !!}
<hr />
@endsection

@push('scripts')
{!! $dataTable->scripts() !!}
@endpush
