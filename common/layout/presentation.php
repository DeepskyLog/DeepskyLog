<?php
interface iPresentation
{ public  function alertMessage($theMessage);
  public  function br2dash($data); 
  public  function br2nl($data);                                                       // The opposite of nl2br
  public  function decToArgoString($decl);
  public  function decToString($decl,$web=1);
  public  function decToStringDSL($decl);
  public  function decToStringDSS($decl);                                              // returns html DSS decl coordinates eg 6+44 for 6°43'55''
  public  function decToTrimmedString($decl);
  public  function line($content,$alignment='',$widths=array(),$lineheight='');
  public  function presentationInt($value, $nullcontition='', $nullvalue='');          // if the null condtion is met, it returns the nullvalue, otherwise returns the value
  public  function presentationInt1($value, $nullcondition='', $nullvalue='');         // if the null condtion is met, it returns the nullvalue, otherwise returns the value formatted %1.1f
  public  function promptWithLink($prompt,$promptDefault,$javaLink,$text);             // displays an anchor link with $text as text, showing when clicked an inputbox with the question $prompt and $promptDefault answer, jumping to $javalink (java format) afterwards 
  public  function raArgoToString($ra);
  public  function raToString($ra);
  public  function raToStringDSL($ra);
  public  function raToStringDSS($ra);                                         // returns html DSS ra coordinates eg 6+43+55 for 6h43m55s
  public  function raToStringHM($ra);
  public  function searchAndLinkCatalogsInText($theText);                              // hyperlinks M, NGC, .. catalogs in a text
  public  function show3Fields($field1,$field2,$field3);
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
{ public  function alertMessage($theMessage)
  { global $baseURL;
    //echo "<script  type=\"text/javascript\">alert('".addslashes(strip_tags(html_entity_decode($this->br2nl($theMessage))))."');</script>";
  $_SESSION['message']=$theMessage;  
  echo "<script  type=\"text/javascript\">window.open('".$baseURL."message.php'".",'".LangMessageDeepskyLog."','location=no,navigation=no,status=no,left=300,top=280,height=200,width=400,scrollbars=no');</script>";
  }
  public function br2dash($data) 
  { return preg_replace('!<br.*>!iU', "-", $data );
  }
  public function br2nl($data)  // The opposite of nl2br
  { return preg_replace('!<br.*>!iU', " ", $data );
  }
  public  function decToArgoString($decl)
  { $sign="+";
    if($decl< 0)
    { $sign="-";
      $decl=-$decl;
    }
    $decl_degrees=floor($decl);
    $subminutes  =60*($decl-$decl_degrees);
    $decl_minutes=floor($subminutes);
    $subseconds = round(60*($subminutes-$decl_minutes));
    if($subseconds==60)
    { $subseconds=0;
      $decl_minutes++;
    }
    if($decl_minutes==60)
    { $decl_minutes=0;
      $decl_degrees++;
    }
    return($sign.sprintf("%02d",$decl_degrees).":".sprintf("%02d",$decl_minutes).":".sprintf("%02d",$subseconds));
  }
  public function decToString($decl,$web=1)
  { $sign="&nbsp;";
    if($decl<0)
    { $sign='-';
      $decl=-$decl;
    }
    else
      if($web!=1)
        $sign=' ';
    $decl_degrees=floor($decl);
    $subminutes  =60*($decl-$decl_degrees);
    $decl_minutes=round($subminutes);
    if($decl_minutes==60)
    { $decl_minutes=0;
      $decl_degrees++;
    }
    return($sign.$decl_degrees.(($web==1)?"&deg;":"d").sprintf("%02d",$decl_minutes).(($web==1)?"d":"'"));
  }
  public  function decToStringDegMin($decl)
  { $sign="";
    if($decl<0)
    { $sign='-';
      $decl=-$decl;
    }
    $decl_degrees=floor($decl);
    $subminutes  =60*($decl-$decl_degrees);
    $decl_minutes=round($subminutes);
    if($decl_minutes==60)
    { $decl_minutes=0;
      $decl_degrees++;
    }
    return($sign.sprintf("%02d",$decl_degrees)."&deg;".sprintf("%02d",$decl_minutes)."&#39;");
  }
  public  function decToStringDSL($decl)
  { if($decl<0)
    { $sign="m";
      $decl=-$decl;
    }
    else
      $sign="p";
    $decl_degrees=floor($decl);
    $subminutes  =60*($decl-$decl_degrees);
    $decl_minutes=round($subminutes);
    if($decl_minutes==60)
    { $decl_minutes=0;
      $decl_degrees++;
    }
    return($sign.sprintf("%02d", "$decl_degrees").sprintf("%02d", "$decl_minutes")."00");
  }
  public  function decToStringDSS($decl)
  { $sign="";
    if($decl<0)
    { $sign="-";
      $decl=-$decl;
    }
    $decl_degrees=floor($decl);
    $subminutes  =60*($decl-$decl_degrees);
    $decl_minutes=round($subminutes);
    if($decl_minutes==60)
    { $decl_minutes=0;
      $decl_degrees++;
    }
    return($sign.$decl_degrees."&#43;".$sign.$decl_minutes);
  }
  public  function decToTrimmedString($decl)
  { $sign="";
	  if($decl<0)
    { $sign="-";
      $decl=-$decl;
    }
    $decl_degrees=floor($decl);
    $subminutes  =60*($decl-$decl_degrees);
    $decl_minutes=round($subminutes);
    if($decl_minutes==60)
    { $decl_minutes=0;
      $decl_degrees++;
    }
    return($sign.$decl_degrees."&deg;".sprintf("%02d",$decl_minutes)."&#39;");
  }
  public  function line($content,$alignment='',$widths=array(),$lineheight='',$classes=array())
  { echo "<div class=\"containerLine\" ".($lineheight?"style=\"height:".$lineheight."px;\"":'').">";
  	for($m=0,$l=0,$a="L",$w=floor(100/count($content));$m<count($content);$m++,$l+=$w)
  	{ if(isset($widths)&&array_key_exists($m,$widths))
  	    $w=$widths[$m];
  	  if(isset($alignment))
  	    $a=substr($alignment,$m,1);
  	  echo "<div class=\"containerLinePart".$a.((array_key_exists($m,$classes))?" ".$classes[$m]:'')."\" style=\"left:".$l."%;width:".$w."%;".($lineheight?"line-height:".$lineheight."px;height:".$lineheight."px;":'')."\">".$content[$m]."</div>";
  	  
  	}
  	echo "</div>";
  }
  public  function presentationInt($value, $nullcontition='', $nullvalue='')
  { return (($value==$nullcontition)?$nullvalue:$value);
  }
  public  function presentationInt1($value, $nullcondition='', $nullvalue='')
  { return (($value==$nullcondition)?$nullvalue:sprintf("%1.1f",$value));
  }
  public function promptWithLink($prompt,$promptDefault,$javaLink,$text)
	{ echo "<a href=\"#\" onclick=\"thetitle = prompt('".addslashes($prompt)."','".addslashes($promptDefault)."'); location.href='".$javaLink."&amp;pdfTitle='+thetitle; return false;\"	target=\"new_window\">".$text."</a>";
  }
  public  function raArgoToString($ra)
  { $ra_hours  =floor($ra);
    $subminutes=60*($ra-$ra_hours);
    $ra_minutes=floor($subminutes);
    $ra_seconds=round(60*($subminutes-$ra_minutes));
    if($ra_seconds==60)
    { $ra_seconds=0;
      $ra_minutes++;
    }
    if($ra_minutes==60)
    { $ra_minutes=0;
      $ra_hours++;
    }
    if($ra_hours==24)
     $ra_hours = 0;
    return(sprintf("%02d",$ra_hours).":".sprintf("%02d",$ra_minutes).":".sprintf("%02d",$ra_seconds));
  }
  public  function raToString($ra)
  { $ra_hours  =floor($ra);
    $subminutes=60*($ra-$ra_hours);
    $ra_minutes=floor($subminutes);
    $ra_seconds=round(60*($subminutes-$ra_minutes));
    if($ra_seconds==60)
    { $ra_seconds=0;
      $ra_minutes++;
    }
    if($ra_minutes==60)
    { $ra_minutes=0;
      $ra_hours++;
    }
    if($ra_hours == 24)
      $ra_hours = 0;
    return(sprintf("%02d",$ra_hours)."h".sprintf("%02d",$ra_minutes)."m".sprintf("%02d",$ra_seconds)."s");  
  }
  public  function raToStringDSL($ra)
  { $ra_hours  =floor($ra);
    $subminutes=60*($ra-$ra_hours);
    $ra_minutes=floor($subminutes);
    $ra_seconds=round(60*($subminutes-$ra_minutes));
    if($ra_seconds==60)
    { $ra_seconds=0;
      $ra_minutes++;
    }
    if($ra_minutes==60)
    { $ra_minutes=0;
      $ra_hours++;
    }
    if($ra_hours == 24)
      $ra_hours = 0;
    return(sprintf("%02d",$ra_hours).sprintf("%02d",$ra_minutes).sprintf("%02d",$ra_seconds));
  }
  public  function raToStringDSS($ra)
  { $ra_hours=floor($ra);
    $subminutes=60*($ra - $ra_hours);
    $ra_minutes=floor($subminutes);
    $ra_seconds=round(60*($subminutes-$ra_minutes));
    if($ra_seconds==60)
    { $ra_seconds=0;
      $ra_minutes++;
    }
    if($ra_minutes==60)
    { $ra_minutes=0;
      $ra_hours++;
    }
    if($ra_hours == 24)
      $ra_hours = 0;
    return($ra_hours."&#43;".$ra_minutes."&#43;".$ra_seconds);
  }
  public  function raToStringHM($ra)
  { $ra_hours=floor($ra);
    $subminutes=60*($ra-$ra_hours);
    $ra_minutes=round($subminutes);
    if($ra_minutes==60)
    { $ra_minutes=0;
      $ra_hours++;
    } 
    if($ra_hours == 24)
      $ra_hours = 0;
    return sprintf('%02d',($ra_hours%24)).'h'.sprintf('%02d',$ra_minutes).'m';
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
  public  function show3Fields($field1,$field2,$field3)
  { echo "<div style=\"position:relative;width:100%;height:40;\">";
    echo "<div style=\"height:40;width:33%;vertical-align:middle;text-align:right;position:absolute;left:0%;top:0px;padding-right:5px;\">".$field1."</div>";
    echo "<div style=\"height:auto;width:33%;text-align:left;position:absolute;left:33%;top:0px;padding-left:5px;\">".$field2."</div>";
    echo "<div style=\"height:auto;width:34%;text-align:left;position:absolute;left:66%;top:0px;padding-left:5px;\">".$field3."</div>";
    echo "</div>";
  	
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
	echo "<h7>";
  echo $title;
	echo "</h7>";
	echo "</td>";
	echo "<td align=\"right\">";
  list($min,$max)=$objUtil->printNewListHeader($list,$link,$min,25,'');	
	echo "</td>";
  echo "</tr>";
	echo "</table>";
}
function divPageTitle($title, $link, &$list, &$min, &$max)
{ global $objUtil;
  echo "<div class=\"container\" style=\"position:relative; height:40px;\">";
	echo "<div class=\"h2header\" style=\"position:absolute; left:0Px;width:60%;height:40px;\">";
  echo $title;
	echo "</div>";
	echo "<div style=\"position:absolute; top:10px;right:0px;width:38%;text-align:right;height:40px;\">";
  list($min,$max)=$objUtil->printNewListHeader2($list,$link,$min,25,'');	
	echo "</div>";
	echo "</div>";
}
function tableSortHeader($header0, $link0)
{ global $baseURL;
  echo "<td style=\"vertical-align:top;\">";         
  echo "<table width=\"100%\">";   
  echo "<tr>";          
  echo "<td>";         
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc. "\"><img src=\"".$baseURL."styles/images/up10.gif\" border=\"0\" alt=\"^\"/></a>";
  echo "</td>";        
  echo "<td align=\"right\">";         
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img src=\"".$baseURL."styles/images/down10.gif\" border=\"0\" alt=\"v\"/></a>";
  echo "</td>";        
  echo "</tr>";        
  echo "<tr>";
	echo "<td colspan=\"2\" style=\"text-align: center\">";           
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc."\">".trim($header0)."</a>";;
  echo "</td>";        
  echo "</tr>";
  echo "</table>";
  echo "</td>";        
}
function tableSortInverseHeader($header0, $link0)
{ global $baseURL;
  echo "<td style=\"vertical-align:top;\">";         
  echo "<table width=\"100%\">";   
  echo "<tr>";          
  echo "<td>";         
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img src=\"".$baseURL."styles/images/up10.gif\" border=\"0\" alt=\"^\" /></a>";
  echo "</td>";        
  echo "<td align=\"right\">";         
  echo "<a target=\"_top\" href=\"".$link0."&amp;sortdirection=asc\" title=\"".LangSortOnAsc."\"><img src=\"".$baseURL."styles/images/down10.gif\" border=\"0\" alt=\"v\" /></a>";
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
