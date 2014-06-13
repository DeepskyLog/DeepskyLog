<?php
// menu.php
// shows the menus

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu();

function menu()
{ global $loggedUser,$instDir,$inIndex;

  echo "  <div class=\"col-sm-2\">
           <ul id=\"sidebar\" class=\"nav nav-stacked\">";

	if($loggedUser) {                                                                            // LOGGED IN
	} else
	  require_once $instDir.'common/menu/languagemenu.php';                                         // LANGUAGE MENU   
	require_once $instDir.$_SESSION['module'].'/menu/quickpick.php';                                // QUICKPICK MENU
  require_once $instDir.'common/menu/moon.php';                                                   // MOON MENU

  echo "    </ul>
			     </div>";
}
?>