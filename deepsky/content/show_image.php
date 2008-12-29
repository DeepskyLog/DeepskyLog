<?php
// show_image.php
// shows the DSS image of an object 

$_GET['object'] = $_POST['name'];
if(array_key_exists('listname',$_SESSION) && ($objList->checkList($_SESSION['listname'])==2)) $myList=True; else $myList = False;
if(array_key_exists('min',$_GET) && $_GET['min']) $min=$_GET['min']; else $min=0;
if(array_key_exists('SID',$_GET) && $_GET['SID']) $_SID=$_GET['SID']; else	$_SID=time();
if(!$_GET['object']) // no object defined in url 
{ throw new Exception("No object defined in url in show_image.php");
}
if($objObject->getDsoProperty($objObject->getDsObjectName($_GET['object']),'ra')!="") // check whether object exists
{ // SEEN
  $seen = "<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
  $seenDetails = $objObject->getSeen($_GET['object']);
  if(substr($seenDetails,0,1)=="X") // object has been seen already
  {
    $seen = "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
  }
  if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
  {
    if (substr($seenDetails,0,1)=="Y") // object has been seen by the observer logged in
      $seen = "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
  }

  echo("<div id=\"main\"><h2>");
  echo (LangViewObjectTitle . "&nbsp;-&nbsp;" . stripslashes($_GET['object']));
  echo "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen;
  echo("</h2>");
	echo "<table width=\"100%\"><tr>";
	echo("<td width=\"25%\" align=\"left\">");
	if($seen!="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectNSeen . "\">-</a>")
	  echo("<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($_GET['object']) . "\">" . LangViewObjectObservations . " " . $_GET['object']);
	echo("</td><td width=\"25%\" align=\"center\">");
  if (array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
    echo("<a href=\"".$baseURL."index.php?indexAction=add_observation&amp;object=" . urlencode($_GET['object']) . "\">" . LangViewObjectAddObservation . $_GET['object'] . "</a>");
	echo("</td>");
	if($myList)
	{
    echo("<td width=\"25%\" align=\"center\">");
    if($objList->checkObjectInMyActiveList($_GET['object']))
      echo("<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['object']) . "&amp;removeObjectFromList=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "</a>");
    else
      echo("<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['object']) . "&amp;addObjectToList=" . urlencode($_GET['object']) . "&amp;showname=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "</a>");
	  echo("</td>");
	}	
	echo("</tr>");
	echo("</table>");
	if(array_key_exists('zoom',$_GET) && $_GET['zoom']) $zoom=$_GET['zoom'];
  else $zoom=30;
	$objObject->showObject($_GET['object'], $zoom);
  echo("<div id=\"main\">\n<h2>");
  echo LangViewDSSImageTitle . $_POST['name'];
  echo "&nbsp;(" . $_POST['imagesize'] . "&#39;&nbsp;x&nbsp;" . $_POST['imagesize'] . "&#39;)</h2>";
  $_SESSION['imagelink'] = ("http://archive.stsci.edu/cgi-bin/dss_search?v=poss2ukstu_red&amp;r=" . $_POST['raDSS'] . ".0&amp;d=" . $_POST['declDSS'] . "&amp;e=J2000&amp;h=" . $_POST['imagesize'] . ".0&amp;w=" . $_POST['imagesize'] . "&amp;f=gif&amp;c=none&amp;fov=NONE&amp;v3=");
  echo "<p style=\"text-align:center\"> <img src=\"".$_SESSION['imagelink']."\" alt=\"".$_POST['name']."\" width=\"495\" height=\"495\"></img> </p>";
  echo "<p>&copy;&nbsp;<a href=\"http://archive.stsci.edu/dss/index.html\">STScI Digitized Sky Survey</a></p>";
}
echo "</div>";

?>
