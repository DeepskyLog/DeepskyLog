<?php // view_observer.php - shows information of an observer 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($user=$objUtil->checkGetKey('user'))) throw new Exception(LangException015b);
else
{
$name=$objObserver->getObserverProperty($user,'name'); 
$firstname=$objObserver->getObserverProperty($user,'firstname');
$location_id = $objObserver->getObserverProperty($user,'stdlocation');
$location_name = $objLocation->getLocationPropertyFromId($location_id,'name');
$instrumentname=$objInstrument->getInstrumentPropertyFromId($objObserver->getObserverProperty($user,'stdtelescope'),'name');
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
$objPresentations->line(array("<h4>".$firstname.' '. $name."</h4>"),"L",array(),30);
echo "<hr />";

if(array_key_exists('admin',$_SESSION)&&($_SESSION['admin']=="yes"))       // admin logged in
  $objPresentations->line(array(LangChangeAccountField2.":","<a href=\"mailto:".$objObserver->getObserverProperty($user,'email')."\">".$objObserver->getObserverProperty($user,'email')."</a>"),"RL",array(20,80),'',array('type10','type10'));
$objPresentations->line(array(LangChangeAccountField3.":",$objObserver->getObserverProperty($user,'firstname')),"RL",array(20,80),20,array('type20','type20'));
$objPresentations->line(array(LangChangeAccountField4.":",$objObserver->getObserverProperty($user,'name')),"RL",array(20,80),20,array('type10','type10'));
$objPresentations->line(array(LangChangeAccountField7.":","<a href=\"".$baseURL."index.php?indexAction=detail_location&amp;location=".urlencode($location_id)."\">".$location_name."</a>"),"RL",array(20,80),20,array('type20','type20'));
$objPresentations->line(array(LangChangeAccountField8.":",($instrumentname?"<a href=\"".$baseURL."index.php?indexAction=detail_instrument&amp;instrument=".urlencode($objObserver->getObserverProperty($user,'stdtelescope'))."\">".(($instrumentname=="Naked eye")?InstrumentsNakedEye:$instrumentname)."</a>":"")),"RL",array(20,80),20,array('type10','type10'));
if($objUtil->checkSessionKey('admin')=="yes")
{ echo "<form action=\"".$baseURL."index.php\" >";
  echo "<input type=\"hidden\" name=\"indexAction\" value=\"change_role\" />";
  echo "<input type=\"hidden\" name=\"user\" value=\"".$user."\" />";
  $content='';
  if($user!="admin")
  { $content = "<select name=\"role\" class=\"\">";
    $content.= "<option ".(($objObserver->getObserverProperty($user,'role',2)==RoleAdmin)?"selected=\"selected\"":"")." value=\"0\">".LangViewObserverAdmin."</option>";
    $content.= "<option ".(($objObserver->getObserverProperty($user,'role',2)==RoleUser)?"selected=\"selected\"":"")." value=\"1\">".LangViewObserverUser."</option>";
    $content.= "<option ".(($objObserver->getObserverProperty($user,'role',2)==RoleCometAdmin)?"selected=\"selected\"":"")." value=\"4\">".LangViewObserverCometAdmin."</option>";
    $content.= "<option ".(($objObserver->getObserverProperty($user,'role',2)==RoleWaitlist)?"selected=\"selected\"":"")." value=\"2\">".LangViewObserverWaitlist."</option>";
    $content.= "</select>&nbsp;";
    $content.= "<input type=\"submit\" name=\"change\" value=\"".LangViewObserverChange."\" />";
  }
  elseif($objObserver->getObserverProperty($user,'role',2)==RoleWaitlist)
    $content = LangViewObserverWaitlist;
  else                                                                          // fixed admin role
  { $content = LangViewObserverAdmin;
  }
  $objPresentations->line(array(LangViewObserverRole.":",$content),"RL",array(20,80),'40',array('fieldname type20','type20'));
  echo "</form>";
}
echo "<hr />";
$content=array();
$classes=array();
$content[]="";
$alignment="R";
$classes[]="";
for($i=0;$i<count($modules);$i++)
{ $content[]=$GLOBALS[$modules[$i]];
  $classes[]="type30";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

$content=array();
$classes=array();
$content[]=LangViewObserverNumberOfObservations.":";
$alignment="R";
$classes[]="fieldname type10";
for($i=0;$i<count($modules);$i++)
{ $content[]=$information[$i][0];
  $classes[]="fieldvalue type10";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

$content=array();
$classes=array();
$content[]=LangTopObserversHeader4.":";
$alignment="R";
$classes[]="fieldname type20";
for($i=0;$i<count($modules);$i++)
{ $content[]=$information[$i][1];
  $classes[]="fieldvalue type20";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

$content=array();
$classes=array();
$content[]=LangTopObserversHeader6.":";
$alignment="R";
$classes[]="fieldname type10";
for($i=0;$i<count($modules);$i++)
{ $content[]=$information[$i][2];
  $classes[]="fieldvalue type10";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

$content=array();
$classes=array();
$content[]=LangTopObserversHeader5.":";
$alignment="R";
$classes[]="fieldname type20";
for($i=0;$i<count($modules);$i++)
{ $content[]=(($key==$i)?$userMobjects." / 110":"-");
  $classes[]="fieldvalue type20";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

$content=array();
$classes=array();
$content[]=LangTopObserversHeader5b.":";
$alignment="R";
$classes[]="fieldname type10";
for($i=0;$i<count($modules);$i++)
{ $content[]=(($key==$i)?$userCaldwellObjects." / 110":"-");
  $classes[]="fieldvalue type10";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

$content=array();
$classes=array();
$content[]=LangTopObserversHeader5c.":";
$alignment="R";
$classes[]="fieldname type20";
for($i=0;$i<count($modules);$i++)
{ $content[]=(($key==$i)?$userH400objects." / 400":"-");
  $classes[]="fieldvalue type20";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

$content=array();
$classes=array();
$content[]=LangTopObserversHeader5d.":";
$alignment="R";
$classes[]="fieldname type10";
for($i=0;$i<count($modules);$i++)
{ $content[]=(($key==$i)?$userHIIobjects." / 400":"-");
  $classes[]="fieldvalue type10";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

$content=array();
$classes=array();
$content[]=LangViewObserverRank.":";
$alignment="R";
$classes[]="fieldname type20";
for($i=0;$i<count($modules);$i++)
{ $content[]=$information[$i][4];
  $classes[]="fieldvalue type20";
  $alignment.="C";
}
$objPresentations->line($content,$alignment,array(33,33,34),25,$classes);

echo "<hr />";
$dir = opendir($instDir.'common/observer_pics');
while(FALSE!==($file=readdir($dir)))
{ if(("." == $file)OR(".."== $file))
    continue;                                                                   // skip current directory and directory above
  if(fnmatch($user. ".gif", $file) || fnmatch($user. ".jpg",$file) || fnmatch($user. ".png", $file))
  { echo "<div><img class=\"viewobserver\" src=\"".$baseURL."common/observer_pics/".$file."\" alt=\"".$firstname."&nbsp;".$name."\"></img></div>";
    echo "<hr />";
  }
}

echo "</div>";
}
?>