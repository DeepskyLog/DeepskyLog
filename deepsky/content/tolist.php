<?php
// tolist.php
// manages and shows lists
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";

echo "<form action=\"".$baseURL."index.php?indexAction=listaction\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />";
echo "<table>";
echo "<tr>";
echo "<td align=\"right\">".LangToListAddNew."</td>";
echo "<td>"."<input style=\"width:20em;\" type=\"text\" class=\"inputfield\" name=\"addlistname\" size=\"40\" value=\"\" />"."</td>";
echo "<td><input type=\"checkbox\" name=\"PublicList\" value=\"" . LangToListPublic . "\">".LangToListPublic . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo "<input style=\"width:10em;\"  type=\"submit\" name=\"addList\" value=\"" . LangToListAdd . "\" />";
if($myList)
  echo "<input style=\"width:10em;\" type=\"submit\" name=\"renameList\" value=\"" . LangToListRename . "\" />";
echo "</td>";
echo"</tr>";
echo "</table>"; 
echo "</form>";
echo "<hr>";
if($listname)
{ echo "<div id=\"containerListHeader\" style=\"position:relative;height:30px;\">";
	echo "<div id=\"title\" style=\"position:absolute;left:0px;width:65%;height:30px;\">";
  echo "<h6>";
  echo LangSelectedObjectsTitle." ".$listname_ss; // page title
  echo "</h6>";
  if(!$myList)
    echo "(".LangToListListBy.$objObserver->getObserverProperty(($listowner=$objList->getListOwner()),'firstname').' '.$objObserver->getObserverProperty($listowner,'name').")";
	if($myList)
    echo "<a href=\"".$baseURL."index.php?indexAction=import_csv_list\">" .  LangToListImport . "</a>";
  echo "</div>";
	echo "<div id=\"ListHeader\" style=\"position:absolute; right:0px;width:35%;height:30px;\">";
  $link = $baseURL."index.php?indexAction=listaction&amp;sort=".$objUtil->checkGetKey('sort','objectpositioninlist');
  echo "<span style=\"text-align:right\">";
	list($min, $max)=$objUtil->printNewListHeader2($_SESSION['Qobj'], $link, $min, 25, "");	
  echo "<span>";
  echo "</div>";  
  echo "</div>";
  echo "<div id=\"container2\" style=\"position:relative;height:100%;\">";
  if(count($_SESSION['Qobj'])>0)
	{ // OUTPUT RESULT
    $link = "".$baseURL."index.php?indexAction=listaction";
	  echo "<hr />";
	  $_GET['min']=$min;
	  $_GET['max']=$max;
	  if($FF)
	    $objObject->showObjects($link, $min, $max,'',1);
    else
	  { $_SESSION['ifrm']="deepsky/content/ifrm_objects.php";
	  	echo "<iframe name=\"obj_list\" id=\"obj_list\" src=\"".$baseURL."ifrm_holder.php?link=".urlencode($link)."&amp;min=".$min."&amp;max=".$max."\&amp;ownShow=&amp;showRank=1\" frameborder=\"0\" width=\"100%\" style=\"heigth:100px\">";
	    $objObject->showObjects($link, $min, $max,'',1);
		  echo "</iframe>";
	  }	
	  echo "<script>resizeElement('obj_list',70);</script>";
	  echo "<hr />";
    echo "<div id=\"containerListFooter\" style=\"position:relative;height:30px;\">";
	  echo "<div id=\"title\" style=\"position:absolute;left:0px;width:50%;height:30px;\">";
      list($min, $max)=$objUtil->printNewListHeader2($_SESSION['Qobj'], $link, $min, 25, "");
    echo "</div>";
	  echo "<div id=\"ListHeader2\" style=\"position:absolute;right:0px;width:50%;height:30px;text-align:right;\">";
    if($myList)
    { echo "<form action=\"".$baseURL."index.php?indexAction=listaction\">";
      echo "<input type=\"hidden\" name=\"indexAction\" value=\"listaction\"></input>";
		  echo "<input style=\"width:12em;\" type=\"submit\" name=\"emptyList\" value=\"" . LangToListEmpty . "\" />";
      echo "<input style=\"width:12em;\" type=\"submit\" name=\"removeList\" value=\"" . LangToListMyListsRemove . "\" />";
      echo "</form>";
    }
    echo "</div>";
    echo "</div>";
    $objPresentations->promptWithLink(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
	  echo "&nbsp;-&nbsp;";
    $objPresentations->promptWithLink(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b);
	  echo "&nbsp;-&nbsp;";
    $objPresentations->promptWithLink(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=" . $_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c);
	  echo "&nbsp;-&nbsp;";
    echo "<a href=\"objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a> &nbsp;-&nbsp;";
    echo "<a href=\"objects.csv?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a></p>";
  }
	else
	{ echo LangToListEmptyList;
	}
  echo "</div>";
}


?>
