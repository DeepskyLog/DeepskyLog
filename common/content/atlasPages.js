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
        mywindow.location='atlasPagesOnePass.pdf?item='+item;  
      }  
      else
    	  window.open('atlasPagesOnePass.pdf?item='+item,'');
      if(item<1)
        generateallonepass(item+1);      
    }
  };
  var url='ajaxinterface.php?instruction=atlasPages&item=1&ra=30&decl=40';
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}
function generateOne(i,msie)
{ if(msie)
  { document.location='objectsSet.pdf?theobject='+urlencode(document.getElementById('R'+i).title)+
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
    window.open('objectsSet.pdf?theobject='+urlencode(document.getElementById('R'+i).title)+
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