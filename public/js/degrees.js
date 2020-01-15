// Extensions to the Math routines - Trig routines in degrees
// JavaScript by Peter Hayes http://www.peter-hayes.freeserve.co.uk/
// Copyright 2001-2002

function rev(angle){return angle-Math.floor(angle/360.0)*360.0;}
function sind(angle){return Math.sin((angle*Math.PI)/180.0);}
function cosd(angle){return Math.cos((angle*Math.PI)/180.0);}
function tand(angle){return Math.tan((angle*Math.PI)/180.0);}
function asind(c){return (180.0/Math.PI)*Math.asin(c);}
function acosd(c){return (180.0/Math.PI)*Math.acos(c);}
function atan2d(y,x){return (180.0/Math.PI)*Math.atan(y/x)-180.0*(x<0);}

function anglestring(a,circle) {
// returns a in degrees as a string degrees:minutes
// circle is true for range between 0 and 360 and false for -90 to +90
  var ar=Math.round(a*60)/60;
  var deg=Math.abs(ar);
  var min=Math.round(60.0*(deg-Math.floor(deg)));
  if (min >= 60) { deg+=1; min=0; }
  var anglestr="";
  if (!circle) anglestr+=(ar < 0 ? "-" : "+");
  if (circle) anglestr+=((Math.floor(deg) < 100) ? "0" : "" );
  anglestr+=((Math.floor(deg) < 10) ? "0" : "" )+Math.floor(deg);
  anglestr+=((min < 10) ? ":0" : ":" )+(min);
  return anglestr;
}

