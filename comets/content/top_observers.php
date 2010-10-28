<?php 
// top_observers.php
// generates an overview of all observers and their rank

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else top_observers();

function top_observers()
{ global $baseURL,$step,
         $objCometObservation,$objPresentations,$objObserver,$objUtil;
	$rank = $objCometObservation->getPopularObservers();
	$link = $baseURL."index.php?indexAction=comets_rank_observers&amp;size=25";
	if(isset($_GET['min']))
	{ $mini = $_GET['min'];
	}
	else
	{ $mini = '';
	}
	echo "<div id=\"main\">";
	if((array_key_exists('steps',$_SESSION))&&(array_key_exists("topComObs",$_SESSION['steps'])))
	  $step=$_SESSION['steps']["topComObs"];
	if(array_key_exists('multiplepagenr',$_GET))
	  $min = ($_GET['multiplepagenr']-1)*$step;
	elseif(array_key_exists('multiplepagenr',$_POST))
	  $min = ($_POST['multiplepagenr']-1)*$step;
	elseif(array_key_exists('min',$_GET))
	  $min=$_GET['min'];
	else
	  $min = 0;
	list($min, $max, $content) = $objUtil->printNewListHeader3($rank, $link, $min, $step, "");
	$content2=$objUtil->printStepsPerPage3($link,"topComObs",$step);
	$objPresentations->line(array("<h4>".LangTopObserversTitle."</h4>",$content),"LR",array(60,40),30);
	$objPresentations->line(array($content2),"R",array(100),20);
	echo "<hr />";
	$count = 0;
	echo "<table>";
	echo "<tr class=\"type3\">";
	echo "<td>" . LangTopObserversHeader1 . "</td>";
	echo "<td>" . LangTopObserversHeader2 . "</td>";
	echo "<td>" . LangTopObserversHeader3 . "</td>";
	echo "<td>" . LangTopObserversHeader4 . "</td>";
	echo "<td>" . LangTopObserversHeader6 . "</td>";
	echo "</tr>";
	$numberOfObservations = $objCometObservation->getNumberOfObservations();
	$numberOfObservationsThisYear = $objCometObservation->getNumberOfObservationsThisYear();
	$numberOfDifferentObjects = $objCometObservation->getNumberOfDifferentObjects();
	while(list($key,$value)=each($rank))
	{ if(($count >= $min)&&($count < $max))
	  { if($count % 2)
	    { $type = "class=\"type1\"";
	    }
	    else
	    { $type = "class=\"type2\"";
	    }
	    $name = $objObserver->getObserverProperty($value,'name');
	    $firstname = $objObserver->getObserverProperty($value,'firstname');
	    echo "<tr ".$type."><td>".($count + 1)."</td><td> <a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=" . urlencode($value) . "\">$firstname&nbsp;$name</a> </td>";
	    echo "<td> ".$objCometObservation->getObservationsThisObserver($value)." &nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", (($objCometObservation->getObservationsThisObserver($value) / $numberOfObservations) * 100))."%)</td>";
	    $objCometObservationThisYear = $objCometObservation->getObservationsThisYear($value);
	    if ($numberOfObservationsThisYear != 0)
	    { $percentObservations = ($objCometObservationThisYear / $numberOfObservationsThisYear) * 100;   }
	    else
	    { $percentObservations = 0;
	    }
	    echo "<td>". $objCometObservationThisYear . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", $percentObservations)."%)</td>";
	    $numberOfObjects = $objCometObservation->getNumberOfObjects($value);
	    echo "<td>". $numberOfObjects . "&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f", (($numberOfObjects / $numberOfDifferentObjects) * 100))."%)</td>";
	    echo("</tr>");
	  }
	  $count++;
	  }
	echo "<tr class=\"type3\"><td>".LangTopObservers1."</td><td></td><td>$numberOfObservations</td><td>$numberOfObservationsThisYear</td><td>$numberOfDifferentObjects</td></tr>";
	echo "</table>";
	echo "<hr />";
	echo "</div>";
}
?>