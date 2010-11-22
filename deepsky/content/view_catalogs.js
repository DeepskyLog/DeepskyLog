function view_catalogs(leftmenu,topmenu)
{ resizeDivsViewCatalogs(leftmenu,topmenu);
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
{ document.getElementById('view_catalogs_right').innerHTML='Getting ajax data for '+thecatalog;	
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

function show_catalog(thecatalogdata)
{ thetext="Number of objects: ...";	
  document.getElementById('view_catalogs_right').innerHTML=thetext;
}