<?php // change_filter.php - form which allows the filter owner to change a filter
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif(!($filterid=$objUtil->checkGetKey('filter'))) throw new Exception(LangException005);
elseif(!($objUtil->checkUserID($objFilter->getFilterPropertyFromId($filterid,'observer','')))) throw new Exception(LangExcpetion006);
//elseif(!($objFilter->getFilterPropertyFromId($filterid,'name')))  throw new Exception("Filter not found in change_filter.php, please contact the developers with this message:".$filterid);
else
{
$filter=$objFilter->getFilterPropertiesFromId($filterid);
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
}
?>
