<?php
// menu.php
// shows the menus

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu();

function menu()
{ global $loggedUser,$instDir,$inIndex;
	// Old style menu
	if($loggedUser) {                                                                            // LOGGED IN
	} else
	  require_once $instDir.'common/menu/languagemenu.php';                                         // LANGUAGE MENU   
	require_once $instDir.$_SESSION['module'].'/menu/quickpick.php';                                // QUICKPICK MENU
  require_once $instDir.'common/menu/moon.php';                                                   // MOON MENU
	require_once $instDir.'common/menu/oalmenu.php';
//	require_once $instDir.'common/menu/validationmenu.php';
}
?>