<?php
// view_object.php
// view all information of one object 

include_once "../lib/setup/language.php";
include_once "../lib/util.php";
include_once "../lib/objects.php";
include_once "../lib/lists.php";

$util = new Util();
$util->checkUserInput();
$objects = new Objects; 
$list = new Lists;

if(array_key_exists('listname',$_SESSION) && ($list->checkList($_SESSION['listname'])==2)) $myList=True; else $myList = False;
if(array_key_exists('min',$_GET) && $_GET['min']) $min=$_GET['min']; else $min=0;
$_SID='QOO';

if(!$_GET['object']) // no object defined in url 
  header("Location: ../index.php");
if($objects->getDsObjectName($_GET['object'])) // check whether object exists
{
  if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
  {
    if(array_key_exists("newaction",$_GET))
  	{
    	if($_GET['newaction']=="NewName")
  	  {
  	    $objects->newName($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
  		  $_GET['object'] = trim($_GET['newcatalog'] . " " . ucwords(trim($_GET['newnumber'])));
      }	
    	if($_GET['newaction']=="NewAltName")
  	    $objects->newAltName($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
    	if($_GET['newaction']=="RemoveAltNameName")
  	    $objects->removeAltName($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
    	if($_GET['newaction']=="NewPartOf")
  	    $objects->newPartOf($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
    	if($_GET['newaction']=="RemovePartOf")
  	    $objects->removePartOf($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
    	if($_GET['newaction']=="RemoveAndReplaceObjectBy")
  	  {
  	    $objects->removeAndReplaceObjectBy($_GET['object'], $_GET['newcatalogue'],$_GET['newnumber']);
  		  $_GET['object'] = trim($_GET['newcatalog'] . " " . ucwords(trim($_GET['newnumber'])));
  	  }			
    	if($_GET['newaction']=="LangObjectSetRA")
  	    $objects->setRA($_GET['object'], $_GET['newnumber']);
    	if($_GET['newaction']=="LangObjectSetDECL")
  	    $objects->setDeclination($_GET['object'], $_GET['newnumber']);
    	if($_GET['newaction']=="LangObjectSetCon")
  	    $objects->setConstellation($_GET['object'], $_GET['newnumber']);
    	if($_GET['newaction']=="LangObjectSetType")
  	    $objects->setType($_GET['object'], $_GET['newnumber']);
    	if($_GET['newaction']=="LangObjectSetMag")
  	    $objects->setMagnitude($_GET['object'], $_GET['newnumber']);
     	if($_GET['newaction']=="LangObjectSetSUBR")
  	    $objects->setSurfaceBrightness($_GET['object'], $_GET['newnumber']);
     	if($_GET['newaction']=="LangObjectSetDiam1")
  		  $objects->setDiam1($_GET['object'], $_GET['newnumber']);
     	if($_GET['newaction']=="LangObjectSetDiam2")
  		  $objects->setDiam2($_GET['object'], $_GET['newnumber']);
     	if($_GET['newaction']=="LangObjectSetPA")
  		  $objects->setPositionAngle($_GET['object'], $_GET['newnumber']);
  	}
  }
	if($_GET['editListObjectDescription'])
  { echo "WP";
    $list->setListObjectDescription($_GET['object'], $_GET['description']);
	}
  if(array_key_exists('addObjectToList',$_GET) && $_GET['addObjectToList'] && $myList)
  {
  	$list->addObjectToList($_GET['addObjectToList'], $_GET['showname']);
    echo "The object <a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['addObjectToList']) . "\">" . $_GET['showname'] . "</a> is added to the list <a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" . $_SESSION['listname'] . "</a>.";
  	echo "<HR>";
  }
  if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $myList)
  {
  	$list->removeObjectFromList($_GET['removeObjectFromList']);
    echo "The object <a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['removeObjectFromList']) . "\">" . $_GET['removeObjectFromList'] . "</a> is removed from the list <a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" . $_SESSION['listname'] . "</a>.";
  	echo "<HR>";
  }
	if(array_key_exists('addAllObjectsFromPageToList',$_GET) && $_GET['addAllObjectsFromPageToList'] && $myList)
  {
	  $count=0;
  	while(($count<($min+25)) && ($count<count($_SESSION[$_SID])))
	  {
		  $list->addObjectToList($_SESSION[$_SID][$count][0],$_SESSION[$_SID][$count][4]);
		  $count++;
    }
	echo "The objects have been added to the list <a href=\"deepsky/index.php?indexAction=listaction&amp;manage=manage\">" .  $_SESSION['listname'] . "</a>.";
	echo "<HR>";
  }

	
	
  // SEEN
  $seen = "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
  $seenDetails = $objects->getSeen($_GET['object']);
  if(substr($seenDetails,0,1)=="X") // object has been seen already
  {
    $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
  }
  if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
  {
    if (substr($seenDetails,0,1)=="Y") // object has been seen by the observer logged in
      $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
  }

  echo("<div id=\"main\"><h2>");
  echo (LangViewObjectTitle . "&nbsp;-&nbsp;" . stripslashes($_GET['object']));
  echo "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen;
  echo("</h2>");
	echo "<table width=\"100%\"><tr>";
	echo("<td width=\"25%\" align=\"left\">");
	if($seen!="<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($_GET['object']) . "\" title=\"" . LangObjectNSeen . "\">-</a>")
	  echo("<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($_GET['object']) . "\">" . LangViewObjectObservations . " " . $_GET['object']);
	echo("</td><td width=\"25%\" align=\"center\">");
  if (array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
    echo("<a href=\"deepsky/index.php?indexAction=add_observation&object=" . urlencode($_GET['object']) . "\">" . LangViewObjectAddObservation . $_GET['object'] . "</a>");
	echo("</td>");
	if($myList)
	{
    echo("<td width=\"25%\" align=\"center\">");
    if($list->checkObjectInMyActiveList($_GET['object']))
      echo("<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['object']) . "&amp;removeObjectFromList=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage3 . $_SESSION['listname'] . "</a>");
    else
      echo("<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['object']) . "&amp;addObjectToList=" . urlencode($_GET['object']) . "&amp;showname=" . urlencode($_GET['object']) . "\">" . $_GET['object'] . LangListQueryObjectsMessage2 . $_SESSION['listname'] . "</a>");
	  echo("</td>");
	}	
	echo("</tr>");
	echo("</table>");

	
	if(array_key_exists('zoom',$_GET) && $_GET['zoom']) $zoom=$_GET['zoom'];
  else $zoom=30;
	$objects->showObject($_GET['object'], $zoom);
	if(!array_key_exists('QOO',$_SESSION))
	{
	  $_SESSION[$_SID] = $objects->getOtherObjects($_GET['object'], $zoom);
    $_SESSION[$_SID] = $objects->getSeenObjectDetails($_SESSION[$_SID]);
	}
	echo("<form name=\"zoomform\" action=\"deepsky/index.php\" method=\"get\">");
	  echo "<table width=\"100%\"><tr><td width=\"50%\">";
		echo "<h2> " . LangViewObjectNearbyObjects . (count($_SESSION[$_SID])-1) . "</h2>";
		echo "</td><td width=\"50%\" align=\"right\">";
    echo LangViewObjectNearbyObjectsMoreLess  . ":&nbsp;";
    echo("<select name=\"zoom\"  onchange=\"zoomform.submit();\" width=\"50%\">");
      if ($zoom=="180") echo("<option selected value=\"180\">3x3&deg;</option>"); else echo("<option value=\"180\">3x3&deg;</option>"); 
      if ($zoom=="120") echo("<option selected value=\"120\">2x2&deg;</option>"); else echo("<option value=\"120\">2x2&deg;</option>"); 
      if ($zoom=="60") echo("<option selected value=\"60\">1x1&deg;</option>"); else echo("<option value=\"60\">1x1&deg;</option>"); 
      if ($zoom=="30") echo("<option selected value=\"30\">30x30'</option>"); else echo("<option value=\"30\">30x30'</option>"); 
      if ($zoom=="15") echo("<option selected value=\"15\">15x15'</option>"); else echo("<option value=\"15\">15x15'</option>"); 
      if ($zoom=="10") echo("<option selected value=\"10\">10x10'</option>"); else echo("<option value=\"10\">10x10'</option>"); 
      if ($zoom=="5") echo("<option selected value=\"5\">5x5'</option>"); else echo("<option value=\"5\">5x5'</option>"); 
    echo("</select>");
		echo "</td></tr></table>";
  	echo("<input type=\"hidden\" name=\"object\" value=\"" . $_GET['object'] . "\"> ");
		echo("<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\"> ");		
  echo("</form>");
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

  $maxcount=count($_SESSION[$_SID]);
  $max = 9999;
  if($maxcount>1)
	{
    $link = 'deepsky/index.php?indexAction=detail_object&amp;object=' . $_GET['object'] . '&amp;zoom=' . $zoom . '&amp;SID=' . $_SID;
	  list($min, $max) = $util->printListHeader($_SESSION[$_SID], $link , $min, 25, "");
	  if($max>count($_SESSION[$_SID]))
		  $max=count($_SESSION[$_SID]);
    $objects->showObjects($link, $_SID, $min, $max, $myList, $_GET['object']);
  }
	echo "<hr>";

  if($maxcount>1)
  {
    echo "<a href=\"deepsky/objects.pdf?SID=" . $_SID . "\" target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a> &nbsp;-&nbsp;";
    echo "<a href=\"deepsky/objects.csv?SID=" . $_SID . "\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a> &nbsp;-&nbsp;";
    echo "<a href=\"deepsky/objects.argo?SID=" . $_SID . "\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a>";
	}
  echo("\n</div>\n");
	
}
else // object doesn't exist
  echo LangViewObjectInexistant;
	


if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
{
  echo "<hr>";
  echo("<form action=\"deepsky/index.php\" method=\"get\">\n");
  echo("<input type=\"hidden\" name=\"object\" value=\"" . $_GET['object'] . "\">");
  echo("<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\">");
  echo("<select name=\"newaction\">\n");
  echo("<option value=\"\"></option>"); // empty field
  echo("<option value=\"NewName\">" . LangObjectNewName . "</option>\n");
  echo("<option value=\"NewAltName\">" . LangObjectNewAltName . "</option>\n");
  echo("<option value=\"RemoveAltNameName\">" . LangObjectRemoveAltNameName . "</option>\n");
  echo("<option value=\"NewPartOf\">" . LangObjectNewPartOf . "</option>\n");
  echo("<option value=\"RemovePartOf\">" . LangObjectRemovePartOf . "</option>\n");
  echo("<option value=\"RemoveAndReplaceObjectBy\">" . LangObjectRemoveAndReplaceObjectBy . "</option>\n");
  echo("<option value=\"LangObjectSetRA\">" . LangObjectSetRA . "</option>\n");
  echo("<option value=\"LangObjectSetDECL\">" . LangObjectSetDECL . "</option>\n");
  echo("<option value=\"LangObjectSetCon\">" . LangObjectSetCon . "</option>\n");
  echo("<option value=\"LangObjectSetType\">" . LangObjectSetType . "</option>\n");
  echo("<option value=\"LangObjectSetMag\">" . LangObjectSetMag . "</option>\n");
  echo("<option value=\"LangObjectSetSUBR\">" . LangObjectSetSUBR . "</option>\n");
  echo("<option value=\"LangObjectSetDiam1\">" . LangObjectSetDiam1 . "</option>\n");
  echo("<option value=\"LangObjectSetDiam2\">" . LangObjectSetDiam2 . "</option>\n");
  echo("<option value=\"LangObjectSetPA\">" . LangObjectSetPA . "</option>\n");
  echo("</select>\n");		


  echo("<select name=\"newcatalogue\">\n");
  echo("<option value=\"\"></option>"); // empty field
  $catalogs = $objects->getCatalogues(); // should be sorted
  while(list($key, $value) = each($catalogs))
  {
    echo("<option value=\"$value\">$value</option>\n");
  }
  echo("</select>\n");		
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"newnumber\" size=\"40\" value=\"\" />");
  echo("<input type=\"submit\" name=\"gonew\" value=\"" . "Go" . "\" /><br />");
  echo("<a href=\"deepsky/index.php?indexAction=manage_csv_object\">" . LangNewObjectSubtitle1b . "</a></br>");
  echo("</form>");
}

	
echo("</body>\n</html>");
?>
