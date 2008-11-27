<?php
// getLocation.php
// prints the locations looked up into the database 

echo "<div id=\"main\">";
echo "<h2>".LangGetLocation1."</h2>";
$count=0;
$result=$objLocation->getLocationsFromDatabase($_POST['location_name'],$_POST['country']);
if($result&& _POST['location_name'])
{ echo "<div class=\"results\">".LangGetLocation2."</div>";
  echo "<p>";
	echo "<table>";
  echo "<tr class=\"type3\">";
  echo "<td>".LangGetLocation3."</td>";
  echo "<td>".LangGetLocation4."</td>";
  echo "<td>".LangGetLocation5."</td>";
  echo "<td>".LangGetLocation6."</td>";
  echo "<td>".LangGetLocation7."</td>";
  echo "</tr>";
  while(list($key, value)=each($result))
  { $vars = explode("\t", $value);
    echo "<tr class=\"type".(2-($count%2))."\">";
    echo "<td>";
    echo "<a href=\"".$baseURL."index.php?indexAction=add_site&amp;sitename=$vars[0]&amp;longitude=$vars[1]&amp;latitude=$vars[2]&amp;region=$vars[4]&amp;country=$vars[3]\">$vars[0]</a> </td><td>". decToString($vars[1], 1) ."</td><td>". decToString($vars[2], 1) ."</td><td> $vars[4] </td><td> $vars[3]";
    echo "</td>";
    echo "</tr>";
    $count++;
  }
  echo "</table>";
	echo "</p>";
}
else
  echo "<p>".LangGetLocation8."</p>";
echo "</div>";
?>
