
function view_catalogs(leftmenu,topmenu)
{
  //resizeDivsViewCatalogs(leftmenu,topmenu);
  var jsonhttp;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Catalog pages are not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { show_catalogs(eval('('+jsonhttp.responseText+')'));
    }
  };
  var url='ajaxinterface.php?instruction=getCatalogs';
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}

function show_catalogs(thecatalogs)
{ var thetext='';
  for(i=0;i<thecatalogs.length;i++)
	thetext+='<a href="#" onclick="view_catalog(\''+thecatalogs[i]+'\');return false;";>'+thecatalogs[i]+'</a><br />';
  document.getElementById('view_catalogs_left').innerHTML=thetext;
}
function view_catalog(thecatalog)
{
  document.getElementById('view_catalogs_right').innerHTML='Getting ajax data for '+thecatalog;
  var jsonhttp;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Catalog pages are not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { show_catalog(eval('('+jsonhttp.responseText+')'));
    }
  };
  var url='ajaxinterface.php?instruction=getCatalogData&thecatalog='+thecatalog;
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}

function show_catalog($thecatalogdata)
{ $thecount=count($thecatalogdata);
  $thetext="Number of objects: ";
  $thetext+=$thecount+"<br />";
  var $theconname= new array();
  var $theconcount=new array();
  for($i=0;$i<$thecount;$i++)
  { for($j=0;$j<$theconname.length;$j++)
      if($thecatalogdata[$i]['con']==$theconname[$j])
      { $theconcount[$j]=$theconcount[$j]+1;
        $j=999;
      }
    if($j<999)
    { $theconname[$theconname.length]=$thecatalogdata[$i]['con'];
      $theconcount[$theconcount.length]=1;
    }
  }
  for($i=0;$i<$theconname.length-1;$i++)
    for($j=$i+1;$j<$theconname.length;$j++)
      if($theconname[$i]>$theconname[$j])
      {  $temp1=$theconname[$j];
         $temp2=$theconcount[$j];
         $theconname[$j]=$theconname[$i];
         $theconcount[$j]=$theconcount[$i];
         $theconname[$i]=$temp1;
         $theconcount[$i]=$temp2;
      }
  $thetext+='<div class="table-responsive"><table class="table table-condensed table-bordered table-hover">';
  for($j=0;$j<$theconname.length;$j++)
  { if(($j%3)==0)
	  $thetext+='<tr>';
	$thetext=$thetext+'<td class="td33pct">'+$theconname[$j]+': '+$theconcount[$j]+"</td>";
	if(($j%3)==2)
	  $thetext+='</tr>';
  }
  if(($j%3)!=0)
  { while((($j++)%3!=2))
	  $thetext=$thetext+'<td class="td33pct">&nbsp;</td>';
	$thetext+='</tr>';
  }
  $thetext+='</table></div>';

  var $thetypename= new array();
  var $thetypecount=new array();
  for($i=0;$i<$thecount;$i++)
  { for($j=0;$j<$thetypename.length;$j++)
      if($thecatalogdata[$i]['type']==$thetypename[$j])
      { $thetypecount[$j]=$thetypecount[$j]+1;
        $j=999;
      }
    if($j<999)
    { $thetypename[$thetypename.length]=$thecatalogdata[$i]['type'];
      $thetypecount[$thetypecount.length]=1;
    }
  }
  for($i=0;$i<$thetypename.length-1;$i++)
    for($j=$i+1;$j<$thetypename.length;$j++)
      if($thetypename[$i]>$thetypename[$j])
      {  $temp1=$thetypename[$j];
         $temp2=$thetypecount[$j];
         $thetypename[$j]=$thetypename[$i];
         $thetypecount[$j]=$thetypecount[$i];
         $thetypename[$i]=$temp1;
         $thetypecount[$i]=$temp2;
      }
  $thetext+='<div class="table-responsive"><table class="table table-condensed table-bordered table-hover">';
  for($j=0;$j<$thetypename.length;$j++)
  { if(($j%3)==0)
	  $thetext+='<tr>';
	$thetext=$thetext+'<td class="td33pct">'+$thetypename[$j]+': '+$thetypecount[$j]+"</td>";
	if(($j%3)==2)
	  $thetext+='</tr>';
  }
  if(($j%3)!=0)
  { while((($j++)%3!=0))
	  $thetext=$thetext+'<td class="td33pct">&nbsp;</td>';
	$thetext+='</tr>';
  }
  $thetext+='</table></div>';




  $thetext+='<div class="table-responsive"><table class="table table-condensed table-bordered table-hover">';
  for($j=0;$j<$thecatalogdata.length;$j++)
  { if(($j%3)==0)
	  $thetext+='<tr>';
	$thetext=$thetext+'<td class="td33pct"><a href="index.php?indexAction=quickpick&titleobjectaction=Search&source=quickpick&searchObjectQuickPickQuickPick=Search%A0Object&object='+urlencode($thecatalogdata[$j]['altname'])+'">'+$thecatalogdata[$j]['altname']+($thecatalogdata[$j]['altname']!=$thecatalogdata[$j]['objectname']?"&nbsp;("+$thecatalogdata[$j]['objectname']+")":"")+"</a></td>";
	if(($j%3)==2)
	  $thetext+='</tr>';
  }
  if(($j%3)!=0)
  { while((($j++)%3!=2))
	  $thetext=$thetext+'<td class="td33pct">&nbsp;</td>';
	$thetext+='</tr>';
  }
  $thetext+='</table></div>';
  document.getElementById('view_catalogs_right').innerHTML=$thetext;
}
