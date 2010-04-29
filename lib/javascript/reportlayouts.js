var ajaxanswer=new Array();
function setLayoutPage()
{ var jsonhttp;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { alert(jsonhttp.responseText);
      ajaxanswer=eval("("+jsonhttp.responseText+")");
      setLayoutPageCallback();
    }
  };
  var url="ajaxinterface.php?instruction=getReportLayout&";
  url+=document.getElementById('reportlayoutselect').options[document.getElementById('reportlayoutselect').selectedIndex].value;
  alert(url);
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}

function setLayoutPageCallback()
{ //alert(ajaxanswer);
}