@extends("layout.master")

@section('title')
{{ $location->name }}
@endsection

@section('content')
<livewire:location.show :location="$location" :media="$media" />


<div id="map"></div>
@auth
@if (Auth::user()->id == $location->user_id || Auth::user()->isAdmin())
<br />
<a href="{{ route('location.edit', $location) }}">
    <button type="button" class="btn btn-sm btn-primary">
        {{ _i('Edit') }} {{ $location->name }}
    </button>
</a>
@endif
@endauth
@endsection

@push('scripts')

<script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key={{ env("GOOGLEMAPS_KEY") }}&v=3.exp&language=en&libraries=places">

</script>

<script type="text/javascript">
    var map;
    var loca = new google.maps.LatLng({{ $location->latitude }}, {{ $location->longitude }});
    var myLocationMarker;
    var myLocations = [];

    function initialize() {
        map = new google.maps.Map(document.getElementById('map'), {
        mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: loca,
            zoom: 15
        });
        myLocationMarker = new google.maps.Marker({
            map: map,
            position: loca,
            draggable: false
        });
    }

    google.maps.event.addDomListener(window, 'load', initialize);

</script>

@endpush
