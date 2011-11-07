function setAllCheckboxes(theCBs,theNumber,theValue)
{ for(i=0;i<theNumber;i++)
    document.getElementById('R'+i+theCBs).checked=theValue;
}
function setCheckedValues(theField,theNumber,theValue,thecheckfield)
{ if(!(thecheckfield))
	thecheckfield=theField;
  for(i=0;i<theNumber;i++)
	if(document.getElementById('R'+i+'CB'+thecheckfield).checked==true)
	  document.getElementById('R'+i+'D'+theField).value=theValue;
}
function generateallonepass(item,msie)
{ var jsonhttp;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Atlas pages are not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { item=jsonhttp.responseText*1;
      if(msie)
      { var mywindow=window.open("",'mywindow'+item);
        mywindow.location='objectsSetsOnePass.pdf.php?item='+item;  
      }  
      else
    	window.open('objectsSetsOnePass.pdf.php?item='+item,'');
      if(document.getElementById('R'+(item+1)))
        generateallonepass(item+1);
      else
      { document.getElementById('thecounter').innerHTML="";
      }
    }
  };
  document.getElementById('thecounter').innerHTML=(LangpdfseriesGenerating+document.getElementById('R'+item).value+'.');
  var url='ajaxinterface.php?instruction=allonepass'+
  '&theSet='+urlencode(document.getElementById('R'+item+'Dfovs').value)+
  '&thedsos='+urlencode(document.getElementById('R'+item+'Ddsos').value)+
  '&thestars='+urlencode(document.getElementById('R'+item+'Dstars').value)+
  '&thephotos='+urlencode(document.getElementById('R'+item+'Dphotos').value)+
  '&datapage='+urlencode(document.getElementById('datapage').checked)+
  '&ephemerides='+urlencode(document.getElementById('ephemerides').checked)+
  '&yearephemerides='+urlencode(document.getElementById('yearephemerides').checked)+
  (document.getElementById('indexpage').checked==true?
  ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):'')+
  '&item='+item;
  //alert(url);
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}
function generateOne(i,msie)
{ if(msie)
  { document.location='objectsSet.pdf.php?theobject='+urlencode(document.getElementById('R'+i).title)+
	  '&theShowname='+urlencode(document.getElementById('R'+i).value)+
	  '&theSet='+urlencode(document.getElementById('R'+i+'Dfovs').value)+
      '&thedsos='+urlencode(document.getElementById('R'+i+'Ddsos').value)+
      '&thestars='+urlencode(document.getElementById('R'+i+'Dstars').value)+
      '&thephotos='+urlencode(document.getElementById('R'+i+'Dphotos').value)+
      '&datapage='+urlencode(document.getElementById('datapage').checked)+
      '&ephemerides='+urlencode(document.getElementById('ephemerides').checked)+
      '&yearephemerides='+urlencode(document.getElementById('yearephemerides').checked)+
      (document.getElementById('indexpage').checked==true?
      ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):'');
  }
  else
    window.open('objectsSet.pdf.php?theobject='+urlencode(document.getElementById('R'+i).title)+
    	'&theShowname='+urlencode(document.getElementById('R'+i).value)+
        '&theSet='+urlencode(document.getElementById('R'+i+'Dfovs').value)+
        '&thedsos='+urlencode(document.getElementById('R'+i+'Ddsos').value)+
        '&thestars='+urlencode(document.getElementById('R'+i+'Dstars').value)+
        '&thephotos='+urlencode(document.getElementById('R'+i+'Dphotos').value)+
        '&datapage='+urlencode(document.getElementById('datapage').checked)+
        '&ephemerides='+urlencode(document.getElementById('ephemerides').checked)+
        '&yearephemerides='+urlencode(document.getElementById('yearephemerides').checked)+
        (document.getElementById('indexpage').checked==true?
        ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):''));
  document.getElementById('thecounter').innerHTML=(LangpdfseriesGenerating+document.getElementById('R'+i).value+'.');
  document.getElementById('T'+i).setAttribute('style','background-color:#DDDDDD;');
  document.getElementById('R'+(i+1)).focus();
}