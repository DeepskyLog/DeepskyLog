<?php
// modulemenu.php
// display the module menu

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else modulemenu();

function modulemenu()
{	global $baseURL,$modules;
  for ($i = 0; $i < count($modules);$i++)
	{ $mod = $modules[$i];
	  echo "<a href=\"".$baseURL."index.php?indexAction=module".$mod."\">".$GLOBALS[$mod]."</a><br />";
	}
}
?>