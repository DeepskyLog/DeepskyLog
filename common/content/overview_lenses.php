<?php // overview_lenses.php - generates an overview of all lenses (admin only)
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif(!$_SESSION['admin']) throw new Exception(LangException001);
else
{
set_time_limit(60);
$sort=$objUtil->checkGetKey('sort','name');
if(!$min) $min=$objUtil->checkGetKey('min',0);
$lns=$objLens->getSortedLenses($sort,'%');
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
// the code below is very strange but works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
$step = 25;
$link = "".$baseURL."index.php?indexAction=view_lenses&amp;sort=" . $sort . "&amp;previous=" . $orig_previous;
echo "<div id=\"main\" style=\"position:relative\">";
echo "<div class=\"container\" style=\"height:40px;\">";
echo "<div class=\"pageTitle\">";
echo "<h6>".LangOverviewLensTitle."</h6>";
echo "</div>";
echo "<div class=\"pageListHeader\">";
list ($min, $max) = $objUtil->printNewListHeader2($lns, $link, $min, $step);
echo "</div>";
echo "</div>";
echo "<table width=\"100%\">";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_lenses&amp;sort=name&amp;previous=$previous\">".LangViewLensName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_lenses&amp;sort=type&amp;previous=$previous\">".LangViewLensFactor."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_lenses&amp;sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
echo "<td></td>";
echo "</tr>";
$count = 0;
while(list($key,$value)=each($lns))
{ if(($count>=$min)&&($count<$max))
  { $name=stripslashes($objLens->getLensPropertyFromId($value,'name'));
    $factor=$objLens->getLensPropertyFromId($value,'factor');
    $observer=$objLens->getLensPropertyFromId($value,'observer');
    if($value!="1")
    { echo "<tr class=\"type".(2-($count%2))."\">";
      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_lens&amp;lens=".urlencode($value)."\">".$name."</a></td>";
      echo "<td>";
		  echo $factor;
      echo "</td>";
      echo "<td>";
		  echo $observer;
      echo "</td>";
      echo "<td>";
      if(!($objLens->getLensUsedFromId($value)))
        echo("<a href=\"".$baseURL."index.php?indexAction=validate_delete_lens&amp;lensid=" . urlencode($value) . "\">" . LangRemove . "</a>");
      echo "</td>";
      echo "</tr>";
    }
  }
  $count++;
}
echo "</table>";
echo "</div>";
}
?>
