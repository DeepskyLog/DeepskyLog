var gridDimensions=new Array(
    		  new Array(180,80.00,2.000,3),                                                 // FoV, L grid distance in deg, D grid distance in deg, default limiting star magnitude level for this zoom level 
    		  new Array(150,60.00,2.000,3),
    		  new Array(120,50.00,2.000,3),
    		  new Array( 90,40.00,2.666,4),
    		  new Array( 75,30.00,2.000,4),
    		  new Array( 60,24.50,1.666,5),
    		  new Array( 45,20.00,1.333,5),
    		  new Array( 35,15.00,1.000,6),
    		  new Array( 30,12.00,0.800,6),
    		  new Array( 25,10.00,0.666,6),
    		  new Array( 20, 8.00,0.633,6),
    		  new Array( 15, 6.00,0.400,7),
    		  new Array( 10, 4.00,0.266,7),
    		  new Array(  7, 3.00,0.200,8),
    		  new Array(  5, 2.00,0.133,8),
    		  new Array(  4, 1.50,0.100,9),
    		  new Array(  3, 1.00,0.066,9),
    		  new Array(  2, 0.80,0.050,10),
    		  new Array(  1, 0.40,0.026,11),
    		  new Array(0.5, 0.20,0.012,12),
    		  new Array(0.25,0.20,0.012,14),
    		  new Array(0.2 ,0.20,0.012,16),
    		  new Array(0.15,0.20,0.012,16),
    		  new Array(0.1 ,0.20,0.012,16)
    		  );
function generateallonepass(item,msie,stepra,stepdecl)
{ if(stepra<0)
  { document.getElementById('ra').value=document.getElementById('rato').value;
    document.getElementById('decl').value=document.getElementById('decl').value-stepdecl;
  }
  else
    document.getElementById('ra').value=(1.0*document.getElementById('ra').value)-stepra;
  item=(item*1.0)+1;
  if(item<10) item='0'+item;
  if(item<100) item='0'+item;
  if(item<1000) item='0'+item;
  var jsonhttp;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Atlas pages are not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { //alert(jsonhttp.responseText);
	  temp=eval('('+jsonhttp.responseText+')');
      tempra=Math.max(Math.floor(document.getElementById('ra').value,0),0);
      tempramin=Math.max(Math.round((document.getElementById('ra').value-tempra)*60,0),0);
      if(tempra<10) tempra='0'+tempra;
      if(tempramin<10) tempramin='0'+tempramin;
      if(((document.getElementById('decl').value*1.0)>0))
      { tempdecl=Math.floor(document.getElementById('decl').value,0);
        tempdeclmin=Math.round((document.getElementById('decl').value-tempdecl)*60,1);
        if(tempdecl<10) tempdecl='0'+tempdecl;
        if(tempdeclmin<10) tempdeclmin='0'+tempdeclmin;
      }
      else
      { tempdecl=Math.floor(document.getElementById('decl').value,0)+1;
        tempdeclmin=Math.round(-(document.getElementById('decl').value-tempdecl)*60,1);
        tempdecl=-tempdecl;
        if(tempdecl<10) tempdecl='0'+tempdecl;
        if(tempdeclmin<10) tempdeclmin='0'+tempdeclmin;
        tempdecl='-'+tempdecl;
      }	
      if(msie)
      { var mywindow=window.open("",'mywindow'+item);
        mywindow.location='atlasPagesOnePass.pdf.php?item='+urlencode(item)+'&filename='+document.getElementById('decl').value+'_'+item+'_'+document.getElementById('ra').value.substr(0,5);  
      }  
      else
      	window.open('atlasPagesOnePass.pdf.php?item='+urlencode(item)+'&filename='+item+'_'+tempdecl+'d'+tempdeclmin+'m'+' '+tempra+'h'+tempramin+'m','');
      if(((document.getElementById('ra').value*1.0)<(temp.raright))&&((temp.declbottom<-80)||((document.getElementById('decl').value*1.0)<-80)))
        return;
      generateallonepass(item,msie,((document.getElementById('ra').value-(temp.raright))*2*(1-document.getElementById('theoverlap').value)),((document.getElementById('decl').value-(temp.declbottom))*2*(1-document.getElementById('theoverlap').value)));      
    }
  };
  var url='ajaxinterface.php?instruction=atlasPages&item='+urlencode(item)+'&'+
          'ra='+document.getElementById('ra').value+'&'+
          'decl='+document.getElementById('decl').value+'&'+
          'stars='+document.getElementById('stars').value+'&'+
          'dsos='+document.getElementById('dsos').value+'&'+
          'zoom='+document.getElementById('zoom').value;
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
function generateOneOverview(i,msie)
{
}
function generateOneLookup(i,msie)
{
}
function generateOneDetail(i,msie)
{
}
function generateoverviewallonepass(item,msie,stepra,stepdecl)
{ document.getElementById('declfrom').value=0;
  document.getElementById('declto').value=82.99;
  document.getElementById('rafrom').value=0;
  document.getElementById('rato').value=24;
  document.getElementById('stars').value=8;
  document.getElementById('dsos').value=10;
  document.getElementById('zoom').value=13;
  document.getElementById('ra').value=document.getElementById('rato').value;
  document.getElementById('decl').value=document.getElementById('declto').value;
  generateallonepass(item,msie,0,0);
}
function generatelookupallonepass(item,msie,stepra,stepdecl)
{ document.getElementById('declfrom').value=0;
  document.getElementById('declto').value=88.49;
  document.getElementById('rafrom').value=0;
  document.getElementById('rato').value=24;
  document.getElementById('stars').value=11;
  document.getElementById('dsos').value=13;
  document.getElementById('zoom').value=15;
  document.getElementById('ra').value=document.getElementById('rato').value;
  document.getElementById('decl').value=document.getElementById('declto').value;
  generateallonepass(item,msie,0,0);

}
function generatedetailallonepass(item,msie,stepra,stepdecl)
{ document.getElementById('declfrom').value=0;
  document.getElementById('declto').value=88.49;
  document.getElementById('rafrom').value=0; 
  document.getElementById('rato').value=24;
  document.getElementById('stars').value=15;
  document.getElementById('dsos').value=15;
  document.getElementById('zoom').value=17;
  document.getElementById('ra').value=document.getElementById('rato').value;
  document.getElementById('decl').value=document.getElementById('declto').value;
  generateallonepass(item,msie,0,0);
}