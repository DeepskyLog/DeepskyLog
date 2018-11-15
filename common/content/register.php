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
	echo "<input type=\"hidden\" name=\"title\" value=\"" . _("Register") . "\" />";
	echo "<h4>" . _("Register");
	echo "<input class=\"btn btn-success pull-right\" type=\"submit\" name=\"register\" value=\"" . _("Register") . "\" />";
	echo "&nbsp;</h4>";
	echo "<hr />";

	echo "<div class=\"form-group\">
	       <label>" . _("Username") . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"deepskylog_id\" required size=\"50\" value=\"" . $objUtil->checkPostKey ( 'deepskylog_id' ) . "\" />";
	echo "<span class=\"help-block\">" . _("This is the name you will use to log in") . "</span>";
	echo "</div>";


	echo "<div class=\"form-group\">
	       <label>" . _("Email address") . "</label>";
	echo "<input type=\"email\" class=\"form-control\" maxlength=\"64\" name=\"email\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'email' ) . "\" />";
	echo "<span class=\"help-block\">" . _("Your email address will remain confidential") . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . _("First name") . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"firstname\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'firstname' ) . "\" />";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . _("Last Name") . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"name\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'name' ) . "\" />";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . _("Motivation") . "</label>";
	echo "<input type=\"text\" class=\"form-control\" maxlength=\"64\" name=\"motivation\" size=\"120\" required value=\"" . $objUtil->checkPostKey ( 'explanation' ) . "\" />";
    echo "<span class=\"help-block\">" . 
        _("Please tell us briefly why you register, this allows us to eliminate automatic registrations.") . 
        "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . _("Password") . "</label>";
	echo "<input type=\"password\" class=\"strength\" maxlength=\"64\" name=\"passwd\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'passwd' ) . "\" />";
	echo "<span class=\"help-block\">" . _("This is not your email account's password") . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . _("Confirm password") . "</label>";
	echo "<input type=\"password\" class=\"form-control\" maxlength=\"64\" name=\"passwd_again\" size=\"50\" required value=\"" . $objUtil->checkPostKey ( 'passwd_again' ) . "\" />";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . _("Standard language for observations") . "</label><br />";
	echo "<span class=\"form-inline\">" . $tempAllList . "</span>";
	echo "<span class=\"help-block\">" . _("The standard language to enter the observations") . "</span>";
	echo "</div>";

	echo "<div class=\"form-group\">
	       <label>" . _("Default language") . "</label><br />";
	echo "<span class=\"form-inline\">" . $tempList . "</span>";
	echo "<span class=\"help-block\">" . _("The language for DeepskyLog") . "</span>";
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
	       <label>" . _("Copyright notice") . "</label>";
	echo "<input type=\"text\" disabled id=\"copyright\" class=\"form-control\" maxlength=\"128\" name=\"copyright\" size=\"40\" value=\"" . $objObserver->getObserverProperty ( $objUtil->checkSessionKey ( 'deepskylog_id' ), 'copyright' ) . "\" />";
	echo "<span class=\"help-block\">" . _("You can specify a copyright notice that will appear under your observations and drawings.") . "</span>";
	echo "</div>";

	reset ( $allLanguages );
	$usedLanguages = $languagesDuringRegistration;

	echo "<div class=\"form-group\">";
	echo "<label>" . _("Languages for observations") . "</label>";
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
    
    echo sprintf(
        _("Your personal information will be processed in accordance with the %sprivacy policy%s and shall be used only for user management and to keep you informed about our activities. "), 
        "<a href='" . $baseURL . "/index.php?indexAction=privacy'>", "</a>"
    ) . "<br /><br />";

	echo "<input class=\"btn btn-success\" type=\"submit\" name=\"register\" value=\"" . _("Register") . "\" />";
	echo "</div></div>";
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
