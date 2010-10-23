<?php
class Presentations
{ public  function alertMessage($theMessage)
  { global $baseURL,$indexAction;
    echo "<div id=\"veil\">";
	  echo "</div>"; 
	  echo "<div id=\"dialogback\">";
	  echo "</div>";
	  echo "<div id=\"dialog\">";
	  echo "<div id=\"dialogdiv1\">";
	  echo $theMessage;
	  echo "</div>";
	  echo "<div id=\"dialogdiv2\">";
	  echo "<input id=\"alertMsgOk\" class=\"alertMsgOk\" type=\"submit\" onclick=\"confirmAlertMessage();\" value=\"Ok\" />";
    echo "</div>";
	  echo "</div>";
	  echo "<script type=\"text/javascript\">messageBox();</script>";	
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
    return($sign.$decl_degrees.(($web==1)?"&deg;":"°").sprintf("%02d",$decl_minutes).(($web==1)?"m":"'"));
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
  public  function decToStringDSS2($decl)
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
    return($sign.$decl_degrees.'+'.$sign.$decl_minutes);
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
  public  function getDSSDeepskyLiveLinks1($object)
  { global $objPresentations,$objObject,$baseURL,$objUtil;
  	$raDSS=$objPresentations->raToStringDSS($objObject->getDsoProperty($object,'ra'));
    $declDSS=$objPresentations->decToStringDSS($objObject->getDsoProperty($object,'decl'));
  	$topline =LangViewObjectDSS."&nbsp;:&nbsp;";
	  $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;raDSS=".$raDSS."&amp;declDSS=".$declDSS."&amp;object=".urlencode($object)."&amp;imagesize=15\" >"."15x15'"."</a>-";
	  $topline.="&nbsp;";
	  $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;raDSS=".$raDSS."&amp;declDSS=".$declDSS."&amp;object=".urlencode($object)."&amp;imagesize=30\" >"."30x30'"."</a>-";
	  $topline.="&nbsp;";
	  $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;raDSS=".$raDSS."&amp;declDSS=".$declDSS."&amp;object=".urlencode($object)."&amp;imagesize=60\" >"."60x60'"."</a>";
  	return $topline;
  }
  public  function getDSSDeepskyLiveLinks2($object)
  { global $loggedUser,$objPresentations,$objObject,$baseURL,$objUtil,$objObserver;
    $topline=LangViewObjectDSL." (pdf):&nbsp;";
	  $topline.=                "<a href=\"" . $baseURL . "atlas.pdf?zoom=".AtlasOverviewZoom."&amp;object=" . urlencode($object) . ($loggedUser?(is_numeric($tempa=$objObserver->getObserverProperty($loggedUser,'overviewdsos',''))?"&amp;dsos=".urlencode($tempa):''):''). ($loggedUser?(is_numeric($tempc=$objObserver->getObserverProperty($loggedUser,'atlaspagefont',''))?"&amp;fontsize=".urlencode($tempc):''):'').                        ($loggedUser?(is_numeric($tempb=$objObserver->getObserverProperty($loggedUser,'overviewstars',''))?"&amp;stars=".urlencode($tempb):''):''). "\">" . OverviewChart . "</a>";
	  $topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "atlas.pdf?zoom=".AtlasLookupZoom.  "&amp;object=" . urlencode($object) . ($loggedUser?(is_numeric($tempa=$objObserver->getObserverProperty($loggedUser,'lookupdsos',''))?  "&amp;dsos=".urlencode($tempa):''):''). ($loggedUser?(is_numeric($tempc=$objObserver->getObserverProperty($loggedUser,'atlaspagefont',''))?"&amp;fontsize=".urlencode($tempc):''):'').                       ($loggedUser?(is_numeric($tempb=$objObserver->getObserverProperty($loggedUser,'lookupstars',''))?  "&amp;stars=".urlencode($tempb):''):''). "\">" . LookupChart . "</a>";
	  $topline.="&nbsp;-&nbsp;"."<a href=\"" . $baseURL . "atlas.pdf?zoom=".AtlasDetailZoom.  "&amp;object=" . urlencode($object) . ($loggedUser?(is_numeric($tempa=$objObserver->getObserverProperty($loggedUser,'detaildsos',''))?  "&amp;dsos=".urlencode($tempa):"&amp;dsos=90"):"&amp;dsos=90").($loggedUser?(is_numeric($tempc=$objObserver->getObserverProperty($loggedUser,'atlaspagefont',''))?"&amp;fontsize=".urlencode($tempc):''):'').($loggedUser?(is_numeric($tempb=$objObserver->getObserverProperty($loggedUser,'detailstars',''))?  "&amp;stars=".urlencode($tempb):''):''). "\">" . DetailChart . "</a>";
    return $topline;
  }
  public  function line($content,$alignment='',$widths=array(),$lineheight='',$classes=array())
  { echo "<div class=\"containerLine\" ".($lineheight?"style=\"height:".$lineheight."px;\"":'').">";
  	for($m=0,$l=0,$a="L",$w=floor(100/count($content));$m<count($content);$m++,$l+=$w)
  	{ if(isset($widths)&&array_key_exists($m,$widths))
  	    $w=$widths[$m];
  	  if(isset($alignment))
  	    $a=substr($alignment,$m,1);
  	  echo "<div class=\"containerLinePart".$a.((array_key_exists($m,$classes))?" ".$classes[$m]:'')."\" style=\"left:".$l."%;width:".$w."%;".($lineheight?"line-height:".$lineheight."px;height:".$lineheight."px;":'')."\">".$content[$m].(($a=="R")?"&nbsp;&nbsp;&nbsp;":"")."</div>";
  	}
  	echo "</div>";
  }
  public  function setPopupForm()
  { global $baseURL,$indexAction;
    echo "<div id=\"veil\">";
	  echo "</div>"; 
	  echo "<div id=\"dialogback\">";
	  echo "</div>";
	  echo "<div id=\"dialog\">";
	  echo "<div id=\"dialogdiv1\">";
	  echo "Temp Placeholder";
	  echo "</div>";
	  echo "</div>";
  }
  public  function presentationInt($value, $nullcontition='', $nullvalue='')
  { return (($value==$nullcontition)?$nullvalue:$value);
  }
  public  function presentationInt1($value, $nullcondition='', $nullvalue='')
  { return (($value==$nullcondition)?$nullvalue:sprintf("%1.1f",$value));
  }
  public function promptWithLink($prompt,$promptDefault,$javaLink,$text)
	{ echo "<a href=\"#\" onclick=\"thetitle = prompt('".addslashes($prompt)."','".addslashes($promptDefault)."'); location.href='".$javaLink."&amp;pdfTitle='+thetitle; return false;\"	rel=\"external\">".$text."</a>";
  }
  public function promptWithLinkText($prompt,$promptDefault,$javaLink,$text)
	{ return "<a href=\"#\" onclick=\"thetitle = prompt('".addslashes($prompt)."','".addslashes($promptDefault)."'); location.href='".$javaLink."&amp;pdfTitle='+thetitle; return false;\"	rel=\"external\">".$text."</a>";
  }
  public function promptWithLinkAndLayout($prompt,$promptDefault,$javaLink,$text)
	{ return "<a href=\"#\" onclick=\"theLayoutName = prompt('".addslashes($prompt)."','".addslashes($promptDefault)."');  location.href='".$javaLink."&amp;layoutName='+theLayoutName+'&amp;orderColumns='+getColumnsOrder()+'&amp;restoreColumns='+getColumnsRestore();  return false;\" >".$text."</a>";
  }
  public function promptWithLinkAndLayoutList($formName,$javaLink)
	{ global $objFormLayout;
	  $layoutList=$objFormLayout->getLayoutList($formName);
		$list ="<select name=\"loadLayout\" class=\"\" onchange=\"location.href='".$javaLink."&amp;layoutName='+this.options[this.selectedIndex].value; \">";
    $list.="<option value=\"\">-----</option>";
    while(list($key,$value)=each($layoutList))
      $list.= "<option value=\"".$value."\">".$value."</option>";
    $list.="</select>";
    return $list;
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
  public  function raToStringDSS2($ra)
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
    return($ra_hours.'+'.$ra_minutes.'+'.$ra_seconds);
  }
  public function radeclToStringALADIN($ra,$decl)
  { $sign="";
    if($decl<0)
    { $sign="-";
      $decl=-$decl;
    }
    else
      $sign="%2b";
    $decl_degrees=floor($decl);
    $subminutes  =60*($decl-$decl_degrees);
    $decl_minutes=round($subminutes);
    if($decl_minutes==60)
    { $decl_minutes=0;
      $decl_degrees++;
    }
    $ra_hours=floor($ra);
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
    return($ra_hours.'%20'.$ra_minutes.'%20'.$ra_seconds.'%20'.$sign.$decl_degrees.'%20'.$decl_minutes);
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
		$replacements[0]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
		$patterns[1]= "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
		$replacements[1]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=NGC%20\\2\">NGC&nbsp;\\2</a>";
		$patterns[2]= "/(IC|Ic|ic)\s*(\d+)/";
		$replacements[2]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=IC%20\\2\">IC&nbsp;\\2</a>";
		$patterns[3]= "/(Arp|ARP|arp)\s*(\d+)/";
		$replacements[3]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=Arp%20\\2\">Arp&nbsp;\\2</a>";
		return preg_replace($patterns, $replacements, $theText);
  }
	public  function tableSortHeader($header0, $link0, $id="", $columnSource="")
	{ global $baseURL;
	  echo "<td class=\"verticalaligntop;\" ".($id?"id=\"".$id."\" ":"").">";         
	  echo "<table>";   
	  echo "<tr>";          
	  echo "<td>";         
	  echo "<a href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc. "\"><img class=\"sortButton\" src=\"".$baseURL."styles/images/up10.gif\" alt=\"^\"/></a>";
	  echo "</td>";        
	  if($id)
	  { echo "<td class=\"centered width100pct\">";         
	    echo "<a href=\"#\" onclick=\"removeColumn('".$id."','".$header0."');return false;\">x</a>";
	    echo "</td>";        
	  }
	  echo "<td class=\"right\">";         
	  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img class=\"sortButton\" src=\"".$baseURL."styles/images/down10.gif\" alt=\"v\"/></a>";
	  echo "</td>";        
	  echo "</tr>";        
	  echo "<tr>";
	  if($id)
	  { echo "<td class=\"left\">";         
	    echo "<a href=\"#\" onclick=\"moveColumnLeft('".$id."');return false;\">&lt;</a>";
	    echo "</td>";        
	  }
	  echo "<td class=\"centered width100pct\">";           
	  echo "<a href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc."\">".trim($header0)."</a>";;
	  echo "</td>";        
	  if($id)
	  { echo "<td class=\"left\">";         
	    echo "<a href=\"#\" onclick=\"moveColumnRight('".$id."');return false;\">&gt;</a>";
	    echo "</td>";        
	  }
	  echo "</tr>";
	  echo "</table>";
	  echo "</td>";        
	}
	public  function tableSortInverseHeader($header0, $link0, $id="", $columnSource="")
	{ global $baseURL;
	  echo "<td class=\"verticalaligntop;\" ".($id?"id=\"".$id."\" ":"").">";         
	  echo "<table>";   
	  echo "<tr>";          
	  echo "<td>";         
	  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img class=\"sortButton\" src=\"".$baseURL."styles/images/up10.gif\" alt=\"^\" /></a>";
	  echo "</td>";        
	  if($id)
	  { echo "<td class=\"centered width100pct\" >";         
	    echo "<a href=\"#\" onclick=\"removeColumn('".$id."','".$header0."');return false;\">x</a>";
	    echo "</td>";        
	  }
	  echo "<td class=\"right\">";         
	  echo "<a href=\"".$link0."&amp;sortdirection=asc\" title=\"".LangSortOnAsc."\"><img class=\"sortButton\" src=\"".$baseURL."styles/images/down10.gif\" alt=\"v\" /></a>";
	  echo "</td>";        
	  echo "</tr>";        
	  echo "<tr >";
	  if($id)
	  { echo "<td class=\"left\">";         
	    echo "<a href=\"#\" onclick=\"moveColumnLeft('".$id."');return false;\">&lt;</a>";
	    echo "</td>";        
	  }
	  echo "<td class=\"centered\">";           
	  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\">".trim($header0)."</a>";;
	  echo "</td>";        
	  if($id)
	  { echo "<td class=\"left\">";         
	    echo "<a href=\"#\" onclick=\"moveColumnRight('".$id."');return false;\">&gt;</a>";
	    echo "</td>";        
	  }
	  echo "</tr>";
	  echo "</table>";
	  echo "</td>";        
	}
}
?>