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
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}

function setLayoutPageCallback()
{ echostring='';
 echostring+="<table>";
 echostring+="<tr class=\"type3\">";
 echostring+="<td>"+""+"</td>";
 echostring+="<td>"+"Positie"+"</td>";
 echostring+="</tr>";
  for(i=0;i<ajaxanswer.length;i++)
    if(ajaxanswer[i]["fieldstyle"]=="LAYOUTMETADATA")
    { echostring+="<tr class=\"type"+(i%2)+"\">";  
      echostring+="<td class=\"right\">"+ajaxanswer[i]["fieldname"]+"</td>";
      echostring+="<td>"+ajaxanswer[i]["fieldposition"]+"</td>";
      echostring+="</tr>";
    }
  echostring+="</table>";
  echostring+="<hr />";
  for(i=0;i<ajaxanswer.length;i++)
    if(ajaxanswer[i]["fieldstyle"]!="LAYOUTMETADATA")
      echostring+=ajaxanswer[i]["fieldname"]+": "+ajaxanswer[i]["fieldposition"]+"<br />";
  echostring+="<hr />";	  
  document.getElementById('reportlayout').innerHTML=echostring;
}