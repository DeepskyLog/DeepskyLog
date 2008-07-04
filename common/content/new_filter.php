<?php

// new_filter.php
// allows the user to add a new filter

//include_once "../lib/observers.php";
//$observers = new observers;

include_once "../lib/filters.php";
include_once "../lib/util.php";
include_once "../lib/cometobservations.php";
include_once "../lib/observations.php";

$filters = new Filters;
$util = new util;
$util->checkUserInput();
$observations = new observations;
$cometobservations = new CometObservations;

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

$filts = $filters->getSortedFilters($sort, $_SESSION['deepskylog_id']);

if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] == "name")
  {
    $filts = array_reverse($filts, true);
  }
  else
  {
    krsort($filts);
    reset($filts);
  }
    $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangOverviewFilterTitle."</h2>");

$link = "common/add_filter.php?sort=" . $sort . "&amp;previous=" . $orig_previous;

list($min, $max) = $util->printListHeader($filts, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"common/add_filter.php?sort=name&amp;previous=$previous\">".LangViewFilterName."</a></td>
      <td><a href=\"common/add_filter.php?sort=type&amp;previous=$previous\">".LangViewFilterType."</a></td>
      <td><a href=\"common/add_filter.php?sort=color&amp;previous=$previous\">".LangViewFilterColor."</a></td>
      <td><a href=\"common/add_filter.php?sort=wratten&amp;previous=$previous\">".LangViewFilterWratten."</a></td>
      <td><a href=\"common/add_filter.php?sort=schott&amp;previous=$previous\">".LangViewFilterSchott."</a></td>";


echo "<td></td>";
echo "</tr>";

$count = 0;

if ($filts != null)
{
 while(list ($key, $value) = each($filts))
 {
  if($count >= $min && $count < $max) // selection
  {
   if ($count % 2)
   {
    $type = "class=\"type1\"";
   }
   else
   {
    $type = "class=\"type2\"";
   }

   $name = stripslashes($filters->getFilterName($value));
   $type = $filters->getType($value);
   $color = $filters->getColor($value);
   $wratten = $filters->getWratten($value);
   $schott = $filters->getSchott($value);

   print("<tr $type>
           <td><a href=\"common/adapt_filter.php?filter=$value\">$name</a></td>\n
           <td>");
   if($type == FilterOther) {echo(FiltersOther);}
   if($type == FilterBroadBand) {echo(FiltersBroadBand);}
   if($type == FilterNarrowBand) {echo(FiltersNarrowBand);}
   if($type == FilterOIII) {echo(FiltersOIII);}
   if($type == FilterHBeta) {echo(FiltersHBeta);}
   if($type == FilterHAlpha) {echo(FiltersHAlpha);}
   if($type == FilterColor) {echo(FiltersColor);}
   if($type == FilterNeutral) {echo(FiltersNeutral);}
   if($type == FilterCorrective) {echo(FiltersCorrective);}
 
   print("</td>\n
           <td>");
   if ($color == 0)
   {
     echo ("-");
   }
   else
   {
     if($color == FilterColorLightRed) {echo(FiltersColorLightRed);}
     if($color == FilterColorRed) {echo(FiltersColorRed);}
     if($color == FilterColorDeepRed) {echo(FiltersColorDeepRed);}
     if($color == FilterColorOrange) {echo(FiltersColorOrange);}
     if($color == FilterColorLightYellow) {echo(FiltersColorLightYellow);}
     if($color == FilterColorDeepYellow) {echo(FiltersColorDeepYellow);}
     if($color == FilterColorYellow) {echo(FiltersColorYellow);}
     if($color == FilterColorYellowGreen) {echo(FiltersColorYellowGreen);}
     if($color == FilterColorLightGreen) {echo(FiltersColorLightGreen);}
     if($color == FilterColorGreen) {echo(FiltersColorGreen);}
     if($color == FilterColorMediumBlue) {echo(FiltersColorMediumBlue);}
     if($color == FilterColorPaleBlue) {echo(FiltersColorPaleBlue);}
     if($color == FilterColorBlue) {echo(FiltersColorBlue);}
     if($color == FilterColorDeepBlue) {echo(FiltersColorDeepBlue);}
     if($color == FilterColorDeepViolet) {echo(FiltersColorDeepViolet);}
   }
   print("</td>\n
           <td>");
   if ($wratten == "")
   {
     echo "-";
   }
   else
   {
     echo $wratten;
   }
   print("</td>\n
           <td>");
   if ($schott == "")
   {
     echo "-";
   }
   else
   {
     echo $schott;
   }
   print ("</td>\n");
           echo("<td>");

           // check if there are no observations made with this filter

           $queries = array("filter" => $value, "observer" => $_SESSION['deepskylog_id']);
           $obs = $observations->getObservationFromQuery($queries, "", "1", "False");

// No filters yet for comet observations!!
//           $queries = array("eyepiece" => $value);
//           $comobs = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

           if(!sizeof($obs) > 0) // no observations from location yet
           {
              echo("<a href=\"common/control/validate_delete_filter.php?filterid=" . $value . "\">" . LangRemove . "</a>");
           }

           echo("</td>\n</tr>");
   }
 }
   $count++;
}
  echo "</table>";

  list($min, $max) = $util->printListHeader($filts, $link, $min, $step, "");

  echo "</div></div>";

echo("<h2>");
echo(LangAddFilterTitle); ?>

</h2>

<ol>
<li value="1">
<?php echo (LangAddFilterExisting);
?>

<?php echo("<table width=\"100%\">");
      echo("<tr>");
      echo("<td width=\"25%\">\n");
      echo("<form name=\"overviewform\">\n ");		
      echo("<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalogue\">\n");

  $filts = $filters->getSortedFilters('name', "", true);
  while(list($key, $value) = each($filts))
  {
	  echo("<option value=\"" . $baseURL . "common/add_filter.php?filterid=$value\">" . $filters->getFilterName($value) . "</option>\n");
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
<li value="2"><?php echo (LangAddFilterFieldManually); ?></li>
</ol>
 
   <form action="common/control/validate_filter.php" method="post">
   <table>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddFilterField1);
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="filtername" size="30" value="<?php 
			 if(array_key_exists('filtername',$_GET) && $_GET['filtername'])
       {
			    echo stripslashes($_GET['filtername']);
			 } 
			 if(array_key_exists('filterid',$_GET) && $_GET['filterid'])
       {
			    echo stripslashes($filters->getFilterName($_GET['filterid']));
			 } 
			 ?>" /></td>
   <td class="explanation"><?php echo(LangAddFilterField1Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddFilterField2); 
			 ?></td>
   <td>

<?php
	 echo("<select name=\"type\">");
   
   if(array_key_exists('type',$_GET) && $_GET['type'])
   {
      $type = $_GET['type'];
	 }
	 if(array_key_exists('filterid',$_GET) && $_GET['filterid'])
   {
      $type = $filters->getType($_GET['filterid']);
	 } 
   ?>

   <option <?php 
   if ($type == FilterOther)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterOther;?>"><?php echo FiltersOther; ?></option>
         <option <?php 
   if ($type == FilterBroadBand)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterBroadBand; ?>"><?php echo FiltersBroadBand; ?></option>
         <option <?php 
   if ($type == FilterNarrowBand)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterNarrowBand; ?>"><?php echo FiltersNarrowBand; ?></option>
         <option <?php 
   if ($type == FilterOIII)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterOIII; ?>"><?php echo FiltersOIII; ?></option>
         <option <?php 
   if ($type == FilterHBeta)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterHBeta; ?>"><?php echo FiltersHBeta; ?></option>
         <option <?php 
   if ($type == FilterHAlpha)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterHAlpha; ?>"><?php echo FiltersHAlpha; ?></option>
         <option <?php 
   if ($type == FilterColor)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColor; ?>"><?php echo FiltersColor; ?></option>
         <option <?php 
   if ($type == FilterNeutral)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterNeutral; ?>"><?php echo FiltersNeutral; ?></option>
         <option <?php 
   if ($type == FilterCorrective)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterCorrective; ?>"><?php echo FiltersCorrective; ?></option>

   <option 

   </select></td>
   </tr>

   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddFilterField3); 
			 ?></td>
   <td>

<?php
	 echo("<select name=\"color\">");
   
   if(array_key_exists('color',$_GET) && $_GET['color'])
   {
      $color = $_GET['color'];
	 }
	 if(array_key_exists('filterid',$_GET) && $_GET['filterid'])
   {
      $color = $filters->getColor($_GET['filterid']);
	 } 
   ?>

   <option value=""><?php echo "&nbsp;"; ?></option>
   <option <?php 
   if ($color == FilterColorLightRed)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorLightRed; ?>"><?php echo FiltersColorLightRed; ?></option>
         <option <?php 
   if ($color == FilterColorRed)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorRed; ?>"><?php echo FiltersColorRed; ?></option>
         <option <?php 
   if ($color == FilterColorDeepRed)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorDeepRed; ?>"><?php echo FiltersColorDeepRed; ?></option>
         <option <?php 
   if ($color == FilterColorOrange)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorOrange; ?>"><?php echo FiltersColorOrange; ?></option>
         <option <?php 
   if ($color == FilterColorLightYellow)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorLightYellow; ?>"><?php echo FiltersColorLightYellow; ?></option>
         <option <?php 
   if ($color == FilterColorDeepYellow)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorDeepYellow; ?>"><?php echo FiltersColorDeepYellow; ?></option>
         <option <?php 
   if ($color == FilterColorYellow)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorYellow; ?>"><?php echo FiltersColorYellow; ?></option>
         <option <?php 
   if ($color == FilterColorYellowGreen)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorYellowGreen; ?>"><?php echo FiltersColorYellowGreen; ?></option>
         <option <?php 
   if ($color == FilterColorLightGreen)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorLightGreen; ?>"><?php echo FiltersColorLightGreen; ?></option>
         <option <?php 
   if ($color == FilterColorGreen)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorGreen; ?>"><?php echo FiltersColorGreen; ?></option>
         <option <?php 
   if ($color == FilterColorMediumBlue)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorMediumBlue; ?>"><?php echo FiltersColorMediumBlue; ?></option>
         <option <?php 
   if ($color == FilterColorPaleBlue)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorPaleBlue; ?>"><?php echo FiltersColorPaleBlue; ?></option>
         <option <?php 
   if ($color == FilterColorBlue)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorBlue; ?>"><?php echo FiltersColorBlue; ?></option>
         <option <?php 
   if ($color == FilterColorDeepBlue)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorDeepBlue; ?>"><?php echo FiltersColorDeepBlue; ?></option>
         <option <?php 
   if ($color == FilterColorDeepViolet)
   {
		 echo " option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorDeepViolet; ?>"><?php echo FiltersColorDeepViolet; ?></option>
   </select></td>
   </tr>

   <tr>
   <td class="fieldname"><?php echo(LangAddFilterField4); ?></td>
   <td>
   <input type="text" class="inputfield" maxlength="5" name="wratten" size="5" value="<?php 
			 if(array_key_exists('wratten',$_GET) && $_GET['wratten'])
       {
			    echo stripslashes($_GET['wratten']);
			 } 
			 if(array_key_exists('filterid',$_GET) && $_GET['filterid'])
       {
			    echo stripslashes($filters->getWratten($_GET['filterid']));
			 } 
			 ?>" />
   </td> 
   </tr>

   <tr>
   <td class="fieldname"><?php echo(LangAddFilterField5); ?></td>
   <td>
   <input type="text" class="inputfield" maxlength="5" name="schott" size="5" value="<?php 
			 if(array_key_exists('schott',$_GET) && $_GET['schott'])
       {
			    echo stripslashes($_GET['schott']);
			 } 
			 if(array_key_exists('filterid',$_GET) && $_GET['filterid'])
       {
			    echo stripslashes($filters->getSchott($_GET['filterid']));
			 } 
			 ?>" />
   </td> 
   </tr>

   <tr>
   <td></td>
   <td><input type="submit" name="add" value="<?php echo (LangAddFilterButton); ?>" /></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>
</body>
</html>

