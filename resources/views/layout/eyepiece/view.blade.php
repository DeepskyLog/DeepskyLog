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

<livewire:eyepiece.view />

@endsection
