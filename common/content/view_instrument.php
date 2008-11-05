<?php
// view_instrument.php
// view information of an instrument 

if(!$_GET['instrument']) // no instrument defined 
  throw(header("Location: ../index.php"));  

echo '<div id=\"main\">';
echo "<h2>" . LangViewInstrumentTitle . "</h2>";
echo "<table width=\"490\">";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangViewInstrumentField1;
echo "</td>";
echo "<td>";
$name = $objInstrument->getInstrumentName($_GET['instrument']);
if ($name == "Naked eye")
 echo InstrumentsNakedEye;
else
 echo($name);
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">";
if($objInstrument->getInstrumentType($_GET['instrument']) != InstrumentNakedEye)
{ echo LangViewInstrumentField2;
  echo "</td>";
  echo "<td>";
  echo round($objInstrument->getDiameter($_GET['instrument']), 0);
  echo "&nbsp;mm</td>";
  echo "</tr>";
}
if(($objInstrument->getInstrumentType($_GET['instrument']) != InstrumentBinoculars)
&& ($objInstrument->getInstrumentType($_GET['instrument']) != InstrumentFinderscope)
&& ($objInstrument->getInstrumentType($_GET['instrument']) != InstrumentNakedEye))
{ echo "<tr>";
  echo "<td class=\"fieldname\">";
  echo LangViewInstrumentField3; 
  echo "</td>";
	echo "<td>";
  echo round($objInstrument->getFd($_GET['instrument']), 1);
  echo "</td>";
	echo "</tr>";
  echo "<tr>";
	echo '<td class=\"fieldname\">';
  echo LangViewInstrumentField4;
  echo '</td>';
	echo '<td>';
  echo round($objInstrument->getInstrumentFocalLength($_GET['instrument']), 0);
  echo "&nbsp;mm";
	echo '</td>';
  echo '</tr>';
}
$fixedMagnification = $objInstrument->getFixedMagnification($_GET['instrument']);
if ($fixedMagnification > 0)
{ echo '<tr>';
  echo '<td class=\"fieldname\">';
  echo LangAddInstrumentField6;
  echo '</td>';
	echo '<td>';
	echo $fixedMagnification;
  echo '</td>';
	echo '</tr>';
}
echo '<tr>';
echo '<td class=\"fieldname\">';
if ($objInstrument->getInstrumentType($_GET['instrument']) != InstrumentNakedEye)
 echo LangViewInstrumentField5;
echo '</td>';
echo '<td>';
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentBinoculars)
  echo(InstrumentsBinoculars);
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentFinderscope)
  echo(InstrumentsFinderscope);
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentReflector)
  echo(InstrumentsReflector);
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentRefractor)
  echo(InstrumentsRefractor);
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentRest)
  echo(InstrumentsOther);
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentCassegrain)
   echo(InstrumentsCassegrain);
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentSchmidtCassegrain)
   echo(InstrumentsSchmidtCassegrain);
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentKutter)
   echo(InstrumentsKutter);
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentMaksutov)
  echo(InstrumentsMaksutov);
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';

?>
