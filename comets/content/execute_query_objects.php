<?php

// execute_query_objects.php
// executes the comet query passed by setup_query_objects.php
// version 0.5: 2005/09/21, WDM

include_once "lib/cometobjects.php";
include_once "lib/observers.php";
include_once "lib/setup/language.php";
include_once "lib/util.php";

$util = new Util();
$util->checkUserInput();

$objects = new CometObjects;
$observer = new Observers;

// PAGE TITLE

echo("<div id=\"main\">\n<h2>");

echo LangSelectedObjectsTitle; // page title

echo("</h2>\n");

if($_GET['name'] || $_GET['icqname']) // at least one search field filled in 
{
  if (isset($_GET['previous']))
  {
    $prev = $_GET['previous'];
  }
  else
  {
    $prev = '';
  }

  $name = $_GET['name'];
  $icqname = $_GET['icqname'];

  // SEARCH ON OBJECT NAME

  // SETUP SEARCH QUERY
  $query = array("name" => $name, "icqname" => $icqname);

  // SORTING

  if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
  {
    $sort = $_GET['sort'];
  }
  else
  {
    $sort = "name"; // standard sort on name
  }

  // SELECT OBJECTS

  $result = $objects->getObjectFromQuery($query, $sort);

  if($sort != '')
  {
    if (isset($_GET['previous']) && $_GET['previous'] == $sort) // reverse sort when pushed twice
    {
      if(sizeof($obs) > 0)
      {
        krsort($obs);
      }
      $previous = ""; // reset previous field to sort on
    }
    else
    {
      $previous = $sort;
    }
  }

  // NUMBER OF PAGES

  if (isset($_GET['min']))
  {
    $min = $_GET['min'];
  }
  else
  {
    $min = '';
  }

  if ($result != "")
  {
    $count = 0; // counter for altering table colors
    
    $link = $baseURL."index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode($_GET['name']) . "&amp;sort=$sort&amp;previous=" . $prev;
    list($min, $max) = $util->printListHeader($result, $link, $min, 25, "");
 
    // OUTPUT RESULT

    echo "<table>\n";

    echo "<tr class=\"type3\">\n";

    echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode($_GET['name']) . "&amp;sort=name&amp;previous=$previous\">".LangOverviewObjectsHeader1."</a></td>\n";
    echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode($_GET['name']) . "&amp;sort=icqname&amp;previous=$previous\">".LangNewObjectIcqname."</a></td>\n";

    // Check the number of objects. If there are less than 500 objects, we 
    // enable the sorting on seen. 
    if (count($result) <= 500)
    {
     echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode($_GET['name']) . "&amp;sort=seen&amp;previous=$previous\">".LangOverviewObjectsHeader7."</a></td>\n";
    }
    else
    {
     echo "<td>".LangOverviewObjectsHeader7."</a></td>\n";
    }
         while(list($key, $value) = each($result))
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

             $name = $value;
             $icqname = $objects->getIcqname($objects->getId($value));

             // SEEN

             $seen = "-";

             $see = $objects->getObserved($name);
  
             if ($see == 1) // object has been seen already
             {
               $seen = "<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objects->getId($value)) . "\">X</a>";
             }
  
             if ($_SESSION['deepskylog_id'] != "")
             {
               $see = $objects->getObservedbyUser($name, $_SESSION['deepskylog_id']);

               if ($see == 1) // object has been seen by the observer logged in
               { 
                 $seen = "<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objects->getId($value)) . "\">Y</a>";
               }
             }

             echo "<tr $typefield>\n";
             echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($objects->getId($value)) . "\">$value</a></td>\n";
             echo "<td>$icqname</td>\n";
             echo "<td class=\"seen\">$seen</td>\n</tr>\n";

           }
           $count++; // increase line counter
         }   
         $_SESSION['object_query'] = $result;
         echo "</table>\n";
//         echo "<p><a href=\"comets/objects.pdf\" target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a></p>\n";
//         echo "<p><a href=\"comets/objects.csv\" target=\"new_window\">".LangExecuteQueryObjectsMessage6."</a></p><p>".LangExecuteQueryObjectsMessage1."</p>\n";
//         echo "</div>\n</body>\n</html>";
       }
       else // no results found
       {
          echo(LangExecuteQueryObjectsMessage2);
       }
     }
     else // no query fields filled in
     {
       echo(LangExecuteQueryObjectsMessage3);
     }
?>
