<?php // VVS Header and our 3 dropdown boxes if logged in 

function headmenu()
{ global $baseURL,$leftmenu,$loggedUser,$modules,$thisDay,$thisMonth,$thisYear,$topmenu,
         $objUtil,$objLocation,$objInstrument,$objObserver;
  echo "<div id=\"div1\">";
	echo "<img src=\"".$baseURL."styles/images/header_bg.jpg\" alt=\"Vereniging voor Sterrenkunde - DeepskyLog\"/>";
	
	echo "<div id=\"div1a\">";
	echo "<img src=\"".$baseURL."styles/images/deepskylog.gif\" alt=\"DSL\" />";
	echo "</div>";
	
	echo "<div id=\"div1b\">";
	echo "<div class=\"floatright\">";
	include $_SESSION['module'].'/menu/date.php';
	echo "</div>";
	echo "<div class=\"floatright\">";
	include $_SESSION['module'].'/menu/location.php';
	echo "</div>";
	echo "<div class=\"floatright\">";
	include $_SESSION['module'].'/menu/instrument.php';
	echo "</div>";
	echo "<div class=\"floatright\">";
	include $_SESSION['module'].'/menu/list.php';
	echo "</div>";
	echo "</div>";
	
	echo "</div>";
	
	// Welcome line with login name
	echo "<div id=\"div2\">";
	echo "<div id=\"div2a\">";

  $thelink='';
  reset($_GET);
  while(list ($key, $value) = each($_GET))
    if (!in_array($key, array (
			'leftmenu'	  )))
  $thelink .= "&amp;" . $key . "=" . urlencode($value);
	$thelink = $baseURL."index.php?".substr($thelink,5);
  
	if($topmenu=="show")
	  echo "<a href=\"".$thelink."&amp;topmenu=hidden\" title=\"".LangHideTopMenu."\">^</a> ";
	else
	  echo "<a href=\"".$thelink."&amp;topmenu=show\" title=\"".LangShowTopMenu."\" >v</a> ";
	if($leftmenu=="show")
	  echo "<a href=\"".$thelink."&amp;leftmenu=hidden\" title=\"".LangHideLeftMenu."\">&lt;</a> ";
	else
	  echo "<a href=\"".$thelink."&amp;leftmenu=show\" title=\"".LangShowLeftMenu."\" >&gt;</a> ";
	  echo "<span class=\"menuLine\" >";
	echo LangWelcome;
	echo $objUtil->checkSessionKey('module');
	echo LangWelcome1;
	echo $baseURL;
	echo ' - ';
	if($loggedUser)
	  echo LangWelcome2.$objObserver->getObserverProperty($loggedUser,'firstname')."&nbsp;".$objObserver->getObserverProperty($loggedUser,'name');
	else
	  echo LangWelcome3;
	echo " - ";
	echo "<a href=\"".$baseURL."index.php?title=Home\">"."Home"."</a>";
	echo "</span>";
	echo "</div>";
	echo "<div id=\"div2b\">";  
	echo "<span class=\"menuLine\">";
	echo LangWelcome4;
	for ($i = 0; $i < count($modules);$i++)
	{ $mod = $modules[$i];
	  if($i>0) echo " - ";
	    echo "<a href=\"".$baseURL."index.php?indexAction=module".$mod."\">".$GLOBALS[$mod]."</a>";
	}
	echo "</span>";
	echo "</div>";
	echo "</div>";
}
headmenu();
?>
