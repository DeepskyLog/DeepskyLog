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
<livewire:lens.view />

@endsection
