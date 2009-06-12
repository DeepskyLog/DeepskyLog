<?php // view_eyepiece.php - view information of an eyepiece 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($eyepieceid=$objUtil->checkGetKey('eyepiece'))) throw new Exception(LangException003);
else
{
$eyepieceproperties=$objEyepiece->getEyepiecePropertiesFromId($eyepieceid);
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".stripslashes($eyepieceproperties['name'])."</h4>"),"L",array(100),30);
echo "<hr />";
$objPresentations->line(array(LangViewEyepieceFocalLength,$eyepieceproperties['focalLength']),"RL",array(20,80),'',array('fieldname','fieldvalue'));
if($eyepieceproperties['maxFocalLength']>0) 
  $objPresentations->line(array(LangAddEyepieceField4,$eyepieceproperties['maxFocalLength']),"RL",array(20,80),'',array('fieldname','fieldvalue'));
$objPresentations->line(array(LangAddEyepieceField3,$eyepieceproperties['apparentFOV']),"RL",array(20,80),'',array('fieldname','fieldvalue'));
echo "<hr />";
echo "</div>";
}
?>
