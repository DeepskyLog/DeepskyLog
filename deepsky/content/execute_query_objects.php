<?php
// execute_query_objects.php
// executes the object query passed by setup_query_objects.php

$showPartOfs = 0;
if(array_key_exists('showPartOfs',$_GET) && $_GET['showPartOfs'])
  $showPartOfs = $_GET['showPartOfs'];
if(count($_SESSION['Qobj'])>1) //=============================================== valid result, multiple objects found
{ echo "<div id=\"main\">";
  $title=LangSelectedObjectsTitle;
	if($showPartOfs)	
	  $title.=LangListQueryObjectsMessage10;
	else
    $title.=LangListQueryObjectsMessage11;
  if(array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id']&&
	   array_key_exists('listname',$_SESSION)&&$_SESSION['listname']&&($_SESSION['listname']<>"----------")&&$myList)
    $title.="&nbsp;-&nbsp;<a href=\"".$link."&amp;min=".$min."&amp;addAllObjectsFromQueryToList=true\" title=\"".LangListQueryObjectsMessage5.$listname_ss."\">".LangListQueryObjectsMessage4."</a>";
  tablePageTitle($title, $link, $_SESSION['Qobj'], $min, $max);
	if($showPartOfs)
    echo "<a href=\"".$link."&amp;showPartOfs=0\">".LangListQueryObjectsMessage12."</a>";
	else
    echo "<a href=\"".$link."&amp;showPartOfs=1\">".LangListQueryObjectsMessage13."</a>";
	$link.="&amp;showPartOfs=".$showPartOfs;
	echo "<hr />";
  $objObject->showObjects($link, $min, $max);
	echo "<hr />";
  list($min,$max)=$objUtil->printNewListHeader($_SESSION['Qobj'],$link,$min,25,'');	
  $objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
	echo "&nbsp;-&nbsp;";
  $objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b);
	echo " &nbsp;-&nbsp;";
  $objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=".$_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c);
  echo "&nbsp;-&nbsp";									 
  echo "<a href=\"".$baseURL."objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a>";
	echo "&nbsp;-&nbsp;";
  if(array_key_exists('listname',$_SESSION)&&$_SESSION['listname']&&$myList)
    echo "<a href=\"".$link."&amp;min=".$min."&amp;addAllObjectsFromQueryToList=true\" title=\"".LangListQueryObjectsMessage5.$_SESSION['listname']."\">".LangListQueryObjectsMessage4."</a>"."&nbsp;-&nbsp;";
  echo "<a href=\"".$baseURL."objects.csv?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a>";
	echo "<p>";
	echo "<a href=\"".$baseURL."index.php?indexAction=query_objects\">".LangExecuteQueryObjectsMessage1."</a>";
	echo "</div>";
}
elseif(count($_SESSION['Qobj'])==1) // ========================================= 1 object found
{ $_GET['object']=$_SESSION['Qobj'][0]['objectname'];
  include "deepsky/content/view_object.php";
} 
else // ========================================================================no results found
{ echo "<div id=\"main\">";
  echo "<h2>".LangSelectedObjectsTitle."</h2>";
  echo LangExecuteQueryObjectsMessage2;
  echo "<p>";
	echo "<a href=\"".$baseURL."index.php?indexAction=query_objects\">".LangExecuteQueryObjectsMessage2a."</a>";
	echo "</div>";
}

?>
