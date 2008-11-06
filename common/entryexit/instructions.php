<?php

$entryMessage=''

if(array_key_exists('addObservationToList',$_GET) && $_GET['addObservationToList'] && $myList)
{ $objList->addObservationToList($_GET['addObservationToList']);
  $entryMessage = LangListQueryObjectsMessage16 . LangListQueryObjectsMessage6 . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $listname_ss . "</a>." . "<HR>";
}

if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
{ $objList->removeObjectFromList($_GET['removeObjectFromList']);
  $entryMessage = LangListQueryObjectsMessage8 . "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($listobjectname) . "\">" . $listobjectname . "</a>" . LangListQueryObjectsMessage7 . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $_SESSION['listname'] . "</a>." . "<HR>";
}


?>
