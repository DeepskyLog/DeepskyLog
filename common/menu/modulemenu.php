<?php
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"moduletable\">";
echo "<tr>";
echo "<th valign=\"top\">".LangDeepskyLogModules."</th> ";
echo "</tr>";
echo "<tr>";
echo "<td height=\"30\" valign=\"top\" class=\"mainlevel\">";
for ($i = 0; $i < count($modules);$i++)
{ $mod = $modules[$i];
  echo "<a href=\"".$baseURL."index.php?indexAction=module".$mod."\">".$GLOBALS[$mod]."</a><br />";
}
echo "</td>";
echo "</tr>";
echo "</table>";
?>