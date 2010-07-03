<?php
function objectsSets()
{ global $objObserver, $loggedUser, $baseURL;
  $fovo=$objObserver->getObserverProperty($loggedUser,'overviewFoV',120);
  $fovl=$objObserver->getObserverProperty($loggedUser,'lookupFoV',60);
  $fovd=$objObserver->getObserverProperty($loggedUser,'detailFoV',15);
  $dsoso=$objObserver->getObserverProperty($loggedUser,'overviewdsos',10);
  $dsosl=$objObserver->getObserverProperty($loggedUser,'lookupdsos',11);
  $dsosd=$objObserver->getObserverProperty($loggedUser,'detaildsos',14);
  $starso=$objObserver->getObserverProperty($loggedUser,'overviewstars',10);
  $starsl=$objObserver->getObserverProperty($loggedUser,'lookupstars',11);
  $starsd=$objObserver->getObserverProperty($loggedUser,'detailstars',14);
  $k=count($_SESSION['Qobj']);
	echo "<script type=\"text/javascript\" src=\"".$baseURL."common/content/objectsSets.js\"></script>";
  echo "<input type=\"button\" value=\""."Go"."\" onclick=\"generate();\"/>";
  echo "<hr />";
  echo "<table>";
  echo "<tr><td>Object</td><td>FoVs</td><td>dsos</td><td>stars</td></tr>";
  for($i=0;$i<$k;$i++)
  { echo "<tr>";
    echo "<td id=\"R".$i."\">".$_SESSION['Qobj'][$i]['showname']."</td>
          <td><input type=\"text\" id=\"R".$i."D"."fov"."\" value=\"".$fovo." ".$fovl." ".$fovd."\" /></td>
          <td><input type=\"text\" id=\"R".$i."D"."dsos"."\" value=\"".$dsoso." ".$dsosl." ".$dsosd."\"/></td>
          <td><input type=\"text\" id=\"R".$i."D"."stars"."\" value=\"".$starso." ".$starsl." ".$starsd."\"/></td>";
  	echo "</tr>";
  }
  echo "</table>";
}
objectsSets();
?>
