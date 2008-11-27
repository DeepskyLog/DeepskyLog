<?php
// change_instrument.php
// form which allows the administrator to change an instrument 

echo "<div id=\"main\">";
echo "<h2>";
$name=$objInstrument->getInstrumentName($_GET['instrument']);
if ($name == "Naked eye")
  echo InstrumentsNakedEye;
else
  echo($name);
echo "</h2>";
echo "<form action=\"common/control/validate_instrument.php\" method=\"post\">";
echo "<table>";
echo "<tr>";
echo "<td class=\"fieldname\">";
echo LangAddInstrumentField1;
echo "</td>";
echo "<td>";
echo "<input value=\"".$name."\" type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"instrumentname\" size=\"30\" />";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td class=\"fieldname\">";

echo(LangAddInstrumentField2);

echo("</td>
   <td>
   <input value=\"");

echo(round($objInstrument->getDiameter($_GET['instrument']), 0));

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
 
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentReflector)
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

if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentRefractor)
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

if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentCassegrain)
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

if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentSchmidtCassegrain)
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

if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentKutter)
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

if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentMaksutov)
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

if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentBinoculars)
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

if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentFinderscope)
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
    
if($objInstrument->getInstrumentType($_GET['instrument']) == InstrumentsOther)
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

if(round($objInstrument->getInstrumentFocalLength($_GET['instrument']), 0) != "0")
{
echo(round($objInstrument->getInstrumentFocalLength($_GET['instrument']), 0));
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

if($objInstrument->getFixedMagnification($_GET['instrument']) != "0")
{
 echo($objInstrument->getFixedMagnification($_GET['instrument']));
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
