@extends("layout.master")

@section('title')
@php if (strpos(Request::url(), 'admin') !== false) {
echo _i("All filters");
} else {
echo _i("Filters of %s", Auth::user()->name);
}
@endphp
@endsection

@section('content')

<livewire:filter.view />

@endsection
