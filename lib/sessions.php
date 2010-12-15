<?php 
// sessions.php
// The session class collects all functions needed to add, remove and adapt DeepskyLog sessions from the database.

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class Sessions
{
  // TODO : Check in Eye&Telescope why comment is used...
  // TODO : If comment is just used for comments, also add a title for the session -> Bijv Waarneemreis werkgroep Deepsky 2008 
//{  public  function getNumberOfUnreadMails()
//  { global $objDatabase, $loggedUser;
//  	if($loggedUser) {
//  	  return " (" . count($this->getIdsNewMails($loggedUser)) . "/" . count($this->getIdsAllMails($loggedUser)) . ")";
//  	} else {
//  	  return "";
//  	}
//  }
//  
//  public  function getIdsNewMails($user)
//  { global $objDatabase;
//    $listOfAllMails = $this->getIdsAllMails($user);
//
//    // Read mails should not be counted in the first part
//    $readMails = $objDatabase->selectSingleArray("select id from messagesRead where receiver = \"".$user."\"", "id");
//
//    $cnt = 0;
//
//    for ($i = 0;$i < count($listOfAllMails);$i++) {
//      if (!in_array($listOfAllMails[$i], $readMails)) {
//        $listOfMails[$cnt] = $listOfAllMails[$i];
//        $cnt++;
//      }
//    }
//    if (isset($listOfMails)) {
//      // Swap the array
//      $listOfMails = array_reverse($listOfMails);
//      
//      return $listOfMails;
//    } else {
//      return Array();
//    } 
//  }
//  
//  // Returns a list of all mails. The deleted mails are not included in the list of id's. 
//  public  function getIdsAllMails($user)
//  { global $objDatabase;
//    $listOfAllMails = $objDatabase->selectSingleArray("select id from messages where receiver = \"".$user."\" or receiver = \"all\"", "id");
//
//    $listOfMails = Array();
//
//    // Removed mails should not be counted
//    $removedMails = $objDatabase->selectSingleArray("select id from messagesDeleted where receiver = \"".$user."\"", "id");
//
//    $cnt = 0;
//    
//    for ($i = 0;$i < count($listOfAllMails);$i++) {
//      if (!in_array($listOfAllMails[$i], $removedMails)) {
//        $listOfMails[$cnt] = $listOfAllMails[$i];
//        $cnt++;
//      }
//    }
//    
//    return $listOfMails;
//  }
// 
//  public  function getSubject($id)
//  { global $objDatabase;
//    return $objDatabase->selectSingleValue("select subject from messages where id = \"" . $id . "\"", "subject");
//  }
//
//  public  function getSender($id)
//  { global $objDatabase;
//    return $objDatabase->selectSingleValue("select sender from messages where id = \"" . $id . "\"", "sender");
//  }
//
//  public  function getReceiver($id)
//  { global $objDatabase;
//    return $objDatabase->selectSingleValue("select receiver from messages where id = \"" . $id . "\"", "receiver");
//  }
//
//  public  function getDate($id)
//  { global $objDatabase;
//    return $objDatabase->selectSingleValue("select date from messages where id = \"" . $id . "\"", "date");
//  }
//  
//  public  function getContent($id)
//  { global $objDatabase;
//    return $objDatabase->selectSingleValue("select message from messages where id = \"" . $id . "\"", "message");
//  }
//
//  public  function removeAllMessages($id)
//  { global $objDatabase;
//    if ($id != "") {
//      $allMessages = $this->getIdsAllMails($id);
//      for ($cnt=0;$cnt<count($allMessages);$cnt++) {
//        $objDatabase->execSQL("insert into messagesDeleted VALUES(\"" . $allMessages[$cnt] . "\", \"" . $id . "\")");
//      }
//    }
//  }
//
//  public  function getContentWithoutLinks($id)
//  { global $objDatabase;
//    $message = $objDatabase->selectSingleValue("select message from messages where id = \"" . $id . "\"", "message");
//    return strip_tags($message, '<br>');
//  }
//
//  public  function isRead($id, $receiver)
//  { global $objDatabase;
//    $read = $objDatabase->selectSingleValue("select id from messagesRead where id = \"" . $id . "\" and receiver = \"" . $receiver . "\"", "id");
//    if ($read == "") {
//      return false;
//    } else {
//      return true;
//    }
//  }
//
//  public  function isDeleted($id, $receiver)
//  { global $objDatabase;
//    $deleted = $objDatabase->selectSingleValue("select id from messagesDeleted where id = \"" . $id . "\" and receiver = \"" . $receiver . "\"", "id");
//    if ($deleted == "") {
//      return false;
//    } else {
//      return true;
//    }
//  }
//  
//  public  function markMessageRead($id, $receiver)
//  { global $objDatabase;
//    $objDatabase->execSQL("insert into messagesRead VALUES(\"" . $id . "\", \"" . $receiver . "\")");
//  }
//
//  public  function validateDeleteMessage()
//  { global $objDatabase, $loggedUser;
//    if ($loggedUser != "") {
//      $id = $_GET["id"];
//      $objDatabase->execSQL("insert into messagesDeleted VALUES(\"" . $id . "\", \"" . $loggedUser . "\")");
//    }
//  }
//  
//  public  function getReplyToSubject($id) 
//  { $subject = $this->getSubject($id);
//    if (strpos($subject, "Re : ") === false) {
//      return "Re : " . $subject;
//    } else {
//      return $subject;
//    }
//  }
//
//  public  function getReplyToMessage($id)
//  { $message = $this->getContent($id);
//    // Replace <br /> by <br />> 
//    $message = str_replace("<br />", "", $message);
//    $message = str_replace("\n", "\n> ", $message);
//    return "> " . $message;
//  }
//
//  public  function validateMessage() 
//  { global $loggedUser;
//		if(!($loggedUser))
//			throw new Exception(LangMessageNotLoggedIn);
//
//		$this->sendMessage($loggedUser, $_POST['receiver'], $_POST['subject'], nl2br($_POST['message']));
//  }
//
//  // Returns a list of all read mails.  
//  public  function getIdsReadMails($user)
//  { global $objDatabase;
//    $listOfAllMails = $this->getIdsAllMails($user);
//    $listOfNewMails = $this->getIdsNewMails($user);
//    
//    $listOfReadMails = Array();
//
//    $cnt = 0;
//    
//    for ($i = 0;$i < count($listOfAllMails);$i++) {
//      if (!in_array($listOfAllMails[$i], $listOfNewMails)) {
//        $listOfReadMails[$cnt] = $listOfAllMails[$i];
//        $cnt++;
//      }
//    }
//    
//    // Swap the array
//    $listOfReadMails = array_reverse($listOfReadMails);
//
//    return $listOfReadMails;
//  }
//  
//  public  function sendMessage($sender, $receiver, $subject, $message)
//  { global $objDatabase;
//    // Make sure not to insert bad code in the database
//    $subject = html_entity_decode($subject, ENT_COMPAT, "ISO-8859-15");
//		$subject = preg_replace("/(\")/", "", $subject);
//		$subject = preg_replace("/;/", ",", $subject);
//    
////    $message = html_entity_decode($message, ENT_COMPAT, "ISO-8859-15");
////		$message = preg_replace("/(\")/", "", $message);
////		$message = preg_replace("/;/", ",", $message);
//
//		$date = $mysqldate = date('Y-m-d H:i:s');
//		
//		$objDatabase->execSQL("INSERT into messages (sender, receiver, subject, message, date) VALUES(\"" . $sender . "\", \"" . $receiver . "\", \"" . $subject . "\", '" . $message . "', \"" . $date . "\")");
//  }
//
//  public  function showListMails($newMails, $readMails, $min, $max, $link2, $step=25) 
//	{ global $baseURL, $baseURL, $objPresentations, $objObserver, $dateformat, $loggedUser; 
//    echo "<table id=\"showListMailsTable\">\n";
//    echo "<tr class=\"type30\">";
//
//    // Making the header for the mails
//    echo "<td class=\"verticalaligntop;\">";         
//	  echo "<table><tr>";
//	  echo "<td class=\"centered width100pct\">";           
//	  echo LangMessageSubject;
//	  echo "</td></tr>";
//	  echo "</table>";
//	  echo "</td>";        
//    
//    echo "<td class=\"verticalaligntop;\">";         
//	  echo "<table>";
//	  echo "<tr><td class=\"centered width100pct\">";           
//	  echo LangMessageSender;
//	  echo "</td></tr>";        
//	  echo "</table>";
//	  echo "</td>";
//
//	  echo "<td class=\"verticalaligntop;\">";         
//	  echo "<table>";
//	  echo "<tr><td class=\"centered width100pct\">";           
//	  echo LangMessageDate;
//	  echo "</td></tr>";        
//	  echo "</table>";
//	  echo "</td>";        
//	  
//		echo "</tr>";
//
//		$countline = 0; // counter for altering table colors
//
//		// Combining all mails
//		$allMails = array_merge($newMails, $readMails);
//
//		// Showing the mails, loop over the id's of the combined array of new and read mails.
//	  for ($cnt = 0;$cnt < count($allMails);$cnt++)
//		{
//		  if ($cnt >= $min && $cnt < $max) {
//		    $countline++;
//		    // Use the different colors for different lines, also make new mails green
//		    if ($loggedUser == "") {
//		      if ($countline % 2 == 0) {
//		        echo "<tr class=\"height5px type20\">";
//		      } else {
//		        echo "<tr class=\"height5px type10\">";
//		      }
//	      } else {
//	        if (in_array($allMails[$cnt], $readMails)) {
//	          if ($countline % 2 == 0) {
//	            echo "<tr class=\"height5px type20\">";
//	          } else {
//	            echo "<tr class=\"height5px type10\">";
//	          }
//	        } else {
//	          // New mails are shown on a green background
//	          echo "<tr class=\"height5px typeGreen\">";
//	        }
//	      }
//	      echo "<td>";
//
//			  echo "<a href = \"" . $baseURL . "index.php?indexAction=view_message&amp;id=" . $allMails[$cnt] . "\">" . $this->getSubject($allMails[$cnt]) . "</a>";
//			  echo "</td>";
//			  $senderId = $this->getSender($allMails[$cnt]);
//			  if ($senderId == "DeepskyLog") {
//			    $senderName = $senderId;
//			  } else {
//			    $senderName = $objObserver->getObserverProperty($senderId, "firstname") . "&nbsp;" . $objObserver->getObserverProperty($senderId, "name");
//			    $senderName = "<a href=\"" . $baseURL . "index.php?indexAction=detail_observer&amp;user=" . $senderId . "\">" . $senderName . "</a>";
//			  }
//			  echo "<td>" . $senderName . "</td>";
//
//			  // Use the date format from databaseInfo
//			  $phpdate = strtotime($this->getDate($allMails[$cnt]));
//			  echo "<td>" . date($dateformat . " G:i:s", $phpdate) . "</td>";
//			
//			  echo "</tr>";
//		  }
//		}
//		echo "</table>";
//	}
}
?>
