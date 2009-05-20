<?php  // overview_eyepieces.php - generates an overview of all eyepieces (admin only)
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
elseif(!$loggedUser) throw new Exception(LangException002);
elseif(!$_SESSION['admin']) throw new Exception(LangException001);
else
{
set_time_limit(60);
// sort
if(isset($_GET['sort']))
 $sort=$_GET['sort'];
else
 $sort="name";
$eyeps=$objEyepiece->getSortedEyepieces($sort,'%');
// the code below looks very strange but it works
if((isset($_GET['previous'])))
  $orig_previous = $_GET['previous'];
else
  $orig_previous = "";
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
$link = $baseURL."index.php?indexAction=view_eyepieces&amp;sort=".$sort."&amp;previous=".$orig_previous;
list ($min, $max,$content) = $objUtil->printNewListHeader3($eyeps, $link, $min, $step);
echo "<div id=\"main\" style=\"position:relative\">";
$objPresentations->line(array("<h5>".LangViewEyepieceTitle."</h5>",$content),"LR",array(70,30),50);
echo "<hr />";
echo "<table width=\"100%\">";
echo "<tr class=\"type3\">";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=name&amp;previous=$previous\">".LangViewEyepieceName."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=focalLength&amp;previous=$previous\">".LangViewEyepieceFocalLength."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=maxFocalLength&amp;previous=$previous\">".LangViewEyepieceMaxFocalLength."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=apparentFOV&amp;previous=$previous\">".LangViewEyepieceApparentFieldOfView."</a></td>";
echo "<td><a href=\"".$baseURL."index.php?indexAction=view_eyepieces&amp;sort=observer&amp;previous=$previous\">".LangViewObservationField2."</a></td>";
echo "<td></td>";
echo "</tr>";
$count = 0;
while(list ($key, $value) = each($eyeps))
{ if(($count>=$min)&&($count<$max))
  { $eyepieceProperties=$objEyepiece->getEyepiecePropertiesFromId($value);
    if ($value != "1")
    { echo "<tr class=\"type".(2-($count%2))."\">";
      echo "<td><a href=\"".$baseURL."index.php?indexAction=adapt_eyepiece&amp;eyepiece=".urlencode($value)."\">".stripslashes($eyepieceProperties['name'])."</a></td>";
      echo "<td align=\"center\">".$eyepieceProperties['focalLength']."</td>";
      echo "<td align=\"center\">".(($eyepieceProperties['maxFocalLength']!=-1)?$eyepieceProperties['maxFocalLength']:"-")."</td>";
      echo "<td align=\"center\">".$eyepieceProperties['apparentFOV']."</td>";
      echo "<td>".$eyepieceProperties['observer']."</td>";
      echo "<td>";
      if(!($objEyepiece->getEyepieceUsedFromId($value)))
        echo "<a href=\"".$baseURL."index.php?indexAction=validate_delete_eyepiece&amp;eyepieceid=".urlencode($value)."\">".LangRemove."</a>";
      echo "</td>";
			echo "</tr>";
   }
 }
 $count++;
}
echo "</table>";
echo "<hr />";
echo "</div>";
}
?>
