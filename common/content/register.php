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
	$tempAllList = "<select name=\"description_language\" class=\"form-control\">";
	while ( list ( $key, $value ) = each ( $allLanguages ) )
		$tempAllList .= "<option value=\"" . $key . "\" " . (($theAllKey == $key) ? "selected=\"selected\"" : "") . ">" . $value . "</option>";
	$tempAllList .= "</select>";
	$languages = $objLanguage->getLanguages ();
	$theKey = $objUtil->checkPostKey ( 'language', $objUtil->checkArrayKey ( $_SESSION, 'lang', $defaultLanguage ) );
	$tempList = "<select name=\"language\" class=\"form-control\">";
	while ( list ( $key, $value ) = each ( $languages ) )
		$tempList .= "<option value=\"" . $key . "\"" . (($theKey = $key) ? " selected=\"selected\"" : "") . ">" . $value . "</option>";
	$tempList .= "</select>";
	echo "<div id=\"main\">";
	echo "<form action=\"" . $baseURL . "index.php\" method=\"post\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_account\" />";
	echo "<input type=\"hidden\" name=\"title\" value=\"" . LangRegisterNewTitle . "\" />";
	echo "<h4>" . LangRegisterNewTitle;
	echo "<input class=\"btn btn-success pull-right\" type=\"submit\" name=\"register\" value=\"" . LangRegisterNewTitle . "\" />";
	echo "&nbsp;</h4>";
	echo "<hr />";

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountField1 . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"deepskylog_id\" required size=\"50\" value=\"" . $objUtil->checkPostKey ( 'deepskylog_id' ) . "\" />";
	echo "<span class=\"help-block\">" . LangChangeAccountField1Expl . "</span>";
	echo "</div>";


	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountField2 . "</label>";
	echo "<input type=\"email\" class=\"form-control\" maxlength=\"64\" name=\"email\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'email' ) . "\" />";
	echo "<span class=\"help-block\">" . LangChangeAccountField2Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountField3 . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"firstname\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'firstname' ) . "\" />";
	echo "<span class=\"help-block\">" . LangChangeAccountField3Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountField4 . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"name\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'name' ) . "\" />";
	echo "<span class=\"help-block\">" . LangChangeAccountField4Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountField13 . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"motivation\" size=\"120\" required value=\"" . $objUtil->checkPostKey ( 'explanation' ) . "\" />";
	echo "<span class=\"help-block\">" . LangChangeAccountField13Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountField5 . "</label>";
	echo "<input type=\"password\" class=\"strength\" maxlength=\"64\" name=\"passwd\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'passwd' ) . "\" />";
	echo "<span class=\"help-block\">" . LangChangeAccountField5Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountField6 . "</label>";
	echo "<input type=\"password\" class=\"form-control\" maxlength=\"64\" name=\"passwd_again\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'passwd_again' ) . "\" />";
	echo "<span class=\"help-block\">" . LangChangeAccountField6Expl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountObservationLanguage . "</label><br />";
	echo "<span class=\"form-inline\">" . $tempAllList . "</span>";
	echo "<span class=\"help-block\">" . LangChangeAccountObservationLanguageExpl . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountLanguage . "</label><br />";
	echo "<span class=\"form-inline\">" . $tempList . "</span>";
	echo "<span class=\"help-block\">" . LangChangeAccountLanguageExpl . "</span>";
	echo "</div>";

	// javascript to disable the copyright field when one of the CC options is selected.
  echo '<script>
          function enableDisableCopyright() {
            var selectBox = document.getElementById("cclicense");
            var selectedValue = selectBox.options[selectBox.selectedIndex].value;
            if (selectedValue == 7) {
              document.getElementById("copyright").disabled=false;
            } else {
              document.getElementById("copyright").disabled=true;
            }
          }
        </script>';

  echo '<div class="form-group">
          <label>' . _("License for drawings") . '</label><br />
          <span class="form-inline">
            <select name="cclicense" id="cclicense" onchange="enableDisableCopyright();" class="inputfield form-control">';
  echo '<option value="0" selected>Attribution CC BY</option>';
  echo '<option value="1">Attribution-ShareAlike CC BY-SA</option>';
  echo '<option value="2">Attribution-NoDerivs CC BY-ND</option>';
  echo '<option value="3">Attribution-NonCommercial CC BY-NC</option>';
  echo '<option value="4">Attribution-NonCommercial-ShareAlike CC BY-NC-SA</option>';
  echo '<option value="5">Attribution-NonCommercial-NoDerivs CC BY-NC-ND</option>';
  echo '<option value="6">' . _("No license (Not recommended!)") . '</option>';
  echo '<option value="7">' . _("Enter your own copyright text") . '</option>';

  echo '    </select>
          </span>
          <span class="help-block">' .
          _('It is important to select the <strong>correct license for your drawings</strong>! For help, see the <a href="http://creativecommons.org/choose/">Creative Commons license-choosing tool</a>.') . '
          </span>
        </div>';

	echo "<div class=\"form-group\">
	       <label>" . LangChangeAccountCopyright . "</label>";
	echo "<input type=\"text\" disabled id=\"copyright\" class=\"form-control\" maxlength=\"128\" name=\"copyright\" size=\"40\" value=\"" . $objObserver->getObserverProperty ( $objUtil->checkSessionKey ( 'deepskylog_id' ), 'copyright' ) . "\" />";
	echo "<span class=\"help-block\">" . LangChangeAccountCopyrightExpl . "</span>";
	echo "</div>";

	reset ( $allLanguages );
	$usedLanguages = $languagesDuringRegistration;

	echo "<div class=\"form-group\">";
	echo "<label>" . LangChangeVisibleLanguages . "</label>";
	echo "<table class=\"table table-condensed borderless\">";

	$j = 0;
	echo "<tr>";
	while((list($key,$value)=each($allLanguages)))
	{ echo "<td><label class=\"checkbox-inline\"><input type=\"checkbox\" " . (($objUtil->checkPostKey ( $key ) || in_array ( $key, $usedLanguages )) ? "checked=\"checked\" " : "") . " name=\"" . $key . "\" value=\"" . $key . "\" />" . $value;
	if (($j + 1) % 3 == 0) {
		echo "</tr><tr>";
	}
	$j++;
	}
	for ($i = $j % 3;$i < 3;$i++) {
		echo "<td></td>";
	}
	echo "</tr></table>";

	echo "<input class=\"btn btn-success\" type=\"submit\" name=\"register\" value=\"" . LangRegisterNewTitle . "\" />";
	echo "</div></div>";
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
