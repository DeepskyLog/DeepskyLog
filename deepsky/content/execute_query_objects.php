<?php
// execute_query_objects.php
// executes the object query passed by setup_query_objects.php
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
$link=$baseURL."index.php?indexAction=query_objects";
reset($_GET);
while(list($key,$value)=each($_GET))
	if(($key!='indexAction')&&($key!='multiplepagenr')&&($key!='sort')&&($key!='sortdirection')&&($key!='showPartOfs'))
    $link.='&amp;'.$key.'='.$value;
if(count($_SESSION['Qobj'])>1) //=============================================== valid result, multiple objects found
{ echo "<div id=\"main\">";
  $title=LangSelectedObjectsTitle;
	if($showPartOfs)	
	  $title.=LangListQueryObjectsMessage10;
	else
    $title.=LangListQueryObjectsMessage11;
  if(array_key_exists('deepskylog_id', $_SESSION)&&$_SESSION['deepskylog_id']&&
	   array_key_exists('listname',$_SESSION)&&$_SESSION['listname']&&($_SESSION['listname']<>"----------")&&$myList)
    $title.="&nbsp;-&nbsp;<a href=\"".$link."&amp;min=".$min."&amp;addAllObjectsFromQueryToList=true\" title=\"".LangListQueryObjectsMessage5.$listname_ss."\">".LangListQueryObjectsMessage4."</a>";
  divPageTitle($title, $link, $_SESSION['Qobj'], $min, $max);
  if($showPartOfs)
    echo "<a href=\"".$link."&amp;showPartOfs=0\">".LangListQueryObjectsMessage12."</a>";
	else
    echo "<a href=\"".$link."&amp;showPartOfs=1\">".LangListQueryObjectsMessage13."</a>";
//	echo "<span style=\"text-align:right\">&nbsp;&nbsp;&nbsp;<a href=\"".$baseURL."index.php?indexAction=query_objects\">".LangExecuteQueryObjectsMessage1."</a></span>";  
  $link.="&amp;showPartOfs=".$showPartOfs;
	echo "<hr />";
	$_GET['min']=$min;
	$_GET['max']=$max;
	if($FF)
	  $objObject->showObjects($link, $min, $max);
  else
	{ $_SESSION['ifrm']="deepsky/content/ifrm_objects.php";
		echo "<iframe name=\"obj_list\" id=\"obj_list\" src=\"".$baseURL."ifrm_holder.php?link=".urlencode($link)."&amp;min=".$min."&amp;max=".$max."&amp;ownShow=&amp;showRank=0\" frameborder=\"0\" width=\"100%\" style=\"heigth:100px\">";
	  $objObject->showObjects($link, $min, $max);
		echo "</iframe>";
	}	
	$resizeElement='obj_list';
	$resizeSize=70;
	echo "<hr />";
  //list($min,$max)=$objUtil->printNewListHeader($_SESSION['Qobj'],$link,$min,25,'');	
  $objPresentations->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4);
	echo "&nbsp;-&nbsp;";
  $objPresentations->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b);
	echo " &nbsp;-&nbsp;";
  $objPresentations->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=".$_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c);
  echo "&nbsp;-&nbsp";									 
  echo "<a href=\"".$baseURL."objects.argo?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage8."</a>";
	echo "&nbsp;-&nbsp;";
  if(array_key_exists('listname',$_SESSION)&&$_SESSION['listname']&&$myList)
    echo "<a href=\"".$link."&amp;min=".$min."&amp;addAllObjectsFromQueryToList=true\" title=\"".LangListQueryObjectsMessage5.$_SESSION['listname']."\">".LangListQueryObjectsMessage4."</a>"."&nbsp;-&nbsp;";
  echo "<a href=\"".$baseURL."objects.csv?SID=Qobj\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a>";
	echo "</div>";
}
else // ========================================================================no results found
{ echo "<div id=\"main\">";
  echo "<h2>".LangSelectedObjectsTitle."</h2>";
  echo LangExecuteQueryObjectsMessage2;
  echo "<p>";
	echo "<a href=\"".$baseURL."index.php?indexAction=query_objects\">".LangExecuteQueryObjectsMessage2a."</a>";
	echo "</div>";
}

?>
