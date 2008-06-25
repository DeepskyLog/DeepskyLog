<?php

// view_instrument.php
// view information of an instrument 

session_start(); // start session

include_once "../lib/instruments.php"; // location table
include_once "../lib/util.php";
include_once "../lib/setup/language.php";

$util = new Util();
$util->checkUserInput();

$instruments = new Instruments; 

if(!$_GET['instrument']) // no instrument defined 
{
   header("Location: ../index.php");
}  

echo("<div id=\"main\">\n<h2>" . LangViewInstrumentTitle . "</h2><table width=\"490\">\n
<tr>\n
<td class=\"fieldname\">\n");

echo LangViewInstrumentField1;

echo("</td>\n<td>\n");

$name = $instruments->getName($_GET['instrument']);

if ($name == "Naked eye")
{
 echo InstrumentsNakedEye;
}
else
{
 echo($name);
}

echo("</td></tr>");

echo("<tr><td class=\"fieldname\">");

if ($instruments->getType($_GET['instrument']) != InstrumentNakedEye)
{
 echo LangViewInstrumentField2;

 echo("</td><td>");

 echo(round($instruments->getDiameter($_GET['instrument']), 0));

 print("&nbsp;mm</td></tr>");
}

if($instruments->getType($_GET['instrument']) != InstrumentBinoculars && $instruments->getType($_GET['instrument']) != InstrumentFinderscope  && $instruments->getType($_GET['instrument']) != InstrumentNakedEye)
{

   echo("<tr><td class=\"fieldname\">");

   echo LangViewInstrumentField3; 

   echo("</td><td>");

   echo(round($instruments->getFd($_GET['instrument']), 1));

   print("</td></tr>");

   print("<tr><td class=\"fieldname\">");

   echo LangViewInstrumentField4;

   echo("</td><td>");

   echo(round($instruments->getFocalLength($_GET['instrument']), 0));

   print("&nbsp;mm</td>
   </tr>");

}

$fixedMagnification = $instruments->getFixedMagnification($_GET['instrument']);

if ($fixedMagnification > 0)
{
  echo("<tr><td class=\"fieldname\">");
  echo LangAddInstrumentField6;
  echo("</td><td>$fixedMagnification");
  echo("</td></tr>");
}
echo("<tr><td class=\"fieldname\">");

if ($instruments->getType($_GET['instrument']) != InstrumentNakedEye)
{
 echo LangViewInstrumentField5;
}
echo("</td><td>");

if($instruments->getType($_GET['instrument']) == InstrumentBinoculars)
   {
   echo(InstrumentsBinoculars);
   }
if($instruments->getType($_GET['instrument']) == InstrumentFinderscope)
   {
   echo(InstrumentsFinderscope);
   }
if($instruments->getType($_GET['instrument']) == InstrumentReflector)
   {
   echo(InstrumentsReflector);
   }
if($instruments->getType($_GET['instrument']) == InstrumentRefractor)
   {
   echo(InstrumentsRefractor);
   }
if($instruments->getType($_GET['instrument']) == InstrumentRest)
   {
   echo(InstrumentsOther);
   }
if($instruments->getType($_GET['instrument']) == InstrumentCassegrain)
   {
   echo(InstrumentsCassegrain);
   }
if($instruments->getType($_GET['instrument']) == InstrumentSchmidtCassegrain)
   {
   echo(InstrumentsSchmidtCassegrain);
   }
if($instruments->getType($_GET['instrument']) == InstrumentKutter)
   {
   echo(InstrumentsKutter);
   }
if($instruments->getType($_GET['instrument']) == InstrumentMaksutov)
   {
   echo(InstrumentsMaksutov);
   }

print("</td>
   </tr>
   </table>");

print("</div></div></body></html>");

?>
