<?php // overview_observers.php - generates an overview of all observers (admin only)
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif($_SESSION['admin']!="yes") throw new Exception(LangException001);
else
{
set_time_limit(60);
$sort=$objUtil->checkGetKey('sort','');
if(!($sort))
{ $sort='registrationDate';
  $_GET['sort']='registrationDate';
  $_GET['previous']='registrationDate';
}
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
$count = 0;
$link=$baseURL."index.php?indexAction=view_observers&amp;sort=".$sort."&amp;previous=".$orig_previous;
if((array_key_exists('steps',$_SESSION))&&(array_key_exists("allObs",$_SESSION['steps'])))
  $step=$_SESSION['steps']["allObs"];
if(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*$step;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*$step;
elseif(array_key_exists('min',$_GET))
  $min=$_GET['min'];
else
  $min = 0;
list ($min,$max,$content) = $objUtil->printNewListHeader3($observers, $link, $min, $step);
echo "<div id=\"main\" style=\"position:relative\">";
$objPresentations->line(array("<h5>".LangViewObserverTitle."</h5>",$content),"LR",array(70,30),30);
$content=$objUtil->printStepsPerPage3($link,"allObs",$step);
$objPresentations->line(array($content),"R",array(100),20);
echo "<hr />";
echo "<table width=\"100%\">";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=id&amp;previous=$previous\">id</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=name&amp;previous=$previous\">".LangViewObserverName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=firstname&amp;previous=$previous\">".LangViewObserverFirstName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=email&amp;previous=$previous\">Email</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=registrationDate&amp;previous=$previous\">Reg. Date</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;sort=role&amp;previous=$previous\">".LangViewObserverRole."</a></td>";
echo "<td></td>";
echo "</tr>";
while(list ($key, $value) = each($observers))
{ if($count >= $min && $count < $max) // selection
  { $name = $objObserver->getObserverProperty($value,'name');
	  $firstname = $objObserver->getObserverProperty($value,'firstname');
	  $email = $objObserver->getObserverProperty($value,'email');
	  $regDate = $objObserver->getObserverProperty($value,'registrationDate');
	  echo "<tr class=\"type".(2-($count%2))."\">";
	  echo "<td><a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=".urlencode($value)."\">".$value."</a> </td>";
	  echo "<td>".$name."</td>";
	  echo "<td>".$firstname."</td>";
	  echo "<td> <a href=\"mailto:".$email."\"> ".$email." </a> </td>";
	  echo "<td>".$regDate." </td>";
	  $role = $objObserver->getObserverProperty($value,'role',2);
	  if ($role == RoleAdmin)
	    echo "<td> ".LangViewObserverAdmin."</td><td></td>";
	  elseif ($role == RoleUser)
	    echo "<td> ".LangViewObserverUser."</td><td></td>";
	  elseif ($role == RoleCometAdmin)
	    echo "<td> ".LangViewObserverCometAdmin."</td><td></td>";
	  elseif ($role == RoleWaitlist)
	    echo "<td> ".LangViewObserverWaitlist."</td><td><a href=\"".$baseURL."index.php?indexAction=validate_observer&amp;validate=".urlencode($value)."\">".LangViewObserverValidate."</a> / <a href=\"".$baseURL."index.php?indexAction=validate_delete_observer&amp;validateDelete=".urlencode($value)."\">"."Verwijder"."</a></td>";
	  echo "</tr>";
  }
  $count++;
}
echo "</table>";
echo "</div>";
}
?>
