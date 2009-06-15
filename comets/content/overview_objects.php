<?php // overview_objects.php - generates an overview of all comets in the database
// SORTING
if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
{ $sort = $_GET['sort'];
}
else
{ $sort = "name"; // standard sort on name
}
$obstest = $objCometObject->getObjects(); // check to test if there are any objects in database
if(isset($_GET['previous']))
{ $prev = $_GET['previous'];
}
else
{ $prev = '';
}
if(sizeof($obstest) > 0) // at least one object in database
{ $obs = $objCometObject->getSortedObjects($sort);
  if((@$sort != '') && @$_GET['previous'] == @$_GET['sort']) // reverse sort when pushed twice
  { if(sizeof($obs) > 0)
    { $obs = array_reverse($obs, true);
    }
    else
    { krsort($obs);
      reset($obs);
    }
    $previous = ""; // reset previous field to sort on
  }
  else
  { $previous = $sort;
  }
}
// TABLE LINKS
if(array_key_exists('min',$_GET))
{  $tempmin = $_GET['min'];
}
else
{ $tempmin = '';
} 
$link = "".$baseURL."index.php?indexAction=comets_view_objects&amp;sort=".$sort."&amp;previous=".$prev;
// PAGE TITLE
echo "<div id=\"main\">";
if((array_key_exists('steps',$_SESSION))&&(array_key_exists("allComObj",$_SESSION['steps'])))
  $step=$_SESSION['steps']["allComObj"];
if(array_key_exists('multiplepagenr',$_GET))
  $min = ($_GET['multiplepagenr']-1)*$step;
elseif(array_key_exists('multiplepagenr',$_POST))
  $min = ($_POST['multiplepagenr']-1)*$step;
elseif(array_key_exists('min',$_GET))
  $min=$_GET['min'];
else
  $min = 0;
list($min, $max, $content) = $objUtil->printNewListHeader3($obs, $link, $min, $step, "");
$content2=$objUtil->printStepsPerPage3($link,"allComObj",$step);
$objPresentations->line(array("<h4>".LangOverviewObjectsTitle."</h4>",$content),"LR",array(70,30),30);
$objPresentations->line(array($content2),"R",array(100),20);
echo "<hr />";
if(sizeof($obstest) > 0)
{ $count = 0; // counter for altering table colors
  // OBJECT TABLE HEADERS
  echo "<table style=\"width:100%\">";
  echo "<tr class=\"type3\">";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_view_objects&amp;name&amp;previous=$previous\">" . LangOverviewObjectsHeader1 . "</a></td>\n";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_view_objects&amp;icqname&amp;previous=$previous\">" . LangNewObjectIcqname . "</a></td>\n";
  echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_view_objects&amp;sort=seen&amp;previous=$previous\">".LangOverviewObjectsHeader7."</a></td>\n";

  while(list ($key, $value) = each($obs)) // go through object array
  { if($count >= $min && $count < $max)
    { if ($count % 2)
      { $typefield = "class=\"type1\"";
      }
      else
      {  $typefield = "class=\"type2\"";
      }
      // NAME
      $name = $value[0];
      $icqname = $objCometObject->getIcqname($objCometObject->getId($value[0]));
      // SEEN
      $seen = "-";
      $see = $objCometObject->getObserved($name);
      if ($see == 1)
      {$seen = "<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objCometObject->getId($value[0])) . "\">X</a>";
      }
      if ($loggedUser)
      { $see = $objCometObject->getObservedbyUser($name, $_SESSION['deepskylog_id']);
        if ($see == 1)
        { $seen = "<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objCometObject->getId($value[0])) . "\">Y</a>";
        }
      }
      // OUTPUT
      echo "<tr $typefield>";
      echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($objCometObject->getId($value[0])) . "\">$value[0]</a></td>";
      echo "<td>$icqname</td>";
      echo "<td class=\"seen\">$seen</td></tr>";
    }
    $count++; // increase line counter
  }
  echo "</table>";
  echo "<hr />";
}
echo "</div>";
?>
