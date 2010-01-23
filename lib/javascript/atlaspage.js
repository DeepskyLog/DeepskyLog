

var atlasmagnitude=0,
    atlasmagnitudedelta=0,
    atlaspagedecldeg=0,    
    atlaspagerahr=0,  
    atlaspagezoomdeg=2,
    by=0,
    canvasDimensionXpx,  
    canvasDimensionYpx,                        // Canvas Dimension X and Dimension Y: width and height of the canvas;
    canvasOffsetXpx=0,
    canvasOffsetYpx=16,
    canvasX1px, 
    canvasX2px,
    canvasY1px,
    canvasY2px,
    conBoundries = new Array(),
    diam1SecToPxCt = 1,
    diam2SecToPxCt = 1,
    divOffsetXpx=0,
    divOffsetX2px=0,
    divOffsetYpx =0,
    divOffsetY2px=0,
    dsl_amn,
    dsl_asc,
    dsl_deg,                                                   // fn coordDeclDecToDegMin results
    dsl_hr,     
    dsl_min,         
    dsl_sec,                                       // fn coordHrDecToHrMin    results
    Dsteps=10,                                                     // Number of steps for drawing coordinate lines between major steps
    f12OverPi  = 3.8197186342054880584532103209403,
    f180OverPi = 57.295779513082320876798154814105,
    fPiOver2   = 1.5707963267948966192313216916398,
    fPiOver12  = 0.26179938779914943653855361527329,
    fPiOver180 = 0.017453292519943295769236907684886,
    f2Pi       = 6.283185307179586476925286766559,
    fontSize1a=10, 
    fontSize1b=6,
    gridActualDimension=16,
    gridCenterOffsetXpx, 
    gridCenterOffsetYpx,
    gridD0rad,
    griddDdeg,
    gridDyRad,
    gridDy1rad,      
    gridDy2rad,    
    gridL0rad,
    gridldDdeg,
    gridldLhr,
    gridlLhr,
    gridluDdeg,
    gridluLhr,
    gridLxRad,
    gridLx1rad, 
    gridLx2rad,     
    gridMaxDimension=23,
    gridMinDimension=0,
    gridOffsetXpx=80,                                               // Grid offset relative to the canvas
    gridOffsetYpx=36,                                               // Grid offset relative to the canvas
    gridrdDdeg,
    gridrdLhr,
    gridrLhr,
    gridruDdeg,
    gridruLhr,
    gridSpanD,
    gridSpanDrad,
    gridSpanL,
    gridSpanLrad,
    griduDdeg,
    gridWidthXpx,        
    gridWidthXpx2=0,
    gridHeightYpx,                             // Grid dimensions in X and Y;
    gridHeightYpx2=0;                          // Half-width or height
    Legend1a=10, 
    Legend1b=10,
    Lsteps=10,
    lx=0,
    rx=0,
    starsmagnitude=0,
    starsmagnitudedelta=0,
    ty=0;
    
     




var gridDimensions=new Array(
    		  new Array(180,80.00,2.000,3),                                                 // FoV, L grid distance in deg, D grid distance in deg, default limiting star magnitude level for this zoom level 
    		  new Array(150,60.00,2.000,3),
    		  new Array(120,50.00,2.000,3),
    		  new Array( 90,40.00,2.666,4),
    		  new Array( 75,30.00,2.000,4),
    		  new Array( 60,24.50,1.666,5),
    		  new Array( 45,20.00,1.333,5),
    		  new Array( 35,15.00,1.000,6),
    		  new Array( 30,12.00,0.800,6),
    		  new Array( 25,10.00,0.666,6),
    		  new Array( 20, 8.00,0.633,6),
    		  new Array( 15, 6.00,0.400,7),
    		  new Array( 10, 4.00,0.266,7),
    		  new Array(  7, 3.00,0.200,8),
    		  new Array(  5, 2.00,0.133,8),
    		  new Array(  4, 1.50,0.100,9),
    		  new Array(  3, 1.00,0.066,9),
    		  new Array(  2, 0.80,0.050,10),
    		  new Array(  1, 0.40,0.026,10),
    		  new Array(0.5, 0.20,0.012,12),
    		  new Array(0.25,0.20,0.012,14),
    		  new Array(0.2 ,0.20,0.012,16),
    		  new Array(0.15 ,0.20,0.012,16),
    		  new Array(0.1 ,0.20,0.012,16)
    		  );









var cnv;
var jg;  
var xmlhttp;
var astroObjects;
var constellationObjects;
var astroObjectsArr= new Array();
var numberOfStarsLimit=200;
var numberOfDsoLimit=200;
var atlasPageDiv1x=0, atlasPageDiv1y=0;
var labelsOn=true; helpOn=false;
var onClickHandling=false;                                                                        // Java Graphics object;
var deltaMag = 0;
var minObjectSize=5;
var abbrevOn = false;
var fsOn=false;

var getObjects = true;
var getStars=true;
var getStarsLimit=7;
var getObjectsLimit=7;



// Positioning Parameters



var gridBorder=true;     gridCoordLines=true;                        // Grid border (true or false)




// Color Parameters
var canvasBkGroundColor    ='#000000';                                          // Background color of the canvas;
var coordLineColor         ='#660000';                                          // Coordinate grid line colors
var coordLblBkGroundColor  ='#000000';                                          // Background Color of coordinate Labels
var coordLblColor          ='#DDDDDD';                                          // Color of coordinate labels
var coordBkGroundColor     ='#000000';                                          // Background color of coordinates of mouse position
var coordColor             ='#AAAAAA';                                          // Color of coordinates of mouse position
var gridBorderColor        ='#FFFF00';                                          // Color of the grid border
var starColor              ='#FFFFFF';
var starColor2             ='#555555';
var seenColor              ='#DD0000';
var seenXColor             ='#DDBB00';
var seenYColor             ='#00FF00';
var constellationColor     ='#CCCCCC';

// Layout Parameters
var  coordGridsV;                                                   // Obsolete - Number of grid lines H and V
var coordCnvXpx, coorCnvYpx;                                                    // Location of mouse coordinate positions relative to canvas


// Help parameters for parameter passing
                                                      // several opertions help parameter

//astro functions ========================================================================================================
//function getAstroObjectValue(j,field)                   Gets the value of the field of the ith element of the object arrray
//function astroDrawStarObject(Lhr,Ddeg,mag,name,object)  Draw a star on the grid
//function astroDrawStarxObject(Lhr,Ddeg,mag,name,object) Draw a double star on the grid
//function astroDrawObjects()                             Draw astro objects

//atlas Functions ========================================================================================================
//function atlasFillPage()                                Fills up an atlas page - Main entry point
//function atlasPageUpBtnFn()                             Move 1 page in the N direction
//function atlasPageSmallUpBtnFn()                        Move 1 division in the N direction
//function atlasPageDownBtnFn()                           Move 1 page in the S direction
//function atlasPageSmallDownBtnFn()                      Move 1 division in the S direction
//function atlasPageLeftBtnFn()                           Move 1 page in the E direction
//function atlasPageSmallDownBtnFn()                      Move 1 division in the E direction
//function atlasPageRightBtnFn()                          Move 1 page in the W direction
//function atlasPageSmallRightBtnFn()                     Move 1 division in the W direction
//function atlasPageZoomInBtnFn()                         Zoom in 1 level
//function atlasPageZoomOutBtnFn()                        Zoom out 1 level
//function atlasRedraw()                                  draws canvas, gridborder, coordinate lines, astro objects

//canvas event actions ===================================================================================================
//function canvasCursor(theCursor)                        Set the cursor shape
//function canvasOnClick(event)                           onClick event
//function canvasOnMouseMove(event)                       onMouseMove event

//canvas functions ======================================================================================================
//function canvasDrawEllipseTilt(cx,cy,w,h,angle)        draws a tilted ellipse
//function canvasDrawFilledCircle(cx,cy,d)               draws a filled circle
//function canvasDrawLabel(bkGrdColor,textColor,theLabel,a,b,w,h,align) draws a label
//function canvasDrawLine(A1px,B1px,A2px,B2px)           draws a line between specified coordinates
//function canvasDrawPoint(Apx,Bpx)                      draws a point on the specified coordinates
//function canvasDrawString(theString,Apx,Bpx)           draws a string on the specified coordinates
//function canvasDrawStringRect(theString,Apx,Bpx,w,h,theAlignment) draws a string on the specified coordinates in a rectangle
//function canvasFillRect(Apx,Bpx,Widthpx,Heightpx)      draws a filled rectangle
//function canvasInit(canvas)                            sets canvas dimensions
//function canvasRedraw()                                redraws the canvas in the background color

//div functions ===========================================================================================================
//function divInit(theDiv)                                sets the div dimensions

//grid functions =========================================================================================================
//function gridClearBorder()                              Turn off grid border
//function gridDrawBorder()                               Draws the grid border
//function gridDrawCoordLines()                           draws the grid coordinate lines
//function gridDrawEllipseTilt(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg) draws a tilted ellipse
//function gridDrawFilledCirclePx(Lhr,Ddeg,DiamPx)        draw filled circle of radius in px on grid coordinates
//function gridDrawLinePxLR(Lhr,Ddeg,d)                   draw horizontal line of d pxs on grid coordinates 
//function gridDrawLineLD(Lhr1,Ddeg1,Lhr2,Ddeg2)          draw grid line between grid coordinates
//function gridDrawPointLD(Lhr,Ddeg)                      draw a point on the grid coordinates
//function gridInit()                                     set grid dimensions
//function gridInitScale(gridLHr,gridDdeg,desiredScale)   set the grid coordinates and the desired minimal FoV in deg
//function gridInitScaleFixed(gridLHr,gridDdeg,desiredScale) set the grid coordinates and the desired minimal FoV in the prdefined step nr.
//function gridLDrad(Lhr,Ddeg)                            calculates the coordinates from hr/deg to rad in gridLxRad,gridDyRad
//function gridLDinvRad(XpxAbsScr,YpxAbsScr)              calculates the coordinates from Xpx,Ypx to rad in gridLxRad,gridDyRad
//function gridSetBorderColor(theColor)                   sets the grid border color
//function gridXpx(Lrad)                                  converts a number of rad ortho coordinates to a number of X pixels
//function gridYpx(Drad)                                  converts a number of rad ortho coordinates to a number of Y pixels
//function gridZoom(zoomFactor)                           zooms in or out by a number of zoom steps

//Presentation functions =================================================================================================
//function coordHrDecToHrMin(theHr)                       display hr as xhym (if y>0)
//function coordHrDecToHrMinSec(theHr)                    display hr as xhymzs if z,y>0)
//function coordDeclDecToDegMin(theDeg)                   display decl as x&deg;y' if y>0
//function coordGridLxDyToString()                        display screen coordinates as L,D

function getAstroObjectValue(j,field)
{ if((thelist=astroObjects[j].getElementsByTagName(field)) != null)
    if((thenode=thelist[0].firstChild) != null)
      return thenode.nodeValue; 
  return '';
}
function getConstellationBoundryValue(j,field)
{ if((thelist=constellationObjects[j].getElementsByTagName(field)) != null)
    if((thenode=thelist[0].firstChild) != null)
      return thenode.nodeValue; 
  return '';
}
//astro functions ========================================================================================================
function astroDrawStarObject(Lhr,Ddeg,vMag,name,object)
{ jg.setColor(starColor);
  //var d = Math.min(Math.max(Math.round((gridActualDimension-(0.02*vMag)+2)*1.5),1),10);
  var d= Math.floor(2*((gridDimensions[gridActualDimension][3])-(vMag/100))+1);
  gridDrawStarObject(Lhr,Ddeg,d,name,object);
  //gridDrawFilledCirclePxObject(Lhr,Ddeg,d,name,object);
}
function astroDrawStarxObject(Lhr,Ddeg,vMag,name,object)
{ var d=Math.max(Math.round((gridActualDimension-(0.02*vMag)-1.5)*1.5),1);
  jg.setColor(starColor);
  if(gridDrawFilledCirclePxObject(Lhr,Ddeg,d,name,object) && (d>1))
  gridDrawLinePxLR(Lhr,Ddeg,(d>>1)+2);
  return true;
}
function astroDrawBRTNBObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object)
{ gridDrawRectangleCWHObject(Lhr,Ddeg,diam1,diam1,pa,nm,seen,object);
}
function astroDrawCLANBObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object)
{ gridDrawRectangleRectangleCWHObjectInterval(Lhr,Ddeg,diam1,diam1,pa,nm,seen,object,3);
}
function astroDrawDRKNBObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object,interval)
{ gridDrawRectangleCWHObjectInterval(Lhr,Ddeg,diam1,diam1,pa,nm,seen,object,interval);
}
function astroDrawHIIObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object)
{ gridDrawRectangleCWHObject(Lhr,Ddeg,diam1,diam1,pa,nm,seen,object);
}
function astroDrawGCObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object)
{ gridDrawGCObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object);
}
function astroDrawGXObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object)
{ gridDrawEllipseTiltObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object);
}
function astroDrawGXCLObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object)
{ gridDrawGXCLObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object);
}
function astroDrawOCObject(Lhr,Ddeg,diam,nm,seen,object)
{ gridDrawEllipseTiltObjectInterval(Lhr,Ddeg,(diam),(diam),0,nm,seen,object,3);
}
function astroDrawPNObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object)
{ gridDrawPNObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object);
}
function astroDrawQSRObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object)
{ gridDrawQSRObject(Lhr,Ddeg,diam1,diam2,pa,nm,seen,object);
}
function incons(con,cons)
{ for(key in cons)
	if(con==cons[key])
	  return true;
  return false;
}
function astroDrawConBoundries()
{ var cons = new Array();
  jg.setColor(constellationColor);
  for(var i=0;i<conBoundries.length;i++)
  { if(gridDrawLongLineLD(conBoundries[i]['ra0'], conBoundries[i]['decl0'], conBoundries[i]['ra1'], conBoundries[i]['decl1']))
	{ if(!incons(conBoundries[i]['con0'],cons))
	  { cons[cons.length]=(conBoundries[i]['con0']);
        if(conBoundries[i]['con0pos']=="L")
	      canvasDrawLabel(canvasBkGroundColor, constellationColor, conBoundries[i]['con0'], ((canvasX1px+canvasX2px)/2)-(fontSize1b*3)-5, (canvasY1px+canvasY2px)/2-(fontSize1a>>1), (fontSize1b*3), fontSize1a, 'left');
        if(conBoundries[i]['con0pos']=="R")
	      canvasDrawLabel(canvasBkGroundColor, constellationColor, conBoundries[i]['con0'], ((canvasX1px+canvasX2px)/2)+5, (canvasY1px+canvasY2px)/2-(fontSize1a>>1), (fontSize1b*3), fontSize1a, 'left');
        if(conBoundries[i]['con0pos']=="A")
	      canvasDrawLabel(canvasBkGroundColor, constellationColor, conBoundries[i]['con0'], ((canvasX1px+canvasX2px)/2)-(fontSize1b*2), (canvasY1px+canvasY2px)/2 + 2, (fontSize1b*3), fontSize1a, 'left');
        if(conBoundries[i]['con0pos']=="B")
	      canvasDrawLabel(canvasBkGroundColor, constellationColor, conBoundries[i]['con0'], ((canvasX1px+canvasX2px)/2)-(fontSize1b*2), (canvasY1px+canvasY2px)/2 - fontSize1a - 2, (fontSize1b*3), fontSize1a, 'left');
	  }
      if((conBoundries[i]['con1']) && (!incons(conBoundries[i]['con1'],cons)))
      { cons[cons.length]=(conBoundries[i]['con1']);
        if(conBoundries[i]['con1pos']=="L")
	      canvasDrawLabel(canvasBkGroundColor, constellationColor, conBoundries[i]['con1'], ((canvasX1px+canvasX2px)/2)-(fontSize1b*3)-5, (canvasY1px+canvasY2px)/2-(fontSize1a>>1), (fontSize1b*3), fontSize1a, 'left');
        if(conBoundries[i]['con1pos']=="R")
	      canvasDrawLabel(canvasBkGroundColor, constellationColor, conBoundries[i]['con1'], ((canvasX1px+canvasX2px)/2)+5, (canvasY1px+canvasY2px)/2-(fontSize1a>>1), (fontSize1b*3), fontSize1a, 'left');
        if(conBoundries[i]['con1pos']=="A")
	      canvasDrawLabel(canvasBkGroundColor, constellationColor, conBoundries[i]['con1'], ((canvasX1px+canvasX2px)/2)-(fontSize1b*2), (canvasY1px+canvasY2px)/2 + 2, (fontSize1b*3), fontSize1a, 'left');
        if(conBoundries[i]['con1pos']=="B")
	      canvasDrawLabel(canvasBkGroundColor, constellationColor, conBoundries[i]['con1'], ((canvasX1px+canvasX2px)/2)-(fontSize1b*2), (canvasY1px+canvasY2px)/2 - fontSize1a-2, (fontSize1b*3), fontSize1a, 'left');
      }
	}
  }
  if(cons.length==0)
  { gridLxRad=gridL0rad;
    gridDyRad=gridD0rad;
	canvasDrawLabel(canvasBkGroundColor, constellationColor, astroGetConstellationFromCoordinates(gridL0rad*f12OverPi,gridD0rad*f180OverPi), gridOffsetXpx+2, gridOffsetYpx, (fontSize1b*3), fontSize1a, 'left');
  }
}
function astroDrawConstellations()
{ if(conBoundries.length == 0)
    astroGetConstellationBoundriesJSON();
  else
    astroDrawConBoundries();
}
function astroDrawObjects()
{ astroDrawStarsArr(0,astroObjectsArr.length,99);
  astroDrawObjectsArr(0,astroObjectsArr.length,99);
  canvasreDrawNamedLabel(coordBkGroundColor,coordColor,atlasPageDone,5,canvasDimensionYpx-20,500,16,'left','infoline');
  jg.paint();
  if(getStarsLimit<starsmagnitude)
  { astroGetStarsMagnitudeJSON((getStarsLimit==7?-2:getStarsLimit),getStarsLimit+1);
  }
  if (getObjectsLimit<atlasmagnitude)
  { astroGetObjectsMagnitudeJSON((getObjectsLimit==7?-999999:getObjectsLimit),getObjectsLimit+1);
  }
}
function astroDrawObjectsArr(from,to,toMagnitude)
{ jg.setFont("Lucida Console", fontSize1a+"px", Font.PLAIN);
  for(var i=from;i<to;i++)
  { if((astroObjectsArr[i]["type"]!='AASTAR1')&&
	   ((astroObjectsArr[i]["mag"]<=atlasmagnitude)||
	    ((astroObjectsArr[i]["mag"]>99)&&(astroObjectsArr[i]["diam1"]>=(60*(15-atlasmagnitude))))
	   ))
    { if(astroObjectsArr[i]["type"]=='AA1STAR')
        astroDrawStarObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='AA2STAR')
        astroDrawStarxObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='AA3STAR')
        astroDrawStarxObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='AA4STAR')
        astroDrawStarxObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='AA5STAR')
        astroDrawStarxObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='AA6STAR')
        astroDrawStarxObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='AA7STAR')
        astroDrawStarxObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='AA8STAR')
        astroDrawStarxObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='ASTER')
        astroDrawOCObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='BRTNB')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='CLANB')
        astroDrawCLANBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='DRKNB')
        astroDrawDRKNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i,10);
      else if(astroObjectsArr[i]["type"]=='DS')
        astroDrawStarxObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["mag"],astroObjectsArr[i]["name"],i);
      else if(astroObjectsArr[i]["type"]=='EMINB')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='ENRNN')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='ENSTR')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='GALCL')
        astroDrawGXCLObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='GALXY')
        astroDrawGXObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='GLOCL')
        astroDrawGCObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='GXADN')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='GXAGC')
        astroDrawGCObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='GACAN')
        astroDrawCLANBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='HII')
        astroDrawHIIObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='LMCCN')
        astroDrawCLANBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='LMCDN')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='LMCGC')
        astroDrawGCObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='LMCOC')
        astroDrawOCObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='OPNCL')
        astroDrawOCObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='PLNNB')
        astroDrawPNObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='REFNB')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='RNHII')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='SMCCN')
        astroDrawCLANBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='SMCDN')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='SMCGC')
        astroDrawGCObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='SMCOC')
        astroDrawOCObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='SNREM')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='STNEB')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='QUASR')
        astroDrawQSRObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else if(astroObjectsArr[i]["type"]=='WRNEB')
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      else
        astroDrawBRTNBObject(astroObjectsArr[i]["ra"],astroObjectsArr[i]["decl"],astroObjectsArr[i]["diam1"],astroObjectsArr[i]["diam2"],astroObjectsArr[i]["pa"],(astroObjectsArr[i]["name"]),astroObjectsArr[i]["seen"],i);
      }
  }
  jg.paint();
  if(toMagnitude<atlasmagnitude)
  {	newtoMagnitude=toMagnitude+1;
    astroGetObjectsMagnitudeJSON(toMagnitude,newtoMagnitude);
    jg.paint();
  }
  else
  { canvasreDrawNamedLabel(coordBkGroundColor,coordColor,atlasPageDone,5,canvasDimensionYpx-20,500,16,'left','infoline');
    jg.paint();
    //alert(astroObjectsArr.length);
  }
}
function astroDrawStarsArr(from,to,toMagnitude)
{ jg.setFont("Lucida Console", fontSize1a+"px", Font.PLAIN);
  for(var i=from;i<to;i++)
  { if((astroObjectsArr[i]["type"]=='AASTAR1')&&(Math.floor(astroObjectsArr[i]["vMag"]/100)<=starsmagnitude))
    { name=astroObjectsArr[i]["nameBayer"]+'&nbsp;'+astroObjectsArr[i]["nameBayer2"]+'&nbsp;'; 
      //alert(name+' '+Math.floor(astroObjectsArr[i]["vMag"]/100)+' '+starsmagnitude);
      if(name!="&nbsp;&nbsp;") name+=astroObjectsArr[i]["nameCon"];
      astroDrawStarObject(astroObjectsArr[i]["RA2000"],astroObjectsArr[i]["DE2000"],astroObjectsArr[i]["vMag"],name,i);
    }
  }
  jg.paint();
  if(toMagnitude<starsmagnitude)
  {	newtoMagnitude=toMagnitude+1;
    astroGetStarsMagnitudeJSON(toMagnitude,newtoMagnitude);
  }
  else
  { canvasreDrawNamedLabel(coordBkGroundColor,coordColor,atlasPageDone,5,canvasDimensionYpx-20,500,16,'left','infoline');
    jg.paint();
  }
}
function astroGetConstellationBoundriesJSON()
{ canvasreDrawNamedLabel(coordBkGroundColor,coordColor,'Fetching constellation boundries',5,canvasDimensionYpx-20,500,16,'left','infoline');
  jg.paint();
  var jsonhttp;
  if(window.XMLHttpRequest)
    jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Atlas pages are not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { if(jsonhttp.readyState==4)
    { conBoundries=eval("("+jsonhttp.responseText+")");
      astroDrawConBoundries();
    }
  };
  var url="ajaxinterface.php";
  url+="?instruction=getConstellationBoundriesJSON";
  url+="&lLhr="+gridlLhr;
  url+="&dDdeg="+griddDdeg;
  url+="&rLhr="+gridrLhr;
  url+="&uDdeg="+griduDdeg;
  //alert(url);
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
}
function astroGetConstellationFromCoordinates(thera,thedecl)
{ var tempdecl=-90;
  var tempcon="";
  var thera0=0.0;
  var thera1=0.0;
  var thedecl0, thedecl1;
  for(var i=0;i<conBoundries.length;i++)
  { thera0=conBoundries[i]['ra0']*1.0;
    thera1=conBoundries[i]['ra1']*1.0;
    thedecl0=conBoundries[i]['decl0']*1.0;
    thedecl1=conBoundries[i]['decl1']*1.0;
    if(Math.abs(conBoundries[i]['ra0']-conBoundries[i]['ra1'])>12)
    { if(Math.abs(thera-conBoundries[i]['ra0'])>12)
    	thera0+=((conBoundries[i]['ra0']<12)?24.0:-24.0); 
      else
      	thera1+=((conBoundries[i]['ra1']<12)?24.0:-24.0); 
    }
    //thedecl01=thedecl0+((thera-thera0)/(thera1-thera0)*(thedecl1-thedecl0));
    if(Math.abs(thera1-thera0)>0)
      thedecl01=thedecl0+((thera-thera0)/(thera1-thera0)*(thedecl1-thedecl0));
    else
      thedecl01=(thedecl0+thedecl1)/2;
	if(((thera0<=thera)&&(thera<=thera1)||(thera1<=thera)&&(thera<=thera0))&&
	   (thedecl01<thedecl)&&(thedecl01>tempdecl))
    { tempdecl=thedecl01;
      if(conBoundries[i]['con0pos']=="A")
    	tempcon=conBoundries[i]['con0'];
      if(conBoundries[i]['con0pos']=="B")
    	tempcon=conBoundries[i]['con1'];
      if(conBoundries[i]['con0pos']=="L")
        if(((thedecl1-thedecl0)/(thera1-thera0))>0)
          tempcon=conBoundries[i]['con1'];
        else
          tempcon=conBoundries[i]['con0'];
      if(conBoundries[i]['con0pos']=="R")
        if(((thedecl1-thedecl0)/(thera1-thera0))>0)
          tempcon=conBoundries[i]['con0'];
        else
          tempcon=conBoundries[i]['con1'];
    }
  }
  return tempcon;
}
function astroGetObjectsMagnitudeJSON(fromMagnitude,toMagnitude)
{ canvasreDrawNamedLabel(coordBkGroundColor,coordColor,atlasPageObjectsFetching+' '+toMagnitude,5,canvasDimensionYpx-20,500,16,'left','infoline');
  var jsonhttp;
  if(window.XMLHttpRequest)
	jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
	jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Atlas pages are not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { var from=astroObjectsArr.length;
	var to=from; 
    if(jsonhttp.readyState==4)
    { astroObjectsArr=astroObjectsArr.concat(eval("("+jsonhttp.responseText+")"));
      to=astroObjectsArr.length;
      astroDrawObjectsArr(from,to,toMagnitude);
    }
  };
  var url="ajaxinterface.php";
  url+="?instruction=getObjectsMagnitudeJSON";
  url+="&lLhr="+gridlLhr;
  url+="&dDdeg="+griddDdeg;
  url+="&rLhr="+gridrLhr;
  url+="&uDdeg="+griduDdeg;
  url+="&frommag="+fromMagnitude;
  url+="&tomag="+toMagnitude;
  //alert(url);
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
  getObjectsLimit=toMagnitude;
}
function astroGetStarsMagnitudeJSON(fromMagnitude,toMagnitude)
{ canvasreDrawNamedLabel(coordBkGroundColor,coordColor,atlasPageStarsFetching+' '+toMagnitude,5,canvasDimensionYpx-20,500,16,'left','infoline');
  jg.paint();
  var jsonhttp;
  if(window.XMLHttpRequest)
	  jsonhttp=new XMLHttpRequest();
  else if(window.activeXObject)
	  jsonhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
    alert("Atlas pages are not supported on non-xmlhttp machines");
  jsonhttp.onreadystatechange=function()
  { var from=astroObjectsArr.length;
	var to=from; 
    if(jsonhttp.readyState==4)
    { astroObjectsArr=astroObjectsArr.concat(eval("("+jsonhttp.responseText+")"));
      to=astroObjectsArr.length;
      astroDrawStarsArr(from,to,toMagnitude);
    }
  };
  //alert(gridlLhr+' '+gridrLhr);

  var url="ajaxinterface.php";
  url+="?instruction=getStarsMagnitudeJSON";
  url+="&lLhr="+gridlLhr;
  url+="&dDdeg="+griddDdeg;
  url+="&rLhr="+gridrLhr;
  url+="&uDdeg="+griduDdeg;
  url+="&frommag="+fromMagnitude;
  url+="&tomag="+toMagnitude;
  //alert(url);
  jsonhttp.open("GET",url,true);
  jsonhttp.send(null);
  getStarsLimit=toMagnitude;
}
function astroObjectDetails(object)
{ var ret='';
  if(this.substr(astroObjectsArr[object]["type"],0,6)=='AASTAR')
  { name=astroObjectsArr[object]["nameBayer"]+'&nbsp;'+astroObjectsArr[object]["nameBayer2"]+'&nbsp;'; 
    if(name!="&nbsp;&nbsp;") name+=astroObjectsArr[object]["nameCon"]; else name=astroObjectsArr[object]["name"];
    ret ="<span style='text-decoration:underline;'>"+this.atlasPageObjectTxt+":</span> "+name+"<br /> ";
    ret+=this.atlasPageMagnTxt+": "+(astroObjectsArr[object]["vMag"]/100)+"<br /> ";
    ret+=this.atlasPageTypeTxt+": "+astroObjectsArr[object]["spType"]+"<br /> ";
  }
  else
  { ret ="<span style='text-decoration:underline;'>"+this.atlasPageObjectTxt+":</span> "+astroObjectsArr[object]["name"]+"<br /> ";
    ret+=this.atlasPageConsTxt+": "+astroObjectsArr[object]["con"]+"<br /> ";
    ret+=this.atlasPageTypeTxt+": "+astroObjectsArr[object]["type"]+"<br /> ";
    ret+=this.atlasPageSeenTxt+": "+astroObjectsArr[object]["seen"]+"<br /> ";
    ret+=(((mag=astroObjectsArr[object]["mag"])==99.9)?"":this.atlasPageMagnTxt)+(((subr=astroObjectsArr[object]["subr"])==99.9)?"":"/"+this.atlasPageSubrTxt)+((mag==99.9)?"":": ")+((mag==99.9)?"":mag)+((subr==99.9)?"":"/"+subr)+((mag==99.9)?"":"<br /> ");
    ret+=(( (diam1=(Math.round(astroObjectsArr[object]["diam1"]/6)/10))==0)?"":this.atlasPageDiamTxt+": "+diam1+
         ((((diam2=(Math.round(astroObjectsArr[object]["diam2"]/6)/10))==0)||(diam2==diam1))?"":"x"+diam2)+"'"+(((pa=astroObjectsArr[object]["pa"])==999)?"":"/"+pa+"&deg;")+"<br /> ");
    ret+=(((descr=astroObjectsArr[object]["description"])=="")?"":"<hr />"+this.htmlentities(descr)+"<br /> ");
  }
  return ret;
}

//atlas Functions =========================================================================================================
function atlasDrawLegend()
{ canvasDrawStarLegend(canvasOffsetXpx+Legend1a+550, canvasOffsetYpx-5, 1);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-.5), Legend1a+510, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+500, canvasOffsetYpx-5, 2);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-1), Legend1a+460, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+450, canvasOffsetYpx-5, 3);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-1.5), Legend1a+410, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+400, canvasOffsetYpx-5, 4);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-2), Legend1a+360, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+350, canvasOffsetYpx-5, 5);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-2.5), Legend1a+310, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+300, canvasOffsetYpx-5, 6);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-3), Legend1a+260, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+250, canvasOffsetYpx-5, 7);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-3.5), Legend1a+210, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+200, canvasOffsetYpx-5, 8);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-4), Legend1a+160, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+150, canvasOffsetYpx-5, 9);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-4.5), Legend1a+110, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+100,canvasOffsetYpx-5,10);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-5), Legend1a+60, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+50,canvasOffsetYpx-5,11);
  canvasDrawStringRect((((gridDimensions[gridActualDimension][3]))-5.5), Legend1a+10, canvasOffsetYpx+canvasDimensionYpx-17, 30, 10, 'center');
  canvasDrawStarLegend(canvasOffsetXpx+Legend1a+0,canvasOffsetYpx-5,12);
  
  jg.drawEllipseTilt(Legend1b+0, canvasOffsetYpx+canvasDimensionYpx+5, 10, 5, 45);
  canvasDrawStringRect('GALXY', Legend1b+10, -fontSize1a, 30, 10, 'left');
  
  jg.drawEllipseTilt(Legend1b+50,canvasOffsetYpx+canvasDimensionYpx+5,5,5,0);
  canvasDrawLine(Legend1b+50-((5+1)>>1),-5, Legend1b+50-5, -5);
  canvasDrawLine(Legend1b+50, -10, Legend1b+50, -5-((5+1)>>1));
  canvasDrawLine(Legend1b+50, 0, Legend1b+50, -5+((5+1)>>1));
  canvasDrawLine(Legend1b+50+((5+1)>>1), -5, Legend1b+50+5, -5);
  canvasDrawStringRect('PLANB', Legend1b+60, -fontSize1a, 30, 10, 'left');
  
  jg.drawEllipseTilt(Legend1b+100,canvasOffsetYpx+canvasDimensionYpx+5,5,5,0);
  canvasDrawLine(Legend1b+97,-5,Legend1b+103,-5);
  canvasDrawLine(Legend1b+100,-2,Legend1b+100,-8);
  canvasDrawStringRect('GLOCL', Legend1b+110, -fontSize1a, 30, 10, 'left');
  
  jg.drawEllipseTiltInterval(Legend1b+150, canvasOffsetYpx+canvasDimensionYpx+5, 12, 12, 0, 2);
  canvasDrawStringRect('OPNCL', Legend1b+160, -fontSize1a, 30, 10, 'left');
  
  canvasDrawLine(Legend1b+194,1,Legend1b+196,1);
  canvasDrawLine(Legend1b+199,1,Legend1b+201,1);
  canvasDrawLine(Legend1b+204,1,Legend1b+206,1);
  canvasDrawLine(Legend1b+194,-11,Legend1b+196,-11);
  canvasDrawLine(Legend1b+199,-11,Legend1b+201,-11);
  canvasDrawLine(Legend1b+204,-11,Legend1b+206,-11);
  canvasDrawLine(Legend1b+194,-11,Legend1b+194,-9);
  canvasDrawLine(Legend1b+194,-6,Legend1b+194,-4);
  canvasDrawLine(Legend1b+194,-1,Legend1b+194,1);
  canvasDrawLine(Legend1b+206,-11,Legend1b+206,-9);
  canvasDrawLine(Legend1b+206,-6,Legend1b+206,-4);
  canvasDrawLine(Legend1b+206,-1,Legend1b+206,1);
  canvasDrawStringRect('DRKNB', Legend1b+210, -fontSize1a, 50, 10, 'left');
  
  canvasDrawLine(Legend1b+244,1,Legend1b+256,1);
  canvasDrawLine(Legend1b+244,-11,Legend1b+256,-11);
  canvasDrawLine(Legend1b+244,-11,Legend1b+244,1);
  canvasDrawLine(Legend1b+256,-11,Legend1b+256,1);
  canvasDrawStringRect('NEB', Legend1b+260, -fontSize1a, 30, 10, 'left');
  
  canvasDrawLine(Legend1b+294,1,Legend1b+295,1);
  canvasDrawLine(Legend1b+298,1,Legend1b+299,1);
  canvasDrawLine(Legend1b+302,1,Legend1b+303,1);
  canvasDrawLine(Legend1b+296,-1,Legend1b+304,-1);
  
  canvasDrawLine(Legend1b+302,-11,Legend1b+303,-11);
  canvasDrawLine(Legend1b+298,-11,Legend1b+299,-11);
  canvasDrawLine(Legend1b+294,-11,Legend1b+295,-11);
  canvasDrawLine(Legend1b+296,-9,Legend1b+304,-9);
  
  canvasDrawLine(Legend1b+294,-11,Legend1b+294,-11);
  canvasDrawLine(Legend1b+294,-8,Legend1b+294,-7);
  canvasDrawLine(Legend1b+294,-4,Legend1b+294,-3);
  canvasDrawLine(Legend1b+294,0,Legend1b+294,1);
  canvasDrawLine(Legend1b+296,-9,Legend1b+296,-1);
  
  canvasDrawLine(Legend1b+306,1,Legend1b+306,1);
  canvasDrawLine(Legend1b+306,-2,Legend1b+306,-3);
  canvasDrawLine(Legend1b+306,-6,Legend1b+306,-7);
  canvasDrawLine(Legend1b+306,-10,Legend1b+306,-11);
  canvasDrawLine(Legend1b+304,-9,Legend1b+304,-1);
  canvasDrawStringRect('CLANB', Legend1b+310, -fontSize1a, 30, 10, 'left');

  var x=Legend1b+350;
  var y=-5;
  var d1=12;
  var d2=12;
  var x1=0, x2=0, y1=0, y2=0;
  x1=x;
  y1=y+((d2+1)>>1);
  x2=x+((d1+1)>>1);
  y2=y+((d2+1)>>3);
  canvasDrawLine(x1,y1,x2,y2);
  x1=x+((d1+1)>>1);
  y1=y+((d2+1)>>3);
  x2=x+(3*((d1+1)>>3));
  y2=y-((d2+1)>>1);
  canvasDrawLine(x1,y1,x2,y2);
  x1=x+(3*((d1+1)>>3));
  y1=y-((d2+1)>>1);
  x2=x-(3*((d1+1)>>3));
  y2=y-((d2+1)>>1);
  canvasDrawLine(x1,y1,x2,y2);
  x1=x-(3*((d1+1)>>3));
  y1=y-((d2+1)>>1);
  x2=x-((d1+1)>>1);
  y2=y+((d2+1)>>3);
  canvasDrawLine(x1,y1,x2,y2);
  x1=x-((d1+1)>>1);
  y1=y+((d2+1)>>3);
  x2=x;
  y2=y+((d2+1)>>1);
  canvasDrawLine(x1,y1,x2,y2);
  canvasDrawStringRect('GALCL', Legend1b+360, -fontSize1a, 30, 10, 'left');

  x=Legend1b+400;
  y=-5;
  d1=3;
  d2=3;
  canvasDrawLine(x-2, y, x-d1-2, y);
  canvasDrawLine(x, y-2-d2, x, y-2);
  canvasDrawLine(x, y+2+d2, x, y+2);
  canvasDrawLine(x+2, y, x+2+d1, y);
  canvasDrawStringRect('QUASR', Legend1b+410, -fontSize1a, 30, 10, 'left');
  
}
function atlasFillPage()
{ divInit('atlasPageDiv');
  canvasInit('atlasPageDiv');
  gridInit();
  gridInitScale(this.atlaspagerahr,this.atlaspagedecldeg,this.atlaspagezoomdeg);
  getStarsLimit=7;
  getObjectsLimit=7;
  astroObjectsArr=new Array();
  atlasRedraw();
  jg.paint();
  document.getElementById('atlasPageDiv1').style.filter='alpha(opacity=90)';
  document.getElementById('atlasPageDiv1').style.opacity=0.9;
  document.getElementById("atlasPageDiv1").style.visibility='hidden';
  document.getElementById('atlasPageDiv2').style.filter='alpha(opacity=90)';
  document.getElementById('atlasPageDiv2').style.opacity=0.9;
  document.getElementById("atlasPageDiv2").style.visibility='hidden';
  document.getElementById('atlasPageDiv3').style.filter='alpha(opacity=90)';
  document.getElementById('atlasPageDiv3').style.opacity=0.9;
  document.getElementById("atlasPageDiv3").style.visibility='hidden';
  this.onKeyDownFns[this.onKeyDownFns.length] = canvasOnKeyDown;
}
function atlasPagePanZoom(event)
{ getStarsLimit=7;
  getObjectsLimit=7;
  astroObjectsArr=new Array();
  if((event.ctrlKey)&&(event.shiftKey))
  { if(event.keyCode==38) 
      gridZoom(1);
    if(event.keyCode==40) 
      gridZoom(-1);
  }
  else
  { if(event.keyCode==37) 
      atlaspagerahr+=(gridSpanL*((event.ctrlKey)?0.0025:((event.shiftKey)?0.1:0.025)));
    if(event.keyCode==39) 
      atlaspagerahr-=(gridSpanL*((event.ctrlKey)?0.0025:((event.shiftKey)?1:0.025)));
    if(event.keyCode==38) 
      atlaspagedecldeg+=(gridSpanD*((event.ctrlKey)?0.04:((event.shiftKey)?1.6:0.4)));
    if(event.keyCode==40) 
      atlaspagedecldeg-=(gridSpanD*((event.ctrlKey)?0.04:((event.shiftKey)?1.6:0.4)));
  }
  if(atlaspagerahr<0) atlaspagerahr+=24;
  if(atlaspagerahr>24) atlaspagerahr-=24;
  if(atlaspagedecldeg<(-90+(gridSpanD/2))) atlaspagedecldeg=(-90+(gridSpanD/2));
  if(atlaspagedecldeg>(90-(gridSpanD/2))) atlaspagedecldeg=90-(gridSpanD/2);  
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasRedraw()
{ canvasRedraw();
  gridSetBorderColor(gridBorderColor);
  if(gridCoordLines)
    gridDrawCoordLines();
  document.getElementById("atlasPageDiv").style.cursor='default';
  gridShowInfo();
  atlasDrawLegend();
  canvasDrawNamedLabel(coordBkGroundColor,coordColor,atlasPageDone,5,canvasDimensionYpx-20,500,16,'left','infoline');
  jg.paint();
  astroDrawConstellations();
  astroDrawObjects();
  gridDrawBorder();
  jg.paint();
}
//canvas actions =========================================================================================================
function canvasCursor(theCursor)
{ cnv.style.cursor = theCursor;
}
function canvasOnClick(event)
{ if(onClickHandling==true)
    return;
  onClickHandling=true;
  x=event.clientX;
  y=event.clientY+1;
  if((x>div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx)&&(x<div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx)&&
       (y>div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx)&&(y<div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx))
  { gridLDinvRad(x,y);
    atlaspagerahr=gridLxRad*f12OverPi;
    atlaspagedecldeg=gridDyRad*f180OverPi;
    if((atlaspagedecldeg+gridSpanD)>90)
      atlaspagedecldeg=90-gridSpanD;
    else if((atlaspagedecldeg-gridSpanD)<-90)
      atlaspagedecldeg=-90+gridSpanD;
    gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
    getStarsLimit=7;
    getObjectsLimit=7;
    astroObjectsArr=new Array();
    atlasRedraw();
  }
  onClickHandling=false;
}
function canvasOnKeyDown(event)
{ if((document.activeElement.id=='quickpickobject')||(document.activeElement.id=='deepskylog_id')||(document.activeElement.id=='passwd'))
    return true;
  if(((event.keyCode)>=37)&&((event.keyCode)<=40)) // arrows
     atlasPagePanZoom(event);
  else if((event.keyCode>=48)&&(event.keyCode<=57)) // 0..9
  { var $zoom=(event.keyCode-47);
    gridZoomLevel(gridMaxDimension-$zoom);
    getStarsLimit=7;
    getObjectsLimit=7;
    astroObjectsArr=new Array();
    atlasRedraw();
  }
  else if((event.keyCode>=96)&&(event.keyCode<=105)) // 0..9
  { var $zoom=(event.keyCode-95);
    gridZoomLevel(gridMaxDimension-$zoom);
    getStarsLimit=7;
    getObjectsLimit=7;
    astroObjectsArr=new Array();
    atlasRedraw();
  }
  else if(event.keyCode==65) // A
  { abbrevOn=!(abbrevOn);
    if(!(abbrevOn))
    { document.getElementById("atlasPageDiv3").style.visibility='hidden';
    }
    else
    { document.getElementById("atlasPageDiv3").style.left  =divOffsetXpx+'px';
      document.getElementById("atlasPageDiv3").style.top   =divOffsetYpx+'px';
      document.getElementById("atlasPageDiv3").style.width =(div5Width-divOffsetXpx-divOffsetX2px-4)+'px';
      document.getElementById("atlasPageDiv3").style.height=(div5Height-divOffsetYpx-divOffsetY2px-4)+'px';
      document.getElementById("atlasPageDiv3").style.visibility='visible';
    }
  }
  else if(event.keyCode==70) // F
  { if(fsOn)
	{ jg.clear();
	  jg.paint();
	  div5Width=div5StdWidth;
      div5Height=div5StdHeight;
      document.getElementById("atlasPageDiv").style.width=div5StdWidth+"px";
      document.getElementById("atlasPageDiv").style.height=div5StdHeight+"px";
      document.getElementById("div5").style.width=div5StdWidth+"px";
      document.getElementById("div5").style.height=div5StdHeight+"px";
      document.getElementById("div5").style.left=div5Left+"px";
      document.getElementById("div5").style.top=div5Top+"px";
      canvasInit('atlasPageDiv');
      gridInit();
      gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
      atlasRedraw();
      jg.paint();
	}
    else
    { jg.clear();
	  jg.paint();
	  document.getElementById("div5").style.left="0px";
      document.getElementById("div5").style.top="0px";
      div5Width=theClientWidth;
      div5Height=theClientHeight;
      document.getElementById("div5").style.width=theClientWidth+"px";
      document.getElementById("div5").style.height=theClientHeight+"px";
      document.getElementById("atlasPageDiv").style.width=theClientWidth+"px";
      document.getElementById("atlasPageDiv").style.height=theClientHeight+"px";
      canvasInitFS('atlasPageDiv');
      gridInit();
      gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
      atlasRedraw();
      jg.paint();
    }
    fsOn=!fsOn;
  }
  else if(event.keyCode==71) // G
  { gridCoordLines=!(gridCoordLines);
    atlasRedraw();
  }
  else if(event.keyCode==72) // H
  { helpOn=!(helpOn);
    if(!(helpOn))
    { document.getElementById("atlasPageDiv2").style.visibility='hidden';
    }
    else
    { document.getElementById("atlasPageDiv2").style.left  =divOffsetXpx+'px';
      document.getElementById("atlasPageDiv2").style.top   =divOffsetYpx+'px';
      document.getElementById("atlasPageDiv2").style.width =(div5Width-divOffsetXpx-divOffsetX2px-4)+'px';
      document.getElementById("atlasPageDiv2").style.height=(div5Height-divOffsetYpx-divOffsetY2px-4)+'px';
      document.getElementById("atlasPageDiv2").style.visibility='visible';
    }
  }
  else if(event.keyCode==76) // L
  { labelsOn=!(labelsOn);
    atlasRedraw();
  }
  else if(event.keyCode==77) // M / m
  { if((event.ctrlKey)&&(event.shiftKey))
      atlasmagnitudedelta=8;
    else if(event.ctrlKey)
      atlasmagnitudedelta=0;
    else if(event.shiftKey)
      atlasmagnitudedelta=Math.min(atlasmagnitudedelta+1,5);
    else
      atlasmagnitudedelta=Math.max(atlasmagnitudedelta-1,-5);
    gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
    atlasRedraw();
  }
  else if(event.keyCode==80) // P
  { location.href= 'atlas.pdf?atlaspagerahr='+atlaspagerahr+'&atlaspagedecldeg='+atlaspagedecldeg+
                            '&atlaspagezoomdeg='+atlaspagezoomdeg+
                            '&atlasmagnitude='+atlasmagnitude+'&starsmagnitude='+starsmagnitude;
    /*
    appletText="<applet codebase=\"lib\\javaclasses\" code=\"atlaspage.class\" width=\""+div5Width+"\" height=\""+div5Height+"\" style=\"position:absolute;top:0px;left:0px;\">";
    appletText+="<param name=decl value="+gridD0rad+" />";
    appletText+="<param name=ra value="+gridL0rad+" />";
    appletText+="</applet>";
    document.getElementById('div5').innerHTML=appletText;
    */
  }
  else if(event.keyCode==83) // S / s
  { if((event.ctrlKey)&&(event.shiftKey))
      starsmagnitudedelta=5;
    else if(event.ctrlKey)
      starsmagnitudedelta=0;
    else if(event.shiftKey)
      starsmagnitudedelta=Math.min(starsmagnitudedelta+1,5);
    else
      starsmagnitudedelta=Math.max(starsmagnitudedelta-1,-5);
    gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
    atlasRedraw();    
  }
  event.returnValue = false;
  if(event.stopPropagation) event.stopPropagation();
  event.preventDefault();
  event.cancelBubble = true;
  return false;
}
function canvasOnMouseMove(event)
{ x=event.clientX;
  y=event.clientY+1;
  if((x>div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx)&&(x<div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx)&&
     (y>div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx)&&(y<div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx))
  { if (document.getElementById("atlasPageDiv").style.cursor!='crosshair')
      document.getElementById("atlasPageDiv").style.cursor='crosshair';
    gridLDinvRad(x,y);
    jg.setFont("Lucida Console",fontSize1a+"px",Font.PLAIN);
    if(document.getElementById('cursorpos'))
      canvasreDrawNamedLabel(coordBkGroundColor,coordColor,astroGetConstellationFromCoordinates(gridLxRad*f12OverPi,gridDyRad*f180OverPi)+" "+coordGridLxDyToString(),5,0,130,fontSize1a+4,'left','cursorpos');
    else
      canvasDrawNamedLabel(coordBkGroundColor,coordColor,astroGetConstellationFromCoordinates(gridLxRad*f12OverPi,gridDyRad*f180OverPi)+" "+coordGridLxDyToString(),5,0,130,fontSize1a+4,'left','cursorpos');
  }
  else if (document.getElementById("atlasPageDiv").style.cursor!='default')
  { document.getElementById("atlasPageDiv").style.cursor='default';
    canvasreDrawNamedLabel(coordBkGroundColor,coordColor,"          ",0,-17,150,16,'center','cursorpos');
  }
  jg.paint();
  if(((x!=atlasPageDiv1x)||(y!=atlasPageDiv1y))&&((document.getElementById("atlasPageDiv1").style.left!='0px')||(document.getElementById("atlasPageDiv1").style.top!='0px')))
  {  document.getElementById("atlasPageDiv1").style.visibility='hidden';
    document.getElementById("atlasPageDiv1").innerHTML='';
  }
}
function canvasOnObjectClick(event,object)
{ if(substr(astroObjectsArr[object]["type"],0,6)!='AASTAR')
    location='index.php?indexAction=quickpick&source=quickpick&myLanguages=true&object='+urlencode(astroObjectsArr[object]["name"])+'&searchObjectQuickPickQuickPick=Search%A0Object';
}

function canvasOnObjectMouseMove(event,object)
{ atlasPageDiv1x=event.clientX;
  atlasPageDiv1y=event.clientY+1;
  if((atlasPageDiv1x+195-div5Left)>div5Width)
    document.getElementById("atlasPageDiv1").style.left=(atlasPageDiv1x-185-div5Left)+'px';  
  else  
    document.getElementById("atlasPageDiv1").style.left=(atlasPageDiv1x+12-div5Left)+'px';
  if((atlasPageDiv1y+140-div5Top)>div5Height)
    document.getElementById("atlasPageDiv1").style.top=(atlasPageDiv1y-132-div5Top)+'px';  
  else  
    document.getElementById("atlasPageDiv1").style.top=(atlasPageDiv1y+2-div5Top)+'px';
  document.getElementById("atlasPageDiv1").style.width="175px";
  document.getElementById("atlasPageDiv1").style.height="130px";
  document.getElementById("atlasPageDiv1").innerHTML=astroObjectDetails(object);
  document.getElementById("atlasPageDiv1").style.visibility='visible';  
}

//canvas functions =======================================================================================================

function canvasDrawEllipseTilt(cx,cy,w,h,angle,name,seen)
{ angle=-angle;
  jg.drawEllipseTiltLimited(canvasOffsetXpx+cx,canvasOffsetYpx+canvasDimensionYpx-cy,w,h,angle,canvasOffsetXpx+gridOffsetXpx,canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,canvasOffsetYpx+gridOffsetYpx,name,seen);
}
function canvasDrawEllipseTiltObject(cx,cy,w,h,angle,name,seen,object)
{  angle=-angle;
  jg.drawEllipseTiltLimitedObject(canvasOffsetXpx+cx,canvasOffsetYpx+canvasDimensionYpx-cy,w,h,angle,canvasOffsetXpx+gridOffsetXpx,canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,canvasOffsetYpx+gridOffsetYpx,name,seen,object);
}
function canvasDrawEllipseTiltObjectInterval(cx,cy,w,h,angle,name,seen,object,interval)
{ angle=-angle;
  jg.drawEllipseTiltLimitedObjectInterval(canvasOffsetXpx+cx,canvasOffsetYpx+canvasDimensionYpx-cy,w,h,angle,canvasOffsetXpx+gridOffsetXpx,canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,canvasOffsetYpx+gridOffsetYpx,name,seen,object,interval);
}
function canvasDrawFilledCircle(cx,cy,d)
{  return jg.fillEllipse((canvasOffsetXpx+cx-((d+1)>>1)),(canvasOffsetYpx+canvasDimensionYpx-cy-((d+1)>>1)),d,d);
}
function canvasDrawFilledCircleObject(cx,cy,d,name,object)
{  return jg.fillEllipseObject(canvasOffsetXpx+cx-((d+1)>>1),canvasOffsetYpx+canvasDimensionYpx-cy-((d+1)>>1),d,d,lx,rx,ty,by,name,object);
}
function canvasDrawLabel(bkGrdColor,textColor,theLabel,a,b,w,h,align)
{ jg.setColor(bkGrdColor);
  canvasFillRect(a,b,w,h);
  jg.setColor(textColor);
  canvasDrawStringRect(theLabel,a,b,w,h,align);
}
function canvasDrawFreeLabel(bkGrdColor,textColor,theLabel,a,b,w,h,align)
{ jg.setColor(bkGrdColor);
  canvasFillRect(a,b,w,h);
  jg.setColor(textColor);
  canvasDrawString(theLabel,a,b);
}
function canvasDrawLine(A1px,B1px,A2px,B2px)
{ return jg.drawLine(canvasOffsetXpx+A1px,canvasOffsetYpx+canvasDimensionYpx-B1px,canvasOffsetXpx+A2px,canvasOffsetYpx+canvasDimensionYpx-B2px);
}
function canvasDrawLineObject(A1px,B1px,A2px,B2px,name,seen,object)
{ return jg.drawLineObject(canvasOffsetXpx+A1px,
                           canvasOffsetYpx+canvasDimensionYpx-B1px,
                           canvasOffsetXpx+A2px,
                           canvasOffsetYpx+canvasDimensionYpx-B2px,                         
                           canvasOffsetXpx+gridOffsetXpx,
                           canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,
                           canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,
                           canvasOffsetYpx+gridOffsetYpx,
                           name,seen,object);
}
function canvasreDrawNamedLabel(bkGrdColor,textColor,theLabel,a,b,w,h,align,name)
{ canvasreDrawStringNamedRect(theLabel,a,b,w,h,align,name);
}
function canvasDrawNamedLabel(bkGrdColor,textColor,theLabel,a,b,w,h,align,name)
{ jg.setColor(bkGrdColor);
  canvasFillRect(a,b,w,h);
  jg.setColor(textColor);
  canvasDrawStringNamedRect(theLabel,a,b,w,h,align,name);
}
function canvasDrawPoint(Apx,Bpx)
{ return jg.drawLine(canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function canvasDrawRectangleCWH(cx,cy,w,h,angle,name,seen)
{  angle=-angle;
  jg.drawRectangleLimited(canvasOffsetXpx+cx,
                          canvasOffsetYpx+canvasDimensionYpx-cy,
                          w,h,angle,
                          canvasOffsetXpx+gridOffsetXpx,
                          canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,
                          canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,
                          canvasOffsetYpx+gridOffsetYpx,
                          name,seen);
}
function canvasDrawRectangleCWHObject(cx,cy,w,h,angle,name,seen,object)
{  angle=-angle;
  jg.drawRectangleLimitedObject(canvasOffsetXpx+cx,
                          canvasOffsetYpx+canvasDimensionYpx-cy,
                          w,h,angle,
                          canvasOffsetXpx+gridOffsetXpx,
                          canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,
                          canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,
                          canvasOffsetYpx+gridOffsetYpx,
                          name,seen,object);
}
function canvasDrawRectangleCWHObjectInterval(cx,cy,w,h,angle,name,seen,object,interval)
{  angle=-angle;
  jg.drawRectangleLimitedObjectInterval(canvasOffsetXpx+cx,
                          canvasOffsetYpx+canvasDimensionYpx-cy,
                          w,h,angle,
                          canvasOffsetXpx+gridOffsetXpx,
                          canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,
                          canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,
                          canvasOffsetYpx+gridOffsetYpx,
                          name,seen,object,interval);
}
function canvasDrawRectangleRectangleCWHObjectInterval(cx,cy,w,h,angle,name,seen,object,interval)
{  angle=-angle;
  jg.drawRectangleRectangleLimitedObjectInterval(canvasOffsetXpx+cx,
                          canvasOffsetYpx+canvasDimensionYpx-cy,
                          w,h,angle,
                          canvasOffsetXpx+gridOffsetXpx,
                          canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,
                          canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,
                          canvasOffsetYpx+gridOffsetYpx,
                          name,seen,object,interval);
}
function canvasDrawStarLegend(x,y,d)
{ if(d==1)
  { jg.drawLine(x,y,x,y);
  }
  else if(d==2)
  { jg.drawLine(x,y+1,x+1,y+1);
    jg.drawLine(x,y  ,x+1,y);
  } 
  else if(d==3)
  { jg.drawLine(x-1,y+1,x+1,y+1);
    jg.drawLine(x-1,y  ,x+1,y);
    jg.drawLine(x-1,y-1,x+1,y-1);
   }
  else if(d==4)
  { jg.drawLine(x  ,y+1,x+1,y+1);
    jg.drawLine(x-1,y  ,x+2,y);
    jg.drawLine(x-1,y-1,x+2,y-1);
    jg.drawLine(x  ,y-2,x+1,y-2);
   }
  else if(d==5)
  { jg.drawLine(x-1,y-2,x+1,y-2);
    jg.drawLine(x-2,y-1,x+2,y-1);
    jg.drawLine(x-2,y  ,x+2,y);
    jg.drawLine(x-2,y+1,x+2,y+1);
    jg.drawLine(x-1,y+2,x+1,y+2);
  }
  else if(d==6)
  { jg.drawLine(x-1,y+2,x+2,y+2);
    jg.drawLine(x-2,y+1,x+3,y+1);
    jg.drawLine(x-2,y  ,x+3,y);
    jg.drawLine(x-2,y-1,x+3,y-1);
    jg.drawLine(x-2,y-2,x+3,y-2);
    jg.drawLine(x-1,y-3,x+2,y-3);
  }
  else if(d==7)
  { jg.drawLine(x-1,y-3,x+1,y-3);
    jg.drawLine(x-2,y-2,x+2,y-2);
    jg.drawLine(x-3,y-1,x+3,y-1);
    jg.drawLine(x-3,y  ,x+3,y);
    jg.drawLine(x-3,y+1,x+3,y+1);
    jg.drawLine(x-2,y+2,x+2,y+2);
    jg.drawLine(x-1,y+3,x+1,y+3);
  }
  else if(d==8)
  { jg.drawLine(x-1,y+3,x+2,y+3);
    jg.drawLine(x-2,y+2,x+3,y+2);
    jg.drawLine(x-3,y+1,x+4,y+1);
    jg.drawLine(x-3,y  ,x+4,y);
    jg.drawLine(x-3,y-1,x+4,y-1);
    jg.drawLine(x-3,y-2,x+4,y-2);
    jg.drawLine(x-2,y-3,x+3,y-3);
    jg.drawLine(x-1,y-4,x+2,y-4);
  }
  else if(d==9)
  { jg.drawLine(x-1,y-4,x+1,y-4);
    jg.drawLine(x-3,y-3,x+3,y-3);
    jg.drawLine(x-3,y-2,x+3,y-2);
    jg.drawLine(x-4,y-1,x+4,y-1);
    jg.drawLine(x-4,y  ,x+4,y);
    jg.drawLine(x-4,y+1,x+4,y+1);
    jg.drawLine(x-3,y+2,x+3,y+2);
    jg.drawLine(x-3,y+3,x+3,y+3);
    jg.drawLine(x-1,y+4,x+1,y+4);
  }
  else if(d==10)
  { jg.drawLine(x-1,y+4,x+2,y+4);
    jg.drawLine(x-3,y+3,x+4,y+3);
    jg.drawLine(x-3,y+2,x+4,y+2);
    jg.drawLine(x-4,y+1,x+5,y+1);
    jg.drawLine(x-4,y  ,x+5,y);
    jg.drawLine(x-4,y-1,x+5,y-1);
    jg.drawLine(x-4,y-2,x+5,y-2);
    jg.drawLine(x-3,y-3,x+4,y-3);
    jg.drawLine(x-3,y-4,x+4,y-4);
    jg.drawLine(x-1,y-5,x+2,y-5);
  }
  else if(d==11)
  { jg.drawLine(x-2,y-5,x+2,y-5);
    jg.drawLine(x-3,y-4,x+3,y-4);
    jg.drawLine(x-4,y-3,x+4,y-3);
    jg.drawLine(x-5,y-2,x+5,y-2);
    jg.drawLine(x-5,y-1,x+5,y-1);
    jg.drawLine(x-5,y  ,x+5,y);
    jg.drawLine(x-5,y+1,x+5,y+1);
    jg.drawLine(x-5,y+2,x+5,y+2);
    jg.drawLine(x-4,y+3,x+4,y+3);
    jg.drawLine(x-3,y+4,x+3,y+4);
    jg.drawLine(x-2,y+5,x+2,y+5);
  }
  else if(d>11)
  { jg.drawLine(x-2,y+5,x+3,y+5);
    jg.drawLine(x-4,y+4,x+5,y+4);
    jg.drawLine(x-4,y+3,x+5,y+3);
    jg.drawLine(x-5,y+2,x+6,y+2);
    jg.drawLine(x-5,y+1,x+6,y+1);
    jg.drawLine(x-5,y  ,x+6,y);
    jg.drawLine(x-5,y-1,x+6,y-1);
    jg.drawLine(x-5,y-2,x+6,y-2);
    jg.drawLine(x-5,y-3,x+6,y-3);
    jg.drawLine(x-4,y-4,x+5,y-4);
    jg.drawLine(x-4,y-5,x+5,y-5);
    jg.drawLine(x-2,y-6,x+3,y-6);
  }
}
function canvasDrawStarObject(x,y,d,name,object)
{ jg.setColor(starColor);
  if(d<=1)
  { //jg.setColor(starColor2);
    jg.drawLine(x,y,x,y);
    d=1;
  }
  else if(d==2)
  { //jg.setColor(starColor);
    //jg.drawLine(x,y,x,y);
    jg.drawLine(x,y+1,x+1,y+1);
    jg.drawLine(x,y  ,x+1,y);
  } 
  else if(d==3)
  { //jg.setColor(starColor2);
	jg.drawLine(x-1,y+1,x-1,y+1);
	jg.drawLine(x+1,y+1,x+1,y+1);
	jg.setColor(starColor);
	jg.drawLine(x,y+1,x,y+1);
    jg.drawLine(x-1,y  ,x+1,y);
    jg.drawLine(x-1,y-1,x+1,y-1);
    //jg.setColor(starColor2);
	jg.drawLine(x-1,y-1,x-1,y-1);
    jg.drawLine(x+1,y-1,x+1,y-1);
  }
  else if(d==4)
  { jg.drawLine(x  ,y+1,x+1,y+1);
    jg.drawLine(x-1,y-1,x+2,y-1);
    jg.drawLine(x  ,y-2,x+1,y-2);
    jg.drawLine(x-1,y  ,x+2,y);
  }
  else if(d==5)
  { jg.drawLine(x-1,y-2,x+1,y-2);
    jg.drawLine(x-2,y-1,x+2,y-1);
    jg.drawLine(x-2,y+1,x+2,y+1);
    jg.drawLine(x-1,y+2,x+1,y+2);
    jg.drawLine(x-2,y  ,x+2,y);
  }
  else if(d==6)
  { jg.drawLine(x-1,y+2,x+2,y+2);
    jg.drawLine(x-2,y+1,x+3,y+1);
    jg.drawLine(x-2,y-1,x+3,y-1);
    jg.drawLine(x-2,y-2,x+3,y-2);
    jg.drawLine(x-1,y-3,x+2,y-3);
    jg.drawLine(x-2,y  ,x+3,y);
  }
  else if(d==7)
  { jg.drawLine(x-1,y-3,x+1,y-3);
    jg.drawLine(x-2,y-2,x+2,y-2);
    jg.drawLine(x-3,y-1,x+3,y-1);
    jg.drawLine(x-3,y+1,x+3,y+1);
    jg.drawLine(x-2,y+2,x+2,y+2);
    jg.drawLine(x-1,y+3,x+1,y+3);
    jg.drawLine(x-3,y  ,x+3,y);
  }
  else if(d==8)
  { jg.drawLine(x-1,y+3,x+2,y+3);
    jg.drawLine(x-2,y+2,x+3,y+2);
    jg.drawLine(x-3,y+1,x+4,y+1);
    jg.drawLine(x-3,y-1,x+4,y-1);
    jg.drawLine(x-3,y-2,x+4,y-2);
    jg.drawLine(x-2,y-3,x+3,y-3);
    jg.drawLine(x-1,y-4,x+2,y-4);
    jg.drawLine(x-3,y  ,x+4,y);
  }
  else if(d==9)
  { jg.drawLine(x-1,y-4,x+1,y-4);
    jg.drawLine(x-3,y-3,x+3,y-3);
    jg.drawLine(x-3,y-2,x+3,y-2);
    jg.drawLine(x-4,y-1,x+4,y-1);
    jg.drawLine(x-4,y+1,x+4,y+1);
    jg.drawLine(x-3,y+2,x+3,y+2);
    jg.drawLine(x-3,y+3,x+3,y+3);
    jg.drawLine(x-1,y+4,x+1,y+4);
    jg.drawLine(x-4,y  ,x+4,y);
  }
  else if(d==10)
  { jg.drawLine(x-1,y+4,x+2,y+4);
    jg.drawLine(x-3,y+3,x+4,y+3);
    jg.drawLine(x-3,y+2,x+4,y+2);
    jg.drawLine(x-4,y+1,x+5,y+1);
    jg.drawLine(x-4,y-1,x+5,y-1);
    jg.drawLine(x-4,y-2,x+5,y-2);
    jg.drawLine(x-3,y-3,x+4,y-3);
    jg.drawLine(x-3,y-4,x+4,y-4);
    jg.drawLine(x-1,y-5,x+2,y-5);
    jg.drawLine(x-4,y  ,x+5,y);
  }
  else if(d==11)
  { jg.drawLine(x-2,y-5,x+2,y-5);
    jg.drawLine(x-3,y-4,x+3,y-4);
    jg.drawLine(x-4,y-3,x+4,y-3);
    jg.drawLine(x-5,y-2,x+5,y-2);
    jg.drawLine(x-5,y-1,x+5,y-1);
    jg.drawLine(x-5,y+1,x+5,y+1);
    jg.drawLine(x-5,y+2,x+5,y+2);
    jg.drawLine(x-4,y+3,x+4,y+3);
    jg.drawLine(x-3,y+4,x+3,y+4);
    jg.drawLine(x-2,y+5,x+2,y+5);
    jg.drawLine(x-5,y  ,x+5,y);
  }
  else if(d>11)
  { jg.drawLine(x-2,y+5,x+3,y+5);
    jg.drawLine(x-4,y+4,x+5,y+4);
    jg.drawLine(x-4,y+3,x+5,y+3);
    jg.drawLine(x-5,y+2,x+6,y+2);
    jg.drawLine(x-5,y+1,x+6,y+1);
    jg.drawLine(x-5,y-1,x+6,y-1);
    jg.drawLine(x-5,y-2,x+6,y-2);
    jg.drawLine(x-5,y-3,x+6,y-3);
    jg.drawLine(x-4,y-4,x+5,y-4);
    jg.drawLine(x-4,y-5,x+5,y-5);
    jg.drawLine(x-2,y-6,x+3,y-6);
    jg.drawLine(x-5,y  ,x+6,y);
    d=12;
  }
  if(name=="&nbsp;&nbsp;")
	jg.drawStringObject(name, x-(((html_entity_decode(name).length)*fontSize1b)>>1), y-(fontSize1a>>1), object, "");
  else
    if(((x+4+((d+1)>>1))>this.lx)&&((x+4+((d+1)>>1)+((html_entity_decode(name).length)*fontSize1b))<this.rx)&&((y-(fontSize1a>>1))<this.ty)&&((y+(fontSize1a>>1))>this.by))
	  jg.drawStringObject(name, (x+4+((d+1)>>1)), y-(fontSize1a>>1), object, "");			

}
function canvasDrawString(theString,Apx,Bpx)
{ return jg.drawString(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function canvasDrawStringRect(theString,Apx,Bpx,w,h,theAlignment)
{ return jg.drawStringRect(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-h,w,theAlignment);
}
function canvasDrawStringNamedRect(theString,Apx,Bpx,w,h,theAlignment,thename)
{ return jg.drawStringNamedRect(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-h,w,theAlignment,thename);
}
function canvasreDrawStringNamedRect(theString,Apx,Bpx,w,h,theAlignment,thename)
{ return jg.redrawStringNamedRect(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-h,w,theAlignment,thename);
}
function canvasFillRect(Apx,Bpx,Widthpx,Heightpx)
{ return jg.fillRect(canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-Heightpx,Widthpx,Heightpx);
}
function canvasInit(canvas)
{ this.canvasDimensionXpx=((this.div5Width-this.divOffsetXpx-this.divOffsetX2px)-(this.canvasOffsetXpx<<1)-2);
  this.canvasDimensionYpx=((this.div5Height-this.divOffsetYpx-this.divOffsetY2px)-(this.canvasOffsetYpx<<1)-2);
  cnv=document.getElementById(canvas);
  if(!(this.cnv)) return false;
  this.jg=new jsGraphics(cnv);
  return 1;
}
function canvasInitFS(canvas)
{ this.canvasDimensionXpx=((this.theClientWidth-this.divOffsetXpx-this.divOffsetX2px)-(this.canvasOffsetXpx<<1)-2);
  this.canvasDimensionYpx=((this.theClientHeight-this.divOffsetYpx-this.divOffsetY2px)-(this.canvasOffsetYpx<<1)-2);
  this.cnv=document.getElementById(canvas);
  if(!(this.cnv)) return false;
    this.jg=new jsGraphics(cnv);
  return 1;
}

function canvasRedraw()
{ jg.clear();
  jg.setColor(canvasBkGroundColor);
  jg.fillRect(canvasOffsetXpx,canvasOffsetYpx,canvasDimensionXpx,canvasDimensionYpx);
  jg.drawRect(canvasOffsetXpx,canvasOffsetYpx,canvasDimensionXpx,canvasDimensionYpx);
  return 1;
}

//div functions ===========================================================================================================
function wheel(event)
{ var delta=0;
  if(!event) event=window.event;
  delta=-event.detail;
  if(!(delta)) delta=event.wheelDelta;
  if(delta)
  { //if(window.opera) delta=-delta;
	  getStarsLimit=7;
	  getObjectsLimit=7;
	  if(delta)
      if(delta>0)
      { gridZoom(1);
        gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
        getStarsLimit=7;
        getObjectsLimit=7;
        astroObjectsArr=new Array();
        atlasRedraw();
      }
      else
      { gridZoom(-1);
        gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
        getStarsLimit=7;
        getObjectsLimit=7;
        astroObjectsArr=new Array();
        atlasRedraw();
      }
  }
}
function divInit(theDiv)
{ document.getElementById(theDiv).style.left  =this.divOffsetXpx+'px';
  document.getElementById(theDiv).style.top   =this.divOffsetYpx+'px';
  document.getElementById(theDiv).style.width =(this.div5Width-this.divOffsetXpx-this.divOffsetX2px)+'px';
  document.getElementById(theDiv).style.height=(this.div5Height-this.divOffsetYpx-this.divOffsetY2px)+'px';
  if(window.addEventListener)
  { window.addEventListener('DOMMouseScroll',wheel,false);
    window.addEventListener('mousewheel',wheel,false);
  }
  else
    document.getElementById(theDiv).attachEvent('onmousewheel',wheel);
}

//grid functions =========================================================================================================
function gridClearBorder()
{ gridBorder=false;
}
function gridDiam1SecToPxMin(Diam1Sec)
{ return Math.max(Math.round(diam1SecToPxCt*Diam1Sec),minObjectSize);
}
function gridDiam2SecToPxMin(Diam2Sec)
{ return Math.max(Math.round(diam2SecToPxCt*Diam2Sec),minObjectSize);
}
function gridDrawBorder()
{ if(gridBorder)
  { jg.setColor(gridBorderColor);
    canvasDrawLine(gridOffsetXpx,gridOffsetYpx,canvasDimensionXpx-gridOffsetXpx,gridOffsetYpx);
    canvasDrawLine(canvasDimensionXpx-gridOffsetXpx,gridOffsetYpx,canvasDimensionXpx-gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx);
    canvasDrawLine(canvasDimensionXpx-gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx+1,gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx+1);
    canvasDrawLine(gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx,gridOffsetXpx,gridOffsetYpx);
  }
}
function gridDrawCoordLines()
{ jg.setFont("Lucida Console", fontSize1a+"px", Font.PLAIN);
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx);
  luLrad=gridLxRad;
  gridluLhr=luLrad*f12OverPi;
  luDrad=gridDyRad;
  gridluDdeg=luDrad*f180OverPi;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx);
  ruLrad=gridLxRad;
  gridruLhr=ruLrad*f12OverPi;
  ruDrad=gridDyRad;
  gridruDdeg=ruDrad*f180OverPi;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  ldLrad=gridLxRad;
  gridldLhr=ldLrad*f12OverPi;
  ldDrad=gridDyRad;
  gridldDdeg=ldDrad*f180OverPi;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  rdLrad=gridLxRad;
  gridrdLhr=rdLrad*f12OverPi;
  rdDrad=gridDyRad;
  gridrdDdeg=rdDrad*f180OverPi;
  
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+((gridWidthXpx+1)>>1),div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx);
  griduDdeg=gridDyRad*f180OverPi;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  griddDdeg=gridDyRad*f180OverPi;

  if(((gridD0rad+gridSpanDrad)<(fPiOver2))&&((gridD0rad-gridSpanDrad)>-(fPiOver2)))
  { if(gridD0rad>0)
    { Lrad=luLrad;
      Rrad=ruLrad;
    }
    else
    { Lrad=ldLrad;
      Rrad=rdLrad;
    }
    if(Lrad<Rrad)
      Rrad-=(f2Pi);
    Urad=Math.max(gridD0rad+gridSpanDrad,Math.max(luDrad,ruDrad));
    Drad=Math.min(gridD0rad-gridSpanDrad,Math.min(ldDrad,rdDrad));
    Lhr=Lrad*f12OverPi;
    RhrNeg=Rrad*f12OverPi;
    Udeg=Urad*f180OverPi;
    Ddeg=Drad*f180OverPi;
  }
  else if((gridD0rad+gridSpanDrad)>=(fPiOver2))
  { Lhr=24;
    RhrNeg=0;
    Udeg=90;
    Ddeg=Math.min(gridD0rad-gridSpanDrad,Math.min(ldDrad,rdDrad))*f180OverPi;
    griduDdeg=90;
  }
  else if((gridD0rad-gridSpanDrad)<=-(fPiOver2))
  { Lhr=24;
    RhrNeg=0;
    Udeg=Math.max(gridD0rad+gridSpanDrad,Math.max(luDrad,ruDrad))*f180OverPi;
    Ddeg=-90;
    griddDdeg=-90;
  }

  // alert(griduDdeg+' '+Urad);
  // alert(griddDdeg+' '+Drad);
  griduDdeg=Udeg;
  griddDdeg=Ddeg;
  
  
  DLhr=(Lhr-RhrNeg);
  LStep=Math.min(Math.round((gridDimensions[gridActualDimension][2]/Math.cos(gridD0rad))*60)/60,2);
  DDdeg=(Udeg-Ddeg);
  DStep=gridDimensions[gridActualDimension][1];
  
  LhrStart=(Math.floor(Lhr/LStep)+1)*LStep;
  DdegStart=(Math.floor(Ddeg/DStep)+1)*DStep;

  gridlLhr=Lhr;
  gridrLhr=(RhrNeg<0?(RhrNeg+24):RhrNeg);

  for(d=DdegStart;d<=Udeg;d+=DStep)
  { d=Math.round(d*60)/60;
    canvasX2px=0;
    jg.setColor(coordLineColor);
    for(l=Lhr;l>RhrNeg;l-=LStep/Lsteps)
      gridDrawLineLD(l,d,(l-(LStep/Lsteps)),d);
    if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx))
      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
    else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
    else if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,gridOffsetYpx-8,60,15,'center');
    else if(canvasX2px)
      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,canvasY2px-17,60,15,'center');
  }
  if(gridD0rad<0)
  {  for(l=LhrStart;l>RhrNeg;l-=LStep)
    { l=Math.round(l*60)/60;
      canvasX2px=0;
      jg.setColor(coordLineColor);
      for(d=Ddeg;d<Udeg;d+=DStep/Dsteps)
        gridDrawLineLD(l,d,l,(d+(DStep/Dsteps)));
      if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx-fontSize1a-2,60,15,'center');
      else if(canvasX2px&&(canvasX2px<=gridOffsetXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx-62,canvasY2px-8,60,15,'right');
      else if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
      else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx+gridHeightYpx,60,15,'center');
    }
  }
  else
  { for(l=LhrStart;l>RhrNeg;l-=LStep)
    {  l=Math.round(l*60)/60;
      canvasX2px=0;
      jg.setColor(coordLineColor);
      for(d=Udeg;d>Ddeg;d-=DStep/Dsteps)
        gridDrawLineLD(l,d,l,(d-(DStep/Dsteps)));
      if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx-10-fontSize1a,60,15,'center');
      else if(canvasX2px&&(canvasX2px<=gridOffsetXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx-62,canvasY2px-8,60,15,'right');
      else if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
      else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
    }
  }
}
function gridDrawEllipseTilt(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg,nameText,seen)
{ AngleDeg=((((AngleDeg*1.0)+90)%180)*0.01745);
  gridLDrad(Lhr,Ddeg); 
  x1=gridLxRad; y1=gridDyRad;
  canvasDrawEllipseTilt(gridCenterOffsetXpx+gridXpx(x1),gridCenterOffsetYpx+gridYpx(y1),Math.round((gridWidthXpx2*(Diam1Sec/3600)/gridSpanL)),Math.round((gridHeightYpx2*(Diam2Sec/3600)/gridSpanD)),AngleDeg,nameText,seen);
}
function gridDrawEllipseTiltObject(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg,nameText,seen,object)
{ AngleDeg=((((AngleDeg*1.0)+90)%180)*0.01745);
  gridLDrad(Lhr,Ddeg); 
  x1=gridLxRad; y1=gridDyRad;
  canvasDrawEllipseTiltObject(gridCenterOffsetXpx+gridXpx(x1),gridCenterOffsetYpx+gridYpx(y1),Math.round((gridWidthXpx2*(Diam1Sec/3600)/gridSpanL)),Math.round((gridHeightYpx2*(Diam2Sec/3600)/gridSpanD)),AngleDeg,nameText,seen,object);
}
function gridDrawGCObject(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg,nameText,seen,object)
{ gridLDrad(Lhr,Ddeg); 
  var x=gridCenterOffsetXpx+gridXpx(gridLxRad);
  var y=gridCenterOffsetYpx+gridYpx(gridDyRad);
  var d1=gridDiam1SecToPxMin(Diam1Sec);
  var d2=gridDiam2SecToPxMin(Diam2Sec);
  canvasDrawEllipseTiltObject(x,y,d1,d2,AngleDeg,nameText,seen,object);
  gridDrawLinex1y1x2y2Px(x-(d1>>1), y, x+(d1>>1), y);
  gridDrawLinex1y1x2y2Px(x, y-(d2>>1), x, y+(d2>>1));
}
function gridDrawGXCLObject(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg,nameText,seen,object)
{ gridLDrad(Lhr,Ddeg); 
  var x=gridCenterOffsetXpx+gridXpx(gridLxRad);
  var y=gridCenterOffsetYpx+gridYpx(gridDyRad);
  var d1=gridDiam1SecToPxMin(Math.max(Diam1Sec,600));
  var d2=d1;

  var cancel = false;
  var x1=0, x2=0, y1=0, y2=0;
  
  x1=x;
  y1=y+((d2+1)>>1);
  x2=x+((d1+1)>>1);
  y2=y+((d2+1)>>3);
  if((x1<gridOffsetXpx)&&(x2<gridOffsetXpx)) cancel=true;
  if((x1>gridOffsetXpx+gridWidthXpx)&&(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if((y1<gridOffsetYpx)&&(y2<gridOffsetYpx)) cancel=true;
  if((y1>gridOffsetYpx+gridHeightYpx)&&(y2>gridOffsetYpx+gridHeightYpx)) cancel=true;
  if(x1<gridOffsetXpx) if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x1=gridOffsetXpx;}
  if(x1>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridOffsetXpx+gridWidthXpx; }
  if(y1>gridOffsetYpx+gridHeightYpx) if(y2==y1) cancel=true; else  {x1=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridOffsetYpx+gridHeightYpx; }
  if(y1<gridOffsetYpx) if(y2==y1) cancel=true; else {x1=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y1=gridOffsetYpx;}
  if((y1<gridOffsetYpx)||(y1>gridOffsetYpx+gridHeightYpx)||(x1<gridOffsetXpx)||(x1>gridOffsetXpx+gridWidthXpx)) cancel=true;  
  if(x2<gridOffsetXpx) if(x2==x1) cancel=true; else {y2=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x2=gridOffsetXpx;}
  if(x2>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else  {y2=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridOffsetXpx+gridWidthXpx;  }
  if(y2>gridOffsetYpx+gridHeightYpx)  if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridOffsetYpx+gridHeightYpx;  }
  if(y2<gridOffsetYpx) if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y2=gridOffsetYpx;}
  if((y2<gridOffsetYpx)||(y2>gridOffsetYpx+gridHeightYpx)||(x2<gridOffsetXpx)||(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if(!cancel)
    canvasDrawLineObject(x1,y1,x2,y2, nameText,seen,object);
  cancel=false;  
  x1=x+((d1+1)>>1);
  y1=y+((d2+1)>>3);
  x2=x+(3*((d1+1)>>3));
  y2=y-((d2+1)>>1);
  if((x1<gridOffsetXpx)&&(x2<gridOffsetXpx)) cancel=true;
  if((x1>gridOffsetXpx+gridWidthXpx)&&(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if((y1<gridOffsetYpx)&&(y2<gridOffsetYpx)) cancel=true;
  if((y1>gridOffsetYpx+gridHeightYpx)&&(y2>gridOffsetYpx+gridHeightYpx)) cancel=true;
  if(x1<gridOffsetXpx) if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x1=gridOffsetXpx;}
  if(x1>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridOffsetXpx+gridWidthXpx; }
  if(y1>gridOffsetYpx+gridHeightYpx) if(y2==y1) cancel=true; else  {x1=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridOffsetYpx+gridHeightYpx; }
  if(y1<gridOffsetYpx) if(y2==y1) cancel=true; else {x1=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y1=gridOffsetYpx;}
  if((y1<gridOffsetYpx)||(y1>gridOffsetYpx+gridHeightYpx)||(x1<gridOffsetXpx)||(x1>gridOffsetXpx+gridWidthXpx)) cancel=true;  
  if(x2<gridOffsetXpx) if(x2==x1) cancel=true; else {y2=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x2=gridOffsetXpx;}
  if(x2>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else  {y2=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridOffsetXpx+gridWidthXpx;  }
  if(y2>gridOffsetYpx+gridHeightYpx)  if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridOffsetYpx+gridHeightYpx;  }
  if(y2<gridOffsetYpx) if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y2=gridOffsetYpx;}
  if((y2<gridOffsetYpx)||(y2>gridOffsetYpx+gridHeightYpx)||(x2<gridOffsetXpx)||(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if(!cancel)
    canvasDrawLine(x1,y1,x2,y2);
  cancel=false;    
  x1=x+(3*((d1+1)>>3));
  y1=y-((d2+1)>>1);
  x2=x-(3*((d1+1)>>3));
  y2=y-((d2+1)>>1);
  if((x1<gridOffsetXpx)&&(x2<gridOffsetXpx)) cancel=true;
  if((x1>gridOffsetXpx+gridWidthXpx)&&(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if((y1<gridOffsetYpx)&&(y2<gridOffsetYpx)) cancel=true;
  if((y1>gridOffsetYpx+gridHeightYpx)&&(y2>gridOffsetYpx+gridHeightYpx)) cancel=true;
  if(x1<gridOffsetXpx) if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x1=gridOffsetXpx;}
  if(x1>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridOffsetXpx+gridWidthXpx; }
  if(y1>gridOffsetYpx+gridHeightYpx) if(y2==y1) cancel=true; else  {x1=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridOffsetYpx+gridHeightYpx; }
  if(y1<gridOffsetYpx) if(y2==y1) cancel=true; else {x1=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y1=gridOffsetYpx;}
  if((y1<gridOffsetYpx)||(y1>gridOffsetYpx+gridHeightYpx)||(x1<gridOffsetXpx)||(x1>gridOffsetXpx+gridWidthXpx)) cancel=true;  
  if(x2<gridOffsetXpx) if(x2==x1) cancel=true; else {y2=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x2=gridOffsetXpx;}
  if(x2>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else  {y2=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridOffsetXpx+gridWidthXpx;  }
  if(y2>gridOffsetYpx+gridHeightYpx)  if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridOffsetYpx+gridHeightYpx;  }
  if(y2<gridOffsetYpx) if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y2=gridOffsetYpx;}
  if((y2<gridOffsetYpx)||(y2>gridOffsetYpx+gridHeightYpx)||(x2<gridOffsetXpx)||(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if(!cancel)
    canvasDrawLine(x1,y1,x2,y2);
  cancel=false;  
  x1=x-(3*((d1+1)>>3));
  y1=y-((d2+1)>>1);
  x2=x-((d1+1)>>1);
  y2=y+((d2+1)>>3);
  if((x1<gridOffsetXpx)&&(x2<gridOffsetXpx)) cancel=true;
  if((x1>gridOffsetXpx+gridWidthXpx)&&(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if((y1<gridOffsetYpx)&&(y2<gridOffsetYpx)) cancel=true;
  if((y1>gridOffsetYpx+gridHeightYpx)&&(y2>gridOffsetYpx+gridHeightYpx)) cancel=true;
  if(x1<gridOffsetXpx) if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x1=gridOffsetXpx;}
  if(x1>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridOffsetXpx+gridWidthXpx; }
  if(y1>gridOffsetYpx+gridHeightYpx) if(y2==y1) cancel=true; else  {x1=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridOffsetYpx+gridHeightYpx; }
  if(y1<gridOffsetYpx) if(y2==y1) cancel=true; else {x1=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y1=gridOffsetYpx;}
  if((y1<gridOffsetYpx)||(y1>gridOffsetYpx+gridHeightYpx)||(x1<gridOffsetXpx)||(x1>gridOffsetXpx+gridWidthXpx)) cancel=true;  
  if(x2<gridOffsetXpx) if(x2==x1) cancel=true; else {y2=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x2=gridOffsetXpx;}
  if(x2>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else  {y2=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridOffsetXpx+gridWidthXpx;  }
  if(y2>gridOffsetYpx+gridHeightYpx)  if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridOffsetYpx+gridHeightYpx;  }
  if(y2<gridOffsetYpx) if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y2=gridOffsetYpx;}
  if((y2<gridOffsetYpx)||(y2>gridOffsetYpx+gridHeightYpx)||(x2<gridOffsetXpx)||(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if(!cancel)
    canvasDrawLine(x1,y1,x2,y2);
  cancel=false;  
  x1=x-((d1+1)>>1);
  y1=y+((d2+1)>>3);
  x2=x;
  y2=y+((d2+1)>>1);
  if((x1<gridOffsetXpx)&&(x2<gridOffsetXpx)) cancel=true;
  if((x1>gridOffsetXpx+gridWidthXpx)&&(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if((y1<gridOffsetYpx)&&(y2<gridOffsetYpx)) cancel=true;
  if((y1>gridOffsetYpx+gridHeightYpx)&&(y2>gridOffsetYpx+gridHeightYpx)) cancel=true;
  if(x1<gridOffsetXpx) if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x1=gridOffsetXpx;}
  if(x1>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridOffsetXpx+gridWidthXpx; }
  if(y1>gridOffsetYpx+gridHeightYpx) if(y2==y1) cancel=true; else  {x1=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridOffsetYpx+gridHeightYpx; }
  if(y1<gridOffsetYpx) if(y2==y1) cancel=true; else {x1=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y1=gridOffsetYpx;}
  if((y1<gridOffsetYpx)||(y1>gridOffsetYpx+gridHeightYpx)||(x1<gridOffsetXpx)||(x1>gridOffsetXpx+gridWidthXpx)) cancel=true;  
  if(x2<gridOffsetXpx) if(x2==x1) cancel=true; else {y2=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x2=gridOffsetXpx;}
  if(x2>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else  {y2=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridOffsetXpx+gridWidthXpx;  }
  if(y2>gridOffsetYpx+gridHeightYpx)  if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridOffsetYpx+gridHeightYpx;  }
  if(y2<gridOffsetYpx) if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y2=gridOffsetYpx;}
  if((y2<gridOffsetYpx)||(y2>gridOffsetYpx+gridHeightYpx)||(x2<gridOffsetXpx)||(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if(!cancel)
    canvasDrawLine(x1,y1,x2,y2);
}
function gridDrawEllipseTiltObjectInterval(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg,nameText,seen,object,interval)
{ AngleDeg=((((AngleDeg*1.0)+90)%180)*0.01745);
  gridLDrad(Lhr,Ddeg); 
  x1=gridLxRad; y1=gridDyRad;
  canvasDrawEllipseTiltObjectInterval(gridCenterOffsetXpx+gridXpx(x1),gridCenterOffsetYpx+gridYpx(y1),gridDiam1SecToPxMin(Diam1Sec),gridDiam2SecToPxMin(Diam2Sec),AngleDeg,nameText,seen,object,interval);
}
function gridDrawFilledCirclePx(Lhr,Ddeg,DiamPx)
{ gridLDrad(Lhr,Ddeg); 
  cx=gridCenterOffsetXpx+gridXpx(gridLxRad);
  cy=gridCenterOffsetYpx+gridYpx(gridDyRad);
  if((cx-DiamPx<gridOffsetXpx)||(cx+DiamPx>gridOffsetXpx+gridWidthXpx)) return false;
  if((cy+DiamPx>gridOffsetYpx+gridHeightYpx)||(cy-DiamPx<gridOffsetYpx)) return false;
  canvasDrawFilledCircle(cx,cy,DiamPx,DiamPx);
  return true;
}
function gridDrawFilledCirclePxObject(Lhr,Ddeg,DiamPx,name,object)
{ gridLDrad(Lhr,Ddeg); 
  cx=gridCenterOffsetXpx+gridXpx(gridLxRad);
  cy=gridCenterOffsetYpx+gridYpx(gridDyRad);
  if((cx-DiamPx<gridOffsetXpx)||(cx+DiamPx>gridOffsetXpx+gridWidthXpx)) return false;
  if((cy+DiamPx>gridOffsetYpx+gridHeightYpx)||(cy-DiamPx<gridOffsetYpx)) return false;
  canvasDrawFilledCircleObject(cx,cy,DiamPx,name,object);
  return true;
}
function gridDrawLinePxLR(Lhr,Ddeg,d)
{ gridLDrad(Lhr,Ddeg); 
  cx=gridCenterOffsetXpx+gridXpx(gridLxRad);
  cy=gridCenterOffsetYpx+gridYpx(gridDyRad);
  if((cx-d<gridOffsetXpx)||(cx+d>gridOffsetXpx+gridWidthXpx)) return;
  if((cy>gridOffsetYpx+gridHeightYpx)||(cy<gridOffsetYpx)) return;
  canvasDrawLine(cx-d,cy,cx+d,cy);
}
function gridDrawLinex1y1x2y2Px(x1,y1,x2,y2,nameText,seen,object)
{ var cancel = false;
  if((x1<gridOffsetXpx)&&(x2<gridOffsetXpx)) cancel=true;
  if((x1>gridOffsetXpx+gridWidthXpx)&&(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if((y1<gridOffsetYpx)&&(y2<gridOffsetYpx)) cancel=true;
  if((y1>gridOffsetYpx+gridHeightYpx)&&(y2>gridOffsetYpx+gridHeightYpx)) cancel=true;
  if(x1<gridOffsetXpx) if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x1=gridOffsetXpx;}
  if(x1>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else {y1=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridOffsetXpx+gridWidthXpx; }
  if(y1>gridOffsetYpx+gridHeightYpx) if(y2==y1) cancel=true; else  {x1=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridOffsetYpx+gridHeightYpx; }
  if(y1<gridOffsetYpx) if(y2==y1) cancel=true; else {x1=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y1=gridOffsetYpx;}
  if((y1<gridOffsetYpx)||(y1>gridOffsetYpx+gridHeightYpx)||(x1<gridOffsetXpx)||(x1>gridOffsetXpx+gridWidthXpx)) cancel=true;  
  if(x2<gridOffsetXpx) if(x2==x1) cancel=true; else {y2=(((gridOffsetXpx-x1)/(x2-x1))*(y2-y1))+y1; x2=gridOffsetXpx;}
  if(x2>gridOffsetXpx+gridWidthXpx)  if(x2==x1) cancel=true; else  {y2=(((gridOffsetXpx+gridWidthXpx-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridOffsetXpx+gridWidthXpx;  }
  if(y2>gridOffsetYpx+gridHeightYpx)  if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx+gridHeightYpx-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridOffsetYpx+gridHeightYpx;  }
  if(y2<gridOffsetYpx) if(y2==y1) cancel=true; else  {x2=(((gridOffsetYpx-y1)/(y2-y1))*(x2-x1))+x1; y2=gridOffsetYpx;}
  if((y2<gridOffsetYpx)||(y2>gridOffsetYpx+gridHeightYpx)||(x2<gridOffsetXpx)||(x2>gridOffsetXpx+gridWidthXpx)) cancel=true;
  if(!cancel)
    if(nameText)
      canvasDrawLineObject(x1,y1,x2,y2,nameText,seen,object);
    else
      canvasDrawLine(x1,y1,x2,y2);
}
function gridDrawLineLD(Lhr1,Ddeg1,Lhr2,Ddeg2)
{ gridLDrad(Lhr1,Ddeg1); x1=gridLxRad; y1=gridDyRad;
  gridLDrad(Lhr2,Ddeg2); x2=gridLxRad; y2=gridDyRad;
  if((x1<-gridSpanLrad)&&(x2<-gridSpanLrad)) return 0;
  if((x1>gridSpanLrad)&&(x2>gridSpanLrad))   return 0;
  if((y1<-gridSpanDrad)&&(y2<-gridSpanDrad)) return 0;
  if((y1>gridSpanDrad)&&(y2>gridSpanDrad))   return 0;
  if(x1<-gridSpanLrad) if(x2==x1) return 0; else {y1=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x1=-gridSpanLrad;}
  if(x1>gridSpanLrad)  if(x2==x1) return 0; else  {y1=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridSpanLrad; }
  if(y1>gridSpanDrad)  if(y2==y1) return 0; else  {x1=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridSpanDrad; }
  if(y1<-gridSpanDrad) if(y2==y1) return 0; else {x1=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y1=-gridSpanDrad;}
  if((y1<-gridSpanDrad)||(y1>gridSpanDrad)||(x1<-gridSpanLrad)||(x1>gridSpanLrad)) return 0;  
  if(x2<-gridSpanLrad) if(x2==x1) return 0; else {y2=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x2=-gridSpanLrad;}
  if(x2>gridSpanLrad)  if(x2==x1) return 0; else  {y2=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridSpanLrad;  }
  if(y2>gridSpanDrad)  if(y2==y1) return 0; else  {x2=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridSpanDrad;  }
  if(y2<-gridSpanDrad) if(y2==y1) return 0; else  {x2=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y2=-gridSpanDrad;}
  if((y2<-gridSpanDrad)||(y2>gridSpanDrad)||(x2<-gridSpanLrad)||(x2>gridSpanLrad)) return 0;
  
  canvasX1px=gridCenterOffsetXpx+gridXpx(x1);
  canvasY1px=gridCenterOffsetYpx+gridYpx(y1);
  canvasX2px=gridCenterOffsetXpx+gridXpx(x2);
  canvasY2px=gridCenterOffsetYpx+gridYpx(y2);
  gridLx1rad=x1;gridDy1rad=y1;gridLx2rad=x2;gridDy2rad=y2;
  canvasDrawLine(canvasX1px,canvasY1px,canvasX2px,canvasY2px);
  return 1;
}
function gridDrawLongLineLD(Lhr1,Ddeg1,Lhr2,Ddeg2)
{ Lhr1=Lhr1*1.0;
  Ddeg1=1.0*Ddeg1; 
  Lhr2=Lhr2*1.0;
  Ddeg2=1.0*Ddeg2;
  gridLDrad(Lhr1,Ddeg1); x1=gridLxRad; y1=gridDyRad;
  gridLDrad(Lhr2,Ddeg2); x2=gridLxRad; y2=gridDyRad;
  if((Math.abs(x1)>fPiOver2)||(Math.abs(y1)>fPiOver2)||(Math.abs(y1)>fPiOver2)||(Math.abs(y2)>fPiOver2)) return 0;
  if((x1<-gridSpanLrad)&&(x2<-gridSpanLrad)) return 0;
  if((x1>gridSpanLrad)&&(x2>gridSpanLrad))   return 0;
  if((y1<-gridSpanDrad)&&(y2<-gridSpanDrad)) return 0;
  if((y1>gridSpanDrad)&&(y2>gridSpanDrad))   return 0;
/*  if(x1<-gridSpanLrad) if(x2==x1) return 0; else {y1=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x1=-gridSpanLrad;}
  if(x1>gridSpanLrad)  if(x2==x1) return 0; else {y1=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridSpanLrad; }
  if(y1>gridSpanDrad)  if(y2==y1) return 0; else {x1=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridSpanDrad; }
  if(y1<-gridSpanDrad) if(y2==y1) return 0; else {x1=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y1=-gridSpanDrad;}
  if(x2<-gridSpanLrad) if(x2==x1) return 0; else {y2=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x2=-gridSpanLrad;}
  if(x2>gridSpanLrad)  if(x2==x1) return 0; else {y2=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridSpanLrad;  }
  if(y2>gridSpanDrad)  if(y2==y1) return 0; else {x2=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridSpanDrad;  }
  if(y2<-gridSpanDrad) if(y2==y1) return 0; else {x2=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y2=-gridSpanDrad;}
*/
  if(Math.abs(Lhr1-Lhr2)>12)
  { if(Lhr1>Lhr2)
	  return Math.max(gridDrawLongLineLD(Lhr1,Ddeg1,24,((1.0*Ddeg1)+((Ddeg2-Ddeg1)*(24-Lhr1)/(Lhr2+24-Lhr1)))),gridDrawLongLineLD(Lhr2,Ddeg2,0,((1.0*Ddeg2)-((Ddeg2-Ddeg1)*(Lhr2)/(Lhr2+24-Lhr1)))));
	else
	  return Math.max(gridDrawLongLineLD(Lhr2,Ddeg2,24,((1.0*Ddeg2)+((Ddeg1-Ddeg2)*(24-Lhr2)/(Lhr1+24-Lhr2)))),gridDrawLongLineLD(Lhr1,Ddeg1,0,((1.0*Ddeg1)-((Ddeg1-Ddeg2)*(Lhr1)/(Lhr1+24-Lhr2)))));
  }
  //alert(y1+' '+y2+' '+(-gridSpanDrad)+' '+(gridSpanDrad)+' '+x1+' '+x2+' '+(-gridSpanLrad)+' '+(gridSpanLrad));
  var retval = 0;
  var ds = 10.0+Math.max(Math.ceil(1000*Math.abs(x1-x2) / gridSpanLrad * fPiOver12),Math.ceil(1000*Math.abs(y1-y2) / gridSpanDrad * fPiOver180));
  for(var i=0.0;i<ds;)
  { gridLDrad((1.0*Lhr1)+((Lhr2-Lhr1)*i/ds),(1.0*Ddeg1)+((Ddeg2-Ddeg1)*i/ds)); x1=gridLxRad; y1=gridDyRad;
	i++;
	gridLDrad((1.0*Lhr1)+((Lhr2-Lhr1)*i/ds),(1.0*Ddeg1)+((Ddeg2-Ddeg1)*i/ds)); x2=gridLxRad; y2=gridDyRad;
    if((x1<-gridSpanLrad)&&(x2<-gridSpanLrad)) continue;
	if((x1>gridSpanLrad)&&(x2>gridSpanLrad))   continue;
	if((y1<-gridSpanDrad)&&(y2<-gridSpanDrad)) continue;
	if((y1>gridSpanDrad)&&(y2>gridSpanDrad))   continue;
	if(x1<-gridSpanLrad) if(x2==x1) continue; else {y1=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x1=-gridSpanLrad;}
	if(x1>gridSpanLrad)  if(x2==x1) continue; else  {y1=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridSpanLrad; }
	if(y1>gridSpanDrad)  if(y2==y1) continue; else  {x1=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridSpanDrad; }
	if(y1<-gridSpanDrad) if(y2==y1) continue; else {x1=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y1=-gridSpanDrad;}
	if((y1<-gridSpanDrad)||(y1>gridSpanDrad)||(x1<-gridSpanLrad)||(x1>gridSpanLrad)) continue;  
	if(x2<-gridSpanLrad) if(x2==x1) continue; else {y2=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x2=-gridSpanLrad;}
	if(x2>gridSpanLrad)  if(x2==x1) continue; else  {y2=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridSpanLrad;  }
	if(y2>gridSpanDrad)  if(y2==y1) continue; else  {x2=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridSpanDrad;  }
	if(y2<-gridSpanDrad) if(y2==y1) continue; else  {x2=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y2=-gridSpanDrad;}
	if((y2<-gridSpanDrad)||(y2>gridSpanDrad)||(x2<-gridSpanLrad)||(x2>gridSpanLrad)) continue;
	canvasX1px=gridCenterOffsetXpx+gridXpx(x1);
	canvasY1px=gridCenterOffsetYpx+gridYpx(y1);
	canvasX2px=gridCenterOffsetXpx+gridXpx(x2);
	canvasY2px=gridCenterOffsetYpx+gridYpx(y2);
	gridLx1rad=x1;gridDy1rad=y1;gridLx2rad=x2;gridDy2rad=y2;
	canvasDrawLine(canvasX1px,canvasY1px,canvasX2px,canvasY2px);
	retval = 1;
  } 
  return retval;
}
function gridDrawPointLD(Lhr,Ddeg)
{ gridLDrad(Lhr,Ddeg);
  if((gridLxRad>-gridSpanLrad)&&(gridLxRad<gridSpanLrad)&&(gridDyRad>-gridSpanDrad)&&(gridDyRad<gridSpanDrad))
    return canvasDrawPoint(gridCenterOffsetXpx+gridXpx(gridLxRad),gridCenterOffsetYpx+gridYpx(gridDyRad));
  else return 0;
}
function gridDrawPNObject(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg,nameText,seen,object)
{ gridLDrad(Lhr,Ddeg); 
  var x=gridCenterOffsetXpx+gridXpx(gridLxRad);
  var y=gridCenterOffsetYpx+gridYpx(gridDyRad);
  var d1=gridDiam1SecToPxMin(Diam1Sec)-2;
  var d2=gridDiam2SecToPxMin(Diam2Sec)-2;
  canvasDrawEllipseTilt(x,y,d1,d2,AngleDeg,nameText,seen);
  gridDrawLinex1y1x2y2Px(x-((d1+1)>>1), y, x-d1, y);
  gridDrawLinex1y1x2y2Px(x, y-d2, x, y-((d2+1)>>1));
  gridDrawLinex1y1x2y2Px(x, y+d2, x, y+((d2+1)>>1));
  gridDrawLinex1y1x2y2Px(x+((d1+1)>>1), y, x+d1, y, nameText,seen,object);
}
function gridDrawQSRObject(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg,nameText,seen,object)
{ gridLDrad(Lhr,Ddeg); 
  var x=gridCenterOffsetXpx+gridXpx(gridLxRad);
  var y=gridCenterOffsetYpx+gridYpx(gridDyRad);
  var d1=gridDiam1SecToPxMin(Diam1Sec)-2;
  var d2=gridDiam2SecToPxMin(Diam2Sec)-2;
  gridDrawLinex1y1x2y2Px(x-2, y, x-d1-2, y);
  gridDrawLinex1y1x2y2Px(x, y-2-d2, x, y-2);
  gridDrawLinex1y1x2y2Px(x, y+2+d2, x, y+2);
  gridDrawLinex1y1x2y2Px(x+2, y, x+2+d1, y, nameText,seen,object);
}

function gridDrawRectangleCWH(Lhr,Ddeg,wSec,hSec,pa,nm,seen)
{ gridLDrad(Lhr,Ddeg);
  canvasDrawRectangleCWH(gridCenterOffsetXpx+gridXpx(gridLxRad),
                         gridCenterOffsetYpx+gridYpx(gridDyRad),
                         Math.round((gridWidthXpx2*(wSec/3600)/gridSpanL)),
                         Math.round((gridHeightYpx2*(hSec/3600)/gridSpanD)),pa,nm,seen);
}
function gridDrawRectangleCWHObject(Lhr,Ddeg,wSec,hSec,pa,nm,seen,object)
{ gridLDrad(Lhr,Ddeg);
  canvasDrawRectangleCWHObject(gridCenterOffsetXpx+gridXpx(gridLxRad),
                         gridCenterOffsetYpx+gridYpx(gridDyRad),
                         Math.round((gridWidthXpx2*(wSec/3600)/gridSpanL)),
                         Math.round((gridHeightYpx2*(hSec/3600)/gridSpanD)),pa,nm,seen,object);
}
function gridDrawRectangleCWHObjectInterval(Lhr,Ddeg,wSec,hSec,pa,nm,seen,object,interval)
{ gridLDrad(Lhr,Ddeg);
  canvasDrawRectangleCWHObjectInterval(gridCenterOffsetXpx+gridXpx(gridLxRad),
                         gridCenterOffsetYpx+gridYpx(gridDyRad),
                         Math.round((gridWidthXpx2*(wSec/3600)/gridSpanL)),
                         Math.round((gridHeightYpx2*(hSec/3600)/gridSpanD)),pa,nm,seen,object,interval);
}
function gridDrawRectangleRectangleCWHObjectInterval(Lhr,Ddeg,wSec,hSec,pa,nm,seen,object,interval)
{ gridLDrad(Lhr,Ddeg);
  canvasDrawRectangleRectangleCWHObjectInterval(gridCenterOffsetXpx+gridXpx(gridLxRad),
                         gridCenterOffsetYpx+gridYpx(gridDyRad),
                         gridDiam1SecToPxMin(wSec),
                         gridDiam2SecToPxMin(hSec),pa,nm,seen,object,interval);
}
function gridDrawStarObject(Lhr,Ddeg,DiamPx,name,object)
{ gridLDrad(Lhr,Ddeg); 
  cx=canvasOffsetXpx+gridCenterOffsetXpx+gridXpx(gridLxRad);
  cy=canvasOffsetYpx+canvasDimensionYpx-gridCenterOffsetYpx-gridYpx(gridDyRad);
  if((cx-DiamPx<this.lx)||(cx+DiamPx>this.rx)) return false;
  if((cy+DiamPx>this.ty)||(cy-DiamPx<this.by)) return false;
  canvasDrawStarObject(cx,cy,DiamPx,name,object);
  return true;
}
function gridInit()
{this.gridWidthXpx=this.canvasDimensionXpx-(this.gridOffsetXpx<<1);
  this.gridWidthXpx2=((this.gridWidthXpx+1)>>1);
  this.gridCenterOffsetXpx=this.gridOffsetXpx+((this.gridWidthXpx+1)>>1);
  this.gridHeightYpx=this.canvasDimensionYpx-(this.gridOffsetYpx<<1);
  this.gridHeightYpx2=((this.gridHeightYpx+1)>>1);
  this.gridCenterOffsetYpx=this.gridOffsetYpx+((this.gridHeightYpx+1)>>1);
  this.lx = this.canvasOffsetXpx+this.gridOffsetXpx;
  this.rx = this.canvasOffsetXpx+this.gridOffsetXpx+this.gridWidthXpx;
  this.ty = this.canvasOffsetYpx+this.gridOffsetYpx+this.gridHeightYpx;
  this.by = this.canvasOffsetYpx+this.gridOffsetYpx;
}
function gridInitScale(gridLHr,gridDdeg,desiredScale)
{ gridActualDimension=gridMaxDimension;
  while((gridActualDimension>gridMinDimension)&&(gridDimensions[gridActualDimension][0]<desiredScale))
    gridActualDimension--;
  gridL0rad=gridLHr*fPiOver12;
  gridD0rad=gridDdeg*fPiOver180;
  if(gridWidthXpx<gridHeightYpx)
  { gridSpanD=gridDimensions[gridActualDimension][0]*(gridHeightYpx/gridWidthXpx);
    gridSpanL=gridDimensions[gridActualDimension][0];
  }
  else
  { gridSpanD=gridDimensions[gridActualDimension][0];
    gridSpanL=gridDimensions[gridActualDimension][0]*(gridWidthXpx/gridHeightYpx);
  }
  gridSpanLrad=gridSpanL*fPiOver180;
  gridSpanDrad=gridSpanD*fPiOver180;
  atlaspagezoomdeg=gridDimensions[gridActualDimension][0];
  atlasmagnitude = gridDimensions[gridActualDimension][3]+atlasmagnitudedelta;
  starsmagnitude = Math.max(gridDimensions[gridActualDimension][3]+starsmagnitudedelta,8);
  diam1SecToPxCt=((gridWidthXpx2/3600)/gridSpanL);
  diam2SecToPxCt=((gridHeightYpx2/3600)/gridSpanD);
}
function gridInitScaleFixed(gridLHr,gridDdeg,desiredScale)
{ gridActualDimension=desiredScale;
  gridL0rad=gridLHr*fPiOver12;
  gridD0rad=gridDdeg*fPiOver180;
  if(gridWidthXpx<gridHeightYpx)
  {  gridSpanD=gridDimensions[gridActualDimension][0]*(gridHeightYpx/gridWidthXpx);
    gridSpanL=gridDimensions[gridActualDimension][0];
  }
  else
  { gridSpanD=gridDimensions[gridActualDimension][0];
    gridSpanL=gridDimensions[gridActualDimension][0]*(gridWidthXpx/gridHeightYpx);
  }
  gridSpanLrad=gridSpanL*fPiOver180;
  gridSpanDrad=gridSpanD*fPiOver180;
  atlaspagezoomdeg=gridDimensions[gridActualDimension][0];
  atlasmagnitude = gridDimensions[gridActualDimension][3]+atlasmagnitudedelta;
  starsmagnitude = gridDimensions[gridActualDimension][3]+starsmagnitudedelta;
  diam1SecToPxCt=((gridWidthXpx2/3600)/gridSpanL);
  diam2SecToPxCt=((gridHeightYpx2/3600)/gridSpanD);
}
function gridLDrad(Lhr,Ddeg)
{ Lrad=Lhr*fPiOver12; Drad=Ddeg*fPiOver180;
  if(Lrad>gridL0rad+Math.PI) Lrad=Lrad-(f2Pi);
  if(Lrad<gridL0rad-Math.PI) Lrad=Lrad+(f2Pi);
  var drad=Math.acos((Math.sin(gridD0rad)*Math.sin(Drad))+(Math.cos(gridD0rad)*Math.cos(Drad)*Math.cos(Lrad-gridL0rad)));
  if(drad>0)
  {  gridLxRad=-(drad*(Math.sin(Lrad-gridL0rad)*Math.cos(Drad)/Math.sin(drad)));
     gridDyRad=(drad*(Math.sin(Drad)-(Math.sin(gridD0rad)*Math.cos(drad)))/(Math.cos(gridD0rad)*Math.sin(drad)));
  }
  else
  { gridLxRad=0;
    gridDyRad=0;
  }
}
function gridLDinvRad(XpxAbsScr,YpxAbsScr)
{ xRad=-((XpxAbsScr-div5Left-divOffsetXpx-canvasOffsetXpx-gridCenterOffsetXpx)/gridWidthXpx2*gridSpanLrad);
  yRad=((gridCenterOffsetYpx+canvasOffsetYpx+divOffsetYpx+div5Top-YpxAbsScr)/gridHeightYpx2*gridSpanDrad);
  drad=Math.sqrt((xRad*xRad)+(yRad*yRad));
  if(drad>0)
  { var sinalpha=xRad/drad;
    var cosalpha=yRad/drad;
    var Dacc=Math.acos((Math.cos(drad)*Math.sin(gridD0rad))+(Math.sin(drad)*Math.cos(gridD0rad)*cosalpha));
    var cosLacc=(Math.cos(drad)-(Math.sin(gridD0rad)*Math.cos(Dacc)))/(Math.cos(gridD0rad)*Math.sin(Dacc));
    if(cosLacc>=0)
      gridLxRad=gridL0rad+(Math.asin(Math.sin(drad)*sinalpha/Math.sin(Dacc)));
    else
      gridLxRad=gridL0rad+Math.PI-(Math.asin(Math.sin(drad)*sinalpha/Math.sin(Dacc)));    
    gridDyRad=((fPiOver2)-Dacc);
  }
  else
  { gridLxRad=gridL0rad;
    gridDyRad=gridD0rad;
  }
  if((gridDyRad)>(fPiOver2))
    gridDyRad=(fPiOver2);
  if((gridDyRad)<(-fPiOver2))
    gridDyRad=(-fPiOver2);
  if((gridLxRad)<0)
    gridLxRad=gridLxRad+(f2Pi);
  if((gridLxRad)>=(f2Pi))
    gridLxRad=gridLxRad-(f2Pi);
}
function gridSetBorderColor(theColor)
{ gridBorder=true;
  gridBorderColor=theColor;
}
function gridShowInfo()
{ jg.setFont("Lucida Console", fontSize1a+"px", Font.PLAIN);
  t1 =atlasPageFoV+' '+(Math.round(gridSpanL*20)/10)+" x "+(Math.round(gridSpanD*20)/10)+"&deg; - ";
  t1+=atlasPageDSLM+' '+atlasmagnitude+" - ";
  t1+=atlasPageStarLM+' '+starsmagnitude;
  canvasDrawNamedLabel(coordBkGroundColor,coordColor,t1,135,0,((t1.length)*fontSize1b)+20,fontSize1a+4,'left','gridInfo');
}
function gridXpx(Lrad) 
{ return Math.round((gridWidthXpx2*Lrad/gridSpanLrad));
}
function gridYpx(Drad)
{ return Math.round((gridHeightYpx2*Drad/gridSpanDrad));
}
function gridZoom(zoomFactor)
{ gridInitScaleFixed(gridL0rad*f12OverPi,gridD0rad*f180OverPi,Math.max(Math.min(gridActualDimension+zoomFactor,gridMaxDimension),gridMinDimension));
}
function gridZoomLevel(zoomLevel)
{ gridInitScaleFixed(gridL0rad*f12OverPi,gridD0rad*f180OverPi,Math.max(Math.min(zoomLevel,gridMaxDimension),gridMinDimension));
}

//Presentation functions ================================================================================================= 
function coordHrDecToHrMin(theHr)
{ while((theHr)>24) theHr-=24;
	while((theHr)<0)  theHr+=24;
	dsl_hr=Math.floor(theHr);
	dsl_min=Math.round((theHr-dsl_hr)*60);
	if(dsl_min==60)
	{ dsl_min=0;
	 ++dsl_hr;
	  if(dsl_hr==24)
	    dsl_hr=0;
	}
	if(dsl_min>0)
	 return dsl_hr+'h'+dsl_min+'m';
	return dsl_hr+'h';
}
function coordHrDecToHrMinSec(theHr)
{ while((theHr)>24) theHr-=24;
	while((theHr)<0)  theHr+=24;
	dsl_hr=Math.floor(theHr);
	dsl_min=Math.floor((theHr-dsl_hr)*60);
	dsl_sec=roundPrecision((theHr-dsl_hr-(dsl_min/60))*3600,10);
	if(dsl_sec==60)
	{ ++dsl_min;
	  dsl_sec=0;
	}
	if(dsl_min==60)
	{ dsl_min=0;
	 ++dsl_hr;
	}
	if(dsl_hr==24)
	 dsl_hr=0;
	if(dsl_sec>0)
	 return dsl_hr+'h'+dsl_min+'m'+dsl_sec+'s';
	else if(dsl_min>0)
	 return dsl_hr+'h'+dsl_min+'m';
	return dsl_hr+'h';
}
function coordDeclDecToDegMin(theDeg)
{ if(theDeg>90)  theDeg=90;
  if(theDeg<-90) theDeg=-90;
  sign='';if(theDeg<0) {theDeg=-theDeg; sign='-';}
  dsl_deg=Math.floor(theDeg);
  dsl_amn=Math.round((theDeg-dsl_deg)*60);
  if(dsl_amn==60)
  { dsl_amn=0;
   ++dsl_deg;
  }
  dsl_deg=sign+dsl_deg;
  if(dsl_amn>0)
    return dsl_deg+'&deg;'+dsl_amn+'\'';
  return dsl_deg+'&deg;';
}
function coordGridLxDyToString()
{ coordHrDecToHrMinSec(gridLxRad*f12OverPi);
  coordDeclDecToDegMin(gridDyRad*f180OverPi);
  return sprintf('%02d',dsl_hr)+'h'+sprintf('%02d',dsl_min)+'m'+sprintf('%02d',dsl_sec)+'s,'+sprintf('%02d',dsl_deg)+'&deg;'+sprintf('%02d',dsl_amn)+'\'';
}
// jg functions


// Utility functions
function roundPrecision(theValue,thePrecision)
{ return(Math.round(theValue/thePrecision)*thePrecision);
}