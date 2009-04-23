var theResizeElement, theResizeSize;
function findVerticalPosition(theElement)
{ var obj=document.getElementById(theElement);
  var top=obj.offsetTop;
   while(obj=obj.offsetParent)
   { top+=obj.offsetTop;
   } 
  return top;
 }
function resizeForm()
{ var height=document.getElementById('dslbody').clientHeight;
  document.getElementById('div4').style.height=(height-111)+'px';
  document.getElementById('div4').style.width =(document.getElementById('dslbody').clientWidth-163)+'px';
  document.getElementById('div4b').style.top = (height-122)+'px';
  document.getElementById('div4c').style.left =(document.getElementById('dslbody').clientWidth-174)+'px';
  document.getElementById('div4d').style.top = (height-122)+'px';
  document.getElementById('div4d').style.left =(document.getElementById('dslbody').clientWidth-174)+'px';
  document.getElementById('div5').style.height=(height-121)+'px';
  document.getElementById('div5').style.width= (document.getElementById('dslbody').clientWidth-185)+'px';
  document.getElementById('div6').style.width= (document.getElementById('dslbody').clientWidth-169)+'px';
  document.getElementById('div6').style.top=   (height-26)+'px';
  height-=findVerticalPosition(theResizeElement);
	height-=theResizeSize;
	if(height<100) 
	  height=100;
	document.getElementById(theResizeElement).style.height=height+'px';
}
theResizeElement='';
theResizeSize=0;