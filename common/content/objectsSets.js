function generate()
{ i=0;
  while(theobject=document.getElementById('R'+i))
  { var load = window.open('objectsSet.pdf?theobject='+urlencode(document.getElementById('R'+i).value)+
                                         '&theSet='+urlencode(document.getElementById('R'+i+'Dfov').value)+
                                         '&thedsos='+urlencode(document.getElementById('R'+i+'Ddsos').value)+
                                         '&thestars='+urlencode(document.getElementById('R'+i+'Dstars').value),
                           document.getElementById('R'+i).value
                           );
    alert('Click "Ok", only when '+document.getElementById('R'+i).value+' is finished!');
    document.getElementById('T'+i).setAttribute('style','background-color:#EEEEEE;');
    document.getElementById('R'+(i+1)).focus();
    i++;
  }
}
function generateOne(i)
{ var load = window.open('objectsSet.pdf?theobject='+urlencode(document.getElementById('R'+i).value)+
	                                   '&theSet='+urlencode(document.getElementById('R'+i+'Dfov').value)+
	                                   '&thedsos='+urlencode(document.getElementById('R'+i+'Ddsos').value)+
	                                   '&thestars='+urlencode(document.getElementById('R'+i+'Dstars').value)+
	                                   '&datapage='+urlencode(document.getElementById('datapage').checked)+
	                                   (document.getElementById('indexpage').checked==true?
	                                   ('&reportlayoutselect='+urlencode(document.getElementById('reportlayoutselect').value)):''),
                           document.getElementById('R'+i).value
                           );
  alert('Generating '+document.getElementById('R'+i).value+'.');
  document.getElementById('T'+i).setAttribute('style','background-color:#EEEEEE;');
  document.getElementById('R'+(i+1)).focus();
}