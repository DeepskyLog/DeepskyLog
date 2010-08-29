var objectList;
function generate()
{ i=0;
  while(theobject=document.getElementById('R'+i))
  { var load = window.open('objectsSet.pdf?theobject='+urlencode(document.getElementById('R'+i).title)+
                                         '&theSet='+urlencode(document.getElementById('R'+i+'Dfov').value)+
                                         '&thedsos='+urlencode(document.getElementById('R'+i+'Ddsos').value)+
                                         '&thestars='+urlencode(document.getElementById('R'+i+'Dstars').value)+
                                         '&thephotos='+urlencode(document.getElementById('R'+i+'Dphotos').value)+
	                                     '&datapage='+urlencode(document.getElementById('datapage').checked)+
                                         '&ephemerides='+urlencode(document.getElementById('ephemerides').checked)+
	                                     (document.getElementById('indexpage').checked==true?
	                                     ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):''),
                          ""
                           );
    alert(Langpdfseriesclickok+document.getElementById('R'+i).value+Langpdfserieswhenfinished);
    document.getElementById('T'+i).setAttribute('style','background-color:#DDDDDD;');
    document.getElementById('R'+(i+1)).focus();
    i++;
  }
}
function generateOne(i)
{ var load = window.open('objectsSet.pdf?theobject='+urlencode(document.getElementById('R'+i).title)+
        '&theSet='+urlencode(document.getElementById('R'+i+'Dfov').value)+
        '&thedsos='+urlencode(document.getElementById('R'+i+'Ddsos').value)+
        '&thestars='+urlencode(document.getElementById('R'+i+'Dstars').value)+
        '&thephotos='+urlencode(document.getElementById('R'+i+'Dphotos').value)+
        '&datapage='+urlencode(document.getElementById('datapage').checked)+
        '&ephemerides='+urlencode(document.getElementById('ephemerides').checked)+
        (document.getElementById('indexpage').checked==true?
        ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):''),
        "");
  alert(LangpdfseriesGenerating+document.getElementById('R'+i).value+'.');
  document.getElementById('T'+i).setAttribute('style','background-color:#DDDDDD;');
  document.getElementById('R'+(i+1)).focus();
}