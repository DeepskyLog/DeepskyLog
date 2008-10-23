<?php

// execute_query_objects.php
// executes the object query passed by setup_query_objects.php
// version 0.5: 2005/06/29, JV
// $$ ok

// Code cleanup - removed by David on 20080704
//include_once "../common/control/dec_to_dm.php";
//include_once "../common/control/ra_to_hms.php";
//include_once "../lib/observers.php";
//$observer = new Observers;

include_once "../lib/lists.php";
include_once "../lib/objects.php";
include_once "../lib/setup/language.php";
include_once "../lib/util.php";

global $baseURL;

$objects = new Objects;
$util = new util;
$util->checkUserInput();
$list = new Lists;
$myList = False;
if(array_key_exists('listname',$_SESSION) && ($list->checkList($_SESSION['listname'])==2))
  $myList=True;
if(array_key_exists('min',$_GET) && $_GET['min'])
 $min=$_GET['min'];
else
 $min=0;
$showPartOfs = 0;
$name='';
$exact = 0;
$_SID='QO';
if(array_key_exists('showPartOfs',$_GET) && $_GET['showPartOfs'])
  $showPartOfs = $_GET['showPartOfs'];
if($showPartOfs=="1")
{  
  if(!array_key_exists('QOP',$_SESSION))
		$_SESSION['QOP']=$objects->getPartOfObjects($_SESSION['QO']);
  $_SID='QOP';
}	  
if(array_key_exists('addObjectToList',$_GET) && $_GET['addObjectToList'] && $myList)
{
	$list->addObjectToList($_GET['addObjectToList'], $_GET['showname']);
  echo LangListQueryObjectsMessage8 . "<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['addObjectToList']) . "\">" . $_GET['showname'] . "</a>" . LangListQueryObjectsMessage6 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" . $_SESSION['listname'] . "</a>.";
	echo "<HR>";
}
if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
{
	$list->removeObjectFromList($_GET['removeObjectFromList']);
  echo LangListQueryObjectsMessage8 . "<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['removeObjectFromList']) . "\">" . $_GET['removeObjectFromList'] . "</a>" . LangListQueryObjectsMessage7 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" . $_SESSION['listname'] . "</a>.";
	echo "<HR>";
}
if(array_key_exists('addAllObjectsFromPageToList',$_GET) && $_GET['addAllObjectsFromPageToList'] && $myList)
{
	$count=$min;
	while(($count<($min+25)) && ($count<count($_SESSION[$_SID])))
	{
		$list->addObjectToList($_SESSION[$_SID][$count][0],$_SESSION[$_SID][$count][4]);
		$count++;
  }
	echo LangListQueryObjectsMessage9 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" .  $_SESSION['listname'] . "</a>.";
	echo "<HR>";
}
if(array_key_exists('addAllObjectsFromQueryToList',$_GET) && $_GET['addAllObjectsFromQueryToList'] && $myList)
{
	$count=0;
	while($count<count($_SESSION[$_SID]))
	{
		$list->addObjectToList($_SESSION[$_SID][$count][0],$_SESSION[$_SID][$count][4]);
		$count++;
  }
	echo LangListQueryObjectsMessage9 . "<a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" .  $_SESSION['listname'] . "</a>.";
	echo "<HR>";
}
$sort='';
if(array_key_exists('SO',$_GET) && (count($_SESSION[$_SID])>1))
{
  $sort = "showname";
  // SORTING
  if($_GET['SO']) // field to sort on given as a parameter in the url
    $sort = $_GET['SO'];
  $_SESSION[$_SID] = $objects->sortObjects($_SESSION[$_SID], $sort);
}
if(array_key_exists('RO',$_GET) && (count($_SESSION[$_SID])>1))
{
  $sort = "showname";
  // SORTING
  if($_GET['RO']) // field to sort on given as a parameter in the url
    $sort = $_GET['RO'];
  $_SESSION[$_SID] = $objects->sortObjects($_SESSION[$_SID], $sort);
  $_SESSION[$_SID] = array_reverse($_SESSION[$_SID], false); 
}	
if(count($_SESSION[$_SID]) > 1) // valid result
{
  $link = 'deepsky/index.php?indexAction=result_query_objects&amp;SID=' . $_SID;

	// PAGE TITLE
  echo("<div id=\"main\">\n<h2>");
  echo LangSelectedObjectsTitle; // page title
	if($showPartOfs)	
	  echo LangListQueryObjectsMessage10;
	else
    echo LangListQueryObjectsMessage11;
  if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] &&
	   array_key_exists("listname",$_SESSION) && $_SESSION['listname'] && $_SESSION['listname'] && ($_SESSION['listname']<>"----------") && $myList)
    echo(" - <a href=\"" . $link . "&amp;min=" . $min . "&amp;addAllObjectsFromQueryToList=true\" title=\"" . LangListQueryObjectsMessage5 . $_SESSION['listname'] . "\">"  . LangListQueryObjectsMessage4 . "</a>");
	echo("</h2>\n");
	if($showPartOfs)
    echo("<a href=\"" . $link . "&amp;showPartOfs=" . 0 ."\">" . LangListQueryObjectsMessage12 . "</a>");
	else
    echo("<a href=\"" . $link . "&amp;showPartOfs=" . 1 . "\">" . LangListQueryObjectsMessage13 . "</a>");
	$link .= "&amp;showPartOfs=" . $showPartOfs;

  list($min, $max) = $util->printListHeader($_SESSION[$_SID], $link , $min, 25, "");
	if($max>count($_SESSION[$_SID]))
		$max=count($_SESSION[$_SID]);
	echo "<HR>";
  // OUTPUT RESULT
  $objects->showObjects($link, $_SID, $min, $max, $myList);
	echo("<hr>");

  list($min, $max) = $util->printListHeader($_SESSION[$_SID], $link, $min, 25, "");	
	
  echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . "," . LangListQueryObjectsMessage15 . ");
								           location.href='" . $baseURL . "deepsky/objects.pdf?SID=" . $_SID . "&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a> &nbsp;-&nbsp;";
  echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . "," . LangListQueryObjectsMessage15 . ");
								           location.href='" . $baseURL . "deepsky/objectnames.pdf?SID=" . $_SID . "&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4b."</a> &nbsp;-&nbsp;";
  echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . "," . LangListQueryObjectsMessage15 . ");
								           location.href='" . $baseURL . "deepsky/objectsDetails.pdf?SID=" . $_SID . "&amp;sort=" . $sort . "&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4c."</a> &nbsp;-&nbsp;";
  echo "<a href=\"deepsky/objects.argo?SID=".$_SID."\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a> &nbsp;-&nbsp;";
  if(array_key_exists('listname',$_SESSION) && $_SESSION['listname'] && $myList)
    echo "<a href=\"" . $link . "&amp;min=" . $min . "&amp;addAllObjectsFromQueryToList=true\" title=\"" . LangListQueryObjectsMessage5 . $_SESSION['listname'] . "\">"
         .LangListQueryObjectsMessage4."</a> &nbsp;-&nbsp;";
  echo "<a href=\"deepsky/objects.csv?SID=".$_SID."\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a>";
	echo "<p><a href=\"deepsky/index.php?indexAction=query_objects\">".LangExecuteQueryObjectsMessage1."</a>";
	echo "</div>\n</body>\n</html>";
}
elseif(count($_SESSION[$_SID]) == 1)
{ 
  $_GET['object'] =  $_SESSION[$_SID][0][0];
  include "view_object.php";
} 
else // no results found
{
  // PAGE TITLE
  echo("<div id=\"main\">\n<h2>");
  echo LangSelectedObjectsTitle; // page title
  echo("</h2>\n");
  echo(LangExecuteQueryObjectsMessage2);
  echo("<p><a href=\"deepsky/index.php?indexAction=query_objects\">");
  echo(LangExecuteQueryObjectsMessage2a . "</a>");
}

?>
