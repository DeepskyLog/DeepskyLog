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
    
	  echo "<div id=\"main\">";
	  echo "<form action=\"".$baseURL."index.php\" method=\"post\" enctype=\"multipart/form-data\"><div>";
		echo "<input type=\"hidden\" name=\"indexAction\"   value=\"validate_message\" />";
	  
		$content="<input type=\"submit\" name=\"newmessage\" value=\"" . "Zend bericht" . "\" />&nbsp;";
		$objPresentations->line(array("<h4>"."Nieuw bericht"."</h4>",$content),"LR",array(80,20),30);

		echo "<hr />";

		echo "<div class=\"inputDiv\">";

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

	  $objPresentations->line(array(LangMessageSender,$senderName,
		                              LangMessageReceiver, $receiverName),
		                        "RLRL",array(10,35,15,35),35,array("fieldname",""));

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
		$subject="<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"120\" name=\"subject\" size=\"60\" value=\"".$subject."\" />";
		
		$objPresentations->line(array(LangMessageSubject,$subject),
		                        "RL",array(10,90),30,array("fieldname",""));

		if ($id != -1) {
		  $replyToMessage = $objMessages->getReplyToMessage($id);
		} else {
		  $replyToMessage = "";
		}

    $message=$objPresentations->br2nl(html_entity_decode(preg_replace("/&amp;/", "&",$replyToMessage)));
		$contentDescription="<textarea name=\"message\" class=\"messageArea inputfield requiredField\" cols=\"1\" rows=\"1\">".$message."</textarea>";
		
	  $objPresentations->line(array(LangMessageMessage,$contentDescription),
		                        "RL",array(10,90),250,array("fieldname",""));
	  
    echo "</div>";
    echo "</div>";
    echo "</form>";
    echo "</div>";
  } else {
		throw new Exception(LangMessageNotLoggedIn);
  }
}
?>
