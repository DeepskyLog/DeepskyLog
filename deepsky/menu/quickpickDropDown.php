<?php 
// quickpick.php
// allows the user to quiclky enter the name of an object and search it, its observations or make a new observation

global $loggedUser,$inIndex,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu_quickpickDropDown();

function menu_quickpickDropDown()
{ global $baseURL,$menuSearch,$loggedUser,$loggedUser;

	echo "<li>
	      <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\">" . LangSearch."<span class=\"arrow\"></span></a>";

	echo " <ul>";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&searchObjectQuickPickQuickPick=Zoek object\">".LangQuickPickSearchObject."</a></li>";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&searchObservationsQuickPick=Zoek waarnemingen\">".LangQuickPickSearchObservations."</a></li>";
	echo " </ul>";
	echo "</li>";
//	  	echo "<input type=\"submit\" name=\"newObservationQuickPick\" class=\"menuButton\" value=\"".."\" />";
}
?>
