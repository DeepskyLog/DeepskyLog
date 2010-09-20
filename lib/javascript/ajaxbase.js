function ajaxbase(callurl,calltype,callbackfunction)
{ var jsonhttp;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Not supported on non-xmlhttp machines");
  
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { callbackfunction(jsonhttp.responseText);
    }
  };
  //alert(callurl);
  jsonhttp.open("GET",callurl,true);
  jsonhttp.send(null);
}