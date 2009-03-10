<?php // register.php - allows the user to apply for an deepskylog account
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else
{
$allLanguages=$objLanguage->getAllLanguages($objUtil->checkArrayKey($_SESSION,'lang',$standardLanguagesForObservationsDuringRegistration));
$theAllKey=$objUtil->checkPostKey('description_language',$objUtil->checkArrayKey($_SESSION,'lang',$standardLanguagesForObservationsDuringRegistration));
$tempAllList="<select name=\"description_language\" class=\"fieldvaluedropdown\">";
while(list($key,$value)=each($allLanguages))
  $tempAllList.="<option value=\"".$key."\" ".(($theAllKey==$key)?"selected=\"selected\"":"").">".$value."</option>";
$tempAllList.="</select>";
$languages=$objLanguage->getLanguages();
$theKey=$objUtil->checkPostKey('language',$objUtil->checkArrayKey($_SESSION,'lang',$defaultLanguage));
$tempList="<select name=\"language\" class=\"fieldvaluedropdown\">";
while(list($key,$value)=each($languages))
  $tempList.="<option value=\"".$key."\"".(($theKey=$key)?" selected=\"selected\"":"").">".$value."</option>";
$tempList.="</select>";
echo "<div id=\"main\">";
echo "<h2>".LangRegisterNewTitle."</h2>";        
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_account\">";
echo "<table width=\"100%\">";
tableFieldnameFieldExplanation(LangChangeAccountField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkPostKey('deepskylog_id')."\" />",LangChangeAccountField1Expl);
tableFieldnameFieldExplanation(LangChangeAccountField2,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"email\" size=\"30\" value=\"".$objUtil->checkPostKey('email')."\" />",LangChangeAccountField2Expl);
tableFieldnameFieldExplanation(LangChangeAccountField3,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"firstname\" size=\"30\" value=\"".$objUtil->checkPostKey('firstname')."\" />",LangChangeAccountField3Expl);
tableFieldnameFieldExplanation(LangChangeAccountField4,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"name\" size=\"30\" value=\"".$objUtil->checkPostKey('name')."\" />",LangChangeAccountField4Expl);
tableFieldnameFieldExplanation(LangChangeAccountField5,"<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd\" size=\"30\" value=\"".$objUtil->checkPostKey('passwd')."\" />",LangChangeAccountField5Expl);
tableFieldnameFieldExplanation(LangChangeAccountField6,"<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd_again\" size=\"30\" value=\"".$objUtil->checkPostKey('passwd_again')."\" />",LangChangeAccountField6Expl);
tableFieldnameFieldExplanation(LangChangeAccountObservationLanguage,$tempAllList,LangChangeAccountObservationLanguageExpl);
tableFieldnameFieldExplanation(LangChangeAccountLanguage,$tempList,LangChangeAccountLanguageExpl);
echo "<tr>";
echo "<td class=\"fieldname\" align=\"right\">".LangChangeVisibleLanguages."</td>";
echo "<td class=\"fieldvalue\">";
$allLanguages=$objLanguage->getAllLanguages($objUtil->checkArrayKey($_SESSION,'lang',$defaultLanguage));
$_SESSION['alllanguages']=$allLanguages; 
$usedLanguages=$languagesDuringRegistration;
echo "<table><tr>";
$j=0;
while(list($key,$value)=each($allLanguages))
{ if(!($j++%3))
    echo "</tr><tr>";
	echo "<td><input type=\"checkbox\" ".(($objUtil->checkPostKey($key)||in_array($key,$usedLanguages))?"checked ":"")." name=\"".$key."\" value=\"".$key."\" />".$value."</td>";
}
echo "</tr></table>";
echo "</td>";
echo "<td class=\"fieldexplanation\">".LangChangeVisibleLanguagesExpl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td style=\"text-align: right\">";
echo "<input type=\"submit\" name=\"register\" value=\"" . LangRegisterNewTitle . "\" />";
echo "</td>";
echo "<td></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
}
?>