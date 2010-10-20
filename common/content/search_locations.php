<?php 
// search_locations.php
// allows the user to search a location in the database 

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else search_locations();

function search_locations()
{ global $baseURL,
         $objLocation,$objPresentations;
  echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php?indexAction=site_result\" method=\"post\"><div>";
	$objPresentations->line(array("<h4>".LangSearchLocations0."</h4>","<input type=\"submit\" name=\"search\" value=\"" . LangSearchLocations7 . "\" />&nbsp;"),"LR",array(80,20),30);
	echo "<hr />";
	$objPresentations->line(array("1. ",LangSearchLocations1),"RL",array(5,95));
	$countries=$objLocation->getDatabaseCountries();
	$content = "<select name=\"country\">";
	while(list($key,$value)=each($countries))
	  $content.= "<option>".$value."</option>";
	$content.= "</select>";
	$objPresentations->line(array(LangSearchLocations2,$content,LangSearchLocations3),"RLL",array(10,20,70),'',array('fieldname','','explanation'));
	echo "<br />";
	$objPresentations->line(array("2. ",LangSearchLocations4),"RL",array(5,95));
	$objPresentations->line(array(LangSearchLocations5,"<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"location_name\" size=\"30\" value=\"\" />",LangSearchLocations6),"RLL",array(10,20,70),'',array('fieldname','','explanation'));
	echo "<hr />";
	echo "</div></form>";
	echo "</div>";
}
?>
