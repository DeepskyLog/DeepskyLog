<?php

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
throw new Exception ( LangException002 );
else
	new_location ();
function new_location() {

	// TODO: Use current location to start the map
	// TODO: Add other/existing locations to the map.
	// TODO: Read out the coordinates of the new location
	// TODO: Read out the Timezone, ... of the new location

	// TODO: Select using google maps.
	// TODO: Show the other locations on the map.
	// TODO: Make it possible to select one of the other locations.
	// TODO: In the overview of the locations, make it possible to show it on the map, and make it possible to get directions to the location.
	// TODO: Maybe add a button with a pencil to change, else, show the google maps, only with your locations.
	
// 	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
// 	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
// 	echo "<input type=\"submit\" class=\"btn btn-primary pull-right tour4\" name=\"add\" value=\"" . LangAddSiteButton . "\" />&nbsp;";
// 	echo "</form>";
	echo "<script src=\"https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true&libraries=places\"></script>";

	
	echo "<script>
      var geocoder;
      var map;
      var infowindow;
	  var loca = new google.maps.LatLng(-29.2558, -70.7403);

      function initialize() {
        geocoder = new google.maps.Geocoder();
		// TODO: Use current location, else use 0.0
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(getPosition);
		} else {
          map = new google.maps.Map(document.getElementById('map'), {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: loca,
            zoom: 15
          });
		}
      }

	  function getPosition(position) {
        loca = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        map = new google.maps.Map(document.getElementById('map'), {
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          center: loca,
          zoom: 15
        });
	  }
			
      function callback(results, status) {
        if (status == google.maps.places.PlacesServiceStatus.OK) {
          for (var i = 0; i < results.length; i++) {
            createMarker(results[i]);
          }
        }
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

  function codeAddress() {
    var address = document.getElementById(\"address\").value;
    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        map.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
            map: map,
            position: results[0].geometry.location
        });
	 } else {
        alert(\"Geocode was not successful for the following reason: \" + status);
      }
    });
}

      google.maps.event.addDomListener(window, 'load', initialize);
    </script>";
	
	echo "<form>
			<div class=\"form-inline\">
	         <input type=\"text\" class=\"form-control\" id=\"address\" placeholder = \"La Silla, Chile\" autofocus onkeypress=\"if (event.keyCode == 13) document.getElementById('btnSearch').click()\"></input>
             <input type=\"button\" class=\"btn btn-success\" id=\"btnSearch\" value=\"" . LangSearchLocations0 . "\" onclick=\"codeAddress();\" ></input>
            </div>
           </form>
           <div id=\"map\"></div>
           ";
// 	echo "<iframe
// 			width=\"450\"
// 			height=\"250\"
// 			frameborder=\"0\" style=\"border:0\"
// 							src=\"https://www.google.com/maps/embed/v1/search?key=AIzaSyDGQJvhs1ItqmrFfYPRrh3vNpBzNbWntis&q=record+stores+in+Seattle\">
// 							</iframe>";
//	exit();
}
?>
