<?php
// new_message.php
// Write a new message

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else new_message();

function new_message()
{ global $baseURL,$loggedUser,$objObserver,$objMessages,$dateformat,$objPresentations;

  if ($loggedUser != "") {
    if (isset($_GET["id"])) {
      $id = $_GET["id"];
    } else {
      $id = -1;
    }

	  $senderName = $objObserver->getObserverProperty($loggedUser, "firstname") . "&nbsp;" . $objObserver->getObserverProperty($loggedUser, "name");
    
	  echo "<div>";
	  echo "<form class=\"form-horizontal\" role=\"form\" action=\"".$baseURL."index.php\" method=\"post\" enctype=\"multipart/form-data\">";
		echo "<input type=\"hidden\" name=\"indexAction\"   value=\"validate_message\" />";
	  
	  echo "<h4>" . LangNewMessage . "</h4>";
		$content="<button class=\"pull-right btn btn-success\" type=\"submit\" name=\"newmessage\" />" . LangSendMessage . "</button>";
		echo $content;

		if ($id != -1) {
		  $receiverId = $objMessages->getSender($id);
		} else {
		  $receiverId = $_GET['receiver'];
		}
		if ($receiverId == "all") {
		  $receiverName = LangMessageAllDeepskyLogUser; 
		} else {
		  $receiverName = $objObserver->getObserverProperty($receiverId, "firstname") . "&nbsp;" . $objObserver->getObserverProperty($receiverId, "name");
		}
		echo "<input type=\"hidden\" name=\"receiver\" value=\"".$receiverId."\" />";

	  echo "<div class=\"form-group\">";
	  echo "<label class=\"col-sm-2 control-label\">" . LangMessageSender . "</label>";
	  echo "<div class=\"col-sm-5\"><p class=\"form-control-static\">" . $senderName . "</p>";
	  echo "</div></div>";

	  echo "<div class=\"form-group\">";
	  echo "<label class=\"col-sm-2 control-label\">" . LangMessageReceiver . "</label>";
	  echo "<div class=\"col-sm-5\"><p class=\"form-control-static\">" . $receiverName . "</p>";
	  echo "</div></div>";
	   

	  if ($id != -1) {
	    $subject = $objMessages->getReplyToSubject($id);
	  } else {
	    if (isset($_GET["subject"])) {
	      $subject = $_GET["subject"];
	    } else {
	      $subject = "";
	    }
	  }

		$subject=$objPresentations->br2nl(html_entity_decode(preg_replace("/&amp;/", "&",$subject)));
		$subject="<input type=\"text\" required class=\"inputfield requiredField\" maxlength=\"120\" name=\"subject\" size=\"60\" value=\"".$subject."\" />";
		

		echo "<div class=\"form-group\">
	         <label for=\"subject\" class=\"col-sm-2 control-label\">".LangMessageSubject."</label>
	         <div class=\"col-sm-5\">" . $subject . 
          "</div>
	        </div>";
		
		
		if ($id != -1) {
		  $replyToMessage = $objMessages->getReplyToMessage($id);
		} else {
		  $replyToMessage = "";
		}

    $message=$objPresentations->br2nl(html_entity_decode(preg_replace("/&amp;/", "&",$replyToMessage)));
		$contentDescription="<textarea name=\"message\" required class=\"form-control inputfield requiredField\" rows=\"15\">".$message."</textarea>";

		echo "<div class=\"form-group\">
	         <label for=\"subject\" class=\"col-sm-2 control-label\">".LangMessageMessage."</label>
	         <div class=\"col-sm-5\">" . $contentDescription .
			         "</div>
	        </div>";
	  
    echo "</form>";
    echo "</div>";
  } else {
		throw new Exception(LangMessageNotLoggedIn);
  }
}
?>
