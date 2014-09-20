<?php
// view_message.php
// generates an overview of all messages
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	view_message ();
function view_message() {
	global $baseURL, $loggedUser, $objObserver, $objMessages, $dateformat, $objPresentations, $instDir;
	$id = $_GET ["id"];
	
	// Here we check whether the logged in user has the permission to see the message.
	$validMail = false;
	if (($objMessages->getReceiver ( $id ) == "all") || $objMessages->getReceiver ( $id ) == $loggedUser) {
		$validMail = true;
	}
	if ($validMail && $objMessages->isDeleted ( $id, $loggedUser )) {
		$validMail = false;
	}
	if ($validMail) {
		echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/presentation.js\"></script>";
		
		echo "<div id=\"main\">";
		
		// Check whether the mail is already read
		if ($loggedUser != "") {
			if (! $objMessages->isRead ( $id, $loggedUser )) {
				// Mark the message as read
				$objMessages->markMessageRead ( $id, $loggedUser );
			}
		}
		echo "<h3>" . $objMessages->getSubject ( $id ); // . "</h3>";
		                                                
		// Show the picture of the sender
		$senderId = $objMessages->getSender ( $id );
		
		$dir = opendir ( $instDir . 'common/observer_pics' );
		while ( FALSE !== ($file = readdir ( $dir )) ) {
			if (("." == $file) or (".." == $file))
				continue; // skip current directory and directory above
			if (fnmatch ( $senderId . ".gif", $file ) || fnmatch ( $senderId . ".jpg", $file ) || fnmatch ( $senderId . ".png", $file )) {
		        echo "<img height=\"72\" src=\"" . $baseURL . "/common/observer_pics/" . $file . "\" class=\"img-rounded pull-right\">";
			}
		}		
		echo "</h3>";
		
		if ($senderId == "DeepskyLog") {
			$senderName = $senderId;
		} else {
			$senderName = $objObserver->getObserverProperty ( $senderId, "firstname" ) . "&nbsp;" . $objObserver->getObserverProperty ( $senderId, "name" );
		}
		
		// Use the date format from databaseInfo
		$phpdate = strtotime ( $objMessages->getDate ( $id ) );
		
		echo "<h4>" . LangMessageBy . $senderName . " - " . date ( $dateformat . " G:i:s", $phpdate ) . "</h4>";
		
		echo "<hr />";
		
		// When the user is not logged in, it should not be possible to have links in the mails
		if ($loggedUser == "") {
			echo $objMessages->getContentWithoutLinks ( $id );
		} else {
			echo $objPresentations->searchAndLinkCatalogsInText ( $objMessages->getContent ( $id ) );
		}
		
		echo "<hr />";
		
		if ($loggedUser != "") {
			if ($senderName != "DeepskyLog") {
				echo "<a class=\"btn btn-primary\" href=\"" . $baseURL . "index.php?indexAction=new_message&amp;id=" . $id . "\">" . LangMessageReply . "</a>&nbsp;&nbsp;&nbsp;";
			}
			echo "<a class=\"btn btn-danger\" href=\"" . $baseURL . "index.php?indexAction=validate_delete_message&amp;id=" . $id . "\">" . LangMessageDelete . "</a>";
		}
		echo "</div>";
		echo "<br />";
	} else {
		throw new Exception ( LangNoPermissionToRead );
	}
}
?>
