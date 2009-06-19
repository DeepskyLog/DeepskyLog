<?php
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else
{ include $instDir.'common/menu/login.php';
	include $instDir.$_SESSION['module'].'/menu/quickpick.php';                                // QUICKPICK MENU
	include $instDir.$_SESSION['module'].'/menu/search.php';                                   // SEARCH MENU
	if($loggedUser)                                                                            // LOGGED IN
	{ include $instDir.$_SESSION['module'].'/menu/change.php';                                 // CHANGE MENU
	  if(array_key_exists('admin', $_SESSION)&&($_SESSION['admin']=='yes'))
	    include $instDir.'common/menu/admin.php';                                              // ADMINISTRATION MENU
	  include $instDir.'common/menu/out.php';                                                  // LOG OUT MENU 
	}
	else
	{ include $instDir.'common/menu/languagemenu.php';                                         // LANGUAGE MENU 
	}
  include $instDir.'common/menu/help.php';                                                   // HELP MENU 
	include $instDir.'common/menu/countermenu.php';
	include $instDir.'common/menu/tellus.php';
	include $instDir.'common/menu/oalmenu.php';
//	include $instDir.'common/menu/validationmenu.php';
}
?>