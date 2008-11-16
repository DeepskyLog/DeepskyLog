<?php
// execute_query_objects.php
// executes the object query passed by setup_query_objects.php

$showPartOfs = 0;
$name='';
$exact = 0;
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
{
  //$link = 'deepsky/index.php?indexAction=result_query_objects&amp;SID=' . $_SID;

	// PAGE TITLE
  echo"<div id=\"main\">";
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

	if($max>count($_SESSION[$_SID]))
		$max=count($_SESSION[$_SID]);
	echo "<HR>";
  // OUTPUT RESULT
  $objObject->showObjects($link, $_SID, $min, $max, $myList);
	echo("<hr>");

  list($min, $max) = $objUtil->printNewListHeader($_SESSION[$_SID], $link, $min, 25, "");	
	
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
