<?php
	// setup_observations_query.php
	// interface to query observations

  $QobsParamsCount=0;
	if(array_key_exists('QobsParams',$_SESSION))
    if(!(($_SESSION['QobsParams']['mindate']==date('Ymd', strtotime('-1 year')))&&($_SESSION['QobsParams']['catalog']='%')))
      $QobsParamsCount=41;
	echo "	<script type=\"text/javascript\" src=\"".$baseURL."lib/javascript/CalendarPopupCC.js\"></script>";
	echo "	<script type=\"text/javascript\" >";
	echo "	var cal = new CalendarPopup();";
	echo "  function SetMultipleValuesFromDate(y,m,d)";
	echo "  {";
	echo "    document.getElementById('minday').value = d;";
	echo "    document.getElementById('minmonth').value = m;";
	echo "    document.getElementById('minyear').value = y;";													 
	echo "	}";
	echo "  function SetMultipleValuesTillDate(y,m,d)";
	echo "  {";
	echo "    document.getElementById('maxday').value = d;";
	echo "    document.getElementById('maxmonth').value = m;";
	echo "    document.getElementById('maxyear').value = y;";													 
	echo "	}";
	echo "	</script>";
	
	if($objUtil->checkGetKey('object'))
	  $entryMessage.=LangInstructionsNoObjectFound.$_GET['object'];
	$_SESSION['result'] = "";
	if(array_key_exists('atlas',$_GET)&&$_GET['atlas'])
	  $atlas=$_GET['atlas'];
	elseif($loggedUser)
	  $atlas=$objAtlas->atlasCodes[$objObserver->getObserverProperty($loggedUser,'standardAtlasCode', 'urano')];
	else
	  $atlas="";
	
	echo "<div id=\"main\">";
	echo "<form action=\"".$baseURL."index.php\" method=\"get\"><div>";
	echo "<input type=\"hidden\" name=\"indexAction\"   value=\"result_selected_observations\" />";
	echo "<input type=\"hidden\" name=\"title\"         value=\"".LangSelectedObservationsTitle2."\" />";
	echo "<input type=\"hidden\" name=\"sort\"          value=\"objectname\" />";
	echo "<input type=\"hidden\" name=\"sortdirection\" value=\"asc\" />";
	echo "<input type=\"hidden\" name=\"myLanguages\"   value=\"true\" />";
	$content="";
	$content1="";
	if($loggedUser)
	{ $content=LangSeen;
		$seen=$objUtil->checkGetKey('seen');
		if($seen=='')
		  if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
		    $seen=$_SESSION['QobsParams']['seen'];
	  $content1 ="<select id=\"seen\" name=\"seen\">";
	  $content1.="<option ".($seen=="D"?"selected=\"selected\"":"")." value=\"D\">" . LangSeenDontCare . "</option>";
	  $content1.="<option ".($seen=="X"?"selected=\"selected\"":"")." value=\"X\">" . LangSeenSomeoneElse . "</option>"."<option value=\"Y\">" . LangSeenByMe . "</option>";
	  $content1.="</select>";
	}
	$content2="<input type=\"submit\" name=\"query\" value=\"" . LangQueryObservationsButton1 . "\" />";
	$objPresentations->line(array("<h4>".LangQueryObservationsTitle."</h4>",$content,$content1,$content2),"LRLL",array(20,20,40,20),30);
	echo "<hr />";
	
	echo "<table width=\"100%\">";
	// OBJECT NAME 
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangViewObservationField1."</td>";
		echo "<td>";
		$catalogs = $objObject->getCatalogs();
		$catalog=$objUtil->checkGetKey('catalog');
	  if($catalog=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $catalog=$_SESSION['QobsParams']['catalog'];
		echo "<select id=\"catalog\" name=\"catalog\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		while(list($key, $value) = each($catalogs))
		  echo "<option".(($value==$catalog)?" selected=\"selected\"":"")." value=\"".$value."\">".$value."</option>";
		echo "</select>";
	  $catNumber=$objUtil->checkGetKey('number');
		if($catNumber=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $catNumber=$_SESSION['QobsParams']['number'];
		echo "<input id=\"number\" name=\"number\" type=\"text\" class=\"inputfield\" maxlength=\"255\" size=\"40\" value=\"".$catNumber."\" />";
		echo "</td>";
	// ATLAS PAGE NUMBER
		echo "<td class=\"fieldname\">".LangQueryObjectsField12."</td>";
		echo "<td>";
		$atlas=$objUtil->checkGetKey('atlas');
	  if($atlas=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $atlas=$_SESSION['QobsParams']['atlas'];	
		echo "<select id=\"atlas\" name=\"atlas\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		while(list($key,$value)=each($objAtlas->atlasCodes))
			echo "<option".(($key==$atlas)?" selected=\"selected\"":"")." value=\"" . $key . "\">".$value."</option>"; 
		echo "</select>";
		$atlasPageNumber=$objUtil->checkGetKey('atlasPageNumber');
	  if($atlasPageNumber=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $atlasPageNumber=$_SESSION['QobsParams']['atlasPageNumber'];
		echo "<input id=\"atlasPageNumber\" name=\"atlasPageNumber\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$atlasPageNumber."\" />";
		echo "</td>";
		echo "</tr>";
	// OBJECT CONSTELLATION
	  echo "<tr>";
		echo "<td class=\"fieldname\">".LangQueryObjectsField2."</td>";
		echo "<td>";
		$constellations = $objObject->getConstellations();
		while(list($key, $value) = each($constellations))
		  $cons[$value] = $GLOBALS[$value];
		asort($cons);
		reset($cons);
	  $con=$objUtil->checkGetKey('con');
	  if($con=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $con=$_SESSION['QobsParams']['con'];	
		echo "<select name=\"con\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>"; 
		while(list($key, $value) = each($cons))
		  echo "<option".(($key==$con)?" selected=\"selected\"":"")." value=\"".$key."\">".$value."</option>";
		echo "</select>";
		echo "</td>";
	// MINIMUM DECLINATION
		echo"<td class=\"fieldname\">".LangQueryObjectsField9."</td>";
		echo "<td>";
	  if(($minDeclDegrees=$objUtil->checkGetKey('minDeclDegrees'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['mindecl']!==''))
	      $minDeclDegrees=(int)($_SESSION['QobsParams']['mindecl']);
	  if(($minDeclMinutes=$objUtil->checkGetKey('minDeclMinutes'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['mindecl']!==''))
	      $minDeclMinutes=(int)(abs($_SESSION['QobsParams']['mindecl']*60) % 60);
	  if(($minDeclSeconds=$objUtil->checkGetKey('minDeclSeconds'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['mindecl']!==''))
	      $minDeclSeconds=round(abs($_SESSION['QobsParams']['mindecl']*3600)) % 60;
		echo "<input id=\"minDeclDegrees\" name=\"minDeclDegrees\" type=\"text\" class=\"inputfield\" maxlength=\"3\" size=\"3\" value=\"".$minDeclDegrees."\" />&nbsp;&deg;&nbsp;";
		echo "<input id=\"minDeclMinutes\" name=\"minDeclMinutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$minDeclMinutes."\" />&nbsp;&#39;&nbsp;";
		echo "<input id=\"minDeclSeconds\" name=\"minDeclSeconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$minDeclSeconds."\" />&nbsp;&quot;&nbsp;";
		echo "</td>";
		echo "</tr>";
	// OBJECT TYPE
	  echo "<tr>";
		echo "<td class=\"fieldname\">".LangQueryObjectsField11."</td>";
		echo "<td>";
		echo "<select id=\"type\" name=\"type\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		$types = $objObject->getDsObjectTypes();
		while(list($key, $value) = each($types))
		  $stypes[$value] = $GLOBALS[$value];
		asort($stypes);
	  $type=$objUtil->checkGetKey('type');
	  if($type=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $type=$_SESSION['QobsParams']['type'];
		while(list($key, $value) = each($stypes))
		  echo "<option  ".(($key==$type)?"selected=\"selected\" ":"")."value=\"".$key."\">".$value."</option>";
		echo "</select>";
		echo "</td>";
	// MAXIMUM DECLINATION
		echo "<td class=\"fieldname\">".LangQueryObjectsField10."</td>";
		echo "<td>";
	  if(($maxDeclDegrees=$objUtil->checkGetKey('maxDeclDegrees'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxdecl']!==''))
	      $maxDeclDegrees=(int)($_SESSION['QobsParams']['maxdecl']);
	  if(($maxDeclMinutes=$objUtil->checkGetKey('maxDeclMinutes'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxdecl']!==''))
	      $maxDeclMinutes=(int)(abs($_SESSION['QobsParams']['maxdecl']*60) % 60);
	  if(($maxDeclSeconds=$objUtil->checkGetKey('maxDeclSeconds'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxdecl']!==''))
	      $maxDeclSeconds=round(abs($_SESSION['QobsParams']['maxdecl']*3600)) % 60;
		echo "<input id=\"maxDeclDegrees\" name=\"maxDeclDegrees\" type=\"text\" class=\"inputfield\" maxlength=\"3\" size=\"3\" value=\"".$maxDeclDegrees."\" />&nbsp;&deg;&nbsp;";
		echo "<input id=\"maxDeclMinutes\" name=\"maxDeclMinutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$maxDeclMinutes."\" />&nbsp;&#39;&nbsp;";
		echo "<input id=\"maxDeclSeconds\" name=\"maxDeclSeconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$maxDeclSeconds."\" />&nbsp;&quot;&nbsp;";
		echo "</td>";
		echo "</tr>";
	// MAXIMUM MAGNITUDE
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangQueryObjectsField4."</td>";
		echo "<td>";
	  if(($maxMag=$objUtil->checkGetKey('maxMag'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $maxMag=$_SESSION['QobsParams']['maxmag'];
		echo "<input id=\"maxmag\" name=\"maxmag\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$maxMag."\" />";
		echo "</td>";
	// MINIMUM RIGHT ASCENSION
		echo "<td class=\"fieldname\">".LangQueryObjectsField7."</td>";
		echo "<td>";
	  if(($minRAHours=$objUtil->checkGetKey('minRAHours'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['minra']!==''))
	      $minRAHours=(int)($_SESSION['QobsParams']['minra']);
	  if(($minRAMinutes=$objUtil->checkGetKey('minRAMinutes'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['minra']!==''))
	      $minRAMinutes=(int)(abs($_SESSION['QobsParams']['minra']*60) % 60);
	  if(($minRASeconds=$objUtil->checkGetKey('minRASeconds'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['minra']!==''))
	      $minRASeconds=round(abs($_SESSION['QobsParams']['minra']*3600)) % 60;
		echo "<input id=\"minRAhours\" name=\"minRAhours\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$minRAHours."\" />&nbsp;h&nbsp;";
		echo "<input id=\"minRAminutes\" name=\"minRAminutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$minRAMinutes."\" />&nbsp;m&nbsp;";
		echo "<input id=\"minRAseconds\" name=\"minRAseconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$minRASeconds."\" />&nbsp;s&nbsp;";
		echo "</td>";
		echo "</tr>";	
	// MINIMUM MAGNITUDE
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangQueryObjectsField3."</td>";
		echo "<td>";
	  if(($minMag=$objUtil->checkGetKey('minMag'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $minMag=$_SESSION['QobsParams']['minmag'];
		echo "<input id=\"minmag\" name=\"minmag\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$minMag."\" />";
		echo "</td>";
	// MAXIMUM RIGHT ASCENSION
		echo "<td class=\"fieldname\">".LangQueryObjectsField8."</td>";
		echo "<td>";
	  if(($maxRAHours=$objUtil->checkGetKey('maxRAHours'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxra']!==''))
	      $maxRAHours=(int)($_SESSION['QobsParams']['maxra']);
	  if(($maxRAMinutes=$objUtil->checkGetKey('maxRAMinutes'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxra']!==''))
	      $maxRAMinutes=(int)(abs($_SESSION['QobsParams']['maxra']*60) % 60);
	  if(($maxRASeconds=$objUtil->checkGetKey('maxRASeconds'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxra']!==''))
	      $maxRASeconds=round(abs($_SESSION['QobsParams']['maxra']*3600)) % 60;
		echo "<input id=\"maxRAhours\" name=\"maxRAhours\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$maxRAHours."\" />&nbsp;h&nbsp;";
		echo "<input id=\"maxRAminutes\" name=\"maxRAminutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$maxRAMinutes."\" />&nbsp;m&nbsp;";
		echo "<input id=\"maxRAseconds\" name=\"maxRAseconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$maxRASeconds."\" />&nbsp;s&nbsp;";
		echo "</td>";
		echo "</tr>";	
	// MINIMIM SURFACE BRIGHTNESS
		echo "<tr>";
	  echo "<td class=\"fieldname\">".LangQueryObjectsField5."</td>";
		echo "<td>";
	  if(($minSB=$objUtil->checkGetKey('minSB'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $minSB=$_SESSION['QobsParams']['minsb'];
		echo "<input id=\"minsb\" name=\"minsb\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$minSB."\" />";
		echo "</td>";
	// MINIMIM SIZE
		echo "<td class=\"fieldname\">".LangQueryObjectsField13."</td>";
		echo "<td>";
	  $size_min_units=$objUtil->checkGetKey('size_min_units');
	  if(($minSize=$objUtil->checkGetKey('minsize'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	    { $minSize=$_SESSION['QobsParams']['mindiam1'];
	      $size_min_units="sec";
	    }
		echo "<input id=\"minsize\" name=\"minsize\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$minSize."\" />";
		echo "&nbsp;&nbsp;";
		echo "<select id=\"size_min_units\" name=\"size_min_units\" class=\"inputfield\">";
		echo "<option".(($size_min_units=="min")?" selected=\"selected\"":"")." value=\"min\">".LangNewObjectSizeUnits1."</option>";
		echo "<option".(($size_min_units=="sec")?" selected=\"selected\"":"")." value=\"sec\">".LangNewObjectSizeUnits2."</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>";
	// MAXIMUM SURFACE BRIGHTNESS
	  echo "<tr>";
		echo "<td class=\"fieldname\">".LangQueryObjectsField6."</td>";
		echo "<td>";
	  if(($maxSB=$objUtil->checkGetKey('maxSB'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $maxSB=$_SESSION['QobsParams']['maxsb'];
		echo "<input id=\"maxsb\" name=\"maxsb\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$maxSB."\" />";
		echo "</td>";
	// MAXIMUM SIZE
		echo "<td class=\"fieldname\">".LangQueryObjectsField14."</td>";
		echo "<td>";
	  $size_max_units=$objUtil->checkGetKey('size_max_units');
	  if(($maxSize=$objUtil->checkGetKey('maxsize'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	    { $maxSize=$_SESSION['QobsParams']['maxdiam1'];
	      $size_max_units="sec";
	    }
		echo "<input id=\"maxsize\" name=\"maxsize\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$maxSize."\" />";
		echo "&nbsp;&nbsp;";
		echo "<select id=\"size_max_units\" name=\"size_max_units\" class=\"inputfield\">";
		echo "<option".(($size_max_units=="min")?" selected=\"selected\"":"")." value=\"min\">" . LangNewObjectSizeUnits1 . "</option>";
		echo "<option".(($size_max_units=="sec")?" selected=\"selected\"":"")." value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>";
	echo "</table>";
	echo "<hr />";

	echo "<table style=\"width:100%\">";
	// OBSERVER 
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangViewObservationField2."</td>";
		echo "<td>";
	  $observer=$objUtil->checkGetKey('observer');
	  if($observer=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $observer=$_SESSION['QobsParams']['observer'];
		echo "<select id=\"observer\" name=\"observer\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		$obs = $objObserver->getPopularObserversByName();
		while(list($key, $value) = each($obs))
		   echo "<option".($key==$observer?' selected="selected"':'')." value=\"".$key."\">".$value."</option>";
		echo "</select>";
		echo "</td>";
	// INSTRUMENT 
		echo "<td class=\"fieldname\">".LangViewObservationField3."</td>";
		echo "<td>";
		$inst = $objInstrument->getSortedInstrumentsList('name');
	  $instrument=$objUtil->checkGetKey('instrument');
	  if($instrument=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $instrument=$_SESSION['QobsParams']['instrument'];
		echo "<select id=\"instrument\" name=\"instrument\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		while(list($key,$value)=each($inst))
		  echo "<option".($key==$instrument?' selected="selected"':'')." value=\"".$key."\">".$value."</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>";
	// MINIMUM DATE
		echo "<tr>";
		echo "<td class=\"fieldname\" align=\"right\" style=\"width:25%\">";
		echo "<a href=\"#\" onclick=\"cal.showNavigationDropdowns();
		                             cal.setReturnFunction('SetMultipleValuesFromDate');
																 cal.showCalendar('FromDateAnchor');
		                             return false;\" 
											 name=\"FromDateAnchor\" 
											 id=\"FromDateAnchor\">".LangFromDate."</a>"; 
		echo "</td>";
		echo "<td>";
	  $minday=$objUtil->checkGetKey('minday');
	  if($minday=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $minday=substr($_SESSION['QobsParams']['mindate'],-2);
    echo "<input id=\"minday\" name=\"minday\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$minday."\" />";
		echo "&nbsp;";
	  $minmonth=$objUtil->checkGetKey('minmonth');
	  if($minmonth=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $minmonth=substr($_SESSION['QobsParams']['mindate'],-4,2);
		echo "<select id=\"minmonth\" name=\"minmonth\" class=\"inputfield\">
		             <option value=\"\">-----</option>
		             <option".($minmonth=='01'?' selected="selected"':"")." value=\"01\">" . LangNewObservationMonth1 . "</option>
		             <option".($minmonth=='02'?' selected="selected"':"")." value=\"02\">" . LangNewObservationMonth2 . "</option>
		             <option".($minmonth=='03'?' selected="selected"':"")." value=\"03\">" . LangNewObservationMonth3 . "</option>
		             <option".($minmonth=='04'?' selected="selected"':"")." value=\"04\">" . LangNewObservationMonth4 . "</option>
		             <option".($minmonth=='05'?' selected="selected"':"")." value=\"05\">" . LangNewObservationMonth5 . "</option>
		             <option".($minmonth=='06'?' selected="selected"':"")." value=\"06\">" . LangNewObservationMonth6 . "</option>
		             <option".($minmonth=='07'?' selected,="selected"':"")." value=\"07\">" . LangNewObservationMonth7 . "</option>
		             <option".($minmonth=='08'?' selected="selected"':"")." value=\"08\">" . LangNewObservationMonth8 . "</option>
		             <option".($minmonth=='09'?' selected="selected"':"")." value=\"09\">" . LangNewObservationMonth9 . "</option>
		             <option".($minmonth=='10'?' selected="selected"':"")." value=\"10\">" . LangNewObservationMonth10 . "</option>
		             <option".($minmonth=='11'?' selected="selected"':"")." value=\"11\">" . LangNewObservationMonth11 . "</option>
		             <option".($minmonth=='12'?' selected="selected"':"")." value=\"12\">" . LangNewObservationMonth12 . "</option>
		             </select>";
		echo "&nbsp;";
	  $minyear=$objUtil->checkGetKey('minyear');
	  if($minyear=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $minyear=substr($_SESSION['QobsParams']['mindate'],-8,4);
		echo "<input id=\"minyear\" name=\"minyear\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$minyear."\" />";
		echo "</td>";
	// MINIMUM DIAMETER
		echo "<td class=\"fieldname\">".LangViewObservationField13."</td>";
		echo "<td>";
	  $mindiameterunits=$objUtil->checkGetKey('mindiameterunits');
	  if(($mindiameter=$objUtil->checkGetKey('mindiameter'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	    { $mindiameter=$_SESSION['QobsParams']['mindiameter'];
	      $mindiameterunits="mm";
	    }
	  echo "<input id=\"mindiameter\" name=\"mindiameter\" type=\"text\" class=\"inputfield\" maxlength=\"64\" size=\"10\" value=\"".$mindiameter."\"/>";
		echo "<select id=\"mindiameterunits\" name=\"mindiameterunits\" class=\"inputfield\">";
		echo "<option".($mindiameterunits=='inch'?' selected="selected"':"").">inch</option>";
		echo "<option".($mindiameterunits=='mm'?' selected="selected"':"").">mm</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>";
		
	// MAXIMUM DATE
	echo("<tr>");
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo("<a href=\"#\" onclick=\"cal.showNavigationDropdowns();
	                              cal.setReturnFunction('SetMultipleValuesTillDate');
															  cal.showCalendar('TillDateAnchor');
	                              return false;\" 
										 name=\"TillDateAnchor\" 
										 id=\"TillDateAnchor\">" . LangTillDate . "</a>"); 
	echo("</td>");
	echo("<td>");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" name=\"maxday\" id=\"maxday\" value=\"\" />");
	echo("&nbsp;");
	echo("<select name=\"maxmonth\" id=\"maxmonth\" class=\"inputfield\">
	             <option value=\"\">-----</option>
	             <option value=\"1\">" . LangNewObservationMonth1 . "</option>
	             <option value=\"2\">" . LangNewObservationMonth2 . "</option>
	             <option value=\"3\">" . LangNewObservationMonth3 . "</option>
	             <option value=\"4\">" . LangNewObservationMonth4 . "</option>
	             <option value=\"5\">" . LangNewObservationMonth5 . "</option>
	             <option value=\"6\">" . LangNewObservationMonth6 . "</option>
	             <option value=\"7\">" . LangNewObservationMonth7 . "</option>
	             <option value=\"8\">" . LangNewObservationMonth8 . "</option>
	             <option value=\"9\">" . LangNewObservationMonth9 . "</option>
	             <option value=\"10\">" . LangNewObservationMonth10 . "</option>
	             <option value=\"11\">" . LangNewObservationMonth11 . "</option>
	             <option value=\"12\">" . LangNewObservationMonth12 . "</option>
	             </select>");
	echo("&nbsp;");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" name=\"maxyear\" id=\"maxyear\" value=\"\" />");
	echo("</td>");
	// MAXIMUM DIAMETER
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangViewObservationField14;
	echo("</td>
	      <td>
	      <input type=\"text\" class=\"inputfield\" maxlength=\"64\" name=\"maxdiameter\" size=\"10\" />
	      <select name=\"maxdiameterunits\" class=\"inputfield\"><option>inch</option><option>mm</option></select>
	      </td>");
	echo("</tr>");
	
	echo("</table>");
	echo("<hr />");
	echo("<table width=\"100%\">");
	
	
	echo("<tr>");
	// SITE 
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangViewObservationField4;
	echo("</td><td style=\"width:25%\">");
	echo("<select name=\"site\" class=\"inputfield\">");
	echo("<option value=\"\">-----</option>"); // empty field
	$sites = $objLocation->getSortedLocations('name');
	while(list($key, $value) = each($sites))
	  if($key != 0) // remove empty location in database
	    echo("<option value=\"$value\">".$objLocation->getLocationPropertyFromId($value,'name')."</option>");
	echo("</select>");
	echo("</td>");
	echo("<td style=\"width:25%\"> &nbsp; </td> <td style=\"width:25%\"> &nbsp;</td>"); 
	echo("</tr>");
	
	echo("<tr>");
	// MINIMUM Latitude
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangQueryObjectsField15;
	echo("</td><td style=\"width:25%\">");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"minLatDegrees\" size=\"3\" value=\"\" />&nbsp;&deg;&nbsp;");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minLatMinutes\" size=\"2\" value=\"\" />&nbsp;&#39;&nbsp;");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minLatSeconds\" size=\"2\" value=\"\" />&nbsp;&quot;&nbsp;");
	echo("</td>");
	// MINIMUM LIMITING MAGNITUDE
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangViewObservationField25;
	echo("</td><td style=\"width:25%\">");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"minlimmag\" size=\"4\" value=\"\" />");
	echo("</td>");
	echo("</tr>");
	
	echo("<tr>");
	// MAXIMUM latitude
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangQueryObjectsField16;
	echo("</td><td style=\"width:25%\">");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"maxLatDegrees\" size=\"3\" value=\"\" />&nbsp;&deg;&nbsp;");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxLatMinutes\" size=\"2\" value=\"\" />&nbsp;&#39;&nbsp;");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxLatSeconds\" size=\"2\" value=\"\" />&nbsp;&quot;&nbsp;");
	echo("</td>");
	// MAXIMUM LIMITING MAGNITUDE
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangViewObservationField26;
	echo("</td><td style=\"width:25%\">");
	echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"maxlimmag\" size=\"4\" value=\"\" />");
	echo("</td>");
	echo("</tr>");
	
	echo("<tr>");
	// MINIMUM SEEING
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangViewObservationField27;
	echo("</td><td style=\"width:25%\">");
	echo("<select name=\"minseeing\" class=\"inputfield\"><option value=\"\">-----</option>");
	// EXCELLENT
	echo("<option value=\"1\">".SeeingExcellent."</option>");
	// GOOD
	echo("<option value=\"2\">".SeeingGood."</option>");
	// MODERATE
	echo("<option value=\"3\">".SeeingModerate."</option>");
	// POOR
	echo("<option value=\"4\">".SeeingPoor."</option>");
	// BAD
	echo("<option value=\"5\">".SeeingBad."</option>");
	echo("</select></td>");
	// MAXIMUM SEEING
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangViewObservationField28;
	echo("</td><td style=\"width:25%\">");
	echo("<select name=\"maxseeing\" class=\"inputfield\"><option value=\"\">-----</option>");
	// EXCELLENT
	echo("<option value=\"1\">".SeeingExcellent."</option>");
	// GOOD
	echo("<option value=\"2\">".SeeingGood."</option>");
	// MODERATE
	echo("<option value=\"3\">".SeeingModerate."</option>");
	// POOR
	echo("<option value=\"4\">".SeeingPoor."</option>");
	// BAD
	echo("<option value=\"5\">".SeeingBad."</option>");
	echo("</select></td>");
	echo("</tr>");
	
	echo("</table>");
	echo("<hr />");
	echo("<table width=\"100%\">");
	echo "<tr>";
	// DRAWINGS
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">". LangQueryObservationsMessage1 . "</td>");
	echo("<td style=\"width:25%\"><input type=\"checkbox\" class=\"inputfield\" name=\"drawings\" /></td>");
	// MINIMUM VISIBILITY
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangViewObservationField23;
	echo("</td>
	      <td>
	      <select name=\"minvisibility\" class=\"inputfield\"><option value=\"\">-----</option>");
	// Very simple, prominent object
	echo("<option value=\"1\">".LangVisibility1."</option>");
	// Object easily percepted with direct vision
	echo("<option value=\"2\">".LangVisibility2."</option>");
	// Object perceptable with direct vision
	echo("<option value=\"3\">".LangVisibility3."</option>");
	// Averted vision required to percept object
	echo("<option value=\"4\">".LangVisibility4."</option>");
	// Object barely perceptable with averted vision
	echo("<option value=\"5\">".LangVisibility5."</option>");
	// Perception of object is very questionable
	echo("<option value=\"6\">".LangVisibility6."</option>");
	// Object definitely not seen
	echo("<option value=\"7\">".LangVisibility7."</option>");
	echo("</select></td>");
	echo("</tr>");
	
	echo("<tr>");
	// DESCRIPTION
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">". LangQueryObservationsMessage2 . "</td><td style=\"width:25%\">
	      <input type=\"text\" class=\"inputfield\" maxlength=\"40\" name=\"description\" size=\"35\" value=\"\" />&nbsp;
	      </td>");
	// MAXIMUM VISIBILITY
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo LangViewObservationField24;
	echo("</td>
	      <td>
	      <select name=\"maxvisibility\" class=\"inputfield\"><option value=\"\">-----</option>"); 
	      
	// Very simple, prominent object
	echo("<option value=\"1\">".LangVisibility1."</option>");
	// Object easily percepted with direct vision
	echo("<option value=\"2\">".LangVisibility2."</option>");
	
	// Object perceptable with direct vision
	echo("<option value=\"3\">".LangVisibility3."</option>");
	// Averted vision required to percept object
	echo("<option value=\"4\">".LangVisibility4."</option>");
	// Object barely perceptable with averted vision
	echo("<option value=\"5\">".LangVisibility5."</option>");
	// Perception of object is very questionable
	echo("<option value=\"6\">".LangVisibility6."</option>");
	// Object definitely not seen
	echo("<option value=\"7\">".LangVisibility7."</option>");
	echo("</select></td><td></td>");
	echo("</tr>");
	
	echo("<tr>");
	echo("<td class=\"fieldname\" align=\"right\" style=\"width:25%\">");
	echo(LangChangeVisibleLanguages);
	echo("</td>");
	$j=1;
	while(list($key,$value)=each($allLanguages))
	{ if($loggedUser)
	    echo "<td><input type=\"checkbox\" ".((in_array($key,$usedLanguages))?"checked=\"checked\" ":"")."name=\"".$key."\" value=\"".$key."\" />".$value."</td>";
	  else
	    echo "<td><input type=\"checkbox\" ".(($key==$_SESSION['lang'])?"checked=\"checked\" ":"")."name=\"".$key."\" value=\"".$key."\" />".$value."</td>";
	  if(!($j++%3))
	     echo "</tr><tr><td></td>"; 
	} 
	echo "</tr>";
echo "</table>";
echo "</div>";
echo "</form>";
echo "</div>";
?>
