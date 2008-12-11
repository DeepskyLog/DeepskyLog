<?php

echo "</td>";

echo "<td colspan=\"3\" align=\"right\" valign=\"top\" style=\"background:url(vvs/images/toolbar_bg.jpg) no-repeat top left; background-color:#FFFFFF\">";
  echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">"; 
  echo "<tr width=\"100%\">";
  echo "<td>";
  echo "<span class=\"mainlevel\">";
  echo LangWelcome;
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

echo "<td height=\"28\" colspan=\"3\" align=\"right\" valign=\"top\" style=\"background:url(vvs/images/rb_bg.gif) no-repeat top right; background-color:#FFFFFF\">";
echo "<img src=\"vvs/images/rb.gif\" width=\"28\" height=\"28\" />";
echo "</td>";


echo "</tr>";

echo "<tr>";
echo "<td bgcolor=\"#FFFFFF\"></td>";
echo "<td colspan=\"3\" valign=\"top\" bgcolor=\"#FFFFFF\">";
echo "<table width=\"100%\"  border=\"0\" cellpadding=\"0\" cellspacing=\"0\" bgcolor=\"#FFFFFF\">";
echo "<tr>";
echo "<td>";
echo "<table class=\"blog2\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr>";
echo "<td valign=\"top\">";
echo "<div>";

echo "<table class=\"contentpaneopen\" width=\"100%\">";
echo "<tr>";
echo "<td valign=\"top\" colspan=\"2\">";

?>
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            