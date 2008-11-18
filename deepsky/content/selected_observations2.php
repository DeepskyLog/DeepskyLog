<?php
// selected_observations2.php
// generates an overview of selected observations in the database

//====================== data_get_observations fetches the data, sorts it and places it in $_SESSION['Qobs'] and puts the toal number of observations in all languages in $_SESSION['QobsTotal'];
include 'content/data_get_observations.php';

if(count($_SESSION['Qobs'])==0) //================================================================================================== no reult present =======================================================================================
{
   echo("</h2>\n");
   echo "<a href=\"deepsky/index.php?indexAction=query_observations\">" . LangObservationNoResults . "</a>";
   echo " " . LangObservationOR . " ";
   echo "<a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=%\">" . LangObservationQueryError3 . "</a>";
}
else                           //================================================================================================== show results in $_SESSION['Qobs'] =======================================================================================
{  $step = 25;
	 $link2 = 'deepsky/index.php?indexAction=result_selected_observations&amp;lco='.urlencode($_SESSION['lco']); 
   reset($_GET);
	 while(list($key,$value)=each($_GET))
	   if($key!='indexAction')
		   $link2.="&amp;".$key."=".urlencode($value);
   while(list($key,$value)=each($usedLanguages))
	   $link2=$link2.'&amp;'.$value.'='.$value; 
   $link = $link2.'&amp;sort='.$_GET['sort'].'&amp;sortdirection='.$_GET['sortdirection'];
  //====================== the remainder of the pages formats the page output and calls showObject (if necessary) and showObservations
  //=============================================== IF IT CONCERNS THE OBSERVATIONS OF 1 SPECIFIC OBJECT, SHOW THE OBJECT BEFORE SHOWING ITS OBSERVATIONS =====================================================================================
  if($object)
  { $object_ss = stripslashes($object);
    $seen="<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=".urlencode($object)."\" title=\"".LangObjectNSeen."\">-</a>";
    $seenDetails = $objObject->getSeen($object);
    if(substr($seenDetails,0,1)=="X")
      $seen="<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."\" title=\"".LangObjectXSeen."\">".$seenDetails."</a>";
    if(array_key_exists("deepskylog_id", $_SESSION)&&$_SESSION["deepskylog_id"])
      if(substr($seenDetails,0,1)=="Y")
        $seen="<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."\" title=\"".LangObjectYSeen . "\">".$seenDetails."</a>";
    echo "<div id=\"main\">";
  	echo "<h2>";
    echo LangViewObjectTitle."&nbsp;-&nbsp;".$object_ss."&nbsp;-&nbsp;".LangOverviewObjectsHeader7."&nbsp;:&nbsp;".$seen;
    echo "</h2>";
  	echo "<table width=\"100%\">";
  	echo "<tr>";
  	echo "<td width=\"25%\" align=\"left\">";
    echo "<a href=\"deepsky/index.php?indexAction=detail_object&amp;object=".urlencode($object)."\">".LangViewObjectViewNearbyObject." ".$object_ss;
  	echo "</td><td width=\"25%\" align=\"center\">";
    if(array_key_exists("deepskylog_id", $_SESSION)&&$_SESSION["deepskylog_id"])
      echo "<a href=\"deepsky/index.php?indexAction=add_observation&object=".urlencode($object)."\">".LangViewObjectAddObservation.$object_ss."</a>";
  	echo "</td>";
  	if($myList)
  	{ echo "<td width=\"25%\" align=\"center\">";
      if($objList->checkObjectInMyActiveList($object))
        echo "<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."&amp;removeObjectFromList=".urlencode($object)."\">".$object_ss.LangListQueryObjectsMessage3.$listname_ss."</a>";
      else
        echo "<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=".urlencode($object)."&amp;addObjectToList=".urlencode($object)."&amp;showname=".urlencode($object)."\">".$object_ss.LangListQueryObjectsMessage2.$listname_ss."</a>";
  	  echo "</td>";
  	}	
  	echo "</tr>";
  	echo "</table>";
   $objObject->showObject($object);
  }	
  //=============================================================================================== START OBSERVATION PAGE OUTPUT =====================================================================================
  echo"<table width=\"100%\">";
  echo"<td>";
  echo("<div id=\"main\">\n<h2>");
  $theDate = date('Ymd', strtotime('-1 year')) ;
  if(array_key_exists('minyear',$_GET) && ($_GET['minyear'] == substr($theDate,0,4)) &&
     array_key_exists('minmonth',$_GET) && ($_GET['minmonth'] == substr($theDate,4,2)) &&
     array_key_exists('minday',$_GET) && ($_GET['minday'] == substr($theDate,6,2)))
    echo (LangSelectedObservationsTitle3); 
  //elseif ($catalogue=="*")
  //  echo (LangOverviewObservationsTitle); 
  elseif($object)
    echo (LangSelectedObservationsTitle . $object);
  else
    echo(LangSelectedObservationsTitle2);
	if(count($_SESSION['Qobs'])>0)
	 { if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
       if($_SESSION['lco']!="L")
  	     echo(" - <a href=\"". $link . "&amp;lco=L" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangOverviewObservationTitle . "\">" . 
  		       LangOverviewObservations . "</a>");
  	 if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
       if($_SESSION['lco']!="C")
         echo(" - <a href=\"". $link . "&amp;lco=C" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangCompactObservationsTitle . "\">" . 
  			        LangCompactObservations . "</a>");
  	 if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
       if($_SESSION['lco']!="O")
  		   echo(" - <a href=\"". $link . "&amp;lco=O" . "&amp;min=" . urlencode($min) . "\" title=\"" . LangCompactObservationsLOTitle . "\">" . 
  			        LangCompactObservationsLO . "</a>");
	 }
	 echo "</h2>";
	 echo"</td>";
	 echo"<td align=\"right\">";	 
   list($min, $max) = $objUtil->printNewListHeader($_SESSION['Qobs'], $link, $min, $step, $_SESSION['QobsTotal']);
	 echo"</td>";
	 echo"</table>";
	 
	 if($_SESSION['lco']=="O")
     echo "<p align=\"right\">" .  LangOverviewObservationsHeader5a;
	 
   if(sizeof($_SESSION['Qobs']) > 0)
   {
      $count = 0; // counter for altering table colors
      if(sizeof($_SESSION['Qobs']) > 0) // ONLY WHEN OBSERVATIONS AVAILABLE
      { echo "<table width=\"100%\">\n";
        echo "<tr width=\"100%\" class=\"type3\">\n"; // LINKS TO SORT ON OBSERVATION TABLE HEADERS
        tableSortHeader(LangOverviewObservationsHeader1, $link2 . "&amp;sort=objectname");
        tableSortHeader(LangViewObservationField1b,      $link2 . "&amp;sort=objectconstellation");
        tableSortHeader(LangOverviewObservationsHeader2, $link2 . "&amp;sort=observersortname");
        tableSortInverseHeader(LangOverviewObservationsHeader3, $link2 . "&amp;sort=instrumentsort");
        tableSortInverseHeader(LangOverviewObservationsHeader4, $link2 . "&amp;sort=observationdate");				
        if($_SESSION['lco']!="O")
				  echo("<td></td>\n");
				else
				  echo("<td width=\"15%\">" . LangOverviewObservationsHeader8 . "</td>\n".
                 "<td width=\"15%\">" . LangOverviewObservationsHeader9 . "</td>\n".
                 "<td width=\"15%\">" . LangOverviewObservationsHeader5. "</td>\n");
         echo "</tr>\n";
         while(list ($key, $value) = each($_SESSION['Qobs'])) // go through observations array
         {  if($count >= $min && $count < $max)
            { if(($_SESSION['lco']=="O")&&array_key_exists('deepskylog_id',$_SESSION)&&$_SESSION['deepskylog_id'])
                $objObservation->showCompactObservationLO($value, $link . "&amp;min=" . $min, $myList);
							elseif(($_SESSION['lco']=="C")&&array_key_exists('deepskylog_id',$_SESSION)&&$_SESSION['deepskylog_id'])
                $objObservation->showCompactObservation($value, $link . "&amp;min=" . $min, $myList);
              else
                $objObservation->showOverviewObservation($value, $count, $link . "&amp;min=" . $min, $myList);
						}
            $count++; // increase counter
         }
         echo ("</table>\n");
      }
			
      list($min, $max) = $objUtil->printNewListHeader($_SESSION['Qobs'], $link, $min, $step, $_SESSION['QobsTotal']);

      echo "<p>";
			$objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."deepsky/observations.pdf?SID=Qobs",LangExecuteQueryObjectsMessage4);
//			echo "<a href=\"deepsky/observations.pdf\" target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a>";
      echo " - ";
//			$objUtil->promptWithLink(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."deepsky/observations.csv?SID=Qobs",LangExecuteQueryObjectsMessage5);
      echo "<a href=\"deepsky/observations.csv\" target=\"new_window\">".LangExecuteQueryObjectsMessage5."</a> - ";
      echo "<a href=\"deepsky/index.php?indexAction=query_objects&amp;source=observation_query\">".LangExecuteQueryObjectsMessage9."</a> - ";

   }
   else //==================================================================================================== NO OBSERVATIONS FOUND - OUTPUT MESSAGE ===================================================================================== 
   {
      echo("</h2>\n");
			echo LangObservationNoResults; 
			echo "<p>";
   }
   //==================================================================================================== PAGE FOOTER - MAKE NEW QUERY ===================================================================================== 
   echo("<a href=\"deepsky/index.php?indexAction=query_observations\">" . LangObservationQueryError2 . "</a>");
}
echo "</div>";
echo "</div>";
?>
