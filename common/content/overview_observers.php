<?php // overview_observers.php - generates an overview of all observers (admin only)
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif($_SESSION['admin']!="yes") throw new Exception(LangException001);
else
{ set_time_limit(60);
  $sort=$objUtil->checkGetKey('sort','inverse.observers.registrationDate');
  $inverse=false;
  if(substr($sort,0,8)=='inverse.')
  { $sort=substr($sort,8);
    $inverse=true;
  }
  if((array_key_exists('observersArr',$_SESSION))&&($_SESSION['observersArrSort']==$sort))
  { $observersArr=$_SESSION['observersArr'];
    if($inverse)
    { $observersArr=array_reverse($observersArr, true);
      $_SESSION['observersArr']=$observersArr;
    }
  }
  elseif(array_key_exists('observersArr',$_SESSION)&&($_SESSION['observersArrSort']!=$sort))
  { $observersArr=$_SESSION['observersArr'];
    $observersArr=$objUtil->recordsetSort($observersArr,((substr($sort,0,10)=="observers.")?substr($sort,10):$sort),SORT_ASC,SORT_ASC);
    $_SESSION['observersArr']=$observersArr;
    $_SESSION['observersArrSort']=$sort;
  }
  else
  { $observersArr=$objObserver->getSortedObserversAdmin($sort);
    if($inverse)
      $observersArr=array_reverse($observersArr, true);
    $_SESSION['observersArr']=$observersArr;
    $_SESSION['observersArrSort']=$sort;
  }
  $count = 0;
  $link=$baseURL."index.php?indexAction=view_observers&amp;sort=".$sort;
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
  list ($min,$max,$content) = $objUtil->printNewListHeader3($observersArr, $link, $min, $step);
  echo "<div id=\"main\">";
  $objPresentations->line(array("<h4>".LangViewObserverTitle."</h4>",$content),"LR",array(70,30),30);
  $content=$objUtil->printStepsPerPage3($link,"allObs",$step);
  $objPresentations->line(array($content),"R",array(100),20);
  echo "<hr />";
  echo "<table>";
  echo "<tr class=\"type3\">";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='observers.id'?'sort=inverse.observers.id':'sort=observers.id')."\">id</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='observers.name'?'sort=inverse.observers.name':'sort=observers.name')."\">".LangViewObserverName."</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='observers.firstname'?'sort=inverse.observers.firstname':'sort=observers.firstname')."\">".LangViewObserverFirstName."</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='observers.email'?'sort=inverse.observers.email':'sort=observers.email')."\">Email</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='observers.registrationDate'?'sort=inverse.observers.registrationDate':'sort=observers.registrationDate')."\">Reg. Date</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='observers.role'?'sort=inverse.observers.role':'sort=observers.role')."\">".LangViewObserverRole."</a></td>";
  echo "<td></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='maxLogDate'?'sort=inverse.maxLogDate':'sort=maxLogDate')."\">".LangViewObserverLastLogin."</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='obsCount'?'sort=inverse.obsCount':'sort=obsCount')."\">"."Observations"."</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='cometobsCount'?'sort=inverse.cometobsCount':'sort=cometobsCount')."\">"."comet Observations"."</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='instrumentCount'?'sort=inverse.instrumentCount':'sort=instrumentCount')."\">".LangViewObserverinstrumentCount."</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='listCount'?'sort=inverse.listCount':'sort=listCount')."\">"."list Count"."</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=view_observers&amp;".($sort=='maxMax'?'sort=inverse.maxMax':'sort=maxMax')."\">"."max Max"."</a></td>";
  echo "</tr>";
  while(list ($key, $value) = each($observersArr))
  { if(($count>=$min)&&($count<$max))
    { echo "<tr class=\"type".(2-($count%2))."\">";
  	  echo "<td><a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=".urlencode($value['id'])."\">".$value['id']."</a> </td>";
  	  echo "<td>".$value['name']."</td>";
  	  echo "<td>".$value['firstname']."</td>";
  	  echo "<td> <a href=\"mailto:".$value['email']."\"> ".$value['email']." </a> </td>";
  	  echo "<td>".$value['registrationDate']." </td>";
  	  $role = $objObserver->getObserverProperty($value['id'],'role',2);
  	  if ($role == RoleAdmin)
  	    echo "<td> ".LangViewObserverAdmin."</td><td></td>";
  	  elseif ($role == RoleUser)
  	  { echo "<td> ".LangViewObserverUser."</td>";
  	    if($value['maxMax'])
          echo "<td class=\"centered\">niet verwijderbaar</td>";
  	    else
  	      echo "<td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=validate_delete_observer&amp;validateDelete=".urlencode($value['id'])."\">"."Verwijder"."</a></td>";
  	  }
  	  elseif ($role == RoleCometAdmin)
  	    echo "<td> ".LangViewObserverCometAdmin."</td><td></td>";
  	  elseif ($role == RoleWaitlist)
  	    echo "<td> ".LangViewObserverWaitlist."</td><td class=\"centered\"><a href=\"".$baseURL."index.php?indexAction=validate_observer&amp;validate=".urlencode($value['id'])."\">".LangViewObserverValidate."</a> / <a href=\"".$baseURL."index.php?indexAction=validate_delete_observer&amp;validateDelete=".urlencode($value['id'])."\">"."Verwijder"."</a></td>";
      echo "<td>".$value['maxLogDate']." </td>";
      echo "<td>".$value['obsCount']." </td>";
      echo "<td>".$value['cometobsCount']." </td>";
      echo "<td>".$value['instrumentCount']." </td>";
      echo "<td>".$value['listCount']." </td>";
      echo "<td>".$value['maxMax']." </td>";
      echo "</tr>";
    }
    $count++;
  }
  echo "</table>";
  echo "<hr />";
  echo "</div>";
}
?>
