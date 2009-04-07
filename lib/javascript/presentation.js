function findVerticalPosition(theElement)
{ var obj=document.getElementById(theElement);
  var top=obj.offsetTop;
   while(obj=obj.offsetParent)
   { top+=obj.offsetTop;
   } 
  return top;
 }
function resizeForm()
{ document.getElementById('div5').style.width= (document.getElementById('dslbody').clientWidth-185)+'px';
  document.getElementById('div5a').style.width=(document.getElementById('dslbody').clientWidth-185)+'px';
  document.getElementById('div5b').style.width=(document.getElementById('dslbody').clientWidth-185)+'px';
  document.getElementById('div6').style.left=  (document.getElementById('dslbody').clientWidth-16)+'px';
  document.getElementById('div7').style.width= (document.getElementById('dslbody').clientWidth-169)+'px';
  document.getElementById('div4').style.height=(document.getElementById('dslbody').clientHeight-111)+'px';
  document.getElementById('div5').style.height=(document.getElementById('dslbody').clientHeight-121)+'px';
  document.getElementById('div5b').style.top=  (document.getElementById('dslbody').clientHeight-41)+'px';
  document.getElementById('div6').style.height=(document.getElementById('dslbody').clientHeight-111)+'px';
  document.getElementById('div7').style.top=   (document.getElementById('dslbody').clientHeight-26)+'px';
  var height=document.getElementById('dslbody').clientHeight;
  height-=findVerticalPosition(theResizeElement);
	height-=theResizeSize;
	if(height<100) 
	  height=100;
	document.getElementById(theResizeElement).style.height=height+'px';
}