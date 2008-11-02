<?php

// change_filter.php
// form which allows the administrator to change a filter
// version 3.2: WDM 21/01/2008

include_once "../lib/filters.php";
$filters = new Filters();

include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

echo("<div id=\"main\">\n<h2>");

$filters = new Filters();

echo stripslashes($filters->getFilterName($_GET['filter']));

echo("</h2>"); ?>
   <form action="common/control/validate_filter.php" method="post">
   <table>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddFilterField1);
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="filtername" size="30" value="<?php echo stripslashes($filters->getFilterName($_GET['filter']));?>" /></td>
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
   
   $type = $filters->getFilterType($_GET['filter']);
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
   
   $color = $filters->getColor($_GET['filter']);
?>
   <option value=""><?php echo "&nbsp;"; ?></option>
   <option <?php 
   if ($color == FilterColorLightRed)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorLightRed; ?>"><?php echo FiltersColorLightRed; ?></option>
         <option <?php 
   if ($color == FilterColorRed)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorRed; ?>"><?php echo FiltersColorRed; ?></option>
         <option <?php 
   if ($color == FilterColorDeepRed)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorDeepRed; ?>"><?php echo FiltersColorDeepRed; ?></option>
         <option <?php 
   if ($color == FilterColorOrange)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorOrange; ?>"><?php echo FiltersColorOrange; ?></option>
         <option <?php 
   if ($color == FilterColorLightYellow)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorLightYellow; ?>"><?php echo FiltersColorLightYellow; ?></option>
         <option <?php 
   if ($color == FilterColorDeepYellow)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorDeepYellow; ?>"><?php echo FiltersColorDeepYellow; ?></option>
         <option <?php 
   if ($color == FilterColorYellow)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorYellow; ?>"><?php echo FiltersColorYellow; ?></option>
         <option <?php 
   if ($color == FilterColorYellowGreen)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorYellowGreen; ?>"><?php echo FiltersColorYellowGreen; ?></option>
         <option <?php 
   if ($color == FilterColorLightGreen)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorLightGreen; ?>"><?php echo FiltersColorLightGreen; ?></option>
         <option <?php 
   if ($color == FilterColorGreen)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorGreen; ?>"><?php echo FiltersColorGreen; ?></option>
         <option <?php 
   if ($color == FilterColorMediumBlue)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorMediumBlue; ?>"><?php echo FiltersColorMediumBlue; ?></option>
         <option <?php 
   if ($color == FilterColorPaleBlue)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorPaleBlue; ?>"><?php echo FiltersColorPaleBlue; ?></option>
         <option <?php 
   if ($color == FilterColorBlue)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorBlue; ?>"><?php echo FiltersColorBlue; ?></option>
         <option <?php 
   if ($color == FilterColorDeepBlue)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorDeepBlue; ?>"><?php echo FiltersColorDeepBlue; ?></option>
         <option <?php 
   if ($color == FilterColorDeepViolet)
   {
		 echo "option selected=\"selected\" ";
   }
   ?> value="<?php echo FilterColorDeepViolet; ?>"><?php echo FiltersColorDeepViolet; ?></option>
   </select></td>
   </tr>

   <tr>
   <td class="fieldname"><?php echo(LangAddFilterField4); ?></td>
   <td>
   <input type="text" class="inputfield" maxlength="5" name="wratten" size="5" value="<?php 
	    echo stripslashes($filters->getWratten($_GET['filter']));
			 ?>" />
   </td> 
   </tr>

   <tr>
   <td class="fieldname"><?php echo(LangAddFilterField5); ?></td>
   <td>
   <input type="text" class="inputfield" maxlength="5" name="schott" size="5" value="<?php 
	    echo stripslashes($filters->getSchott($_GET['filter']));
			 ?>" />
   </td> 
   </tr>

   <tr>
   <td></td>
   <td><input type="submit" name="change" value="<?php echo (LangChangeFilterButton);

echo("\" /><input type=\"hidden\" name=\"id\" value=\"");

echo ($_GET['filter']);

echo("\"></input>"); ?></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>
</body></html>


