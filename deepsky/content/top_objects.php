<?php 
// top_objects.php
// generates an overview of all observed objects and their rank 

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else top_objects();

function top_objects()
{ global $baseURL,$step,$FF,
         $objObject,$objObservation,$objPresentations,$objUtil;
	echo"<div id=\"main\">";
	$link=$baseURL."index.php?indexAction=rank_objects";
	echo "<h4>".LangTopObjectsTitle."</h4>";
	echo "<hr />";
	$objObject->showObjects($link, '',2,'',"top_objects");
	echo "<hr />";
	echo "</div>";
}
?>