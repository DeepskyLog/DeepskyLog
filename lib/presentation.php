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
  public  function getDSSDeepskyLiveLinks($object);
  public  function getDSSDeepskyLiveLinks1($object);
  public  function getDSSDeepskyLiveLinks2($object);
  public  function line($content,$alignment='',$widths=array(),$lineheight='',$classes=array());
  public  function presentationInt($value, $nullcontition='', $nullvalue='');          // if the null condtion is met, it returns the nullvalue, otherwise returns the value
  public  function presentationInt1($value, $nullcondition='', $nullvalue='');         // if the null condtion is met, it returns the nullvalue, otherwise returns the value formatted %1.1f
  public  function promptWithLink($prompt,$promptDefault,$javaLink,$text);             // displays an anchor link with $text as text, showing when clicked an inputbox with the question $prompt and $promptDefault answer, jumping to $javalink (java format) afterwards 
  public  function promptWithLinkText($prompt,$promptDefault,$javaLink,$text);         // returns an anchor link with $text as text, showing when clicked an inputbox with the question $prompt and $promptDefault answer, jumping to $javalink (java format) afterwards 
  public  function raArgoToString($ra);
  public  function raToString($ra);
  public  function raToStringDSL($ra);
  public  function raToStringDSS($ra);                                         // returns html DSS ra coordinates eg 6+43+55 for 6h43m55s
  public  function raToStringHM($ra);
  public  function searchAndLinkCatalogsInText($theText);                              // hyperlinks M, NGC, .. catalogs in a text
  public  function tableSortHeader($header0, $link0);                                           // sorting header on table
  public  function tableSortInverseHeader($header0, $link0);                                    // inverse sorting header on table
}
class Presentations implements iPresentation
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
    return($sign.$decl_degrees.(($web==1)?"&deg;":"d").sprintf("%02d",$decl_minutes).(($web==1)?"m":"'"));
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
  public  function getDSSDeepskyLiveLinks($object)
  { global $objPresentations,$objObject,$baseURL,$deepskylive,$objUtil;
  	$raDSS=$objPresentations->raToStringDSS($objObject->getDsoProperty($object,'ra'));
    $declDSS=$objPresentations->decToStringDSS($objObject->getDsoProperty($object,'decl'));
  	$topline =LangViewObjectDSS."&nbsp;:&nbsp;";
	  $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;raDSS=".$raDSS."&amp;declDSS=".$declDSS."&amp;object=".urlencode($object)."&amp;imagesize=15\" >"."15x15'"."</a>-";
	  $topline.="&nbsp;";
	  $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;raDSS=".$raDSS."&amp;declDSS=".$declDSS."&amp;object=".urlencode($object)."&amp;imagesize=30\" >"."30x30'"."</a>-";
	  $topline.="&nbsp;";
	  $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;raDSS=".$raDSS."&amp;declDSS=".$declDSS."&amp;object=".urlencode($object)."&amp;imagesize=60\" >"."60x60'"."</a>";
	  $topline.="&nbsp;-&nbsp;";
	  if ($deepskylive == 1)
	  { $raDSL=$objPresentations->raToStringDSL($objObject->getDsoProperty($object,'ra'));
	    $declDSL=$objPresentations->decToStringDSL($objObject->getDsoProperty($object,'decl'));
	    $topline.=LangViewObjectDSL."&nbsp;:&nbsp;";
	    $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."&amp;dslsize=1&amp;showDSL=1\">1x1&deg;</a>-";
	    $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."&amp;dslsize=2&amp;showDSL=1\">2x2&deg;</a>-";
	    $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."&amp;dslsize=3&amp;showDSL=1\">3x3&deg;</a>";
	    if($objUtil->checkGetKey("showDSL",0)==1)
	    { $fov=$objUtil->checkGetKey("dslsize",30);
	      echo "<applet code=\"Deepskylive.class\" codebase=\"http://users.telenet.be/deepskylive/applet/\" height=\"1\" width=\"1\">
	            <param name=\"ra\" value=\"".$raDSL."\">
	            <param name=\"dec\" value=\"".$declDSL."\">
	            <param name=\"fov\" value=\"".$fov."\">
	            <param name=\"p\" value=\"1\">
	            </applet>";
	    }
	  }
  	return $topline;
  }
  public  function getDSSDeepskyLiveLinks1($object)
  { global $objPresentations,$objObject,$baseURL,$deepskylive,$objUtil;
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
  { global $objPresentations,$objObject,$baseURL,$deepskylive,$objUtil;
    $topline='';
	  if ($deepskylive == 1)
	  { $raDSL=$objPresentations->raToStringDSL($objObject->getDsoProperty($object,'ra'));
	    $declDSL=$objPresentations->decToStringDSL($objObject->getDsoProperty($object,'decl'));
	    $topline.=LangViewObjectDSL."&nbsp;:&nbsp;";
	    $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."&amp;dslsize=1&amp;showDSL=1\">1x1&deg;</a>-";
	    $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."&amp;dslsize=2&amp;showDSL=1\">2x2&deg;</a>-";
	    $topline.="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=".urlencode($object)."&amp;dslsize=3&amp;showDSL=1\">3x3&deg;</a>";
	    if($objUtil->checkGetKey("showDSL",0)==1)
	    { $fov=$objUtil->checkGetKey("dslsize",30);
	      echo "<applet code=\"Deepskylive.class\" codebase=\"http://users.telenet.be/deepskylive/applet/\" height=\"1\" width=\"1\">
	            <param name=\"ra\" value=\"".$raDSL."\">
	            <param name=\"dec\" value=\"".$declDSL."\">
	            <param name=\"fov\" value=\"".$fov."\">
	            <param name=\"p\" value=\"1\">
	            </applet>";
	    }
	  }
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
		$replacements[0]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=M%20\\2\">&nbsp;M&nbsp;\\2</a>";
		$patterns[1]= "/(NGC|Ngc|ngc)\s*(\d+\w+)/";
		$replacements[1]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=NGC%20\\2\">NGC&nbsp;\\2</a>";
		$patterns[2]= "/(IC|Ic|ic)\s*(\d+)/";
		$replacements[2]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=IC%20\\2\">IC&nbsp;\\2</a>";
		$patterns[3]= "/(Arp|ARP|arp)\s*(\d+)/";
		$replacements[3]="<a href=\"".$baseURL."index.php?indexAction=detail_object&amp;object=Arp%20\\2\">Arp&nbsp;\\2</a>";
		return preg_replace($patterns, $replacements, $theText);
  }
	public  function tableSortHeader($header0, $link0)
	{ global $baseURL;
	  echo "<td style=\"vertical-align:top;\">";         
	  echo "<table>";   
	  echo "<tr>";          
	  echo "<td>";         
	  echo "<a href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc. "\"><img class=\"sortButton\" src=\"".$baseURL."styles/images/up10.gif\" alt=\"^\"/></a>";
	  echo "</td>";        
	  echo "<td align=\"right\">";         
	  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img class=\"sortButton\" src=\"".$baseURL."styles/images/down10.gif\" alt=\"v\"/></a>";
	  echo "</td>";        
	  echo "</tr>";        
	  echo "<tr>";
		echo "<td colspan=\"2\" class=\"centered\">";           
	  echo "<a href=\"".$link0."&amp;sortdirection=asc\"  title=\"".LangSortOnAsc."\">".trim($header0)."</a>";;
	  echo "</td>";        
	  echo "</tr>";
	  echo "</table>";
	  echo "</td>";        
	}
	public  function tableSortInverseHeader($header0, $link0)
	{ global $baseURL;
	  echo "<td style=\"vertical-align:top;\">";         
	  echo "<table>";   
	  echo "<tr>";          
	  echo "<td>";         
	  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\"><img class=\"sortButton\" src=\"".$baseURL."styles/images/up10.gif\" alt=\"^\" /></a>";
	  echo "</td>";        
	  echo "<td align=\"right\">";         
	  echo "<a href=\"".$link0."&amp;sortdirection=asc\" title=\"".LangSortOnAsc."\"><img class=\"sortButton\" src=\"".$baseURL."styles/images/down10.gif\" alt=\"v\" /></a>";
	  echo "</td>";        
	  echo "</tr>";        
	  echo "<tr >";
	  echo "<td colspan=\"2\" class=\"centered\">";           
	  echo "<a href=\"".$link0."&amp;sortdirection=desc\" title=\"".LangSortOnDesc."\">".trim($header0)."</a>";;
	  echo "</td>";        
	  echo "</tr>";
	  echo "</table>";
	  echo "</td>";        
	}
}
$objPresentations=new Presentations;
?>