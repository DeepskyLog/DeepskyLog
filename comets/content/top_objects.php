<?php  
// top_objects.php
// generates an overview of all observed objects and their rank 

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else top_objects();

function top_objects()
{ global $baseURL,$step,
         $objCometObject,$objCometObservation,$objPresentations,$objUtil;
	echo "<div id=\"main\">";
	if((array_key_exists('steps',$_SESSION))&&(array_key_exists("topComObj",$_SESSION['steps'])))
	  $step=$_SESSION['steps']["topComObj"];
	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;
	$rank = $objCometObservation->getPopularObservations();
	$link = $baseURL."index.php?indexAction=comets_rank_objects&amp;size=25";
	list($min, $max, $content) = $objUtil->printNewListHeader3($rank, $link, $min, $step, "");
	$content2=$objUtil->printStepsPerPage3($link,"topComObj",$step);
	$objPresentations->line(array("<h4>".LangTopObjectsTitle."</h4>",$content),"LR",array(60,40),30);
	$objPresentations->line(array($content2),"R",array(100),20);
	echo "<hr />";
	$count = 0;
	echo "<table>";
	echo "<tr class=\"type3\">";
	echo "<td>" . LangTopObjectsHeader1 . "</td>";
	echo "<td>" . LangTopObjectsHeader2 . "</td>";
	echo "<td>" . LangTopObjectsHeader5 . "</td>";
	echo "</tr>";
	while(list ($key, $value) = each($rank))
	{ if(($count>=$min)&&($count < $max))
	  { if($count%2)
	    { $type="class=\"type1\"";
	    }
	    else
	    { $type="class=\"type2\"";
	    }
	    echo "<tr $type><td>" . ($count + 1) . "</td><td> <a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($key) . "\">".$objCometObject->getName($key)."</a> </td>";
	    echo "<td> $value </td>";
	    echo"</tr>";
	  }
	  $count++;
	}
	echo "</table>";
	echo "<hr />";
	echo "</div>";
}
?>
