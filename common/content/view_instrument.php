<?php
// view_instrument.php
// view information of an instrument 

if(!$objUtil->checkGetKey('instrument')) 
  throw("No instrument specified");  
if(!($name=$objInstrument->getInstrumentName($_GET['instrument'])))
  throw("Instrument not found");
  
if($name=="Naked eye")
  $name=InstrumentsNakedEye;
  
$fixedMagnification=$objInstrument->getFixedMagnification($_GET['instrument']);
$instrumentType=$objInstrument->getInstrumentType($_GET['instrument']);
$instrumentFD=$objInstrument->getFd($_GET['instrument']);
$instrumentDiameter=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'diameter');
$instrumentFocalLength=$objInstrument->getInstrumentFocalLength($_GET['instrument']);
$instrumentEchoType=$objInstrument->getInstrumentEchoType($instrumentType);

echo '<div id=\"main\">';
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
?>
