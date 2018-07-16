<?php
// new_message.php
// Write a new message
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	new_message ();
function new_message() {
	global $baseURL, $loggedUser, $objObserver, $objMessages, $dateformat, $objPresentations, $instDir;

	if ($loggedUser != "") {
		if (isset ( $_GET ["id"] )) {
			$id = $_GET ["id"];
		} else {
			$id = - 1;
		}

		$senderName = $objObserver->getObserverProperty ( $loggedUser, "firstname" ) . "&nbsp;" . $objObserver->getObserverProperty ( $loggedUser, "name" );

		echo "<div>";
		echo "<form class=\"form-horizontal\" role=\"form\" action=\"" . $baseURL . "index.php\" method=\"post\" enctype=\"multipart/form-data\">";
		echo "<input type=\"hidden\" name=\"indexAction\"   value=\"validate_message\" />";

		echo "<h4>" . LangNewMessage;
		$content = "<button class=\"pull-right btn btn-success\" type=\"submit\" name=\"newmessage\" />" . LangSendMessage . "</button>";
		echo $content;
		echo "</h4>";
		if ($id != - 1) {
			$receiverId = $objMessages->getSender ( $id );
		} else {
			$receiverId = $_GET ['receiver'];
		}
		if ($receiverId == "all") {
			$receiverName = LangMessageAllDeepskyLogUser;
		} else {
			$receiverName = $objObserver->getObserverProperty ( $receiverId, "firstname" ) . "&nbsp;" . $objObserver->getObserverProperty ( $receiverId, "name" );
		}
		echo "<input type=\"hidden\" name=\"receiver\" value=\"" . $receiverId . "\" />";

		echo "<div class=\"col-md-11\">";
		echo "<div class=\"form-group\">";
		echo "<label class=\"col-sm-2 control-label\">" . LangMessageSender . "</label>";
		echo "<div class=\"col-sm-5\"><p class=\"form-control-static\">" . $senderName . "</p>";
		echo "</div></div>";

		echo "<div class=\"form-group\">";
		echo "<label class=\"col-sm-2 control-label\">" . LangMessageReceiver . "</label>";
		echo "<div class=\"col-sm-5\"><p class=\"form-control-static\">" . $receiverName . "</p>";
		echo "</div></div>";

		if ($receiverId == 'all') {
			echo "<div class=\"form-group\">";
			echo "<label class=\"col-sm-2 control-label\">" . _("Send as real mail") . "</label>";
			echo "<div class=\"col-sm-5\"><p class=\"form-control-static\"><input type=\"checkbox\" class=\"inputfield\" name=\"send_mail\"></p>";
			echo "</div></div>";
		}

		if ($id != - 1) {
			$subject = $objMessages->getReplyToSubject ( $id );
		} else {
			if (isset ( $_GET ["subject"] )) {
				$subject = $_GET ["subject"];
			} else {
				$subject = "";
			}
		}

		$subject = $objPresentations->br2nl ( html_entity_decode ( preg_replace ( "/&amp;/", "&", $subject ) ) );
		$subject = "<input type=\"text\" required class=\"inputfield requiredField\" maxlength=\"120\" name=\"subject\" size=\"60\" value=\"" . $subject . "\" />";

		echo "<div class=\"form-group\">
	         <label for=\"subject\" class=\"col-sm-2 control-label\">" . LangMessageSubject . "</label>
	         <div class=\"col-sm-5\">" . $subject . "</div>
	        </div>";

		if ($id != - 1) {
			$replyToMessage = $objMessages->getReplyToMessage ( $id );
		} else {
			$replyToMessage = "";
		}

		$message = $objPresentations->br2nl ( html_entity_decode ( preg_replace ( "/&amp;/", "&", $replyToMessage ) ) );
		$contentDescription = "<textarea maxlength=\"5000\" name=\"message\" required class=\"form-control inputfield requiredField\" rows=\"15\">" . $message . "</textarea>";

		echo "<div class=\"form-group\">
	         <label for=\"subject\" class=\"col-sm-2 control-label\">" . LangMessageMessage . "</label>
	         <div class=\"col-sm-5\">" . $contentDescription . "</div>
	        </div>";

		echo "</form>";
		echo "</div>";

		echo "<div class=\"col-md-1\">";
		$dir = opendir ( $instDir . 'common/observer_pics' );
		while ( FALSE !== ($file = readdir ( $dir )) ) {
			if (("." == $file) or (".." == $file))
				continue; // skip current directory and directory above
			if (fnmatch ( $receiverId . ".gif", $file ) || fnmatch ( $receiverId . ".jpg", $file ) || fnmatch ( $receiverId . ".png", $file )) {
				echo "<br /><br /><img height=\"72\" src=\"" . $baseURL . "/common/observer_pics/" . $file . "\" class=\"img-rounded pull-right\">";
			}
		}
		echo "</div>";

		echo "</div>";
	} else {
		throw new Exception ( LangMessageNotLoggedIn );
	}
}
?>
