<?php
// view_object.php
// view all information of one object 

if(!$_GET['object']) // no object defined in url 
  throw new Exception("// no object defined in url, line 6 in view_object.php");
if(!($_GET['object']=$objObject->getDsObjectName($_GET['object'])))
  throw new Exception("// no corresponding object found, line 8 in view_object.php");

$_GET['source']='objects_nearby';
$_GET['zoom']=$GLOBALS['objUtil']->checkGetKey('zoom',30);	
include "deepsky/content/data_get_objects.php";	

$seen=$GLOBALS['objObject']->getDSOseen($_GET['object']);
echo "<div id=\"main\">";
echo "<h2>";
echo LangViewObjectTitle."&nbsp;-&nbsp;".stripslashes($_GET['object']);
echo "&nbsp;-&nbsp;".LangOverviewObjectsHeader7."&nbsp;:&nbsp;".$seen;
echo "</h2>";
echo "<table width=\"100%\"><tr>";
echo "<td width=\"25%\" align=\"left\">";
if(substr($GLOBALS['objObject']->getSeen($_GET['object']),0,1)!='-')
  echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=".urlencode($_GET['object'])."\">".LangViewObjectObservations."&nbsp;".$_GET['object'];
echo "</td>";
echo "<td width=\"25%\" align=\"center\">";
if(array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id'])
  echo "<a href=\"".$baseURL."index.php?indexAction=add_observation&amp;object=".urlencode($_GET['object'])."\">".LangViewObjectAddObservation.$_GET['object']."</a>";
echo "</td>";
if($myList)
{ echo "<td width=\"25%\" align=\"center\">";
  if($objList->checkObjectInMyActiveList($_GET['object']))
    echo "<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['object'])."&amp;removeObjectFromList=".urlencode($_GET['object'])."\">".$_GET['object'].LangListQueryObjectsMessage3.$listname_ss."</a>";
  else
    echo "<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['object'])."&amp;addObjectToList=".urlencode($_GET['object'])."&amp;showname=".urlencode($_GET['object'])."\">".$_GET['object'].LangListQueryObjectsMessage2.$listname_ss."</a>";
  echo "</td>";
}	
echo "</tr>";
echo "</table>";

$objObject->showObject($_GET['object'],$_GET['zoom']);

echo("<form name=\"zoomform\" action=\"".$baseURL."index.php\" method=\"get\">");
echo "<table width=\"100%\">";
echo "<tr>";
echo "<td width=\"50%\">";
echo "<h2> " . LangViewObjectNearbyObjects.(count($_SESSION['Qobj'])-1)."+".$_GET['object']."</h2>";
echo "</td>";
echo "<td width=\"50%\" align=\"right\">";
  echo LangViewObjectNearbyObjectsMoreLess  . ":&nbsp;";
  echo("<select name=\"zoom\"  onchange=\"zoomform.submit();\" width=\"50%\">");
    if ($_GET['zoom']=="180") echo("<option selected value=\"180\">3x3&deg;</option>"); else echo("<option value=\"180\">3x3&deg;</option>"); 
    if ($_GET['zoom']=="120") echo("<option selected value=\"120\">2x2&deg;</option>"); else echo("<option value=\"120\">2x2&deg;</option>"); 
    if ($_GET['zoom']=="60") echo("<option selected value=\"60\">1x1&deg;</option>"); else echo("<option value=\"60\">1x1&deg;</option>"); 
    if ($_GET['zoom']=="30") echo("<option selected value=\"30\">30x30'</option>"); else echo("<option value=\"30\">30x30'</option>"); 
    if ($_GET['zoom']=="15") echo("<option selected value=\"15\">15x15'</option>"); else echo("<option value=\"15\">15x15'</option>"); 
    if ($_GET['zoom']=="10") echo("<option selected value=\"10\">10x10'</option>"); else echo("<option value=\"10\">10x10'</option>"); 
    if ($_GET['zoom']=="5") echo("<option selected value=\"5\">5x5'</option>"); else echo("<option value=\"5\">5x5'</option>"); 
  echo("</select>");
echo "</td>";
echo "</tr>";
echo "</table>";
echo "<input type=\"hidden\" name=\"object\" value=\"".$_GET['object']."\"> ";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\"> ";		
echo "</form>";
$maxcount=count($_SESSION['Qobj']);
$max = 9999;
$link = $baseURL.'index.php?indexAction=detail_object&amp;object='.urlencode($_GET['object']).'&amp;zoom='.$_GET['zoom'].'&amp;SID=Qobj';
list($min, $max) = $objUtil->printNewListHeader($_SESSION['Qobj'],$link ,$min,25,"");
if($max>count($_SESSION['Qobj']))
  $max=count($_SESSION['Qobj']);
$objObject->showObjects($link,'Qobj',$min,$max,$myList,$_GET['object']);
list($min, $max) = $objUtil->printNewListHeader($_SESSION['Qobj'],$link ,$min,25,"");
echo "<hr />";
$objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."deepsky/objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
echo "&nbsp;-&nbsp;";
echo "<a href=\"".$baseURL."deepsky/objects.csv?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a> &nbsp;-&nbsp;";
echo "<a href=\"".$baseURL."deepsky/objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a>";
echo "</div>";

//============================================================================== Admin section permits to change object settings in DB remotely
if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
{
  echo "<hr>";
  echo("<form action=\"".$baseURL."index.php\" method=\"get\">\n");
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
  echo "<select name=\"newcatalogue\">";
  echo "<option value=\"\"></option>"; // empty field
  $catalogs = $objObject->getCatalogues(); // should be sorted
  while(list($key, $value) = each($catalogs))
    echo "<option value=\"$value\">".$value."</option>";
  echo "</select>";		
  echo "<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"newnumber\" size=\"40\" value=\"\"/>";
  echo "<input type=\"submit\" name=\"gonew\" value=\"Go\"/><br />";
  echo "<a href=\"".$baseURL."index.php?indexAction=manage_csv_object\">" . LangNewObjectSubtitle1b . "</a><br />";
  echo "</form>";
}
?>
