<?php
// menu.php
// shows the menus

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu();

function menu()
{ global $loggedUser,$instDir,$inIndex;
	if($loggedUser)                                                                            // LOGGED IN
	  require_once $instDir.'common/menu/out.php';                                                  // LOG OUT MENU 
	else
	  require_once $instDir.'common/menu/languagemenu.php';                                         // LANGUAGE MENU   
	require_once $instDir.'common/menu/login.php';
	require_once $instDir.$_SESSION['module'].'/menu/quickpick.php';                                // QUICKPICK MENU
	require_once $instDir.$_SESSION['module'].'/menu/search.php';                                   // SEARCH MENU
	if($loggedUser)                                                                            // LOGGED IN
	{ require_once $instDir.$_SESSION['module'].'/menu/change.php';                                 // CHANGE MENU
	  if(array_key_exists('admin', $_SESSION)&&($_SESSION['admin']=='yes'))
	    require_once $instDir.'common/menu/admin.php';                                              // ADMINISTRATION MENU
	}
  if($_SESSION['module']=='deepsky')
    require_once $instDir.'deepsky/menu/downloads.php';
  require_once $instDir.'common/menu/moon.php';                                                   // MOON MENU
  require_once $instDir.'common/menu/help.php';                                                   // HELP MENU 
	require_once $instDir.'common/menu/tellus.php';
	require_once $instDir.'common/menu/oalmenu.php';
//	require_once $instDir.'common/menu/validationmenu.php';
}
?>