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
	echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
  echo "<form action=\"".$_SESSION['module']."/index.php\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"setLanguage\">";
  echo "<tr align=\"left\">";
	echo "<td>";
  echo "<p>";
	echo "<select name=\"language\">";
  $previous_language=$_SESSION['lang'];
  $languages = $objLanguage->getLanguages();
  while(list ($key, $value) = each($languages))
    echo "<option value=\"" . $key . "\"".($key==$_SESSION['lang']?" selected=\"selected\"":'').">".$value."</option>";
  echo "</select>";
	echo "</p><p>";
	echo "<input type=\"submit\" name=\"change_language\" value=\"";
  echo LangLanguageMenuButton;
  echo "\" />";
  echo "</td>";
	echo "</tr>";
	echo "</form>";
}
?>

