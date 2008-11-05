<?php
// selected_observations2.php
// generates an overview of selected observations in the database




//=========================================================================================== LOOKING FOR SPECIFIC OBJECT, BUT NO OBJECT FOUND WITH THAT NAME ================================================================== 
/*if(((array_key_exists('number',$_GET) && $_GET['number']) || 
    (array_key_exists('catalogue',$_GET) && $_GET['catalogue'])) && ($object==''))
{ echo("</h2>\n");
	echo "<p>" . LangObservationQueryError1b . "</p>";
  echo "<a href=\"deepsky/index.php?indexAction=query_observations\">" . LangObservationQueryError2 . "</a>";
  echo " " . LangObservationOR . " ";
  echo "<a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=%\">" . LangObservationQueryError3 . "</a>";
}*/





include 'content/data_get_observations.php';

if(count($obs))
{   
	 $step = 25;
	 $link2 = "deepsky/index.php?indexAction=result_selected_observations&"    .
				                                                 "catalogue="        . urlencode($catalogue) .
                                                    "&amp;instrument="       . urlencode($instrument) .
                                                    "&amp;object="           . urlencode($object) . 
                                                    "&amp;lco="              . urlencode($_SESSION['lco']) . 
                                                    "&amp;number="           . urlencode($number) .
                                                    "&amp;".$atlas."="       . urlencode($pagenumber) .
                                                    "&amp;observer="         . urlencode($observer) .
                                                    "&amp;site="             . urlencode($site) .
                                                    "&amp;minyear="          . urlencode($minyear) .
                                                    "&amp;minmonth="         . urlencode($minmonth) .
                                                    "&amp;minday="           . urlencode($minday) .
                                                    "&amp;maxyear="          . urlencode($maxyear) . 
                                                    "&amp;maxmonth="         . urlencode($maxmonth) .
                                                    "&amp;maxday="           . urlencode($maxday) . 
        	                                          "&amp;maxdiameter="      . urlencode($maxdiameter) .
                                                    "&amp;maxdiameterunits=" . urlencode($maxdiameterunits) .
	                                                  "&amp;mindiameter="      . urlencode($mindiameter) .
                                                    "&amp;mindiameterunits=" . urlencode($mindiameterunits) .
                                                    "&amp;type="             . urlencode($type) .
                                                    "&amp;con="              . urlencode($con) . 
                                                    "&amp;minLatDegrees="    . urlencode($minLatDegrees) .
		                                                "&amp;minLatMinutes="    . urlencode($minLatMinutes) .
		                                                "&amp;minLatSeconds="    . urlencode($minLatSeconds) .
	                                                  "&amp;maxLatDegrees="    . urlencode($maxLatDegrees) .
			                                              "&amp;maxLatMinutes="    . urlencode($maxLatMinutes) .
			                                              "&amp;maxLatSeconds="    . urlencode($maxLatSeconds) .
                                                    "&amp;maxmag="           . urlencode($maxmag) .
                                                    "&amp;minmag="           . urlencode($minmag) .
                                                    "&amp;maxsb="            . urlencode($maxsb) .
                                                    "&amp;minsb="            . urlencode($minsb) .
                                                    "&amp;minRAhours="       . urlencode($minRAhours) .
                                                    "&amp;minRAminutes="     . urlencode($minRAminutes) .
                                                    "&amp;minRAseconds="     . urlencode($minRAseconds) .
                                                    "&amp;maxRAhours="       . urlencode($maxRAhours) .
                                                    "&amp;maxRAminutes="     . urlencode($maxRAminutes) .
                                                    "&amp;maxRAseconds="     . urlencode($maxRAseconds) .
                                                    "&amp;maxDeclDegrees="   . urlencode($maxDeclDegrees) .
                                                    "&amp;maxDeclMinutes="   . urlencode($maxDeclMinutes) .
                                                    "&amp;maxDeclSeconds="   . urlencode($maxDeclSeconds) .
                                                    "&amp;minDeclDegrees="   . urlencode($minDeclDegrees) .
                                                    "&amp;minDeclMinutes="   . urlencode($minDeclMinutes) .
                                                    "&amp;minDeclSeconds="   . urlencode($minDeclSeconds) .
                                                    "&amp;minsize="          . urlencode($minsize) .
                                                    "&amp;size_min_units="   . urlencode($size_min_units) .
                                                    "&amp;maxsize="          . urlencode($maxsize) .
                                                    "&amp;size_max_units="   . urlencode($size_max_units) .
                                                    "&amp;atlas="            . urlencode($atlas) .
                                                    "&amp;page="             . urlencode($page) .
                                                    "&amp;description="      . urlencode($description) .
                                                    "&amp;drawings="         . urlencode($drawings) .
	                                                  "&amp;minvisibility="    . urlencode($minvisibility) .
           	                                        "&amp;maxvisibility="    . urlencode($maxvisibility) .
																				            "&amp;seen="             . urlencode($seenpar);
   while(list($key,$value)=each($usedLanguages))
	   $link2 = $link2 . "&amp;" . $value . "=" . $value;
		  
   $link = $link2 . "&amp;sort=" . $sort . "&amp;sortdirection=" . $_GET['sortdirection'];
	 
  //=============================================== IF IT CONCERNS THE OBSERVATIONS OF 1 SPECIFIC OBJECT, SHOW THE OBJECT AND IT'S OBSERVATIONS =====================================================================================
  if($object)
  { $object_ss = stripslashes($object);
    if(($object!='* ') && ((!array_key_exists('catalogue',$_GET)) || (array_key_exists('catalogue',$_GET) && $_GET['catalogue']=="") || ($_GET['number']!='')))
  	{ $seen = "<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\" title=\"" . LangObjectNSeen . "\">-</a>";
      $seenDetails = $objObject->getSeen($object);
      if(substr($seenDetails,0,1)=="X") // object has been seen already
        $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($object) . "\" title=\"" . LangObjectXSeen . "\">" . $seenDetails . "</a>";
      if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
      { if (substr($seenDetails,0,1)=="Y") // object has been seen by the observer logged in
          $seen = "<a href=\"deepsky/index.php?indexAction=result_selected_observations&object=" . urlencode($object) . "\" title=\"" . LangObjectYSeen . "\">" . $seenDetails . "</a>";
      }
      echo("<div id=\"main\"><h2>");
      echo (LangViewObjectTitle . "&nbsp;-&nbsp;" . $object_ss);
      echo "&nbsp;-&nbsp;" . LangOverviewObjectsHeader7 . "&nbsp;:&nbsp;" . $seen;
      echo("</h2>");
    	echo "<table width=\"100%\"><tr>";
    	echo("<td width=\"25%\" align=\"left\">");
      echo("<a href=\"deepsky/index.php?indexAction=detail_object&object=" . urlencode($object) . "\">" . LangViewObjectViewNearbyObject . " " . $object_ss);
    	echo("</td><td width=\"25%\" align=\"center\">");
      if (array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
        echo("<a href=\"deepsky/index.php?indexAction=add_observation&object=" . urlencode($object) . "\">" . LangViewObjectAddObservation . $object_ss . "</a>");
    	echo("</td>");
    	if($myList)
    	{ echo("<td width=\"25%\" align=\"center\">");
        if($objList->checkObjectInMyActiveList($object))
          echo("<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a>");
        else
          echo("<a href=\"deepsky/index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a>");
    	  echo("</td>");
    	}	
    	echo("</tr>");
    	echo("</table>");
  	  $objObject->showObject($object);
  	}
  }	
	
//=============================================================================================== START PAGE OUTPUT =====================================================================================
// TITLE
echo"<table width=\"100%\">";
echo"<td>";
echo("<div id=\"main\">\n<h2>");
if(array_key_exists('minyear',$_GET) && ($_GET['minyear'] == substr($theDate,0,4)) &&
   array_key_exists('minmonth',$_GET) && ($_GET['minmonth'] == substr($theDate,4,2)) &&
   array_key_exists('minday',$_GET) && ($_GET['minday'] == substr($theDate,6,2)))
  echo (LangSelectedObservationsTitle3); 
elseif ($catalogue=="*")
  echo (LangOverviewObservationsTitle); 
elseif($object)
  echo (LangSelectedObservationsTitle . $object);
else
  echo(LangSelectedObservationsTitle2);


	 
   if(count($obs)>0)
	 {
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
   list($min, $max) = $objUtil->printNewListHeader($obs, $link, $min, $step, $total);
	 echo"</td>";
	 echo"</table>";
	 
	 if($_SESSION['lco']=="O")
     echo "<p align=\"right\">" .  LangOverviewObservationsHeader5a;
	 
   if(sizeof($obs) > 0)
   {
      $count = 0; // counter for altering table colors
      if(sizeof($obs) > 0) // ONLY WHEN OBSERVATIONS AVAILABLE
      {
        // LINKS TO SORT ON OBSERVATION TABLE HEADERS
        echo "<table width=\"100%\">\n";
        echo "<tr width=\"100%\" class=\"type3\">\n";
				
				
        include "../common/layout/tables.php";
        tableSortHeader(LangOverviewObservationsHeader1, $link2 . "&amp;sort=objectname");
        tableSortHeader(LangViewObservationField1b,      $link2 . "&amp;sort=objects.con");
        tableSortHeader(LangOverviewObservationsHeader2, $link2 . "&amp;sort=observerid");
        tableSortHeader(LangOverviewObservationsHeader3, $link2 . "&amp;sort=instrumentid");
        tableSortHeader(LangOverviewObservationsHeader4, $link2 . "&amp;sort=date");
								
        if($_SESSION['lco']!="O")
				  echo("<td></td>\n");
				else
				  echo("<td width=\"15%\">" . LangOverviewObservationsHeader8 . "</td>\n".
                 "<td width=\"15%\">" . LangOverviewObservationsHeader9 . "</td>\n".
                 "<td width=\"15%\">" . LangOverviewObservationsHeader5. "</td>\n");
         echo "</tr>\n";
         while(list ($key, $value) = each($obs)) // go through observations array
         {
            if($count >= $min && $count < $max)
            { 
						  if($_SESSION['lco']=="L")
                $objObservation->showOverviewObservation($value, $count, $link . "&amp;min=" . $min, $myList);
							elseif($_SESSION['lco']=="C")
                $objObservation->showCompactObservation($value, $link . "&amp;min=" . $min, $myList);
							elseif($_SESSION['lco']=="O")
                $objObservation->showCompactObservationLO($value, $link . "&amp;min=" . $min, $myList);
            }
            $count++; // increase counter
         }
         echo ("</table>\n");
      }
			
      list($min, $max) = $objUtil->printNewListHeader($obs, $link, $min, $step, $total);

      $_SESSION['observation_query'] = $obs;
      echo "<p><a href=\"deepsky/observations.pdf\" target=\"new_window\">".LangExecuteQueryObjectsMessage4."</a> - ";
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
else //================================================================================================== no search fields filled in =======================================================================================
{
   echo("</h2>\n");
	 echo "<p>" . LangObservationQueryError1 . "</p>";
   echo "<a href=\"deepsky/index.php?indexAction=query_observations\">" . LangObservationQueryError2 . "</a>";
   echo " " . LangObservationOR . " ";
   echo "<a href=\"deepsky/index.php?indexAction=result_selected_observations&catalogue=%\">" . LangObservationQueryError3 . "</a>";
}
echo("</div>\n</div>\n</body>\n</html>");
?>
