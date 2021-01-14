@extends("layout.master")

@section('title')
@php if (strpos(Request::url(), 'admin') !== false) {
echo _i("All sets");
} else {
echo _i("Sets of %s", Auth::user()->name);
}
@endphp
@endsection

@section('content')

<livewire:set.view />

@endsection
