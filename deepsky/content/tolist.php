<?php
// tolist.php
// manages and shows lists

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
{ echo "<table>";
	echo "<tr>";
	echo "<td>";
  echo("<div id=\"main\">\n<h2>");
  echo LangSelectedObjectsTitle . " " . $listname_ss; // page title
  echo("</h2>\n");
  echo "</td>";
	if($myList)
  { echo "<td width=\"200\" align=\"center\">";
		echo("<a href=\"".$baseURL."index.php?indexAction=import_csv_list\">" .  LangToListImport . "</a>");
    echo "</td>";	
    echo("<form action=\"".$baseURL."index.php?indexAction=listaction\">");
    echo("<input type=\"hidden\" name=\"indexAction\" value=\"listaction\"></input>");
    echo "<td width=\"200\" align=\"center\">";
		echo("<input style=\"width:12em;\" type=\"submit\" name=\"emptyList\" value=\"" . LangToListEmpty . "\" />");
    echo "</td>";	
    echo "<td width=\"200\" align=\"center\">";
		echo("<input style=\"width:12em;\" type=\"submit\" name=\"removeList\" value=\"" . LangToListMyListsRemove . "\" />");
    echo "</td>";
		echo("</form>");
  }
  echo "</table>";
	if(count($_SESSION['Qobj'])>0)
	{ $link = $baseURL."index.php?indexAction=listaction&amp;sort=".$objUtil->checkGetKey('sort','objectpositioninlist');
    list($min, $max) = $objUtil->printNewListHeader($_SESSION['Qobj'], $link, $min, 25, "");	
  
    // OUTPUT RESULT
    $link = "".$baseURL."index.php?indexAction=listaction";
    $objObject->showObjects($link,$min,$max, '',1);
    echo("<hr>");
  
    list($min, $max) = $objUtil->printNewListHeader($_SESSION['Qobj'], $link, $min, 25, "");
    $objUtil->promptWithLink(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
	  echo "&nbsp;-&nbsp;";
    $objUtil->promptWithLink(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b);
	  echo "&nbsp;-&nbsp;";
    $objUtil->promptWithLink(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=" . $_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c);
	  echo "&nbsp;-&nbsp;";
    echo "<a href=\"objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a> &nbsp;-&nbsp;";
    echo "<a href=\"objects.csv?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a></p>";
  }
	else
	{ echo LangToListEmptyList;
	}
}


?>
