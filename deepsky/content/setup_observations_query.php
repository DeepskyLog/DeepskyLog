<?php
function setup_observations_query()
{ global $baseURL, $loggedUser, $allLanguages, $usedLanguages, $usedLanguages,
         $objPresentations, $objUtil, $objObserver, $objAtlas, $objObject, $objInstrument, $objLocation;
  $QobsParamsCount=0;
	if(array_key_exists('QobsParams',$_SESSION))
    if(!(($_SESSION['QobsParams']['mindate']==date('Ymd', strtotime('-1 year')))&&($_SESSION['QobsParams']['catalog']=='%')))
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
	echo "<script type=\"text/javascript\" src=\"".$baseURL."deepsky/content/setup_observations_query.js\"></script>";
	$content2.="&nbsp;".'<input type="button" onclick="clearFields();" value="'.LangQueryObservationsButton2.'"/>';
  $objPresentations->line(array("<h4>".LangQueryObservationsTitle."</h4>",$content,$content1,$content2),"LRLL",array(20,20,40,20),30);
	echo "<hr />";
  	echo "<table width=\"100%\">";
	// OBJECT NAME 
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangViewObservationField1."</td>";
		echo "<td>";
		$catalogs = $objObject->getCatalogs();
		if(($catalog=$objUtil->checkGetKey('catalog'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $catalog=$_SESSION['QobsParams']['catalog'];
		echo "<select id=\"catalog\" name=\"catalog\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		while(list($key, $value) = each($catalogs))
		  echo "<option".(($value==$catalog)?" selected=\"selected\"":"")." value=\"".$value."\">".$value."</option>";
		echo "</select>";
	  if(($catNumber=$objUtil->checkGetKey('number'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $catNumber=$_SESSION['QobsParams']['number'];
		echo "<input id=\"number\" name=\"number\" type=\"text\" class=\"inputfield\" maxlength=\"255\" size=\"40\" value=\"".$catNumber."\" />";
		echo "</td>";
	// ATLAS PAGE NUMBER
		echo "<td class=\"fieldname\">".LangQueryObjectsField12."</td>";
		echo "<td>";
		if(($atlas=$objUtil->checkGetKey('atlas'))=='')
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
	  if(($con=$objUtil->checkGetKey('con'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $con=$_SESSION['QobsParams']['con'];	
		echo "<select id=\"con\" name=\"con\" class=\"inputfield\">";
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
	  if(($maxMag=$objUtil->checkGetKey('maxmag'))=='')
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
	  if(($minMag=$objUtil->checkGetKey('minmag'))=='')
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
	  if(($minSB=$objUtil->checkGetKey('minsb'))=='')
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
	  if(($maxSB=$objUtil->checkGetKey('maxsb'))=='')
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
		echo "<tr>";
		echo "<td class=\"fieldname\">";
		echo "<a href=\"#\" onclick=\"cal.showNavigationDropdowns();
		                              cal.setReturnFunction('SetMultipleValuesTillDate');
																  cal.showCalendar('TillDateAnchor');
		                              return false;\" 
											 name=\"TillDateAnchor\" 
											 id=\"TillDateAnchor\">" . LangTillDate . "</a>"; 
		echo "</td>";
		echo "<td>";
	  $maxday=$objUtil->checkGetKey('maxday');
	  if($maxday=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $maxday=substr($_SESSION['QobsParams']['maxdate'],-2);
		echo "<input id=\"maxday\" name=\"maxday\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$maxday."\" />";
		echo "&nbsp;";
	  $maxmonth=$objUtil->checkGetKey('maxmonth');
	  if($maxmonth=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $maxmonth=substr($_SESSION['QobsParams']['maxdate'],-4,2);
		echo "<select id=\"maxmonth\" name=\"maxmonth\" class=\"inputfield\">
		             <option value=\"\">-----</option>
		             <option".($maxmonth=='01'?' selected="selected"':"")." value=\"01\">" . LangNewObservationMonth1 . "</option>
		             <option".($maxmonth=='02'?' selected="selected"':"")." value=\"02\">" . LangNewObservationMonth2 . "</option>
		             <option".($maxmonth=='03'?' selected="selected"':"")." value=\"03\">" . LangNewObservationMonth3 . "</option>
		             <option".($maxmonth=='04'?' selected="selected"':"")." value=\"04\">" . LangNewObservationMonth4 . "</option>
		             <option".($maxmonth=='05'?' selected="selected"':"")." value=\"05\">" . LangNewObservationMonth5 . "</option>
		             <option".($maxmonth=='06'?' selected="selected"':"")." value=\"06\">" . LangNewObservationMonth6 . "</option>
		             <option".($maxmonth=='07'?' selected="selected"':"")." value=\"07\">" . LangNewObservationMonth7 . "</option>
		             <option".($maxmonth=='08'?' selected="selected"':"")." value=\"08\">" . LangNewObservationMonth8 . "</option>
		             <option".($maxmonth=='09'?' selected="selected"':"")." value=\"09\">" . LangNewObservationMonth9 . "</option>
		             <option".($maxmonth=='10'?' selected="selected"':"")." value=\"10\">" . LangNewObservationMonth10 . "</option>
		             <option".($maxmonth=='11'?' selected="selected"':"")." value=\"11\">" . LangNewObservationMonth11 . "</option>
		             <option".($maxmonth=='12'?' selected="selected"':"")." value=\"12\">" . LangNewObservationMonth12 . "</option>
		             </select>";
		echo "&nbsp;";
	  $maxyear=$objUtil->checkGetKey('maxyear');
	  if($maxyear=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $maxyear=substr($_SESSION['QobsParams']['maxdate'],-8,4);
		echo "<input id=\"maxyear\" name=\"maxyear\" type=\"text\" class=\"inputfield\" maxlength=\"4\" size=\"4\" value=\"".$maxyear."\" />";
		echo "</td>";
	// MAXIMUM DIAMETER
		echo "<td class=\"fieldname\">".LangViewObservationField14."</td>";
		echo "<td>";
	  $maxdiameterunits=$objUtil->checkGetKey('maxdiameterunits');
	  if(($maxdiameter=$objUtil->checkGetKey('maxdiameter'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	    { $maxdiameter=$_SESSION['QobsParams']['maxdiameter'];
	      $maxdiameterunits="mm";
	    }
		echo "<input id=\"maxdiameter\" name=\"maxdiameter\" type=\"text\" class=\"inputfield\" maxlength=\"64\" size=\"10\" value=\"".$maxdiameter."\" />";
		echo "<select id=\"maxdiameterunits\" name=\"maxdiameterunits\" class=\"inputfield\">";
		echo "<option".($maxdiameterunits=='inch'?' selected="selected"':"").">inch</option>";
		echo "<option".($maxdiameterunits=='mm'?' selected="selected"':"").">mm</option>";
		echo "</select>";
		echo "</td>";
		echo "</tr>";
	echo "</table>";
	echo "<hr />" ;
	echo "<table>";
	// SITE 
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangViewObservationField4."</td>";
		echo "<td>";
		$sites = $objLocation->getSortedLocations('name');
	  $site=$objUtil->checkGetKey('site');
	  if($site=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $site=$_SESSION['QobsParams']['location'];
		echo "<select id=\"site\" name=\"site\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		while(list($key, $value) = each($sites))
		  if($key)
		    echo "<option".(($value==$site)?' selected="selected"':'')." value=\"".$value."\">".$objLocation->getLocationPropertyFromId($value,'name')."</option>";
		echo "</select>";
		echo "</td>";
		echo "<td>"."&nbsp;"."</td>";
		echo "<td>"."&nbsp;"."</td>"; 
		echo "</tr>";
	// MINIMUM Latitude
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangQueryObjectsField15."</td>";
		echo "<td>";
	  if(($minLatDegrees=$objUtil->checkGetKey('minLatDegrees'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['minLat']!==''))
	      $minLatDegrees=(int)($_SESSION['QobsParams']['minLat']);
	  if(($minLatMinutes=$objUtil->checkGetKey('minLatMinutes'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['minLat']!==''))
	      $minLatMinutes=(int)(abs($_SESSION['QobsParams']['minLat']*60) % 60);
	  if(($minLatSeconds=$objUtil->checkGetKey('minLatSeconds'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['minLat']!==''))
	      $minLatSeconds=round(abs($_SESSION['QobsParams']['minLat']*3600)) % 60;
		echo "<input id=\"minLatDegrees\" name=\"minLatDegrees\" type=\"text\" class=\"inputfield\" maxlength=\"3\" size=\"3\" value=\"".$minLatDegrees."\" />&nbsp;&deg;&nbsp;";
		echo "<input id=\"minLatMinutes\" name=\"minLatMinutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$minLatMinutes."\" />&nbsp;&#39;&nbsp;";
		echo "<input id=\"minLatSeconds\" name=\"minLatSeconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$minLatSeconds."\" />&nbsp;&quot;&nbsp;";
		echo "</td>" ;
	// MINIMUM LIMITING MAGNITUDE
		echo "<td class=\"fieldname\">".LangViewObservationField25."</td>";
		echo "<td>";
	  $minlimmag=$objUtil->checkGetKey('minlimmag');
	  if($minlimmag=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $minlimmag=$_SESSION['QobsParams']['minlimmag'];
		echo "<input id=\"minlimmag\" name=\"minlimmag\" type=\"text\" class=\"inputfield\" maxlength=\"3\" size=\"4\" value=\"".$minlimmag."\" />";
		echo "</td>";
		echo "</tr>";
	// MAXIMUM latitude
  	echo "<tr>";
		echo "<td class=\"fieldname\">".LangQueryObjectsField16."</td>";
		echo "<td>";
	  if(($maxLatDegrees=$objUtil->checkGetKey('maxLatDegrees'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxLat']!==''))
	      $maxLatDegrees=(int)($_SESSION['QobsParams']['maxLat']);
	  if(($maxLatMinutes=$objUtil->checkGetKey('maxLatMinutes'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxLat']!==''))
	      $maxLatMinutes=(int)(abs($_SESSION['QobsParams']['maxLat']*60) % 60);
	  if(($maxLatSeconds=$objUtil->checkGetKey('maxLatSeconds'))=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount)&&($_SESSION['QobsParams']['maxLat']!==''))
	      $maxLatSeconds=round(abs($_SESSION['QobsParams']['maxLat']*3600)) % 60;
		echo "<input id=\"maxLatDegrees\" name=\"maxLatDegrees\" type=\"text\" class=\"inputfield\" maxlength=\"3\" size=\"3\" value=\"".$maxLatDegrees."\" />&nbsp;&deg;&nbsp;";
		echo "<input id=\"maxLatMinutes\" name=\"maxLatMinutes\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$maxLatMinutes."\" />&nbsp;&#39;&nbsp;";
		echo "<input id=\"maxLatSeconds\" name=\"maxLatSeconds\" type=\"text\" class=\"inputfield\" maxlength=\"2\" size=\"2\" value=\"".$maxLatSeconds."\" />&nbsp;&quot;&nbsp;";
		echo "</td>" ;
	// MAXIMUM LIMITING MAGNITUDE
		echo "<td class=\"fieldname\">".LangViewObservationField26."</td>";
		echo "<td>";
	  $maxlimmag=$objUtil->checkGetKey('maxlimmag');
	  if($maxlimmag=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $maxlimmag=$_SESSION['QobsParams']['maxlimmag'];
		echo "<input id=\"maxlimmag\" name=\"maxlimmag\" type=\"text\" class=\"inputfield\" maxlength=\"3\" size=\"4\" value=\"".$maxlimmag."\" />";
		echo "</td>";
		echo "</tr>";
	// MINIMUM SEEING
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangViewObservationField27."</td>";
		echo "<td>";
	  $minseeing=$objUtil->checkGetKey('minseeing');
	  if($minseeing=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $minseeing=$_SESSION['QobsParams']['minseeing'];
		echo "<select id=\"minseeing\" name=\"minseeing\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
    echo "<option".(($minseeing==1)?' selected="selected"':'')." value=\"1\">".SeeingExcellent."</option>";  // EXCELLENT
	  echo "<option".(($minseeing==2)?' selected="selected"':'')." value=\"2\">".SeeingGood."</option>";	     // GOOD
    echo "<option".(($minseeing==3)?' selected="selected"':'')." value=\"3\">".SeeingModerate."</option>";	 // MODERATE
    echo "<option".(($minseeing==4)?' selected="selected"':'')." value=\"4\">".SeeingPoor."</option>";       // POOR
	  echo "<option".(($minseeing==5)?' selected="selected"':'')." value=\"5\">".SeeingBad."</option>" ;	     // BAD	
	  echo "</select>";
	  echo "</td>";
	// MAXIMUM SEEING
		echo "<td class=\"fieldname\">".LangViewObservationField28."</td>";
		echo "<td>";
	  $maxseeing=$objUtil->checkGetKey('maxseeing');
	  if($maxseeing=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $maxseeing=$_SESSION['QobsParams']['maxseeing'];
		echo "<select id=\"maxseeing\" name=\"maxseeing\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		echo "<option".(($maxseeing==1)?' selected="selected"':'')." value=\"1\">".SeeingExcellent."</option>";		// EXCELLENT
		echo "<option".(($maxseeing==2)?' selected="selected"':'')." value=\"2\">".SeeingGood."</option>";	      // GOOD
		echo "<option".(($maxseeing==3)?' selected="selected"':'')." value=\"3\">".SeeingModerate."</option>"; 		// MODERATE
		echo "<option".(($maxseeing==4)?' selected="selected"':'')." value=\"4\">".SeeingPoor."</option>";     		// POOR
		echo "<option".(($maxseeing==5)?' selected="selected"':'')." value=\"5\">".SeeingBad."</option>";		      // BAD
		echo "</select>";
		echo "</td>";
		echo "</tr>";
	echo "</table>";
	echo "<hr />";
	echo "<table>";
	// DRAWINGS
		echo "<tr>";
		echo "<td class=\"fieldname\">". LangQueryObservationsMessage1."</td>";
		echo "<td>";
	  $drawings=$objUtil->checkGetKey('drawings');
	  if($drawings=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $drawings=$_SESSION['QobsParams']['hasDrawing'];
	  echo "<input id=\"drawings\" name=\"drawings\" type=\"checkbox\" class=\"inputfield\" ".($drawings=='on'?' checked="on"':"")."/>";
		echo "</td>";
	// MINIMUM VISIBILITY
		echo "<td class=\"fieldname\">".LangViewObservationField23."</td>";
		echo "<td>";
	  $minvisibility=$objUtil->checkGetKey('minvisibility');
	  if($minvisibility=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $minvisibility=$_SESSION['QobsParams']['minvisibility'];
		echo "<select id=\"minvisibility\" name=\"minvisibility\" class=\"inputfield\">";
		echo "<option value=\"\">-----</option>";
		echo "<option".($minvisibility==1?' selected="selected"':'')." value=\"1\">".LangVisibility1."</option>";	// Very simple, prominent object
		echo "<option".($minvisibility==2?' selected="selected"':'')." value=\"2\">".LangVisibility2."</option>";	// Object easily percepted with direct vision
		echo "<option".($minvisibility==3?' selected="selected"':'')." value=\"3\">".LangVisibility3."</option>";	// Object perceptable with direct vision
	  echo "<option".($minvisibility==4?' selected="selected"':'')." value=\"4\">".LangVisibility4."</option>";	// Averted vision required to percept object
	  echo "<option".($minvisibility==5?' selected="selected"':'')." value=\"5\">".LangVisibility5."</option>";	// Object barely perceptable with averted vision
		echo "<option".($minvisibility==6?' selected="selected"':'')." value=\"6\">".LangVisibility6."</option>";	// Perception of object is very questionable
		echo "<option".($minvisibility==7?' selected="selected"':'')." value=\"7\">".LangVisibility7."</option>";	// Object definitely not seen
		echo "</select>";
		echo "</td>";
		echo "</tr>";
	echo("<tr>");
	// DESCRIPTION
	  echo "<td class=\"fieldname\">". LangQueryObservationsMessage2 . "</td>";
	  echo "<td>";
	  $description=$objUtil->checkGetKey('description');
	  if($description=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $description=$_SESSION['QobsParams']['description'];
	  echo "<input id=\"description\" name=\"description\" type=\"text\" class=\"inputfield\" maxlength=\"40\" size=\"35\" value=\"".$description."\" />";
	  echo "</td>";
	// MAXIMUM VISIBILITY
	  echo "<td class=\"fieldname\">".LangViewObservationField24."</td>";
	  echo "<td>";
	  $maxvisibility=$objUtil->checkGetKey('maxvisibility');
	  if($maxvisibility=='')
	    if(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
	      $maxvisibility=$_SESSION['QobsParams']['maxvisibility'];
	  echo "<select id=\"maxvisibility\" name=\"maxvisibility\" class=\"inputfield\">";
	  echo "<option value=\"\">-----</option>"; 
		echo "<option".($maxvisibility==1?' selected="selected"':'')." value=\"1\">".LangVisibility1."</option>";		// Very simple, prominent object
		echo "<option".($maxvisibility==2?' selected="selected"':'')." value=\"2\">".LangVisibility2."</option>";		// Object easily percepted with direct vision
		echo "<option".($maxvisibility==3?' selected="selected"':'')." value=\"3\">".LangVisibility3."</option>";		// Object perceptable with direct vision
		echo "<option".($maxvisibility==4?' selected="selected"':'')." value=\"4\">".LangVisibility4."</option>";		// Averted vision required to percept object
		echo "<option".($maxvisibility==5?' selected="selected"':'')." value=\"5\">".LangVisibility5."</option>";		// Object barely perceptable with averted vision
		echo "<option".($maxvisibility==6?' selected="selected"':'')." value=\"6\">".LangVisibility6."</option>";		// Perception of object is very questionable
		echo "<option".($maxvisibility==7?' selected="selected"':'')." value=\"7\">".LangVisibility7."</option>";		// Object definitely not seen		
		echo "</select>";
		echo "</td>";
		echo "<td>"."&nbsp;"."</td>";
		echo "</tr>";
	// LANGUAGES
		echo "<tr>";
		echo "<td class=\"fieldname\">".LangChangeVisibleLanguages."</td>";
		$j=1;
		$temp='';
		while(list($key,$value)=each($allLanguages))
		{ if($objUtil->checkRequestKey($key))
		    echo "<td><input id=\"".$key."\" name=\"".$key."\" type=\"checkbox\" ".(($objUtil->checkRequestKey($key))?"checked=\"checked\" ":"")." value=\"".$key."\" />".$value."</td>";
		  elseif(array_key_exists('QobsParams',$_SESSION)&&(count($_SESSION['QobsParams'])==$QobsParamsCount))
		    echo "<td><input id=\"".$key."\" name=\"".$key."\" type=\"checkbox\" ".((in_array($key,$_SESSION['QobsParams']['languages']))?"checked=\"checked\" ":"")." value=\"".$key."\" />".$value."</td>";
		  elseif($loggedUser)
		    echo "<td><input id=\"".$key."\" name=\"".$key."\" type=\"checkbox\" ".((in_array($key,$usedLanguages))?"checked=\"checked\" ":"")." value=\"".$key."\" />".$value."</td>";
		  else
		    echo "<td><input id=\"".$key."\"name=\"".$key."\" type=\"checkbox\" ".(($key==$_SESSION['lang'])?"checked=\"checked\" ":"")." value=\"".$key."\" />".$value."</td>";
		  if(!($j++%3))
		     echo "</tr><tr><td></td>"; 
	    $temp=$temp.$key."/";
		} 
		echo "</tr>";
	echo "</table>";
	echo "</div>";
	echo "</form>";
	echo "<input id=\"temp\" type=\"hidden\" value=\"".$temp."\" />";
	echo "<hr />";
	if($loggedUser)
	{ $content=LangStoredQueries."&nbsp;";
	  $content.='<select id="observerqueries" onchange="restoreQuery();"><option value="-----">-----</option></select>'.'&nbsp;';
	  $content.='<input id="savequeryas" type="button" value="'.LangSaveAs.'" onclick="saveObserverQueryAs();"/>'.'&nbsp;';
	  $content.='<input id="deletequery" type="button" value="'.LangRemove.'" class="hidden" onclick="removeQuery();"/>'.'&nbsp;';
		  $objPresentations->line(array($content),"L",array(100));
	}
	echo "</div>";
	echo '<script type="text/javascript">setobserverqueries();</script>';
}
setup_observations_query();	
?>
