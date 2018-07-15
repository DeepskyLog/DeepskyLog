<?php
/** 
 * Allows a site owner or an the administrator to change site details
 * or another user to view the site details
 * 
 * PHP Version 7
 * 
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
} elseif (!($locationid = $objUtil->checkGetKey('location'))) {
    throw new Exception(LangException011b);
} elseif (!($objLocation->getLocationPropertyFromId($locationid, 'name'))) {
    throw new Exception(
        "Location not found in change_instrument.php, " 
        . "please contact the developers with this message:" 
        . $locationid
    );
} else {
    changeSite();
}

/**
 * Changes the site: limiting magnitude, surface brightness, latitude and longitude
 * 
 * @return None
 */
function changeSite() 
{
    global $baseURL, $locationid, $loggedUser, $objLocation, $objPresentations;
    global $objUtil, $objContrast;
    $disabled = " disabled=\"disabled\"";
    if ($loggedUser 
        && ($objUtil->checkAdminOrUserID(
            $objLocation->getLocationPropertyFromId($locationid, 'observer', '')
        ))
    ) {
        $disabled = "";
    }
    $latitude = $objLocation->getLocationPropertyFromId($locationid, 'latitude');
    $longitude = $objLocation->getLocationPropertyFromId($locationid, 'longitude');
    $lm = $objLocation->getLocationPropertyFromId($locationid, 'limitingMagnitude');
    $sb = $objLocation->getLocationPropertyFromId($locationid, 'skyBackground');

    echo "<h4>";
    echo stripslashes($objLocation->getLocationPropertyFromId($locationid, 'name'));
    echo "</h4>";
    echo "<hr />";
    echo "<a href=\"https://clearoutside.com/forecast/" 
        . round($latitude, 2) . "/" . round($longitude, 2) . "\">
                <img src=\"https://clearoutside.com/forecast_image_small/" 
        . round($latitude, 2) . "/" . round($longitude, 2) . "/forecast.png\" />
        </a>";
    echo "<br /><br />";
    echo "<form>";
    echo "<div class=\"form-inline\">
             <input type=\"text\" class=\"form-control\" id=\"address\" " 
            . " onkeypress=\"searchKeyPress(event);\" placeholder=\"" 
            . stripslashes(
                $objLocation->getLocationPropertyFromId($locationid, 'name')
            )
             . "\" autofocus></input>
             <input type=\"button\" class=\"btn btn-primary\" id=\"btnSearch\"" 
            . " value=\"" 
            . _("Search location") 
            . "\" onclick=\"codeAddress();\" ></input>
            </div>
          </form>
          <div id=\"map\"></div>
          ";

    echo "<br /><form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
    echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
    echo "<input type=\"hidden\" name=\"id\" value=\"" . $locationid . "\" />&nbsp;";
    echo "<input type=\"hidden\" name=\"latitude\" id=\"latitude\"" 
        . " value=\"" . $latitude . "\"/>";
    echo "<input type=\"hidden\" name=\"longitude\" id=\"longitude\"" 
        . " value=\"" . $longitude . "\"/>";
    echo "<input type=\"hidden\" name=\"country\" id=\"country\" />";
    echo "<input type=\"hidden\" name=\"elevation\" id=\"elevation\" />";
    echo "<input type=\"hidden\" name=\"timezone\" id=\"timezone\" />";
    echo "<div class=\"form-inline\">
            <input type=\"text\" required class=\"form-control\" " 
        . "name=\"locationname\" placeholder=\"" 
        . LangAddSiteField1 . "\" value=\"" 
        . stripslashes($objLocation->getLocationPropertyFromId($locationid, 'name')) 
        . "\"  " . $disabled . "></input>
             ";
    $content = $disabled 
        ? "" 
        : "  <input type=\"submit\" class=\"btn btn-primary tour4\" name=\"change\"" 
        . " value=\"" . ("Change site") . "\" />";
    echo $content;

    // Limiting magnitude
    echo "</div><br />
            <table class='table'>
            <tr>
                <th>" . LangAddSiteField7 . "</th>
                <th>" . LangAddSiteField8 . "</th>
                <th>" . LangAddSiteField9 . "</th>
                <th><a class='btn btn-primary' href='#' role='button'" 
            . " id='lightpollutioninfo'>" 
            . LangAddSiteField10 . "</a></th>
            </tr>";

    echo "  <tr>
                <td><div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"0\" max=\"8.0\" step=\"0.1\" " 
        . "class=\"form-control\" maxlength=\"5\" id=\"lm\""
        . " name=\"lm\" size=\"5\" value=\"" 
        . (($lm > - 900) ? $lm : "") . "\"  " . $disabled . " />";
    echo "</div>";
    echo "</td>";

    // SQM
    echo "<td>";
    echo "<div class=\"form-inline\">";
    echo "<input type=\"number\" min=\"10.0\" max=\"25.0\" step=\"0.01\" " 
        . "class=\"form-control\" maxlength=\"5\" id=\"sqm\""
        . " name=\"sb\" size=\"5\" value=\"" 
        . (($sb > - 900) ? $sb : "") . "\"  " . $disabled . " />";
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
        $("#lm").on("keyup change", function(event) {
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
            if ($("#sqm").val() != "") {
                val = $("#sqm").val();
                $("#sqm").val(val).change();
            }
            if ($("#lm").val() != "") {
                val = $("#lm").val();
                $("#lm").val(val).change();
            }
    
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
                var yql = "http://query.yahooapis.com/v1/public/yql?q=" 
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

    echo "<script type=\"text/javascript\"" 
        . " src=\"https://maps.googleapis.com/maps/api/js" 
        . "?key=AIzaSyD8QoWrJk48kEjHhaiwU77Tp-qSaT2xCNE&v=3.exp&language=en"
        . "&libraries=places\"></script>";

    echo "<script>
      var geocoder;
      var map;
      var infowindow;
      var loca = new google.maps.LatLng(" . $latitude . ", " . $longitude . ");
      var myLocationMarker;
      var myLocations = [];

      function initialize() {
        geocoder = new google.maps.Geocoder();
        // Use current location, else use La Silla.
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
          fillHiddenFields(loca);

          addLocations();

            google.maps.event.addListener(myLocationMarker, 'dragend', function(evt){
             document.getElementById('latitude').value = evt.latLng.lat();
             document.getElementById('longitude').value = evt.latLng.lng();
            fillHiddenFields(evt.latLng);
          });

      }

      function codeAddress() {
         var address = document.getElementById(\"address\").value;
         geocoder.geocode( { 'address': address}, function(results, status) {
           if (status == google.maps.GeocoderStatus.OK) {
             map.setCenter(results[0].geometry.location);
             document.getElementById('latitude').value = " 
        . "results[0].geometry.location.lat();
              document.getElementById('longitude').value = " 
        . "results[0].geometry.location.lng();
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

      function fillHiddenFields(latLng) {
        // Do reverse geocoding:
        geocoder.geocode({'latLng': latLng}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
              if (results[0]) {
              arrAddress = results[0].address_components;
              for (ac = 0; ac < arrAddress.length; ac++) {
                if (arrAddress[ac].types[0] == \"country\") {
                  document.getElementById('country').value = " 
                . "arrAddress[ac].long_name;
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

        elevator.getElevationForLocations(positionalRequest, " 
        . "function(results, status) {
          if (status == google.maps.ElevationStatus.OK) {
              // Retrieve the first result
              if (results[0]) {
              document.getElementById('elevation').value = results[0].elevation;
            }
          }
        });
      }

      function addLocations( ) {
        var image = '" . $baseURL . "/images/telescope.png';";
    if ($loggedUser != "") {
        foreach ($objLocation->getSortedLocations("id", $loggedUser) as $location) {
            if ($location != $locationid) {
                echo "// Let's add the existing locations to the map.
                 var contentString = \"<strong>" 
                    . html_entity_decode(
                        $objLocation->getLocationPropertyFromId($location, "name")
                    ) 
                    . "</strong><br /><br />Limiting magnitude: ";
                $limmag = $objLocation->getLocationPropertyFromId(
                    $location, 'limitingMagnitude'
                );
                $sb = $objLocation->getLocationPropertyFromId(
                    $location, 'skyBackground'
                );
                if (($limmag < - 900) && ($sb > 0)) {
                    $limmag = sprintf(
                        "%.1f", 
                        $objContrast->calculateLimitingMagnitudeFromSkyBackground(
                            $sb
                        )
                    );
                } elseif (($limmag < - 900) && ($sb < - 900)) {
                    $limmag = "-";
                    $sb = "-";
                } else {
                    $sb = sprintf(
                        "%.1f", 
                        $objContrast->calculateSkyBackgroundFromLimitingMagnitude(
                            $limmag
                        )
                    );
                }
                echo $limmag . "<br />SQM: " . $sb . "<br />";

                if ($objLocation->getLocationPropertyFromId(
                    $location, "locationactive"
                )
                ) {
                    echo LangViewActive;
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
                    . $objLocation->getLocationPropertyFromId(
                        $location, "longitude"
                    ) . ");
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
        }
    }
    echo "}
              google.maps.event.addDomListener(window, 'load', initialize);
              </script>";
}
?>
