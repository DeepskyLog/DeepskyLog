function clearFields()
{ document.getElementById('seen').selectedIndex=0;
  document.getElementById('catalog').selectedIndex=0;
  document.getElementById('number').value='';
  document.getElementById('atlas').selectedIndex=0;
  document.getElementById('atlasPageNumber').value='';
  document.getElementById('con').selectedIndex=0;
  //document.getElementById('conto').selectedIndex=0;
  document.getElementById('minDeclDegrees').value='';
  document.getElementById('minDeclMinutes').value='';
  document.getElementById('minDeclSeconds').value='';
  document.getElementById('type').selectedIndex=0;
  document.getElementById('maxDeclDegrees').value='';
  document.getElementById('maxDeclMinutes').value='';
  document.getElementById('maxDeclSeconds').value='';
  document.getElementById('maxmag').value='';
  document.getElementById('minRAhours').value='';
  document.getElementById('minRAminutes').value='';
  document.getElementById('minRAseconds').value='';
  document.getElementById('minmag').value='';
  document.getElementById('maxRAhours').value='';
  document.getElementById('maxRAminutes').value='';
  document.getElementById('maxRAseconds').value='';
  document.getElementById('minsb').value='';
  document.getElementById('minsize').value='';
  document.getElementById('size_min_units').selectedIndex=0;
  document.getElementById('maxsb').value='';
  document.getElementById('maxsize').value='';
  document.getElementById('size_max_units').selectedIndex=0;
  document.getElementById('observer').selectedIndex=0;
  document.getElementById('instrument').selectedIndex=0;
  document.getElementById('minday').value='';
  document.getElementById('minmonth').selectedIndex=0;
  document.getElementById('minyear').value='';
  document.getElementById('mindiameter').value='';
  document.getElementById('mindiameterunits').selectedIndex=0;
  document.getElementById('maxday').value='';
  document.getElementById('maxmonth').selectedIndex=0;
  document.getElementById('maxyear').value='';
  document.getElementById('maxdiameter').value='';
  document.getElementById('maxdiameterunits').selectedIndex=0;
  document.getElementById('site').selectedIndex=0;
  document.getElementById('minLatDegrees').value='';
  document.getElementById('minLatMinutes').value='';
  document.getElementById('minLatSeconds').value='';
  document.getElementById('minlimmag').value='';
  document.getElementById('maxLatDegrees').value='';
  document.getElementById('maxLatMinutes').value='';
  document.getElementById('maxLatSeconds').value='';
  document.getElementById('maxlimmag').value='';
  document.getElementById('maxseeing').value='';
  document.getElementById('drawings').checked=false;
  document.getElementById('minvisibility').selectedIndex=0;
  document.getElementById('description').value='';
  document.getElementById('maxvisibility').selectedIndex=0;
  document.getElementById('inlist').selectedIndex=0;
  //document.getElementById('notinlist').selectedIndex=0;
  var temp=document.getElementById("temp").value;
  while((pos=temp.indexOf('/'))>=0)
  { document.getElementById(temp.substr(0,pos)).checked='';
    temp=temp.substr(pos+1);
  }
}
function setobserverqueries()
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
      queries=eval("("+jsonhttp.responseText+")");
      for(i=document.getElementById('observerqueries').options.length-1;i>=0;i--)
        document.getElementById('observerqueries').remove(i);

      optn = document.createElement("option");
      optn.text = "-----";
      optn.value = "------";
      document.getElementById('observerqueries').options.add(optn);
      
      for(i=0;i<queries.length;i++)
      { optn = document.createElement("option");
        optn.value = queries[i]["observerquery"];
        optn.text = queries[i]["observerqueryname"];
        document.getElementById('observerqueries').options.add(optn);
      }
    }
  };
  var url="ajaxinterface.php"              +"?"+
          "instruction=getObserverQueries" +"&"+
          "observerquerytype="+"obs";
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}

function saveObserverQueryAs()
{ var jsonhttp, newqueryname;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { //alert(jsonhttp.responseText);
      queries=eval("("+jsonhttp.responseText+")");
      for(i=document.getElementById('observerqueries').options.length-1;i>=0;i--)
        document.getElementById('observerqueries').remove(i);

      optn = document.createElement("option");
      optn.text = "-----";
      optn.value = "------";
      document.getElementById('observerqueries').options.add(optn);
      
      for(i=0;i<queries.length;i++)
      { optn = document.createElement("option");
        optn.value = queries[i]["observerquery"];
        optn.text = queries[i]["observerqueryname"];
        document.getElementById('observerqueries').options.add(optn);
      }
      document.getElementById('observerqueries').selectedIndex=i;
    }
  };

  query="";
  query=query+'seen='+document.getElementById('seen').selectedIndex+'&';
  query=query+'catalog='+document.getElementById('catalog').selectedIndex+'&';
  query=query+'number='+document.getElementById('number').value+'&';
  query=query+'atlas='+document.getElementById('atlas').selectedIndex+'&';
  query=query+'atlasPageNumber='+document.getElementById('atlasPageNumber').value+'&';
  query=query+'con='+document.getElementById('con').selectedIndex+'&';
  //query=query+'conto='+document.getElementById('conto').selectedIndex+'&';
  query=query+'type='+document.getElementById('type').selectedIndex+'&';
  query=query+'minDeclDegrees='+document.getElementById('minDeclDegrees').value+'&';
  query=query+'minDeclMinutes='+document.getElementById('minDeclMinutes').value+'&';
  query=query+'minDeclSeconds='+document.getElementById('minDeclSeconds').value+'&';
  query=query+'maxDeclDegrees='+document.getElementById('maxDeclDegrees').value+'&';
  query=query+'maxDeclMinutes='+document.getElementById('maxDeclMinutes').value+'&';
  query=query+'maxDeclSeconds='+document.getElementById('maxDeclSeconds').value+'&';
  query=query+'minRAhours='+document.getElementById('minRAhours').value+'&';
  query=query+'minRAminutes='+document.getElementById('minRAminutes').value+'&';
  query=query+'minRAseconds='+document.getElementById('minRAseconds').value+'&';
  query=query+'maxRAhours='+document.getElementById('maxRAhours').value+'&';
  query=query+'maxRAminutes='+document.getElementById('maxRAminutes').value+'&';
  query=query+'maxRAseconds='+document.getElementById('maxRAseconds').value+'&';
  query=query+'maxmag='+document.getElementById('maxmag').value+'&';
  query=query+'minmag='+document.getElementById('minmag').value+'&';
  query=query+'maxsb='+document.getElementById('maxsb').value+'&';
  query=query+'minsb='+document.getElementById('minsb').value+'&';
  query=query+'minsize='+document.getElementById('minsize').value+'&';
  query=query+'size_min_units='+document.getElementById('size_min_units').selectedIndex+'&';
  query=query+'maxsize='+document.getElementById('maxsize').value+'&';
  query=query+'size_max_units='+document.getElementById('size_max_units').selectedIndex+'&';
  query=query+'observer='+document.getElementById('observer').selectedIndex+'&';
  query=query+'instrument='+document.getElementById('instrument').selectedIndex+'&';
  query=query+'minday='+document.getElementById('minday').value+'&';
  query=query+'minmonth='+document.getElementById('minmonth').selectedIndex+'&';
  query=query+'minyear='+document.getElementById('minyear').value+'&';
  query=query+'mindiameter='+document.getElementById('mindiameter').value+'&';
  query=query+'mindiameterunits='+document.getElementById('mindiameterunits').selectedIndex+'&';
  query=query+'maxday='+document.getElementById('maxday').value+'&';
  query=query+'maxmonth='+document.getElementById('maxmonth').selectedIndex+'&';
  query=query+'maxyear='+document.getElementById('maxyear').value+'&';
  query=query+'maxdiameter='+document.getElementById('maxdiameter').value+'&';
  query=query+'maxdiameterunits='+document.getElementById('maxdiameterunits').selectedIndex+'&';
  query=query+'site='+document.getElementById('site').selectedIndex+'&';
  query=query+'minLatDegrees='+document.getElementById('minLatDegrees').value+'&';
  query=query+'minLatMinutes='+document.getElementById('minLatMinutes').value+'&';
  query=query+'minLatSeconds='+document.getElementById('minLatSeconds').value+'&';
  query=query+'minlimmag='+document.getElementById('minlimmag').value+'&';
  query=query+'maxLatDegrees='+document.getElementById('maxLatDegrees').value+'&';
  query=query+'maxLatMinutes='+document.getElementById('maxLatMinutes').value+'&';
  query=query+'maxLatSeconds='+document.getElementById('maxLatSeconds').value+'&';
  query=query+'maxlimmag='+document.getElementById('maxlimmag').value+'&';
  query=query+'maxseeing='+document.getElementById('maxseeing').value+'&';
  query=query+'drawings='+document.getElementById('drawings').checked+'&';
  query=query+'minvisibility='+document.getElementById('minvisibility').selectedIndex+'&';
  query=query+'description='+document.getElementById('description').value+'&';
  query=query+'maxvisibility='+document.getElementById('maxvisibility').selectedIndex+'&';
  var temp=document.getElementById("temp").value;
  while((pos=temp.indexOf('/'))>=0)
  { query=query+temp.substr(0,pos)+"="+document.getElementById(temp.substr(0,pos)).checked+'&';
    temp=temp.substr(pos+1);
  }
  newqueryname=prompt('Give the name','queryname');
  if(!(newqueryname))
    newqueryname='newqueryname';
  var url="ajaxinterface.php"            +"?"+
          "instruction=saveObserverQuery" +"&"+
          "observerquerytype="+"obs"     +"&"+
          "observerqueryname="+  newqueryname   +"&"+
          query;
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}

function restoreQuery()
{ document.getElementById('deletequery').style.visibility='visible';
  field='';
  value='';
  temp=document.getElementById('observerqueries').value;
  temp=temp.substring(temp.indexOf('&seen=')+1);
  while(temp)
  { field=temp.substring(0,temp.indexOf('='));
    temp=temp.substring(temp.indexOf('=')+1);
    value=temp.substring(0,temp.indexOf('&'));
    temp=temp.substring(temp.indexOf('&')+1);
    if(document.getElementById(field).type=='select-one')
      document.getElementById(field).selectedIndex=value;
    else if(document.getElementById(field).type=='text')
      document.getElementById(field).value=value;
    else if(document.getElementById(field).type=='checkbox')
      document.getElementById(field).checked=(value==='true'?true:false);
  }
}

function removeQuery()
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
      queries=eval("("+jsonhttp.responseText+")");
      for(i=document.getElementById('observerqueries').options.length-1;i>=0;i--)
        document.getElementById('observerqueries').remove(i);

      optn = document.createElement("option");
      optn.text = "-----";
      optn.value = "------";
      document.getElementById('observerqueries').options.add(optn);
      
      for(i=0;i<queries.length;i++)
      { optn = document.createElement("option");
        optn.value = queries[i]["observerquery"];
        optn.text = queries[i]["observerqueryname"];
        document.getElementById('observerqueries').options.add(optn);
      }
    }
  };  
  var url="ajaxinterface.php"            +"?"+
    "instruction=removeObserverQuery" +"&"+
    "observerquerytype="+"obs"     +"&"+
    "observerqueryname="+document.getElementById('observerqueries').options[document.getElementById('observerqueries').selectedIndex].text;
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}
