function clearFields()
{ document.getElementById('seen').selectedIndex=0;
  document.getElementById('catalog').selectedIndex=0;
  document.getElementById('catNumber').value='';
  document.getElementById('atlas').selectedIndex=0;
  document.getElementById('atlasPageNumber').value='';
  document.getElementById('con').selectedIndex=0;
  document.getElementById('conto').selectedIndex=0;
  document.getElementById('type').selectedIndex=0;
  document.getElementById('minDeclDegrees').value='';
  document.getElementById('minDeclMinutes').value='';
  document.getElementById('minDeclSeconds').value='';
  document.getElementById('maxDeclDegrees').value='';
  document.getElementById('maxDeclMinutes').value='';
  document.getElementById('maxDeclSeconds').value='';
  document.getElementById('minRAHours').value='';
  document.getElementById('minRAMinutes').value='';
  document.getElementById('minRASeconds').value='';
  document.getElementById('maxRAHours').value='';
  document.getElementById('maxRAMinutes').value='';
  document.getElementById('maxRASeconds').value='';
  document.getElementById('maxMag').value='';
  document.getElementById('minMag').value='';
  document.getElementById('maxSB').value='';
  document.getElementById('minSB').value='';
  document.getElementById('minSize').value='';
  document.getElementById('size_min_units').selectedIndex=0;
  document.getElementById('maxSize').value='';
  document.getElementById('size_max_units').selectedIndex=0;
  document.getElementById('minContrast').value='';
  document.getElementById('maxContrast').value='';
  document.getElementById('inList').selectedIndex=0;
  document.getElementById('descriptioncontains').value='';
//document.getElementById('notInList').selectedIndex=0;
  var temp=document.getElementById("temp").value;
  while((pos=temp.indexOf('/'))>=0)
  { document.getElementById(temp.substr(0,pos)).checked='';
    temp=temp.substr(pos+1);
  }
  document.getElementById('excludeexceptseen').checked='';
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
          "observerquerytype="+"obj";
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
  query=query+'catNumber='+document.getElementById('catNumber').value+'&';
  query=query+'atlas='+document.getElementById('atlas').selectedIndex+'&';
  query=query+'atlasPageNumber='+document.getElementById('atlasPageNumber').value+'&';
  query=query+'con='+document.getElementById('con').selectedIndex+'&';
  query=query+'conto='+document.getElementById('conto').selectedIndex+'&';
  query=query+'type='+document.getElementById('type').selectedIndex+'&';
  query=query+'minDeclDegrees='+document.getElementById('minDeclDegrees').value+'&';
  query=query+'minDeclMinutes='+document.getElementById('minDeclMinutes').value+'&';
  query=query+'minDeclSeconds='+document.getElementById('minDeclSeconds').value+'&';
  query=query+'maxDeclDegrees='+document.getElementById('maxDeclDegrees').value+'&';
  query=query+'maxDeclMinutes='+document.getElementById('maxDeclMinutes').value+'&';
  query=query+'maxDeclSeconds='+document.getElementById('maxDeclSeconds').value+'&';
  query=query+'minRAHours='+document.getElementById('minRAHours').value+'&';
  query=query+'minRAMinutes='+document.getElementById('minRAMinutes').value+'&';
  query=query+'minRASeconds='+document.getElementById('minRASeconds').value+'&';
  query=query+'maxRAHours='+document.getElementById('maxRAHours').value+'&';
  query=query+'maxRAMinutes='+document.getElementById('maxRAMinutes').value+'&';
  query=query+'maxRASeconds='+document.getElementById('maxRASeconds').value+'&';
  query=query+'maxMag='+document.getElementById('maxMag').value+'&';
  query=query+'minMag='+document.getElementById('minMag').value+'&';
  query=query+'maxSB='+document.getElementById('maxSB').value+'&';
  query=query+'minSB='+document.getElementById('minSB').value+'&';
  query=query+'minSize='+document.getElementById('minSize').value+'&';
  query=query+'size_min_units='+document.getElementById('size_min_units').selectedIndex+'&';
  query=query+'maxSize='+document.getElementById('maxSize').value+'&';
  query=query+'size_max_units='+document.getElementById('size_max_units').selectedIndex+'&';
  query=query+'minContrast='+document.getElementById('minContrast').value+'&';
  query=query+'maxContrast='+document.getElementById('maxContrast').value+'&';
  query=query+'inList='+document.getElementById('inList').selectedIndex+'&';
  query=query+'descriptioncontains='+document.getElementById('descriptioncontains').value+'&';
//document.getElementById('notInList').selectedIndex=0;
  var temp=document.getElementById("temp").value;
  while((pos=temp.indexOf('/'))>=0)
  { query=query+temp.substr(0,pos)+"="+document.getElementById(temp.substr(0,pos)).checked+'&';
    temp=temp.substr(pos+1);
  }
  query=query+'excludeexceptseen='+document.getElementById('excludeexceptseen').checked;  
  newqueryname=prompt('Give the name','queryname');
  if(!(newqueryname))
    newqueryname='newqueryname';
  var url="ajaxinterface.php"            +"?"+
          "instruction=saveObserverQuery" +"&"+
          "observerquerytype="+"obj"     +"&"+
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
      document.getElementById(field).text=value;
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
    "observerquerytype="+"obj"     +"&"+
    "observerqueryname="+document.getElementById('observerqueries').options[document.getElementById('observerqueries').selectedIndex].text;
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}
