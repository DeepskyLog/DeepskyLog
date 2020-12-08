@extends("layout.master")

@section('title')
@php if (strpos(Request::url(), 'admin') !== false) {
echo _i("All instruments");
} else {
echo _i("Instruments of %s", Auth::user()->name);
}
@endphp
@endsection

@section('content')

<livewire:instrument.view />

@endsection
