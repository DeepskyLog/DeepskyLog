@extends("layout.master")

@section('title')
@php if (strpos(Request::url(), 'admin') !== false) {
echo _i("All locations");
} else {
echo _i("Locations of %s", Auth::user()->name);
}
@endphp
@endsection

@section('content')

<livewire:location.view />

@endsection
