<?php
// language.php
// menu which allows a non-registered user to change the language he sees the information in 

if($languageMenu==1)
{ echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
  echo "<tr>";
  echo "<th valign=\"top\">";
  echo LangLanguageMenuTitle;
  echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>";
  echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"setLanguage\">";
  echo "<tr align=\"left\">";
	echo "<td>";
  echo "<p>";
	echo "<select name=\"language\" style=\"width: 147px\" >";
  $previous_language=$_SESSION['lang'];
  $languages = $objLanguage->getLanguages();
  while(list ($key, $value) = each($languages))
    echo "<option value=\"".$key."\"".(($key==$_SESSION['lang'])?" selected=\"selected\"":'').">".$value."</option>";
  echo "</select>";
	echo "<br />";
	echo "<input type=\"submit\" style=\"width: 147px\" name=\"change_language\" value=\"";
  echo LangLanguageMenuButton;
  echo "\" />";
  echo "</td>";
	echo "</tr>";
	echo "</form>";
	echo "</table>";
}
?>