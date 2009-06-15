<?php  // new_observation.php - GUI to add a new observation of a comet to the database - Version 0.5: 2005/12/05, JV
include_once "lib/icqmethod.php";
include_once "lib/icqreferencekey.php";
$role = $objObserver->getObserverProperty($loggedUser,'role',2);
$adapt=false;
echo "<div id=\"main\">";
echo "<form action=\"".$baseURL."index.php\" method=\"post\" enctype=\"multipart/form-data\">";
if(($objUtil->checkGetKey('indexAction')=="comets_adapt_observation")&&(($role == RoleAdmin) || ($role == RoleCometAdmin))&&($obsid=$objUtil->checkRequestKey('observation',0)))
{ $adapt=true;
  echo "<input type=\"hidden\" name=\"observation\" value=\"".$obsid."\" />";
}
echo "<input type=\"hidden\" name=\"indexAction\" value=\"".($adapt?"comets_validate_change_observation":"comets_validate_observation")."\" />";
$objPresentations->line(array("<h4>".LangNewObservationTitle."</h4>","<input type=\"submit\" name=\"addobservation\" value=\"".($adapt?LangChangeObservationTitle:LangViewObservationButton1)."\" />&nbsp;"),"LR",array(50,50),30);
echo "<hr />";
$id = $objUtil->checkSessionKey('observedobject',$objUtil->checkGetKey('observedobject'));
$content="<select name=\"comet\" class=\"inputfield requiredField\">";
$content.="<option value=\"\">&nbsp;</option>";
if($adapt)
  $objID=$objCometObservation->getObjectId($obsid);
else
  $objID=$objUtil->checkSessionKey('observedobject',$objUtil->checkGetKey('observedobject',-1));
$catalogs=$objCometObject->getSortedObjects("name");
while(list($key, $value) = each($catalogs))
  $content.="<option value=\"".$value[0]."\"".(($objID==$objCometObject->getId($value[0]))?" selected=\"selected\" ":"").">".$value[0]."</option>";
$content.="</select>";
$objPresentations->line(array(LangQueryObjectsField1."&nbsp;*",$content),"RL",array(20,80),30);
$content="<input type=\"text\"  class=\"inputfield requiredField\" maxlength=\"2\" size=\"2\" name=\"day\"  value=\"".($adapt?substr($objCometObservation->getDate($obsid),6,2):$objUtil->checkSessionKey('day'))."\" />";
$content.="&nbsp;&nbsp;";
$content.="<select name=\"month\" class=\"inputfield requiredField\">";
for($i= 1;$i<13;$i++)
	$content.="<option value=\"".$i."\"".($adapt?(substr($objCometObservation->getDate($obsid),4,2)==$i?" selected=\"selected\"":""):(($objUtil->checkSessionKey('month')==$i)?" selected=\"selected\"":"")).">".$GLOBALS['Month'.$i]."</option>";
$content.="</select>";
$content.="&nbsp;&nbsp;";
$content.="<input type=\"text\"  class=\"inputfield requiredField\" maxlength=\"4\" size=\"4\" name=\"year\" value=\"".($adapt?substr($objCometObservation->getDate($obsid),0,4):$objUtil->checkSessionKey('year'))."\" />";
$objPresentations->line(array(LangViewObservationField5."&nbsp;*",$content,LangViewObservationField10),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
if ($objObserver->getObserverProperty($loggedUser,'UT'))
  $content1=LangViewObservationField9."&nbsp;*";
else
  $content1=LangViewObservationField9lt . "&nbsp;*";
$content2="<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" size=\"2\" name=\"hours\" value=\"".($adapt?substr($objCometObservation->getTime($obsid),0,2):"")."\" />&nbsp;&nbsp;".
          "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"2\" size=\"2\" name=\"minutes\" value=\"".($adapt?substr($objCometObservation->getTime($obsid),2,2):"")."\" />";
$content3=LangViewObservationField11;
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangViewObservationField4;
$content2="<select name=\"site\" class=\"inputField\">";
$sites = $objLocation->getSortedLocationsList("name",$loggedUser);
if($adapt)
  $theLocation=$objCometObservation->getLocationId($obsid);
elseif(!($theLocation=$objUtil->checkSessionKey('location')))
  $theLocation=$objObserver->getObserverProperty($loggedUser,'stdlocation',0);
$content2.="<option value=\"\">&nbsp;</option>";
for($i=0;$i<count($sites);$i++)
  $content2.="<option ".(($theLocation==$sites[$i][0])?(" selected=\"selected\" "):"")." value=\"".$sites[$i][0]."\" >".$sites[$i][1]."</option>";
$content2.="</select>";
$content3="<a href=\"".$baseURL."index.php?indexAction=add_site\">" . LangChangeAccountField7Expl ."</a>";
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangViewObservationField3;
$content2="<select name=\"instrument\" class=\"inputField\">";
$instr = $objInstrument->getSortedInstrumentsList("name",$loggedUser);
$content2.="<option value=\"\">&nbsp;</option>";
while(list($key,$value)=each($instr)) // go through instrument array
{ $instrumentname = $value;
  if($adapt)
    $theInstrument=$objCometObservation->getInstrumentId($obsid);
  elseif(!($theInstrument=$objUtil->checkSessionKey('instrument')))
    $theInstrument=$objObserver->getObserverProperty($loggedUser,'stdtelescope');
  $content2.="<option ".(($key==$theInstrument)?" selected=\"selected\"":"")." value=\"".$key."\">".(($value=="Naked eye")?InstrumentsNakedEye:$value)."</option>";
}
$content2.="</select>";
$content3="<a href=\"".$baseURL."index.php?indexAction=add_instrument\">".LangChangeAccountField8Expl."</a>";
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$objPresentations->line(array(LangNewComet4,"<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"magnification\" size=\"3\" value=\"".($adapt?$objCometObservation->getMagnification($obsid):"")."\"/>",""),"RLL",array(20,40,40),30,array("fieldname","","fieldexplanation"));
$ICQMETHODS = new ICQMETHOD();
$methods = $ICQMETHODS->getIds();
$content1=LangNewComet5;
$content2="<select name=\"icq_method\" class=\"inputField\">";
$content2.="<option value=\"\">&nbsp;</option>";
while(list($key, $value) = each($methods))
  $content2.="<option value=\"".$value."\"".($adapt?($objCometObservation->getMethode($obsid)==$value?" selected=\"selected\" ":""):"").">".$value." - ".$ICQMETHODS->getDescription($value)."</option>";
$content2.="</select>";
$content3="<a href=\"http://cfa-www.harvard.edu/icq/ICQKeys.html\" rel=\"external\">".LangNewComet7."</a>";
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$ICQREFERENCEKEYS = new ICQREFERENCEKEY();
$methods = $ICQREFERENCEKEYS->getIds();
$content1=LangNewComet6;
$content2="<select name=\"icq_reference_key\" class=\"inputField\">";
$content2.="<option value=\"\">&nbsp;</option>"; 
while(list($key, $value) = each($methods))
  $content2.="<option value=\"$value\"".($adapt?($objCometObservation->getChart($obsid)==$value?" selected=\"selected\" ":""):"").">".$value." - ".$ICQREFERENCEKEYS->getDescription($value)."</option>";
$content2.="</select>";
$content3="<a href=\"http://cfa-www.harvard.edu/icq/ICQRec.html\" rel=\"external\">".LangNewComet7."</a>";
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangNewComet1;
$content2="<select name=\"smaller\" class=\"inputField\">";
$content2.="<option value=\"0\">&nbsp;</option>";
$content2.="<option value=\"1\"".($adapt&&$objCometObservation->getMagnitudeWeakerThan($obsid)?" selected=\"selected\" ":"").">". LangNewComet3 . "</option>";
$content2.="</select>";
$content2.="&nbsp;";
$content2.="<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"mag\" size=\"4\" value=\"".($adapt?$objCometObservation->getMagnitude($obsid):"")."\"/>";
$content2.="<input type=\"checkbox\" name=\"uncertain\" class=\"inputField\" ".($adapt&&$objCometObservation->getMagnitudeUncertain($obsid)?" checked=\"checked\" ":"")." />" . LangNewComet2;
$objPresentations->line(array($content1,$content2,""),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangNewComet8;
$content2="<select name=\"condensation\" class=\"inputField\">";
$content2.="<option value=\"\">&nbsp;</option>";
for($i=0;$i<=9;$i++)
  $content2.="<option value=\"".$i."\"".($adapt&&($objCometObservation->getDc($obsid)==$i)?" selected=\"selected\" ":"").">".$i."</option>";
$content2.="</select>";
$objPresentations->line(array($content1,$content2,""),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangNewComet9;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"coma\" size=\"3\" value=\"".($adapt?$objCometObservation->getComa($obsid):"")."\" />";
$content3=LangNewComet13;
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangNewComet10;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"tail_length\" size=\"3\" value=\"".($adapt?$objCometObservation->getTail($obsid):"")."\" />";
$content3=LangNewComet13;
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangNewComet11;
$content2="<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"position_angle\" size=\"3\" value=\"".($adapt?$objCometObservation->getPa($obsid):"")."\" />";
$content3=LangNewComet12;
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangViewObservationField12;
$content2="<input type=\"file\" name=\"drawing\" class=\"inputField\" />";
$content3="";
$objPresentations->line(array($content1,$content2,$content3),"RLL",array(20,50,30),30,array("fieldname","","fieldexplanation"));
$content1=LangViewObservationField8;
$content2="<textarea name=\"description\" class=\"description\" cols=\"1\" rows=\"1\" >".($adapt?$objCometObservation->getDescription($obsid):"")."</textarea>";
$objPresentations->line(array($content1,$content2),"RLL",array(20,80),130,array("fieldname",""));
echo "</form>";
echo "<hr />";
echo "</div>";
?>
