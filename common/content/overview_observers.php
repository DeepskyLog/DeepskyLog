<?php

// overview_observers.php
// generates an overview of all observers (admin only)
// version 0.2: JV, 20041226

//  include_once "../lib/locations.php";

  include_once "../lib/observers.php";
  include_once "../lib/util.php";

  $obs = new Observers;
  $util = new util;
  $util->checkUserInput();

// sort

  if(isset($_GET['sort']))
  {
     $sort = $_GET['sort']; // field to sort on
  }
  else
  {
     $sort = "name";
     $_GET['sort'] = $sort;
  }

// minimum

if(isset($_GET['min']))
{
  $min = $_GET['min'];
}
else
{
  $min = 0;
}

$observers = $obs->getSortedObservers($sort);

// the code below is very strange but works

if((isset($_GET['previous'])))
{
  $orig_previous = $_GET['previous'];
}
else
{
  $orig_previous = "";
}

if((isset($_GET['sort'])) && (isset($_GET['previous'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] != "")
  {
    $observers = array_reverse($observers, true);
  }
  else
  {
    krsort($observers);
    reset($observers);
  }
  $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

  echo("<div id=\"main\">\n<h2>".LangViewObserverTitle."</h2>");

  $step = 25;

  $link = "common/view_observers.php?sort=" . $sort . "&amp;previous=" . $orig_previous;

  list($min, $max) = $util->printListHeader($observers, $link, $min, $step, "");

  $count = 0;

  echo "<table>
         <tr class=\"type3\">
          <td><a href=\"common/view_observers.php?sort=id&amp;previous=$previous\">id</a></td>
          <td><a href=\"common/view_observers.php?sort=name&amp;previous=$previous\">".LangViewObserverName."</a></td>
          <td><a href=\"common/view_observers.php?sort=firstname&amp;previous=$previous\">".LangViewObserverFirstName."</a></td>";

  echo "<td><a href=\"common/view_observers.php?sort=email&amp;previous=$previous\">Email</a></td>";

  echo "<td><a href=\"common/view_observers.php?sort=role&amp;previous=$previous\">".LangViewObserverRole."</a></td>
        <td></td>
         </tr>";

  while(list ($key, $value) = each($observers))
  {
   if($count >= $min && $count < $max) // selection
   {
   if ($count % 2)
   {
    $type = "class=\"type1\"";
   }
   else
   {
    $type = "class=\"type2\"";
   }

   $name = $obs->getObserverName($value);
   $firstname = $obs->getFirstName($value);
   $email = $obs->getEmail($value);

   $url = $_SERVER['REQUEST_URI'];

   echo "<tr $type><td><a href=\"common/detail_observer.php?user=$value&amp;back=$url\">$value</a> </td><td> $name </td><td> $firstname </td>";

   echo "<td> <a href=\"mailto:$email\"> $email </a> </td>";

   echo "<td> ";

   $role = $obs->getRole($value);

   if ($role == RoleAdmin)
   {
    echo LangViewObserverAdmin."</td><td></td>";
   }
   elseif ($role == RoleUser)
   {
    echo LangViewObserverUser."</td><td></td>";
   }
   elseif ($role == RoleCometAdmin)
   {
    echo LangViewObserverCometAdmin."</td><td></td>";
   }
   elseif ($role == RoleWaitlist)
   {
    echo LangViewObserverWaitlist."</td><td><a href=\"common/control/validate_observer.php?validate=$value\">".LangViewObserverValidate."</a></td>";
   }
  
   echo("</tr>");
   }
   $count++;
  }
  echo "</table>";

  list($min, $max) = $util->printListHeader($observers, $link, $min, $step, "");

  echo "</div></div></body></html>";
?>
