<?php
for ($i = 0; $i < count($modules);$i++)
{ $mod = $modules[$i];
  echo "<a href=\"".$baseURL."index.php?indexAction=module".$mod."\">".$GLOBALS[$mod]."</a><br />";
}
?>