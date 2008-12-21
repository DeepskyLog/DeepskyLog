<?php
// change_filter.php
// form which allows the administrator to change a filter

echo "<div id=\"main\">";
echo "<h2>".stripslashes($objFilter->getFilterName($_GET['filter']))."</h2>";
echo "<hr>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\" />";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_filter\">";
echo "<input type=\"hidden\" name=\"id\"          value=\"".$_GET['filter']."\" />";
echo "<table>";
tableFieldnameFieldExplanation(LangAddFilterField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"filtername\" size=\"30\" value=\"".stripslashes($objFilter->getFilterName($_GET['filter']))."\" />",LangAddFilterField1Expl);
tableFieldnameField(LangAddFilterField2,$objFilter->getEchoListType($objFilter->getFilterType($_GET['filter'])));
tableFieldnameField(LangAddFilterField3,$objFilter->getEchoListColor($objFilter->getColor($_GET['filter'])));
tableFieldnameField(LangAddFilterField4,"<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"wratten\" size=\"5\" value=\"".stripslashes($objFilter->getWratten($_GET['filter']))."\" />");
tableFieldnameField(LangAddFilterField5,"<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"schott\" size=\"5\" value=\"".stripslashes($objFilter->getSchott($_GET['filter']))."\" />");
echo "</table>";
echo "<hr />";
echo "<p><input type=\"submit\" name=\"change\" value=\"".LangChangeFilterButton."\" /></p>";
echo "</form>";
echo "</div>";
?>
