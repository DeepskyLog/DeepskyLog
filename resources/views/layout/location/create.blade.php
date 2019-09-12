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

    @csrf
    <div>
        <hr />
        <input type="submit" class="btn btn-success float-right" name="add" value="@if ($update){{ _i("Change location") }}@else{{ _i("Add location") }}@endif">
        <br />
        <form>
            <ol>
                <li>
                    {!! _i('Set your location on the map or by entering the name and pressing the %s button.',
                        '<strong>"' . _i('Search location') . '"</strong>') !!}
                </li>
                <div class="form-inline">
                    <input type="text" class="form-control" id="address" onkeypress="searchKeyPress(event);" placeholder="La Silla, Chile" autofocus></input>
                    &nbsp;
                    <input type="button" class="btn btn-primary" id="btnSearch" value="{{ _i('Search location') }}" onclick="codeAddress();" ></input>
                </div>
            </form>
            <div id="map"></div>

            <br />

            @if ($update)
                <form role="form" action="/location/{{ $location->id }}" method="POST">
                @method('PATCH')
            @else
                <form role="form" action="/location" method="POST">
            @endif

            <div>
        <input type="hidden" name="latitude" id="latitude" />
        <input type="hidden" name="longitude" id="longitude" />
        <input type="hidden" name="country" id="country" />
        <input type="hidden" name="elevation" id="elevation" />
        <input type="hidden" name="timezone" id="timezone" />
        <li>
            {!! _i("Define your own name for this location, eventually add a naked eye limiting magnitude (or SQM value) and press the %s button.",
            '<strong>"' . _i("Add site") . '"</strong>') !!}

            <br />
            <br />
        </li>

        <div class="form-inline">
            <input type="text" required placeholder="{{ _i('Location name') }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" maxlength="64" name="name" size="30" value="@if ($location->name){{ $location->name }}@else{{ old('name') }}@endif" />

        </div>

        <br />

        <table class='table'>
            <tr>
                <th>{{ _i("Typical naked eye limiting magnitude") }}</th>
                <th>{{ _i("Sky Quality Meter (SQM) value") }}</th>
                <th>{{ _i("Bortle Scale") }}</th>
                <th>
                    <a class='btn btn-primary' href='#' role='button' id='lightpollutioninfo'>
                        {{ _i("Use value from lightpollutionmap.info") }}
                    </a>
                </th>
            </tr>
            <tr>
                <td>
                    <div class="form-inline">
                        <input type="number" min="0" max="8.0" step="0.1" class="form-control" maxlength="5" id="lm" name="lm" size="5" />
                    </div>
                </td>

                <td>
                    <div class="form-inline">
                        <input type="number" min="10.0" max="25.0" step="0.01" class="form-control" maxlength="5" id="sqm" name="sb" size="5" />
                    </div>
                </td>

                <td>
                    <div class="form-inline">
                        <select id="bortle" name="bortle">
                            <option></option>
                            <option value="1">1 - {{_i("Excellent dark-sky site")}}</option>
                            <option value="2">2 - {{_i("Typical truly dark site")}}</option>
                            <option value="3">3 - {{_i("Rural sky")}}</option>
                            <option value="4">4 - {{_i("Rural/suburban transition")}}</option>
                            <option value="5">5 - {{_i("Suburban sky")}}</option>
                            <option value="6">6 - {{_i("Bright suburban sky")}}</option>
                            <option value="7">7 - {{_i("Suburban/urban transition")}}</option>
                            <option value="8">8 - {{_i("City sky")}}</option>
                            <option value="9">9 - {{_i("Inner-city sky")}}</option>
                        </select>
                    </div>
                </td>
                <td>
                </td>
            </table>
        </ol>

        <input type="submit" class="btn btn-success" name="add" value="@if ($update){{ _i("Change location") }}@else{{ _i("Add location") }}@endif" />

    </form>
    <br />
    <br />


        echo '<script src="' . $baseURL
            . 'lib/javascript/sqm.js" type="text/javascript"></script>';
        echo '<script type="text/javascript">
            var bortleChange = 1;
            $("#lm").keyup(function(event) {
                lm = event.target.value;
                if (lm < 0) {
                    lm = 0.0;
                    $("#lm").val(lm);
                }
                sqm = lmToSqm(lm);
                $("#sqm").val(sqm);

                bortleChange = 0;
                $("#bortle").val(sqmToBortle(sqm)).change();
            });

            // Javascript to convert from sqm to limiting magnitude and bortle
            $("#sqm").on("keyup change", function(event) {
                sqm = event.target.value;

                if (sqm > 22.0) {
                    sqm = 22.0;
                    $("#sqm").val(22.0);
                }

                lm = sqmToLm(sqm);
                $("#lm").val(lm);

                bortleChange = 0;
                $("#bortle").val(sqmToBortle(sqm)).change();
            });

            // Javascript to convert from bortle to limiting magnitude and sqm
            $(document).ready(function() {
                $("#bortle").change(function(){
                    bortle = $(this).find("option:selected").attr("value");

                    if (bortleChange == 1) {
                        $("#lm").val(bortleToLm(bortle));
                        $("#sqm").val(bortleToSqm(bortle));
                    } else {
                        bortleChange = 1;
                    }
                });
                // Javascript to fill out the limiting magnitude,
                // SQM and Bortle automatically from lightpollutioninfo.info
                $("#lightpollutioninfo").on("click", function(event) {
                    // Prevent following the link
                    event.preventDefault();

                    // Get the value from the lightpollution.info site
                    // We use yql. This will work cross-domain and will return json.
                    url = "https://www.lightpollutionmap.info/"
                        + "QueryRaster/?ql=wa_2015&qt=point&qd="
                        + $("#longitude").val() + ","
                        + $("#latitude").val() + "&key=6hDh3zLAIhFXdpaX";
                    var yql = "https://query.yahooapis.com/v1/public/yql?q="
                        + encodeURIComponent(
                            "select * from htmlstring where url=\"" + url
                            + "\" and xpath=\"//body\""
                        ) + "&format=json"
                    + "&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

                    $.getJSON(yql,function(data){
                        data = data.query.results.result;
                        // Remove the html tags and convert to a number
                        lpNumber = Number(data.replace(/<\/?[^>]+(>|$)/g, ""));
                        // We need to add 0.132025599479675, which is the natural sky
                        // brightness.
                        lpNumber += 0.132025599479675;
                        sqm = Math.log10(lpNumber / 108000000) / -0.4;

                        // Set the sqm in the field and update the field
                        $("#sqm").val(Math.round(sqm * 100) / 100).change();
                    });
                });
            });
            </script>';

        echo "<script>
          var geocoder;
          var map;
          var infowindow;
          var loca = new google.maps.LatLng(-29.2558, -70.7403);
          var myLocationMarker;
          var myLocations = [];

          function initialize() {
            geocoder = new google.maps.Geocoder();
            // Use current location, else use La Silla.
            if (navigator.geolocation) {
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
               document.getElementById('latitude').value = loca.latLng.lat();
               document.getElementById('longitude').value = loca.latLng.lng();
               fillHiddenFields(loca);
              addLocations();
            }
          }

          function errorCallBack(error) {
            var loca = new google.maps.LatLng(-29.2558, -70.7403);
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
               document.getElementById('latitude').value = loca.lat();
               document.getElementById('longitude').value = loca.lng();
              fillHiddenFields(loca);
              addLocations();
              google.maps.event.addListener(myLocationMarker, 'dragend', function(evt){
                 document.getElementById('latitude').value = evt.latLng.lat();
                 document.getElementById('longitude').value = evt.latLng.lng();
                fillHiddenFields(evt.latLng);
              });
          }

          function getPosition(position) {
            loca = new google.maps.LatLng(
                position.coords.latitude, position.coords.longitude
            );
             document.getElementById('latitude').value = position.coords.latitude;
             document.getElementById('longitude').value = position.coords.longitude;
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


              // gets the coords when drag event ends
              // then updates the input with the new coords
              google.maps.event.addListener(myLocationMarker, 'dragend', function(evt){
                 document.getElementById('latitude').value = evt.latLng.lat();
                 document.getElementById('longitude').value = evt.latLng.lng();
                fillHiddenFields(evt.latLng);
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
                    if (arrAddress[ac].types[0] == \"country\") {
                      document.getElementById('country').value =
                        arrAddress[ac].long_name;
                    }
                  }
                }
               }
              });

              // Find the timezone
            url = 'https://maps.googleapis.com/maps/api/timezone/json"
                . "?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE&location='
                + latLng.lat() + ',' + latLng.lng() + '&timestamp='
                + new Date().getTime() / 1000;
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
               if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var myArr = JSON.parse(xmlhttp.responseText);
                document.getElementById('timezone').value = myArr.timeZoneId;
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
                  document.getElementById('elevation').value = results[0].elevation;
                }
              }
            });
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
             var address = document.getElementById(\"address\").value;
             geocoder.geocode( { 'address': address}, function(results, status) {
               if (status == google.maps.GeocoderStatus.OK) {
                 map.setCenter(results[0].geometry.location);
                 document.getElementById('latitude').value =
                    results[0].geometry.location.lat();
                 document.getElementById('longitude').value =
                    results[0].geometry.location.lng();
                 fillHiddenFields(results[0].geometry.location);

                 // Remove old marker
                 myLocationMarker.setMap(null);
                 myLocationMarker = new google.maps.Marker({
                   map: map,
                   position: results[0].geometry.location,
                   draggable: true
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
            var image = '" . $baseURL . "/images/telescope.png';";

        foreach ($objLocation->getSortedLocations("id", $loggedUser) as $location) {
            echo "// Let's add the existing locations to the map.
                             var contentString = \"<strong>"
                . htmlspecialchars(
                    html_entity_decode(
                        $objLocation->getLocationPropertyFromId($location, "name")
                    )
                ) . "</strong><br /><br />Limiting magnitude: ";
            $limmag = $objLocation->getLocationPropertyFromId(
                $location, 'limitingMagnitude'
            );
            $sb = $objLocation->getLocationPropertyFromId($location, 'skyBackground');
            if (($limmag < - 900) && ($sb > 0)) {
                $limmag = sprintf(
                    "%.1f",
                    $objContrast->calculateLimitingMagnitudeFromSkyBackground($sb)
                );
            } elseif (($limmag < - 900) && ($sb < - 900)) {
                $limmag = "-";
                $sb = "-";
            } else {
                $sb = sprintf(
                    "%.1f",
                    $objContrast->calculateSkyBackgroundFromLimitingMagnitude($limmag)
                );
            }
            echo $limmag . "<br />SQM: " . $sb . "<br />";

            if ($objLocation->getLocationPropertyFromId($location, "locationactive")) {
                echo _("Active");
            } else {
                echo _("Not active");
            }

            echo "\";
                 var infowindow = new google.maps.InfoWindow({
                     content: contentString
                 });";

            echo "newLocation = new google.maps.LatLng("
                . $objLocation->getLocationPropertyFromId($location, "latitude")
                . ", "
                . $objLocation->getLocationPropertyFromId($location, "longitude") . ");
                  marker = new google.maps.Marker({
                  position: newLocation,
                  icon: image,
                  map: map,
                  html: contentString,
                  title: \""
                . htmlspecialchars(
                    html_entity_decode(
                        $objLocation->getLocationPropertyFromId($location, "name")
                    )
                ) . "\"
                });

                myLocations.push(marker);

                  map.addListener('center_changed', function(){
                    document.getElementById('latitude').value = map.getCenter().lat();
                    document.getElementById('longitude').value = map.getCenter().lng();
                    fillHiddenFields(map.getCenter());
                  });


                google.maps.event.addListener(marker, 'mouseover', function() {
                    infowindow.setContent(this.html);
                    infowindow.open(map, this);
                });
                ";
        }

        echo "      }
                </script>";


    </div>
</form>


@endsection

@push('scripts')

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE&v=3.exp&language=en&libraries=places">
</script>

<script>
    $(document).ready(function() {
        $("#location").select2({
            ajax: {
                // Do the autocompletion. Get all locations with the requested characters.
                url: '/location/autocomplete',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
            cache: true
            }
        });
    });

    $('.fd').on('input', function() {
        // If diameter is not set, don't do anything.
        if ($('.diameter input').val() != '') {
            $fl = parseFloat($('.fd input').val() * $('.diameter input').val()).toFixed(2);
            $('.focalLength input').val($fl);
        }
    });

    $('.focalLength').on('input', function() {
        // If diameter is not set, don't do anything.
        if ($('.diameter input').val() != '') {
            $fd = parseFloat($('.focalLength input').val() / $('.diameter input').val()).toFixed(2);
            $('.fd input').val($fd);
        }
    });

    $('.diameter').on('input', function() {
        // If fd is not set, don't do anything.
        if ($('.fd input').val() != '') {
            $focalLength = parseFloat($('.fd input').val() * $('.diameter input').val()).toFixed(2);
            $('.focalLength input').val($focalLength);
        }
    });

    $('#location').on("select2:selecting", function(e) {
        // Get the id of the selected location
        id = e.params.args.data.id;

        var self = this
        // Read the information of the location
        $.getJSON('/getLocationJson/' + id, function(data) {
            $('.name input').val(data.name);
            $('.type select').val(data.type);
            if ({{ Auth::user()->showInches }}) {
                $('.diameter input').val((data.diameter / 25.4).toFixed(1));
                $('.focalLength input').val((data.fd * data.diameter / 25.4).toFixed(1));
            } else {
                $('.diameter input').val(data.diameter);
                $('.focalLength input').val((data.fd * data.diameter).toFixed(2));
            }
            $('.fd input').val(data.fd);
            $('.fixedMagnification input').val(data.fixedMagnification);
        });
    });
</script>
@endpush
