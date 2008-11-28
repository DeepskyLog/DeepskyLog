<?php
// new_site.php
// allows the user to add a new site

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

$eyeps = $objEyepiece->getSortedEyepieces($sort, $_SESSION['deepskylog_id']);

if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] == "name")
  {
    $eyeps = array_reverse($eyeps, true);
  }
  else
  {
    krsort($eyeps);
    reset($eyeps);
  }
    $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangOverviewEyepieceTitle."</h2>");

$link = $baseURL."index.php?indexAction=add_eyepiece&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;

list($min, $max) = $util->printListHeader($eyeps, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=name&amp;previous=$previous\">".LangViewEyepieceName."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=focalLength&amp;previous=$previous\">".LangViewEyepieceFocalLength."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=maxFocalLength&amp;previous=$previous\">".LangViewEyepieceMaxFocalLength."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=apparentFOV&amp;previous=$previous\">".LangViewEyepieceApparentFieldOfView."</a></td>";


echo "<td></td>";
echo "</tr>";

$count = 0;

if ($eyeps != null)
{
 while(list ($key, $value) = each($eyeps))
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

   $name = stripslashes($objEyepiece->getEyepieceName($value));
   $focalLength = $objEyepiece->getFocalLength($value);
   $apparentFOV = $objEyepiece->getApparentFOV($value);
   $maxFocalLength = $objEyepiece->getMaxFocalLength($value);
   if ($maxFocalLength == "-1")
	 {
		 $maxFocalLength = "-";
	 }

   print("<tr $type>
           <td><a href=\"".$baseURL."index.php?indexAction=adapt_eyepiece&amp;eyepiece=".urlencode($value)."\">$name</a></td>\n
           <td>$focalLength</td>\n
           <td>$maxFocalLength</td>\n
           <td>$apparentFOV</td>\n");
           echo("<td>");

           // check if there are no observations made with this eyepiece

           $queries = array("eyepiece" => $value, "observer" => $_SESSION['deepskylog_id']);
           $obs = $objObservation->getObservationFromQuery($queries, "", "1", "False");

// No eyepieces yet for comet observations!!
//           $queries = array("eyepiece" => $value);
//           $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");

           if(!sizeof($obs) > 0) // no observations from location yet
           {
              echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_eyepiece&amp;eyepieceid=" . urlencode($value) . "\">" . LangRemove . "</a>");
           }

           echo("</td>\n</tr>");

  }
 }
   $count++;
}
  echo "</table>";

  list($min, $max) = $util->printListHeader($eyeps, $link, $min, $step, "");

  echo "</div></div>";

echo("<h2>");
echo(LangAddEyepieceTitle); ?>

</h2>

<ol>
<li value="1">
<?php echo (LangAddEyepieceExisting);
?>

<?php echo("<table width=\"100%\">");
      echo("<tr>");
      echo("<td width=\"25%\">\n");
      echo("<form name=\"overviewform\">\n ");		
      echo("<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalogue\">\n");

  $eyeps = $objEyepiece->getSortedEyepieces('focalLength', "", true);

  while(list($key, $value) = each($eyeps))
  {
		  echo("<option value=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;eyepieceid=".urlencode($value).\">" . $objEyepiece->getEyepieceName($value) . "</option>\n");
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
<li value="2"><?php echo (LangAddSiteFieldManually); ?></li>
</ol>
 
<?php   echo "<form action=\"".$baseURL."index.php?indexAction=validate_eyepiece\" method=\"post\">"; ?>
   <table>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddEyepieceField1);
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="eyepiecename" size="30" value="<?php 
			 if(array_key_exists('eyepiecename',$_GET) && $_GET['eyepiecename'])
       {
			    echo stripslashes($_GET['eyepiecename']);
			 } 
			 if(array_key_exists('eyepieceid',$_GET) && $_GET['eyepieceid'])
       {
			    echo stripslashes($objEyepiece->getEyepieceName($_GET['eyepieceid']));
			 } 
			 ?>" /></td>
   <td class="explanation"><?php echo(LangAddEyepieceField1Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddEyepieceField2); 
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="5" name="focalLength" size="5" value="<?php 
			 if(array_key_exists('focalLength',$_GET) && $_GET['focalLength']) 
       {
			    echo ($_GET['focalLength']);
			 } 
			 if(array_key_exists('eyepieceid',$_GET) && $_GET['eyepieceid'])
       {
			    echo stripslashes($objEyepiece->getFocalLength($_GET['eyepieceid']));
			 } 
			 ?>" /></td>
   <td class="explanation"><?php echo(LangAddEyepieceField2Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddEyepieceField4); 
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="5" name="maxFocalLength" size="5" value="<?php 
			 $mfl = -1;
			 if(array_key_exists('maxFocalLength',$_GET) && $_GET['maxFocalLength']) 
       {
			    $mfl = $_GET['maxFocalLength'];
			 } 
			 if(array_key_exists('eyepieceid',$_GET) && $_GET['eyepieceid'])
       {
			    $mfl = stripslashes($objEyepiece->getMaxFocalLength($_GET['eyepieceid']));
			 } 
			 if ($mfl < 0) {
				$mfl = "";
			 }
			 print $mfl;
			 ?>" /></td>
   <td class="explanation"><?php echo(LangAddEyepieceField4Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname">
   <?php 
     echo(LangAddEyepieceField3);
   ?></td>
   <td><input type="text" class="inputfield" maxlength="5" name="apparentFOV" size="5" value="<?php
    	 if(array_key_exists('apparentFOV',$_GET) && $_GET['apparentFOV'])
  	   {
			  echo ($_GET['apparentFOV']);
			 }
			 if(array_key_exists('eyepieceid',$_GET) && $_GET['eyepieceid'])
       {
			    echo stripslashes($objEyepiece->getapparentFOV($_GET['eyepieceid']));
			 } 
     ?>" /></td>
   <td class="explanation"><?php echo(LangAddEyepieceField3Expl); ?></td>
   </tr>

   <tr>
   <td></td>
   <td><input type="submit" name="add" value="<?php echo (LangAddEyepieceButton); ?>" /></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>
</body>
</html>

