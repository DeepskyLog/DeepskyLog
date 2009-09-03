baseURL= "www.dsltrunk.be/";

var onKeyDownFns = new Array();

function bodyOnKeyDown(event)
{ for(var i=0;i<onKeyDownFns.length;i++)
  	onKeyDownFns[i](event);
}