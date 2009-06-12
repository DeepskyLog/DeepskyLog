<?php // view_location.php - view information of location 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($locationid=$objUtil->checkGetKey('location'))) throw new Exception(LangException011b);
else
{
$name=stripslashes($objLocation->getLocationPropertyFromId($locationid,'name'));
$timezone = $objLocation->getLocationPropertyFromId($locationid,'timezone');
echo "<div id=\"main\">";
$objPresentations->line(array("<h4>".$name."</h4>"),"L",array(100),30);
echo "<hr />";
$objPresentations->line(array(LangViewLocationProvince,stripslashes($objLocation->getLocationPropertyFromId($locationid,'region'))),"RL",array(20,80),'',array('fieldname','fieldvalue'));
$objPresentations->line(array(LangViewLocationCountry,$objLocation->getLocationPropertyFromId($locationid,'country')),"RL",array(20,80),'',array('fieldname','fieldvalue'));
$objPresentations->line(array(LangViewLocationLongitude,$objPresentations->decToTrimmedString($objLocation->getLocationPropertyFromId($locationid,'longitude'))),"RL",array(20,80),'',array('fieldname','fieldvalue'));
$objPresentations->line(array(LangViewLocationLatitude,$objPresentations->decToTrimmedString($objLocation->getLocationPropertyFromId($locationid,'latitude'))),"RL",array(20,80),'',array('fieldname','fieldvalue'));
$objPresentations->line(array(LangAddSiteField6,$timezone),"RL",array(20,80),'',array('fieldname','fieldvalue'));
$lm = $objLocation->getLocationPropertyFromId($locationid,'limitingMagnitude');
$sb = $objLocation->getLocationPropertyFromId($locationid,'skyBackground');
if(($lm>-900)||($sb>-900))
{ if ($lm>-900)
    $sb=$objContrast->calculateSkyBackgroundFromLimitingMagnitude($lm);
  else 
    $lm=$objContrast->calculateLimitingMagnitudeFromSkyBackground($sb);
  $objPresentations->line(array(LangAddSiteField7,sprintf("%.1f", $lm)),"RL",array(20,80),'',array('fieldname','fieldvalue'));
  $objPresentations->line(array(LangAddSiteField8,sprintf("%.2f", $sb)),"RL",array(20,80),'',array('fieldname','fieldvalue'));
}
echo "<a href=\"http://maps.google.com/maps?ll=" . $objLocation->getLocationPropertyFromId($locationid,'latitude') . "," . $objLocation->getLocationPropertyFromId($locationid,'longitude') . "&amp;spn=4.884785,11.585083&amp;t=h&amp;hl=en\"><img class=\"account\" src=\"".$baseURL."common/content/map.php?lat=" . $objLocation->getLocationPropertyFromId($locationid,'latitude') . "&amp;long=" . $objLocation->getLocationPropertyFromId($locationid,'longitude') . "\" width=\"490\" height=\"245\" title=\"".LangGooglemaps."\" alt=\"\" /></a>";
echo "<hr />";
echo "</div>";
}
?>
