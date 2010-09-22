var onKeyDownFns = new Array();

function bodyOnKeyDown(event)
{ var ret = true;
  for(var i=0;i<onKeyDownFns.length;i++)
  { ret = ret && onKeyDownFns[i](event);
  }
  return ret;
}