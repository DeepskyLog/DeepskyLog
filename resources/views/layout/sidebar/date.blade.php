{{-- @php
// Current date
$datetime = new \Carbon\Carbon();
$date = $datetime->format('d/m/Y');

if (Session::has('date')) {
$date = session('date');
} else {
Session::put('date', $date);
}
@endphp --}}
{{-- <input class="form-control" type="text" value="{{ $date }}" id="datepicker" size="10"> --}}

<livewire:sidebar.date />
