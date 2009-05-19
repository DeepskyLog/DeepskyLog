<?php // search_locations.php - allows the user to search a location in the database 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else
{
echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php?indexAction=site_result\" method=\"post\">";
$objPresentations->line(array("<h5>".LangSearchLocations0."</h5>","<input type=\"submit\" name=\"search\" value=\"" . LangSearchLocations7 . "\" />&nbsp;"),"LR",array(80,20),50);
echo "<hr />";
echo "<ol>";
echo "<li value=\"1\">".LangSearchLocations1."</li>";
echo "</ol>";
$countries=$objLocation->getDatabaseCountries();
$content = "<select name=\"country\">";
while(list($key,$value)=each($countries))
  $content.= "<option>".$value."</option>";
$content.= "</select>";
$objPresentations->line(array(LangSearchLocations2,$content,LangSearchLocations3),"RLL",array(10,20,70),'',array('fieldname','','explanation'));
echo "<ol>";
echo "<li value=\"2\">".LangSearchLocations4."</li>";
echo "</ol>";
$objPresentations->line(array(LangSearchLocations5,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"location_name\" size=\"30\" value=\"\" />",LangSearchLocations6),"RLL",array(10,20,70),'',array('fieldname','','explanation'));
echo "<hr />";
echo "</form>";
echo "</div>";
}
?>
