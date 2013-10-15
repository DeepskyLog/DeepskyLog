<?php 
// headmenu.php
// VVS Header and our 3 dropdown boxes if logged in 

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else headmenu();

function headmenu()
{ global $baseURL,$leftmenu,$loggedUser,$modules,$thisDay,$thisMonth,$thisYear,$topmenu, $register,
         $objUtil,$objLocation,$objInstrument,$objObserver,$objMessages, $instDir, $objDatabase;

    echo "<header>";
	// Everything is set in the css
	echo "</header>";
	
	// TODO : html5
	if($loggedUser)
	{ echo "<div id=\"div1b\">";
		echo "<div class=\"floatright\">";
		require_once $_SESSION['module'].'/menu/date.php';
		echo "</div>";
		echo "<div class=\"floatright\">";
		require_once $_SESSION['module'].'/menu/list.php';
		echo "</div>";
		echo "</div>";
	}
	
	// Welcome line with login name
	echo "<div id=\"div2\">";

	// Here, we set the new style, drop down menu
	// Make the drop down menu
	echo " <ul id=\"menu\">";
 	echo "<li>";
 	echo "<a href=\"" . $baseURL . "index.php?title=Home\"><img src=\"" . $baseURL . "images/home.png\" /></a>";
 	echo "</li>";
	require_once $instDir.$_SESSION['module'].'/menu/search.php';                                   // Overview MENU
 	if($_SESSION['module']=='deepsky') {
 		require_once $instDir.$_SESSION['module'].'/menu/quickpickDropDown.php';                                   // Search MENU
 	}
	if($loggedUser)                                                                            // LOGGED IN
	{ require_once $instDir.$_SESSION['module'].'/menu/change.php';                                 // CHANGE MENU
	  if(array_key_exists('admin', $_SESSION)&&($_SESSION['admin']=='yes'))
	    require_once $instDir.'common/menu/admin.php';                                              // ADMINISTRATION MENU
	}
	if($_SESSION['module']=='deepsky')
	  require_once $instDir.'deepsky/menu/downloads.php';
	require_once $instDir.'common/menu/help.php';                                                   // HELP MENU

	// Select the standard location and instrument
	if($loggedUser)                                                                            // LOGGED IN
	{
      if(array_key_exists('admin', $_SESSION)&&($_SESSION['admin']!='yes'))
      {
	  require_once 'common/menu/location.php';
		require_once 'common/menu/instrument.php';
      }
	}
	
	// Select the modules
	echo "<span class=\"right\"><li>";
	echo "<a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\">" . $GLOBALS[$_SESSION['module']]. "<span class=\"arrow\"></span></a>";
  echo "<ul><span class=\"left\">";
	for ($i = 0; $i < count($modules);$i++)
 	{
    $mod = $modules[$i];
    if ($mod != $_SESSION['module']) {
      echo " <li><a href=\"".$baseURL."index.php?indexAction=module".$mod."\">".$GLOBALS[$mod]."</a></li>";
    }
 	}
 	echo "</span></ul>";
	echo "</li></span>";
	
	// If new messages != 0, make background red
	echo "<span class=\"right\"><li>";
	$unreadMails = $objMessages->getNumberOfUnreadMails();
	$unreadMailsSplit = explode("/", $unreadMails);
	if ($unreadMailsSplit[0] > 0) {
	  echo "<a class=\"newMails\" href=\"". $baseURL . "index.php?indexAction=show_messages\">" . $unreadMails . "</a>";
	} else {
	  echo "<a href=\"". $baseURL . "index.php?indexAction=show_messages\">" . $unreadMails . "</a>";
	}
	echo "</li></span>";

	echo "<span class=\"right\">";
	if($loggedUser) {
	  echo "<li><a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\">" . $objObserver->getObserverProperty($loggedUser,'firstname') . "<span class=\"arrow\"></span></a>";
  	echo "<ul><span class=\"left\">";
  	echo " <li><a href=\"".$baseURL."index.php?indexAction=detail_observer&user=" . $loggedUser . "\">" . LangDetails . "</a></li>";
    echo " <li><a href=\"".$baseURL."index.php?indexAction=change_account\">".LangChangeMenuItem1."</a></li>";
    echo " <li><a href=\"".$baseURL."index.php?indexAction=logout&amp;title=".urlencode(LangLogoutMenuItem1)."\">".LangLogoutMenuItem1."</a></li>";
  	echo "</span></ul>";
    echo " </li></span>";	
	} else {
	  // Let's make a sign in / register tab
	  if($register == "yes") {                                                       // includes register link 
	    echo "<li><a class=\"register\" href=\"".$baseURL."index.php?indexAction=subscribe&amp;title=".urlencode(LangLoginMenuRegister)."\">".LangLoginMenuRegister."</a></li>";
	    echo "</span><span class=\"right\">";
	  }
	  echo "<li><a href=\"" . $baseURL . "index.php?indexAction=login\">" . $objObserver->getObserverProperty($loggedUser,'firstname')."&nbsp;". LangLoginMenuTitle . "</a></li>";
	  echo "</span>";	
	}
	echo " </ul>";
  
	echo "</div>";
}
?>
