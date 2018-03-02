<?php
// view_object.php
// view all information of one object
global $inIndex, $loggedUser, $objUtil;
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	view_object ();
function view_object() {
	global $baseURL, $loggedUser, $objObserver, $objPresentations, $objCometObject;
	if (! $_GET ['object']) // no object defined in url
		header ( "Location: ../index.php" );
	echo "<div id=\"main\">";
	
	// Let's test for the observer... If cometadministrator (or normal administrator), we can change the object)
	$admin = false;
	
	// Check if there is an observer
	if (array_key_exists ( 'deepskylog_id', $_SESSION )) {
		// Check if this observer is cometadministrator
		if ($objObserver->getObserverProperty ( $_SESSION ['deepskylog_id'], "role" ) == ROLECOMETADMIN || $objObserver->getObserverProperty ( $_SESSION ['deepskylog_id'], "role" ) == ROLEADMIN) {
			$admin = true;
		}
	}
	
	if ($admin) {
		echo "<form action=\"" . $baseURL . "index.php?indexAction=comets_validate_change_object\" method=\"post\"><div>";
		echo "<input type=\"hidden\" name=\"object\" value=\"" . $_GET ['object'] . "\" />";
		$content = "<input type=\"submit\" class=\"btn btn-successpull-right\" name=\"newobject\" value=\"" . LangChangeAccountButton . "\" />";
		echo "<h4>" . LangChangeObject . " " . $objCometObject->getName ( $_GET ['object'] ) . "</h4>";
		echo $content;
		echo "<br /><hr />";
		$content = "<input type=\"text\" required class=\"form-control\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"" . $objCometObject->getName ( $_GET ['object'] ) . "\" />";
		echo "<strong>" . LangViewObjectField1 . "&nbsp;*</strong>";
		echo $content;
		
		if ($objCometObject->getIcqName ( $_GET ['object'] )) {
			$icqname = $objCometObject->getIcqName ( $_GET ['object'] );
		} else {
			$icqname = "";
		}
		$content = "<input type=\"text\" required class=\"form-control\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"" . $icqname . "\" />";
		echo "<strong>" . LangNewObjectIcqname . "&nbsp;*</strong>";
		echo $content;
		;
		echo "<hr />";
		echo "</div></form>";
	} else {
		echo "<h4>" . LangViewObjectTitle . "&nbsp;-&nbsp;" . $objCometObject->getName ( $_GET ['object'] ) . "</h4>";
		echo "<hr />";
		
		echo "<strong>" . LangViewObjectField1 . "</strong>";
		echo "<input type=\"text\" disabled class=\"form-control\" maxlength=\"40\" name=\"name\" size=\"40\" value=\"" . $objCometObject->getName ( $_GET ['object'] ) . "\" />";
		if ($objCometObject->getIcqName ( $_GET ['object'] )) {
			echo "<strong>" . LangNewObjectIcqname . "</strong>";
			echo "<input type=\"text\" disabled class=\"form-control\" maxlength=\"40\" name=\"icqname\" size=\"40\" value=\"" . $objCometObject->getIcqName ( $_GET ['object'] ) . "\" />";
		}
		echo "<hr />";
	}
	
	// LINK TO OBSERVATIONS OF OBJECT
	$observations = new CometObservations ();
	$queries = array (
			"object" => $objCometObject->getName ( $_GET ['object'] ) 
	);
	$content = "";
	if (count ( $observations->getObservationFromQuery ( $queries ) ) > 0)
		$content .= "&nbsp;&nbsp;<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode ( $_GET ['object'] ) . "\">" . LangViewObjectObservations . " " . $objCometObject->getName ( $_GET ['object'] ) . "</a>";
		// extra link to add observation of this object
	if ($loggedUser) {
		$_SESSION ['observedobject'] = $_GET ['object'];
		$_SESSION ['result'] = $objCometObject->getExactObject ( $_SESSION ['observedobject'] );
		// $_SESSION['observedobject'] = $_SESSION['result'][0]; // use name in database
		$_SESSION ['found'] = "yes";
		$_SESSION ['backlink'] = "validate_search_object.php";
		$content .= "&nbsp;&nbsp;<a class=\"btn btn-success\" href=\"" . $baseURL . "index.php?indexAction=comets_add_observation&amp;observedobject=" . urlencode ( $_GET ['object'] ) . "\">" . LangViewObjectAddObservation . "&nbsp;" . $objCometObject->getName ( $_GET ['object'] ) . "</a>";
	}
	echo $content; 
	echo ("</div>");
}
?>
