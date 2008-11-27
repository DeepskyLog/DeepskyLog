<?php
// register.php
// allows the user to apply for an deepskylog account

echo "<div id=\"main\">";
echo "<h2>".LangRegisterNewTitle."</h2>";        
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"common_control_validate_account\">";
echo "<table>";
tableFieldnameFieldExplanation(LangChangeAccountField1,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkPostKey('deepskylog_id')."\" />",LangChangeAccountField1Expl);
tableFieldnameFieldExplanation(LangChangeAccountField2,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"email\" size=\"30\" value=\"".$objUtil->checkPostKey('email')."\" />",LangChangeAccountField2Expl);
tableFieldnameFieldExplanation(LangChangeAccountField3,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"firstname\" size=\"30\" value=\"".$objUtil->checkPostKey('firstname')."\" />",LangChangeAccountField3Expl);
tableFieldnameFieldExplanation(LangChangeAccountField4,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"name\" size=\"30\" value=\"".$objUtil->checkPostKey('name')."\" />",LangChangeAccountField4Expl);
tableFieldnameFieldExplanation(LangChangeAccountField5,"<input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd\" size=\"30\" value=\"".$objUtil->checkPostKey('passwd')."\" />",LangChangeAccountField5Expl);
tableFieldnameFieldExplanation(LangChangeAccountField6,"<input type=\"password\" class=\"inputfield\" maxlength=\"64\" name=\"passwd_again\" size=\"30\" value=\"".$objUtil->checkPostKey('passwd_again')."\" />",LangChangeAccountField6Expl);
echo "<tr>";
echo "<td>".LangChangeAccountObservationLanguage."</td>";
echo "<td>";
$allLanguages=$objLanguage->getAllLanguages($objUtil->checkArrayKey($_SESSION,'lang',$standardLanguagesForObservationsDuringRegistration));
$theKey=$objUtil->checkPostKey('description_language',$objUtil->checkArrayKey($_SESSION,'lang',$standardLanguagesForObservationsDuringRegistration));
echo "<select name=\"description_language\">";
while(list($key,$value)=each($allLanguages))
  echo "<option value=\"".$key."\" ".(($theKey==$key)?"selected=\"selected\"":"").">".$value."</option>";
echo "</select>";
echo "</td>";
echo "<td class=\"explanation\">".LangChangeAccountObservationLanguageExpl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td>".LangChangeAccountLanguage."</td>";
echo "<td>";
echo "<select name=\"language\">";
$languages=$objLanguage->getLanguages();
$theKey=$objUtil->checkPostKey('language',$objUtil->checkArrayKey($_SESSION,'lang',$defaultLanguage));
while(list($key,$value)=each($languages))
  echo "<option value=\"".$key."\"".(($theKey=$key)?" selected=\"selected\"":"").">".$value."</option>";
echo "</select>";
echo "</td>";
echo "<td class=\"explanation\">".LangChangeAccountLanguageExpl."</td>";
echo "</tr>";   	 
echo "<tr>";
echo "<td class=\"fieldname\">".LangChangeVisibleLanguages."</td>";
echo "<td>";
$allLanguages=$objLanguage->getAllLanguages($objUtil->checkArrayKey($_SESSION,'lang',$defaultLanguage));
$_SESSION['alllanguages']=$allLanguages; 
$usedLanguages=$languagesDuringRegistration;
while(list($key,$value)=each($allLanguages))
  echo "<input type=\"checkbox\" ".(($objUtil->checkPostKey($key)||in_array($key,$usedLanguages))?"checked ":"")." name=\"".$key."\" value=\"".$key."\" />".$value."<br />";
echo "</td>";
echo "<td class=\"explanation\">".LangChangeVisibleLanguagesExpl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td>";
echo "<input type=\"submit\" name=\"register\" value=\"" . LangRegisterNewTitle . "\" />";
echo "</td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>