<?php

$entryMessage='';

if(array_key_exists('addObservationToList',$_GET) && $_GET['addObservationToList'] && $myList)
{ $objList->addObservationToList($_GET['addObservationToList']);
  $entryMessage = LangListQueryObjectsMessage16 . LangListQueryObjectsMessage6 . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $listname_ss . "</a>.";
}

if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
{ $objList->removeObjectFromList($_GET['removeObjectFromList']);
  $entryMessage = LangListQueryObjectsMessage8 . "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($listobjectname) . "\">" . $listobjectname . "</a>" . LangListQueryObjectsMessage7 . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $_SESSION['listname'] . "</a>.";
}
if(array_key_exists('addObjectToList',$_GET) && $_GET['addObjectToList'] && $myList)
{
	$objList->addObjectToList($_GET['addObjectToList'], $_GET['showname']);
  $entryMessage = LangListQueryObjectsMessage8 . "<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['addObjectToList']) . "\">" . $_GET['showname'] . "</a>" . LangListQueryObjectsMessage6 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" . $_SESSION['listname'] . "</a>.";
}
if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
{
	$objList->removeObjectFromList($_GET['removeObjectFromList']);
  $entryMessage = LangListQueryObjectsMessage8 . "<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['removeObjectFromList']) . "\">" . $_GET['removeObjectFromList'] . "</a>" . LangListQueryObjectsMessage7 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" . $_SESSION['listname'] . "</a>.";
}
if(array_key_exists('addAllObjectsFromPageToList',$_GET) && $_GET['addAllObjectsFromPageToList'] && $myList)
{
	$count=$min;
	while(($count<($min+25)) && ($count<count($_SESSION[$_SID])))
	{
		$objList->addObjectToList($_SESSION[$_SID][$count][0],$_SESSION[$_SID][$count][4]);
		$count++;
  }
	$entryMessage = LangListQueryObjectsMessage9 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" .  $_SESSION['listname'] . "</a>.";
}
if(array_key_exists('addAllObjectsFromQueryToList',$_GET) && $_GET['addAllObjectsFromQueryToList'] && $myList)
{
	$count=0;
	while($count<count($_SESSION[$_SID]))
	{
		$objList->addObjectToList($_SESSION[$_SID][$count][0],$_SESSION[$_SID][$count][4]);
		$count++;
  }
	$entryMessage = LangListQueryObjectsMessage9 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" .  $_SESSION['listname'] . "</a>.";
}

?>
