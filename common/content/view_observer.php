<?php
// view_observer.php
// shows information of an observer 
// Version 0.4: 20060108, JV

$user=urldecode($objUtil->checkGetKey('user'));
$user=str_replace("&amp;", "&", $user);
$user=str_replace("&amp;", "&", $user);
if(!($objObserver->getObserverName($user)))                                     // no session variable set 
  throw new Exception(LangViewObserverInexistant); 
$firstname=$objObserver->getFirstName($user);
$name=$objObserver->getObserverName($user);
echo "<div id=\"main\" style=\"text-align:center\">";
echo "<h2>".$firstname.' '. $name."</h2>";
$dir = opendir($instDir.'common/observer_pics');
while(FALSE!==($file=readdir($dir)))
{ if(("." == $file)OR(".."== $file))
    continue;                                                                   // skip current directory and directory above
  if(fnmatch($user. ".gif", $file) || fnmatch($user. ".jpg",$file) || fnmatch($user. ".png", $file))
    echo "<p ><img class=\"viewobserver\"  src=\"".$baseURL."common/observer_pics/".$file."\" alt=\"".$firstname."&nbsp;".$name."\"></img></p>";
}
echo "<form action=\"".$baseURL."index.php?indexAction=change_role\">";
echo "<table width=\"100%\">";
if(array_key_exists('admin',$_SESSION)&&($_SESSION['admin']=="yes"))       // admin logged in
{ echo "<tr class=\"type1\">";
  echo "<td class=\"fieldname\" style=\"text-align:right\">";
  echo LangChangeAccountField2;
  echo "</td>";
  echo "<td style=\"text-align:left\">";
  echo "<a href=\"mailto:".$objObserver->getEmail($user)."\">".$objObserver->getEmail($user)."</a>";
  echo "</td>";
	echo "</tr>";
}
echo "<tr class=\"type2\">";
echo "<td  style=\"text-align:right\">";
echo LangChangeAccountField3;
echo "</td>";
echo "<td  style=\"text-align:left\">";
echo $objObserver->getFirstName($user);
echo "</td>";
echo "</tr>";
echo "<tr class=\"type1\">";
echo "<td  style=\"text-align:right\">";
echo LangChangeAccountField4;
echo "</td>";
echo "<td style=\"text-align:left\">";
echo $objObserver->getObserverName($user);
echo "</td>";
echo "</tr>";
echo "<tr class=\"type2\">";
echo "<td  style=\"text-align:right\">";
echo LangChangeAccountField7;
echo "</td>";
echo "<td  style=\"text-align:left\">";
$location_id = $objObserver->getStandardLocation($user);
$location_name = $objLocation->getLocationName($location_id);
echo "<a href=\"".$baseURL."index.php?indexAction=detail_location&amp;location=".urlencode($location_id)."\">".$location_name."</a>";
echo "</td>";
echo "</tr>";
echo "<tr class=\"type1\">";
echo "<td  style=\"text-align:right\">".LangChangeAccountField8."</td>";
echo "<td  style=\"text-align:left\">";
if($instrumentname=$objInstrument->getInstrumentName($objObserver->getStandardTelescope($user)))
  echo "<a href=\"".$baseURL."index.php?indexAction=detail_instrument&amp;instrument=".urlencode($objObserver->getStandardTelescope($user))."\">".(($instrumentname=="Naked eye")?InstrumentsNakedEye:$instrumentname)."</a>";
echo "</td>";
echo "</tr>";
if($objUtil->checkSessionKey('admin')=="yes")
{ echo "<tr class=\"type2\">";
  echo "<td style=\"text-align:right\">".LangViewObserverRole."</td>";
  if(($objObserver->getRole($user)!=RoleWaitlist)&&($user!="admin") )                 // user not in waitlist
  { echo "<td  style=\"text-align:left\">";
    echo "<select name=\"role\">";
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
  echo "</tr>";
}
echo "</table>";

echo "<table width=\"100%\">";
echo "<tr>";
$number_of_observations=$objObserver->getNumberOfDsObservations($user);                 // NUMBER OF OBSERVATIONS
$rank = $objObserver->getRank($user);
if($rank == 0)
  $rank = "-";
$number_of_comet_observations = $objObserver->getNumberOfCometObservations($user);
if(($number_of_observations!=0)||($number_of_comet_observations!=0))
{ echo "<td colspan=\"".(sizeof($modules) + 1)."\" class=\"type1\">&nbsp;</td>";
  echo "</tr>";
  echo "<tr class=\"type3\">";
  echo "<td> &nbsp; </td>";
  for ($i = 0; $i < count($modules);$i++)
  { $mod = $modules[$i];
    echo"<td>" . $$mod . "</td>\n";
  }
  $numberOfObservations=$objObservation->getNumberOfDsObservations();
  $numberOfObservationsThisYear=$objObservation->getNumberOfObservationsLastYear();
  $numberOfDifferentObjects=$objObservation->getNumberOfDifferentObjects();
  $observationsThisYear=$objObservation->getObservationsLastYear($user);
  $cometrank = $objObserver->getCometRank($user);
  if ($cometrank == 0)
    $cometrank = "-";
  $numberOfCometObservations=$objCometObservation->getNumberOfObservations();
  $numberOfCometObservationsThisYear=$objCometObservation->getNumberOfObservationsThisYear();
  $numberOfDifferentCometObjects=$objCometObservation->getNumberOfDifferentObjects();
  if($numberOfDifferentCometObjects == "")
  { $numberOfDifferentCometObjects = 0;
  }
  $objCometObservationThisYear=$objCometObservation->getObservationsThisYear($user);
  // Loop over all the modules. Put the information in an array, sorted on the 
  // modules.
  // The array has the following information :
  //  $information[$i][0] = The string describing the number of observations.
  //  $information[$i][1] = The string describing the number of observations this year.
  //  $information[$i][2] = The string describing the number of different objects.
  //  $information[$i][4] = The string describing the rank of the observer
  for ($i = 0; $i < count($modules);$i++)
  { if(strcmp($$modules[$i], $deepsky) == 0)
    { if ($numberOfObservations != 0)
      { $percentObservations = ($number_of_observations / $numberOfObservations) * 100;
      }
      else
      { $percentObservations = 0;
      }
    $information[$i][0] = $number_of_observations." / ".$numberOfObservations." (".sprintf("%.2f",$percentObservations)."%)";
    if($numberOfObservationsThisYear!=0)
    { $percentObservations=($observationsThisYear / $numberOfObservationsThisYear) * 100;
    }
    else
    { $percentObservations = 0;
    }
    $information[$i][1]=$observationsThisYear." / ".$numberOfObservationsThisYear."&nbsp;&nbsp;&nbsp;&nbsp;(".sprintf("%.2f",$percentObservations)."%)";
    $numberOfObjects=$objObservation->getNumberOfObjects($user);                // Deepsky : Number of different objects
    if ($numberOfDifferentObjects != 0)
    { $percentObjects=($numberOfObjects/$numberOfDifferentObjects) * 100;
    }
    else
    { $percentObjects=0;
    }
    $information[$i][2]=$numberOfObjects." / ".$numberOfDifferentObjects." (" . sprintf("%.2f",$percentObjects)."%)";
    $information[$i][4]=$rank;                                                  // Deepsky : Rank
   }
   if(strcmp($$modules[$i], $comets) == 0)
   { if ($numberOfCometObservations != 0)
     { $percentObservations = ($number_of_comet_observations / $numberOfCometObservations) * 100;
     }
     else
    { $percentObservations = 0;
    }
    $information[$i][0]=$number_of_comet_observations." / ".$numberOfCometObservations." (".sprintf("%.2f", $percentObservations) . "%)";
    if ($numberOfCometObservationsThisYear!=0)
    { $percentCometObservations=($objCometObservationThisYear/$numberOfCometObservationsThisYear) * 100;
    }
    else
    { $percentCometObservations = 0;
    }
    $information[$i][1] = $objCometObservationThisYear." / ".$numberOfCometObservationsThisYear."&nbsp;(".sprintf("%.2f", $percentCometObservations)."%)";
    $numberOfCometObjects = $objCometObservation->getNumberOfObjects($user);    // Comets : Number of different objects
    if ($numberOfDifferentCometObjects!=0)
    { $percentObjects=($numberOfCometObjects/$numberOfDifferentCometObjects)*100;
    }
    else
    { $percentObjects=0;
    }
    $information[$i][2]=$numberOfCometObjects . " / ".$numberOfDifferentCometObjects." (" . sprintf("%.2f", $percentObjects)."%)";
    $information[$i][4]=$cometrank;                                             // Comets : Rank
  }
}

echo "<tr class=\"type1\">";                                                    // Now that all the information is available in the $information array, we can print the table.
echo "<td  style=\"text-align:right\">".LangViewObserverNumberOfObservations."</td>";
for ($i = 0;$i < count($modules);$i++)
{ echo "<td>".$information[$i][0]."</td>";
}
echo "</tr>";
echo "<tr class=\"type2\">";
echo "<td  style=\"text-align:right\">".LangTopObserversHeader4."</td>";
for ($i = 0;$i < count($modules);$i++)
{ echo "<td>" . $information[$i][1] . "</td>";
}
echo "</tr>";
echo "<tr>";
echo "<td  style=\"text-align:right\">".LangTopObserversHeader6."</td>";
for ($i = 0;$i < count($modules);$i++)
{ echo "<td>" . $information[$i][2] . "</td>";
}
echo "</tr>";
$key=array_search("deepsky", $modules);                                         // Deepsky : Number of Messier objects. This should only be displayed if the Deepsky Module is available.
if(is_null($key))
{ $key = false;
}
if ($key !== false)
{ echo "<tr  class=\"type2\">";
  echo "<td style=\"text-align:right\">".LangTopObserversHeader5."</td>";
  for($i=0;$i<count($modules);$i++)
  { echo "<td>";
    if($key==$i)
    { echo $objObservation->getObservedCountFromCatalogue($user,"M")." / 110";
    }
    else
    { echo "-";
    }
    echo "</td>";
  }
  echo "<tr class=\"type1\">";
  echo "<td  style=\"text-align:right\">".LangTopObserversHeader5b."</td>";
  for($i=0;$i<count($modules);$i++)
  { echo "<td>";
    if ($key == $i)
    { echo $objObservation->getObservedCountFromCatalogue($user,"Caldwell")." / 110";
    }
    else
    { echo "-";
    }
    echo "</td>";
  }
  echo "<tr class=\"type2\">";
  echo "<td  style=\"text-align:right\">".LangTopObserversHeader5c."</td>";
  for($i=0;$i<count($modules);$i++)
  { echo "<td>";
    if ($key == $i)
    { echo $objObservation->getObservedCountFromCatalogue($user,"H400")." / 400";
    }
    else
    { echo "-";
    }
    echo "</td>";
  }

  echo "<tr class=\"type1\">";
  echo "<td  style=\"text-align:right\">".LangTopObserversHeader5d."</td>";
  for($i=0;$i<count($modules);$i++)
  { echo "<td>";
    if($key==$i)
    { echo $objObservation->getObservedCountFromCatalogue($user,"HII")." / 400";
    }
    else
    { echo "-";
    }
    echo "</td>";
  }
echo "</tr>";
}
echo "<tr class=\"type2\">";
echo "<td  style=\"text-align:right\">".LangViewObserverRank."</td>";
for($i=0;$i<count($modules);$i++)
{ echo "<td>".$information[$i][4]."</td>";
}
echo "</tr>";
}
echo "<tr>";
echo "<td></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>
