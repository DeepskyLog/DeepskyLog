<?php
echo "<script type=\"text/javascript\" src=\"".$baseURL."common/menu/wz_tooltip.js\"></script>";
// VVS Header and our 3 dropdown boxes if logged in 
echo "<div style=\"background-color:#003366;
                   position:relative;
                   left:0px; top:0px;
                   width:100%; heigth:60px;
                   \">";
echo "<img src=\"".$baseURL."styles/images/header_bg.jpg\"/>";
echo "<div style=\"position:absolute;right:2px;top:0px;\">";
echo "<img src=\"".$baseURL."styles/images/deepskylog.gif\"/>";
echo "</div>";
echo "<div style=\"position:absolute;top:7px;right:30px;\">";
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
echo "<div style=\"position:relative;width:100%;height:25px;\">";
echo "<div style=\"position:absolute;left:5px;top:3px;\">";
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
echo "<div style=\"position:absolute;right:10px;top:3px;\">";  
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
