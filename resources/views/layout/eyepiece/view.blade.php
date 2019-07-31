@extends("layout.master")

@section('title')
    @php if (strpos(Request::url(), 'admin') !== false) {
        echo _i("All eyepieces");
    } else {
        echo _i("Eyepieces of %s", Auth::user()->name);
    }
    @endphp
@endsection

@section('content')
	<h4>
        <!-- We have to check if admin is part of request, because we can not
            add a variable to the view, because we are using YarJa dataTables. -->
        @php if (strpos(Request::url(), 'admin') !== false) {
            echo _i("All eyepieces");
        } else {
            echo _i("Eyepieces of %s", Auth::user()->name);
        }
        @endphp
    </h4>
	<hr />
    <a class="btn btn-success float-right" href="/eyepiece/create">
        {{ _i("Add eyepiece") }}
    </a>
    <br /><br />

    {!! $dataTable->table(['class' => 'table table-sm table-striped table-hover']) !!}

@endsection

@push('scripts')

{!! $dataTable->scripts() !!}

@endpush
