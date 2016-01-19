<?php
// quickpick.php
// allows the user to quiclky enter the name of an object and search it, its observations or make a new observation

global $loggedUser,$inIndex,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu_quickpickDropDown();

function menu_quickpickDropDown()
{ global $baseURL,$menuSearch,$loggedUser,$loggedUser;

	echo "<ul class=\"nav navbar-nav\">
			  <li class=\"dropdown\">
	      <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . LangSearch."<b class=\"caret\"></b></a>";

	echo " <ul class=\"dropdown-menu\">";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&searchObjectQuickPickQuickPick=Zoek object\">".LangQuickPickSearchObject."</a></li>";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&searchObservationsQuickPick=Zoek waarnemingen\">".LangQuickPickSearchObservations."</a></li>";
	if (($loggedUser) && ($loggedUser != "admin")) { // admin doesn't have own observations
		echo "  <li class=\"disabled\">─────────────────</li>";
		echo "  <li><a href=\"" . $baseURL . "index.php?indexAction=result_selected_sessions\">" . LangSearchMenuItem12 . "</a></li>";
	}
	echo " </ul>";
	echo "</li>
			  </ul>";
}
?>
