<?php 
// messages.php
// generates an overview of all messages

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else messages();

function messages()
{ global $baseURL,$FF,$loggedUser,$myList,$step,
         $objObserver,$objPresentations,$objUtil,$objMessages;

  $link2 = $baseURL . "index.php?indexAction=show_messages";
	reset($_GET);
         
	while (list ($key, $value) = each($_GET))
	  if (!in_array($key, array (
			'indexAction',
			'multiplepagenr',
			'min')))
	    $link2 .= "&amp;" . $key . "=" . urlencode($value);

	if((array_key_exists('steps',$_SESSION))&&(array_key_exists("messages",$_SESSION['steps'])))
	  $step=$_SESSION['steps']["messages"];
	    
	echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";

	echo "<div id=\"main\">";

	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;

	$content1 = "<h4>" . LangViewMessages . " (" . $objMessages->getNumberOfUnreadMails() . ")</h4>";

	$newMails = $objMessages->getIdsNewMails($loggedUser);

	$readMails = $objMessages->getIdsReadMails($loggedUser);

	// Combining all mails
	$allMails = array_merge($newMails, $readMails);
	
	// Make a header and make it possible to move through the pages
	list($min, $max,$content2,$pageleft,$pageright,$pagemax)=$objUtil->printNewListHeader4($allMails, $link2, $min, $step);
  $objPresentations->line(array($content1,$content2),"LR",array(50,50),30);

  // Change the number of steps
  $content4=$objUtil->printStepsPerPage3($link2,"messages",$step);
  $objPresentations->line(array("",$content4),"LR",array(50,50),25);
  
  // Show the mails
	$objMessages->showListMails($newMails, $readMails, $min, $max, $link2);

	echo "</div>";
}
?>
