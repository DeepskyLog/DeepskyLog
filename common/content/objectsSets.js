var objectList;
function generateallonepass(item)
{ var jsonhttp;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Atlas pages are not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { alert('theitem '+item);
      window.location='objectsSetsOnePass.pdf?item='+item;
      item=jsonhttp.responseText;
      alert(item);
      /*if(item)
      { alert(item);
        generateallonepass(item);
      }
      else
    	alert('Done');
      */
    }
  };
  var url='ajaxinterface.php?instruction=allonepass'+
  '&theSet='+urlencode(document.getElementById('R'+item+'Dfov').value)+
  '&thedsos='+urlencode(document.getElementById('R'+item+'Ddsos').value)+
  '&thestars='+urlencode(document.getElementById('R'+item+'Dstars').value)+
  '&thephotos='+urlencode(document.getElementById('R'+item+'Dphotos').value)+
  '&datapage='+urlencode(document.getElementById('datapage').checked)+
  '&ephemerides='+urlencode(document.getElementById('ephemerides').checked)+
  '&yearephemerides='+urlencode(document.getElementById('yearephemerides').checked)+
  (document.getElementById('indexpage').checked==true?
  ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):'')+
  '&item=0';
  //alert(url);
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}
function generate()
{ i=0;
  while(theobject=document.getElementById('R'+i))
  { location=('objectsSet.pdf?theobject='+urlencode(document.getElementById('R'+i).title)+
                                         '&theSet='+urlencode(document.getElementById('R'+i+'Dfov').value)+
                                         '&thedsos='+urlencode(document.getElementById('R'+i+'Ddsos').value)+
                                         '&thestars='+urlencode(document.getElementById('R'+i+'Dstars').value)+
                                         '&thephotos='+urlencode(document.getElementById('R'+i+'Dphotos').value)+
	                                     '&datapage='+urlencode(document.getElementById('datapage').checked)+
                                         '&ephemerides='+urlencode(document.getElementById('ephemerides').checked)+
                                         '&yearephemerides='+urlencode(document.getElementById('yearephemerides').checked)+
	                                     (document.getElementById('indexpage').checked==true?
	                                     ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):''));
    alert(Langpdfseriesclickok+document.getElementById('R'+i).value+Langpdfserieswhenfinished);
    document.getElementById('T'+i).setAttribute('style','background-color:#DDDDDD;');
    document.getElementById('R'+(i+1)).focus();
    i++;
  }
}
function generateOne(i)
{ location=('objectsSet.pdf?theobject='+urlencode(document.getElementById('R'+i).title)+
        '&theSet='+urlencode(document.getElementById('R'+i+'Dfov').value)+
        '&thedsos='+urlencode(document.getElementById('R'+i+'Ddsos').value)+
        '&thestars='+urlencode(document.getElementById('R'+i+'Dstars').value)+
        '&thephotos='+urlencode(document.getElementById('R'+i+'Dphotos').value)+
        '&datapage='+urlencode(document.getElementById('datapage').checked)+
        '&ephemerides='+urlencode(document.getElementById('ephemerides').checked)+
        '&yearephemerides='+urlencode(document.getElementById('yearephemerides').checked)+
        (document.getElementById('indexpage').checked==true?
        ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):''));
  alert(LangpdfseriesGenerating+document.getElementById('R'+i).value+'.');
  document.getElementById('T'+i).setAttribute('style','background-color:#DDDDDD;');
  document.getElementById('R'+(i+1)).focus();
}