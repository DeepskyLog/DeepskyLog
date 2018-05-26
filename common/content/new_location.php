<?php
/** 
 * Add a new location for the logged in user.
 * 
 * PHP Version 7
 * 
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
if ((! isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (! $loggedUser) {
    throw new Exception(LangException002);
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
    echo "<ol><li>" . LangAddSiteStep1 . "<strong>\"" . LangSearchLocations0 
        . "\"</strong>" . LangAddSiteStep1Button . "<br /><br /></li>";
    echo "<div class=\"form-inline\">
             <input type=\"text\" class=\"form-control\" id=\"address\" " 
        . "onkeypress=\"searchKeyPress(event);\" placeholder=\"La Silla, Chile\"" 
        . " autofocus></input>
             <input type=\"button\" class=\"btn btn-primary\" id=\"btnSearch\"" 
        . " value=\"" 
        . LangSearchLocations0 . "\" onclick=\"codeAddress();\" ></input>
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
    echo "<li>" . LangAddSiteStep2 . "<strong>\"" . LangAddSiteButton 
        . "\"</strong>" . LangAddSiteStep1Button . "<br /><br /></li>";
    echo "<div class=\"form-inline\">
            <input type=\"text\" required class=\"form-control\" " 
        . "name=\"locationname\" placeholder=\"" 
        . LangAddSiteField1 . "\"></input>";
    echo "  <input type=\"submit\" class=\"btn btn-success tour4\" " 
        . "name=\"add\" value=\"" 
        . LangAddSiteButton . "\" />";

    echo "</div><br />
            <label>" . LangAddSiteField7 . "</label>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"0\" max=\"9.9\" step=\"0.1\" " 
        . "class=\"form-control\" maxlength=\"5\" name=\"lm\" size=\"5\" />";
    echo "</div>";
    echo "<span class=\"help-block\">" . LangAddSiteField7Expl . "</span>";
    echo "</div>";

    echo "<div class=\"form-group\">
                <label>" . LangAddSiteField8 . "</label>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"10.0\" max=\"25.0\" step=\"0.01\" " 
        . "class=\"form-control\" maxlength=\"5\" name=\"sb\" size=\"5\" />";
    echo "</div>";
    echo "<span class=\"help-block\">" . LangAddSiteField8Expl . "</span>";
    echo "</div>";

    echo "</div></ol></form><br /><br />";

    echo "<script type=\"text/javascript\" " 
        . "src=\"https://maps.googleapis.com/maps/api/" 
        . "js?v=3.exp&language=en&sensor=true&libraries=places\">" 
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
                  document.getElementById('country').value = 
                    arrAddress[ac].long_name;
                }
              }
            }
           }
          });

          // Find the timezone
        url = 'https://maps.googleapis.com/maps/api/timezone/json?location=' 
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
            . html_entity_decode(
                $objLocation->getLocationPropertyFromId($location, "name")
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
            echo LangViewActive;
        } else {
            echo LangViewNotActive;
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
            . html_entity_decode(
                $objLocation->getLocationPropertyFromId($location, "name")
            ) . "\"
            });

            myLocations.push(marker);
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
