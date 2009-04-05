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
  echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"setLanguage\">";
	echo "<tr>";
	echo "<td style=\"padding-top:5px\">";
	echo "<select name=\"language\" style=\"width: 147px\" onchange=\"this.form.submit()\">";
  $previous_language=$_SESSION['lang'];
  $languages = $objLanguage->getLanguages();
  while(list ($key, $value) = each($languages))
    echo "<option value=\"".$key."\"".(($key==$_SESSION['lang'])?" selected=\"selected\"":'').">".$value."</option>";
  echo "</select>";
  echo "</td>";
	echo "</tr>";
	echo "</form>";
	echo "</table>";
}
?>