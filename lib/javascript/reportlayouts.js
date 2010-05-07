var ajaxanswer=new Array();
var newlayoutname='test';
function setLayoutPage()
{ if(document.getElementById('reportlayoutselect').options[document.getElementById('reportlayoutselect').selectedIndex].value)
  { var jsonhttp;
    if(window.XMLHttpRequest)
      jsonhttp=new XMLHttpRequest();
    else if(window.activeXObject)
      jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
    else
      alert("Not supported on non-xmlhttp machines");
    jsonhttp.onreadystatechange=function()
    { if(jsonhttp.readyState==4)
      { //alert(jsonhttp.responseText);
        ajaxanswer=eval("("+jsonhttp.responseText+")");
        setLayoutPageCallback();
      }
    };
    var url="ajaxinterface.php?instruction=getReportLayout&reportname="+thereport;
    url+='&reportlayout='+document.getElementById('reportlayoutselect').options[document.getElementById('reportlayoutselect').selectedIndex].value;
    jsonhttp.open("GET",url,true);
    jsonhttp.send(null);
  }
  else
  { document.getElementById('reportlayout').innerHTML="";
  }
}
function json_encode_dee(thearray)
{ returnvalue="";
  if(thearray)
  for(k=0;k<thearray.length;k++)
  { returnvalue+=k+" => array(";
    returnvalue+="\"reportlayoutpk\" => \""+thearray[k]["reportlayoutpk"]+"\",";
	returnvalue+="\"observerid\" => \""+thearray[k]["observerid"]+"\",";
	returnvalue+="\"reportname\" => \""+thearray[k]["reportname"]+"\",";
	returnvalue+="\"reportlayout\" => \""+thearray[k]["reportlayout"]+"\",";
	returnvalue+="\"fieldname\" => \""+thearray[k]["fieldname"]+"\",";
	returnvalue+="\"fieldline\" => \""+thearray[k]["fieldline"]+"\",";
	returnvalue+="\"fieldposition\" => \""+thearray[k]["fieldposition"]+"\",";
	returnvalue+="\"fieldwidth\" => \""+thearray[k]["fieldwidth"]+"\",";
	returnvalue+="\"fieldheight\" => \""+thearray[k]["fieldheight"]+"\",";
	returnvalue+="\"fieldstyle\" => \""+thearray[k]["fieldstyle"]+"\"";
	returnvalue+="),";
  }
  return "array("+returnvalue.substring(0,returnvalue.length-1)+")";
}

function saveLayoutPage(theurl,reportName,pdfTitle,SID,thesort)
{ if(document.getElementById('reportlayoutselect').options[document.getElementById('reportlayoutselect').selectedIndex].value)
  { var jsonhttp;
    if(window.XMLHttpRequest)
      jsonhttp=new XMLHttpRequest();
    else if(window.activeXObject)
      jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
    else
      alert("Not supported on non-xmlhttp machines");
    jsonhttp.onreadystatechange=function()
    { if(jsonhttp.readyState==4)
      { //alert(jsonhttp.responseText);
        location=(theurl+"?reportname="+reportName+"&"+
                "pdfTitle="+pdfTitle+"&"+
                "SID="+SID+"&"+
                "sort="+thesort+"&"+
                "reportlayout="+document.getElementById('reportlayoutselect').value);
      }
    };
    var url="ajaxinterface.php";
    var params="instruction=saveReportLayout&"+
               "reportname="+reportName+"&"+
               "reportlayout="+document.getElementById('reportlayoutselect').value+"&"+
               "thedata="+json_encode_dee(ajaxanswer);
    jsonhttp.open("POST",url,true);
    jsonhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    jsonhttp.setRequestHeader("Content-length", params.length);
    jsonhttp.setRequestHeader("Connection", "close");
    jsonhttp.send(params);
  }
  else
  { document.getElementById('reportlayout').innerHTML="";
  }
}
function saveAsLayoutPage(reportName)
{ if(document.getElementById('reportlayoutselect').options[document.getElementById('reportlayoutselect').selectedIndex].value)
  { var jsonhttp;
    if(window.XMLHttpRequest)
      jsonhttp=new XMLHttpRequest();
    else if(window.activeXObject)
      jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
    else
      alert("Not supported on non-xmlhttp machines");
    jsonhttp.onreadystatechange=function()
    { if(jsonhttp.readyState==4)
      { //alert(jsonhttp.responseText);
        alert(document.getElementById("tempname").value);
        layouts=eval("("+jsonhttp.responseText+")");
        for(i=document.getElementById('reportlayoutselect').options.length-1;i>=0;i--)
          document.getElementById('reportlayoutselect').remove(i);
        for(i=0,j=0;i<layouts.length;i++)
        { optn = document.createElement("option");
          optn.text = layouts[i];
          optn.value = layouts[i];
          document.getElementById('reportlayoutselect').options.add(optn);
          if(layouts[i]==document.getElementById("tempname").value)
            j=i;
        }
        document.getElementById('reportlayoutselect').selectedIndex=j;
      }
    };
    var url="ajaxinterface.php";
    document.getElementById("tempname").value=prompt('Layout name:','My layout name');
    var params="instruction=saveReportLayout&"+
               "reportname="+reportName+"&"+
               "reportlayout="+document.getElementById("tempname").value+"&"+
               "thedata="+json_encode_dee(ajaxanswer);
    jsonhttp.open("POST",url,true);
    jsonhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    jsonhttp.setRequestHeader("Content-length", params.length);
    jsonhttp.setRequestHeader("Connection", "close");
    jsonhttp.send(params);
  }
  else
  { document.getElementById('reportlayout').innerHTML="";
  }
}
function newvalue(thefield)
{ for(i=0;(i<this.ajaxanswer.length)&&(this.ajaxanswer[i]["fieldname"]!=thefield);i++)
    ;
  this.ajaxanswer[i]["fieldposition"]=prompt('Give a new value for '+thefield,this.ajaxanswer[i]["fieldposition"]);
  setLayoutPageCallback();
}
function newnamevalue(thefield,thevalue,thename)
{ for(i=0;(i<this.ajaxanswer.length)&&(this.ajaxanswer[i]["fieldname"]!=thefield);i++)
    ;
  this.ajaxanswer[i][thevalue]=prompt('Give a new value for '+thename+' of '+thefield,this.ajaxanswer[i][thevalue]);
  setLayoutPageCallback();
}

function setLayoutPageCallback()
{ echostring='<hr />';
  echostring+="<table>";
  for(i=0,j=1;i<this.ajaxanswer.length;i++)
  { if(this.ajaxanswer[i]["fieldstyle"]=="LAYOUTMETADATA")
    { if(!(i%3))
      { echostring+="<tr class=\"type"+(j%2)+"\">";  
        j++;
      }
      echostring+="<td class=\"right\">"+this.ajaxanswer[i]["fieldname"]+"</td>";
      echostring+="<td><span class=\"alike\" onclick=\"newvalue('"+this.ajaxanswer[i]["fieldname"]+"')\">"+this.ajaxanswer[i]["fieldposition"]+"</span></td>";
      if(!((i+1)%3))
      { echostring+="</tr>";  
      }
    }
  }
  if((i%3))
  { echostring+="</tr>";  
  }
  echostring+="</table>";
  echostring+="<hr />";
  echostring+="<table>";
  echostring+="<tr class=\"type3\">";
  echostring+="<td class=\"right width125px\">"+"Name"+"</td>";
  echostring+="<td>"+"On line-y"+"</td>";
  echostring+="<td>"+"Position-x"+"</td>";
  echostring+="<td>"+"Width"+"</td>";
  echostring+="<td>"+"Style"+"</td>";
  echostring+="</tr>";  
  for(i=0,j=0;i<this.ajaxanswer.length;i++)
  { if(this.ajaxanswer[i]["fieldstyle"]!="LAYOUTMETADATA")
    { echostring+="<tr class=\"type"+(j%2)+"\">";  
      echostring+="<td class=\"right width125px\">"+this.ajaxanswer[i]["fieldname"]+"</td>";
      echostring+="<td onclick=\"newnamevalue('"+this.ajaxanswer[i]["fieldname"] +"','fieldline'    ,'"+"On line-y" +"');\"><span class=\"alike\">"+this.ajaxanswer[i]["fieldline"]    +"</span></td>";
      echostring+="<td onclick=\"newnamevalue('"+this.ajaxanswer[i]["fieldname"] +"','fieldposition','"+"Position-x"+"');\"><span class=\"alike\">"+this.ajaxanswer[i]["fieldposition"]+"</span></td>";
      echostring+="<td onclick=\"newnamevalue('"+this.ajaxanswer[i]["fieldname"] +"','fieldwidth'   ,'"+"Width"     +"');\"><span class=\"alike\">"+this.ajaxanswer[i]["fieldwidth"]   +"</span></td>";
      echostring+="<td onclick=\"newnamevalue('"+this.ajaxanswer[i]["fieldname"] +"','fieldstyle'   ,'"+"Style"     +"');\"><span class=\"alike\">"+this.ajaxanswer[i]["fieldstyle"]   +"</span></td>";
      echostring+="</tr>";
      j++;
    }
  }
  echostring+="</table>";
  echostring+="<hr />";	  
  document.getElementById('reportlayout').innerHTML=echostring;
}
function savereportlayout()
{ thename=prompt('Save report as...',document.getElementById('reportlayoutselect').toString());
}