<?php
// selected_observations.php
// generates an overview of selected observations in the database

global $inIndex,$loggedUser,$objUtil;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else selected_observation();

function selected_observation()
{ global $baseURL,$dateformat,
         $objUtil;
	// creation of objects
	
	$observations = new CometObservations;
	$instruments = new Instruments;
	$observers = new Observers;
	$objects = new CometObjects;
	$util = $objUtil;
	
	// selection of all observations of one object
	
	echo "<div id=\"main\">";
	if(isset($_GET['objectname']))
	{ $queries = array("object" => $objects->getName($_GET['objectname'])); // sql query
	  if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
	  { $sort = $_GET['sort'];
	    $obs = $observations->getObservationFromQuery($queries, $sort);
	  }
	  else
	  { $sort = "id"; // standard sort on insertion date
	    $obs = $observations->getObservationFromQuery($queries);
	    if(sizeof($obs) > 0)
	    { krsort($obs);
	    } 
	  }
	
	  if(isset($_GET['previous']))
	  { $prev = $_GET['previous'];
	    $previous = $_GET['previous'];
	  }
	  else
	  { $prev = '';
	    $previous = '';
	  }
	
	  if(isset($_GET['sort']))
	  { $sort = $_GET['sort'];
	  }
	  else
	  { $sort = '';
	  }
	
	  if(($sort != '') && $previous == $sort) // reverse sort when pushed twice
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
	
	  // save $obs as a session variable
	
	  $_SESSION['obs'] = $obs;
	  $_SESSION['observation_query'] = $obs;
	
	  $count = 0; // counter for altering table colors
	
	   if(isset($_GET['min']))
	   { $min = $_GET['min'];
	   } 
	   else
	   { $min = 0;
	   }
	
	  $link = "".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=".$sort."&amp;previous=".$prev;
		if((array_key_exists('steps',$_SESSION))&&(array_key_exists("selComObs1",$_SESSION['steps'])))
			$step=$_SESSION['steps']["selComObs1"];
		if(array_key_exists('multiplepagenr',$_GET))
		  $min = ($_GET['multiplepagenr']-1)*$step;
		elseif(array_key_exists('multiplepagenr',$_POST))
		  $min = ($_POST['multiplepagenr']-1)*$step;
		elseif(array_key_exists('min',$_GET))
		  $min=$_GET['min'];
		else
		  $min = 0;
	  list($min, $max, $content) = $util->printNewListHeader3($obs, $link, $min, $step, "");
	  $objPresentations->line(array("<h4>".LangSelectedObservationsTitle .$objects->getName($_GET['objectname'])."</h4>",$content),"LR",array(50,50),30);
		$content=$objUtil->printStepsPerPage3($link,"selComObs1",$step);
		$objPresentations->line(array($content),"R",array(100),20);
	  echo "<hr />";
	   
	  if(sizeof($obs) > 0)
	  {
	    echo "<table>
	      <tr class=\"type3\">
	      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=objectid&amp;previous=$previous\">" . LangOverviewObservationsHeader1 . "</a></td>
	      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=observerid&amp;previous=$previous\">" . LangOverviewObservationsHeader2 . "</a></td>
	      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=date&amp;previous=$previous\">" . LangOverviewObservationsHeader4 . "</a></td>
	      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=mag&amp;previous=$previous\">" . LangNewComet1 . "</a></td>
	      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=inst&amp;previous=$previous\">" .LangViewObservationField3 . "</a></td>
	      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=coma&amp;previous=$previous\">" . LangViewObservationField19 . "</a></td>
	      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=dc&amp;previous=$previous\">" . LangViewObservationField18b . "</a></td>
	      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;objectname=" . $_GET['objectname'] . "&amp;sort=tail&amp;previous=$previous\">" . LangViewObservationField20b . "</a></td>
	      <td></td>
	      </tr>";
	
	    while(list ($key, $value) = each($obs)) // go through observations array
	    { if($count >= $min && $count < $max)
	      { if ($count % 2)
	        { $typefield = "class=\"type1\"";
	        }
	        else
	        { $typefield = "class=\"type2\"";
	        }
	
	        // OBJECT
	
	        $object = $observations->getObjectId($value); // overhead as this is every time the same object?!
	
	        // OUTPUT
	
	        echo("<tr $typefield>
	            <td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($object) . "\">" . $objects->getName($object) . "</a></td>");
	
	        // OBSERVER
	
	        $observer = $observations->getObserverId($value);
	
	        echo("<td>");
	
	        echo("<a href=\"".$baseURL."index.php?indexAction=detail_observer&amp;user=" . urlencode($observer) . "\">" . $observers->getObserverProperty($observer,'firstname') . "&nbsp;" . $observers->getObserverProperty($observer,'name') . "</a>");
	
	        echo("</td>");
	
	        // DATE
	
	        if ($observers->getObserverProperty($loggedUser,'UT'))
	        {
	          $date = sscanf($observations->getDate($value), "%4d%2d%2d");
	        }
	        else
	        {
	          $date = sscanf($observations->getLocalDate($value), "%4d%2d%2d");
	        }
	
	
	        echo("<td>");
	
	        echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));
	
	        // TIME
	
	        echo(" (");
	
	        if ($observers->getObserverProperty($loggedUser,'UT'))
	        {
	         $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");
	        }
	        else
	        {
	         $time = sscanf(sprintf("%04d", $observations->getLocalTime($value)), "%2d%2d");
	        }
	
	        printf("%02d", $time[0]);
	        echo (":");
	
	        printf("%02d", $time[1]);
	
	        $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");
	
	        echo(")</td>");
	
		      // INSTRUMENT
		
		      $temp = $observations->getInstrumentId($value);
		      $instrument = $instruments->getInstrumentPropertyFromId($temp,'name');
		      $instrumentsize = $instruments->getInstrumentPropertyFromId($temp,'diameter');
		      if ($instrument == "Naked eye")
		      {
		       $instrument = InstrumentsNakedEye;
		      }
		
		      // MAGNITUDE
		
		      $mag = $observations->getMagnitude($value);
		
		      if ($mag < -90)
		      {
		       $mag = '';
		      }
		      else
		      {
		       $mag = sprintf("%01.1f", $observations->getMagnitude($value));
		      }
		
		      // COMA
		
		      $coma = $observations->getComa($value);
		      if ($coma < -90)
		      {
		       $coma = '';
		      }
		      else
		      {
		       $coma = $coma."'";
		      }
		
		      // DC
		
		      $dc = $observations->getDc($value);
		
		      if ($dc < -90)
		      {
		       $dc = '';
		      }
		
		      // TAIL
		
		      $tail = $observations->getTail($value);
		      if ($tail < -90)
		      {
		       $tail = '';
		      }
		      else
		      {
		       $tail = $tail."'";
		      }
		
		      if($instrument != InstrumentsNakedEye && $instrument != "")
		      {
		         $instrument = $instrument . " (" . $instrumentsize . "&nbsp;mm" . ")";
		      }
	
	        echo(" <td>$mag</td>
	            <td>$instrument</td>
	            <td>$coma</td>
	            <td>$dc</td>
	            <td>$tail</td>");
	
	        // DETAILS
	
	        echo("<td><a href=\"".$baseURL."index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");
	
	        // LINK TO DRAWING (IF AVAILABLE)
	
	        echo("</a></td></tr>");
	
	      }
	
	      $count++; // increase counter
	    }
	
			echo ("</table>");
			echo "<hr />";
			$objPresentations->line(array(LangExecuteQueryObjectsMessage4." <a href=\"".$baseURL."cometobservations.pdf\" rel=\"external\">".LangExecuteQueryObjectsMessage4a."</a>"),"L",array(),20);
		}
	  else // no observations of object
	  {
	    echo LangNoObservations;
	  }
	  echo "</div>";
	}
	elseif($_GET['user']) // selection of all observations of one observer 
	{ $query = array("observer"=>$_GET['user']);
		if(isset($_GET['sort'])) // field to sort on given as a parameter in the url
		{ $sort = $_GET['sort'];
		  $obs = $observations->getObservationFromQuery($query, $sort);
		}
		else
		{ $sort = "id"; // standard sort on date
		  $obs = $observations->getObservationFromQuery($query, $sort);
		  if(sizeof($obs) > 0)
		    krsort($obs);
		}
		// save $obs as a session variable
		$_SESSION['obs'] = $obs;
		$_SESSION['observation_query'] = $obs;
		$count = 0; // counter for altering table colors
		if (isset($_GET['sort']))
		  $sort = $_GET['sort'];
		else
		  $sort = '';
		if (isset($_GET['min']))
		  $min = $_GET['min'];
		else
		  $min = '';
		if(isset($_GET['previous']))
		  $previous = $_GET['previous'];
		else
		  $previous = '';
		if(($sort != '') && $_GET['previous'] == $_GET['sort']) // reverse sort when pushed twice
		{ if(sizeof($obs) > 0)
		    krsort($obs);
		  $previous = ""; // reset previous field to sort on
		}
		else
		  $previous = $sort;
		$link = "".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=".$sort."&amp;previous=".$previous;
		if((array_key_exists('steps',$_SESSION))&&(array_key_exists("selComObs1",$_SESSION['steps'])))
			$step=$_SESSION['steps']["selComObs1"];
		if(array_key_exists('multiplepagenr',$_GET))
		  $min = ($_GET['multiplepagenr']-1)*$step;
		elseif(array_key_exists('multiplepagenr',$_POST))
		  $min = ($_POST['multiplepagenr']-1)*$step;
		elseif(array_key_exists('min',$_GET))
		  $min=$_GET['min'];
		else
		  $min = 0;
		list($min,$max,$content)=$objUtil->printNewListHeader3($obs, $link, $min, $step);
		$objPresentations->line(array("<h4>".LangSelectedObservationsTitle.$observers->getObserverProperty($_GET['user'],'firstname')."&nbsp;".$observers->getObserverProperty($_GET['user'],'name')."</h4>",$content),
	                          "LR",array(50,50),30);
		$content=$objUtil->printStepsPerPage3($link,"selComObs1",$step);
	  $objPresentations->line(array($content),"R",array(100),20);
	  echo "<hr />";
	
		
		// NEW BEGIN
		  
		if(sizeof($obs) > 0)
		{ // OBJECT TABLE HEADERS
			echo "<table>
			      <tr class=\"type3\">
			      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=objectid&amp;previous=$previous\">" . LangOverviewObservationsHeader1 . "</a></td>
			      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=date&amp;previous=$previous\">" . LangOverviewObservationsHeader4 . "</a></td>
			      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=mag&amp;previous=$previous\">" . LangNewComet1 . "</a></td>
			      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=inst&amp;previous=$previous\">" . LangViewObservationField3 . "</a></td>
			      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=coma&amp;previous=$previous\">" . LangViewObservationField19 . "</a></td>
			      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=dc&amp;previous=$previous\">" . LangViewObservationField18b . "</a></td>
			      <td><a href=\"".$baseURL."index.php?indexAction=comets_result_query_observations&amp;user=" . $_GET['user'] . "&amp;sort=tail&amp;previous=$previous\">" . LangViewObservationField20b . "</a></td>
			      <td></td>
			      </tr>";
			
			while(list ($key, $value) = each($obs)) // go through observations array
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
			
			      // OBJECT
			
			      $object = $observations->getObjectId($value);
			
			      // OUTPUT
			
			      echo("<tr $typefield>
			            <td><a href=\"".$baseURL."index.php?indexAction=comets_detail_object&amp;object=" . urlencode($object) . "\">" . $objects->getName($object) . "</a></td>
			            <td>");
			
			      // DATE
			
			        if ($observers->getObserverProperty($loggedUser,'UT'))
			        {
			         $date = sscanf($observations->getDate($value), "%4d%2d%2d");
			        }
			        else
			        {
			         $date = sscanf($observations->getLocalDate($value), "%4d%2d%2d");
			        }
			
			      echo date ($dateformat, mktime (0,0,0,$date[1],$date[2],$date[0]));
			
			      // TIME
			
			      echo("&nbsp;(");
			
			        if ($observers->getObserverProperty($loggedUser,'UT'))
			        {
			         $time = sscanf(sprintf("%04d", $observations->getTime($value)), "%2d%2d");
			        }
			        else
			        {
			         $time = sscanf(sprintf("%04d", $observations->getLocalTime($value)), "%2d%2d");
			        }
			       
			         printf("%02d", $time[0]);
			
			         echo (":");
			
			         printf("%02d", $time[1]);
			
			     echo(")</td>");
			
			      // INSTRUMENT
			
			      $temp = $observations->getInstrumentId($value);
			      $instrument = $instruments->getInstrumentPropertyFromId($temp,'name');
			      if ($instrument == "Naked eye")
			      {
			       $instrument = InstrumentsNakedEye;
			      }
			
			      // MAGNITUDE
			
			      $mag = $observations->getMagnitude($value);
			
			      if ($mag < -90)
			      {
			       $mag = '';
			      }
			
			      // COMA
			
			      $coma = $observations->getComa($value);
			      if ($coma < -90)
			      {
			       $coma = '';
			      }
			      else
			      {
			       $coma = $coma."'";
			      }
			
			      // DC
			
			      $dc = $observations->getDc($value);
			
			      if ($dc < -90)
			      {
			       $dc = '';
			      }
			
			      // TAIL
			
			      $tail = $observations->getTail($value);
			      if ($tail < -90)
			      {
			       $tail = '';
			      }
			      else
			      {
			       $tail = $tail."'";
			      }
			
			     echo(" <td>$mag</td>
			            <td>$instrument</td>
			            <td>$coma</td>
			            <td>$dc</td>
			            <td>$tail</td>");
			
			     // DETAILS
			
			     echo("<td><a href=\"".$baseURL."index.php?indexAction=comets_detail_observation&amp;observation=" . $value . "\">details");
			
			      // LINK TO DRAWING (IF AVAILABLE)
			
			     $upload_dir = 'cometdrawings';
			     $dir = opendir($instDir."comets/".$upload_dir);
			
			     while (FALSE !== ($file = readdir($dir)))
			     {
			       if ("." == $file OR ".." == $file)
			       {
			         continue; // skip current directory and directory above
			       }
			       if(fnmatch($value . "_resized.gif", $file) || fnmatch($value . "_resized.jpg", $file) || fnmatch($value. "_resized.png", $file))
			       {
			         echo("&nbsp;+&nbsp;");
			         echo LangDrawing;
			      }
			   }
			   echo("</a></td></tr>");
			 }	
			 $count++; // increase counter
			}
			echo ("</table>");
			echo "<hr />";
			$_SESSION['observation_query'] = $obs;
			$objPresentations->line(array(LangExecuteQueryObjectsMessage4." <a href=\"".$baseURL."cometobservations.pdf\" rel=\"external\">".LangExecuteQueryObjectsMessage4a."</a>"),"L",array(),20);
		}
		echo "</div>";
	}
}
?>
