<?php
// view_observer.php
// shows information of an observer 

if(!$objUtil->checkGetKey('user'))
  throw new Exception("User not specified");
$user=$objUtil->checkGetKey('user');
if(!($name=$objObserver->getObserverProperty($user,'name'))) 
  throw new Exception($user); 

$firstname=$objObserver->getObserverProperty($user,'firstname');
$location_id = $objObserver->getStandardLocation($user);
$location_name = $objLocation->getLocationPropertyFromId($location_id,'name');
$instrumentname=$objInstrument->getInstrumentPropertyFromId($objObserver->getStandardTelescope($user),'name');

$userDSobservation=$objObserver->getNumberOfDsObservations($user);
$totalDSObservations=$objObservation->getNumberOfDsObservations();
$userDSYearObservations=$objObservation->getObservationsLastYear($user);
$totalDSYearObservations=$objObservation->getObservationsLastYear('%');
$userDSObjects=$objObservation->getNumberOfObjects($user);
$totalDSobjects=$objObservation->getNumberOfDifferentObservedDSObjects();
$userMobjects=$objObservation->getObservedCountFromCatalogOrList($user,"M");
$userCaldwellObjects=$objObservation->getObservedCountFromCatalogOrList($user,"Caldwell");
$userH400objects=$objObservation->getObservedCountFromCatalogOrList($user,"H400");
$userHIIobjects=$objObservation->getObservedCountFromCatalogOrList($user,"HII");
$userDSrank=$objObserver->getDsRank($user);
if($userDSrank===false)
  $userDSrank = "-";
else
  $userDSrank++;
  
$userCometobservation=$objObserver->getNumberOfCometObservations($user);
$totalCometObservations=$objCometObservation->getNumberOfObservations();
$userCometYearObservations=$objCometObservation->getObservationsThisYear($user);
$totalCometYearObservations=$objCometObservation->getNumberOfObservationsThisYear();
$userCometObjects = $objCometObservation->getNumberOfObjects($user);
$totalCometobjects=$objCometObservation->getNumberOfDifferentObjects();
$cometrank = $objObserver->getCometRank($user);
if ($cometrank===false)
  $cometrank = "-";
else
  $cometrank++;
 
for($i =0;$i<count($modules);$i++)
{ if(strcmp($$modules[$i], $deepsky)==0)
  { $information[$i][0]=$userDSobservation." / ".$totalDSObservations." (".sprintf("%.2f",($userDSobservation / $totalDSObservations) * 100)."%)";
    $information[$i][1]=$userDSYearObservations." / ".$totalDSYearObservations."&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f",$userDSYearObservations/$totalDSYearObservations*100)."%)";
    $information[$i][2]=$userDSObjects." / ".$totalDSobjects." (" . sprintf("%.2f",$userDSObjects/$totalDSobjects*100)."%)";
    $information[$i][4]=$userDSrank;
  }
  if(strcmp($$modules[$i], $comets) == 0)
  { $information[$i][0]=$userCometobservation." / ".$totalCometObservations." (".sprintf("%.2f", $userCometobservation/$totalCometObservations*100)."%)";
    $information[$i][1]=$userCometYearObservations." / ".$totalCometYearObservations."&nbsp;(".sprintf("%.2f", $userCometYearObservations/($totalCometYearObservations?$totalCometYearObservations:1)*100)."%)";
    $information[$i][2]=$userCometObjects . " / ".$totalCometobjects." (" . sprintf("%.2f", $userCometObjects/$totalCometobjects*100)."%)";
    $information[$i][4]=$cometrank;
  }
}  
  
  
  
echo "<div id=\"main\">";
echo "<h2>".$firstname.' '. $name."</h2>";
$dir = opendir($instDir.'common/observer_pics');
while(FALSE!==($file=readdir($dir)))
{ if(("." == $file)OR(".."== $file))
    continue;                                                                   // skip current directory and directory above
  if(fnmatch($user. ".gif", $file) || fnmatch($user. ".jpg",$file) || fnmatch($user. ".png", $file))
    echo "<p><img class=\"viewobserver\" src=\"".$baseURL."common/observer_pics/".$file."\" alt=\"".$firstname."&nbsp;".$name."\"></img></p>";
}
echo "<table>";
if(array_key_exists('admin',$_SESSION)&&($_SESSION['admin']=="yes"))       // admin logged in
  tableTypeFieldnameField('type1',LangChangeAccountField2,"<a href=\"mailto:".$objObserver->getObserverProperty($user,'email')."\">".$objObserver->getEmail($user)."</a>");
tableTypeFieldnameField("type2",LangChangeAccountField3,$objObserver->getObserverProperty($user,'firstname'));
tableTypeFieldnameField("type1",LangChangeAccountField4,$objObserver->getObserverProperty($user,'name'));
tableTypeFieldnameField("type2",LangChangeAccountField7,"<a href=\"".$baseURL."index.php?indexAction=detail_location&amp;location=".urlencode($location_id)."\">".$location_name."</a>");
tableTypeFieldnameField("type1",LangChangeAccountField8,($instrumentname?"<a href=\"".$baseURL."index.php?indexAction=detail_instrument&amp;instrument=".urlencode($objObserver->getStandardTelescope($user))."\">".(($instrumentname=="Naked eye")?InstrumentsNakedEye:$instrumentname)."</a>":""));
if($objUtil->checkSessionKey('admin')=="yes")
{ echo "<tr class=\"type2\">";
  echo "<td class=\"fieldname\">".LangViewObserverRole."</td>";
  echo "<form action=\"".$baseURL."index.php\" >";
  echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_role\" />";
  echo "<input type=\"hidden\" name=\"user\" value=\"".$user."\" />";
  if($user!="admin")
  { echo "<td>";
    echo "<select name=\"role\" class=\"fieldvalue\">";
    echo "<option ".(($objObserver->getRole($user)==RoleAdmin)?"selected=\"selected\"":"")." value=\"0\">".LangViewObserverAdmin."</option>";
    echo "<option ".(($objObserver->getRole($user)==RoleUser)?"selected=\"selected\"":"")." value=\"1\">".LangViewObserverUser."</option>";
    echo "<option ".(($objObserver->getRole($user)==RoleCometAdmin)?"selected=\"selected\"":"")." value=\"4\">".LangViewObserverCometAdmin."</option>";
    echo "<option ".(($objObserver->getRole($user)==RoleWaitlist)?"selected=\"selected\"":"")." value=\"2\">".LangViewObserverWaitlist."</option>";
    echo "</select>";
    echo "<input type=\"submit\" name=\"change\" value=\"".LangViewObserverChange."\" />";
  }
  elseif($objObserver->getRole($user)==RoleWaitlist)
    echo(LangViewObserverWaitlist."</td>");
  else                                                                          // fixed admin role
  {  echo "<td>".LangViewObserverAdmin."</td>";
  }
  echo "</form>";
  echo "</tr>";
}
echo "</table>";
echo "<hr />";

echo "<table>";
echo "<tr class=\"type3\">";
echo "<td>&nbsp;</td>";
for($i=0;$i<count($modules);$i++)
  echo"<td style=\"text-align:center\">".$GLOBALS[$modules[$i]]."</td>";
echo "<tr class=\"type1\">";
echo "<td class=\"fieldname\">".LangViewObserverNumberOfObservations."</td>";
for($i=0;$i<count($modules);$i++)
  echo "<td class=\"fieldvalue\">".$information[$i][0]."</td>";
echo "</tr>";
echo "<tr class=\"type2\">";
echo "<td class=\"fieldname\">".LangTopObserversHeader4."</td>";
for($i=0;$i<count($modules);$i++)
  echo "<td class=\"fieldvalue\">".$information[$i][1]."</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangTopObserversHeader6."</td>";
for ($i = 0;$i < count($modules);$i++)
  echo "<td class=\"fieldvalue\">" . $information[$i][2] . "</td>";
echo "</tr>";
$key=array_search("deepsky", $modules);
if(!is_null($key))
{ echo "<tr  class=\"type2\">";
  echo "<td style=\"text-align:right\">".LangTopObserversHeader5."</td>";
  for($i=0;$i<count($modules);$i++)
    echo "<td class=\"fieldvalue\">".(($key==$i)?$userMobjects." / 110":"-")."</td>";
  echo "</tr>";
  echo "<tr class=\"type1\">";
  echo "<td class=\"fieldname\">".LangTopObserversHeader5b."</td>";
  for($i=0;$i<count($modules);$i++)
    echo "<td class=\"fieldvalue\">".(($key==$i)?$userCaldwellObjects." / 110":"-")."</td>";
  echo "</tr>";
  echo "<tr class=\"type2\">";
  echo "<td class=\"fieldname\">".LangTopObserversHeader5c."</td>";
  for($i=0;$i<count($modules);$i++)
    echo "<td class=\"fieldvalue\">".(($key==$i)?$userH400objects." / 400":"-")."</td>";
  echo "</tr>";
  echo "<tr class=\"type1\">";
  echo "<td class=\"fieldname\">".LangTopObserversHeader5d."</td>";
  for($i=0;$i<count($modules);$i++)
    echo "<td class=\"fieldvalue\">".(($key==$i)?$userHIIobjects." / 400":"-")."</td>";
  echo "</tr>";
}
echo "<tr class=\"type2\">";
echo "<td class=\"fieldname\">".LangViewObserverRank."</td>";
for($i=0;$i<count($modules);$i++)
  echo "<td class=\"fieldvalue\">".$information[$i][4]."</td>";
echo "</tr>";
echo "</table>";

echo "</div>";
?>
