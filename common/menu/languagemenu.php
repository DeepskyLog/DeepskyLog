<?php
// language.php
// menu which allows a non-registered user to change the language he sees the information in 
//include_once "../lib/setup/databaseInfo.php";
//include_once "../lib/util.php";
//$util = new Util();
//$util->checkUserInput();
if ($languageMenu == 1)
{
  echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">\n
        <tr>\n
        <th valign=\"top\">\n";
  echo (LangLanguageMenuTitle);
  echo "</th>\n</tr>\n<tr>\n<td>\n
        <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
  echo "<form action=\"common/control/setLanguage.php\" method=\"post\">";
  echo "<tr align=\"left\">\n<td>
        <p>
        <select name=\"language\">";
  $previous_language = $_SESSION['lang'];
  include_once "../lib/setup/language.php";
  $language = new Language();
  $languages = $language->getLanguages();
  while(list ($key, $value) = each($languages))
  {
    if($previous_language == $value)
    {
      echo("<option value=\"" . $key . "\" selected=\"selected\">$value</option>\n");
    }
    else
    {
      echo("<option value=\"" . $key . "\">$value</option>\n");
    }
  }
  echo ("\n</select>\n</p>\n<p>\n<input type=\"submit\" name=\"change_language\" value=\"");
  echo (LangLanguageMenuButton);
  echo ("\" />");
  echo "</td></tr></form>";
}
?>

