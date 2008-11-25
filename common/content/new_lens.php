<?php

// new_filter.php
// allows the user to add a new filter

//include_once "../lib/observers.php";
//$observers = new observers;


include_once "../lib/lenses.php";
include_once "../lib/util.php";
include_once "../lib/cometobservations.php";
include_once "../lib/observations.php";

$lenses = new Lenses;
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

$lns = $lenses->getSortedLenses($sort, $_SESSION['deepskylog_id']);

if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{
  if ($_GET['sort'] == "name")
  {
    $lns = array_reverse($lns, true);
  }
  else
  {
    krsort($lns);
    reset($lns);
  }
    $previous = ""; // reset previous field to sort on
}
else
{
  $previous = $sort;
}

$step = 25;

echo("<div id=\"main\">\n<h2>".LangOverviewLensTitle."</h2>");

$link = $baseURL."common/indexCommon.php?indexAction=add_lens&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;

list($min, $max) = $util->printListHeader($lns, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"".$baseURL."common/indexCommon.php?indexAction=add_lens&amp;sort=name&amp;previous=$previous\">".LangViewLensName."</a></td>
      <td><a href=\"".$baseURL."common/indexCommon.php?indexAction=add_lens.&amp;sort=factor&amp;previous=$previous\">".LangViewLensFactor."</a></td>";


echo "<td></td>";
echo "</tr>";

$count = 0;

if ($lns != null)
{
 while(list ($key, $value) = each($lns))
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

   $name = stripslashes($lenses->getLensName($value));
   $factor = $lenses->getFactor($value);

   print("<tr $type>
           <td><a href=\"common/adapt_lens.php?lens=$value\">$name</a></td>\n
           <td>");
   echo $factor;
   print ("</td>\n");
   echo("<td>");

   // check if there are no observations made with this filter

   $queries = array("lens" => $value, "observer" => $_SESSION['deepskylog_id']);
   $obs = $observations->getObservationFromQuery($queries, "", "1", "False");

// No filters yet for comet observations!!
//           $queries = array("eyepiece" => $value);
//           $comobs = $cometobservations->getObservationFromQuery($queries, "", "1", "False");

    if(!sizeof($obs) > 0) // no observations from location yet
    {
      echo("<a href=\"common/control/validate_delete_lens.php?lensid=" . $value . "\">" . LangRemove . "</a>");
    }

    echo("</td>\n</tr>");
   }
 }
   $count++;
}
  echo "</table>";

  list($min, $max) = $util->printListHeader($lns, $link, $min, $step, "");

  echo "</div></div>";

echo("<h2>");
echo(LangAddLensTitle); ?>

</h2>

<ol>
<li value="1">
<?php echo (LangAddLensExisting);
?>

<?php echo("<table width=\"100%\">");
      echo("<tr>");
      echo("<td width=\"25%\">\n");
      echo("<form name=\"overviewform\">\n ");		
      echo("<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalogue\">\n");

  $lns = $lenses->getSortedLenses('name', "", true);
  while(list($key, $value) = each($lns))
  {
		  echo("<option value=\"".$baseURL."common/indexCommon.php?indexAction=add_lens&amp;lensid=$value\">" . $lenses->getLensName($value) . "</option>\n");
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
<li value="2"><?php echo (LangAddLensFieldManually); ?></li>
</ol>
 <?php
   echo "<form action=\"".$baseURL."common/indexCommon.php?indexAction=validate_lens\" method=\"post\">";
 ?>
   <table>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddLensField1);
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="lensname" size="30" value="<?php 
			 if(array_key_exists('lensname',$_GET) && $_GET['lensname'])
       {
			    echo stripslashes($_GET['lensname']);
			 } 
			 if(array_key_exists('lensid',$_GET) && $_GET['lensid'])
       {
			    echo stripslashes($lenses->getLensName($_GET['lensid']));
			 } 
			 ?>" /></td>
   <td class="explanation"><?php echo(LangAddLensField1Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangAddLensField2); ?></td>
   <td>
   <input type="text" class="inputfield" maxlength="5" name="factor" size="5" value="<?php 
			 if(array_key_exists('factor',$_GET) && $_GET['factor'])
       {
			    echo stripslashes($_GET['factor']);
			 } 
			 if(array_key_exists('lensid',$_GET) && $_GET['lensid'])
       {
			    echo stripslashes($lenses->getFactor($_GET['lensid']));
			 } 
			 ?>" />
   </td> 
   <td class="explanation"><?php echo(LangAddLensField2Expl); ?></td>
   </tr>

   <tr>
   <td></td>
   <td><input type="submit" name="add" value="<?php echo (LangAddLensButton); ?>" /></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>
</body>
</html>

