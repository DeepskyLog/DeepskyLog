<?php  
// overview_filters.php
// generates an overview of all filters (admin only)

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif($_SESSION['admin']!="yes") throw new Exception(LangException001);
else overview_filters();

function overview_filters()
{ global $baseURL, $step,
         $objFilter,$objPresentations,$objUtil;
	set_time_limit(60);
	$sort=$objUtil->checkGetKey('sort','name');
	$filts=$objFilter->getSortedFilters($sort,'%');
	if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
	{ if($_GET['sort']=="name")
	    $filts = array_reverse($filts, true);
	  else
	  { krsort($filts);
	    reset($filts);
	  }
	  $previous = ""; // reset previous field to sort on
	}
	else
	  $previous = $sort;
	// the code below is very strange but works
	if((isset($_GET['previous'])))
	  $orig_previous = $_GET['previous'];
	else
	  $orig_previous = "";
	$link=$baseURL."index.php?indexAction=view_filters&amp;sort=".$sort."&amp;previous=".$orig_previous;
	if((array_key_exists('steps',$_SESSION))&&(array_key_exists("allFilts",$_SESSION['steps'])))
	  $step=$_SESSION['steps']["allFilts"];
	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;
	$contentSteps=$objUtil->printStepsPerPage3($link,"allFilts",$step);
	list ($min,$max,$content) = $objUtil->printNewListHeader3($filts, $link, $min, $step);
	echo "<div id=\"main\">";
	$objPresentations->line(array("<h4>".LangOverviewFilterTitle."</h4>",$content),"LR",array(70,30),30);
	$objPresentations->line(array($contentSteps),"R",array(100),20);
	echo "<hr />";
	echo "<table>";
	echo "<tr class=\"type3\">";
	echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=name&amp;previous=$previous\">".LangViewFilterName."</a></td>";
	echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=type&amp;previous=$previous\">".LangViewFilterType."</a></td>";
	echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=color&amp;previous=$previous\">".LangViewFilterColor."</a></td>";
	echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=wratten&amp;previous=$previous\">".LangViewFilterWratten."</a></td>";
	echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=schott&amp;previous=$previous\">".LangViewFilterSchott."</a></td>";
	echo "<td><a href=\"".$baseURL."index.php?indexAction=view_filters&amp;sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
	echo "<td></td>";
	echo "</tr>";
	$count = 0;
	while(list($key,$value)=each($filts))
	{ if(($count>=$min)&&($count<$max))
	  { if($value!="1")
	    { echo "<tr class=\"type".(2-($count%2))."\">";
	      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_filter&amp;filter=".urlencode($value)."\">".stripslashes($objFilter->getFilterPropertyFromId($value,'name'))."</a></td>";
	      echo "<td>".$objFilter->getEchoType($objFilter->getFilterPropertyFromId($value,'type'))."</td>";
	      echo "<td>".$objFilter->getEchoColor($objFilter->getFilterPropertyFromId($value,'color'))."</td>";
	      echo "<td>".(($wratten=$objFilter->getFilterPropertyFromId($value,'wratten'))?$wratten:"-")."</td>";
	      echo "<td>".(($schott=$objFilter->getFilterPropertyFromId($value,'schott'))?$schott:"-")."</td>";
	      echo "<td>".$objFilter->getFilterPropertyFromId($value,'observer')."</td>";
	      echo "<td>";
	      if(!($objFilter->getFilterUsedFromId($value)))
	        echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_filter&amp;filterid=".urlencode($value)."\">".LangRemove."</a>";
	      echo "</td>";
	      echo "</tr>";
	    }
	  }
	  $count++;
	}
	echo "</table>";
	echo "<hr />";
	echo "</div>";
}
?>
