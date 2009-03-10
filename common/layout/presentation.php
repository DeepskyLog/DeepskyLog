<?php
interface iPresentation
{ public  function br2dash($data); 
  public  function br2nl($data);                                                       // The opposite of nl2br
  public function decToString($decl,$web=1);
  public  function decToStringDSS($decl);                                              // returns html DSS decl coordinates eg 6+44 for 6°43'55''
  public  function presentationInt($value, $nullcontition='', $nullvalue='');          // if the null condtion is met, it returns the nullvalue, otherwise returns the value
  public  function presentationInt1($value, $nullcondition='', $nullvalue='');         // if the null condtion is met, it returns the nullvalue, otherwise returns the value formatted %1.1f
  public  function promptWithLink($prompt,$promptDefault,$javaLink,$text);             // displays an anchor link with $text as text, showing when clicked an inputbox with the question $prompt and $promptDefault answer, jumping to $javalink (java format) afterwards 
  public  function raToStringDSS($ra);                                         // returns html DSS ra coordinates eg 6+43+55 for 6h43m55s
  public  function searchAndLinkCatalogsInText($theText);                              // hyperlinks M, NGC, .. catalogs in a text
  
}
// function tableFieldnameFieldExplanation($name,$field,$explanation)                   // 3-item field line, containing the name of the field, the field value and the explanation
// function tableFieldnameField($name,$field)                                           // 2-item field line, containing the name of the field and the field value
// function tableFieldnameField2($name1,$field1,$name2,$field2)             // 4-item filed line, containing the line type, the 2 names and values of the dields, each 25% wide
// function tableFieldnameField3($name1,$field1,$name2,$field2,$name3,$field3, $type="")
// function tableMenuItem($link, $menuItem)                                             // Item for the left colums menus
// function tablePageTitle($title, $link, &$list, &$min, &$max)                         // TITLE in h2 and List Header 
// function tableSortHeader($header0, $link0)                                           // sorting header on table
// function tableSortInverseHeader($header0, $link0)                                    // inverse sorting header on table
// function tableTypeFieldnameField($type,$name,$field)                                 // 2-item type line, containing the name and the field, and formatted to the type
class Presentations implements iPresentation
{ public function br2dash($data) 
  { return preg_replace('!<br.*>!iU', "-", $data );
  }
  public function br2nl($data)  // The opposite of nl2br
  { return preg_replace('!<br.*>!iU', " ", $data );
  }
  public function decToString($decl,$web=1)
  { $sign =0;
    if($decl < 0)
    { $sign = -1;
      $decl = -$decl;
    }
    $decl_degrees = floor($decl);
    $subminutes = 60 * ($decl - $decl_degrees);
    $decl_minutes = round($subminutes);
    if($decl_minutes == 60)
    { $decl_minutes = 0;
      $decl_degrees++;
    }
    if($decl_degrees >= 0 && $decl_degrees <= 9)
      $decl_degrees = "0" . $decl_degrees;
    if ($sign == -1)
      $decl_degrees = "-" . $decl_degrees;
    else
    { if ($web == 1)
      { //$decl_degrees = "&nbsp;" . $decl_degrees; // add white space for overview locations
        $decl_degrees = $decl_degrees; // remove white space for object details
      }
      else
      { $decl_degrees = " " . $decl_degrees;
      }
    }
    if($decl_minutes <= 9)
    { $decl_minutes = "0" . $decl_minutes;
    } 
    if ($web == 1)
    { $d = "&deg;";
      $m = "&#39;";
    } 
    else
    { $d = "d";
      $m = "'";
    }
    return("$decl_degrees" .$d. "$decl_minutes" . $m);
  }
  public  function decToStringDSS($decl)
  { $sign=0;
    if($decl<0)
    { $sign=-1;
      $decl=-$decl;
    }
    $decl_degrees=floor($decl);
    $subminutes=60*($decl-$decl_degrees);
    $decl_minutes=round($subminutes);
    if($sign==-1)
    { $decl_minutes = "-".$decl_minutes;
      $decl_degrees = "-".$decl_degrees;
    }
    return("$decl_degrees"."&#43;"."$decl_minutes");
  }
  public  function presentationInt($value, $nullcontition='', $nullvalue='')
  { return (($value==$nullcontition)?$nullvalue:$value);
  }
  public  function presentationInt1($value, $nullcondition='', $nullvalue='')
  { return (($value==$nullcondition)?$nullvalue:sprintf("%1.1f",$value));
  }
  public function promptWithLink($prompt,$promptDefault,$javaLink,$text)
	{ echo "<a target=\"_top\" href=\"\" onclick=\"thetitle = prompt('".addslashes($prompt)."','".addslashes($promptDefault)."'); location.href='".$javaLink."&amp;pdfTitle='+thetitle; return false;\"	target=\"new_window\">".$text."</a>";
  }
  public  function raToStringDSS($ra)
  { $ra_hours=floor($ra);
    $subminutes=60*($ra - $ra_hours);
    $ra_minutes=floor($subminutes);
    $ra_seconds=round(60*($subminutes-$ra_minutes));
    return("$ra_hours"."&#43;"."$ra_minutes"."&#43;"."$ra_seconds");
  }
  public function searchAndLinkCatalogsInText($theText)
  { global $baseURL;
    $patterns[0]="/\s+(M)\s*(\d+)/";
		$replacements[0]="<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
		$patterns[1]= "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
		$replacements[1]="<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=NGC%20\\2\">NGC&nbsp;\\2</a>";
		$patterns[2]= "/(IC|Ic|ic)\s*(\d+)/";
		$replacements[2]="<a 	target=\"_top\" href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=IC%20\\2\">IC&nbsp;\\2</a>";
		$patterns[3]= "/(Arp|ARP|arp)\s*(\d+)/";
		$replacements[3]="<a target=\"_top\" href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=Arp%20\\2\">Arp&nbsp;\\2</a>";
		return preg_replace($patterns, $replacements, $theText);
  }
}
$objPresentations=new Presentations;
function tableFieldnameFieldExplanation($name,$field,$explanation, $type="")
{ echo "<tr ".$type.">";
  echo "<td class=\"fieldname\">".$name."</td>";
  echo "<td class=\"fieldvalue\">".$field."</td>";
  echo "<td class=\"fieldexplanation\">".$explanation."</td>";
  echo "</tr>";
}
function tableFieldnameField($name,$field, $type="")
{ echo "<tr ".$type.">";
  echo "<td class=\"fieldname\">".$name."</td>";
  echo "<td class=\"fieldvalue\">".$field."</td>";
  echo "</tr>";
}
function tableFieldnameField2($name1,$field1,$name2,$field2, $type="")
{ echo "<tr ".$type.">";
  echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">".$name1."</td>";                                                                                                         // ALTERNATIVE NAME
  echo "<td width=\"25%\">".$field1."</td>";
  echo "<td class=\"fieldname\" align=\"right\" width=\"25%\">".$name2."</td>";
  echo "<td width=\"25%\">".$field2."</td>";
  echo "</tr>";
}
function tableFieldnameField3($name1,$field1,$name2,$field2,$name3,$field3, $type="")
{ echo "<tr ".$type.">";
  echo "<td class=\"fieldname\" align=\"right\" width=\"16%\">".$name1."</td>";                                                                                                         // ALTERNATIVE NAME
  echo "<td width=\"17%\">".$field1."</td>";
  echo "<td class=\"fieldname\" align=\"right\" width=\"16%\">".$name2."</td>";
  echo "<td width=\"17%\">".$field2."</td>";
  echo "<td class=\"fieldname\" align=\"right\" width=\"17%\">".$name3."</td>";
  echo "<td width=\"17%\">".$field3."</td>";
  echo "</tr>";
}
function tableMenuItem($link, $menuItem)
{ echo "<tr align=\"center\" height=\"25px\">";
  echo "<td>";
  echo "<a target=\"_top\" href=\"".$link."\" class=\"mainlevel\">".$menuItem."</a>";
  echo "</td>";
  echo "</tr>";
}
function tablePageTitle($title, $link, &$list, &$min, &$max)
{ global $objUtil;
  echo "<table width=\"100%\">";
	echo "<tr>";
	echo "<td>";
	echo "<h2>";
  echo $title;
	echo "</h2>";
	echo "</td>";
	echo "<td align=\"right\">";
  list($min,$max)=$objUtil->printNewListHeader($list,$link,$min,25,'');	
	echo "</td>";
	echo "</table>";
}
function tableSortHeader($header0, $link0)
{ global $baseURL;
  echo "<td style=\"vertical-align:top;\">";         
  echo "<table width=\"100%\">";   
  echo "<tr>";          
  echo "<td>";         
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc. "\"><img src=\"".$baseURL."styles/images/up10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "<td align=\"right\">";         
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img src=\"".$baseURL."styles/images/down10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "</tr>";        
  echo "<tr>";
	echo "<td colspan=\"2\" style=\"text-align: center\">";           
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc."\">".trim($header0)."</a>";;
  echo "</td>";        
  echo "<tr>";
  echo "</table>";
  echo "</td>";        
}
function tableSortInverseHeader($header0, $link0)
{ global $baseURL;
  echo "<td style=\"vertical-align:top;\">";         
  echo "<table width=\"100%\">";   
  echo "<tr>";          
  echo "<td>";         
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img src=\"".$baseURL."styles/images/up10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "<td align=\"right\">";         
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=asc\" title=\"".LangSortOnAsc."\"><img src=\"".$baseURL."styles/images/down10.gif\" border=\"0\"></a>";
  echo "</td>";        
  echo "</tr>";        
  echo "<tr >";
  echo "<td colspan=\"2\" style=\"text-align: center\">";           
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\">".trim($header0)."</a>";;
  echo "</td>";        
  echo "</tr>";
  echo "</table>";
  echo "</td>";        
}
function tableTypeFieldnameField($type,$name,$field)
{ echo "<tr class=\"".$type."\">";
  echo "<td class=\"fieldname\">".$name."</td>";
  echo "<td class=\"fieldvalue\">".$field."</td>";
  echo "</tr>";
}

?>
