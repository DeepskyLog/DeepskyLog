<?php

global $baseURL;

if(($objList->checkList($_SESSION['listname'])==2) && ($_SESSION['listname']<>"----------"))
 $listname=$_SESSION['listname'];	
else
  $listname='';
$listname_ss = stripslashes($_SESSION['listname']);
	
if(array_key_exists('listnameMessage',$_GET) && $_GET['listnameMessage'])
{
  echo $_GET['listnameMessage'];
	echo "<hr>";
}

if(array_key_exists('emptyList',$_GET) && ($objList->checkList($listname)==2))
{
  $objList->emptyList($listname);
  echo LangToListEmptied . $listname_ss . ".";
	$_SESSION['QOL'] = $objList->getObjectsFromList($_SESSION['listname']);
	echo "<hr>";
}

if(array_key_exists('ObjectDownInList',$_GET) && $_GET['ObjectDownInList'] && $listname)
{
	$objList->ObjectDownInList($_GET['ObjectDownInList']);
	$_SESSION['QOL'] = $objList->getObjectsFromList($_SESSION['listname']);
  echo LangToListMoved1 . $_GET['ObjectDownInList'] . LangToListMoved3 . "<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">" . $listname_ss . "</a>.";
	echo "<HR>";
}

if(array_key_exists('ObjectUpInList',$_GET) && $_GET['ObjectUpInList'] && $listname)
{ 
	$objList->ObjectUpInList($_GET['ObjectUpInList']);
	$_SESSION['QOL'] = $objList->getObjectsFromList($_SESSION['listname']);
  echo LangToListMoved1 . $_GET['ObjectUpInList'] . LangToListMoved2 . "<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">" . $listname_ss . "</a>.";
	echo "<HR>";
}

if(array_key_exists('ObjectToPlaceInList',$_GET) && $_GET['ObjectToPlaceInList'] && $listname)
{ 
	$objList->ObjectFromToInList($_GET['ObjectFromPlaceInList'],$_GET['ObjectToPlaceInList']);
	$_SESSION['QOL'] = $list->getObjectsFromList($_SESSION['listname']);
  echo LangToListMoved7 . $_GET['ObjectToPlaceInList'] . ".";
	echo "<HR>";
}

if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $listname)
{
	$objList->removeObjectFromList($_GET['removeObjectFromList']);
	$_SESSION['QOL'] = $objList->getObjectsFromList($_SESSION['listname']);
  echo LangToListMoved1 . "<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=" . urlencode($_GET['removeObjectFromList']) . "\">" . $_GET['removeObjectFromList'] . "</a>" . LangToListObjectRemoved . "<a href=\"".$baseURL."index.php?indexAction=listaction&amp;manage=manage\">" . $listname_ss . "</a>.";
	echo "<HR>";
}

if(array_key_exists('removePageObjectsFromList',$_GET) && $_GET['removePageObjectsFromList'] && $listname)
{
	if(count($_SESSION['QOL'])>0)
	{
    if(array_key_exists('min',$_GET) && $_GET['min'])
     $min=$_GET['min'];
    else
     $min=0;
		$count=$min;
	  while(($count<($min+25)) && ($count<count($_SESSION['QOL'])))
	  {
		  $objList->removeObjectFromList($_SESSION['QOL'][$count][0],$_SESSION['QOL'][$count][4]);
		  $count++;
    }
	  $_SESSION['QOL'] = $objList->getObjectsFromList($_SESSION['listname']);
    echo LangToListPageRemoved;
	  echo "<HR>";
	}
}

echo("<form action=\"".$baseURL."index.php?indexAction=listaction\">");
echo("<input type=\"hidden\" name=\"indexAction\" value=\"listaction\"></input>");
echo "<table>";
 echo "<tr>";
  echo "<td align=\"right\">";
   echo(LangToListAddNew);
  echo "</td>";
	echo "<td>";
	 echo("<input style=\"width:20em;\" type=\"text\" class=\"inputfield\" name=\"addlistname\" size=\"40\" value=\"\" />");
  echo "</td>";
	echo "<td>";
	 echo("<input type=\"checkbox\" name=\"PublicList\" value=\"" . LangToListPublic . "\">");
	 echo LangToListPublic . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	 echo("<input style=\"width:10em;\"  type=\"submit\" name=\"addList\" value=\"" . LangToListAdd . "\" />");
	 if($listname)
  	 echo("<input style=\"width:10em;\"  type=\"submit\" name=\"renameList\" value=\"" . LangToListRename . "\" />");
	echo "</td>";
 echo"</tr>";
echo "</table>"; 
echo("</form>");
echo "<hr>";

if($_SESSION['listname']<>"----------")
{
  if(array_key_exists('sort', $_GET) && $_GET['sort'])
	  $sort=$_GET['sort'];
	else
	  $sort="objectplace";
	if(!array_key_exists('QOL',$_SESSION))
	  $_SESSION['QOL'] = $objList->getObjectsFromList($_SESSION['listname']);
	echo "<table>";
	echo "<tr>";
	echo "<td>";
  echo("<div id=\"main\">\n<h2>");
  echo LangSelectedObjectsTitle . " " . $listname_ss; // page title
  echo("</h2>\n");
  echo "</td>";
	if($listname)
  {
    echo "<td width=\"200\" align=\"center\">";
		echo("<a href=\"".$baseURL."index.php?indexAction=import_csv_list\">" .  LangToListImport . "</a>");
    echo "</td>";	
    echo("<form action=\"".$baseURL."index.php?indexAction=listaction\">");
    echo("<input type=\"hidden\" name=\"indexAction\" value=\"listaction\"></input>");
    echo "<td width=\"200\" align=\"center\">";
		echo("<input style=\"width:12em;\"  type=\"submit\" name=\"emptyList\" value=\"" . LangToListEmpty . "\" />");
    echo "</td>";	
    echo "<td width=\"200\" align=\"center\">";
		echo("<input style=\"width:12em;\"  type=\"submit\" name=\"removeList\" value=\"" . LangToListMyListsRemove . "\" />");
    echo "</td>";
		echo("</form>");
  }
  echo "</table>";
	if(count($_SESSION['QOL'])>0)
	{
   if(array_key_exists('min',$_GET) && $_GET['min'])
     $min=$_GET['min'];
    else
     $min=0;

  	if(array_key_exists('previous',$_GET) && ($_GET['previous'] == $_GET['sort'])) // reverse sort when pushed twice
    {
      $reverse = true;
      $previous = ""; // reset previous field to sort on
    }
    else
    {
      $reverse = false;
      $previous = $sort;
    }
  
    $_SESSION['QOL'] = $objObject->sortObjects($_SESSION['QOL'], $sort, $reverse);
  	
    $link = $baseURL."index.php?indexAction=listaction&amp;sort=$sort&amp;";
    if(array_key_exists('min',$_GET))
  	  $min = $_GET['min'];
  	else
  	  $min = '';
  
    list($min, $max) = $objUtil->printListHeader($_SESSION['QOL'], $link, $min, 25, "");	
  
    // OUTPUT RESULT
    echo "<table width=\"100%\">\n";
    echo "<tr class=\"type3\">\n";
    echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=objectplace&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader0) . "\">".LangOverviewObjectsHeader0."</a></td>\n";
    echo "<td><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=showname&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader1) . "\">".LangOverviewObjectsHeader1."</a></td>\n";
    echo "<td><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=con&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader2) . "\">".LangOverviewObjectsHeader2."</a></td>\n";
    echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=mag&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader3) . "\">".LangOverviewObjectsHeader3."</a></td>\n";
    echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=subr&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader3b) . "\">".LangOverviewObjectsHeader3b."</a></td>\n";
    echo "<td><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=type&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader4) . "\">".LangOverviewObjectsHeader4."</a></td>\n";
    echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=atlas" . $objAtlas->atlasCodes[$objObserver->getStandardAtlasCode($_SESSION['deepskylog_id'])] . "&amp;previous=$previous\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
    echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=contrast&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangViewObjectFieldContrastReserve) . "\">". LangViewObjectFieldContrastReserve . "</a></td>\n";
    echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=magnification&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangViewObjectFieldMagnification) . "\">". LangViewObjectFieldMagnification . "</a></td>\n";
    echo "<td align=\"center\"><a href=\"".$baseURL."index.php?indexAction=listaction&amp;sort=seen&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader7) . "\">".LangOverviewObjectsHeader7."</a></td>\n";
    if($listname && ($sort=="objectplace"))
      echo "<td><a href=\"".$baseURL."index.php?indexAction=listaction&amp;removePageObjectsFromList=true\" title=\"". LangToListRemovePageObjectsFromList . "\">". LangToListRemovePageObjectsFromListText ."</a></td>\n";
  	if(array_key_exists('previous',$_GET) && ($_GET['previous'] == $_GET['sort'])) // reverse sort when pushed twice
  	  $previous = $sort;
    else
      $previous = "";
    $link = "".$baseURL."index.php?indexAction=listaction&amp;sort=$sort";
  	$count=$min;
  	$maxcount = count($_SESSION['QOL']);
//		$objects->showObjects($link, $_SID, $min, $max, $myList, 1); 
    while(($count<$max) && ($count<$maxcount))
    { $typefield = "class=\"type".(2-($count%2))."\"";
      $name = $_SESSION['QOL'][$count]['objectname'];
			$showname=$_SESSION['QOL'][$count]['showname']; 
      $place = $_SESSION['QOL'][$count][24];
      $con = $_SESSION['QOL'][$count]['objectconstellation'];
      $type = $_SESSION['QOL'][$count]['objecttype'];
      $magnitude = sprintf("%01.1f", $_SESSION['QOL'][$count]['objectmagnitude']);
      if($magnitude == 99.9)
        $magnitude = "&nbsp;&nbsp;-&nbsp;";
      $sb = sprintf("%01.1f", $_SESSION['QOL'][$count]['objectsurfacebrightness']);
      if($sb == 99.9)
        $sb = "&nbsp;&nbsp;-&nbsp;";
      $ra = raToString($_SESSION['QOL'][$count]['objectra']);
      $decl = decToStringDegMin($_SESSION['QOL'][$count]['objectdecl']);
  
      $atlas = $objObserver->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $_SESSION['QOL'][$count][$atlas];  
      $seen="<a href=\"".$baseURL."index.php?indexAction=detail_object&object=" . urlencode($name) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
      if(substr($_SESSION['QOL'][$count]['objectseen'],0,1)=="X")
        $seen = "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($name) . "\" title=\"" . LangObjectXSeen . "\">" . $_SESSION['QOL'][$count]['objectseen'] . "</a>";
      if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && (substr($_SESSION['QOL'][$count]['objectseen'],0,1)=="Y"))
        $seen = "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;object=" . urlencode($name) . "\" title=\"" . LangObjectYSeen . "\">" . $_SESSION['QOL'][$count]['objectseen'] . "</a>";
      echo "<tr $typefield>\n";
  		if($listname && ($sort=="objectplace"))
      {
				echo "<td align=\"center\">
              <a href=\"\"
                 onclick=\"theplace = prompt('Please enter the new position','" . $place . "');
								           location.href='" . $link . "&amp;ObjectFromPlaceInList=" . $place . "&amp;ObjectToPlaceInList='+theplace+'&amp;min=" . $min . "'
					                 return false\"
								 title=\"" . LangToListMoved6 . "\">".$place."</a></td>";
      }
      else
        echo "<td align=\"center\"\">".$place."</td>\n";
      echo "<td><a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=" . urlencode($name) . "\">" . $showname . "</a></td>\n";
      echo "<td>".$$con."</td>\n";
      echo "<td align=\"center\">$magnitude</td>\n";
      echo "<td align=\"center\">$sb</td>\n";
      echo "<td>".$$type."</td>\n";
      echo "<td align=\"center\">".$page."</td>\n";
      echo "<td align=\"center\" class=\"" . $_SESSION['QOL'][$count][22] . "\" onmouseover=\"Tip('" . $_SESSION['QOL'][$count][23] . "')\">" .
           $_SESSION['QOL'][$count][21] . "</td>\n";
			if ($_SESSION['QOL'][$count][21] == "-")
      {
				$magnification = "-";
      } else {
				$magnification = ($_SESSION['QOL'][$count][25]);
			}
      echo "<td align=\"center\">" . $magnification . "</td>\n";
      echo "<td align=\"center\" class=\"seen\">$seen</td>";
  		if($listname)
			{
        echo("<td>");
  		  echo("<a href=\"$link&min=" . $min . "&removeObjectFromList=" . urlencode($name) . "\" title=\"". LangToListRemoveObjectFromList . "\">R</a>");
        echo("</td>");
  		}
			echo("</tr>");
  		
      $count++; // increase line counter	
    }   
    echo "</table>\n";
    echo("<hr>");
  
    list($min, $max) = $objUtil->printListHeader($_SESSION['QOL'], $link, $min, 25, "");
    echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . ",'" . $listname_ss . "');
								           location.href='".$baseURL."objects.pdf?SID=QOL&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a> &nbsp;-&nbsp;";
  echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . ",'" . $listname_ss . "');
								           location.href='".$baseURL."objectnames.pdf?SID=QOL&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4b."</a> &nbsp;-&nbsp;";
  echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . ",'" . $listname_ss . "');
								           location.href='".$baseURL."objectsDetails.pdf?SID=QOL&amp;sort=" . $sort . "&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4c."</a> &nbsp;-&nbsp;";
    echo "<a href=\"objects.argo?SID=QOL\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a> &nbsp;-&nbsp;";
    echo "<a href=\"objects.csv?SID=QOL\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a></p>";
  }
	else
	{
	  echo LangToListEmptyList;
	}
}


?>
