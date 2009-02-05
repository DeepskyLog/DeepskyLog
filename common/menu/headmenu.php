<?php
echo "<body leftmargin=\"0\" topmargin=\"0\" rightmargin=\"0\" bottommargin=\"0\" marginwidth=\"0\" marginheight=\"0\">";
echo "<script type=\"text/javascript\" src=\"".$baseURL."common/menu/wz_tooltip.js\"></script>";

// VVS Header and our 3 dropdown boxes if logged in 
echo "<div style=\"background-color:#003366\" align=\"right\">";
echo "<div style=\"background:url(".$baseURL."styles/images/header_bg.jpg); background-repeat: no-repeat; background-position: 0% 0%;\">";
echo "<table>";
echo "<tr>";
echo "<td heigth=\"64px\">";
include $_SESSION['module'].'/menu/list.php';
echo"</td><td>";
include $_SESSION['module'].'/menu/instrument.php';
echo"</td><td>";
include $_SESSION['module'].'/menu/location.php';
echo"</td><td>";
echo "<img src=\"".$baseURL."styles/images/deepskylog.gif\"/>";
echo"</td>";
echo "</table>";
echo "</div>";
echo "</div>";

// Welcome line with login name
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr>";
echo "<td style=\"line-height:20px\">";
echo "<span class=\"mainlevel\">";
echo "&nbsp;".LangWelcome;
echo $objUtil->checkSessionKey('module');
echo LangWelcome1;
echo $baseURL;
echo ' - ';
if($objUtil->checkSessionKey('deepskylog_id'))
  echo LangWelcome2.$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'firstname')."&nbsp;".$objObserver->getObserverProperty($_SESSION['deepskylog_id'],'name');
else
  echo LangWelcome3;
echo "</span>";
echo "</td>";
echo "<td align=\"right\">";  
echo "<span class=\"mainlevel\">";
echo LangWelcome4;
for ($i = 0; $i < count($modules);$i++)
{ $mod = $modules[$i];
  if($i>0) echo " - ";
    echo "<a href=\"".$baseURL."index.php?indexAction=module".$mod."\">".$GLOBALS[$mod]."</a>";
}
echo "&nbsp;</span>";
echo "</td>";
echo"</tr>";
echo "</table>";

?>
