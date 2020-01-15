// JavaScript by Peter Hayes http://www.aphayes.pwp.blueyonder.co.uk/
// Copyright 2001-2010
// Unless otherwise stated this code is based on the methods in
// Astronomical Algorithms, first edition, by Jean Meeus
// Published by Willmann-Bell, Inc.
// This code is made freely available but please keep this notice.
// The calculations are approximate but should be good enough for general use,
// I accept no responsibility for errors in astronomy or coding.

// WARNING moonrise code changed on 6 May 2003 to correct a systematic error
// these are now local times NOT UTC as the original code did.

// Meeus first edition table 45.A Longitude and distance of the moon

   var T45AD = new Array(0, 2, 2, 0, 0, 0, 2, 2, 2, 2,
                         0, 1, 0, 2, 0, 0, 4, 0, 4, 2,
                         2, 1, 1, 2, 2, 4, 2, 0, 2, 2,
                         1, 2, 0, 0, 2, 2, 2, 4, 0, 3,
                         2, 4, 0, 2, 2, 2, 4, 0, 4, 1,
                         2, 0, 1, 3, 4, 2, 0, 1, 2, 2);

   var T45AM = new Array(0,  0,  0,  0,  1,  0,  0, -1,  0, -1,
                         1,  0,  1,  0,  0,  0,  0,  0,  0,  1,
                         1,  0,  1, -1,  0,  0,  0,  1,  0, -1,
                         0, -2,  1,  2, -2,  0,  0, -1,  0,  0,
                         1, -1,  2,  2,  1, -1,  0,  0, -1,  0,
                         1,  0,  1,  0,  0, -1,  2,  1,  0,  0);

   var T45AMP = new Array( 1, -1,  0,  2,  0,  0, -2, -1,  1,  0,
                          -1,  0,  1,  0,  1,  1, -1,  3, -2, -1,
                           0, -1,  0,  1,  2,  0, -3, -2, -1, -2,
                           1,  0,  2,  0, -1,  1,  0, -1,  2, -1,
                           1, -2, -1, -1, -2,  0,  1,  4,  0, -2,
                           0,  2,  1, -2, -3,  2,  1, -1,  3, -1);

   var T45AF  = new Array( 0,  0,  0,  0,  0,  2,  0,  0,  0,  0,
                           0,  0,  0, -2,  2, -2,  0,  0,  0,  0,
                           0,  0,  0,  0,  0,  0,  0,  0,  2,  0,
                           0,  0,  0,  0,  0, -2,  2,  0,  2,  0,
                           0,  0,  0,  0,  0, -2,  0,  0,  0,  0,
                          -2, -2,  0,  0,  0,  0,  0,  0,  0, -2);

   var T45AL = new Array(6288774, 1274027, 658314, 213618, -185116,
                         -114332,   58793,  57066,  53322,   45758,
                          -40923,  -34720, -30383,  15327,  -12528,
                           10980,   10675,  10034,   8548,   -7888,
                           -6766,   -5163,   4987,   4036,    3994,
                            3861,    3665,  -2689,  -2602,    2390,
                           -2348,    2236,  -2120,  -2069,    2048,
                           -1773,   -1595,   1215,  -1110,    -892,
                            -810,     759,   -713,   -700,     691,
                             596,     549,    537,    520,    -487,
                            -399,    -381,    351,   -340,     330,
                             327,    -323,    299,    294,       0);

   var T45AR = new Array(-20905355, -3699111, -2955968, -569925,   48888,
                             -3149,   246158,  -152138, -170733, -204586,
                           -129620,   108743,   104755,   10321,       0,
                             79661,   -34782,   -23210,  -21636,   24208,
                             30824,    -8379,   -16675,  -12831,  -10445,
                            -11650,    14403,    -7003,       0,   10056,
                              6322,    -9884,     5751,       0,   -4950,
                              4130,        0,    -3958,       0,    3258,
                              2616,    -1897,    -2117,    2354,       0,
                                 0,    -1423,    -1117,   -1571,   -1739,
                                 0,    -4421,        0,       0,       0,
                                 0,     1165,        0,       0,    8752);

// Meeus table 45B latitude of the moon

   var T45BD = new Array(0, 0, 0, 2, 2, 2, 2, 0, 2, 0,
                         2, 2, 2, 2, 2, 2, 2, 0, 4, 0,
                         0, 0, 1, 0, 0, 0, 1, 0, 4, 4,
                         0, 4, 2, 2, 2, 2, 0, 2, 2, 2,
                         2, 4, 2, 2, 0, 2, 1, 1, 0, 2,
                         1, 2, 0, 4, 4, 1, 4, 1, 4, 2);

   var T45BM = new Array( 0,  0,  0,  0,  0,  0,  0, 0,  0,  0,
                         -1,  0,  0,  1, -1, -1, -1, 1,  0,  1,
                          0,  1,  0,  1,  1,  1,  0, 0,  0,  0,
                          0,  0,  0,  0, -1,  0,  0, 0,  0,  1,
                          1,  0, -1, -2,  0,  1,  1, 1,  1,  1,
                          0, -1,  1,  0, -1,  0,  0, 0, -1, -2);

   var T45BMP = new Array(0,  1, 1,  0, -1, -1,  0,  2,  1,  2,
                          0, -2, 1,  0, -1,  0, -1, -1, -1,  0,
                          0, -1, 0,  1,  1,  0,  0,  3,  0, -1,
                          1, -2, 0,  2,  1, -2,  3,  2, -3, -1,
                          0,  0, 1,  0,  1,  1,  0,  0, -2, -1,
                          1, -2, 2, -2, -1,  1,  1, -1,  0,  0);

   var T45BF = new Array( 1,  1, -1, -1,  1, -1,  1,  1, -1, -1,
                         -1, -1,  1, -1,  1,  1, -1, -1, -1,  1,
                          3,  1,  1,  1, -1, -1, -1,  1, -1,  1,
                         -3,  1, -3, -1, -1,  1, -1,  1, -1,  1,
                          1,  1,  1, -1,  3, -1, -1,  1, -1, -1,
                          1, -1,  1, -1, -1, -1, -1, -1, -1,  1);

   var T45BL = new Array(5128122, 280602, 277693, 173237, 55413,
                           46271,  32573,  17198,   9266,  8822,
                            8216,   4324,   4200,  -3359,  2463,
                            2211,   2065,  -1870,   1828, -1794,
                           -1749,  -1565,  -1491,  -1475, -1410,
                           -1344,  -1335,   1107,   1021,   833,
                             777,    671,    607,    596,   491,
                            -451,    439,    422,    421,  -366,
                            -351,    331,    315,    302,  -283,
                            -229,    223,    223,   -220,  -220,
                            -185,    181,   -177,    176,   166,
                            -164,    132,   -119,    115,   107);

// MoonPos calculates the Moon position, based on Meeus chapter 45

function MoonPos(year,month,day,hours) {
  // julian date
  var jd=julian(year,month,day,hours);
  var T=(jd-2451545.0)/36525;
  var T2=T*T;
  var T3=T2*T;
  var T4=T3*T;
  // Moons mean longitude L'
  var LP=218.3164477+481267.88123421*T-0.0015786*T2+T3/538841.0-T4/65194000.0;
  // Moons mean elongation
  var D=297.8501921+445267.1114034*T-0.0018819*T2+T3/545868.0-T4/113065000.0;
  // Suns mean anomaly
  var M=357.5291092+35999.0502909*T-0.0001536*T2+T3/24490000.0;
  // Moons mean anomaly M'
  var MP=134.9633964+477198.8675055*T+0.0087414*T2+T3/69699.0-T4/14712000.0;
  // Moons argument of latitude
  var F=93.2720950+483202.0175233*T-0.0036539*T2-T3/3526000.0+T4/863310000.0;

  // Additional arguments
  var A1=119.75+131.849*T;
  var A2=53.09+479264.290*T;
  var A3=313.45+481266.484*T;
  var E=1-0.002516*T-0.0000074*T2;
  var E2=E*E;
  // Sums of periodic terms from table 45.A and 45.B
  var Sl=0.0;
  var Sr=0.0;
  for (var i=0; i<60; i++) {
    var Eterm=1;
    if (Math.abs(T45AM[i])==1) Eterm=E;
    if (Math.abs(T45AM[i])==2) Eterm=E2;
    Sl+=T45AL[i]*Eterm*sind(rev(T45AD[i]*D+T45AM[i]*M+T45AMP[i]*MP+T45AF[i]*F));
    Sr+=T45AR[i]*Eterm*cosd(rev(T45AD[i]*D+T45AM[i]*M+T45AMP[i]*MP+T45AF[i]*F));
  }
  var Sb=0.0;
  for (var i=0; i<60; i++) {
    var Eterm=1;
    if (Math.abs(T45BM[i])==1) Eterm=E;
    if (Math.abs(T45BM[i])==2) Eterm=E2;
    Sb+=T45BL[i]*Eterm*sind(rev(T45BD[i]*D+T45BM[i]*M+T45BMP[i]*MP+T45BF[i]*F));
  }
  // Additional additive terms
  Sl=Sl+3958*sind(rev(A1))+1962*sind(rev(LP-F))+318*sind(rev(A2));
  Sb=Sb-2235*sind(rev(LP))+382*sind(rev(A3))+175*sind(rev(A1-F))+
     175*sind(rev(A1+F))+127*sind(rev(LP-MP))-115*sind(rev(LP+MP));
  // geocentric longitude, latitude and distance
  var mglong=rev(LP+Sl/1000000.0);
  var mglat=rev(Sb/1000000.0);
  if (mglat > 180.0) mglat=mglat-360;
  var mr=Math.round(385000.56+Sr/1000.0);
  // Obliquity of Ecliptic
  var obl=23.4393-3.563E-9*(jd-2451543.5);
  // RA and dec
  var ra=rev(atan2d(sind(mglong)*cosd(obl)-tand(mglat)*sind(obl),
                    cosd(mglong)))/15.0;
  var dec=rev(asind(sind(mglat)*cosd(obl)+cosd(mglat)*sind(obl)*sind(mglong)));
  if (dec > 180.0) dec=dec-360;
  return new Array(ra,dec,mr);
}

function MoonRise(year,month,day,TZ,latitude,longitude) {
  // returns an array containing rise and set times or one of the
  // following codes.
  // -1 rise or set event not found and moon was down at 00:00
  // -2 rise or set event not found and moon was up   at 00:00
  // WARNING code changes on 6/7 May 2003 these are now local times
  // NOT UTC and rise/set not found codes changed.
  var hours=0;
  var riseset=new Array();
  // elh is the elevation at the hour elhdone is true if elh calculated
  var elh=new Array();
  var elhdone=new Array();
  for (var i=0; i<=24; i++) {elhdone[i]=false;}
  // Compute the moon elevation at start and end of day
  // store elevation at the hours in an array elh to save search time
  var rad=MoonPos(year,month,day,hours-TZ);
  var altaz=radtoaa(rad[0],rad[1],year,month,day,hours-TZ,latitude,longitude);
  elh[0]=altaz[0]; elhdone[0]=true;
  // set the return code to allow for always up or never rises
  if (elh[0] > 0.0) {
    riseset=new Array(-2,-2);
  } else {
    riseset=new Array(-1,-1);
  }
  hours=24;
  rad=MoonPos(year,month,day,hours-TZ);
  altaz=radtoaa(rad[0],rad[1],year,month,day,hours-TZ,latitude,longitude);
  elh[24]=altaz[0]; elhdone[24]=true;
  // search for moonrise and set
  for (var rise=0; rise<2; rise++) {
     var found=false;
     var hfirst=0;
     var hlast=24;
     // Try a binary chop on the hours to speed the search
     while (Math.ceil((hlast-hfirst)/2) > 1) {
       hmid=hfirst+Math.round((hlast-hfirst)/2);
       if (!elhdone[hmid]) {
         hours=hmid;
         rad=MoonPos(year,month,day,hours-TZ);
         altaz=radtoaa(rad[0],rad[1],year,month,day,hours-TZ,latitude,longitude);
         elh[hmid]=altaz[0]; elhdone[hmid]=true;
       }
       if (((rise == 0) && (elh[hfirst] <= 0.0) && (elh[hmid] >= 0.0)) ||
           ((rise == 1) && (elh[hfirst] >= 0.0) && (elh[hmid] <= 0.0))) {
         hlast=hmid; found=true; continue;
       }
       if (((rise == 0) && (elh[hmid] <= 0.0) && (elh[hlast] >= 0.0)) ||
           ((rise == 1) && (elh[hmid] >= 0.0) && (elh[hlast] <= 0.0))) {
         hfirst=hmid; found=true; continue;
       }
       break;
     }
     // If the binary chop did not find a 1 hour interval
     if ((hlast-hfirst) > 1) {
       for (var i=hfirst; i<hlast; i++) {
         found=false;
         if (!elhdone[i+1]) {
           hours=i+1;
           rad=MoonPos(year,month,day,hours-TZ);
           altaz=radtoaa(rad[0],rad[1],year,month,day,hours-TZ,latitude,longitude);
           elh[hours]=altaz[0]; elhdone[hours]=true;
         }
         if (((rise == 0) && (elh[i] <= 0.0) && (elh[i+1] >= 0.0)) ||
             ((rise == 1) && (elh[i] >= 0.0) && (elh[i+1] <= 0.0))) {
           hfirst=i; hlast=i+1; found=true; break;
         }
       }
     }
     // simple linear interpolation for the minutes
     if (found) {
       var elfirst=elh[hfirst]; var ellast=elh[hlast];
       hours=hfirst+0.5;
       rad=MoonPos(year,month,day,hours-TZ);
       altaz=radtoaa(rad[0],rad[1],year,month,day,hours-TZ,latitude,longitude);
       // alert("day ="+day+" hour ="+hours+" altaz="+altaz[0]+" "+altaz[1]);
       if ((rise == 0) && (altaz[0] <= 0.0)) {hfirst=hours; elfirst=altaz[0];}
       if ((rise == 0) && (altaz[0] > 0.0)) {hlast=hours; ellast=altaz[0];}
       if ((rise == 1) && (altaz[0] <= 0.0)) {hlast=hours; ellast=altaz[0];}
       if ((rise == 1) && (altaz[0] > 0.0)) {hfirst=hours; elfirst=altaz[0];}
       var eld=Math.abs(elfirst)+Math.abs(ellast);
       riseset[rise]=hfirst+(hlast-hfirst)*Math.abs(elfirst)/eld;
     }
  } // End of rise/set loop
  return(riseset);
}

function MoonPhase(year,month,day,hours) {
  // the illuminated percentage from Meeus chapter 46
  var j=dayno(year,month,day,hours)+2451543.5;
  var T=(j-2451545.0)/36525;
  var T2=T*T;
  var T3=T2*T;
  var T4=T3*T;
  // Moons mean elongation Meeus first edition
  // var D=297.8502042+445267.1115168*T-0.0016300*T2+T3/545868.0-T4/113065000.0;
  // Moons mean elongation Meeus second edition
  var D=297.8501921+445267.1114034*T-0.0018819*T2+T3/545868.0-T4/113065000.0;
  // Moons mean anomaly M' Meeus first edition
  // var MP=134.9634114+477198.8676313*T+0.0089970*T2+T3/69699.0-T4/14712000.0;
  // Moons mean anomaly M' Meeus second edition
  var MP=134.9633964+477198.8675055*T+0.0087414*T2+T3/69699.0-T4/14712000.0;
  // Suns mean anomaly
  var M=357.5291092+35999.0502909*T-0.0001536*T2+T3/24490000.0;
  // phase angle
  var pa=180.0-D-6.289*sind(MP)+2.1*sind(M)-1.274*sind(2*D-MP)
         -0.658*sind(2*D)-0.214*sind(2*MP)-0.11*sind(D);
  return(rev(pa));
}

function MoonQuarters(year,month,day) {
  // returns an array of Julian Ephemeris Days (JDE) for
  // new moon, first quarter, full moon and last quarter
  // Meeus first edition chapter 47 with only the most larger additional corrections
  // Meeus code calculate Terrestrial Dynamic Time
  // TDT = UTC + (number of leap seconds) + 32.184
  // At the end of June 2012 the 25th leap second was added
  //
  var quarters = new Array();
  // k is an integer for new moon incremented by 0.25 for first quarter 0.5 for new etc.
  var k=Math.floor((year+((month-1)+day/30)/12-2000)*12.3685);
  // Time in Julian centuries since 2000.0
  var T=k/1236.85;
  // Sun's mean anomaly
  var M=rev(2.5534+29.10535669*k-0.0000218*T*T);
  // Moon's mean anomaly (M' in Meeus)
  var MP=rev(201.5643+385.81693528*k+0.0107438*T*T+0.00001239*T*T*T-0.00000011*T*T*T);
  var E=1-0.002516*T-0.0000074*T*T;
  // Moons argument of latitude
  var F=rev(160.7108+390.67050274*k-0.0016341*T*T-0.00000227*T*T*T+0.000000011*T*T*T*T);
  // Longitude of ascending node of lunar orbit
  var Omega=rev(124.7746-1.56375580*k+0.0020691*T*T+0.00000215*T*T*T);
  // The full planetary arguments include 14 terms, only used the 7 most significant
  var A = new Array();
  A[1]=rev(299.77+ 0.107408*k-0.009173*T*T);
  A[2]=rev(251.88+ 0.016321*k);
  A[3]=rev(251.83+26.651886*k);
  A[4]=rev(349.42+36.412478*k);
  A[5]=rev( 84.88+18.206239*k);
  A[6]=rev(141.74+53.303771*k);
  A[7]=rev(207.14+ 2.453732*k);

  // New moon
  var JDE0=2451550.09765+29.530588853*k+0.0001337*T*T-0.000000150*T*T*T+0.00000000073*T*T*T*T;
  // Correct for TDT since 1 July 2012
  JDE0=JDE0-57.184/(24*60*60);
  var JDE=JDE0 -0.40720*sind(MP)      +0.17241*E*sind(M)      +0.01608*sind(2*MP)    +0.01039*sind(2*F)
               +0.00739*E*sind(MP-M)  -0.00514*E*sind(MP+M)   +0.00208*E*E*sind(2*M) -0.00111*sind(MP-2*F)
               -0.00057*sind(MP+2*F)  +0.00056*E*sind(2*MP+M) -0.00042*sind(3*MP)    +0.00042*E*sind(M+2*F)
               +0.00038*E*sind(M-2*F) -0.00024*E*sind(2*MP-M) -0.00017*sind(Omega)   -0.00007*sind(MP+2*M);

  quarters[0]=JDE+0.000325*sind(A[1])+0.000165*sind(A[2])+0.000164*sind(A[3])+0.000126*sind(A[4])
                 +0.000110*sind(A[5])+0.000062*sind(A[6])+0.000060*sind(A[7]);

   // The following code needs tidying up with a loop and conditionals for each quarter
  for (var k=1; k<=3; k++) {
    JDE=JDE0+29.530588853 *0.25 * k;

    M=rev(M+29.10535669*0.25);
    MP=rev(MP+385.81693528*0.25);
    F=rev(F+390.67050274*0.25);
    Omega=rev(Omega-1.56375580*0.25);
    A[1]=rev(A[1]+ 0.107408*0.25);
    A[2]=rev(A[2]+ 0.016321*0.25);
    A[3]=rev(A[3]+26.651886*0.25);
    A[4]=rev(A[4]+36.412478*0.25);
    A[5]=rev(A[5]+18.206239*0.25);
    A[6]=rev(A[6]+53.303771*0.25);
    A[7]=rev(A[7]+ 2.453732*0.25);

    if (k === 2) {
        JDE=JDE-0.40614*sind(MP)      +0.17302*E*sind(M)      +0.01614*sind(2*MP)    +0.01043*sind(2*F)
               +0.00734*E*sind(MP-M)  -0.00515*E*sind(MP+M)   +0.00209*E*E*sind(2*M) -0.00111*sind(MP-2*F)
               -0.00057*sind(MP+2*F)  +0.00056*E*sind(2*MP+M) -0.00042*sind(3*MP)    +0.00042*E*sind(M+2*F)
               +0.00038*E*sind(M-2*F) -0.00024*E*sind(2*MP-M) -0.00017*sind(Omega)   -0.00007*sind(MP+2*M);
    } else {
        JDE=JDE-0.62801*sind(MP)      +0.17172*E*sind(M)        -0.01183*E*sind(MP+M)    +0.00862*sind(2*MP)
               +0.00804*sind(2*F)     +0.00454*E*sind(MP-M)     +0.00204*E*E*sind(2*M)  -0.00180*sind(MP-2*F)
               -0.00070*sind(MP+2*F)  -0.00040*sind(3*MP)       -0.00034*E*sind(2*MP-M) +0.00032*E*sind(M+2*F)
               +0.00032*E*sind(M-2*F) -0.00028*E*E*sind(MP+2*M) +0.00027*E*sind(2*MP+M) -0.00017*sind(Omega);

        if (k === 1) {
            JDE=JDE + (0.00306 - 0.00038*E*cosd(M) + 0.00026*cosd(MP) - 0.00002*cosd(MP-M) + 0.00002*cosd(MP+M) + 0.00002*cosd(2*F));
        } else {
            JDE=JDE - (0.00306 - 0.00038*E*cosd(M) + 0.00026*cosd(MP) - 0.00002*cosd(MP-M) + 0.00002*cosd(MP+M) + 0.00002*cosd(2*F));

        }
    }

    quarters[k]=JDE+0.000325*sind(A[1])+0.000165*sind(A[2])+0.000164*sind(A[3])+0.000126*sind(A[4])
                 +0.000110*sind(A[5])+0.000062*sind(A[6])+0.000060*sind(A[7]);

  }

  return quarters;
}
