@extends("layout.master")

@section('title')
    @php if (strpos(Request::url(), 'admin') !== false) {
        echo _i("All lenses");
    } else {
        echo _i("Lenses of %s", Auth::user()->name);
    }
    @endphp
@endsection

@section('content')
	<h4>
        <!-- We have to check if admin is part of request, because we can not
            add a variable to the view, because we are using YarJa dataTables. -->
        @php if (strpos(Request::url(), 'admin') !== false) {
            echo _i("All lenses");
        } else {
            echo _i("Lenses of %s", Auth::user()->name);
        }
        @endphp
    </h4>
	<hr />
    <a class="btn btn-success float-right" href="/lens/create">
        {{ _i("Add lens") }}
    </a>
    <br /><br />

    {!! $dataTable->table() !!}

@endsection

@push('scripts')

{!! $dataTable->scripts() !!}

@endpush
