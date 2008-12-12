<?php
echo "<body style=\"background-color:##5C7D9D\"  leftmargin=\"0\" topmargin=\"0\" rightmargin=\"0\" bottommargin=\"0\" marginwidth=\"0\" marginheight=\"0\">";
echo "<script type=\"text/javascript\" src=\"".$baseURL."common/menu/wz_tooltip.js\"></script>";

// VVS Header and our 3 dropdown boxes 
echo "<div style=\"background-color:#003366\" align=\"right\">";
echo "<div height=\64\" style=\"background:url(".$baseURL."vvs/images/header_bg.jpg) no-repeat top left;\">";
echo "<table>";
echo "<tr>";
echo "<td>";
include $_SESSION['module'].'/menu/list.php';
echo"</td><td>";
include $_SESSION['module'].'/menu/instrument.php';
echo"</td><td>";
include $_SESSION['module'].'/menu/location.php';
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
echo "</div>";

// Welcome line with login name
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr>";
echo "<td>";
echo "<span class=\"mainlevel\" style=\"background:url(".$baseURL."vvs/images/left_bg.jpg) repeat-x top left; background-color:#5C7D9D\">";
echo "&nbsp;".LangWelcome;
echo $objUtil->checkSessionKey('module');
echo LangWelcome1;
echo $baseURL;
echo ' - ';
if($objUtil->checkSessionKey('deepskylog_id'))
  echo LangWelcome2.$objObserver->getFirstName($_SESSION['deepskylog_id'])."&nbsp;".$objObserver->getObserverName($_SESSION['deepskylog_id']);
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
