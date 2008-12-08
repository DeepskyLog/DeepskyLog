<?php
// overview_observers.php
// generates an overview of all observers (admin only)

$sort=$objUtil->checkGetKey('sort','name');
if(!$min) $min=$objUtil->checkGetKey('min',0);
// the code below is very strange but works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
$observers = $objObserver->getSortedObservers($sort);
if((isset($_GET['sort'])) && (isset($_GET['previous'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{ if ($_GET['sort'] != "")
    $observers = array_reverse($observers, true);
  else
  { krsort($observers);
    reset($observers);
  }
  $previous = ""; // reset previous field to sort on
}
else
  $previous = $sort;
echo "<div id=\"main\">";
echo "<h2>".LangViewObserverTitle."</h2>";
$step=25;
$link=$baseURL."index.php?indexAction=view_observers&amp;sort=".$sort."&amp;previous=".$orig_previous;
list($min, $max) = $util->printListHeader($observers, $link, $min, $step, "");
$count = 0;
echo "<table>";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=id&amp;previous=$previous\">id</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=name&amp;previous=$previous\">".LangViewObserverName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=firstname&amp;previous=$previous\">".LangViewObserverFirstName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=email&amp;previous=$previous\">Email</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=role&amp;previous=$previous\">".LangViewObserverRole."</a></td>";
echo "<td></td>";
echo "</tr>";
while(list ($key, $value) = each($observers))
{ $name = $objObserver->getObserverName($value);
  $firstname = $objObserver->getFirstName($value);
  $email = $objObserver->getEmail($value);
  $url = $_SERVER['REQUEST_URI'];
  echo "<tr class=\"type".(2-($count%2))."\">";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=".urlencode($value)."&amp;back=$url\">".$value."</a> </td>";
  echo "<td>".$name."</td>";
  echo "<td>".$firstname."</td>";
  echo "<td> <a href=\"mailto:".$email."\"> ".$email." </a> </td>";
  echo "<td> ";
  $role = $objObserver->getRole($value);
  if ($role == RoleAdmin)
    echo LangViewObserverAdmin."</td><td></td>";
  elseif ($role == RoleUser)
    echo LangViewObserverUser."</td><td></td>";
  elseif ($role == RoleCometAdmin)
    echo LangViewObserverCometAdmin."</td><td></td>";
  elseif ($role == RoleWaitlist)
    echo LangViewObserverWaitlist."</td><td><a href=\"".$baseURL."index.php?indexAction=validate_observer&amp;validate=".urlencode($value)."\">".LangViewObserverValidate."</a></td>";
  echo "</tr>";
  $count++;
}
echo "</table>";
list($min, $max) = $util->printListHeader($observers, $link, $min, $step, "");
echo "</div>";
?>
