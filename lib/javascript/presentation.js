var theResizeElement, theResizeSize;
var theClientHeight, theClientWidth;
var div5Width, div5Height;
var div5StdWidth, div5StdHeight;
var div5Left=169; // see style sheet
var div5Top=90;   // see style sheet
var restoreColumns= new Array();
function findVerticalPosition(theElement)
{ var obj=document.getElementById(theElement);
  var top=obj.offsetTop;
   while(obj=obj.offsetParent)
   { top+=obj.offsetTop;
   } 
  return top;
 }
function resizeForm()
{ var height=window.innerHeight;
  var width=window.innerWidth;
  var mydoc=window.document;
  if(!(height))
  { height=document.documentElement.clientHeight;
    width =document.documentElement.clientWidth;
  }
  else if(!(height))
  { height=document.body.clientHeight;
    width =document.body.clientWidth;
  }
  theClientHeight=height;
  theClientWidth=width;
  document.getElementById('div4').style.height=(height-113)+'px';
  document.getElementById('div4').style.width =(width-163)+'px';
  document.getElementById('div4b').style.top = (height-128)+'px';
  document.getElementById('div4c').style.left =(width-174)+'px';
  document.getElementById('div4d').style.top = (height-128)+'px';
  document.getElementById('div4d').style.left =(width-174)+'px';
  div5Height=(height-127);
  div5StdHeight=(height-127);
  document.getElementById('div5').style.height=(height-123)+'px';
  div5Width=(width-185);
  div5StdWidth=(width-185);
  document.getElementById('div5').style.width= (width-185)+'px';
  document.getElementById('div6').style.width= (width-169)+'px';
  document.getElementById('div6').style.top=   (height-28)+'px';
  if(theResizeElement)
  { height-=findVerticalPosition(theResizeElement);
	height-=theResizeSize;
    if(height<400) 
	  height=400;
    document.getElementById(theResizeElement).style.height=height+'px';
  }
}
theResizeElement='';
theResizeSize=0;
function messageBox()
{ var height=window.innerHeight;
  var width=window.innerWidth;
  if(!(height))
  { height=document.documentElement.clientHeight;
    width =document.documentElement.clientWidth;
  }
  else if(!(height))
  { height=document.body.clientHeight;
    width =document.body.clientWidth;
  }
  document.getElementById('veil').style.height=height+'px';
  document.getElementById('veil').style.width =width+'px';
  document.getElementById('veil').style.filter='alpha(opacity=20)';
  document.getElementById('veil').style.opacity=0.2;
  document.getElementById('dialogback').style.top=((height/2)-92)+'px';
  document.getElementById('dialogback').style.left =((width/2)-142)+'px';
  document.getElementById('dialogback').style.height=200+'px';
  document.getElementById('dialogback').style.width =300+'px';
  document.getElementById('dialogback').style.filter='alpha(opacity=30)';
  document.getElementById('dialogback').style.opacity=0.3;
  document.getElementById('dialog').style.top=((height/2)-100)+'px';
  document.getElementById('dialog').style.left =((width/2)-150)+'px';
  document.getElementById('dialog').style.height=200+'px';
  document.getElementById('dialog').style.width =300+'px';
  document.getElementById('dialogdiv1').style.height=150+'px';
  document.getElementById('dialogdiv1').style.width =280+'px';
  document.getElementById('dialogdiv2').style.top=170+'px';
  document.getElementById('dialogdiv2').style.height=30+'px';
  document.getElementById('dialogdiv2').style.width =300+'px';
  document.getElementById('alertMsgOk').focus();
}
function confirmAlertMessage()
{ document.getElementById('veil').style.height=0+'px';
  document.getElementById('veil').style.width =0+'px';
  document.getElementById('dialogback').style.top=0+'px';
  document.getElementById('dialogback').style.left =0+'px';
  document.getElementById('dialogback').style.height=0+'px';
  document.getElementById('dialogback').style.width =0+'px';
  document.getElementById('dialog').style.top=0+'px';
  document.getElementById('dialog').style.left =0+'px';
  document.getElementById('dialog').style.height=0+'px';
  document.getElementById('dialog').style.width =0+'px';
  document.getElementById('dialog').style.overflow='hidden';
  document.getElementById('dialogdiv1').style.height=0+'px';
  document.getElementById('dialogdiv1').style.width =0+'px';
  document.getElementById('dialogdiv1').style.overflow='hidden';
  document.getElementById('dialogdiv2').style.top=0+'px';
  document.getElementById('dialogdiv2').style.height=0+'px';
  document.getElementById('dialogdiv2').style.width =0+'px';	
  document.getElementById('dialogdiv2').style.overflow='hidden';
}
function externalLinks() 
{ if (!document.getElementsByTagName) return;
  var anchors = document.getElementsByTagName("a");
  for (var i=0; i<anchors.length; i++) 
  { var anchor = anchors[i];
    if (anchor.getAttribute("href") && (anchor.getAttribute("rel")=="external"))
    { anchor.target = "new_window";
    }
  }
}
function restoreColumn(columnSource,theColumn)
{ i=0;
  while(restoreColumns[i][0]!=theColumn)
    i++;
  document.getElementById(theColumn).innerHTML=restoreColumns[i][1];
  j=0;
  while(j<restoreColumns[i][2].length)
  { document.getElementById(theColumn+'D'+j).innerHTML=restoreColumns[i][2][j];
    j++;
  }
  var cookie = new Array();
  if(getCookie('restoreColumns'))
    cookie=eval(getCookie('restoreColumns'));
  cookiestring='';
  for(i=0;i<cookie.length;i++)
    if((cookie[i][0]!=columnSource)||(cookie[i][1]!=theColumn))
      cookiestring=cookiestring+"['"+cookie[i][0]+"','"+cookie[i][1]+"','"+cookie[i][2]+"'],";  
  var exdate= new Date();
  exdate.setDate(exdate.getDate()+365);
  document.cookie="restoreColumns=["+cookiestring.substring(0, (cookiestring.length-1))+"];expires="+exdate.toUTCString();
  return false;
}
function getCookie(theCookie)
{ if(document.cookie.length>0)
  { start=document.cookie.indexOf(theCookie+'=');
    if(start!=-1)
    { start=start+theCookie.length+1;
      end=document.cookie.indexOf(';',start);
      if(end==-1) 
        end=document.cookie.length;
      return unescape(document.cookie.substring(start,end));
    }
  }
}
function removeColumn(columnSource, theColumn, header0)
{ var restoreThisColumn= new Array();
  restoreThisColumn[0]=theColumn;
  restoreThisColumn[1]=document.getElementById(theColumn).innerHTML;
  document.getElementById(theColumn).innerHTML="<a href=\"#\" onclick=\"restoreColumn('"+columnSource+"','"+theColumn+"');return false;\" title=\""+header0+"\">.</a>";
  i=0; var restoreColumnData= new Array();
  while(document.getElementById(theColumn+'D'+i))
  { restoreColumnData[i]=document.getElementById(theColumn+'D'+i).innerHTML;
    //if(!(document.getElementById(theColumn+'D'+i).title))
    //  document.getElementById(theColumn+'D'+i).title=document.getElementById(theColumn+'D'+i).innerHTML;
	document.getElementById(theColumn+'D'+i).innerHTML="";
    i++;
  }
  restoreThisColumn[2]=restoreColumnData;
  restoreColumns[restoreColumns.length]=restoreThisColumn;
  var cookie = new Array();
  if(getCookie('restoreColumns'))
    cookie=eval(getCookie('restoreColumns'));
  cookiestring='';
  for(i=0;i<cookie.length;i++)
    cookiestring=cookiestring+"['"+cookie[i][0]+"','"+cookie[i][1]+"','"+cookie[i][2]+"'],";  
  cookiestring=cookiestring+"['"+columnSource+"','"+theColumn+"','"+header0+"']";
  var exdate= new Date();
  exdate.setDate(exdate.getDate()+365);
  document.cookie="restoreColumns=["+cookiestring+"];expires="+exdate.toUTCString();
}
function checkColumns(columnSource)
{ var restoreThisColumn;
  var cookie = new Array();
  if(getCookie('restoreColumns'))
    cookie=eval(getCookie('restoreColumns'));
  for(i=0;i<cookie.length;i++)
    if(cookie[i][0]==columnSource)
    { restoreThisColumn= new Array();
      restoreThisColumn[0]=cookie[i][1];
      restoreThisColumn[1]=document.getElementById(cookie[i][1]).innerHTML;
      document.getElementById(cookie[i][1]).innerHTML="<a href=\"#\" onclick=\"restoreColumn('"+cookie[i][0]+"','"+cookie[i][1]+"');return false;\" title=\""+cookie[i][2]+"\">.</a>";
      j=0; var restoreColumnData= new Array();
	  while(document.getElementById(cookie[i][1]+'D'+j))
	  { restoreColumnData[j]=document.getElementById(cookie[i][1]+'D'+j).innerHTML;
		document.getElementById(cookie[i][1]+'D'+j).innerHTML="";
	    j++;
	  }
	  restoreThisColumn[2]=restoreColumnData;
	  restoreColumns[restoreColumns.length]=restoreThisColumn;
    }
  return false;
}
function moveColumnLeft(columnSource,id)
{ var orderColumns=eval(getCookie('orderColumns'));
  orderColumnSourceOrder= new Array();
  if(orderColumns)
  { for(i=0;i<orderColumns.length;i++)
	  if(orderColumns[i][0]==columnSource)
	  	orderColumnSourceOrder[i]=orderColumns[i][1];  
  }
  cookiestring='';
  previouscookiestring='';
  for(i=0;i<orderColumns.length;i++)
    if(orderColumns[i][0]!=columnSource)
      cookiestring=cookiestring+"['"+orderColumns[i][0]+"','"+orderColumns[i][1]+"'],";  
  for(i=0;i<orderColumns.length;i++)
	if(orderColumns[i][0]==columnSource)
	{ if(orderColumns[i][1]==id)
	  { cookiestring=cookiestring+"['"+orderColumns[i][0]+"','"+orderColumns[i][1]+"'],";
	  }
	  else
	  { cookiestring=cookiestring+previouscookiestring;
		previouscookiestring="['"+orderColumns[i][0]+"','"+orderColumns[i][1]+"'],";
	  }
	}
  cookiestring=cookiestring+previouscookiestring;
  var exdate= new Date();
  exdate.setDate(exdate.getDate()+365);
  document.cookie="orderColumns=["+cookiestring.substr(0,cookiestring.length-1)+"];expires="+exdate.toUTCString();	  
}
function moveColumnRight(columnSource,id)
{ var orderColumns=eval(getCookie('orderColumns'));
  orderColumnSourceOrder= new Array();
  if(orderColumns)
  { for(i=0;i<orderColumns.length;i++)
	  if(orderColumns[i][0]==columnSource)
	  	orderColumnSourceOrder[i]=orderColumns[i][1];  
  }
  cookiestring='';
  previouscookiestring='';
  for(i=0;i<orderColumns.length;i++)
    if(orderColumns[i][0]!=columnSource)
      cookiestring=cookiestring+"['"+orderColumns[i][0]+"','"+orderColumns[i][1]+"'],";  
  for(i=0;i<orderColumns.length;i++)
	if(orderColumns[i][0]==columnSource)
	{ if(orderColumns[i][1]!=id)
	  { cookiestring=cookiestring+"['"+orderColumns[i][0]+"','"+orderColumns[i][1]+"'],";
	  }
	  else
	  { if(i<orderColumns.length)
		{ cookiestring=cookiestring+"['"+orderColumns[i+1][0]+"','"+orderColumns[i+1][1]+"'],";
		}
        cookiestring=cookiestring+"['"+orderColumns[i][0]+"','"+orderColumns[i][1]+"'],";
	    i++;
	  }
	}
  cookiestring=cookiestring+previouscookiestring;
  var exdate= new Date();
  exdate.setDate(exdate.getDate()+365);
  document.cookie="orderColumns=["+cookiestring.substr(0,cookiestring.length-1)+"];expires="+exdate.toUTCString();	  
}
function writeColumnsOrder(columnSource)
{ var orderColumns=eval(getCookie('orderColumns'));
  orderColumnSourceOrder= new Array();
  if(orderColumns)
  { for(i=0;i<orderColumns.length;i++)
      if(orderColumns[i][0]==columnSource)
    	orderColumnSourceOrder[i]=orderColumns[i][1];  
  }
  if(orderColumnSourceOrder.length>0)
  { var titleData = new Array();
	var columnData = new Array();
    i=1;
    while(document.getElementById('C'+i))
    { titleData[i]=document.getElementById('C'+i).innerHTML;
      j=0; var tempColumnData= new Array();
      while(document.getElementById('C'+i+'D'+j))
      { tempColumnData[tempColumnData.length]=document.getElementById('C'+i+'D'+j).innerHTML;
        j++;
      }
      columnData[i]=tempColumnData;
      i++;
    }  
    i=1;
    while(orderColumnSourceOrder[i])
    { document.getElementById('C'+i).innerHTML=titleData[orderColumnSourceOrder[i].substr(1)];
      j=0;
      while(columnData[i][j])
      { document.getElementById('C'+i+'D'+j).innerHTML=columnData[orderColumnSourceOrder[i].substr(1)][j];
        j++;
      }
      i++;
    }  
  }
  else
  { cookiestring='';
    if(orderColumns)
    { for(i=0;i<orderColumns.length;i++)
        if(orderColumns[i][0]!=columnSource)
          cookiestring=cookiestring+"['"+orderColumns[i][0]+"','"+orderColumns[i][1]+"'],";  
    }
    i=0;
    while((i<1) || document.getElementById('C'+i))
    { cookiestring=cookiestring+"['"+columnSource+"','C"+i+"'],";	  
      i++;
    }  
    var exdate= new Date();
    exdate.setDate(exdate.getDate()+365);
    document.cookie="orderColumns=["+cookiestring.substr(0,cookiestring.length-1)+"];expires="+exdate.toUTCString();	  
  }
}