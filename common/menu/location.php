<?php
// location.php
// menu which allows the user to change its standard location
global $inIndex, $loggedUser, $objUtil;

if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
elseif (! ($loggedUser))
	throw new Exception ( LangException001 );
elseif (! ($objUtil->checkAdminOrUserID ( $loggedUser )))
	throw new Exception ( LangException012 );
else
	menu_location ();
function menu_location() {
	global $baseURL, $loggedUser, $objLocation, $objObject, $objObserver;
	if ($loggedUser) {
		if (array_key_exists ( 'activeLocationId', $_GET ) && $_GET ['activeLocationId']) {
			$objObserver->setObserverProperty ( $loggedUser, 'stdlocation', $_GET ['activeLocationId'] );
			if (array_key_exists ( 'Qobj', $_SESSION ))
				$_SESSION ['Qobj'] = $objObject->getObjectVisibilities ( $_SESSION ['Qobj'] );
		}
		$result = $objLocation->getSortedLocations ( 'name', $loggedUser, 1 );
		$loc = $objObserver->getObserverProperty ( $loggedUser, 'stdlocation' );

		if ($result) {
			echo "<ul class=\"nav navbar-nav\">
			      <li class=\"dropdown\">
					<a href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle navbar-btn\" data-toggle=\"dropdown\">" . $objLocation->getLocationPropertyFromId ( $loc, 'name' ) . "<b class=\"caret\"></b></a>";
			echo " <ul class=\"dropdown-menu\">";

			$url = "http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"];
			if ($url == $baseURL || $url == $baseURL . "#" || $url = $baseURL . "index.php") {
				$url = $baseURL . "index.php?title=Home";
			}
			foreach ($result as $key=>$value) {
				echo "  <li><a href=\"" . $url . "&amp;activeLocationId=" . $value . "\">" . $objLocation->getLocationPropertyFromId ( $value, 'name' ) . "</a></li>";
			}

			echo " </ul>";
			echo "</li>
			    </ul>";
		}
	}
}
?>
