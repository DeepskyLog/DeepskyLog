<?php
// new_eyepiece.php
// allows the user to add a new eyepiece

$mfl = -1;
if(array_key_exists('maxFocalLength',$_GET) && $_GET['maxFocalLength']) 
  $mfl = $_GET['maxFocalLength'];
if(array_key_exists('eyepieceid',$_GET) && $_GET['eyepieceid'])
  $mfl = stripslashes($objEyepiece->getMaxFocalLength($_GET['eyepieceid']));
if($mfl<0)
  $mfl="";

$sort=$objUtil->checkGetKey('sort','name');
if(!$min) $min=$objUtil->checkGetKey('min',0);
// the code below looks very strange but it works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
$eyeps = $objEyepiece->getSortedEyepieces($sort, $_SESSION['deepskylog_id']);
if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{ if ($_GET['sort'] == "name")
    $eyeps = array_reverse($eyeps, true);
  else
  { krsort($eyeps);
    reset($eyeps);
  }
  $previous = ""; // reset previous field to sort on
}
else
  $previous = $sort;
$step = 25;
echo "<div id=\"main\">";
echo "<h2>".LangOverviewEyepieceTitle."</h2>";
$link = $baseURL."index.php?indexAction=add_eyepiece&amp;sort=".$sort."&amp;previous=".$orig_previous;
list($min, $max) = $objUtil->printNewListHeader($eyeps, $link, $min, $step, "");
echo "<table width=\"100%\">";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=name&amp;previous=$previous\">".LangViewEyepieceName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=focalLength&amp;previous=$previous\">".LangViewEyepieceFocalLength."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=maxFocalLength&amp;previous=$previous\">".LangViewEyepieceMaxFocalLength."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;sort=apparentFOV&amp;previous=$previous\">".LangViewEyepieceApparentFieldOfView."</a></td>";
echo "<td></td>";
echo "</tr>";
$count = 0;
if ($eyeps != null)
{ while(list ($key, $value) = each($eyeps))
  { $name = stripslashes($objEyepiece->getEyepieceName($value));
    $focalLength = $objEyepiece->getFocalLength($value);
    $apparentFOV = $objEyepiece->getApparentFOV($value);
    $maxFocalLength = $objEyepiece->getMaxFocalLength($value);
    if ($maxFocalLength == "-1")
	    $maxFocalLength = "-";
    echo "<tr class=\"type".(2-($count%2))."\">";
		echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_eyepiece&amp;eyepiece=".urlencode($value)."\">$name</a></td>";
		echo "<td>".$focalLength."</td>";
		echo "<td>".$maxFocalLength."</td>";
		echo "<td>".$apparentFOV."</td>";
		echo "<td>";
    $queries = array("eyepiece" => $value, "observer" => $_SESSION['deepskylog_id']);           // check if there are no observations made with this eyepiece
    $obs = $objObservation->getObservationFromQuery($queries, "D", "1");
		// No eyepieces yet for comet observations!!
		//           $queries = array("eyepiece" => $value);
		//           $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
    if(!sizeof($obs) > 0) // no observations from location yet
      echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_eyepiece&amp;eyepieceid=" . urlencode($value) . "\">" . LangRemove . "</a>");
    echo("</td>\n</tr>");
    $count++;
  }
}
echo "</table>";
list($min, $max) = $objUtil->printNewListHeader($eyeps, $link, $min, $step, "");
echo "</div>";
echo "<hr />";
echo "<h2>".LangAddEyepieceTitle."</h2>";
echo "<ol>";
echo "<li value=\"1\">";
echo LangAddEyepieceExisting;
echo "<table width=\"100%\">";
echo "<tr>";
echo "<td width=\"25%\">";
echo "<form name=\"overviewform\">";
echo "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
$eyeps = $objEyepiece->getSortedEyepieces('focalLength', "", true);
while(list($key, $value)=each($eyeps))
  echo "<option value=\"".$baseURL."index.php?indexAction=add_eyepiece&amp;eyepieceid=".urlencode($value)."\" >" . $objEyepiece->getEyepieceName($value) . "</option>";
echo "</select>";
echo "</form>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</li>";
echo "</ol>";
echo "<p>";
echo LangAddSiteFieldOr;
echo "</p>";
echo "<ol>";
echo "<li value=\"2\">".LangAddSiteFieldManually."</li>";
echo "</ol>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_eyepiece\" />";
echo "<table>";
tableFieldnameFieldExplanation(LangAddEyepieceField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"eyepiecename\" size=\"30\" value=\"".stripslashes($objUtil->checkGetKey('eyepiecename')).stripslashes($objEyepiece->getEyepieceName($objUtil->checkGetKey('eyepieceid')))."\" />",
                               LangAddEyepieceField1Expl);
tableFieldnameFieldExplanation(LangAddEyepieceField2,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"5\" name=\"focalLength\" size=\"5\" value=\"".$objUtil->checkGetKey('focalLength').stripslashes($objEyepiece->getFocalLength($objUtil->checkGetKey('eyepieceid')))."\" />",
                               LangAddEyepieceField2Expl);
tableFieldnameFieldExplanation(LangAddEyepieceField4,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"maxFocalLength\" size=\"5\" value=\"".$mfl."\" />",
                               LangAddEyepieceField4Expl);
tableFieldnameFieldExplanation(LangAddEyepieceField3,
                               "<input type=\"text\" class=\"inputfield\" maxlength=\"5\" name=\"apparentFOV\" size=\"5\" value=\"".$objUtil->checkGetKey('apparentFOV').stripslashes($objEyepiece->getapparentFOV($objUtil->checkGetKey('eyepieceid')))."\" />",
                               LangAddEyepieceField3Expl);
echo "</table>";
echo "<hr />";
echo "<input type=\"submit\" name=\"add\" value=\"".LangAddEyepieceButton."\" />";
echo "</form>";
echo "</div>";
?>
