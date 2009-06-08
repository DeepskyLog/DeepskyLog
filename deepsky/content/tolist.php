<?php
// tolist.php
// manages and shows lists
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
echo "<div id=\"main\" style=\"position:relative\">";
echo "<form action=\"".$baseURL."index.php?indexAction=listaction\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />";
echo "<table>";
echo "<tr>";
echo "<td align=\"right\">".LangToListAddNew."</td>";
echo "<td>"."<input style=\"width:20em;\" type=\"text\" class=\"inputfield\" name=\"addlistname\" size=\"40\" value=\"\" />"."</td>";
echo "<td><input type=\"checkbox\" name=\"PublicList\" value=\"" . LangToListPublic . "\" />".LangToListPublic . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo "<input style=\"width:10em;\"  type=\"submit\" name=\"addList\" value=\"" . LangToListAdd . "\" />";
if($myList)
  echo "<input style=\"width:10em;\" type=\"submit\" name=\"renameList\" value=\"" . LangToListRename . "\" />";
echo "</td>";
echo"</tr>";
echo "</table>"; 
echo "</form>";
echo "<hr />";
if($listname)
{ $link = $baseURL."index.php?indexAction=listaction&amp;sort=".$objUtil->checkGetKey('sort','objectpositioninlist');
  if((array_key_exists('steps',$_SESSION))&&(array_key_exists("listObj",$_SESSION['steps'])))
	  $step=$_SESSION['steps']["listObj"];
	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;
  list($min, $max,$content)=$objUtil->printNewListHeader3($_SESSION['Qobj'], $link, $min, $step, "");	
  $objPresentations->line(array("<h5>".LangSelectedObjectsTitle." ".$listname_ss. "</h5>",
                                $content),
                          "LR", array(60,40),40);
	  
  $content2=$objUtil->printStepsPerPage3($link,"listObj",$step);
  $objPresentations->line(array((!$myList)?
                                "(".LangToListListBy.$objObserver->getObserverProperty(($listowner=$objList->getListOwner()),'firstname').' '.$objObserver->getObserverProperty($listowner,'name').")":
                                "<a href=\"".$baseURL."index.php?indexAction=import_csv_list\">" .  LangToListImport . "</a>",$content2),
                          "LR",array(80,20),25);
  if(count($_SESSION['Qobj'])>0)
	{ // OUTPUT RESULT
    $link = "".$baseURL."index.php?indexAction=listaction";
	  echo "<hr />";
	  $_GET['min']=$min;
	  $_GET['max']=$max;
	  if($FF)
	  { echo "<script type=\"text/javascript\">";
      echo "theResizeElement='obj_list';";
      echo "theResizeSize=90;";
      echo "</script>";
  	}
	  $objObject->showObjects($link, $min, $max,'',1, 25);
	  echo "<hr />";
    if($myList)
    { $content2 =" <form action=\"".$baseURL."index.php?indexAction=listaction\">";
      $content2.="<input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />";
		  $content2.="<input style=\"width:12em;\" type=\"submit\" name=\"emptyList\" value=\"" . LangToListEmpty . "\" />";
      $content2.="<input style=\"width:12em;\" type=\"submit\" name=\"removeList\" value=\"" . LangToListMyListsRemove . "\" />";
      $content2.="</form>";
    }
    $content =$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
	  $content.="&nbsp;-&nbsp;";
    $content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b);
	  $content.="&nbsp;-&nbsp;";
    $content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=" . $_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c);
	  $content.="&nbsp;-&nbsp;";
    $content.="<a href=\"objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a> &nbsp;-&nbsp;";
    $content.="<a href=\"objects.csv?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a>";
    $objPresentations->line(array($content,$content2),"LR",array(75,25));
	}
	else
	{ echo LangToListEmptyList;
	}
}
echo "</div>";


?>
