<?php
// search.php
// menu which allows the user to search the observation database 

global $inIndex,$loggedUser,$objUtil;

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else menu_downloads();

function menu_downloads()
{ global $loggedUser,$menuDownloads,$baseURL;
	echo "<li>
	      <a href=\"http://". $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"] ."#\">" . LangDownloadsMenuTitle."<span class=\"arrow\"></span></a>";
	echo " <ul>";
  echo "  <li><a href=\"".$baseURL."index.php?indexAction=downloadAstroImageCatalogs\">".LangSearchMenuItem14."</a></li>";
	echo "  <li><a href=\"".$baseURL."index.php?indexAction=view_atlaspages\">".LangSearchMenuItem13."</a></li>";
	echo " </ul>";
	echo "</li>";
}
?>