<?php  // view_object.php - view all information of one object 
if(!($object=$objUtil->checkGetKey('object')))
  throw new Exception('To implement');
else
{ echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
	$seen=$objObject->getDSOseenLink($object);
	echo "<div id=\"main\">";
	$object_ss = stripslashes($object);
	$objPresentations->line(array("<h4>".LangViewObjectTitle."&nbsp;-&nbsp;".$object_ss."&nbsp;-&nbsp;".LangOverviewObjectsHeader7."&nbsp;:&nbsp;".$seen."</h4>",$objPresentations->getDSSDeepskyLiveLinks1($object)),
	                        "LR",array(60,40),30);
	$topline="";
	if($imagesize=$objUtil->checkRequestKey('imagesize'))
	  $topline="&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."&amp;titleobject=".urlencode(LangViewObjectViewNearbyObject)."\">".LangViewObjectViewNearbyObject."</a>";
	if ($myList) 
	{ if ($objList->checkObjectInMyActiveList($object))
			$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a>";
		else
			$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a>";
	}
	$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=atlaspage&amp;object=" . urlencode($object) . "\">" . LangAtlasPage . "</a>";
	$objPresentations->line(array(substr($topline,13),$objPresentations->getDSSDeepskyLiveLinks2($object)),"LR",array(60,40),20);
	echo "<hr />";
	$objObject->showObject($object);
	
	if(!($imagesize))
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
		$objPresentations->line(array($content1,$content2),"LR",array(50,50),30);
	  $content1 ="<form action=\"".$link."\" method=\"get\"><div>";
		$content1.=LangViewObjectNearbyObjectsMoreLess .":&nbsp;";
	  $content1.="<select name=\"zoom\" onchange=\"submit();\">";
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
		$content1.="</div></form>";
		$content2="";
		$content2=$objUtil->printStepsPerPage3($link,"nearbyObjects",$step);
		$objPresentations->line(array($content1,$content2),"LR",array(50,50),25);
		echo "<hr />";
		if($max>count($_SESSION['Qobj']))
		  $max=count($_SESSION['Qobj']);
		$_GET['min']=$min;
		$_GET['max']=$max;
		if($FF)
		{ echo "<script type=\"text/javascript\">";
		  echo "theResizeElement='obj_list';";
		  echo "theResizeSize=65;";
		  echo "</script>";
		}
		$objObject->showObjects($link, $min, $max,$_GET['object'],0,$step);
		echo "<hr />";
		$content =LangExecuteQueryObjectsMessage4."&nbsp;";
		$content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4a)."&nbsp;-&nbsp;";
		$content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b)."&nbsp;-&nbsp;";
		$content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=".$_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c)."&nbsp;-&nbsp;";
		$content.="<a href=\"".$baseURL."objects.argo?SID=Qobj\">".LangExecuteQueryObjectsMessage8."</a>&nbsp;-&nbsp;";
		$content.="<a href=\"".$baseURL."objects.csv?SID=Qobj\" >".LangExecuteQueryObjectsMessage6."</a>";;
		$objPresentations->line(array($content),"L",array(),20);
	}
	else
	{ $objPresentations->line(array("<h4>".LangViewDSSImageTitle.$object."&nbsp;(".$imagesize."&#39;&nbsp;x&nbsp;".$imagesize."&#39;)</h4>"),"L");
	  $imagelink = "http://archive.stsci.edu/cgi-bin/dss_search?"."v=poss2ukstu_red&amp;r=".urlencode($objUtil->checkRequestKey('raDSS')).".0&amp;d=".urlencode($objUtil->checkRequestKey('declDSS'))."&amp;e=J2000&amp;h=".$imagesize.".0&amp;w=".$imagesize."&amp;f=gif&amp;c=none&amp;fov=NONE&amp;v3=";
	  echo "<p class=\"centered DSSImage\"> <img class=\"centered DSSImage\" src=\"".$imagelink."\" alt=\"".$object."\" ></img> </p>";
	  echo "<p>&copy;&nbsp;<a href=\"http://archive.stsci.edu/dss/index.html\">STScI Digitized Sky Survey</a></p>";
	}
	echo "</div>";
	//============================================================================== Admin section permits to change object settings in DB remotely
	if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
	{ echo "<hr />";
	  echo("<form action=\"".$baseURL."index.php\" method=\"get\"><div>");
	  echo("<input type=\"hidden\" name=\"object\" value=\"" . $_GET['object'] . "\" />");
	  echo("<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" />");
	  echo("<select name=\"newaction\">");
	  echo("<option value=\"\">&nbsp;</option>"); // empty field
	  echo("<option value=\"NewName\">" . LangObjectNewName . "</option>");
	  echo("<option value=\"NewAltName\">" . LangObjectNewAltName . "</option>");
	  echo("<option value=\"RemoveAltNameName\">" . LangObjectRemoveAltNameName . "</option>");
	  echo("<option value=\"NewPartOf\">" . LangObjectNewPartOf . "</option>");
	  echo("<option value=\"RemovePartOf\">" . LangObjectRemovePartOf . "</option>");
	  echo("<option value=\"RemoveAndReplaceObjectBy\">" . LangObjectRemoveAndReplaceObjectBy . "</option>");
	  echo("<option value=\"LangObjectSetRA\">" . LangObjectSetRA . "</option>");
	  echo("<option value=\"LangObjectSetDECL\">" . LangObjectSetDECL . "</option>");
	  echo("<option value=\"LangObjectSetCon\">" . LangObjectSetCon . "</option>");
	  echo("<option value=\"LangObjectSetType\">" . LangObjectSetType . "</option>");
	  echo("<option value=\"LangObjectSetMag\">" . LangObjectSetMag . "</option>");
	  echo("<option value=\"LangObjectSetSUBR\">" . LangObjectSetSUBR . "</option>");
	  echo("<option value=\"LangObjectSetDiam1\">" . LangObjectSetDiam1 . "</option>");
	  echo("<option value=\"LangObjectSetDiam2\">" . LangObjectSetDiam2 . "</option>");
	  echo("<option value=\"LangObjectSetPA\">" . LangObjectSetPA . "</option>");
	  echo("</select>");		
	  echo "<select name=\"newcatalog\">";
	  echo "<option value=\"\">&nbsp;</option>"; // empty field
	  $catalogs = $objObject->getCatalogs(); // should be sorted
	  while(list($key, $value) = each($catalogs))
	    echo "<option value=\"$value\">".$value."</option>";
	  echo "</select>";		
	  echo "<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"newnumber\" size=\"40\" value=\"\"/>";
	  echo "<input type=\"submit\" name=\"gonew\" value=\"Go\"/><br />";
	  echo "<a href=\"".$baseURL."index.php?indexAction=manage_csv_object\">" . LangNewObjectSubtitle1b . "</a><br />";
	  echo "</div></form>";
	}
}
?>
	