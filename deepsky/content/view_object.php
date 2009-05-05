<?php
// view_object.php
// view all information of one object 
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
$seen=$objObject->getDSOseenLink($_GET['object']);
echo "<div id=\"main\">";
echo "<div style=\"position:relative;\">";
echo "<h6 class=\"title\">".LangViewObjectTitle."&nbsp;-&nbsp;".stripslashes($_GET['object'])."&nbsp;-&nbsp;".LangOverviewObjectsHeader7."&nbsp;:&nbsp;".$seen."</h6>";
$topline="";
if(substr($objObject->getSeen($_GET['object']),0,1)!='-')
  $topline.= "&nbsp;-&nbsp;<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=".urlencode($_GET['object'])."\">".LangViewObjectObservations."&nbsp;".$_GET['object']."</a>";
if($loggedUser)
  $topline.= "&nbsp;-&nbsp;<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=add_observation&amp;object=".urlencode($_GET['object'])."\">".LangViewObjectAddObservation.$_GET['object']."</a>";
if($myList)
{ if($objList->checkObjectInMyActiveList($_GET['object']))
    $topline.= "&nbsp;-&nbsp;<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['object'])."&amp;removeObjectFromList=".urlencode($_GET['object'])."\">".$_GET['object'].LangListQueryObjectsMessage3.$listname_ss."</a>";
  else
    $topline.= "&nbsp;-&nbsp;<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['object'])."&amp;addObjectToList=".urlencode($_GET['object'])."&amp;showname=".urlencode($_GET['object'])."\">".$_GET['object'].LangListQueryObjectsMessage2.$listname_ss."</a>";
}	
echo substr($topline,13);
$objObject->showObject($_GET['object'],$objUtil->checkGetKey('zoom',30));
echo"</div>";
$maxcount=count($_SESSION['Qobj']);
$max = 9999;
$link = $baseURL.'index.php?indexAction=detail_object&amp;object='.urlencode($_GET['object']).'&amp;zoom='.$objUtil->checkGetKey('zoom',30).'&amp;SID=Qobj';
echo "<div style=\"position:relative; left:0px; height:65px; width:100%;\">";
echo "<div style=\"position:absolute; left:0px; width:60%;text-align:left;\">";
echo "<h6 class=\"title\">".$_GET['object'];
if(count($_SESSION['Qobj'])>2)
 echo ' '.LangViewObjectAndNearbyObjects.' '.(count($_SESSION['Qobj'])-1).' '.LangViewObjectNearbyObjects;
elseif(count($_SESSION['Qobj'])>1)
 echo ' '.LangViewObjectAndNearbyObjects.' '.(count($_SESSION['Qobj'])-1).' '.LangViewObjectNearbyObject;
else
 echo ' '.LangViewObjectNoNearbyObjects;
echo "</h6>";
echo "<form name=\"zoomform\" action=\"".$link."\" method=\"get\">";
echo LangViewObjectNearbyObjectsMoreLess .":&nbsp;";
echo "<select name=\"zoom\"  onchange=\"zoomform.submit();\" width=\"50%\">";
if($objUtil->checkGetKey('zoom',30)=="180") echo("<option selected value=\"180\">3x3&deg;</option>"); else echo("<option value=\"180\">3x3&deg;</option>"); 
if($objUtil->checkGetKey('zoom',30)=="120") echo("<option selected value=\"120\">2x2&deg;</option>"); else echo("<option value=\"120\">2x2&deg;</option>"); 
if($objUtil->checkGetKey('zoom',30)=="60")  echo("<option selected value=\"60\">1x1&deg;</option>"); else echo("<option value=\"60\">1x1&deg;</option>"); 
if($objUtil->checkGetKey('zoom',30)=="30")  echo("<option selected value=\"30\">30x30'</option>"); else echo("<option value=\"30\">30x30'</option>"); 
if($objUtil->checkGetKey('zoom',30)=="15")  echo("<option selected value=\"15\">15x15'</option>"); else echo("<option value=\"15\">15x15'</option>"); 
if($objUtil->checkGetKey('zoom',30)=="10")  echo("<option selected value=\"10\">10x10'</option>"); else echo("<option value=\"10\">10x10'</option>"); 
if($objUtil->checkGetKey('zoom',30)=="5")   echo("<option selected value=\"5\">5x5'</option>"); else echo("<option value=\"5\">5x5'</option>"); 
echo "</select>";
echo "<input type=\"hidden\" name=\"object\" value=\"".$_GET['object']."\" /> ";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" /> ";		
echo "</form>";
echo "</div>";
  
echo "<div class=\"title\" style=\"position:absolute; right:0px;width:38%;text-align:right;\">";
list($min,$max)=$objUtil->printNewListHeader2($_SESSION['Qobj'],$link ,$min,25,"");
echo "</div>";
echo "</div>";

echo "<div style=\"position:relative; left:0px; width:100%;\">";
if($max>count($_SESSION['Qobj']))
  $max=count($_SESSION['Qobj']);
$_GET['min']=$min;
$_GET['max']=$max;
if($FF)
{ echo "<script type=\"text/javascript\">";
  echo "theResizeElement='obj_list';";
  echo "theResizeSize=80;";
  echo "</script>";
}
$objObject->showObjects($link, $min, $max,$_GET['object']);
	
echo "</div>";

echo "<div style=\"position:relative; left:0px;height:30px;width:100%;\">";
echo "<hr />";
$objPresentations->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
echo "&nbsp;-&nbsp;";
echo "<a href=\"".$baseURL."objects.csv?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a> &nbsp;-&nbsp;";
echo "<a href=\"".$baseURL."objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a>";
echo "</div>";
echo "</div>";

//============================================================================== Admin section permits to change object settings in DB remotely
if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
{ echo "<hr />";
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
  echo "<select name=\"newcatalog\">";
  echo "<option value=\"\"></option>"; // empty field
  $catalogs = $objObject->getCatalogs(); // should be sorted
  while(list($key, $value) = each($catalogs))
    echo "<option value=\"$value\">".$value."</option>";
  echo "</select>";		
  echo "<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"newnumber\" size=\"40\" value=\"\"/>";
  echo "<input type=\"submit\" name=\"gonew\" value=\"Go\"/><br />";
  echo "<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=manage_csv_object\">" . LangNewObjectSubtitle1b . "</a><br />";
  echo "</form>";
}
?>
