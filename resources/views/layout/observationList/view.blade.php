@extends("layout.master")

@section('title')
@php if (strpos(Request::url(), 'admin') !== false) {
echo _i("All observation lists");
} else {
echo _i("Observation lists of %s", Auth::user()->name);
}
@endphp
@endsection

@section('content')
<livewire:observationlist.view />

@endsection
