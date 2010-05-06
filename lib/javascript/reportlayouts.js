var ajaxanswer=new Array();
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
               "reportuser=defaultuser&"+
               "reportname="+reportName+"&"+
               "pdfTitle="+pdfTitle+"&"+
               "SID="+SID+"&"+
               "sort="+thesort+"&"+
               "reportlayout="+document.getElementById('reportlayoutselect').value+"&"+
               "thedata="+json_encode_dee(ajaxanswer);
    jsonhttp.open("POST",url,true);
    jsonhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    jsonhttp.setRequestHeader("Content-length", params.length);
    jsonhttp.setRequestHeader("Connection", "close");
    jsonhttp.send(params);
    /*
    var exdate= new Date();
    exdate.setDate(exdate.getDate()+1);
    document.cookie="reportlayoutcookie=["+"DEE"+"];expires="+exdate.toUTCString();	  
	location=theurl;
	*/
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

function setLayoutPageCallback()
{ echostring='';
  echostring+="<table>";
  echostring+="<tr class=\"type3\">";
  echostring+="<td>"+""+"</td>";
  echostring+="<td>"+"Positie"+"</td>";
  echostring+="</tr>";
  for(i=0;i<this.ajaxanswer.length;i++)
    if(this.ajaxanswer[i]["fieldstyle"]=="LAYOUTMETADATA")
    { echostring+="<tr class=\"type"+(i%2)+"\">";  
      echostring+="<td class=\"right width125px\">"+this.ajaxanswer[i]["fieldname"]+"</td>";
      echostring+="<td><span class=\"alike\"  onclick=\"newvalue('"+this.ajaxanswer[i]["fieldname"]+"')\">"+this.ajaxanswer[i]["fieldposition"]+"</span></td>";
      echostring+="</tr>";
    }
  echostring+="</table>";
  echostring+="<hr />";
  for(i=0;i<this.ajaxanswer.length;i++)
    if(this.ajaxanswer[i]["fieldstyle"]!="LAYOUTMETADATA")
      echostring+=this.ajaxanswer[i]["fieldname"]+": "+this.ajaxanswer[i]["fieldposition"]+"<br />";
  echostring+="<hr />";	  
  document.getElementById('reportlayout').innerHTML=echostring;
}
function savereportlayout()
{ thename=prompt('Save report as...',document.getElementById('reportlayoutselect').toString());
}