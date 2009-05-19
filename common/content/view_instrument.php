<?php // view_instrument.php - view information of an instrument 
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!($instrumentid=$objUtil->checkGetKey('instrument'))) throw new Exception(LangException007b);
else
{
$name=$objInstrument->getInstrumentPropertyFromId($instrumentid,'name');
if($name=="Naked eye")
  $name=InstrumentsNakedEye;
$fixedMagnification=$objInstrument->getInstrumentPropertyFromId($instrumentid,'fixedMagnification');
$instrumentType=$objInstrument->getInstrumentPropertyFromId($instrumentid,'type');
$instrumentFD=$objInstrument->getInstrumentPropertyFromId($instrumentid,'fd');
$instrumentDiameter=$objInstrument->getInstrumentPropertyFromId($instrumentid,'diameter');
$instrumentFocalLength=$objInstrument->getInstrumentPropertyFromId($instrumentid,'diameter')*$objInstrument->getInstrumentPropertyFromId($instrumentid,'fd');
$instrumentEchoType=$objInstrument->getInstrumentEchoType($instrumentType);
echo "<div id=\"main\">";
$objPresentations->line(array("<h5>".$name."</h5>"),"L",array(100),50);
echo "<hr />";
if($instrumentType!=InstrumentNakedEye)
  $objPresentations->line(array(LangViewInstrumentField2,round($instrumentDiameter, 0)."&nbsp;mm"),"RL",array(20,80),'',array('fieldname','fieldvalue'));
if(($instrumentType!=InstrumentBinoculars)
&& ($instrumentType!=InstrumentFinderscope)
&& ($instrumentType!=InstrumentNakedEye))
{ $objPresentations->line(array(LangViewInstrumentField3,sprintf("%.1f",round($instrumentFD, 1))),"RL",array(20,80),'',array('fieldname','fieldvalue'));
  $objPresentations->line(array(LangViewInstrumentField4,round($instrumentFocalLength, 0)."&nbsp;mm"),"RL",array(20,80),'',array('fieldname','fieldvalue'));
}
if($fixedMagnification > 0)
  $objPresentations->line(array(LangAddInstrumentField6,$fixedMagnification),"RL",array(20,80),'',array('fieldname','fieldvalue'));
if ($instrumentType!=InstrumentNakedEye)
  $objPresentations->line(array(LangViewInstrumentField5,$instrumentEchoType),"RL",array(20,80),'',array('fieldname','fieldvalue'));
echo "<hr />";
echo "</div>";
}
?>
