<?php // tolist.php - manages and shows lists
echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
echo "<div id=\"main\">";
if($loggedUser)
{ echo "<form action=\"".$baseURL."index.php?indexAction=listaction\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />";
	$content1 =LangToListAddNew;
	$content1.="<input type=\"text\" class=\"inputfield\" name=\"addlistname\" size=\"35\" value=\"\" />";
	$content1.="<input type=\"checkbox\" name=\"PublicList\" value=\"" . LangToListPublic . "\" />".LangToListPublic;
	$content1.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$content1.="<input class=\"width125px\" type=\"submit\" name=\"addList\" value=\"" . LangToListAdd . "\" />";
	if($myList)
	  $content1.="<input class=\"width125px\" type=\"submit\" name=\"renameList\" value=\"" . LangToListRename . "\" />";
	$objPresentations->line(array($content1),"L",array(100));
	echo "</div></form>";
	echo "<hr />";
}
if($listname)
{ $link = $baseURL."index.php?indexAction=listaction";
  reset($_GET);
  while(list($key,$value)=each($_GET))
    if(!in_array($key,array('addobservationstolist','restoreColumns','orderColumn','loadLayout','saveLayout','removeLayout','indexAction','multiplepagenr','sort','sortdirection','showPartOfs','noShowName')))
      $link.='&amp;'.urlencode($key).'='.urlencode($value);
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
  list($min, $max,$content,$pageleft,$pageright,$pagemax)=$objUtil->printNewListHeader4($_SESSION['Qobj'], $link, $min, $step, "");	
  $objPresentations->line(array("<h4>".LangSelectedObjectsTitle." ".$listname_ss. "</h4>",
                                $content),
                          "LR", array(60,40),30);  
  $content2=$objUtil->printStepsPerPage3($link,"listObj",$step);
  $content1="";
  if($myList)
  { $content1 ="<a href=\"".$baseURL."index.php?indexAction=import_csv_list\">" .  LangToListImport . "</a> - ";
  	$content1.="<a href=\"".$baseURL."index.php?indexAction=listaction&amp;emptyList=emptyList\">".LangToListEmpty."</a> - ";
    $content1.="<a href=\"".$baseURL."index.php?indexAction=listaction&amp;removeList=removeList\">".LangToListMyListsRemove."</a> - ";
    $content1.="<a href=\"".$baseURL."index.php?indexAction=listaction&amp;addobservationstolist=longest\">".LangToListMyListsAddLongestObsDescription."</a> - ";
    $content1.="<a href=\"".$baseURL."index.php?indexAction=listaction&amp;removeobservationsfromlist=all\">".LangToListMyListsRemoveObsDescription."</a>";
  }
  else
    $content1="(".LangToListListBy.$objObserver->getObserverProperty(($listowner=$objList->getListOwner()),'firstname').' '.$objObserver->getObserverProperty($listowner,'name').")";
  $content1.=" - <a href=\"".$link."&amp;noShowName=noShowName\">".LangListQueryObjectsMessage17."</a>";
  $objPresentations->line(array($content1,$content2),"LR",array(80,20),20);
  if(count($_SESSION['Qobj'])>0)
	{ // OUTPUT RESULT
    echo "<hr />";
	  $_GET['min']=$min;
	  $_GET['max']=$max;
	  if($FF)
	  { echo "<script type=\"text/javascript\">";
      echo "theResizeElement='obj_list';";
      echo "theResizeSize=90;";
      echo "</script>";
  	}
	  $objObject->showObjects($link, $min, $max,'',1, $step,"removePageObjectsFromList","tolist");
	  echo "<hr />";
	  /*
	  $content=LangExecuteQueryObjectsMessage4."&nbsp;";
    $content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4a);
	  $content.="&nbsp;-&nbsp;";
    $content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b);
	  $content.="&nbsp;-&nbsp;";
    $content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,$listname_ss,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=" . $_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c);
	  $content.="&nbsp;-&nbsp;";
    $content.="<a href=\"objects.argo?SID=Qobj\" rel=\"external\">".LangExecuteQueryObjectsMessage8."</a> &nbsp;-&nbsp;";
    $content.="<a href=\"objectslist.csv?SID=Qobj\" rel=\"external\">".LangExecuteQueryObjectsMessage6."</a>";
	  if($loggedUser)
	    $content.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=reportsLayout&amp;reportname=ReportQueryOfObjects&amp;reporttitle=ReportQueryOfObjects&amp;SID=Qobj&amp;sort=".$_SESSION['QobjSort']."&amp;pdfTitle=Test\" >".ReportLink."</a>";
	  $content.="&nbsp;-&nbsp;";
    $content.="<a href=\"".$baseURL."index.php?indexAction=objectsSets"."\" rel=\"external\">".LangExecuteQueryObjectsMessage11."</a>";
	  $objPresentations->line(array($content),"L",array(),30);
	  */
	}
	else
	{ echo "<hr />";$objPresentations->line(array(LangToListEmptyList),"L",array(100),50);
	}
  echo "<script type=\"text/javascript\">";
  echo "
  function pageOnKeyDownToList(event)
  { if(event.keyCode==37)
      if(event.shiftKey)
        if(event.ctrlKey)
          location=html_entity_decode('".$link."&amp;multiplepagenr=0"."');    
        else
          location=html_entity_decode('".$link."&amp;multiplepagenr=".$pageleft."');
    if(event.keyCode==39)
      if(event.shiftKey) 
        if(event.ctrlKey)
          location=html_entity_decode('".$link."&amp;multiplepagenr=".$pagemax."');
        else  
          location=html_entity_decode('".$link."&amp;multiplepagenr=".$pageright."');
  }
  this.onKeyDownFns[this.onKeyDownFns.length] = pageOnKeyDownToList;
  ";
  echo "</script>";
	
}
echo "</div>";


?>
