<?php
// register.php
// allows the user to apply for an deepskylog account
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	register ();
function register() {
	global $baseURL, $step, $defaultLanguage, $languagesDuringRegistration, $standardLanguagesForObservationsDuringRegistration, $objObserver, $objLanguage, $objPresentations, $objUtil;
	$allLanguages = $objLanguage->getAllLanguages ( $objUtil->checkArrayKey ( $_SESSION, 'lang', $standardLanguagesForObservationsDuringRegistration ) );
	$theAllKey = $objUtil->checkPostKey ( 'description_language', $objUtil->checkArrayKey ( $_SESSION, 'lang', $standardLanguagesForObservationsDuringRegistration ) );
	$tempAllList = "<select name=\"description_language\" class=\"fieldvaluedropdown\">";
	while ( list ( $key, $value ) = each ( $allLanguages ) )
		$tempAllList .= "<option value=\"" . $key . "\" " . (($theAllKey == $key) ? "selected=\"selected\"" : "") . ">" . $value . "</option>";
	$tempAllList .= "</select>";
	$languages = $objLanguage->getLanguages ();
	$theKey = $objUtil->checkPostKey ( 'language', $objUtil->checkArrayKey ( $_SESSION, 'lang', $defaultLanguage ) );
	$tempList = "<select name=\"language\" class=\"fieldvaluedropdown\">";
	while ( list ( $key, $value ) = each ( $languages ) )
		$tempList .= "<option value=\"" . $key . "\"" . (($theKey = $key) ? " selected=\"selected\"" : "") . ">" . $value . "</option>";
	$tempList .= "</select>";
	echo "<div id=\"main\">";
	echo "<form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_account\" />";
	echo "<input type=\"hidden\" name=\"title\" value=\"" . LangRegisterNewTitle . "\" />";
	echo "<h4>" . LangRegisterNewTitle . "</h4>";
    echo "<input type=\"submit\" name=\"register\" value=\"" . LangRegisterNewTitle . "\" />&nbsp;"; 
	echo "<hr />";
	$objPresentations->line ( array (
			LangChangeAccountField1,
			"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"deepskylog_id\" required placeholder=\"" . LangChangeAccountField1Expl . "\"size=\"50\" value=\"" . $objUtil->checkPostKey ( 'deepskylog_id' ) . "\" />" 
	), "RL", array (
			20,
			80 
	), array (
			'fieldname',
			'fieldvalue' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountField2,
			"<input type=\"email\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"email\" size=\"50\" required placeholder=\"" . LangChangeAccountField2Expl . "\" value=\"" . $objUtil->checkPostKey ( 'email' ) . "\" />" 
	), "RL", array (
			20,
			80 
	), array (
			'fieldname',
			'fieldvalue' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountField3,
			"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"firstname\" size=\"50\" required placeholder=\"" . LangChangeAccountField3Expl . "\" value=\"" . $objUtil->checkPostKey ( 'firstname' ) . "\" />" 
	), "RL", array (
			20,
			80 
	), '', array (
			'fieldname',
			'fieldvalue' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountField4,
			"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"name\" size=\"50\" required placeholder=\"" . LangChangeAccountField4Expl . "\" value=\"" . $objUtil->checkPostKey ( 'name' ) . "\" />" 
	), "RL", array (
			20,
			80 
	), '', array (
			'fieldname',
			'fieldvalue' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountField13,
			"<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"motivation\" size=\"120\" required placeholder=\"" . LangChangeAccountField13Expl . "\" value=\"" . $objUtil->checkPostKey ( 'explanation' ) . "\" />" 
	), "RL", array (
			20,
			80 
	), '', array (
			'fieldname',
			'fieldvalue' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountField5,
			"<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd\" size=\"50\" required placeholder=\"" . LangChangeAccountField5Expl . "\" value=\"" . $objUtil->checkPostKey ( 'passwd' ) . "\" />" 
	), "RL", array (
			20,
			80 
	), '', array (
			'fieldname',
			'fieldvalue' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountField6,
			"<input type=\"password\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"passwd_again\" size=\"50\" required placeholder=\"" . LangChangeAccountField6Expl . "\" value=\"" . $objUtil->checkPostKey ( 'passwd_again' ) . "\" />" 
	), "RL", array (
			20,
			80 
	), '', array (
			'fieldname',
			'fieldvalue' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountObservationLanguage,
			$tempAllList,
			LangChangeAccountObservationLanguageExpl 
	), "RLL", array (
			20,
			40,
			40 
	), '', array (
			'fieldname',
			'fieldvalue',
			'fieldexplanation' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountLanguage,
			$tempList,
			LangChangeAccountLanguageExpl 
	), "RLL", array (
			20,
			40,
			40 
	), '', array (
			'fieldname',
			'fieldvalue',
			'fieldexplanation' 
	) );
	$objPresentations->line ( array (
			LangChangeAccountCopyright,
			"<input type=\"text\" class=\"inputfield\" maxlength=\"128\" name=\"copyright\" size=\"40\" value=\"" . $objObserver->getObserverProperty ( $objUtil->checkSessionKey ( 'deepskylog_id' ), 'copyright' ) . "\" />",
			LangChangeAccountCopyrightExpl 
	), "RLL", array (
			20,
			40,
			40 
	), '', array (
			'fieldname',
			'fieldvalue',
			'fieldexplanation' 
	) );
	
	reset ( $allLanguages );
	$usedLanguages = $languagesDuringRegistration;
	$j = 0;
	$tempObsLangList [] = LangChangeVisibleLanguages;
	while ( ($j < 3) && (list ( $key, $value ) = each ( $allLanguages )) ) {
		$tempObsLangList [] = "<input type=\"checkbox\" " . (($objUtil->checkPostKey ( $key ) || in_array ( $key, $usedLanguages )) ? "checked=\"checked\" " : "") . " name=\"" . $key . "\" value=\"" . $key . "\" />" . $value;
		$j ++;
	}
	$tempObsLangList [] = LangChangeVisibleLanguagesExpl;
	$objPresentations->line ( $tempObsLangList, "RLLLL", array (
			20,
			13,
			13,
			14,
			40 
	), '', array (
			"fieldname",
			"fieldvalue",
			"",
			"",
			"fieldexplanation" 
	) );
	unset ( $tempObsLangList );
	$tempObsLangList [] = "";
	while ( (list ( $key, $value ) = each ( $allLanguages )) ) {
		$tempObsLangList [] = "<input type=\"checkbox\" " . (($objUtil->checkPostKey ( $key ) || in_array ( $key, $usedLanguages )) ? "checked=\"checked\" " : "") . " name=\"" . $key . "\" value=\"" . $key . "\" />" . $value;
		$j ++;
		if (($j % 3) == 0) {
			$tempObsLangList [] = "";
			$objPresentations->line ( $tempObsLangList, "RLLLL", array (
					20,
					13,
					13,
					14,
					40 
			), '', array (
					"fieldname",
					"fieldvalue",
					"",
					"",
					"fieldexplanation" 
			) );
			unset ( $tempObsLangList );
			$tempObsLangList [] = "";
		}
	}
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>