<?php

include_once "../lib/lists.php";
include_once "../lib/observers.php";
include_once "../lib/objects.php";
include_once "../common/control/ra_to_hms.php";
include_once "../common/control/dec_to_dm.php";

$list = new Lists;
$observer = new Observers;
$objects = new Objects;



if(($list->checkList($_SESSION['listname'])==2) && ($_SESSION['listname']<>"----------"))
  $listname = $_SESSION['listname'];
else
  $listname='';
	
if(array_key_exists('listnameMessage',$_GET) && $_GET['listnameMessage'])
{
  echo $_GET['listnameMessage'];
	echo "<hr>";
}

if(array_key_exists('emptyList',$_GET) && ($list->checkList($listname)==2))
{
  $list->emptyList($listname);
  echo LangToListEmptied . $listname . ".";
	echo "<hr>";
}

if(array_key_exists('ObjectDownInList',$_GET) && $_GET['ObjectDownInList'] && $listname)
{
	$list->ObjectDownInList($_GET['ObjectDownInList']);
  echo LangToListMoved1 . $_GET['ObjectDownInList'] . LangToListMoved3 . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $listname . "</a>.";
	echo "<HR>";
}

if(array_key_exists('ObjectUpInList',$_GET) && $_GET['ObjectUpInList'] && $listname)
{ 
	$list->ObjectUpInList($_GET['ObjectUpInList']);
  echo LangToListMoved1 . $_GET['ObjectUpInList'] . LangToListMoved2 . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $listname . "</a>.";
	echo "<HR>";
}

if(array_key_exists('ObjectToPlaceInList',$_GET) && $_GET['ObjectToPlaceInList'] && $listname)
{ 
	$list->ObjectFromToInList($_GET['ObjectFromPlaceInList'],$_GET['ObjectToPlaceInList']);
  echo LangToListMoved7 . $_GET['ObjectToPlaceInList'] . ".";
	echo "<HR>";
}

if(array_key_exists('removeObjectFromList',$_GET) && $_GET['removeObjectFromList'] && $listname)
{
	$list->removeObjectFromList($_GET['removeObjectFromList']);
  echo LangToListMoved1 . "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($_GET['removeObjectFromList']) . "\">" . $_GET['removeObjectFromList'] . "</a>" . LangToListObjectRemoved . "<a href=\"deepsky/index.php?indexAction=listaction&manage=manage\">" . $listname . "</a>.";
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
		  $list->removeObjectFromList($_SESSION['QOL'][$count][0],$_SESSION['QOL'][$count][4]);
		  $count++;
    }
    echo LangToListPageRemoved;
	  echo "<HR>";
	}
}

echo("<form action=\"deepsky/index.php?indexAction=listaction\">");
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
	  $_SESSION['QOL'] = $list->getObjectsFromList($_SESSION['listname']);
	echo "<table>";
	echo "<tr>";
	echo "<td>";
  echo("<div id=\"main\">\n<h2>");
  echo LangSelectedObjectsTitle . " " . $_SESSION['listname']; // page title
  echo("</h2>\n");
  echo "</td>";
	if($listname)
  {
    echo "<td width=\"200\" align=\"center\">";
		echo("<a href=\"deepsky/index.php?indexAction=import_csv_list\">" .  LangToListImport . "</a>");
    echo "</td>";	
    echo("<form action=\"deepsky/index.php?indexAction=listaction\">");
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
  
    $_SESSION['QOL'] = $objects->sortObjects($_SESSION['QOL'], $sort, $reverse);
  	
    $link = "deepsky/index.php?indexAction=listaction&amp;sort=$sort&amp;";
    if(array_key_exists('min',$_GET))
  	  $min = $_GET['min'];
  	else
  	  $min = '';
  
    list($min, $max) = $util->printListHeader($_SESSION['QOL'], $link, $min, 25, "");	
  
    // OUTPUT RESULT
    echo "<table width=\"100%\">\n";
    echo "<tr class=\"type3\">\n";
  	if($listname && ($sort=="objectplace"))
  	  echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
    echo "<td align=\"right\"><a href=\"deepsky/index.php?indexAction=listaction&sort=objectplace&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader0) . "\">".LangOverviewObjectsHeader0."</a></td>\n";
    echo "<td><a href=\"deepsky/index.php?indexAction=listaction&sort=showname&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader1) . "\">".LangOverviewObjectsHeader1."</a></td>\n";
    echo "<td><a href=\"deepsky/index.php?indexAction=listaction&sort=con&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader2) . "\">".LangOverviewObjectsHeader2."</a></td>\n";
    echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=mag&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader3) . "\">".LangOverviewObjectsHeader3."</a></td>\n";
    echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=subr&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader3b) . "\">".LangOverviewObjectsHeader3b."</a></td>\n";
    echo "<td><a href=\"deepsky/index.php?indexAction=listaction&sort=type&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader4) . "\">".LangOverviewObjectsHeader4."</a></td>\n";
    $atlas2 = $observer->getStandardAtlas($_SESSION['deepskylog_id']);
    if ($atlas2 == 0)
      echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=urano&amp;previous=$previous\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
    else if ($atlas2 == 1)
      echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=urano_new&amp;previous=$previous\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
    else if ($atlas2 == 2)
      echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=sky&amp;previous=$previous\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
    else if ($atlas2 == 3)
      echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=millenium&amp;previous=$previous\" title=\"". LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
    else if ($atlas2 == 4)
      echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=taki&amp;previous=$previous\" title=\"". $seenpar . LangSortOn . "atlas\">"."Atlas"."</a></td>\n";
  
   echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=contrast&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangViewObjectFieldContrastReserve) . "\">". LangViewObjectFieldContrastReserve . "</a></td>\n";
   echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=magnification&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangViewObjectFieldMagnification) . "\">". LangViewObjectFieldMagnification . "</a></td>\n";
   echo "<td align=\"center\"><a href=\"deepsky/index.php?indexAction=listaction&sort=seen&amp;previous=$previous\" title=\"". LangSortOn . mb_strtolower(LangOverviewObjectsHeader7) . "\">".LangOverviewObjectsHeader7."</a></td>\n";
   if($listname && ($sort=="objectplace"))
     echo "<td><a href=\"deepsky/index.php?indexAction=listaction&removePageObjectsFromList=true\" title=\"". LangToListRemovePageObjectsFromList . "\">". LangToListRemovePageObjectsFromListText ."</a></td>\n";

  	if(array_key_exists('previous',$_GET) && ($_GET['previous'] == $_GET['sort'])) // reverse sort when pushed twice
  	  $previous = $sort;
    else
      $previous = "";
    $link = "deepsky/index.php?indexAction=listaction&amp;sort=$sort";
  	$count=$min;
  	$maxcount = count($_SESSION['QOL']);
    while(($count<$max) && ($count<$maxcount))
    {
      if ($count % 2)
        $typefield = "class=\"type1\"";
      else
        $typefield = "class=\"type2\"";
  
      $name = $_SESSION['QOL'][$count][0];
			$showname=$_SESSION['QOL'][$count][4]; 
      $place = $_SESSION['QOL'][$count][20];
      $con = $_SESSION['QOL'][$count][2];
      $type = $_SESSION['QOL'][$count][1];
      $magnitude = sprintf("%01.1f", $_SESSION['QOL'][$count][5]);
      if($magnitude == 99.9)
        $magnitude = "&nbsp;&nbsp;-&nbsp;";
      $sb = sprintf("%01.1f", $_SESSION['QOL'][$count][6]);
      if($sb == 99.9)
        $sb = "&nbsp;&nbsp;-&nbsp;";
      $ra = RAToString($_SESSION['QOL'][$count][7]);
      $decl = decToStringDegMin($_SESSION['QOL'][$count][8]);
  
      $atlas = $observer->getStandardAtlas($_SESSION['deepskylog_id']);
      if ($atlas == 0)      $page = $_SESSION['QOL'][$count][9];
      else if ($atlas == 1) $page = $_SESSION['QOL'][$count][10];
      else if ($atlas == 2) $page = $_SESSION['QOL'][$count][11];
      else if ($atlas == 3) $page = $_SESSION['QOL'][$count][12];
      else if ($atlas == 4) $page = $_SESSION['QOL'][$count][13];
  
       $seen="<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($name) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
        if(substr($_SESSION['QOL'][$count][3],0,1)=="X")
          $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($name) . "\" title=\"" . LangObjectXSeen . "\">" . $_SESSION['QOL'][$count][3] . "</a>";
        if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'] && (substr($_SESSION['QOL'][$count][3],0,1)=="Y"))
          $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($name) . "\" title=\"" . LangObjectYSeen . "\">" . $_SESSION['QOL'][$count][3] . "</a>";
  
        echo "<tr $typefield>\n";
  		if($listname && ($sort=="objectplace"))
      {
  		  echo "<td align=\"center\"><a href=\"$link&amp;ObjectUpInList=" . $place . "&amp;min=" . $min . "\" title=\"" . LangToListMoved5 . "\">D</a></td>";
        
				echo "<td align=\"center\">
              <a href=\"\"
                 onclick=\"theplace = prompt('Please enter the new position','" . $place . "');
								           location.href='" . $link . "&amp;ObjectFromPlaceInList=" . $place . "&amp;ObjectToPlaceInList='+theplace+'&amp;min=" . $min . "'
					                 return false\"
								 title=\"" . LangToListMoved6 . "\">T</a></td>";

  			echo "<td align=\"center\"><a href=\"$link&amp;ObjectDownInList=" . $place . "&amp;min=" . $min . "\" title=\"" . LangToListMoved4 . "\">U</a></td>";
      }
      echo "<td align=\"right\"\">".$place."</td>\n";
      echo "<td><a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($name) . "\">" . $showname . "</a></td>\n";
      echo "<td>".$$con."</td>\n";
      echo "<td align=\"center\">$magnitude</td>\n";
      echo "<td align=\"center\">$sb</td>\n";
      echo "<td>".$$type."</td>\n";
      echo "<td align=\"center\">".$page."</td>\n";
      echo "<td align=\"center\" class=\"" . $_SESSION['QOL'][$count][18] . "\" onmouseover=\"Tip('" . $_SESSION['QOL'][$count][19] . "')\">" .
           $_SESSION['QOL'][$count][17] . "</td>\n";
			if ($_SESSION['QOL'][$count][17] == "-")
      {
				$magnification = "-";
      } else {
				$magnification = ($_SESSION['QOL'][$count][21]);
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
  
    list($min, $max) = $util->printListHeader($_SESSION['QOL'], $link, $min, 25, "");
    echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . "," . LangListQueryObjectsMessage15 . ");
								           location.href='deepsky/objects.pdf?SID=QOL&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a> &nbsp;-&nbsp;";
  echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . "," . LangListQueryObjectsMessage15 . ");
								           location.href='deepsky/objectnames.pdf?SID=QOL&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4b."</a> &nbsp;-&nbsp;";
  echo "<a href=\"\"
                 onclick=\"thetitle = prompt(" . LangListQueryObjectsMessage14 . "," . LangListQueryObjectsMessage15 . ");
								           location.href='deepsky/objectsDetails.pdf?SID=QOL&amp;pdfTitle='+thetitle+''
					                 return false\"
													 
								 target=\"new_window\">".LangExecuteQueryObjectsMessage4c."</a> &nbsp;-&nbsp;";
    echo "<a href=\"deepsky/objects.argo?SID=QOL\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a> &nbsp;-&nbsp;";
    echo "<a href=\"deepsky/objects.csv?SID=QOL\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a></p>";
  }
	else
	{
	  echo LangToListEmptyList;
	}
}


?>
