<?php
include 'common/menu/head.php';                                                   // HTML head
include 'common/menu/headmenu.php';                                               // HEAD MENU
//include 'common/menu/modulemenu.php';                                             // MODULES Menu
include 'common/menu/login.php';
include $_SESSION['module'].'/menu/quickpick.php';                                   // SEARCH MENU
include $_SESSION['module'].'/menu/list.php';                                   // SEARCH MENU
include $_SESSION['module'].'/menu/search.php';                                   // SEARCH MENU
if(array_key_exists('deepskylog_id', $_SESSION) && $_SESSION['deepskylog_id'])    // LOGGED IN
{ include $_SESSION['module'].'/menu/change.php';                                 // CHANGE MENU
  include $_SESSION['module'].'/menu/location.php';
  include $_SESSION['module'].'/menu/instrument.php';
  include 'common/menu/help.php';                                                 // HELP MENU 
  if(array_key_exists('admin', $_SESSION)&&($_SESSION['admin']=='yes'))
    include 'common/menu/admin.php';                                              // ADMINISTRATION MENU
  include 'common/menu/out.php';                                                  // LOG OUT MENU 
}
else
{ include 'common/menu/help.php';                                                 // HELP MENU 
  include 'common/menu/languagemenu.php';                                         // LANGUAGE MENU 
}
include 'common/menu/tellus.php';
include 'common/menu/countermenu.php';
include 'common/menu/endmenu.php';                                                // END MENU
?>