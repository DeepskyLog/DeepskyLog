<?php
/**
 * Add a new location for the logged in user.
 *
 * PHP Version 7
 *
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (! $loggedUser) {
    throw new Exception(
        _("You need to be logged in to change your locations or equipment.")
    );
} else {
    newLocation();
}

/**
 * Add a new location for the logged in user.
 *
 * @return None
 */
function newLocation()
{
    global $objLocation, $loggedUser, $objContrast, $baseURL;
    echo "<form>";
    echo "<ol><li>"
        . sprintf(
            _("Set your location on the map or by entering the name and pressing the %s button."),
            "<strong>\"" . _("Search location")
            . "\"</strong>"
        ) . "<br /><br /></li>";
    echo "<div class=\"form-inline\">
             <input type=\"text\" class=\"form-control\" id=\"address\" "
        . "onkeypress=\"searchKeyPress(event);\" placeholder=\"La Silla, Chile\""
        . " autofocus></input>
             <input type=\"button\" class=\"btn btn-primary\" id=\"btnSearch\""
        . " value=\""
        . _("Search location") . "\" onclick=\"codeAddress();\" ></input>
            </div>
           </form>
           <div id=\"map\"></div>
           ";

    echo "<br /><form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
    echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
    echo "<input type=\"hidden\" name=\"latitude\" id=\"latitude\" />";
    echo "<input type=\"hidden\" name=\"longitude\" id=\"longitude\" />";
    echo "<input type=\"hidden\" name=\"country\" id=\"country\" />";
    echo "<input type=\"hidden\" name=\"elevation\" id=\"elevation\" />";
    echo "<input type=\"hidden\" name=\"timezone\" id=\"timezone\" />";
    echo "<li>"
        . sprintf(
            _("Define your own name for this location, eventually add a naked eye limiting magnitude (or SQM value) and press the %s button."),
            "<strong>\"" . _("Add site") . "\"</strong>"
        ) . "<br /><br /></li>";
    echo "<div class=\"form-inline\">
            <input type=\"text\" required class=\"form-control\" "
        . "name=\"locationname\" placeholder=\""
        . _("Location name") . "\"></input>";
    echo "  <input type=\"submit\" class=\"btn btn-success tour4\" "
        . "name=\"add\" value=\""
        . _("Add site") . "\" />";

    // Limiting magnitude
    echo "</div><br />
            <table class='table'>
            <tr>
                <th>" . _("Typical naked eye limiting magnitude") . "</th>
                <th>" . _("Sky Quality Meter (SQM) value") . "</th>
                <th>" . _("Bortle Scale") . "</th>
                <th><a class='btn btn-primary' href='#' role='button'"
            . " id='lightpollutioninfo'>"
            . _("Use value from lightpollutionmap.info") . "</a></th>
            </tr>";
    echo "  <tr>
                <td><div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"0\" max=\"8.0\" step=\"0.1\" "
        . "class=\"form-control\" maxlength=\"5\" id=\"lm\""
        . " name=\"lm\" size=\"5\" />";
    echo "</div>";
    echo "</td>";

    // SQM
    echo "<td>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"10.0\" max=\"25.0\" step=\"0.01\" "
        . "class=\"form-control\" maxlength=\"5\" id=\"sqm\""
        . " name=\"sb\" size=\"5\" />";
    echo "</div>";
    echo "</td>";

    // Bortle Scale
    echo "<td>";
    echo "<div class=\"form-inline\">";
    echo '<select id="bortle" name="bortle">
            <option></option>
            <option value="1">1 - ' . _("Excellent dark-sky site") . '</option>
            <option value="2">2 - ' . _("Typical truly dark site") . '</option>
            <option value="3">3 - ' . _("Rural sky") . '</option>
            <option value="4">4 - ' . _("Rural/suburban transition") . '</option>
            <option value="5">5 - ' . _("Suburban sky") . '</option>
            <option value="6">6 - ' . _("Bright suburban sky") . '</option>
            <option value="7">7 - ' . _("Suburban/urban transition") . '</option>
            <option value="8">8 - ' . _("City sky") . '</option>
            <option value="9">9 - ' . _("Inner-city sky") . '</option>
          </select>';
    echo "</div>";
    echo "</td>
          <td></td></table></ol></form><br /><br />";

    // Javascript to convert from limiting magnitude to sqm and bortle
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
                url = "/lightpollutionmap.php?longitude=" +
                + $("#longitude").val() + "&latitude="
                + $("#latitude").val();

            $.getJSON(url,function(data) {
                // Remove the html tags and convert to a number
                lpNumber = Number(data);
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

    echo "<script type=\"text/javascript\" "
        . "src=\"https://maps.googleapis.com/maps/api/"
        . "js?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE"
        . "&v=3.exp&language=en&libraries=places\">"
        . "</script>";

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
                            document.getElementById('country').value = arrAddress[ac].long_name;
                        }
                    }
                }
            }
        });

          // Find the timezone
          var requestOptions = {
            method: 'GET',
          };

          url = 'https://api.geoapify.com/v1/geocode/reverse?lat=' + latLng.lat() + '&lon=' + latLng.lng() +'&apiKey=7bdf49488a0e4e22b7f7227b775282db';
          var tz = 'UTC';
          fetch(url, requestOptions).then(resp => resp.json()).then((result) => {
            try {
                tz = result['features'][0]['properties']['timezone']['name'];
            } catch (error) {
                tz = 'UTC';
            }
            document.getElementById('timezone').value = tz;
          });

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
}
?>
