function resizeElement(theElement,delta) 
{ var height=document.documentElement.clientHeight;
	height-=document.getElementById(theElement).offsetTop;
	height-=delta;
	document.getElementById(theElement).style.height=height+'px';
 	}