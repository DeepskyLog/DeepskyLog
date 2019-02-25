// Utility functions for astronomical programming.
// JavaScript by Peter Hayes http://www.peter-hayes.freeserve.co.uk/
// Copyright 2001-2002
// This code is made freely available but please keep this notice.
// I accept no liability for any errors in my coding but please
// let me know of any errors you find. My address is on my home page.

function dayno(year,month,day,hours) {
  // Day number is a modified Julian date, day 0 is 2000 January 0.0
  // which corresponds to a Julian date of 2451543.5
  var d= 367*year-Math.floor(7*(year+Math.floor((month+9)/12))/4)
         +Math.floor((275*month)/9)+day-730530+hours/24;
  return d;
}

function julian(year,month,day,hours) {
   return  dayno(year,month,day,hours)+2451543.5
}

function jdtocd(jd) {
  // The calendar date from julian date
  // Returns year, month, day, day of week, hours, minutes, seconds
  var Z=Math.floor(jd+0.5);
  var F=jd+0.5-Z;
  if (Z < 2299161){
    var A=Z;
  } else {
    var alpha=Math.floor((Z-1867216.25)/36524.25);
    var A=Z+1+alpha-Math.floor(alpha/4);
  }
  var B=A+1524;
  var C=Math.floor((B-122.1)/365.25);
  var D=Math.floor(365.25*C);
  var E=Math.floor((B-D)/30.6001);
  var d=B-D-Math.floor(30.6001*E)+F;
  if (E < 14) {var month=E-1;} else {var month=E-13;}
  if ( month>2) {var year=C-4716;} else {var year=C-4715;}
  var day=Math.floor(d);
  var h=(d-day)*24;
  var hours=Math.floor(h);
  var m=(h-hours)*60;
  var minutes=Math.floor(m);
  var seconds=Math.round((m-minutes)*60);
  if (seconds >= 60) {minutes=minutes+1;seconds=seconds-60;}
  if (minutes >= 60) {hours=hours+1;minutes=0;}
  var dw=Math.floor(jd+1.5)-7*Math.floor((jd+1.5)/7);
  return new Array(year,month,day,dw,hours,minutes,seconds);  
}

function local_sidereal(year,month,day,hours,lon) {
  // Compute local siderial time in degrees
  // year, month, day and hours are the Greenwich date and time
  // lon is the observers longitude
  var d=dayno(year,month,day,hours);
  var lst=(98.9818+0.985647352*d+hours*15+lon);
  return rev(lst)/15;
}

function radtoaa(ra,dec,year,month,day,hours,lat,lon) {
  // convert ra and dec to altitude and azimuth
  // year, month, day and hours are the Greenwich date and time
  // lat and lon are the observers latitude and longitude
  var lst=local_sidereal(year,month,day,hours,lon);
  var x=cosd(15.0*(lst-ra))*cosd(dec);
  var y=sind(15.0*(lst-ra))*cosd(dec);
  var z=sind(dec);
  // rotate so z is the local zenith
  var xhor=x*sind(lat)-z*cosd(lat);
  var yhor=y;
  var zhor=x*cosd(lat)+z*sind(lat);
  var azimuth=rev(atan2d(yhor,xhor)+180.0); // so 0 degrees is north
  var altitude=atan2d(zhor,Math.sqrt(xhor*xhor+yhor*yhor));
  return new Array(altitude,azimuth);
}

