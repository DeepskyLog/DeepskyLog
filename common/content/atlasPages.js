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
var decl;
var dsos;
var theoverlap = 0.15;
var ra;
var rato;
var stars;
var zoom;

function generateallonepass(item,msie,stepra,stepdecl)
{ if(stepra<0)
  { ra=rato;
    decl=decl-stepdecl;
  }
  else
    ra=(1.0*ra)-stepra;
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
	    if(item==0)
	    { if(msie)
        { var mywindow=window.open("",'mywindow'+item);
          mywindow.location='atlasPagesOnePass.pdf.php?item='+urlencode(item)+'&filename='+item;  
        }   
	      else
    	    window.open('atlasPagesOnePass.pdf.php?item='+urlencode(item)+'&filename='+item);
        generateallonepass(item,msie,0,0);
	    }
	    else
	    { temp=eval('('+jsonhttp.responseText+')');
	      tempra=Math.max(Math.floor(ra,0),0);
	      tempramin=Math.max(Math.round((ra-tempra)*60,0),0);
	      if(tempra<10) tempra='0'+tempra;
	      if(tempramin<10) tempramin='0'+tempramin;
	      if(((decl*1.0)>0))
	      { tempdecl=Math.floor(decl,0);
	        tempdeclmin=Math.round((decl-tempdecl)*60,1);
	        if(tempdecl<10) tempdecl='0'+tempdecl;
	        if(tempdeclmin<10) tempdeclmin='0'+tempdeclmin;
	      }
	      else
	      { tempdecl=Math.floor(decl,0)+1;
	        tempdeclmin=Math.round(-(decl-tempdecl)*60,1);
	        tempdecl=-tempdecl;
	        if(tempdecl<10) tempdecl='0'+tempdecl;
	        if(tempdeclmin<10) tempdeclmin='0'+tempdeclmin;
	        tempdecl='-'+tempdecl;
	      }	
	      if(msie)
	      { var mywindow=window.open("",'mywindow'+item);
	        mywindow.location='atlasPagesOnePass.pdf.php?item='+urlencode(item)+'&filename='+decl+'_'+item+'_'+ra.substr(0,5);  
	      }  
	      else
	      	window.open('atlasPagesOnePass.pdf.php?item='+urlencode(item)+'&filename='+item+'_'+tempdecl+'d'+tempdeclmin+'m'+' '+tempra+'h'+tempramin+'m','');
	      if(((ra*1.0)<(temp.raright))&&((temp.declbottom<-80)||((decl*1.0)<-80)))
	        return;
	      generateallonepass(item,msie,((ra-(temp.raright))*2*(1-theoverlap)),((decl-(temp.declbottom))*2*(1-theoverlap)));      
	    }
	  }
  };
  var url='ajaxinterface.php?instruction=atlasPages&item='+urlencode(item)+'&'+
          'ra='+ra+'&'+
          'decl='+decl+'&'+
          'stars='+stars+'&'+
          'dsos='+dsos+'&'+
          'zoom='+zoom+'&'+
          'pageorientation=';
  if(document.getElementById('pageorientationportrait').checked)
  	url+='portrait';
  else
  	url+='landscape';
  if(document.getElementById('pagesizea3').checked)
  	url+='&pagesize=a3';
  else
  	url+='&pagesize=a4';
  //alert(url);
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);	
}
function generateone(msie)
{ var jsonhttp;
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
      tempra=Math.floor(document.getElementById('ra').value,0);
      tempramin=Math.round((document.getElementById('ra').value-tempra)*60,0);
      if(tempra<10)
      	tempra='0'+tempra;
      if(tempramin<10)
      	tempramin='0'+tempramin;
      tempdecl=Math.floor(document.getElementById('decl').value,0);
      if(tempdecl>0)
      { tempdeclmin=Math.round((document.getElementById('decl').value-tempdecl)*60,1);
        if(tempdecl<10)
      	  tempdecl='0'+tempdecl;
        if(tempdeclmin<10)
      	  tempdeclmin='0'+tempdeclmin;
      }
      else
      { tempdeclmin=Math.round((document.getElementById('decl').value-tempdecl)*60,1);
        tempdecl=-tempdecl;
        if(tempdecl<10)
    	    tempdecl='0'+tempdecl;
        if(tempdeclmin<10)
    	    tempdeclmin='0'+tempdeclmin;
        tempdecl='-'+tempdecl;
      }	
      if(msie=='true')
      { var mywindow=window.open("",'mywindow'+item);
        mywindow.location='atlasPagesOnePass.pdf.php?item=1&filename='+document.getElementById('decl').value+'_'+item+'_'+ra.substr(0,5);  
      }  
      else
      	window.open('atlasPagesOnePass.pdf.php?item=1&filename='+tempdecl+'d'+tempdeclmin+'m'+'_'+tempra+'h'+tempramin+'m','');
    }
  };
  var url='ajaxinterface.php?instruction=atlasPages&item=1&'+
          'ra='+document.getElementById('ra').value+'&'+
          'decl='+document.getElementById('decl').value+'&'+
          'stars='+stars+'&'+
          'dsos='+dsos+'&'+
          'zoom='+zoom+'&'+
          'pageorientation=';
  if(document.getElementById('pageorientationportrait').checked)
  	url+='portrait';
  else
  	url+='landscape';
  if(document.getElementById('pagesizea3').checked)
  	url+='&pagesize=a3';
  else
  	url+='&pagesize=a4';
  //alert(url);
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}
function generateoneoverview(i,msie)
{ stars=10;
  dsos=10;
  zoom=13;
  generateone(msie);
}
function generateonelookup(i,msie)
{	stars=12;
  dsos=13;
  zoom=15;
  generateone(msie);
}
function generateonedetail(i,msie)
{ stars=15;
  dsos=15;
  zoom=17;
  generateone(msie);
}
function generateoverviewallonepass(item,msie,stepra,stepdecl)
{ if(document.getElementById('pagesizea4').checked)
  { if(document.getElementById('pageorientationportrait').checked)
  	  decl=80.05;
    else
  	  decl=82.99;
    stars=10;
    dsos=10;
  }
  else if(document.getElementById('pagesizea3').checked)
  {	if(document.getElementById('pageorientationportrait').checked)
      decl=79.69;
    else
      decl=82.99;
    stars=11;
    dsos=12;
  }
  rato=24;
  zoom=13;
  ra=rato;
  generateallonepass(-1,msie,0,0);
}
function generatelookupallonepass(item,msie,stepra,stepdecl)
{ if(document.getElementById('pagesizea4').checked)
	{ if(document.getElementById('pageorientationportrait').checked)
  	    decl=84.29;
	  else
	    decl=85.99;
	  stars=12;
	  dsos=12;
	}
	else if(document.getElementById('pagesizea3').checked)
	{	if(document.getElementById('pageorientationportrait').checked)
	  	decl=84.99;
	  else
	  	decl=85.99;
	  stars=13;
	  dsos=14;
	}
  rato=24;
  zoom=15;
  ra=rato;
  generateallonepass(-1,msie,0,0);
}
function generatedetailallonepass(item,msie,stepra,stepdecl)
{ if(document.getElementById('pagesizea4').checked)
  { if(document.getElementById('pageorientationportrait').checked)
	  decl=87.14;
    else
      decl=87.99;
    stars=14;
    dsos=20;
  }
  else if(document.getElementById('pagesizea3').checked)
  { if(document.getElementById('pageorientationportrait').checked)
  	  decl=87.04;
    else
  	  decl=87.99;
    stars=15;
    dsos=20;
  }
  rato=24;
  zoom=17;
  ra=rato;
  generateallonepass(-1,msie,0,0);
}