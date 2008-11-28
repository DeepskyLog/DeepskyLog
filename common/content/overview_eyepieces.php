<?php
// overview_eyepieces.php
// generates an overview of all eyepieces (admin only)

// sort

if(isset($_GET['sort']))
{
  $sort = $_GET['sort']; // field to sort on
}
else
{
  $sort = "name"; // standard sort on location name
}

$eyeps = $objEyepiece->getSortedEyepieces($sort);

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

echo("<div id=\"main\">\n<h2>".LangViewEyepieceTitle."</h2>");

$link = $baseURL."index.php?indexAction=view_eyepieces&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;

list($min, $max) = $util->printListHeader($eyeps, $link, $min, $step, "");

echo "<table>
      <tr class=\"type3\">
      <td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=name&amp;previous=$previous\">".LangViewEyepieceName."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=focalLength&amp;previous=$previous\">".LangViewEyepieceFocalLength."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=maxFocalLength&amp;previous=$previous\">".LangViewEyepieceMaxFocalLength."</a></td>
      <td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=apparentFOV&amp;previous=$previous\">".LangViewEyepieceApparentFieldOfView."</a></td>";

			echo "<td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
			echo "<td></td>";
			echo "</tr>";

$count = 0;

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
   $focalLength = stripslashes($objEyepiece->getFocalLength($value));
   $apparentFOV = $objEyepiece->getApparentFOV($value);
   $observer = $objEyepiece->getObserverFromEyepiece($value);
   $maxFocalLength = $objEyepiece->getMaxFocalLength($value);
   if ($maxFocalLength == "-1")
   {
     $maxFocalLength = "-";
   }

   if ($value != "1")
   {
    print("<tr $type>
           <td><a href=\"".$baseURL."index.php?indexAction=adapt_eyepiece&amp;eyepiece=".urlencode($value)."\">$name</a></td>\n
           <td>$focalLength</td>\n
           <td>$maxFocalLength</td>\n
           <td>$apparentFOV</td>\n
            <td>");
           echo ($observer);
           echo("</td>\n<td>");

           // check if there are no observations made with this eyepiece

           $queries = array("eyepiece" => $value);
           $obs = $objObservation->getObservationFromQuery($queries, "", "1", "False");

//           $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");

//           if(!sizeof($obs) > 0 && !sizeof($comobs) > 0) // no observations with eyepiece yet
           if(!sizeof($obs) > 0) // no observations with eyepiece yet
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

  echo "</div></div></body></html>";
?>
