<?php
// execute_query_objects.php
// executes the object query passed by setup_query_objects.php

$showPartOfs = 0;
$_SID='Qobj';
if(array_key_exists('showPartOfs',$_GET) && $_GET['showPartOfs'])
  $showPartOfs = $_GET['showPartOfs'];
if($showPartOfs=="1")
{ if(!array_key_exists('QOP',$_SESSION))
		$_SESSION['QOP']=$objObject->getPartOfObjects($_SESSION['Qobj']);
  $_SID='QOP';
}	  
if($entryMessage)
  echo $entryMessage.'<hr>';
if(count($_SESSION[$_SID]) > 1) // valid result
{ echo"<div id=\"main\">";
	echo"<table width=\"100%\">";
	echo"<tr>";
	echo"<td>";
	echo"<h2>";
  echo LangSelectedObjectsTitle; // page title
	if($showPartOfs)	
	  echo LangListQueryObjectsMessage10;
	else
    echo LangListQueryObjectsMessage11;
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] &&
	   array_key_exists("listname",$_SESSION) && $_SESSION['listname'] && $_SESSION['listname'] && ($_SESSION['listname']<>"----------") && $myList)
    echo(" - <a href=\"" . $link . "&amp;min=" . $min . "&amp;addAllObjectsFromQueryToList=true\" title=\"" . LangListQueryObjectsMessage5 . $_SESSION['listname'] . "\">"  . LangListQueryObjectsMessage4 . "</a>");
	echo("</h2>\n");
	echo"</td>";
	echo"<td align=\"right\">";
  list($min, $max) = $objUtil->printNewListHeader($_SESSION[$_SID], $link, $min, 25, "");	
	echo"</td>";
	echo"</table>";
	if($showPartOfs)
    echo("<a href=\"" . $link . "&amp;showPartOfs=" . 0 ."\">" . LangListQueryObjectsMessage12 . "</a>");
	else
    echo("<a href=\"" . $link . "&amp;showPartOfs=" . 1 . "\">" . LangListQueryObjectsMessage13 . "</a>");
	$link .= "&amp;showPartOfs=" . $showPartOfs;
	echo "<HR>";
  $objObject->showObjects($link, 'Qobj', $min, $max, $myList);
	echo("<hr>");
  list($min, $max) = $objUtil->printNewListHeader($_SESSION[$_SID], $link, $min, 25, "");	
  $objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."deepsky/objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
	echo "&nbsp;-&nbsp;";
  $objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."deepsky/objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b);
	echo " &nbsp;-&nbsp;";
  $objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."deepsky/objectsDetails.pdf?SID=Qobj&amp;sort=".$_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c);
  echo "&nbsp;-&nbsp";									 
  echo "<a href=\"deepsky/objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a>";
	echo "&nbsp;-&nbsp;";
  if(array_key_exists('listname',$_SESSION) && $_SESSION['listname'] && $myList)
    echo "<a href=\"" . $link . "&amp;min=" . $min . "&amp;addAllObjectsFromQueryToList=true\" title=\"" . LangListQueryObjectsMessage5 . $_SESSION['listname'] . "\">"
         .LangListQueryObjectsMessage4."</a> &nbsp;-&nbsp;";
  echo "<a href=\"deepsky/objects.csv?SID=".$_SID."\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a>";
	echo "<p><a href=\"deepsky/index.php?indexAction=query_objects\">".LangExecuteQueryObjectsMessage1."</a>";
	echo "</div>\n</body>\n</html>";
}
elseif(count($_SESSION['Qobj']) == 1)
{ $_GET['object'] =  $_SESSION['Qobj'][0]['name'];
  include "view_object.php";
} 
else // no results found
{ echo("<div id=\"main\">\n<h2>");
  echo LangSelectedObjectsTitle; // page title
  echo("</h2>\n");
  echo(LangExecuteQueryObjectsMessage2);
  echo("<p><a href=\"deepsky/index.php?indexAction=query_objects\">");
  echo(LangExecuteQueryObjectsMessage2a . "</a>");
}

?>
