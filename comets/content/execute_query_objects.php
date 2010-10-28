<?php 
// execute_query_objects.php
// executes the comet query passed by setup_query_objects.php

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else comets_execute_query_objects();

function comets_execute_query_objects()
{ global $baseURL,$step,$loggedUser,
         $objCometObject,$objPresentations,$objUtil,$objCometObservation;
	echo "<div id=\"main\">";
	$content="";
	if($_GET['name'] || $_GET['icqname']) // at least one search field filled in 
	{ if (isset($_GET['previous']))
	  { $prev = $_GET['previous'];
	  }
	  else
	  { $prev = '';
	  }
	  $name = $objUtil->checkGetKey('name');
	  $icqname = $objUtil->checkGetKey('icqname');
	  // SEARCH ON OBJECT NAME
	  // SETUP SEARCH QUERY
	  $query = array("name" => $name, "icqname" => $icqname);
	  // SORTING
	  if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
	  { $sort = $_GET['sort'];
	  }
	  else
	  { $sort = "name"; // standard sort on name
	  }
	  // SELECT OBJECTS
	  $result=$objCometObject->getObjectFromQuery($query, $sort);
	  if($sort)
	  { if (isset($_GET['previous']) && $_GET['previous'] == $sort) // reverse sort when pushed twice
	    { if(sizeof($result) > 0)
	      { krsort($result);
	      }
	      $previous = ""; // reset previous field to sort on
	    }
	    else
	    { $previous = $sort;
	    }
	  }
	  // NUMBER OF PAGES
	  if ($result)
	  { $count=0; // counter for altering table colors
	    $link = $baseURL."index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode($_GET['name']) . "&amp;sort=$sort&amp;previous=" . $prev;
	    // OUTPUT RESULT
			if((array_key_exists('steps',$_SESSION))&&(array_key_exists("comObj",$_SESSION['steps'])))
			  $step=$_SESSION['steps']["comObj"];
			if(array_key_exists('multiplepagenr',$_GET))
			  $min = ($_GET['multiplepagenr']-1)*$step;
			elseif(array_key_exists('multiplepagenr',$_POST))
			  $min = ($_POST['multiplepagenr']-1)*$step;
			elseif(array_key_exists('min',$_GET))
			  $min=$_GET['min'];
			else
			  $min = 0;
			$rank = $objCometObservation->getPopularObservations();
			list($min, $max, $content) = $objUtil->printNewListHeader3($result, $link, $min, $step, "");
			$content2=$objUtil->printStepsPerPage3($link,"comObj",$step);
	    $objPresentations->line(array("<h4>".LangSelectedObjectsTitle."</h4>",$content),"LR",array(60,40),30);
	 		$objPresentations->line(array($content2),"R",array(100),20);
	    echo "<hr />";
	    echo "<table>";
	    echo "<tr class=\"type3\">";
	    echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode($_GET['name']) . "&amp;sort=name&amp;previous=$previous\">".LangOverviewObjectsHeader1."</a></td>";
	    echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode($_GET['name']) . "&amp;sort=icqname&amp;previous=$previous\">".LangNewObjectIcqname."</a></td>";
	    // Check the number of objects. If there are less than 500 objects, we 
	    // enable the sorting on seen. 
	    if (count($result) <= 500)
	    { echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_objects&amp;name=" . urlencode($_GET['name']) . "&amp;sort=seen&amp;previous=$previous\">".LangOverviewObjectsHeader7."</a></td>";
	    }
	    else
	    { echo "<td>".LangOverviewObjectsHeader7."</a></td>";
	    }
	    echo "</tr>";
	    while(list($key,$value)=each($result))
	    { if(($count>=$min)&&($count<$max))
	      { if ($count % 2)
	        { $typefield = "class=\"type1\"";
	        }
	        else
	        { $typefield = "class=\"type2\"";
	        }
	        // NAME
	        $name = $value;
	        $icqname = $objCometObject->getIcqname($objCometObject->getId($value));
	        // SEEN
	        $seen = "-";
	        $see = $objCometObject->getObserved($name);
	        if ($see == 1) // object has been seen already
	        { $seen = "<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objCometObject->getId($value)) . "\">X</a>";
	        }
	        if($loggedUser)
	        { $see = $objCometObject->getObservedbyUser($name, $loggedUser);
	          if ($see == 1) // object has been seen by the observer logged in
	          { $seen = "<a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . urlencode($objCometObject->getId($value)) . "\">Y</a>";
	          }
	        }
	        echo "<tr $typefield>";
	        echo "<td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($objCometObject->getId($value)) . "\">$value</a></td>";
	        echo "<td>$icqname</td>";
	        echo "<td class=\"seen\">$seen</td>";
	        echo "</tr>";
	      }
	      $count++; // increase line counter
	    }   
	    $_SESSION['object_query'] = $result;
	    echo "</table>";
	    echo "<hr />";
	  }
	  else // no results found
	  { $objPresentations->line(array("<h4>".LangSelectedObjectsTitle."</h4>"),"L",array(),30);
	    echo "<hr />";
	  	echo LangExecuteQueryObjectsMessage2;
	  }
	}
	else // no query fields filled in
	{ $objPresentations->line(array("<h4>".LangSelectedObjectsTitle."</h4>",$content),"LR",array(60,40),30);
	  echo "<hr />";
	  echo LangExecuteQueryObjectsMessage3;
	}
	echo "</div>";
}
?>
