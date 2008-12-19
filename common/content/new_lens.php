<?php
// new_filter.php
// allows the user to add a new filter

$sort=$objUtil->checkGetKey('sort','name');
if(!$min) $min=$objUtil->checkGetKey('min',0);
// the code below looks very strange but it works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
$lns = $objLens->getSortedLenses($sort, $_SESSION['deepskylog_id']);
if((isset($_GET['sort'])) && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
{ if ($_GET['sort'] == "name")
    $lns = array_reverse($lns, true);
  else
  { krsort($lns);
    reset($lns);
  }
    $previous = ""; // reset previous field to sort on
}
else
  $previous = $sort;
$step = 25;
echo "<div id=\"main\">";
echo "<h2>".LangOverviewLensTitle."</h2>";
$link = $baseURL."index.php?indexAction=add_lens&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;
list($min, $max) = $objUtil->printListHeader($lns, $link, $min, $step, "");
echo "<table>";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_lens&amp;sort=name&amp;previous=$previous\">".LangViewLensName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=add_lens&amp;sort=factor&amp;previous=$previous\">".LangViewLensFactor."</a></td>";
echo "<td></td>";
echo "</tr>";
$count = 0;
if ($lns != null)
{ while(list ($key, $value) = each($lns))
  { if($count >= $min && $count < $max) // selection
    { $name = stripslashes($objLens->getLensName($value));
      $factor = $objLens->getFactor($value);
      echo "<tr class=\"type".(2-($count%2))."\">";
      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_lens&amp;lens=".urlencode($value)."\">".$name."</a></td>";
      echo "<td>";
      echo $factor;
      echo "</td>";
      echo "<td>";
      $queries = array("lens" => $value, "observer" => $_SESSION['deepskylog_id']);
      $obs = $objObservation->getObservationFromQuery($queries, "D", "1", "False");
      // No filters yet for comet observations!!
      //           $queries = array("eyepiece" => $value);
      //           $comobs = $objCometObservation->getObservationFromQuery($queries, "", "1", "False");
      if(!sizeof($obs) > 0) // no observations from location yet
      { echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_lens&amp;lensid=" . urlencode($value) . "\">" . LangRemove . "</a>";
      }
      echo "</td>";
      echo "</tr>";
    }
    $count++;
  }
}
echo "</table>";
list($min, $max) = $objUtil->printListHeader($lns, $link, $min, $step, "");
echo "</div>";
echo("<h2>").LangAddLensTitle."</h2>";
echo "<ol>";
echo "<li value=\"1\">".LangAddLensExisting;
echo "<table width=\"100%\">";
echo "<tr>";
echo "<td width=\"25%\">";
echo "<form name=\"overviewform\"> ";		
echo "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
$lns=$objLens->getSortedLenses('name', "", true);
while(list($key, $value) = each($lns))
  echo "<option value=\"".$baseURL."index.php?indexAction=add_lens&amp;lensid=".urlencode($value)."\">".$objLens->getLensName($value)."</option>";
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
echo "<li value=\"2\">";
echo LangAddLensFieldManually;
echo "</li>";
echo "</ol>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
echo "<table>";
echo "<tr>";
echo "<td class=\"fieldname\"".LangAddLensField1."</td>";
echo "<td><input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"";
if(array_key_exists('lensname',$_GET) && $_GET['lensname'])
  echo stripslashes($_GET['lensname']);
if(array_key_exists('lensid',$_GET) && $_GET['lensid'])
  echo stripslashes($objLens->getLensName($_GET['lensid']));
echo "\" />";
echo "</td>";
echo "<td class=\"explanation\"".LangAddLensField1Expl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=\"fieldname\">".LangAddLensField2."</td>";
echo "<td>";
echo "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"";
if(array_key_exists('factor',$_GET) && $_GET['factor'])
  echo stripslashes($_GET['factor']);
if(array_key_exists('lensid',$_GET) && $_GET['lensid'])
  echo stripslashes($objLens->getFactor($_GET['lensid']));
echo "\" />";
echo "</td>";
echo "<td class=\"explanation\">".LangAddLensField2Expl."</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type=\"submit\" name=\"add\" value=\"".LangAddLensButton."\" /></td>";
echo "<td></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</div>";
?>
