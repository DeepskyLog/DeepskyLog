<?php

echo "<body style=\"background-color:##5C7D9D\"  leftmargin=\"0\" topmargin=\"0\" rightmargin=\"0\" bottommargin=\"0\" marginwidth=\"0\" marginheight=\"0\">";
echo "<script type=\"text/javascript\" src=\"".$baseURL."common/menu/wz_tooltip.js\"></script>";

echo "<div style=\"background-color:#003366\">";
echo "<table style=\"background:url(".$baseURL."vvs/images/header_bg.jpg) no-repeat\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr align=\"right\">";
echo "<td height=\"64\" colspan=\"7\">";
  
  echo "<td width=\"33%\">";
  echo "&nbsp";
  echo "</td>";
  
  echo "<td whidth=\"150px\">";
  include $_SESSION['module'].'/menu/list.php';
  echo "</td>";
  
  echo "<td whidth=\"150px\">";
  include $_SESSION['module'].'/menu/instrument.php';
  echo "</td>";
  
  echo "<td whidth=\"150px\">";
  include $_SESSION['module'].'/menu/location.php';
  echo "</td>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";

echo "<table width=\"100%\" height=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr>"; // TOP ROW
echo "<td colspan=\"2\" valign=\"top\" style=\"background:url(".$baseURL."vvs/images/toolbar_bg.jpg) no-repeat top left; background-color:#FFFFFF\">";
  echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">"; 
  echo "<tr width=\"100%\">";
  echo "<td align=\"left\">";
  echo "<span class=\"mainlevel\">";
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
  echo "</span>";
  echo "</td>";
  echo "</tr>";
  echo "</table>";
echo "</td>";
echo "<td>&nbsp;</td";
echo "</tr>";

echo "<tr>"; // CENTER ROW
echo "<td width=\"153px\" align=\"left\" valign=\"top\" style=\"background:url(".$baseURL."vvs/images/left_bg.jpg) repeat-x top left; background-color:#5C7D9D\">";
echo "<br />";

?>
