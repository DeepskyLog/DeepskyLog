<?php
// view_filter.php
// view information of a filter 

if(!$objUtil->checkGetKey('filter'))
  throw("No filter specified");
if(!($name=$objFilter->getFilterName($_GET['filter'])))
  throw("Filter not found");
  
$type=$objFilter->getFilterType($_GET['filter']);
$echoType=$objFilter->getEchoType($type);
$color = $objFilter->getColor($_GET['filter']);
$echoColor=$objFilter->getEchoColor($color);
$wratten=$objFilter->getWratten($_GET['filter']);
$schott = $objFilter->getSchott($_GET['filter']);

echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewFilterName,$name);
tableFieldnameField(LangViewFilterType,$echoType); 
tableFieldnameField(LangViewFilterColor,$echoColor);
if ($wratten != "")
  tableFieldnameField(LangViewFilterWratten,$wratten);
if($schott!="")
  tableFieldnameField(LangViewFilterSchott,$schott);
echo "</table>";
echo "</div>";
?>
