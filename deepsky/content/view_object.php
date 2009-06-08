<?php  // view_object.php - view all information of one object 
if(!($object=$objUtil->checkGetKey('object')))
  throw new Exception('To implement');
else
{
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
$seen=$objObject->getDSOseenLink($object);
echo "<div id=\"main\">";
$object_ss = stripslashes($object);
$seen = "<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=detail_object&amp;object=" . urlencode($object) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
$seenDetails = $objObject->getSeen($object);
if (substr($seenDetails, 0, 1) == "X")
	$seen = "<a target=\"_top\" href=\"" .
	$baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
if (array_key_exists("deepskylog_id", $_SESSION) && $_SESSION["deepskylog_id"])
	if (substr($seenDetails, 0, 1) == "Y")
		$seen = "<a target=\"_top\" href=\"" .
		$baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
$objPresentations->line(array("<h4>".LangViewObjectTitle."&nbsp;-&nbsp;".$object_ss."&nbsp;-&nbsp;".LangOverviewObjectsHeader7."&nbsp;:&nbsp;".$seen."</h4>"),
                        "L",array(100),30);
  $topline="";
if(substr($objObject->getSeen($object),0,1)!='-')
  $topline.= "&nbsp;-&nbsp;<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."\">".LangViewObjectObservations."&nbsp;".$object_ss."</a>";
if($loggedUser)
	$topline.="&nbsp;-&nbsp;"."<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=add_observation&amp;object=" . urlencode($object) . "\">" . LangViewObjectAddObservation . $object_ss . "</a>";
if ($myList) 
{ if ($objList->checkObjectInMyActiveList($object))
		$topline.="&nbsp;-&nbsp;"."<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a>";
	else
		$topline.="&nbsp;-&nbsp;"."<a target=\"_top\" href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a>";
}
$topline.="&nbsp;-&nbsp;".$objPresentations->getDSSDeepskyLiveLinks($object);
$objPresentations->line(array(substr($topline,13)),"L",array(100),20);
echo "<hr />";
$objObject->showObject($object);

if(!($imagesize=$objUtil->checkRequestKey('imagesize')))
{ $maxcount=count($_SESSION['Qobj']);
	$max = 9999;
	
	if((array_key_exists('steps',$_SESSION))&&(array_key_exists("nearbyObjects",$_SESSION['steps'])))
	  $step=$_SESSION['steps']["nearbyObjects"];
	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;
	
	$link = $baseURL.'index.php?indexAction=detail_object&amp;object='.urlencode($_GET['object']).'&amp;zoom='.$objUtil->checkGetKey('zoom',30).'&amp;SID=Qobj';
	
	$content1 ="<h4>".$_GET['object'];
	if(count($_SESSION['Qobj'])>2)
	 $content1.=' '.LangViewObjectAndNearbyObjects.' '.(count($_SESSION['Qobj'])-1).' '.LangViewObjectNearbyObjects;
	elseif(count($_SESSION['Qobj'])>1)
	 $content1.=' '.LangViewObjectAndNearbyObjects.' '.(count($_SESSION['Qobj'])-1).' '.LangViewObjectNearbyObject;
	else
	 $content1.=' '.LangViewObjectNoNearbyObjects;
	$content1.="</h4>";
	list($min,$max,$content2)=$objUtil->printNewListHeader3($_SESSION['Qobj'],$link ,$min,$step);
	$objPresentations->line(array($content1,$content2),"LR",array(75,25),30);
  $content1 ="<form name=\"zoomform\" action=\"".$link."\" method=\"get\">";
	$content1.=LangViewObjectNearbyObjectsMoreLess .":&nbsp;";
  $content1.="<select name=\"zoom\"  onchange=\"zoomform.submit();\">";
	if($objUtil->checkGetKey('zoom',30)=="180") $content1.=("<option selected=\"selected\" value=\"180\">3x3&deg;</option>"); else $content1.=("<option value=\"180\">3x3&deg;</option>"); 
	if($objUtil->checkGetKey('zoom',30)=="120") $content1.=("<option selected=\"selected\" value=\"120\">2x2&deg;</option>"); else $content1.=("<option value=\"120\">2x2&deg;</option>"); 
	if($objUtil->checkGetKey('zoom',30)=="60")  $content1.=("<option selected=\"selected\" value=\"60\">1x1&deg;</option>"); else $content1.=("<option value=\"60\">1x1&deg;</option>"); 
	if($objUtil->checkGetKey('zoom',30)=="30")  $content1.=("<option selected=\"selected\" value=\"30\">30x30'</option>"); else $content1.=("<option value=\"30\">30x30'</option>"); 
	if($objUtil->checkGetKey('zoom',30)=="15")  $content1.=("<option selected=\"selected\" value=\"15\">15x15'</option>"); else $content1.=("<option value=\"15\">15x15'</option>"); 
	if($objUtil->checkGetKey('zoom',30)=="10")  $content1.=("<option selected=\"selected\" value=\"10\">10x10'</option>"); else $content1.=("<option value=\"10\">10x10'</option>"); 
	if($objUtil->checkGetKey('zoom',30)=="5")   $content1.=("<option selected=\"selected\" value=\"5\">5x5'</option>"); else $content1.=("<option value=\"5\">5x5'</option>"); 
	$content1.="</select>";
	$content1.="<input type=\"hidden\" name=\"object\" value=\"".$_GET['object']."\" /> ";
	$content1.="<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" /> ";		
	$content1.="</form>";
	$content2="";
	$content2=$objUtil->printStepsPerPage3($link,"nearbyObjects",$step);
	$objPresentations->line(array($content1,$content2),"LR",array(70,30),25);
	echo "<hr />";
	
	echo "<div style=\"position:relative; left:0px; width:100%;\">";
	if($max>count($_SESSION['Qobj']))
	  $max=count($_SESSION['Qobj']);
	$_GET['min']=$min;
	$_GET['max']=$max;
	if($FF)
	{ echo "<script type=\"text/javascript\">";
	  echo "theResizeElement='obj_list';";
	  echo "theResizeSize=68;";
	  echo "</script>";
	}
	$objObject->showObjects($link, $min, $max,$_GET['object'],0,$step);
		
	echo "</div>";
	
	echo "<div style=\"position:relative; left:0px;height:30px;width:100%;\">";
	echo "<hr />";
	$objPresentations->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
	echo "&nbsp;-&nbsp;";
	echo "<a href=\"".$baseURL."objects.csv?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a> &nbsp;-&nbsp;";
	echo "<a href=\"".$baseURL."objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a>";
	echo "</div>";
}
else
{ $objPresentations->line(array("<h5>".LangViewDSSImageTitle.$object."&nbsp;(".$imagesize."&#39;&nbsp;x&nbsp;".$imagesize."&#39;)</h5>"),"L");
  $imagelink = ("http://archive.stsci.edu/cgi-bin/dss_search?v=poss2ukstu_red&amp;r=".$objUtil->checkRequestKey('raDSS').".0&amp;d=".$objUtil->checkRequestKey('declDSS')."&amp;e=J2000&amp;h=".$imagesize.".0&amp;w=".$imagesize."&amp;f=gif&amp;c=none&amp;fov=NONE&amp;v3=");
  echo "<p style=\"text-align:center\"> <img src=\"".$imagelink."\" alt=\"".$object."\" width=\"495\" height=\"495\"></img> </p>";
  echo "<p>&copy;&nbsp;<a href=\"http://archive.stsci.edu/dss/index.html\">STScI Digitized Sky Survey</a></p>";
}
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
}
?>
	