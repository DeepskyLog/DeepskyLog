<?php
include 'common/menu/login.php';
include $_SESSION['module'].'/menu/search.php';                                   // SEARCH MENU
include $_SESSION['module'].'/menu/quickpick.php';                                // QUICKPICK MENU
if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])    // LOGGED IN
{ include $_SESSION['module'].'/menu/change.php';                                 // CHANGE MENU
  if(array_key_exists('admin', $_SESSION)&&($_SESSION['admin']=='yes'))
    include 'common/menu/admin.php';                                              // ADMINISTRATION MENU
  include 'common/menu/out.php';                                                  // LOG OUT MENU 
}
else
{ include 'common/menu/languagemenu.php';                                         // LANGUAGE MENU 
}
  include 'common/menu/help.php';                                                 // HELP MENU 
include 'common/menu/tellus.php';
include 'common/menu/countermenu.php';
?>