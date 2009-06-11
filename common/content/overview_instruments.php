<?php // overview_instruments.php - generates an overview of all instruments (admin only)
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif($_SESSION['admin']!="yes") throw new Exception(LangException001);
else
{
set_time_limit(180);
$sort=$objUtil->checkGetKey('sort','name');
if(!$min) $min=$objUtil->checkGetKey('min',0);
$telescopes=$objInstrument->getSortedInstruments($sort,'%');
$insts=$objObserver->getListOfInstruments();
// the code below is very strange but works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
if((isset($_GET['sort'])) && (isset($_GET['previous'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{ if ($_GET['sort'] != "")
    $telescopes = array_reverse($telescopes, true);
  else
  { krsort($telescopes);
    reset($telescopes);
  }
  $previous = ""; // reset previous field to sort on
}
else
  $previous = $sort;
$link=$baseURL."index.php?indexAction=view_instruments&amp;sort=".$sort."&amp;previous=".$orig_previous;
if((array_key_exists('steps',$_SESSION))&&(array_key_exists("allInsts",$_SESSION['steps'])))
  $step=$_SESSION['steps']["allInsts"];
if(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*$step;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*$step;
elseif(array_key_exists('min',$_GET))
  $min=$_GET['min'];
else
  $min = 0;
$contentSteps=$objUtil->printStepsPerPage3($link,"allInsts",$step);
list ($min,$max,$content) = $objUtil->printNewListHeader3($telescopes, $link, $min, $step);
echo "<div id=\"main\" style=\"position:relative\">";
$objPresentations->line(array("<h5>".LangOverviewInstrumentsTitle."</h5>",$content),"LR",array(70,30),30);
$objPresentations->line(array($contentSteps),"R",array(100),20);
echo "<hr />";
echo "<table width=\"100%\">";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_instruments&amp;sort=name&amp;previous=$previous\">".LangOverviewInstrumentsName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_instruments&amp;sort=diameter&amp;previous=$previous\">".LangOverviewInstrumentsDiameter."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_instruments&amp;sort=fd&amp;previous=$previous\">".LangOverviewInstrumentsFD."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_instruments&amp;sort=fixedMagnification&amp;previous=$previous\">".LangOverviewInstrumentsFixedMagnification."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_instruments&amp;sort=type&amp;previous=$previous\">".LangOverviewInstrumentsType."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_instruments&amp;sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
echo "<td></td>";
echo "</tr>";
$count = 0;
while(list ($key, $value) = each($telescopes))
{ if(($count>=$min)&&($count<$max))
  { $name = $objInstrument->getInstrumentPropertyFromId($value,'name');
	  $diameter = round($objInstrument->getInstrumentPropertyFromId($value,'diameter'), 0);
	  $fd = round($objInstrument->getInstrumentPropertyFromId($value,'fixedMagnification'), 1);
	  if ($fd == "0")
	    $fd = "-";
	  $type = $objInstrument->getInstrumentPropertyFromId($value,'type');
	  $fixedMagnification = $objInstrument->getInstrumentPropertyFromId($value,'fixedMagnification');
	  if ($fixedMagnification == "0")
	    $fixedMagnification = "-";
	  $observer = $objInstrument->getObserverFromInstrument($value);
	  echo "<tr class=\"type".(2-($count%2))."\">";
	  echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_instrument&amp;instrument=".urlencode($value)."\">".$name."</a></td>";
	  echo "<td>$diameter</td>";
	  echo "<td>$fd</td>";
	  echo "<td>$fixedMagnification</td>";
	  echo "<td>";
	  if($type == InstrumentReflector) {echo(InstrumentsReflector);}
	  if($type == InstrumentFinderscope) {echo(InstrumentsFinderscope);}
	  if($type == InstrumentRefractor) {echo(InstrumentsRefractor);}
	  if($type == InstrumentRest) {echo(InstrumentsOther);}
	  if($type == InstrumentBinoculars) {echo(InstrumentsBinoculars);}
	  if($type == InstrumentCassegrain) {echo(InstrumentsCassegrain);}
	  if($type == InstrumentSchmidtCassegrain) {echo(InstrumentsSchmidtCassegrain);}
	  if($type == InstrumentKutter) {echo(InstrumentsKutter);}
	  if($type == InstrumentMaksutov) {echo(InstrumentsMaksutov);}
	  echo "</td>";
	  echo "<td>".$observer."</td>";
	  echo "<td>";
	  //$queries = array("instrument" => $value);
	  //$obs = $objObservation->getObservationFromQuery($queries, "", "1", "False");
	  //$obscom = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
	  if(!($objInstrument->getInstrumentUsedFromId($value))) // no observations with instrument yet
	    echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_instrument&amp;instrumentid=".urlencode($value)."\">".LangRemove."</a>";
	  echo "</td>";
	  echo "</tr>";
  }
  $count++;
}
echo "</table>";
echo "</div>";
}
?>
