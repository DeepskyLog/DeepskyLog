<?php
// execute_query_objects.php
// executes the object query passed by setup_query_objects.php
if((array_key_exists('steps',$_SESSION))&&(array_key_exists("selObj",$_SESSION['steps'])))
  $step=$_SESSION['steps']["selObj"];
if(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*$step;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*$step;
elseif(array_key_exists('min',$_GET))
  $min=$_GET['min'];
else
  $min = 0;
$link=$baseURL."index.php?indexAction=query_objects";
reset($_GET);
while(list($key,$value)=each($_GET))
  if(!(in_array($key,array('formName','layoutName','restoreColumns','orderColumns','loadLayout','saveLayout','removeLayout','sort','soretorder','multiplepagenr','noShowName','sortdirection'))))  
    $link.='&amp;'.urlencode($key).'='.urlencode($value);
if(count($_SESSION['Qobj'])>1) //=============================================== valid result, multiple objects found
{ echo "<div id=\"main\">";
  $title="<h4>".LangSelectedObjectsTitle;
	if($showPartOfs)	
	  $title.=LangListQueryObjectsMessage10;
	else
    $title.=LangListQueryObjectsMessage11;
  if($myList)
    $title.="&nbsp;-&nbsp;<a href=\"".$link."&amp;min=".$min."&amp;addAllObjectsFromQueryToList=true\" title=\"".LangListQueryObjectsMessage5.$listname_ss."\">".LangListQueryObjectsMessage4."</a>";
  $title.="</h4>";
  list ($min,$max,$content) = $objUtil->printNewListHeader3($_SESSION['Qobj'],$link,$min,$step);
  $objPresentations->line(array($title,$content),"LR",array(70,30),30);
  $content1=" - <a href=\"".$link."&amp;noShowName=noShowName\">".LangListQueryObjectsMessage17."</a>";
  $content2=$objUtil->printStepsPerPage3($link,"selObj",$step);
  if($showPartOfs)
    $objPresentations->line(array("<a href=\"".$link."&amp;showPartOfs=0\">".LangListQueryObjectsMessage12."</a>".$content1,$content2),"LR",array(70,30),20);
	else
    $objPresentations->line(array("<a href=\"".$link."&amp;showPartOfs=1\">".LangListQueryObjectsMessage13."</a>".$content1,$content2),"LR",array(70,30),20);
  $link.="&amp;showPartOfs=".$showPartOfs;
	echo "<hr />";
	$_GET['min']=$min;
	$_GET['max']=$max;
	if($FF)
	{ echo "<script type=\"text/javascript\">";
    echo "theResizeElement='obj_list';";
    echo "theResizeSize=80;";
    echo "</script>";
	}
	$objObject->showObjects($link, $min, $max,'',0, $step,'',"execute_query_objects");
	echo "<hr />";
	$content1 =LangExecuteQueryObjectsMessage4."&nbsp;";
	$content1.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4a);
	$content1.="&nbsp;-&nbsp;";
	$content1.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b);
	$content1.="&nbsp;-&nbsp;";
	$content1.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=".$_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c);
	$content1.="&nbsp;-&nbsp;";
	$content1.="<a href=\"".$baseURL."objects.argo?SID=Qobj\" rel=\"external\">".LangExecuteQueryObjectsMessage8."</a>";
	$content1.="&nbsp;-&nbsp;";
  if(array_key_exists('listname',$_SESSION)&&$_SESSION['listname']&&$myList)
	  $content1.="<a href=\"".$link."&amp;min=".$min."&amp;addAllObjectsFromQueryToList=true\" title=\"".LangListQueryObjectsMessage5.$_SESSION['listname']."\">".LangListQueryObjectsMessage4."</a>"."&nbsp;-&nbsp;";
	$content1.="<a href=\"".$baseURL."objects.csv?SID=Qobj\" rel=\"external\">".LangExecuteQueryObjectsMessage6."</a>";
	if($loggedUser)
	  $content1.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=reportsLayout&amp;reportname=ReportQueryOfObjects&amp;reporttitle=ReportQueryOfObjects&amp;SID=Qobj&amp;sort=".$_SESSION['QobjSort']."&amp;pdfTitle=Test\" >".ReportLink."</a>";
  $content1.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=objectsSets"."\" rel=\"external\">".LangExecuteQueryObjectsMessage11."</a>";
	$objPresentations->line(array($content1),"L",array(100),20);
  echo "</div>";
}
else // ========================================================================no results found
{ echo "<div id=\"main\">";
  $objPresentations->line("<h4>".LangSelectedObjectsTitle."</h4>","L",array(),30);
  $objPresentations->line(array(LangExecuteQueryObjectsMessage2),"L");
  $objPresentations->line(array("<a href=\"".$baseURL."index.php?indexAction=query_objects\">".LangExecuteQueryObjectsMessage2a."</a>"),"L");
	echo "</div>";
}

?>
