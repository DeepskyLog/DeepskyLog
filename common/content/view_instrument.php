<?php // view_instrument.php - view information of an instrument 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($instrumentid=$objUtil->checkGetKey('instrument'))) throw new Exception(LangException007b);
else
{
if($name=="Naked eye")
  $name=InstrumentsNakedEye;
$fixedMagnification=$objInstrument->getInstrumentPropertyFromId($instrumentid,'fixedMagnification');
$instrumentType=$objInstrument->getInstrumentPropertyFromId($instrumentid,'type');
$instrumentFD=$objInstrument->getInstrumentPropertyFromId($instrumentid,'fd');
$instrumentDiameter=$objInstrument->getInstrumentPropertyFromId($instrumentid,'diameter');
$instrumentFocalLength=$objInstrument->getInstrumentPropertyFromId($instrumentid,'diameter')*$objInstrument->getInstrumentPropertyFromId($instrumentid,'fd');
$instrumentEchoType=$objInstrument->getInstrumentEchoType($instrumentType);
echo "<div id=\"main\">";
echo "<h2>".$name."</h2>";
echo "<table>";
if($instrumentType!=InstrumentNakedEye)
  tableFieldnameField(LangViewInstrumentField2,round($instrumentDiameter, 0)."&nbsp;mm");
if(($instrumentType!=InstrumentBinoculars)
&& ($instrumentType!=InstrumentFinderscope)
&& ($instrumentType!=InstrumentNakedEye))
{ tableFieldnameField(LangViewInstrumentField3,sprintf("%.1f",round($instrumentFD, 1)));
  tableFieldnameField(LangViewInstrumentField4,round($instrumentFocalLength, 0)."&nbsp;mm");
}
if($fixedMagnification > 0)
  tableFieldnameField(LangAddInstrumentField6,$fixedMagnification);
if ($instrumentType!=InstrumentNakedEye)
  tableFieldnameField(LangViewInstrumentField5,$instrumentEchoType);
echo "</table>";
echo "</div>";
}
?>
