<?php
// setup_objects_query.php
// interface to query objects

require_once '../deepsky/content/data_get_objects.php';
$link="../deepsky/index.php?indexAction=query_objects";
while(list($key,$value)=each($_GET))
  $link.='&amp;'.$key.'='.$value;
if(array_key_exists('Qobj',$_SESSION) && (count($_SESSION['Qobj'])>0)) // valid result
  include("content/execute_query_objects.php"); 
else
{
  if($_SID)
	{
	  echo("<div id=\"main\">\n<h2>");
    echo LangSelectedObjectsTitle; // page title
    echo("</h2>\n");
    echo(LangExecuteQueryObjectsMessage2);		
    echo "<hr>";
  }
  else
	  $_SID=time();
  echo("<div id=\"main\">\n");
  echo("<h2>");
  
  echo LangQueryObjectsTitle;
  
  echo("</h2>\n");
  
  echo("<table width=\"100%\">\n");
  echo("<tr><td align=\"centre\" width=\"25%\">");
	echo("<form action=\"deepsky/index.php\">");
	echo("<input type=\"hidden\" name=\"indexAction\" value=\"query_objects\"></input>");
  echo("<input type=\"submit\" name=\"clear\" value=\"" . LangQueryObjectsButton2 . "\" />");
  echo("</form>");
  echo("</td>");
  echo("<td align=\"right\" width=\"25%\">" . LangSeen);
  echo("</td>");
  echo("<td>");
  echo("<form action=\"deepsky/index.php\" method=\"get\">\n");
  echo("<input type=\"hidden\" name=\"indexAction\" value=\"query_objects\"></input>");
  $_SID=(int) time();
  echo("<input type=\"hidden\" name=\"SID\" value=\"".$_SID."\"></input>");
  echo("<select name=\"seen\">");
  echo("<option selected value=\"D\">" . LangSeenDontCare . "</option>".
       "<option value=\"-\">" . LangNotSeen . "</option>");
  if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
  {
    echo("<option value=\"X\">" . LangSeenSomeoneElse . "</option>".
  		   "<option value=\"-X\">" . LangNotSeenByMeOrNotSeenAtAll . "</option>".
  		   "<option value=\"XY\">" . LangSeenByMeOrSomeoneElse . "</option>".
  		   "<option value=\"Y\">" . LangSeenByMe . "</option>");
  }
  echo("</select>");
  echo("</td>");
  echo("<td><input type=\"submit\" name=\"query\" value=\"" . LangQueryObjectsButton1 . "\" />\n</td>");
  echo("</tr>");
  
  echo("</table>");
  echo("<hr>");
  echo("<table>");
  
  echo("<tr>");
  // OBJECT NAME 
  echo("<td class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField1;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<select name=\"catalog\">\n");
  echo("<option value=\"\"></option>"); // empty field
  $catalogs = $objObject->getCatalogues(); // should be sorted
  while(list($key, $value) = each($catalogs))
    if($value==$catalog)
      echo("<option selected value=\"$value\">$value</option>\n");
  	else
      echo("<option value=\"$value\">$value</option>\n");
  echo("</select>\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"255\" name=\"catNumber\" size=\"40\" value=\"" . $catNumber .  "\" />");
  echo("</td>");
  // ATLAS PAGE NUMBER
  echo("<td"); 
	if($pageError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField12; 
  echo("</td>\n<td width=\"25%\">\n");
  echo("<select name=\"atlas\">\n");
  while(list($key,$value)=each($objAtlas->atlasCodes))
	  if($key==$atlas) echo("<option selected value=\"" . $key . "\">".$value."</option>\n"); 
		else echo("<option value=\"" . $key . "\">".$value."</option>\n");
  echo("</select>\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"atlasPageNumber\" size=\"4\" value=\"" . $atlasPageNumber .  "\" />"); 
  echo("</td>");
  echo("</tr>");
  
  echo("<tr>");
  // CONSTELLATION
  echo("<td class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField2;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<select name=\"con\">\n");
  echo("<option value=\"\"></option>"); // empty field
  $constellations = $objObject->getConstellations(); // should be sorted
  while(list($key, $value) = each($constellations))
    $cons[$value] = $$value;
  asort($cons);
  reset($cons);
	if(array_key_exists('con',$_GET)) $con=$_GET['con']; else $con='';
  while(list($key, $value) = each($cons))
    if($value==$con)
      echo("<option selected value=\"$key\">".$value."</option>\n");
		else
      echo("<option value=\"$key\">".$value."</option>\n");
  echo("</select>\n"); 
  echo("</td>");
  // TYPE
  echo("<td class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField11;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<select name=\"type\">\n");
  echo("<option value=\"\"></option>"); // empty field
  $types = $objObject->getDsObjectTypes(); 
  while(list($key, $value) = each($types))
    $stypes[$value] = $$value;
  asort($stypes);
	if(array_key_exists('type',$_GET)) $type=$_GET['type']; else $type='';
  while(list($key, $value) = each($stypes))
    if($value==$type)
		  echo("<option selected value=\"$key\">".$value."</option>\n");
		else
		  echo("<option value=\"$key\">".$value."</option>\n");
  echo("</select>\n");
  echo("</td>"); 
  echo("</tr>");
  
  echo("<tr>");
  // MINIMUM DECLINATION
  echo("<td"); 
	if($minDeclDegreesError || $minDeclMinutesError || $minDeclSecondsError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField9;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"minDeclDegrees\" size=\"3\" value=\"" . $minDeclDegrees .  "\" />&nbsp;&deg;&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minDeclMinutes\" size=\"2\" value=\"" . $minDeclMinutes .  "\" />&nbsp;&#39;&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minDeclSeconds\" size=\"2\" value=\"" . $minDeclSeconds .  "\" />&nbsp;&quot;&nbsp;"); 
  echo("</td>");
  // MAXIMUM DECLINATION
  echo("<td"); 
	if($maxDeclDegreesError || $maxDeclMinutesError || $maxDeclSecondsError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField10;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"3\" name=\"maxDeclDegrees\" size=\"3\" value=\"" . $maxDeclDegrees .  "\" />&nbsp;&deg;&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxDeclMinutes\" size=\"2\" value=\"" . $maxDeclMinutes .  "\" />&nbsp;&#39;&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxDeclSeconds\" size=\"2\" value=\"" . $maxDeclSeconds .  "\" />&nbsp;&quot;&nbsp;");
  echo("</td>");
  echo("</tr>");
  
  echo("<tr>");
  // MINIMUM RIGHT ASCENSION
  echo("<td"); 
	if($minRAHoursError || $minRAMinutesError || $minRASecondsError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField7;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRAHours\" size=\"2\" value=\"" . $minRAHours .  "\" />&nbsp;h&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRAMinutes\" size=\"2\" value=\"" . $minRAMinutes .  "\" />&nbsp;m&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"minRASeconds\" size=\"2\" value=\"" . $minRASeconds .  "\" />&nbsp;s&nbsp;");
  echo("</td>");
    // MAXIMUM RIGHT ASCENSION
  echo("<td"); 
	if($maxRAHoursError || $maxRAMinutesError || $maxRASecondsError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField8;  
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRAHours\" size=\"2\" value=\"" . $maxRAHours .  "\" />&nbsp;h&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRAMinutes\" size=\"2\" value=\"" . $maxRAMinutes .  "\" />&nbsp;m&nbsp;");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"2\" name=\"maxRASeconds\" size=\"2\" value=\"" . $maxRASeconds .  "\" />&nbsp;s&nbsp;");
  echo("</td>");
  echo("</tr>");
  
	echo("<tr>");
  // MAGNITUDE BRIGHTER THAN
  echo("<td"); 
	if($maxMagError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField4;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxMag\" size=\"4\" value=\"" . $maxMag .  "\" />"); 
  echo("</td>");
  // MAGNITUDE LESSER THAN
  echo("<td"); 
	if($minMagError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField3;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" align=\"center\" maxlength=\"4\" name=\"minMag\" size=\"4\" value=\"" . $minMag .  "\" />");
  echo("</td>");
  echo("</tr>\n");
  
  echo("<tr>");
  // SURFACE BRIGHTNESS BRIGHTER THAN
  echo("<td"); 
	if($maxSBError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField6;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxSB\" size=\"4\" value=\"" . $maxSB .  "\" />");
  echo("</td>");
  // SURFACE BRIGHTNESS LESSER THAN
  echo("<td"); 
	if($minSBError) echo(" style=\"color:red\"");
	echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField5;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minSB\" size=\"4\" value=\"" . $minSB .  "\" />");
  echo("</td>");
  echo("</tr>");
  
  echo("<tr>");
  // MINIMIM SIZE
  echo("<td"); 
  if($minSizeError) echo(" style=\"color:red\"");
  echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField13;
  echo("</td>\n<td>\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minSize\" size=\"4\" value=\"" . $minSize . "\" />");
  echo("&nbsp;&nbsp;<select name=\"size_min_units\">");
  if($size_min_units=="min") echo("<option selected value=\"min\">" . LangNewObjectSizeUnits1 . "</option>"); else echo("<option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>");
  if($size_min_units=="sec") echo("<option selected value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>"); else echo("<option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>");
  echo("</select>\n</td>");
  // MAXIMUM SIZE
  echo("<td"); 
  if($maxSizeError) echo(" style=\"color:red\"");
  echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
  echo LangQueryObjectsField14;
  echo("</td>\n<td width=\"25%\">\n");
  echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxSize\" size=\"4\" value=\"" . $maxSize . "\" />");
  echo("&nbsp;&nbsp;<select name=\"size_max_units\">");
  if($size_max_units=="min") echo("<option selected value=\"min\">" . LangNewObjectSizeUnits1 . "</option>"); else echo("<option value=\"min\">" . LangNewObjectSizeUnits1 . "</option>");
  if($size_max_units=="sec") echo("<option selected value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>"); else echo("<option value=\"sec\">" . LangNewObjectSizeUnits2 . "</option>");
  echo("</select>\n</td>");
  echo("</tr>");

	if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
	{  
    echo("<tr>");
    // MINIMUM CONTRAST RESERVE
    echo("<td"); 
    if($minContrastError) echo(" style=\"color:red\"");
    echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
    echo LangQueryObjectsField18;
    echo("</td>\n");
    echo("<td width=\"25%\">\n");
    echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"minContrast\" size=\"4\" value=\"" . $minContrast .  "\" />");
    echo("</td>");
    // MAXIMUM CONTRAST RESERVE
    echo("<td"); 
    if($maxContrastError) echo(" style=\"color:red\"");
    echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
    echo LangQueryObjectsField17;
    echo("</td>\n<td>\n");
    echo("<input type=\"text\" class=\"inputfield\" maxlength=\"4\" name=\"maxContrast\" size=\"4\" value=\"" . $maxContrast .  "\" />");
    echo("</td>");
    echo("</tr>");

    echo("<tr>");
    // IN LIST
    echo("<td"); 
    if($listError) echo(" style=\"color:red\"");
    echo(" class=\"fieldname\" align=\"right\" width=\"300px\">");
    echo LangQueryObjectsField19;
    echo("</td>\n<td width=\"25%\">\n");
    echo("<select name=\"inList\">\n");
    echo("<option value=\"\"></option>"); // empty field
    $lists = $objList->getLists(); 
    while(list($key, $value) = each($lists))
      if($value==$inList)
        echo("<option selected value=\"$value\">$value</option>\n");
    	else
        echo("<option value=\"$value\">$value</option>\n");
    echo("</select>\n");
    echo("</td>");
    // NOT IN LIST
  /*
    echo("<td"); 
    if($listError) echo(" style=\"color:red\"");
    echo(" class=\"fieldname\" align=\"right\" width=\"25%\">");
    echo LangQueryObjectsField20;
    echo("</td>\n<td width=\"25%\">\n");
    echo("<select name=\"notInList\">\n");
    echo("<option value=\"\"></option>"); // empty field
    $lists = $objList->getLists(); 
    while(list($key, $value) = each($lists))
      if($value==$notInList)
        echo("<option selected value=\"$value\">$value</option>\n");
    	else
        echo("<option value=\"$value\">$value</option>\n");
    echo("</select>\n");
    echo("</td>");
  */
    echo("</tr>");
  }
  echo("</table>");
}
echo "</div>";
echo "</div>";
echo "</body>";
echo "</html>";
?>
