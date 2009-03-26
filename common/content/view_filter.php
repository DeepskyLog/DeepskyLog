<?php // view_filter.php - view information of a filter 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($filterid=$objUtil->checkGetKey('filter'))) throw new Exception(LangException005b);
//elseif(!($objFilter->getFilterPropertyFromId($filterid,'name')))  throw new Exception("Filter not found in change_filter.php, please contact the developers with this message:".$filterid);
else
{
$filter=$objFilter->getFilterPropertiesFromId($filterid);
echo "<div id=\"main\">";
echo "<h2>".$filter['name']."</h2>";
echo "<table>";
tableFieldnameField(LangViewFilterName,$filter['name']);
tableFieldnameField(LangViewFilterType,$objFilter->getEchoType($filter['type'])); 
tableFieldnameField(LangViewFilterColor,$objFilter->getEchoColor($filter['color']));
if($filter['wratten'])
  tableFieldnameField(LangViewFilterWratten,$filter['wratten']);
if($filter['schott'])
  tableFieldnameField(LangViewFilterSchott,$filter['schott']);
echo "</table>";
echo "</div>";
}
?>
