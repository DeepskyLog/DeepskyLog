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
var ra;
var rato=24;
var stars;
var zoom;

var magnegdecl=-89.50;

var atlaspages = new Array(new Array(12,20,26,30,32,32,30,26,20,12),                          // overview portrait
		                   new Array(12,20,30,36,42,48,52,54,54,54,52,48,42,36,30,20,12),     // lookup portrait
                           new Array(12,21,31,40,48,55,63,70,77,83,89,94,98,101,104,106,107,107,107,107,106,104,101,98,94,89,83,77,70,63,55,48,40,31,21,12), // Detail portrait
                           new Array(6,10,13,16,19,20,21,21,21,20,19,16,13,10,6),             // overview landscape
                           new Array(6,10,13,17,21,24,27,30,32,34,35,36,36,36,36,36,35,34,32,30,27,24,21,17,13,10,6), // lookup landscape
                           new Array(6,10,14,18,22,26,30,33,37,41,44,47,50,53,56,58,61,63,65,66,68,70,70,71,71,72,72,72,71,71,70,70,68,66,65,63,61,58,56,53,50,47,44,41,37,33,30,26,22,18,14,10,6) // detail landscape
                          );
var atlastype = 0;

function generateallonepassij(item,msie,i,j)
{ decl=(90+((-0.5-i)*(180/(atlaspages[atlastype].length))));
  ra  =(24+((-0.5-j)*(24 /(atlaspages[atlastype][i]))));
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
        generateallonepassij(item,msie,0,0,0,0);
	  }
	  else
	  { tempra=Math.max(Math.floor(ra,0),0);
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
	    
	    j++;
	    if(j==(atlaspages[atlastype][i]))
	    { j=0;
	      i++;
	      if(i==(atlaspages[atlastype].length))
          { if(msie)
		    { var mywindow=window.open("",'mywindow'+item);
	          mywindow.location='atlasPagesIndex.pdf.php';  
	        }  
	        else
	        { url='atlasPagesIndex.pdf.php?pageorientation=';
	          if(document.getElementById('pageorientationportrait').checked)
	        	  url+='portrait';
	          else
	        	url+='landscape';
	          if(document.getElementById('pagesizea3').checked)
	            url+='&pagesize=a3';
	          else
	            url+='&pagesize=a4';
	          window.open(url,'');  
	        }
	        return;
          }
	    }
	    generateallonepassij(item,msie,i,j);      
	  }
    }
  };
  var url='ajaxinterface.php?instruction=atlasPages&item='+urlencode(item)+'&'+
          'ra='+ra+'&'+
          'decl='+decl+'&'+
          'stars='+stars+'&'+
          'dsos='+dsos+'&'+
          'zoom='+zoom+'&'+
          'atlastype='+atlastype+'&'+
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
      tempra=Math.floor(ra,0);
      tempramin=Math.round((ra-tempra)*60,0);
      if(tempra<10)
      	tempra='0'+tempra;
      if(tempramin<10)
      	tempramin='0'+tempramin;
      tempdecl=Math.floor(decl,0);
      if(tempdecl>0)
      { tempdeclmin=Math.round((decl-tempdecl)*60,1);
        if(tempdecl<10)
      	  tempdecl='0'+tempdecl;
        if(tempdeclmin<10)
      	  tempdeclmin='0'+tempdeclmin;
      }
      else
      { tempdeclmin=Math.round((decl-tempdecl)*60,1);
        tempdecl=-tempdecl;
        if(tempdecl<10)
    	    tempdecl='0'+tempdecl;
        if(tempdeclmin<10)
    	    tempdeclmin='0'+tempdeclmin;
        tempdecl='-'+tempdecl;
      }	
      
      
      if(msie=='true')
      { var mywindow=window.open("",'mywindow'+item);
        mywindow.location='atlasPagesOnePass.pdf.php?item=1&filename='+decl+'_'+item+'_'+ra.substr(0,5);  
      }  
      else
      {	window.open('atlasPagesOnePass.pdf.php?item=1&filename='+tempdecl+'d'+tempdeclmin+'m'+'_'+tempra+'h'+tempramin+'m','');
        url='atlasPagesIndex.pdf.php?pageorientation=';
        if(document.getElementById('pageorientationportrait').checked)
        	url+='portrait';
        else
        	url+='landscape';
        if(document.getElementById('pagesizea3').checked)
        	url+='&pagesize=a3';
        else
        	url+='&pagesize=a4';
        //alert(url);
        window.open(url,'');
      } 
    }
  };
  var url='ajaxinterface.php?instruction=atlasPages&item=1&'+
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
function setradecl()
{ ra=document.getElementById('rah').value*1.0;
  ra+=document.getElementById('ram').value/60;
  ra+=document.getElementById('ras').value/3600;
  decl=document.getElementById('declh').value*1.0;
  if(document.getElementById('declh').value.substr(0,1)=='-')
  { decl-=document.getElementById('declm').value/60;
    decl-=document.getElementById('decls').value/3600;
  }
  else
  { decl+=document.getElementById('declm').value/60;
    decl+=document.getElementById('decls').value/3600;  
  }
  ra=max(min(ra,24),0);
  decl=max(min(decl,90),-90);
}
function generateoneoverview(i,msie)
{ setradecl();
  stars=11;
  dsos=12;
  zoom=13;
  generateone(msie);
}
function generateonelookup(i,msie)
{ setradecl();
  stars=13;
  dsos=14;
  zoom=15;
  generateone(msie);
}
function generateonedetail(i,msie)
{ setradecl();
  stars=15;
  dsos=20;
  zoom=17;
  generateone(msie);
}
function generateoverviewallonepass(item,msie,stepra,stepdecl)
{ if(document.getElementById('pagesizea4').checked)
  { stars=10;
    dsos=10;
  }
  else if(document.getElementById('pagesizea3').checked)
  {	stars=11;
    dsos=12;
  }
  zoom=13;
  if(document.getElementById('pageorientationportrait').checked)
  	atlastype=0;
  else
  	atlastype=3;
  generateallonepassij(-1,msie,0,0);
}
function generatelookupallonepass(item,msie,stepra,stepdecl)
{ if(document.getElementById('pagesizea4').checked)
  { stars=12;
	dsos=12;
  }
  else if(document.getElementById('pagesizea3').checked)
  { stars=13;
	dsos=14;
  }
  zoom=15;
  if(document.getElementById('pageorientationportrait').checked)
    atlastype=1;
  else
	atlastype=4;
  generateallonepassij(-1,msie,0,0);
}
function generatedetailallonepass(item,msie,stepra,stepdecl)
{ if(document.getElementById('pagesizea4').checked)
  { stars=14;
    dsos=20;
  }
  else if(document.getElementById('pagesizea3').checked)
  { stars=15;
    dsos=20;
  }
  zoom=17;
  ra=rato;
  if(document.getElementById('pageorientationportrait').checked)
    atlastype=2;
  else
	atlastype=5;
  generateallonepassij(-1,msie,0,0);
}