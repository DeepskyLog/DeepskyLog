<?php

// new_instrument.php
// form which allows the user to add a new instrument 
// version 3.2: WDM, 20/01/2008

include_once "lib/instruments.php";
include_once "lib/util.php";
include_once "lib/observers.php";
include_once "lib/cometobservations.php";

$instruments = new Instruments;
$util = new util;
$util->checkUserInput();
$observations = new observations;
$cometobservations = new CometObservations;
$observers = new observers;

// sort

if(isset($_GET['sort']))
{
  $sort = $_GET['sort']; // field to sort on
}
else
{
  $sort = "name"; // standard sort on location name
}

// minimum

if(isset($_GET['min']))
{
  $min = $_GET['min'];
}
else
{
  $min = 0;
}

// the code below looks very strange but it works

if((isset($_GET['previous'])))
{
  $orig_previous = $_GET['previous'];
}
else
{
  $orig_previous = "";
}

$insts = $instruments->getSortedInstruments($sort, $_SESSION['deepskylog_id']);

if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] == "name")
  {
    $insts = array_reverse($insts, true);
  }
  else
  {
    krsort($insts);
    reset($insts);
  }
    $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangOverviewInstrumentsTitle."</h2>");

$link = $baseURL."index.php?indexAction=add_instrument&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;

list($min, $max) = $util->printListHeader($insts, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=name&amp;previous=$previous\">".LangOverviewInstrumentsName."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=diameter&amp;previous=$previous\">".LangOverviewInstrumentsDiameter."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=fd&amp;previous=$previous\">".LangOverviewInstrumentsFD."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=fixedMagnification&amp;previous=$previous\">".LangOverviewInstrumentsFixedMagnification."</a></td>";

echo "<td><a href=\"".$baseURL."index.php?indexAction=add_instrument&amp;sort=type&amp;previous=$previous\">".LangOverviewInstrumentsType."</a></td>";
echo "<td>".LangChangeAccountField8."</td>";
echo "<td></td>";

echo "</tr>";
echo "<form action=\"".$baseURL."index.php?indexAction=validate_instrument\" method=\"post\">";

$count = 0;

if ($insts != null)
{
 while(list ($key, $value) = each($insts))
 {
  if($count >= $min && $count < $max) // selection
  {
   if ($count % 2)
   {
    $typefield = "class=\"type1\"";
   }
   else
   {
    $typefield = "class=\"type2\"";
   }

   $name = $instruments->getInstrumentName($value);
   $diameter = round($instruments->getDiameter($value), 0);
   $fd = round($instruments->getFd($value), 1);
   if ($fd == "0")
   {
    $fd = "-";
   }
   $type = $instruments->getInstrumentType($value);
   $fixedMagnification = $instruments->getFixedMagnification($value);

   if ($name == "Naked eye")
   {
    print("<tr $typefield>
           <td><a href=\"".$baseURL."index.php?indexAction=detail_instrument&amp;instrument=".urlencode($value)."\">".InstrumentsNakedEye."</a></td>\n
           <td>$diameter</td>\n
           <td>$fd </td>\n
           <td>$fixedMagnification</td>
           <td>");
   }
   else
   {
    print("<tr $typefield>
           <td><a href=\"".$baseURL."index.php?indexAction=adapt_instrument&amp;instrument=".urlencode($value)."\">".$name."</a></td>\n
           <td>$diameter</td>\n
           <td>$fd</td>\n");
    echo("<td>\n");
    if ($fixedMagnification > 0)
    {
      echo($fixedMagnification);
    }
    else
    {
      echo("-");
    }

    echo("<td>");
   }

   if($type == InstrumentReflector) {echo(InstrumentsReflector);}
   if($type == InstrumentFinderscope) {echo(InstrumentsFinderscope);}
   if($type == InstrumentRefractor) {echo(InstrumentsRefractor);}
   if($type == InstrumentRest) {echo(InstrumentsOther);}
   if($type == InstrumentBinoculars) {echo(InstrumentsBinoculars);}
   if($type == InstrumentCassegrain) {echo(InstrumentsCassegrain);}
   if($type == InstrumentSchmidtCassegrain) {echo(InstrumentsSchmidtCassegrain);}
   if($type == InstrumentKutter) {echo(InstrumentsKutter);}
   if($type == InstrumentMaksutov) {echo(InstrumentsMaksutov);}

   echo("</td>\n<td>\n");
		// Radio button for the standard instrument

	 if ($value == $observers->getStandardTelescope($_SESSION['deepskylog_id']))
	 {
			echo("<input type=\"radio\" name=\"stdtelescope\" value=\"". $value ."\" checked>&nbsp;<br>");
	 } else {
			echo("<input type=\"radio\" name=\"stdtelescope\" value=\"". $value ."\">&nbsp;<br>");
	 }
   echo("</td>\n<td>\n");
	
   $queries = array("instrument" => $value, "observer" => $_SESSION['deepskylog_id']);
   $obs = $observations->getObservationFromQuery($queries, "", "1", "False", "D", "1");
   $obscom = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

   if(!sizeof($obs) > 0 && !sizeof($obscom) > 0) // no observations with instrument yet
   {
      echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_instrument&amp;instrumentid=" . urlencode($value) . "\">" . LangRemove . "</a>");
   }
   echo("</td>\n</tr>");
  }
 }
 $count++;
}
  echo "</table>";
  echo("<input type=\"hidden\" name=\"adaption\" value=\"1\">");
  echo("<input type=\"submit\" name=\"adapt\" value=\"" . LangAddInstrumentStdTelescope . "\" />");
	echo "</form>";

  list($min, $max) = $util->printListHeader($insts, $link, $min, $step, "");

  echo "</div></div>";

echo("<h2>");
echo(LangAddInstrumentTitle); ?>

</h2>

<ol>
<li value="1">
<?php echo (LangAddInstrumentExisting);
?>

<?php echo("<table width=\"100%\">");
      echo("<tr>");
      echo("<td width=\"25%\">\n");
      echo("<form name=\"overviewform\">\n ");		
      echo("<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalogue\">\n");

	echo("<option selected value=\"".$baseURL."index.php?indexAction=add_instrument\"> &nbsp; </option>\n");
  $insts = $instruments->getSortedInstruments('name', "", true);
  while(list($key, $value) = each($insts))
  {
	  echo("<option value=\"".$baseURL."index.php?indexAction=add_instrument&amp;instrumentid=".urlencode($value)."\">" . $instruments->getInstrumentName($value) . "</option>\n");
  }
  echo("</select>\n");
  echo("</form>");
  echo("</td>");
  echo("</tr>");
  echo("</table>");
?>
</li>
</ol>
<p><?php echo (LangAddSiteFieldOr); ?></p>
<ol>
<li value="2"><?php echo (LangAddInstrumentManually); ?></li>
</ol>
 
<?php   echo"<form action=\"".$baseURL."index.php?indexAction=validate_instrument\" method=\"post\">"; ?>
   <table>
   <tr>
   <td class="fieldname"><?php echo(LangAddInstrumentField1); ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="instrumentname" size="30"  value="<?php
			 if(array_key_exists('instrumentname',$_GET) && $_GET['instrumentname'])
       {
			    echo stripslashes($_GET['instrumentname']);
			 } 
			 if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
       {
			    echo stripslashes($instruments->getInstrumentName($_GET['instrumentid']));
			 } 
			 ?>" /></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangAddInstrumentField2); ?></td>
   <td>
   <input type="text" class="inputfield" maxlength="64" name="diameter" size="10" value="<?php
			 if(array_key_exists('diameter',$_GET) && $_GET['diameter'])
       {
			    echo stripslashes($_GET['diameter']);
			 } 
			 if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
       {
			    echo stripslashes($instruments->getDiameter($_GET['instrumentid']));
			 } 
			 ?>" />
   <select name="diameterunits"><option>inch</option><option selected="selected">mm</option></select>
   </td> 
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangAddInstrumentField5); ?></td>
   <td>
   <select name="type">
   <?php if(array_key_exists('type',$_GET) && $_GET['type'])
   {
      $type = $_GET['type'];
	 }
	 if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
   {
      $type = $instruments->getInstrumentType($_GET['instrumentid']);
	 } 
   ?>
   <option <?php 
   if ($type == InstrumentReflector)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentReflector;?>"><?php echo InstrumentsReflector; ?></option>
   <option <?php 
   if ($type == InstrumentRefractor)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentRefractor; ?>"><?php echo InstrumentsRefractor; ?></option>
   <option <?php 
   if ($type == InstrumentCassegrain)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentCassegrain; ?>"><?php echo InstrumentsCassegrain; ?></option>
   <option <?php 
   if ($type == InstrumentSchmidtCassegrain)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentSchmidtCassegrain; ?>"><?php echo InstrumentsSchmidtCassegrain; ?></option>
   <option <?php 
   if ($type == InstrumentKutter)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentKutter; ?>"><?php echo InstrumentsKutter; ?></option>
   <option <?php 
   if ($type == InstrumentMaksutov)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentMaksutov; ?>"><?php echo InstrumentsMaksutov; ?></option>
   <option <?php 
   if ($type == InstrumentBinoculars)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentBinoculars; ?>"><?php echo InstrumentsBinoculars; ?></option>
   <option <?php 
   if ($type == InstrumentFinderscope)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentFinderscope; ?>"><?php echo InstrumentsFinderscope; ?></option>
   <option <?php 
   if ($type == InstrumentOther)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo InstrumentRest; ?>"><?php echo InstrumentsOther; ?></option>
   </select></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangAddInstrumentField4); ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="focallength" size="10"  value="<?php
			 if(array_key_exists('focallength',$_GET) && $_GET['focallength'])
       {
			    echo stripslashes($_GET['focallength']);
			 } 
			 if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
       {
			    echo stripslashes($instruments->getInstrumentFocalLength($_GET['instrumentid']));
			 } 
			 ?>" />
   <select name="focallengthunits"><option>inch</option><option selected="selected">mm</option></select>
   </td> 
   </tr>

   <tr>
   <td class="fieldname"><?php echo(LangAddInstrumentOr); ?></td>
   <td></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangAddInstrumentField3); ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="fd" size="10" value="<?php
			 if(array_key_exists('fd',$_GET) && $_GET['fd'])
       {
			    echo stripslashes($_GET['fd']);
			 } 
			 if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
       {
			    echo stripslashes($instruments->getFd($_GET['instrumentid']));
			 } 
			 ?>" />
   </td>
   </tr>

   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddInstrumentField6); 
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="5" name="fixedMagnification" size="5" value="<?php 
			 if(array_key_exists('fixedMagnification',$_GET) && $_GET['fixedMagnification']) 
       {
			    echo ($_GET['fixedMagnification']);
			 } 
			 if(array_key_exists('instrumentid',$_GET) && $_GET['instrumentid'])
       {
			    echo stripslashes($instruments->getFixedMagnification($_GET['instrumentid']));
			 }
			 ?>" /></td>
   <td class="explanation"><?php echo(LangAddInstrumentField6Expl); ?></td>
   </tr>

   <tr>
   <td></td>
   <td><input type="submit" name="add" value="<?php echo(LangAddInstrumentAdd); ?>" /></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>
</body>
</html>
