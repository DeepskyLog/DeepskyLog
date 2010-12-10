<?php 
// view_message.php
// generates an overview of all messages

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else view_message();

function view_message()
{ global $baseURL,$loggedUser,$objObserver,$objMessages,$dateformat,$objPresentations;
	$id = $_GET["id"];

	// Here we check whether the logged in user has the permission to see the message. 
	$validMail = false;
	if (($objMessages->getReceiver($id) == "all") || $objMessages->getReceiver($id) == $loggedUser) {
	  $validMail = true;
	}
	if ($validMail && $objMessages->isDeleted($id, $loggedUser)) {
	  $validMail = false;
	}
	if ($validMail) {
	  echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";

	  echo "<div id=\"main\">";

	  // Check whether the mail is already read
	  if ($loggedUser != "") {
	    if (!$objMessages->isRead($id, $loggedUser)) {
	      // Mark the message as read
  	    $objMessages->markMessageRead($id, $loggedUser);
	    }
	  }
	  echo "<h1>" . $objMessages->getSubject($id) . "</h1>";
	
	  $senderId = $objMessages->getSender($id);
	  if ($senderId == "DeepskyLog") {
	    $senderName = $senderId;
	  } else {
	    $senderName = $objObserver->getObserverProperty($senderId, "firstname") . "&nbsp;" . $objObserver->getObserverProperty($senderId, "name");
	  }

	  // Use the date format from databaseInfo
	  $phpdate = strtotime($objMessages->getDate($id));
	
	  echo "<h2>" . LangMessageBy . $senderName . " - " . date($dateformat . " G:i:s", $phpdate) . "</h2>";

	  echo "<hr />";
	
	  // When the user is not logged in, it should not be possibleto have links in the mails
	  if ($loggedUser == "") {
	    echo $objMessages->getContentWithoutLinks($id);
	  } else {
	    echo $objPresentations->searchAndLinkCatalogsInText($objMessages->getContent($id));
	  }
	  
	  echo "<hr />";

      if ($loggedUser != "") {
	    if ($senderName != "DeepskyLog") {
	      echo "<a href=\"" . $baseURL . "index.php?indexAction=new_message&amp;id=" . $id . "\">" . LangMessageReply . "</a> - ";
	    }
	    echo "<a href=\"". $baseURL . "index.php?indexAction=validate_delete_message&amp;id=" . $id . "\">" . LangMessageDelete . "</a>";
      }
	  echo "</div>";
	} else {
		throw new Exception(LangNoPermissionToRead);
	}
}
?>
