function findVerticalPosition(theElement)
{ var obj=document.getElementById(theElement);
  var top=obj.offsetTop;
  while(obj=obj.offsetParent)
  { top+=obj.offsetTop;
  } 
  return top;
}
function resizeElement(theElement,delta) 
{ var height=document.documentElement.clientHeight;
//	alert('screenheight: '+height);
//	alert('Offset:'+document.getElementById(theElement).style.height);
	height-=findVerticalPosition(theElement);
//	alert(height);
	height-=delta;
	if(height<100) 
	{ alert(height);
	  height=100;
	}  
	document.getElementById(theElement).style.height=height+'px';
}