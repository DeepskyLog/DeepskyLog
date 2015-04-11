<?php
// change_site.php
// allows a site owner or an the administrator to change site details
// or another user to view the site details
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($locationid = $objUtil->checkGetKey ( 'location' )))
	throw new Exception ( LangException011b );
elseif (! ($objLocation->getLocationPropertyFromId ( $locationid, 'name' )))
	throw new Exception ( "Location not found in change_instrument.php, please contact the developers with this message:" . $eyepieceid );
else
	change_site ();
function change_site() {
	global $baseURL, $locationid, $loggedUser, $objLocation, $objPresentations, $objUtil, $objContrast;
	$disabled = " disabled=\"disabled\"";
	if (($loggedUser) && ($objUtil->checkAdminOrUserID ( $objLocation->getLocationPropertyFromId ( $locationid, 'observer', '' ) )))
		$disabled = "";
	$latitude = $objLocation->getLocationPropertyFromId ( $locationid, 'latitude' );
	$longitude = $objLocation->getLocationPropertyFromId ( $locationid, 'longitude' );
	$lm = $objLocation->getLocationPropertyFromId ( $locationid, 'limitingMagnitude' );
	$sb = $objLocation->getLocationPropertyFromId ( $locationid, 'skyBackground' );
	
	echo "<h4>" . stripslashes ( $objLocation->getLocationPropertyFromId ( $locationid, 'name' ) ) . "</h4>";
	echo "<hr />";
	echo "<a href=\"http://clearoutside.com/forecast/" . round ( $latitude, 2 ) . "/" . round ( $longitude, 2 ) . "\">
				<img src=\"http://clearoutside.com/forecast_image_small/" . round ( $latitude, 2 ) . "/" . round ( $longitude, 2 ) . "/forecast.png\" /></a>";
	echo "<br /><br />";
	echo "<div id=\"map\"></div>";
	
	echo "<br /><form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
	echo "<input type=\"hidden\" name=\"id\" value=\"" . $locationid . "\" />&nbsp;";
	echo "<input type=\"hidden\" name=\"latitude\" id=\"latitude\" />";
	echo "<input type=\"hidden\" name=\"longitude\" id=\"longitude\" />";
	echo "<input type=\"hidden\" name=\"country\" id=\"country\" />";
	echo "<input type=\"hidden\" name=\"elevation\" id=\"elevation\" />";
	echo "<input type=\"hidden\" name=\"timezone\" id=\"timezone\" />";
	echo "<div class=\"form-inline\">
    		<input type=\"text\" required class=\"form-control\" name=\"locationname\" placeholder=\"" . LangAddSiteField1 . "\" value=\"" . stripslashes ( $objLocation->getLocationPropertyFromId ( $locationid, 'name' ) ) . "\"  " . $disabled . "></input>";
	$content = ($disabled ? "" : "  <input type=\"submit\" class=\"btn btn-primary tour4\" name=\"change\" value=\"" . LangAddSiteButton2 . "\" />");
	echo $content;
	
	echo "</div>
 	       <label>" . LangAddSiteField7 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"0\" max=\"9.9\" step=\"0.1\" class=\"form-control\" maxlength=\"5\" name=\"lm\" size=\"5\" value=\"" . (($lm > - 900) ? $lm : "") . "\"  " . $disabled . " />";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSiteField7Expl . "</span>";
	echo "</div>";
	
	echo "<div class=\"form-group\">
     	       <label>" . LangAddSiteField8 . "</label>";
	echo "<div class=\"form-inline\">";
	echo "<input type=\"number\" min=\"10.0\" max=\"25.0\" step=\"0.01\" class=\"form-control\" maxlength=\"5\" name=\"sb\" size=\"5\" value=\"" . (($sb > - 900) ? $sb : "") . "\"  " . $disabled . " />";
	echo "</div>";
	echo "<span class=\"help-block\">" . LangAddSiteField8Expl . "</span>";
	echo "</div>";
	
	echo "</div></form><br /><br />";
	
	echo "<script type=\"text/javascript\" src=\"https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true&libraries=places\"></script>";
	
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
		url = 'https://maps.googleapis.com/maps/api/timezone/json?location=' + latLng.lat() + ',' + latLng.lng() + '&timestamp=' + new Date().getTime() / 1000;
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
		
		elevator.getElevationForLocations(positionalRequest, function(results, status) {
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
	
	foreach ( $objLocation->getSortedLocations ( "id", $loggedUser ) as $location ) {
		if ($location != $locationid) {
			echo "// Let's add the existing locations to the map.
	   		  var contentString = \"<strong>" . html_entity_decode ( $objLocation->getLocationPropertyFromId ( $location, "name" ) ) . "</strong><br /><br />Limiting magnitude: ";
			$limmag = $objLocation->getLocationPropertyFromId ( $location, 'limitingMagnitude' );
			$sb = $objLocation->getLocationPropertyFromId ( $location, 'skyBackground' );
			if (($limmag < - 900) && ($sb > 0))
				$limmag = sprintf ( "%.1f", $objContrast->calculateLimitingMagnitudeFromSkyBackground ( $sb ) );
			elseif (($limmag < - 900) && ($sb < - 900)) {
				$limmag = "-";
				$sb = "-";
			} else {
				$sb = sprintf ( "%.1f", $objContrast->calculateSkyBackgroundFromLimitingMagnitude ( $limmag ) );
			}
			echo $limmag . "<br />SQM: " . $sb . "<br />";
			
			if ($objLocation->getLocationPropertyFromId ( $location, "locationactive" )) {
				echo "Active";
			} else {
				echo "Not active";
			}
			
			echo "\";
 			var infowindow = new google.maps.InfoWindow({
	 			content: contentString
	 		});";
			
			echo "newLocation = new google.maps.LatLng(" . $objLocation->getLocationPropertyFromId ( $location, "latitude" ) . ", " . $objLocation->getLocationPropertyFromId ( $location, "longitude" ) . ");
			  marker = new google.maps.Marker({
			  position: newLocation,
			  icon: image,
			  map: map,
			  html: contentString,
			  title: \"" . html_entity_decode ( $objLocation->getLocationPropertyFromId ( $location, "name" ) ) . "\"
			});
	
			myLocations.push(marker);
			google.maps.event.addListener(marker, 'mouseover', function() {
				infowindow.setContent(this.html);
				infowindow.open(map, this);
			});
			";
		}
	}
	echo "}      		
      		google.maps.event.addDomListener(window, 'load', initialize);
      		</script>";
}
?>
