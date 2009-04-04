<?php // new_filter.php - allows the user to add a new filter
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
else
{
$sort=$objUtil->checkGetKey('sort','name');
$lns =$objLens->getSortedLenses($sort, $loggedUser);
echo "<div id=\"main\">";
if ($lns!=null)
{ $orig_previous=$objUtil->checkGetKey('previous','');
  if((isset($_GET['sort']))&&($orig_previous==$_GET['sort'])) // reverse sort when pushed twice
  { if ($_GET['sort'] == "name")
      $lns = array_reverse($lns, true);
    else
    { krsort($lns);
      reset($lns);
    }
    $previous = "";
  }
  else
    $previous = $sort;
  $link=$baseURL."index.php?indexAction=add_lens&amp;sort=".$sort."&amp;previous=".$orig_previous;
  echo "<h2>".LangOverviewLensTitle."</h2>";
  echo "<table width=\"100%\">";
  echo "<tr class=\"type3\">";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=add_lens&amp;sort=name&amp;previous=$previous\">".LangViewLensName."</a></td>";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=add_lens&amp;sort=factor&amp;previous=$previous\">".LangViewLensFactor."</a></td>";
  echo "<td width=\"50%\"></td>";
  echo "</tr>";
  $count = 0;
  while(list($key,$value)=each($lns))
  { $name = stripslashes($objLens->getLensPropertyFromId($value,'name'));
    $factor = $objLens->getLensPropertyFromId($value,'factor');
    echo "<tr class=\"type".(2-($count%2))."\">";
    echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_lens&amp;lens=".urlencode($value)."\">".$name."</a></td>";
    echo "<td>".$factor."</td>";
    echo "<td>";
    if(!($obsCnt=$objLens->getLensUsedFromId($value)))
      echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_lens&amp;lensid=".urlencode($value)."\">".LangRemove."</a>";
    else
      echo "<a href=\"".$baseURL."index.php?indexAction=result_selected_observations&amp;observer=".$loggedUser."&amp;lens=".$value."&amp;exactinstrumentlocation=true\">".$obsCnt.' '.LangGeneralObservations."</a>";
    echo "</td>";
    echo "</tr>";
    $count++;
  }
  echo "</table>";
  echo "<hr>";
}
echo "<h2>".LangAddLensTitle."</h2>";
echo "<ol>";
echo "<li value=\"1\">".LangAddLensExisting;
echo "<form name=\"overviewform\"> ";		
echo "<select onchange=\"location = this.options[this.selectedIndex].value;\" name=\"catalog\">";
$lns=$objLens->getSortedLenses('name');
while(list($key, $value) = each($lns))
  echo "<option value=\"".$baseURL."index.php?indexAction=add_lens&amp;lensid=".urlencode($value)."\">".$objLens->getLensPropertyFromId($value,'name')."</option>";
echo "</select>";
echo "</form>";
echo "</li>";
echo "</ol>";
echo "<p>".LangAddSiteFieldOr."</p>";
echo "<ol><li value=\"2\">".LangAddLensFieldManually."</li></ol>";
echo "<form action=\"".$baseURL."index.php\" method=\"post\">";
echo "<input type=\"hidden\" name=\"indexAction\" value=\"validate_lens\" />";
echo "<table>";
tableFieldnameFieldExplanation(LangAddLensField1,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"64\" name=\"lensname\" size=\"30\" value=\"".stripslashes($objUtil->checkGetKey('lensname','')).stripslashes($objLens->getLensPropertyFromId($objUtil->checkGetKey('lensid'),'name'))."\">",
                               LangAddLensField1Expl);
tableFieldnameFieldExplanation(LangAddLensField2,
                               "<input type=\"text\" class=\"inputfield requiredField\" maxlength=\"5\" name=\"factor\" size=\"5\" value=\"".stripslashes($objUtil->checkGetKey('factor','')).stripslashes($objLens->getLensPropertyFromId($objUtil->checkGetKey('lensid'),'factor'))."\" />",
                               LangAddLensField2Expl);
echo "</table>";
echo "<hr />";
echo "<input type=\"submit\" name=\"add\" value=\"".LangAddLensButton."\" />";
echo "</form>";
echo "</div>";
}
?>