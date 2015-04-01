<?php
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! $loggedUser)
throw new Exception ( LangException002 );
else
	new_location ();
function new_location() {
    global $objLocation, $loggedUser, $objContrast, $baseURL;
	// TODO: Make sure the coordinates can be read out also when we search for a location.
	// TODO: Read out the coordinates of the new location when we don't drag with the mouse.
	// TODO: Do we need state / province for OAL? What do we need. 
	// TODO: Read out the Timezone of the new location
	// TODO: Move strings to language files. 
	
	// TODO: Make it possible to select one of the other locations.
	// TODO: In the overview of the locations, make it possible to show it on the map, and make it possible to get directions to the location.
	// TODO: Maybe add a button with a pencil to change, else, show the google maps, only with your locations.
	
	// TODO: After clicking OK, ask in a dialog for the name, then public / private, SQM / limiting magnitude, ...
// 	echo "<form role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
// 	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_site\" />";
// 	echo "<input type=\"submit\" class=\"btn btn-primary pull-right tour4\" name=\"add\" value=\"" . LangAddSiteButton . "\" />&nbsp;";
// 	echo "</form>";
	echo "<form>
			<div class=\"form-inline\">
	         <input type=\"text\" class=\"form-control\" id=\"address\" onkeypress=\"searchKeyPress(event);\" placeholder=\"La Silla, Chile\" autofocus></input>
             <input type=\"button\" class=\"btn btn-success\" id=\"btnSearch\" value=\"" . LangSearchLocations0 . "\" onclick=\"codeAddress();\" ></input>
            </div>
           </form>
           <div id=\"map\"></div>
           ";
	
	echo "<label for='latitude'>Latitude:</label><br />
	<input id='latitude' type='text' value='' /><br />
	<label for='longitude'>Longitude:</label><br />
	<input id='longitude' type='text' value='' /></p>";
	
	echo "<script src=\"https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=true&libraries=places\"></script>";
	
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
		  // gets the coords when drag event ends
          // then updates the input with the new coords
          google.maps.event.addListener(myLocationMarker, 'dragend', function(evt){
 			document.getElementById('latitude').value = evt.latLng.lat().toFixed(6);
 			document.getElementById('longitude').value = evt.latLng.lng().toFixed(6);
		  });
		  
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
		  // gets the coords when drag event ends
          // then updates the input with the new coords
          google.maps.event.addListener(myLocationMarker, 'dragend', function(evt){
 			document.getElementById('latitude').value = evt.latLng.lat().toFixed(6);
 			document.getElementById('longitude').value = evt.latLng.lng().toFixed(6);
		  });
			addLocations();
      }

	  function getPosition(position) {
        loca = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
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
 			document.getElementById('latitude').value = evt.latLng.lat().toFixed(6);
 			document.getElementById('longitude').value = evt.latLng.lng().toFixed(6);
			
			// Do reverse geocoding:
			geocoder.geocode({'latLng': evt.latLng}, function(results, status) {
    			if (status == google.maps.GeocoderStatus.OK) {
      				if (results[0]) {
						arrAddress = results[0].address_components;
						for (ac = 0; ac < arrAddress.length; ac++) {
							if (arrAddress[ac].types[0] == \"country\") { alert(arrAddress[ac].long_name) }
						}
			
			
//							var state = results[i].address_components[2].short_name;
			
//			            alert(results[1].address_components[5]);
				    } else {
        				alert('No results found');
      				}
    			} else {
      				alert('Geocoder failed due to: ' + status);
    			}
  			});
		  });
		  addLocations();
	  }

	  function addLocations( ) {
		var image = '" . $baseURL . "/images/telescope.png';";
	
  		foreach($objLocation->getSortedLocations("id", $loggedUser) as $location) {
  			echo "
  		// Let's add the existing locations to the map.
  		var contentString = \"<strong>" . html_entity_decode($objLocation->getLocationPropertyFromId($location, "name")) . "</strong><br /><br />Limiting magnitude: "; 
	
			$limmag = $objLocation->getLocationPropertyFromId($location,'limitingMagnitude');
  			$sb = $objLocation->getLocationPropertyFromId($location,'skyBackground');
  			if(($limmag<-900)&&($sb>0))
  				$limmag = sprintf("%.1f", $objContrast->calculateLimitingMagnitudeFromSkyBackground($sb));
  			elseif(($limmag<-900)&&($sb<-900))
  			{ $limmag="-";
  			  $sb="-";
  			} else {
  			  $sb=sprintf("%.1f", $objContrast->calculateSkyBackgroundFromLimitingMagnitude($limmag));
  			}
  			  	
  		echo $limmag . "<br />SQM: " . $sb . "<br />";
  		if ($objLocation->getLocationPropertyFromId($location, "locationactive")) {
  			echo "Active";
  		} else {
  			echo "Not active";
  		}

  		echo "\";
 		var infowindow = new google.maps.InfoWindow({
 				content: contentString
 			});";
		echo "newLocation = new google.maps.LatLng(" . $objLocation->getLocationPropertyFromId($location, "latitude") .
		                       ", " . $objLocation->getLocationPropertyFromId($location, "longitude") . ");
		marker = new google.maps.Marker({
    		position: newLocation,
            icon: image,
    		map: map,
		    html: contentString,
		    title: '" . html_entity_decode($objLocation->getLocationPropertyFromId($location, "name")) . "'
  		});
  		myLocations.push(marker);
		google.maps.event.addListener(marker, 'mouseover', function() {
		  infowindow.setContent(this.html);
          infowindow.open(map, this);
        });

        // assuming you also want to hide the infowindow when user mouses-out
        google.maps.event.addListener(marker, 'mouseout', function() {
          infowindow.close();
        });
		    		
		    		";
  		
  			
//   			print $objLocation->getLocationPropertyFromId($location, "locationactive");
  				
  		}
	
	echo "
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
	         // Remove old marker
		     myLocationMarker.setMap(null);
             myLocationMarker = new google.maps.Marker({
               map: map,
               position: results[0].geometry.location,
			   draggable: true
           });
	     } else {
            alert(\"Geocode was not successful for the following reason: \" + status);
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
			</script>";
}
?>
