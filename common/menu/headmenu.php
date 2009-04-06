<?php // VVS Header and our 3 dropdown boxes if logged in 

echo "<div id=\"div1\">";
echo "<img src=\"".$baseURL."styles/images/header_bg.jpg\" alt=\"Vereniging voor Sterrenkunde - DeepskyLog\"/>";

echo "<div id=\"div1a\">";
echo "<img src=\"".$baseURL."styles/images/deepskylog.gif\" alt=\"DSL\" />";
echo "</div>";

echo "<div id=\"div1b\">";
echo "<div style=\"float:right\">";
include $_SESSION['module'].'/menu/location.php';
echo "</div>";
echo "<div style=\"float:right\">";
include $_SESSION['module'].'/menu/instrument.php';
echo "</div>";
echo "<div style=\"float:right\">";
include $_SESSION['module'].'/menu/list.php';
echo "</div>";
echo "</div>";

echo "</div>";

// Welcome line with login name
echo "<div id=\"div2\">";
echo "<div id=\"div2a\">";
echo "<span class=\"mainlevel\">";
echo LangWelcome;
echo $objUtil->checkSessionKey('module');
echo LangWelcome1;
echo $baseURL;
echo ' - ';
if($objUtil->checkSessionKey('deepskylog_id'))
  echo LangWelcome2.$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'firstname')."&nbsp;".$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'name');
else
  echo LangWelcome3;
echo "</span>";
echo "</div>";
echo "<div id=\"div2b\">";  
echo "<span class=\"mainlevel\">";
echo LangWelcome4;
for ($i = 0; $i < count($modules);$i++)
{ $mod = $modules[$i];
  if($i>0) echo " - ";
    echo "<a href=\"".$baseURL."index.php?indexAction=module".$mod."\">".$GLOBALS[$mod]."</a>";
}
echo "</span>";
echo "</div>";
echo "</div>";
?>
