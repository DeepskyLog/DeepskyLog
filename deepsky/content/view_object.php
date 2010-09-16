<?php  // view_object.php - view all information of one object 
if(!($object=$objUtil->checkGetKey('object')))
  throw new Exception('To implement');
else
{ echo "<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/presentation.js\"></script>";
	$seen=$objObject->getDSOseenLink($object);
	echo "<div id=\"main\">";
	$object_ss = stripslashes($object);
	$objPresentations->line(array("<h4>".LangViewObjectTitle."&nbsp;-&nbsp;".$object_ss."&nbsp;-&nbsp;".LangOverviewObjectsHeader7."&nbsp;:&nbsp;".$seen."</h4>",$objPresentations->getDSSDeepskyLiveLinks1($object)),
	                        "LR",array(60,40),30);
	$topline="";
	if($imagesize=$objUtil->checkRequestKey('imagesize'))
	  $topline="&nbsp;-&nbsp;"."<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."\">".LangViewObjectViewNearbyObject."</a>";
	if ($myList) 
	{ if ($objList->checkObjectInMyActiveList($object))
			$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;removeObjectFromList=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage3 . $listname_ss . "</a>";
		else
			$topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "index.php?indexAction=result_selected_observations&amp;object=" . urlencode($object) . "&amp;addObjectToList=" . urlencode($object) . "&amp;showname=" . urlencode($object) . "\">" . $object_ss . LangListQueryObjectsMessage2 . $listname_ss . "</a>";
	}
  $objPresentations->line(array("<a href=\"" . $baseURL . "index.php?indexAction=atlaspage&amp;object=" . urlencode($object) . "\">" . LangAtlasPage . "</a>",$objPresentations->getDSSDeepskyLiveLinks2($object)),"LR",array(40,60),20);
	echo "<hr />";
	$objObject->showObject($object);
	if($loggedUser && ($theLocation=$objObserver->getObserverProperty($loggedUser, 'stdLocation')))
	{ if(array_key_exists('viewobjectephemerides',$_GET))
	    $viewobjectephemerides=$_GET['viewobjectephemerides'];
	  elseif(array_key_exists('viewobjectephemerides',$_COOKIE))
	    $viewobjectephemerides=$_COOKIE['viewobjectephemerides'];
	  else
	    $viewobjectephemerides='show';
		if($viewobjectephemerides=="show")
      $objPresentations->line(array("<h4>"."<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['object']).'&amp;zoom='.$objUtil->checkGetKey("zoom",30).'&amp;SID=Qobj&amp;viewobjectephemerides=hidden'."\" title=\"".ReportEpehemeridesForHide."\">-</a> ".ReportEpehemeridesFor."&nbsp;".$object_ss.' '.ReportEpehemeridesIn.' '.$objLocation->getLocationPropertyFromId($theLocation, 'name')."</h4>"),
	                        "L",array(100),30);
    else
		  $objPresentations->line(array("<h4>"."<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['object']).'&amp;zoom='.$objUtil->checkGetKey("zoom",30).'&amp;SID=Qobj&amp;viewobjectephemerides=show'."\" title=\"".ReportEpehemeridesForShow."\">+</a> ".ReportEpehemeridesFor."&nbsp;".$object_ss.' '.ReportEpehemeridesIn.' '.$objLocation->getLocationPropertyFromId($theLocation, 'name')."</h4>"),
	                        "L",array(100),30);
    $longitude = 1.0 * $objLocation->getLocationPropertyFromId($theLocation, 'longitude');
    $latitude = 1.0 * $objLocation->getLocationPropertyFromId($theLocation, 'latitude');
	  
    $timezone=$objLocation->getLocationPropertyFromId($theLocation,'timezone');
    $dateTimeZone=new DateTimeZone($timezone);
      
    echo "<hr />";
    if($viewobjectephemerides=="show")
    { echo "<div id=\"ephemeridesdiv\">";
	    for($i=1;$i<13;$i++)
			{ $datestr=sprintf("%02d",$i)."/".sprintf("%02d",1)."/".$_SESSION['globalYear'];
	      $dateTime = new DateTime($datestr, $dateTimeZone);
	      $timedifference = $dateTimeZone->getOffset($dateTime);
	      if (strncmp($timezone, "Etc/GMT", 7)==0) 
	        $timedifference = -$timedifference;
	      date_default_timezone_set ("UTC");
				$theTimeDifference1[$i]=$timedifference;
	      $theEphemerides1[$i]=$objObject->getEphemerides($object,1,$i,2010);
	      $theNightEphemerides1[$i]=date_sun_info(strtotime("2010"."-".$i."-"."1"), $latitude, $longitude);
				$datestr=sprintf("%02d",$i)."/".sprintf("%02d",1)."/".$_SESSION['globalYear'];
	      $dateTime = new DateTime($datestr, $dateTimeZone);
	      $timedifference = $dateTimeZone->getOffset($dateTime);
	      if (strncmp($timezone, "Etc/GMT", 7)==0) 
	        $timedifference = -$timedifference;
	      date_default_timezone_set ("UTC");
				$theTimeDifference15[$i]=$timedifference;
	      $theEphemerides15[$i]=$objObject->getEphemerides($object,15,$i,2010);
	      $theNightEphemerides15[$i]=date_sun_info(strtotime("2010"."-".$i."-"."15"), $latitude, $longitude);	
			}
			echo "<table>";
			echo "<tr class=\"type10\">";
			echo "<td class=\"centered\">".LangMonth." > </td>";
			for($i=1;$i<13;$i++)
			{ echo"<td>&nbsp;</td><td class=\"centered\">".$i."</td>";
			}
			echo"<td>&nbsp;</td>";
			echo "</tr>";
			echo "<tr class=\"type20\">";
			echo "<td class=\"centered\">".LangMaxAltitude."</td>";
			for($i=1;$i<13;$i++)
			{ $colorclass="";
			  if($i==1)
			  { if(($theEphemerides1[$i]['altitude']!='-') && ($theEphemerides15[$i]['altitude']!='-') &&
			       (($theEphemerides1[$i]['altitude']==$theEphemerides15[$i]['altitude']) ||
			        ($theEphemerides1[$i]['altitude']==$theEphemerides15[12]['altitude'])))
			      $colorclass="ephemeridesgreen";
			  }
	      else
	        if(($theEphemerides1[$i]['altitude']!='-') && ($theEphemerides15[$i]['altitude']!='-') && 
	           (($theEphemerides1[$i]['altitude']==$theEphemerides15[$i]['altitude']) ||
			        ($theEphemerides1[$i]['altitude']==$theEphemerides15[$i-1]['altitude'])))
			      $colorclass="ephemeridesgreen";
				echo"<td class=\"centered ".$colorclass."\">".$theEphemerides1[$i]['altitude']."</td>";
	      $colorclass="";
			  if($i==12)
			  { if(($theEphemerides1[$i]['altitude']!='-') && ($theEphemerides15[$i]['altitude']!='-') &&
			       (($theEphemerides15[$i]['altitude']==$theEphemerides1[$i]['altitude']) ||
			        ($theEphemerides15[$i]['altitude']==$theEphemerides1[1]['altitude'])))
			      $colorclass="ephemeridesgreen";
			  }
	      else
	        if(($theEphemerides15[$i]['altitude']!='-') && ($theEphemerides15[$i]['altitude']!='-') && 
	           (($theEphemerides15[$i]['altitude']==$theEphemerides1[$i]['altitude']) ||
			        ($theEphemerides15[$i]['altitude']==$theEphemerides1[$i+1]['altitude'])))
			      $colorclass="ephemeridesgreen";
				echo"<td class=\"centered ".$colorclass."\">".$theEphemerides15[$i]['altitude']."</td>";
			}
			$colorclass="";
			if(($theEphemerides1[1]['altitude']!='-') && ($theEphemerides15[1]['altitude']!='-') &&
			       (($theEphemerides1[1]['altitude']==$theEphemerides15[1]['altitude']) ||
			        ($theEphemerides1[1]['altitude']==$theEphemerides15[12]['altitude'])))
			      $colorclass="ephemeridesgreen";
			echo"<td class=\"centered ".$colorclass."\">".$theEphemerides1[1]['altitude']."</td>";
			echo "</tr>";
			echo "<tr class=\"type10\">";
			echo "<td class=\"centered\">".LangTransit."</td>";
			for($i=1;$i<13;$i++)
			{ $colorclass="";
			  if((date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"])!="00:00") && $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides1[$i]['transit'],date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"]+$theTimeDifference1[$i]),date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_begin"]+$theTimeDifference1[$i])))
			    $colorclass="ephemeridesgreen";
			  elseif((date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"])!="00:00") && $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides1[$i]['transit'],date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"]+$theTimeDifference1[$i]),date("H:i", $theNightEphemerides1[$i]["nautical_twilight_begin"]+$theTimeDifference1[$i])))
			    $colorclass="ephemeridesyellow";  
			  echo"<td class=\"centered ".$colorclass."\">".$theEphemerides1[$i]['transit']."</td>";
			  $colorclass="";
			  if((date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])!="00:00") && $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides15[$i]['transit'],date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"]+$theTimeDifference15[$i]),date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_begin"]+$theTimeDifference15[$i])))
			    $colorclass="ephemeridesgreen";
			  elseif((date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])!="00:00") && $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides15[$i]['transit'],date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"]+$theTimeDifference15[$i]),date("H:i", $theNightEphemerides15[$i]["nautical_twilight_begin"]+$theTimeDifference15[$i])))
			    $colorclass="ephemeridesyellow";  
			  echo"<td class=\"centered ".$colorclass."\">".$theEphemerides15[$i]['transit']."</td>";
			}
      $colorclass="";
			if((date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"])!="00:00") && $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides1[1]['transit'],date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"]+$theTimeDifference1[1]),date("H:i", $theNightEphemerides1[1]["astronomical_twilight_begin"]+$theTimeDifference1[1])))
			  $colorclass="ephemeridesgreen";
			elseif((date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"])!="00:00") && $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides1[1]['transit'],date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"]+$theTimeDifference1[1]),date("H:i", $theNightEphemerides1[1]["nautical_twilight_begin"]+$theTimeDifference1[1])))
			  $colorclass="ephemeridesyellow";  
			echo"<td class=\"centered ".$colorclass."\">".$theEphemerides1[1]['transit']."</td>";
			echo "</tr>";
			echo "<tr class=\"type20\">";
			echo "<td class=\"centered\">".LangAstroNight."</td>";
			for($i=1;$i<13;$i++)
			{ echo"<td class=\"centered\">".
			  ((date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"])!="00:00")
			   ?date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"]+$theTimeDifference1[$i])."<br />-<br />".date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_begin"]+$theTimeDifference1[$i])
			   :"-")."</td>";
			  echo"<td class=\"centered\">".
			  ((date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"])!="00:00")
			   ?date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"]+$theTimeDifference15[$i])."<br />-<br />".date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_begin"]+$theTimeDifference15[$i])
			   :"-")."</td>";
			}
			echo"<td class=\"centered\">".
			  ((date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"])!="00:00")
			   ?date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"]+$theTimeDifference1[1])."<br />-<br />".date("H:i", $theNightEphemerides1[1]["astronomical_twilight_begin"]+$theTimeDifference1[1])
			   :"-")."</td>";
			echo "</tr>";
			echo "<tr class=\"type20\">";
			echo "<td class=\"centered\">".LangNauticalNight."</td>";
			for($i=1;$i<13;$i++)
			{ echo"<td class=\"centered\">".
			  ((date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"])!="00:00")
			   ?date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"]+$theTimeDifference1[$i])."<br />-<br />".date("H:i", $theNightEphemerides1[$i]["nautical_twilight_begin"]+$theTimeDifference1[$i])
			   :"-")."</td>";
			  echo"<td class=\"centered\">".
			  ((date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])!="00:00")
			   ?date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"]+$theTimeDifference15[$i])."<br />-<br />".date("H:i", $theNightEphemerides15[$i]["nautical_twilight_begin"]+$theTimeDifference15[$i])
			   :"-")."</td>";
			}
			echo"<td class=\"centered\">".
			 ((date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"])!="00:00")
			  ?date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"]+$theTimeDifference1[1])."<br />-<br />".date("H:i", $theNightEphemerides1[1]["nautical_twilight_begin"]+$theTimeDifference1[1])
			  :"-")."</td>";
			echo "</tr>";
			echo "<tr class=\"type20\">";
			echo "<td class=\"centered\">".LangObjectRiseSet."</td>";
			for($i=1;$i<13;$i++)
			{ $colorclass="";
				if($theEphemerides1[$i]['rise']=='-')
			  { if((date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"])!="00:00"))
			      $colorclass="ephemeridesgreen";
			    else if ((date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"])!="00:00"))
			      $colorclass="ephemeridesyellow";
			  }
			  if((date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"])!="00:00") && 
		            $objUtil->checkNightHourMinutePeriodOverlap($theEphemerides1[$i]['rise'],$theEphemerides1[$i]['set'],date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"]+$theTimeDifference1[$i]),date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_begin"]+$theTimeDifference1[$i]))
			  			    )
			    $colorclass="ephemeridesgreen";
			  else if((date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"])!="00:00")&&
		            $objUtil->checkNightHourMinutePeriodOverlap($theEphemerides1[$i]['rise'],$theEphemerides1[$i]['set'],date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"]+$theTimeDifference1[$i]),date("H:i", $theNightEphemerides1[$i]["nautical_twilight_begin"]+$theTimeDifference1[$i]))
			  			    )
			    $colorclass="ephemeridesyellow";
			  echo"<td class=\"centered ".$colorclass."\">".($theEphemerides1[$i]['rise']=='-'?"-":$theEphemerides1[$i]['rise']."<br />-<br />".$theEphemerides1[$i]['set'])."</td>";
				$colorclass="";
			  if($theEphemerides15[$i]['rise']=='-')
			  { if((date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"])!="00:00"))
			      $colorclass="ephemeridesgreen";
			    else if ((date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])!="00:00"))
			      $colorclass="ephemeridesyellow";
			  }
			  else if((date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"])!="00:00") && 
		            $objUtil->checkNightHourMinutePeriodOverlap($theEphemerides15[$i]['rise'],$theEphemerides15[$i]['set'],date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"]+$theTimeDifference15[$i]),date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_begin"]+$theTimeDifference15[$i]))
			  			    )
			    $colorclass="ephemeridesgreen";
			  else if((date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])!="00:00")&&
		            $objUtil->checkNightHourMinutePeriodOverlap($theEphemerides15[$i]['rise'],$theEphemerides15[$i]['set'],date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"]+$theTimeDifference15[$i]),date("H:i", $theNightEphemerides15[$i]["nautical_twilight_begin"]+$theTimeDifference15[$i]))
			  			    )
			    $colorclass="ephemeridesyellow";
			  echo"<td class=\"centered ".$colorclass."\">".($theEphemerides15[$i]['rise']=="-"?"-":$theEphemerides15[$i]['rise']."<br />-<br />".$theEphemerides15[$i]['set'])."</td>";
			  
			}
      $colorclass="";
			if($theEphemerides1[1]['rise']=='-')
		  { if((date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"])!="00:00"))
		      $colorclass="ephemeridesgreen";
		    else if ((date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"])!="00:00"))
		      $colorclass="ephemeridesyellow";
		  }
		  else if((date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"])!="00:00") && 
		          $objUtil->checkNightHourMinutePeriodOverlap($theEphemerides1[1]['rise'],$theEphemerides1[1]['set'],date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"]+$theTimeDifference1[1]),date("H:i", $theNightEphemerides1[1]["astronomical_twilight_begin"]+$theTimeDifference1[1]))
		  		    )
		    $colorclass="ephemeridesgreen";
		  else if((date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"])!="00:00")&&
		          $objUtil->checkNightHourMinutePeriodOverlap($theEphemerides1[1]['rise'],$theEphemerides1[1]['set'],date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"]+$theTimeDifference1[1]),date("H:i", $theNightEphemerides1[1]["nautical_twilight_begin"]+$theTimeDifference1[1]))
		          )
		    $colorclass="ephemeridesyellow";
			echo"<td class=\"centered ".$colorclass."\">".($theEphemerides1[1]['rise']=='-'?'-':$theEphemerides1[1]['rise']."<br />-<br />".$theEphemerides1[1]['set'])."</td>";
			echo "</tr>";
			echo "</table>";
			echo "<hr />";
			echo "</div>";
    }
	}
	if(!($imagesize))
	{ $maxcount=count($_SESSION['Qobj']);
		$max = 9999;
		
		if((array_key_exists('steps',$_SESSION))&&(array_key_exists("nearbyObjects",$_SESSION['steps'])))
		  $step=$_SESSION['steps']["nearbyObjects"];
		if(array_key_exists('multiplepagenr',$_GET))
		  $min = ($_GET['multiplepagenr']-1)*$step;
		elseif(array_key_exists('multiplepagenr',$_POST))
		  $min = ($_POST['multiplepagenr']-1)*$step;
		elseif(array_key_exists('min',$_GET))
		  $min=$_GET['min'];
		else
		  $min = 0;
		
		$link = $baseURL.'index.php?indexAction=detail_object&amp;object='.urlencode($_GET['object']).'&amp;zoom='.$objUtil->checkGetKey('zoom',30).'&amp;SID=Qobj';
		
		$content1 ="<h4>";
		if(array_key_exists('viewobjectobjectsnearby',$_GET))
	    $viewobjectobjectsnearby=$_GET['viewobjectobjectsnearby'];
	  elseif(array_key_exists('viewobjectobjectsnearby',$_COOKIE))
	    $viewobjectobjectsnearby=$_COOKIE['viewobjectobjectsnearby'];
	  else
	    $viewobjectobjectsnearby='show';
		if($viewobjectobjectsnearby=="show")
		  $content1.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['object']).'&amp;zoom='.$objUtil->checkGetKey("zoom",30).'&amp;SID=Qobj&amp;viewobjectobjectsnearby=hidden'."\" title=\"".ReportEpehemeridesForHide."\">-</a> ";
		else
		  $content1.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($_GET['object']).'&amp;zoom='.$objUtil->checkGetKey("zoom",30).'&amp;SID=Qobj&amp;viewobjectobjectsnearby=show'."\" title=\"".ReportEpehemeridesForHide."\">+</a> ";
		$content1.=$_GET['object'];
		if(count($_SESSION['Qobj'])>2)
		 $content1.=' '.LangViewObjectAndNearbyObjects.' '.(count($_SESSION['Qobj'])-1).' '.LangViewObjectNearbyObjects;
		elseif(count($_SESSION['Qobj'])>1)
		 $content1.=' '.LangViewObjectAndNearbyObjects.' '.(count($_SESSION['Qobj'])-1).' '.LangViewObjectNearbyObject;
		else
		 $content1.=' '.LangViewObjectNoNearbyObjects;
		$content1.="</h4>";
		list($min,$max,$content2)=$objUtil->printNewListHeader3($_SESSION['Qobj'],$link ,$min,$step);
		$objPresentations->line(array($content1,$content2),"LR",array(50,50),30);
		if($viewobjectobjectsnearby=="show")
		{ $content1 ="<form action=\"".$link."\" method=\"get\"><div>";
			$content1.=LangViewObjectNearbyObjectsMoreLess .":&nbsp;";
		  $content1.="<select name=\"zoom\" onchange=\"submit();\">";
			if($objUtil->checkGetKey('zoom',30)=="180") $content1.=("<option selected=\"selected\" value=\"180\">3x3&deg;</option>"); else $content1.=("<option value=\"180\">3x3&deg;</option>"); 
			if($objUtil->checkGetKey('zoom',30)=="120") $content1.=("<option selected=\"selected\" value=\"120\">2x2&deg;</option>"); else $content1.=("<option value=\"120\">2x2&deg;</option>"); 
			if($objUtil->checkGetKey('zoom',30)=="60")  $content1.=("<option selected=\"selected\" value=\"60\">1x1&deg;</option>"); else $content1.=("<option value=\"60\">1x1&deg;</option>"); 
			if($objUtil->checkGetKey('zoom',30)=="30")  $content1.=("<option selected=\"selected\" value=\"30\">30x30'</option>"); else $content1.=("<option value=\"30\">30x30'</option>"); 
			if($objUtil->checkGetKey('zoom',30)=="15")  $content1.=("<option selected=\"selected\" value=\"15\">15x15'</option>"); else $content1.=("<option value=\"15\">15x15'</option>"); 
			if($objUtil->checkGetKey('zoom',30)=="10")  $content1.=("<option selected=\"selected\" value=\"10\">10x10'</option>"); else $content1.=("<option value=\"10\">10x10'</option>"); 
			if($objUtil->checkGetKey('zoom',30)=="5")   $content1.=("<option selected=\"selected\" value=\"5\">5x5'</option>"); else $content1.=("<option value=\"5\">5x5'</option>"); 
			$content1.="</select>";
			$content1.="<input type=\"hidden\" name=\"object\" value=\"".$_GET['object']."\" /> ";
			$content1.="<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" /> ";		
			$content1.="</div></form>";
			$content2="";
			$content2=$objUtil->printStepsPerPage3($link,"nearbyObjects",$step);
			$objPresentations->line(array($content1,$content2),"LR",array(50,50),25);
			echo "<hr />";
			if($max>count($_SESSION['Qobj']))
			  $max=count($_SESSION['Qobj']);
			$_GET['min']=$min;
			$_GET['max']=$max;
			if($FF)
			{ echo "<script type=\"text/javascript\">";
			  echo "theResizeElement='obj_list';";
			  echo "theResizeSize=75;";
			  echo "</script>";
			}
			$objObject->showObjects($link, $min, $max,$_GET['object'],0,$step,'','view_object');
		}
		echo "<hr />";
		$content =LangExecuteQueryObjectsMessage4."&nbsp;";
		$content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objects.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4a)."&nbsp;-&nbsp;";
		$content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectnames.pdf?SID=Qobj",LangExecuteQueryObjectsMessage4b)."&nbsp;-&nbsp;";
		$content.=$objPresentations->promptWithLinkText(LangListQueryObjectsMessage14,LangListQueryObjectsMessage15,$baseURL."objectsDetails.pdf?SID=Qobj&amp;sort=".$_SESSION['QobjSort'],LangExecuteQueryObjectsMessage4c)."&nbsp;-&nbsp;";
		$content.="<a href=\"".$baseURL."objects.argo?SID=Qobj\">".LangExecuteQueryObjectsMessage8."</a>&nbsp;-&nbsp;";
		$content.="<a href=\"".$baseURL."objects.csv?SID=Qobj\" >".LangExecuteQueryObjectsMessage6."</a>";;
	  //if($loggedUser)
	  { $content.="&nbsp;-&nbsp;<a href=\"".$baseURL."index.php?indexAction=reportsLayout&amp;reportname=ReportQueryOfObjects&amp;reporttitle=ReportQueryOfObjects&amp;SID=Qobj&amp;sort=".$_SESSION['QobjSort']."&amp;pdfTitle=Test\" >".ReportLink."</a>&nbsp;-&nbsp;";
      $content.="<a href=\"".$baseURL."index.php?indexAction=objectsSets"."\" rel=\"external\">".LangExecuteQueryObjectsMessage11."</a>";
	  }
	  $objPresentations->line(array($content),"L",array(),20);    
	}
	else
	{ $objPresentations->line(array("<h4>".LangViewDSSImageTitle.$object."&nbsp;(".$imagesize."&#39;&nbsp;x&nbsp;".$imagesize."&#39;)</h4>"),"L");
	  $imagelink = "http://archive.stsci.edu/cgi-bin/dss_search?"."v=poss2ukstu_red&amp;r=".urlencode($objUtil->checkRequestKey('raDSS')).".0&amp;d=".urlencode($objUtil->checkRequestKey('declDSS'))."&amp;e=J2000&amp;h=".$imagesize.".0&amp;w=".$imagesize."&amp;f=gif&amp;c=none&amp;fov=NONE&amp;v3=";
	  echo "<p class=\"centered DSSImage\"> <img class=\"centered DSSImage\" src=\"".$imagelink."\" alt=\"".$object."\" ></img> </p>";
	  echo "<p>&copy;&nbsp;<a href=\"http://archive.stsci.edu/dss/index.html\">STScI Digitized Sky Survey</a></p>";
	}
	echo "</div>";
	//============================================================================== Admin section permits to change object settings in DB remotely
	if(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == "yes")
	{ echo "<hr />";
	  echo "<form action=\"".$baseURL."index.php\" method=\"get\"><div>";
	  echo "<input type=\"hidden\" name=\"object\" value=\"" . $_GET['object'] . "\" />";
	  echo "<input type=\"hidden\" name=\"indexAction\" value=\"detail_object\" />";
	  echo "<select name=\"newaction\">";
	  echo "<option value=\"\">&nbsp;</option>";
	  echo "<option value=\"NewName\">" . LangObjectNewName . "</option>";
	  echo "<option value=\"NewAltName\">" . LangObjectNewAltName . "</option>";
	  echo "<option value=\"RemoveAltNameName\">" . LangObjectRemoveAltNameName . "</option>";
	  echo "<option value=\"NewPartOf\">" . LangObjectNewPartOf . "</option>";
	  echo "<option value=\"RemovePartOf\">" . LangObjectRemovePartOf . "</option>";
	  echo "<option value=\"RemoveAndReplaceObjectBy\">" . LangObjectRemoveAndReplaceObjectBy . "</option>";
	  echo "<option value=\"LangObjectSetRA\">" . LangObjectSetRA . "</option>";
	  echo "<option value=\"LangObjectSetDECL\">" . LangObjectSetDECL . "</option>";
	  echo "<option value=\"LangObjectSetCon\">" . LangObjectSetCon . "</option>";
	  echo "<option value=\"LangObjectSetType\">" . LangObjectSetType . "</option>";
	  echo "<option value=\"LangObjectSetMag\">" . LangObjectSetMag . "</option>";
	  echo "<option value=\"LangObjectSetSUBR\">" . LangObjectSetSUBR . "</option>";
	  echo "<option value=\"LangObjectSetDiam1\">" . LangObjectSetDiam1 . "</option>";
	  echo "<option value=\"LangObjectSetDiam2\">" . LangObjectSetDiam2 . "</option>";
	  echo "<option value=\"LangObjectSetPA\">" . LangObjectSetPA . "</option>";
	  echo "<option value=\"LangObjectSetDESC\">" . LangEditObjectDescription . "</option>";
	  echo "</select>";		
	  echo "<select name=\"newcatalog\">";
	  echo "<option value=\"\">&nbsp;</option>";
	  $catalogs = $objObject->getCatalogs();
	  while(list($key, $value) = each($catalogs))
	    echo "<option value=\"$value\">".$value."</option>";
	  echo "</select>";		
	  echo "<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"newnumber\" size=\"40\" value=\"\"/>";
	  echo "<input type=\"submit\" name=\"gonew\" value=\"Go\"/><br />";
	  echo "<a href=\"".$baseURL."index.php?indexAction=manage_csv_object\">" . LangNewObjectSubtitle1b . "</a><br />";
	  echo "</div></form>";
	}
}
?>
	