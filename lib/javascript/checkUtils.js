// checkInteger(event)                      -- check if the pressed key is 0..9, first character can be minus sign
// checkPositiveInteger(event)              -- check if the pressed key is 0..9
function checkInteger(event)
{ if(window.event)     
    keynum = event.keyCode;
  else if(event.which)
    keynum = event.which;
  if((keynum!=8)
	&& ((keynum<48)||(keynum>57))
	&& ((keynum!=45)||(theValue.length>0))) 
	 return false;
  return true;
}
function checkPositiveInteger(event)
{ if(window.event)     
    keynum = event.keyCode;
  else if(event.which) 
    keynum = event.which;
  if((keynum!=8)&&((keynum<48)||(keynum>57))) 
	 return false;
  return true;
}
