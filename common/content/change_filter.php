<?php // change_filter.php - form which allows the filter owner to change a filter
if(!$loggedUser)
  throw new Exception("No logged user in change_filter.php, please contact the developers with this message.");
if(!($filterid=$objUtil->checkGetKey('filter')))
  throw new Exception("No filter specified in change_filter.php, please contact the developers with this message.");
if(!($objFilter->getFilterPropertyFromId($filterid,'name')))
  throw new Exception("Filter not found in change_filter.php, please contact the developers with this message:".$filterid);
$filter=$objFilter->getFilterPropertiesFromId($_GET['filter']);
echo "<div id=\"main\">";
echo "<h2>".stripslashes($filter['name'])."</h2>";
echo "<hr>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\" />";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\">";
echo "<input type=\"hidden\" name=\"id\"          value=\"".$filterid."\" />";
echo "<table>";
tableFieldnameFieldExplanation(LangAddFilterField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($filter['name'])."\" />",LangAddFilterField1Expl);
tableFieldnameField(LangAddFilterField2,$objFilter->getEchoListType($filter['type']));
tableFieldnameField(LangAddFilterField3,$objFilter->getEchoListColor($filter['color']));
tableFieldnameField(LangAddFilterField4,"<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($filter['wratten'])."\" />");
tableFieldnameField(LangAddFilterField5,"<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($filter['schott'])."\" />");
echo "</table>";
echo "<hr />";
echo "<p><input type=\"submit\" name=\"change\" value=\"".LangChangeFilterButton."\" /></p>";
echo "</form>";
echo "</div>";
?>
