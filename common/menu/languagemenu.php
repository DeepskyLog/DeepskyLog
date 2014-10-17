<?php
// language.php
// menu which allows a non-registered user to change the language he sees the information in
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	menu_language ();
function menu_language() {
	global $baseURL, $languageMenu, $objLanguage;
	if ($languageMenu == 1) {
		echo "<div class=\"menuDiv\"><br />";
		echo "<form action=\"" . $baseURL . "index.php\" method=\"post\">";
		echo "<div>";
		echo "<input type=\"hidden\" name=\"indexAction\" value=\"setLanguage\" />";
		echo "<select name=\"language\" class=\"btn-default btn-sm\" onchange=\"this.form.submit()\">";
		$previous_language = $_SESSION ['lang'];
		$languages = $objLanguage->getLanguages ();
		while ( list ( $key, $value ) = each ( $languages ) )
			echo "<option value=\"" . $key . "\"" . (($key == $_SESSION ['lang']) ? " selected=\"selected\"" : '') . ">" . $value . "</option>";
		echo "</select>";
		echo "</div>";
		echo "</form>";
		echo "</div>";
	}
}
?>