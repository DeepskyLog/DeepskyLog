<?php // view_filter.php - view information of a filter 
if(!$loggedUser)
  throw new Exception("No logged user in change_filter.php, please contact the developers with this message.");
if(!($filterid=$objUtil->checkGetKey('filter')))
  throw new Exception("No filter specified in change_filter.php, please contact the developers with this message.");
if(!($objFilter->getEyepiecePropertyFromId($filterid,'name')))
  throw new Exception("Filter not found in change_filter.php, please contact the developers with this message:".$filterid);
echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
tableFieldnameField(LangViewFilterName,$filter['name']);
tableFieldnameField(LangViewFilterType,$objFilter->getEchoType($type)); 
tableFieldnameField(LangViewFilterColor,$objFilter->getEchoColor($color));
if($filter['wratten'])
  tableFieldnameField(LangViewFilterWratten,$filter['wratten']);
if($filter['schott'])
  tableFieldnameField(LangViewFilterSchott,$filter['schott']);
echo "</table>";
echo "</div>";
?>
