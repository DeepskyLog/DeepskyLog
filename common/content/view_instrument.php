<?php
// view_instrument.php
// view information of an instrument 

if(!$objUtil->checkGetKey('instrument')) 
  throw("No instrument specified");  
if(!($name=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'name')))
  throw("Instrument not found");
  
if($name=="Naked eye")
  $name=InstrumentsNakedEye;
  
$fixedMagnification=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'fixedMagnification');
$instrumentType=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'type');
$instrumentFD=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'fd');
$instrumentDiameter=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'diameter');
$instrumentFocalLength=$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'diameter')*$objInstrument->getInstrumentPropertyFromId($_GET['instrument'],'fd');
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
