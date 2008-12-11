<?php


echo "</td>";
echo "<td colspan=\"3\" align=\"right\" valign=\"top\" style=\"background:url(vvs/images/toolbar_bg.jpg) no-repeat top left; background-color:#FFFFFF\">";
/*
echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">"; 
echo "<tr width=\"100%\">";
echo "<td width=\"50%\">";
echo "<span class=\"pathway\"> &nbsp".LangYouAreHere;
echo "<span class=\"pathway\">";
echo "<a href=\"http://www.vvs.be/\" class=\"pathway\">".LangHome."</a>";
echo "<img src=\"vvs/images/arrow.png\" alt=\"arrow\" />";
echo "<a href=\"http://www.deepsky.be/\" class=\"pathway\">Deepsky</a>";
echo "<img src=\"vvs/images/arrow.png\" alt=\"arrow\" />   DeepskyLog";
echo "</span>";
echo "</span>";
echo "</td>";
echo "<td align=\"right\" width=\"50%\" nowrap=\"nowrap\"><span class=\"mainlevel\"> VVS: </span>";
echo "<span class=\"mainlevel\"> | </span>";
echo "<a href=\"http://www.vvs.be/component/option,com_frontpage/Itemid,1/\" class=\"mainlevel\" >".LangHome."</a>";
echo "<span class=\"mainlevel\"> | </span>";
echo "<a href=\"http://www.vvs.be/component/option,com_wrapper/Itemid,348/?\" class=\"mainlevel\" >".LangBecomeMember."</a>";
echo "<span class=\"mainlevel\"> | </span>";
echo "<a href=\"http://www.vvs.be/component/option,com_search/Itemid,81/\" class=\"mainlevel\" >".LangSearch."</a>";
echo "<span class=\"mainlevel\"> | </span>";
echo "<a href=\"http://www.vvs.be/component/option,com_contact/Itemid,80/\" class=\"mainlevel\" >".LangContact."</a>";
echo "<span class=\"mainlevel\"> | </span>";
echo "</td>";
echo "</tr>";
echo "</table>";
*/
echo "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">"; 
echo "<tr width=\"100%\">";
echo "<td width=\"50%\">";
echo "<span class=\"mainlevel\">";
echo LangWelcome;
echo $objUtil->checkSessionKey('module');
echo LangWelcome1;
echo $baseURL;
echo "</span>";
echo "</td>";
echo "<td align=\"right\" width=\"50%\" nowrap=\"nowrap\">";
echo "<span class=\"mainlevel\">";
if($objUtil->checkSessionKey('deepskylog_id'))
  echo LangWelcome2.$objObserver->getFirstName($_SESSION['deepskylog_id'])."&nbsp;".$objObserver->getObserverName($_SESSION['deepskylog_id']);
else
  echo LangWelcome3;
echo "</span>";
echo "</td>";
echo "</tr>";
echo "</table>";

echo "</td>";
echo "<td height=\"28\" colspan=\"3\" align=\"right\" valign=\"top\" style=\"background:url(vvs/images/rb_bg.gif) no-repeat top right; background-color:#FFFFFF\">";
echo "<img src=\"vvs/images/rb.gif\" width=\"28\" height=\"28\" />";
echo "</td>";
echo "<td width=\"151\" rowspan=\"3\" align=\"left\" valign=\"top\" bgcolor=\"#5C7D9D\">";
echo "<table width=\"100%\"  border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
echo "<tr>";
echo "<td bgcolor=\"#003466\">&nbsp;<br />";

echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";

if ($_SESSION['lang'] == "nl")
{ echo"<tr>";
	echo "<th valign=\"top\">Help</th>";
	echo "</tr><tr>";
	echo "<td valign=\"top\" height=\"60\">";
	echo "<a href=\"http://www.deepskylog.org/wiki/bin/view/Main/DeepskyLogManualNL\" target=\"_blank\">Handleiding</a>";
	echo "</td>";
	echo "</tr>";
}

echo "</table>";
echo "</td>";
echo "</tr>";


echo "<tr>";
echo "<td height=\"13\" align=\"left\" valign=\"bottom\" background=\"vvs/images/rightcolumn_2.gif\">";
echo "<img src=\"vvs/images/rightcolumn_1.gif\" width=\"10\" height=\"13\" />";
echo "</td>";
echo "</tr>";


echo "</table>";
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
/*
echo "<table class=\"contentpaneopen\">";
echo "<tr>";
echo "<td class=\"contentheading\" width=\"100%\">";
echo "http://www.deepskylog.org ";
echo "<div style=\"text-align:right\">";
$mod = $_SESSION['module'];
echo $mod;
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";
*/
echo "<table class=\"contentpaneopen\" width=\"100%\">";
echo "<tr>";
echo "<td valign=\"top\" colspan=\"2\">";
?>
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            
					            