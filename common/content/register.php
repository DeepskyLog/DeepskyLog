<?php 
// register.php
// allows the user to apply for an deepskylog account

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else register();

function register()
{ global $baseURL, $step, $defaultLanguage, $languagesDuringRegistration , $standardLanguagesForObservationsDuringRegistration,
         $objObserver,$objLanguage,$objPresentations,$objUtil;	
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
	echo "<form action=\"".$baseURL."index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_account\" />";
	echo "<input type=\"hidden\" name=\"title\" value=\"".LangRegisterNewTitle."\" />";
	$objPresentations->line(array("<h4>".LangRegisterNewTitle."</h4>","<input type=\"submit\" name=\"register\" value=\"" . LangRegisterNewTitle . "\" />&nbsp;"),"LR",array(80,20),30);
	echo "<hr />";
	$objPresentations->line(array(LangChangeAccountField1,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"deepskylog_id\" size=\"30\" value=\"".$objUtil->checkPostKey('deepskylog_id')."\" />",LangChangeAccountField1Expl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	$objPresentations->line(array(LangChangeAccountField2,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"email\" size=\"30\" value=\"".$objUtil->checkPostKey('email')."\" />",LangChangeAccountField2Expl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	$objPresentations->line(array(LangChangeAccountField3,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"firstname\" size=\"30\" value=\"".$objUtil->checkPostKey('firstname')."\" />",LangChangeAccountField3Expl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	$objPresentations->line(array(LangChangeAccountField4,"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"name\" size=\"30\" value=\"".$objUtil->checkPostKey('name')."\" />",LangChangeAccountField4Expl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	$objPresentations->line(array(LangChangeAccountField5,"<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd\" size=\"30\" value=\"".$objUtil->checkPostKey('passwd')."\" />",LangChangeAccountField5Expl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	$objPresentations->line(array(LangChangeAccountField6,"<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd_again\" size=\"30\" value=\"".$objUtil->checkPostKey('passwd_again')."\" />",LangChangeAccountField6Expl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	$objPresentations->line(array(LangChangeAccountObservationLanguage,$tempAllList,LangChangeAccountObservationLanguageExpl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	$objPresentations->line(array(LangChangeAccountLanguage,$tempList,LangChangeAccountLanguageExpl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	$objPresentations->line(array(LangChangeAccountCopyright,"<input type=\"text\" class=\"inputfield\" maxlength=\"128\" name=\"copyright\" size=\"40\" value=\"".$objObserver->getObserverProperty($objUtil->checkSessionKey('deepskylog_id'),'copyright')."\" />",LangChangeAccountCopyrightExpl),"RLL",array(20,40,40),'',array('fieldname','fieldvalue','fieldexplanation'));
	
	
	reset($allLanguages);
	$usedLanguages=$languagesDuringRegistration;
	$j=0;
	$tempObsLangList[]=LangChangeVisibleLanguages;
	while(($j<3)&&(list($key,$value)=each($allLanguages)))
	{ $tempObsLangList[]="<input type=\"checkbox\" ".(($objUtil->checkPostKey($key)||in_array($key,$usedLanguages))?"checked=\"checked\" ":"")." name=\"".$key."\" value=\"".$key."\" />".$value;
	  $j++;
	}
	$tempObsLangList[]=LangChangeVisibleLanguagesExpl;
	$objPresentations->line($tempObsLangList,"RLLLL",array(20,13,13,14,40),'',array("fieldname","fieldvalue","","","fieldexplanation"));
	unset($tempObsLangList);
	$tempObsLangList[]="";
	while((list($key,$value)=each($allLanguages)))
	{ $tempObsLangList[]="<input type=\"checkbox\" ".(($objUtil->checkPostKey($key)||in_array($key,$usedLanguages))?"checked=\"checked\" ":"")." name=\"".$key."\" value=\"".$key."\" />".$value;
	  $j++;
	  if(($j%3)==0)
	  { $tempObsLangList[]="";
	    $objPresentations->line($tempObsLangList,"RLLLL",array(20,13,13,14,40),'',array("fieldname","fieldvalue","","","fieldexplanation"));
	    unset($tempObsLangList);
	    $tempObsLangList[]="";
	  }
	}	
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>