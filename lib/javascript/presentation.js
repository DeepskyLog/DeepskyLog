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
  document.getElementById('div4').style.height=(height-111)+'px';
  document.getElementById('div4').style.width =(width-163)+'px';
  document.getElementById('div4b').style.top = (height-122)+'px';
  document.getElementById('div4c').style.left =(width-174)+'px';
  document.getElementById('div4d').style.top = (height-122)+'px';
  document.getElementById('div4d').style.left =(width-174)+'px';
  document.getElementById('div5').style.height=(height-121)+'px';
  document.getElementById('div5').style.width= (width-185)+'px';
  document.getElementById('div6').style.width= (width-169)+'px';
  document.getElementById('div6').style.top=   (height-26)+'px';
  height-=findVerticalPosition(theResizeElement);
	height-=theResizeSize;
  if(height<100) 
	height=100;
  document.getElementById(theResizeElement).style.height=height+'px';
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
  document.getElementById('dialogback').style.top=((height/2)-92)+'px';
  document.getElementById('dialogback').style.left =((width/2)-142)+'px';
  document.getElementById('dialogback').style.height=200+'px';
  document.getElementById('dialogback').style.width =300+'px';
  document.getElementById('dialog').style.top=((height/2)-100)+'px';
  document.getElementById('dialog').style.left =((width/2)-150)+'px';
  document.getElementById('dialog').style.height=200+'px';
  document.getElementById('dialog').style.width =300+'px';
  document.getElementById('dialogdiv1').style.height=150+'px';
  document.getElementById('dialogdiv1').style.width =280+'px';
  document.getElementById('dialogdiv2').style.top=170+'px';
  document.getElementById('dialogdiv2').style.height=30+'px';
  document.getElementById('dialogdiv2').style.width =300+'px';
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