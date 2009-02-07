<?php
// overview_objects.php
// generates an overview of all comets in the database
// Version 0.3: 2005/09/21, WDM

include_once "lib/cometobjects.php";
include_once "lib/setup/language.php";
include_once "common/control/ra_to_hms.php";
include_once "common/control/dec_to_dm.php";
include_once "lib/util.php";

$objects = new CometObjects;
$util = new Utils();
$util->checkUserInput();

// SORTING

if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
{
  $sort = $_GET['sort'];
}
else
{
  $sort = "name"; // standard sort on name
}

$obstest = $objects->getObjects(); // check to test if there are any objects in database


if(isset($_GET['previous']))
{
  $prev = $_GET['previous'];
}
else
{
  $prev = '';
}

if(sizeof($obstest) > 0) // at least one object in database
{
  $obs = $objects->getSortedObjects($sort);


  if((@$sort != '') && @$_GET['previous'] == @$_GET['sort']) // reverse sort when pushed twice
  {
    if(sizeof($obs) > 0)
    {
      $obs = array_reverse($obs, true);
    }
    else
    {
      krsort($obs);
      reset($obs);
    }
    $previous = ""; // reset previous field to sort on
  }
  else
  {
    $previous = $sort;
  }
}


// PAGE TITLE

echo("<div id=\"main\">\n<h2>");

echo LangOverviewObjectsTitle; // page title

echo("</h2>\n");

// TABLE LINKS

if(array_key_exists('min',$_GET))
{   
	 $tempmin = $_GET['min'];
}
else
{
	 $tempmin = '';
} 

if(sizeof($obstest) > 0)
{
  $count = 0; // counter for altering table colors

  $link = "".$baseURL."index.php?indexAction=comets_view_objects&amp;sort=".$sort."&amp;previous=".$prev;
  list($min, $max) = $util->printNewListHeader($obs, $link, $tempmin, 25, "");
 
  // OBJECT TABLE HEADERS

  echo "<table>\n
        <tr class=\"type3\">\n
        <td><a href=\"".$baseURL."index.php?indexAction=comets_view_objects&amp;name&amp;previous=$previous\">" . LangOverviewObjectsHeader1 . "</a></td>\n";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_view_objects&amp;icqname&amp;previous=$previous\">" . LangNewObjectIcqname . "</a></td>\n";

      echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_view_objects&amp;sort=seen&amp;previous=$previous\">".LangOverviewObjectsHeader7."</a></td>\n";

  while(list ($key, $value) = each($obs)) // go through object array
  {
    if($count >= $min && $count < $max)
    { 
      if ($count % 2)
      {
        $typefield = "class=\"type1\"";
      }
      else
      {
         $typefield = "class=\"type2\"";
      }

      // NAME

      $name = $value[0];
      $icqname = $objects->getIcqname($objects->getId($value[0]));

      // SEEN

      $seen = "-";

      $see = $objects->getObserved($name);

      if ($see == 1)
      {
        $seen = "<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objects->getId($value[0])) . "\">X</a>";
      }

      if ($_SESSION['deepskylog_id'] != "")
      {
        $see = $objects->getObservedbyUser($name, $_SESSION['deepskylog_id']);

        if ($see == 1)
        {
          $seen = "<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objects->getId($value[0])) . "\">Y</a>";
        }
      }
 
      // OUTPUT

      echo("<tr $typefield>\n");
      echo("<td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($objects->getId($value[0])) . "\">$value[0]</a></td>\n");
      echo("<td>$icqname</td>\n");
      echo("<td class=\"seen\">$seen</td></tr>\n");

    }

    $count++; // increase line counter
  }

  echo "</table>\n";
}

echo "</div>\n</body>\n</html>";

?>
