<?php

// change_instrument.php
// form which allows the administrator to change an instrument 
// version 3.2: WDM 21/01/2008

include_once "../lib/instruments.php";
$instruments = new Instruments();

include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

echo("<div id=\"main\">\n<h2>");

$name = $instruments->getName($_GET['instrument']);

if ($name == "Naked eye")
{
 echo InstrumentsNakedEye;
}
else
{
 echo($name);
}

echo("</h2>");

echo("<form action=\"common/control/validate_instrument.php\" method=\"post\">
   <table>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddInstrumentField1);

echo("</td>
   <td><input value=\"");

echo $name;

echo("\" type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"instrumentname\" size=\"30\" /></td>
   </tr>");

echo("<tr>
   <td class=\"fieldname\">");

echo(LangAddInstrumentField2);

echo("</td>
   <td>
   <input value=\"");

echo(round($instruments->getDiameter($_GET['instrument']), 0));

echo("\" type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"diameter\" size=\"10\" />");

echo("<select name=\"diameterunits\"><option>inch</option><option selected=\"selected\">mm</option></select>
   </td> 
   </tr>");

echo("<tr>
   <td class=\"fieldname\">");

echo(LangAddInstrumentField5);

echo("</td>
   <td>
   <select name=\"type\">
   <option value=\"");

echo InstrumentReflector;
 
echo("\""); 
 
if($instruments->getType($_GET['instrument']) == InstrumentReflector)
{ 
  echo(" selected=\"selected\">");
} 
else  
{
  echo(">"); 
}
 
echo InstrumentsReflector;

echo("</option>
   <option value=\"");

echo InstrumentRefractor;

echo("\"");

if($instruments->getType($_GET['instrument']) == InstrumentRefractor)
{
  echo(" selected=\"selected\">");
}
else 
{   
  echo(">");
}

echo InstrumentsRefractor;

echo("</option>
   <option value=\"");

echo InstrumentCassegrain;

echo("\"");

if($instruments->getType($_GET['instrument']) == InstrumentCassegrain)
{
  echo(" selected=\"selected\">");
}
else 
{
  echo(">");
}

echo InstrumentsCassegrain;

echo("</option>
   <option value=\"");

echo InstrumentSchmidtCassegrain;

echo("\"");

if($instruments->getType($_GET['instrument']) == InstrumentSchmidtCassegrain)
{
  echo(" selected=\"selected\">");
}
else 
{
  echo(">");
}

echo InstrumentsSchmidtCassegrain;

echo("</option>
   <option value=\"");

echo InstrumentKutter;

echo("\"");

if($instruments->getType($_GET['instrument']) == InstrumentKutter)
{
  echo(" selected=\"selected\">");
}
else 
{
  echo(">");
}

echo InstrumentsKutter;

echo("</option>
   <option value=\"");

echo InstrumentMaksutov;

echo("\"");

if($instruments->getType($_GET['instrument']) == InstrumentMaksutov)
{
  echo(" selected=\"selected\">");
}
else 
{
  echo(">");
}

echo InstrumentsMaksutov;

echo("</option>
   <option value=\"");

echo InstrumentBinoculars;

echo("\"");

if($instruments->getType($_GET['instrument']) == InstrumentBinoculars)
{
  echo(" selected=\"selected\">");
}
else
{
  echo(">");
}

echo InstrumentsBinoculars;
echo("</option>
   <option value=\"");

echo InstrumentFinderscope;

echo("\"");

if($instruments->getType($_GET['instrument']) == InstrumentFinderscope)
{
  echo(" selected=\"selected\">");
}
else
{
  echo(">");
}

echo InstrumentsFinderscope;

echo("</option>
   <option value=\"");
 
echo InstrumentRest;
 
echo("\"");  
    
if($instruments->getType($_GET['instrument']) == InstrumentsOther)
{   
  echo(" selected=\"selected\">");
}   
else   
{   
  echo(">");
}

echo InstrumentsOther;

echo("</option>
   </select></td>
   </tr>
   <tr>
   <td>&nbsp;</td>
   <td>&nbsp;</td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddInstrumentField4);

echo("</td>
   <td><input value=\"");

if(round($instruments->getFocalLength($_GET['instrument']), 0) != "0")
{
echo(round($instruments->getFocalLength($_GET['instrument']), 0));
}

echo("\" type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"focallength\" size=\"10\" />
   <select name=\"focallengthunits\"><option>inch</option><option selected=\"selected\">mm</option></select>
   </td> 
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddInstrumentOr);

echo("</td>
   <td></td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddInstrumentField3);

echo("</td>
   <td><input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"fd\" size=\"10\" /></td>
   </tr>
   <tr>
   <td class=\"fieldname\">");

echo(LangAddInstrumentField6);

echo("</td>
   <td><input value=\"");

if($instruments->getFixedMagnification($_GET['instrument']) != "0")
{
 echo($instruments->getFixedMagnification($_GET['instrument']));
}

echo("\" type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"fixedMagnification\" size=\"10\" />
   </td> 
   </tr>

   <tr>
   <td>&nbsp;</td>
   <td>&nbsp;</td>
   </tr>
   <tr>
   <td></td>
   <td><input type=\"submit\" name=\"change\" value=\"");

echo(LangChangeInstrumentButton);

echo("\" /><input type=\"hidden\" name=\"id\" value=\"");

echo($_GET['instrument']);

echo("\"></input>
</td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>
</body></html>");

?>
