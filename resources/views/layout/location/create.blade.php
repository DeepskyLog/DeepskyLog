@extends("layout.master")

@section('title')
@if ($update)
{{ $location->name }}
@else
{{ _i("Add a new location") }}
@endif
@endsection

@section('content')

<h4>
    @if ($update)
    {{ $location->name }}
    @else
    {{ _i("Add a new location") }}
    @endif
</h4>
<div>
    <hr />
    <br />
    <form>
        {!! _i('Set your location on the map or by entering the name and pressing the %s button.',
        '<strong>"' . _i('Search location') . '"</strong>') !!}
        <br /><br />
        <div class="form-group mx-sm-3 mb-2">
            <input type="text" class="form-control" id="address" onkeypress="searchKeyPress(event);"
                placeholder="La Silla, Chile" autofocus></input>
            <input type="button" class="btn btn-primary" id="btnSearch" value="{{ _i('Search location') }}"
                onclick="codeAddress();"></input>
        </div>
    </form>

    <div id="map"></div>

    <br />

    <livewire:location.create :location="$location" />
</div>
@endsection

@push('scripts')

<script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key={{ env("GOOGLEMAPS_KEY") }}&v=3.exp&language=en&libraries=places">
</script>

<script type="text/javascript">
    var geocoder;
    var map;
    var infowindow;
    @if ($update)
        var getLocation = false;
        var loca = new google.maps.LatLng({{ $location->latitude }}, {{ $location->longitude  }});
    @else
        var getLocation = true;
        @if (old('latitude'))
            var loca = new google.maps.LatLng({{ old('latitude') }} , {{ old('longitude') }});
        @else
            var loca = new google.maps.LatLng(-29.2558, -70.7403);
        @endif
    @endif
    var myLocationMarker;
    var myLocations = [];

    function initialize() {
        geocoder = new google.maps.Geocoder();
        // Use current location, else use La Silla.
        if (navigator.geolocation && getLocation) {
            navigator.geolocation.getCurrentPosition(getPosition, errorCallBack);
        } else {
            map = new google.maps.Map(document.getElementById('map'), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
                center: loca,
                zoom: 15
            });
            myLocationMarker = new google.maps.Marker({
                map: map,
                position: loca,
                draggable: true
            });
            Livewire.emit('latitude', loca.lat());
            Livewire.emit('longitude', loca.lng());
            fillHiddenFields(loca);
            addLocations();
            google.maps.event.addListener(myLocationMarker, 'dragend', function(evt){
                Livewire.emit('latitude', evt.latLng.lat());
                Livewire.emit('longitude', evt.latLng.lng());
                fillHiddenFields(evt.latLng);
            });
        }
    }

    function errorCallBack(error) {
        @if ($update)
            var loca = new google.maps.LatLng({{ $location->latitude }}, {{ $location->longitude }});
        @else
            @if (old('latitude'))
                var loca = new google.maps.LatLng({{ old('latitude') }} , {{ old('longitude') }});
            @else
                var loca = new google.maps.LatLng(-29.2558, -70.7403);
            @endif
        @endif
        map = new google.maps.Map(document.getElementById('map'), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: loca,
            zoom: 15
        });
        myLocationMarker = new google.maps.Marker({
            map: map,
            position: loca,
            draggable: true
        });
        // Set the coordinates in the form
        Livewire.emit('latitude', loca.lat());
        Livewire.emit('longitude', loca.lng());
        fillHiddenFields(loca);
        addLocations();
        google.maps.event.addListener(myLocationMarker, 'dragend', function(evt){
            Livewire.emit('latitude', evt.latLng.lat());
            Livewire.emit('longitude', evt.latLng.lng());
            fillHiddenFields(evt.latLng);
        });
    }

    function getPosition(position) {
        loca = new google.maps.LatLng(
            position.coords.latitude, position.coords.longitude
        );
        Livewire.emit('latitude', position.coords.latitude);
        Livewire.emit('longitude', position.coords.longitude);
        fillHiddenFields(loca);

        map = new google.maps.Map(document.getElementById('map'), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: loca,
            zoom: 15
        });
        myLocationMarker = new google.maps.Marker({
            map: map,
            position: loca,
            draggable: true
        });

        addLocations();
    }

    function fillHiddenFields(latLng) {
        // Do reverse geocoding:
        geocoder.geocode({'latLng': latLng}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    arrAddress = results[0].address_components;
                    for (ac = 0; ac < arrAddress.length; ac++) {
                        if (arrAddress[ac].types[0] == "country") {
                            Livewire.emit('country', arrAddress[ac].short_name);
                        }
                    }
                }
            }
        });

        // Find the timezone
        url = 'https://maps.googleapis.com/maps/api/timezone/json?key={{ env("GOOGLEMAPS_KEY") }}&location='
            + latLng.lat() + ',' + latLng.lng() + '&timestamp='
            + new Date().getTime() / 1000;
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var myArr = JSON.parse(xmlhttp.responseText);
                Livewire.emit('timezone', myArr.timeZoneId);
            }
        }
        xmlhttp.open('GET', url, true);
        xmlhttp.send();

        // Find the elevation
        elevator = new google.maps.ElevationService();

        var locations = [];

        locations.push(latLng);

        // Create a LocationElevationRequest object using the array's one value
        var positionalRequest = {
            'locations': locations
        }

        elevator.getElevationForLocations(
            positionalRequest, function(results, status) {
                if (status == google.maps.ElevationStatus.OK) {
                    // Retrieve the first result
                    if (results[0]) {
                        Livewire.emit('elevation', results[0].elevation);
                    }
                }
            }
        );
    }

    google.maps.event.addDomListener(window, 'load', initialize);

    function searchKeyPress(e)
    {
        // look for window.event in case event isn't passed in
        e = e || window.event;
        if (e.keyCode == 13)
        {
            e.preventDefault();
            document.getElementById('btnSearch').click();
        }
    }

    function codeAddress() {
        var address = document.getElementById("address").value;
        geocoder.geocode( { 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                map.setCenter(results[0].geometry.location);
                Livewire.emit('latitude', results[0].geometry.location.lat());
                Livewire.emit('longitude', results[0].geometry.location.lng());
                fillHiddenFields(results[0].geometry.location);

                // Remove old marker
                myLocationMarker.setMap(null);
                myLocationMarker = new google.maps.Marker({
                    map: map,
                    position: results[0].geometry.location,
                    draggable: true
                });
                google.maps.event.addListener(myLocationMarker, 'dragend', function(evt){
                    Livewire.emit('latitude', evt.latLng.lat());
                    Livewire.emit('longitude', evt.latLng.lng());
                    fillHiddenFields(evt.latLng);
                });
            }
        });
    }

    function createMarker(place) {
        var placeLoc = place.geometry.location;
        var marker = new google.maps.Marker({
            map: map,
            position: place.geometry.location
        });

        google.maps.event.addListener(marker, 'mouseover', function() {
            infowindow.setContent(place.name);
            infowindow.open(map, this);
        });
    }

    function callback(results, status) {
        if (status == google.maps.places.PlacesServiceStatus.OK) {
            for (var i = 0; i < results.length; i++) {
                createMarker(results[i]);
            }
        }
    }

    function addLocations( ) {
        var image = '/images/telescope.png';

        @php
        // Let us add the existing locations to the map.
        foreach (auth()->user()->locations as $location) {
            echo 'var contentString = "<strong>' . $location->name . '</strong><br /><br />';
            $limmag = $location->limitingMagnitude;
            if ($limmag) {
                echo _i('Limiting magnitude') . ': ' . $limmag . '<br />';
            }
            $sb = $location->skyBackground;
            if ($sb) {
                echo 'SQM' . ': ' . $sb . '<br />';
            }
            $bortle = $location->bortle;
            if ($bortle) {
                echo 'Bortle' . ': ' . $bortle . '<br />';
            }
            if ($location->active) {
                echo _("Active");
            } else {
                echo _("Not active");
            }
            echo '";';
            echo 'var infowindow = new google.maps.InfoWindow({
                    content: contentString
                    });';

            echo 'newLocation = new google.maps.LatLng('
                . $location->latitude . ', '
                . $location->longitude . ');
                  marker = new google.maps.Marker({
                    position: newLocation,
                    icon: image,
                    map: map,
                    html: contentString,
                    title: "' .
                        $location->name . '"
                    });';

            echo "myLocations.push(marker);

            map.addListener('center_changed', function(){
                Livewire.emit('latitude', map.getCenter().lat());
                Livewire.emit('longitude', map.getCenter().lng());
                fillHiddenFields(map.getCenter());
            });

            google.maps.event.addListener(marker, 'mouseover', function() {
                    infowindow.setContent(this.html);
                    infowindow.open(map, this);
                });
            ";
        }
        @endphp
    }

</script>
@endpush
