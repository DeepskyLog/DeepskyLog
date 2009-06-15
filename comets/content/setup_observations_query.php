<?php // setup_observations_query.php - interface to query observations
include_once "lib/icqmethod.php";
include_once "lib/icqreferencekey.php";
$_SESSION['result'] = "";
echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php\" method=\"get\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"comets_result_selected_observations\" />";
$id=$objUtil->checkSessionKey('observedobject',$objUtil->checkGetKey('observedobject'));
$objPresentations->line(array("<h4>".LangQueryObservationsTitle."</h4>",
                              "<input type=\"submit\" name=\"query\" value=\"".LangQueryObservationsTitle."\" />"),
                        "LR",array(70,30),30);
echo "<hr />";
// OBJECT NAME
$content1=LangQueryObjectsField1;
$content2="<select name=\"object\">";
$content2.="<option value=\"\">&nbsp;</option>";
$catalogs=$objCometObject->getSortedObjects("name");
while(list($key,$value)=each($catalogs))
  $content2.="<option value=\"".$value[0]."\"".((($id)&&($id==$objCometObject->getId($value)))?" selected=\"selected\" ":"").">".$value[0]."</option>";
$content2.="</select>";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// OBSERVER 
$content1=LangViewObservationField2;
$content2="<select name=\"observer\">";
$content2.="<option value=\"\">&nbsp;</option>";
$obs = $objObserver->getSortedObservers('name'); 
$obs = $objCometObservation->getPopularObservers();
while(list($key,$value)=each($obs))
  $sortobs[$value] = $objObserver->getObserverProperty($value,'name')." ".$objObserver->getObserverProperty($value,'firstname');
natcasesort($sortobs);
while(list($value, $key) = each($sortobs))
  $content2.="<option value=\"".$value."\">".$key."</option>";
$content2.="</select>";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// INSTRUMENT 
$content1=LangViewObservationField3;
$content2="<select name=\"instrument\">";
$content2.="<option value=\"\">&nbsp;</option>";
$inst = $objInstrument->getSortedInstrumentsList("name");
while(list($key,$value)=each($inst))
  $content2.="<option value=\"".$key."\">".$value."</option>";
$content2.="</select>";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MINIMUM DIAMETER
$content1=LangViewObservationField13;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"mindiameter\" size=\"10\" />";
$content2.="&nbsp;";
$content2.="<select name=\"mindiameterunits\" class=\"inputfield\"><option selected=\"selected\">&nbsp;</option><option>inch</option><option>mm</option></select>";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MAXIMUM DIAMETER
$content1=LangViewObservationField14;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"maxdiameter\" size=\"10\" />";
$content2.="&nbsp;";
$content2.="<select name=\"maxdiameterunits\" class=\"inputfield\"><option selected=\"selected\">&nbsp;</option><option>inch</option><option>mm</option></select>";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// SITE 
$content1=LangViewObservationField4;
$content2="<select name=\"site\">";
$content2.="<option value=\"\">&nbsp;</option>";
$sites = $objLocation->getSortedLocations("name");
while(list($key,$value)=each($sites))
  if($key)
    $content2.="<option value=\"".$value."\"".">".$objLocation->getLocationPropertyFromId($value,'name')."</option>";
$content2.="</select>";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MINIMUM DATE
$content1=LangFromDate;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"minday\" value=\"\" />";
$content2.="&nbsp;&nbsp;";
$content2.="<select name=\"minmonth\">";
$content2.="<option value=\"\">&nbsp;</option>";
for($i=1;$i<13;$i++)
  $content2.="<option value=\"".$i."\">".constant("LangNewObservationMonth".$i)."</option>";
$content2.="</select>";
$content2.="&nbsp;&nbsp;";
$content2.="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"minyear\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MAXIMUM DATE
$content1=LangTillDate;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"maxday\" value=\"\" />";
$content2.="&nbsp;&nbsp;";
$content2.="<select name=\"maxmonth\">";
$content2.="<option value=\"\">&nbsp;</option>";
for($i=1;$i<13;$i++)
  $content2.="<option value=\"".$i."\">".constant("LangNewObservationMonth".$i)."</option>";
$content2.="</select>";
$content2.="&nbsp;&nbsp;";
$content2.="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"maxyear\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// DESCRIPTION
$content1=LangQueryObservationsMessage2;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"description\" size=\"35\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MAXIMUM MAGNITUDE
$content1=LangQueryObjectsField4;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxmag\" size=\"4\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MINIMUM MAGNITUDE
$content1=LangQueryObjectsField3;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minmag\" size=\"4\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MINIMUM DC
$content1=LangQueryCometObjectsField3;
$content2="<select name=\"mindc\">";
$content2.="<option value=\"\">&nbsp;</option>";
for ($i=1;$i<=9;$i++)
  $content2.="<option value=\"".$i."\">".$i."</option>";
$content2.="</select>";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MAXIMUM DC
$content1=LangQueryCometObjectsField4;
$content2="<select name=\"maxdc\">";
$content2.="<option value=\"\">&nbsp;</option>";
for ($i=1;$i<=9;$i++)
  $content2.="<option value=\"".$i."\">".$i."</option>";
$content2.="</select>";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MINIMUM COMA
$content1=LangQueryCometObjectsField5;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"mincoma\" size=\"4\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MAXIMUM COMA
$content1=LangQueryCometObjectsField6;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxcoma\" size=\"4\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MINIMUM TAIL
$content1=LangQueryCometObjectsField7;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"mintail\" size=\"4\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
// MAXIMUM TAIL
$content1=LangQueryCometObjectsField8;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxtail\" size=\"4\" value=\"\" />";
$objPresentations->line(array($content1,$content2),"RL",array(30,70),30,array("fieldname",""));
echo "</form>";
echo "</div>";
?>
