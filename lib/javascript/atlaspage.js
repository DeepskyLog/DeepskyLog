var cnv;
var jg;	
var xmlhttp;
var astroObjects;
var numberOfStarsLimit=200;
var numberOfDsoLimit=200;

var astroObjectsHotZones = new Array();

var onClickHandling=false;	                                                                      // Java Graphics object;

// Positioning Parameters

var divOffsetXpx =0;
var divOffsetYpx =0;
var divOffsetX2px=0;
var divOffsetY2px=0;

var canvasDimensionXpx,  canvasDimensionYpx;                        // Canvas Dimension X and Dimension Y: width and height of the canvas;
var canvasOffsetXpx=0;
var canvasOffsetYpx=16;

var gridBorder=true;     gridCoordLines=true;						            // Grid border (true or false)
var gridCenterOffsetXpx, gridCenterOffsetYpx;                       // Grid center offset X and offset Y: distance obetween the grid and the canvas border;
var gridOffsetXpx=80;                                               // Grid offset relative to the canvas
var gridOffsetYpx=30;                                               // Grid offset relative to the canvas
var gridWidthXpx,        gridHeightYpx;                             // Grid dimensions in X and Y;
var gridWidthXpx2=0,     gridHeightYpx2=0;                          // Half-width or height

var gridL0rad,           gridD0rad;		                              // Grid center coordinates ra and decl
var gridSpanLrad,        gridSpanDrad;  			                      // Grid span in L and D in rad
var gridSpanL,           gridSpanD;                                 // Grid span in L and D in deg
var gridluLhr, gridluDdeg, gridldLhr, gridldDdeg,                   // grid corner coordinates
    gridrdLhr, gridrdDdeg, gridruLhr, gridruDdeg,
    griduDdeg, griddDdeg;

var atlaspagerahr=0, atlaspagedecldeg=0, atlaspagezoomdeg=5;
var atlasmagnitude=0, atlasmagnitudedelta=0;                        // atlas magnitude of shown stars, and the delta in relation to the standard magnitude for the actual zoom level

// Color Parameters
var canvasBkGroundColor    ='#000000';                                          // Background color of the canvas;
var coordLineColor         ='#660000';                                          // Coordinate grid line colors
var coordLblBkGroundColor  ='#000000';                                          // Background Color of coordinate Labels
var coordLblColor          ='#DDDDDD';                                          // Color of coordinate labels
var coordBkGroundColor     ='#000000';                                          // Background color of coordinates of mouse position
var coordColor             ='#AAAAAA';                                          // Color of coordinates of mouse position
var gridBorderColor        ='#FFFF00';                                          // Color of the grid border
var starColor              ='#FFFF00';
var seenColor              ='#DD0000';
var seenXColor             ='#DDBB00';
var seenYColor             ='#00FF00';

// Layout Parameters
var coordGridsH, coordGridsV;                                                   // Obsolete - Number of grid lines H and V
var coordCnvXpx, coorCnvYpx;                                                    // Location of mouse coordinate positions relative to canvas
var Lsteps=10,   Dsteps=10;                                                     // Number of steps for drawing coordinate lines between major steps
var gridDimensions=new Array(
  new Array(180,80.00,2.000,5),                                                 // FoV, L grid distance in deg, D grid distance in deg, default limiting star magnitude level for this zoom level 
  new Array(150,60.00,2.000,5),
  new Array(120,50.00,2.000,5),
	new Array( 90,40.00,2.666,5),
	new Array( 75,30.00,2.000,5),
	new Array( 60,24.50,1.666,5),
	new Array( 45,20.00,1.333,5),
	new Array( 35,15.00,1.000,5),
	new Array( 30,12.00,0.800,5),
	new Array( 25,10.00,0.666,5),
	new Array( 20, 8.00,0.633,6),
	new Array( 15, 6.00,0.400,8),
	new Array( 10, 4.00,0.266,10),
	new Array(  7, 3.00,0.200,11),
	new Array(  5, 2.00,0.133,12),
	new Array(  4, 1.50,0.100,14),
	new Array(  3, 1.00,0.066,16),
	new Array(  2, 0.80,0.050,16),
	new Array(  1, 0.40,0.026,18),
	new Array(0.5, 0.20,0.012,18),
	new Array(0.25,0.20,0.012,18)
	);
var gridActualDimension=16;
var gridMaxDimension=20;
var gridMinDimension=0;
var hotZones= new Array(
  'atlasPageUpBtn','atlasPageSmallUpBtn','atlasPageDownBtn','atlasPageSmallDownBtn',
  'atlasPageLeftBtn','atlasPageSmallLeftBtn','atlasPageRightBtn','atlasPageSmallRightBtn',
  'space',
  'atlasPageZoomInBtn','atlasPageZoomOutBtn','atlasPageZoom1Btn','atlasPageZoom2Btn'  
  );

// Help parameters for parameter passing
var dsl_hr,     dsl_min,         dsl_sec;                                       // fn coordHrDecToHrMin    results
var dsl_deg,    dsl_amn,         dsl_asc;                                       // fn coordDeclDecToDegMin results
var canvasX1px, canvasY1px,      canvasX2px,     canvasY2px;                    // fn gridDrawLineLD     points from - to where the line is drawn (= intersection with the grid)
var gridLx1rad, gridDy1rad,      gridLx2rad,     gridDy2rad;                    // fn gridDrawLineLD     points from - to where the line is drawn (= intersection with the grid)
var gridLxRad,  gridDyRad;                                                      // several opertions help parameter

//astro functions ========================================================================================================
//function getAstroObjectValue(j,field)                   Gets the value of the field of the ith element of the object arrray
//function astroDrawStar(Lhr,Ddeg,mag)                    Draw a star on the grid
//function astroDrawDStar(Lhr,Ddeg,mag)                   Draw a double star on the grid
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
//function gridSetHotZones()                              sets the clickable buttons
//function gridXpx(Lrad)                                  converts a number of rad ortho coordinates to a number of X pixels
//function gridYpx(Drad)                                  converts a number of rad ortho coordinates to a number of Y pixels
//function gridZoom(zoomFactor)                           zooms in or out by a number of zoom steps

//Presentation functions =================================================================================================
//function coordHrDecToHrMin(theHr)                       display hr as xhym (if y>0)
//function coordHrDecToHrMinSec(theHr)                    display hr as xhymzs if z,y>0)
//function coordDeclDecToDegMin(theDeg)                   display decl as x°y' if y>0
//function coordGridLxDyToString()                        display screen coordinates as L,D

function getAstroObjectValue(j,field)
{ return astroObjects[j].getElementsByTagName(field)[0].firstChild.nodeValue;
}
//astro functions ========================================================================================================
function astroDrawStar(Lhr,Ddeg,mag)
{ var d=Math.round((gridActualDimension-(2*mag)-1)*1.5);
  if(d<=1) d=1;
  jg.setColor(starColor);
  gridDrawFilledCirclePx(Lhr,Ddeg,d);
}
function astroDrawDStar(Lhr,Ddeg,mag)
{ var d=Math.round((gridActualDimension-mag-1)*1.5);
  var e=Math.max(Math.round(d*1.2),d+2);
  if(d<=0) return false;
  jg.setColor(starColor);
  if(gridDrawFilledCirclePx(Lhr,Ddeg,d) && (d>1))
	  gridDrawLinePxLR(Lhr,Ddeg,(Math.floor(d/2)+2));
  return true;
}
function astroDrawBRTNB(Lhr,Ddeg,diam1,diam2,pa, nm, seen)
{ if(diam2==0) diam2=diam1;
  gridDrawRectangleCWH(Lhr,Ddeg,diam1,diam2,pa,nm,seen);
}
function astroDrawCLANB(Lhr,Ddeg,diam1,diam2,pa, nm, seen)
{ if(diam2==0) diam2=diam1;
  gridDrawRectangleCWH(Lhr,Ddeg,diam1,diam2,pa,nm,seen);
  gridDrawEllipseTilt(Lhr,Ddeg,(diam1),(diam2),pa,nm,seen);
}
function astroDrawDRKNB(Lhr,Ddeg,diam1,diam2,pa, nm, seen)
{ if(diam2==0) diam2=diam1;
  gridDrawRectangleCWH(Lhr,Ddeg,diam1,diam2,pa,nm,seen);
}
function astroDrawGX(Lhr,Ddeg,diam1,diam2,pa, nm, seen)
{ if(diam2==0) diam2=diam1;
  gridDrawEllipseTilt(Lhr,Ddeg,diam1,diam2,pa,nm,seen);
}
function astroDrawOC(Lhr,Ddeg,diam,nm,seen)
{ jg.setColor(starColor);
  gridDrawEllipseTilt(Lhr,Ddeg,(diam),(diam),0,nm,seen);
}

function astroDrawObjects()
{ astroGetObjects(); // Chains to drawing automatically
//astroGetDSObjects();
}
function astroDrawAllObjects()
{ //alert(astroObjects.length);
  for(var i=0;(i<astroObjects.length);i++)
  { //alert(urldecode(getAstroObjectValue(i,"name")));
    if(getAstroObjectValue(i,"type")=='AA1STAR')
      astroDrawStar(getAstroObjectValue(i,"RA2000"),getAstroObjectValue(i,"DE2000"),getAstroObjectValue(i,"vMag"));
    else if(getAstroObjectValue(i,"type")=='AA2STAR')
      astroDrawDStar(getAstroObjectValue(i,"ra"),getAstroObjectValue(i,"decl"),getAstroObjectValue(i,"mag"));
    else if(getAstroObjectValue(i,"type")=='OPNCL')
      astroDrawOC(getAstroObjectValue(i,"ra"),getAstroObjectValue(i,"decl"),getAstroObjectValue(i,"diam1"),(getAstroObjectValue(i,"name")),getAstroObjectValue(i,"seen"));
    else if(getAstroObjectValue(i,"type")=='GALXY')
      astroDrawGX(getAstroObjectValue(i,"ra"),getAstroObjectValue(i,"decl"),getAstroObjectValue(i,"diam1"),getAstroObjectValue(i,"diam2"),getAstroObjectValue(i,"pa"),(getAstroObjectValue(i,"name")),getAstroObjectValue(i,"seen"));
    else if(getAstroObjectValue(i,"type")=='BRTNB')
      astroDrawBRTNB(getAstroObjectValue(i,"ra"),getAstroObjectValue(i,"decl"),getAstroObjectValue(i,"diam1"),getAstroObjectValue(i,"diam2"),getAstroObjectValue(i,"pa"),(getAstroObjectValue(i,"name")),getAstroObjectValue(i,"seen"));
    else if(getAstroObjectValue(i,"type")=='CLANB')
      astroDrawCLANB(getAstroObjectValue(i,"ra"),getAstroObjectValue(i,"decl"),getAstroObjectValue(i,"diam1"),getAstroObjectValue(i,"diam2"),getAstroObjectValue(i,"pa"),(getAstroObjectValue(i,"name")),getAstroObjectValue(i,"seen"));
    else if(getAstroObjectValue(i,"type")=='DRKNB')
      astroDrawDRKNB(getAstroObjectValue(i,"ra"),getAstroObjectValue(i,"decl"),getAstroObjectValue(i,"diam1"),getAstroObjectValue(i,"diam2"),getAstroObjectValue(i,"pa"),(getAstroObjectValue(i,"name")),getAstroObjectValue(i,"seen"));
    else if(getAstroObjectValue(i,"type")!='GALXY')
      astroDrawGX(getAstroObjectValue(i,"ra"),getAstroObjectValue(i,"decl"),getAstroObjectValue(i,"diam1"),getAstroObjectValue(i,"diam2"),getAstroObjectValue(i,"pa"),(getAstroObjectValue(i,"name")),getAstroObjectValue(i,"seen"));
  }
  //astroDrawGX(atlaspagerahr,atlaspagedecldeg,346.86,53.46,89,"David");
  jg.paint();
}
function astroGetObjects()
{ if(window.XMLHttpRequest)
	  xmlhttp=new XMLHttpRequest();
  else if(window.activeXObject)
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  else
	  alert("Atlas pages are not supported on non-xmlhttp machines");
  xmlhttp.onreadystatechange=function()
  { if(xmlhttp.readyState==4)
    { var xmlDoc=xmlhttp.responseXML.documentElement;
      astroObjects=xmlDoc.getElementsByTagName("object");
      astroDrawAllObjects();
    }
  };
  var lLhr=Math.max(gridluLhr,gridldLhr);
  var rLhr=Math.min(gridrdLhr,gridruLhr);
  var url="ajaxinterface.php";
  url+="?instruction=getObjects";
  url+="&lLhr="+lLhr;
  url+="&dDdeg="+griddDdeg;
  url+="&rLhr="+rLhr;
  url+="&uDdeg="+griduDdeg;
  url+="&mag="+atlasmagnitude;
//  alert(url);
  xmlhttp.open("GET",url,true);
  xmlhttp.send(null);
}

//atlas Functions =========================================================================================================
function atlasFillPage()
{ divInit('myDiv');
  canvasInit('myDiv');
  gridInit();
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  gridSetHotZones();
  atlasRedraw();
  jg.paint();
}
function atlasPageUpBtnFn()
{ atlaspagedecldeg=atlaspagedecldeg+(gridSpanD * 1.6);
  if(atlaspagedecldeg>(90-(gridSpanD/2))) atlaspagedecldeg=90-(gridSpanD/2);
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageSmallUpBtnFn()
{ atlaspagedecldeg=atlaspagedecldeg+(gridSpanD * 0.4);
  if(atlaspagedecldeg>(90-(gridSpanD/2))) atlaspagedecldeg=90-(gridSpanD/2);
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageDownBtnFn()
{ atlaspagedecldeg=atlaspagedecldeg-(gridSpanD * 1.6);
  if(atlaspagedecldeg<(-90+(gridSpanD/2))) atlaspagedecldeg=(-90+(gridSpanD/2));
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageSmallDownBtnFn()
{ atlaspagedecldeg=atlaspagedecldeg-(gridSpanD * 0.4);
  if(atlaspagedecldeg<(-90+(gridSpanD/2))) atlaspagedecldeg=(-90+(gridSpanD/2));
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageLeftBtnFn()
{ atlaspagerahr=atlaspagerahr+(gridSpanL * 0.1);
  if(atlaspagerahr<0) atlaspagerahr+=24;
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageSmallLeftBtnFn()
{ atlaspagerahr=atlaspagerahr+(gridSpanL * 0.025);
  if(atlaspagerahr<0) atlaspagerahr+=24;
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageRightBtnFn()
{ atlaspagerahr=atlaspagerahr-(gridSpanL * 0.05333);
  if(atlaspagerahr>24) atlaspagerahr-=24;
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageSmallRightBtnFn()
{ atlaspagerahr=atlaspagerahr-(gridSpanL * 0.01333);
  if(atlaspagerahr>24) atlaspagerahr-=24;
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageZoomInBtnFn()
{ gridZoom(1);
  atlasRedraw();
}
function atlasPageZoom1BtnFn()
{ gridInitScale(atlaspagerahr,atlaspagedecldeg,1);
  atlasRedraw();
}
function atlasPageZoom2BtnFn()
{ gridInitScale(atlaspagerahr,atlaspagedecldeg,2);
  atlasRedraw();
}
function atlasPageZoomOutBtnFn()
{ gridZoom(-1);
  atlasRedraw();
}
function atlasRedraw()
{ canvasRedraw();
  gridSetBorderColor(gridBorderColor);
  jg.setFont("arial","12px","");
  if(gridCoordLines)
	  gridDrawCoordLines();
	gridDrawBorder();
	jg.paint();
  jg.setFont("arial","8px","");
  astroDrawObjects();
  jg.setFont("arial","12px","");
  document.getElementById("myDiv").style.cursor='default';
}

//canvas actions =========================================================================================================
function canvasCursor(theCursor)
{ cnv.style.cursor = theCursor;
}
function canvasOnClick(event)
{ if(onClickHandling==true)
    return;
	onClickHandling=true;
	if((x>div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx)&&(x<div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx)&&
			 (y>div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx)&&(y<div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx))
	{ x=event.clientX;
    y=event.clientY+1;
    gridLDinvRad(x,y);
    atlaspagerahr=gridLxRad/(2*Math.PI)*24;
    atlaspagedecldeg=gridDyRad/(Math.PI)*180;
    gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
    atlasRedraw();
	}
	onClickHandling=false;
}
function canvasOnMouseMove(event)
{ x=event.clientX;
  y=event.clientY+1;
	if((x>div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx)&&(x<div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx)&&
		 (y>div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx)&&(y<div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx))
	{	if (document.getElementById("myDiv").style.cursor!='crosshair')
		  document.getElementById("myDiv").style.cursor='crosshair';
		gridLDinvRad(x,y);
	  if(document.getElementById('cursorpos'))
	  	canvasreDrawNamedLabel(coordBkGroundColor,coordColor,coordGridLxDyToString(),0,-17,150,16,'center','cursorpos');
	  else
	  	canvasDrawNamedLabel(coordBkGroundColor,coordColor,coordGridLxDyToString(),0,-17,150,16,'center','cursorpos');
	}
	else if (document.getElementById("myDiv").style.cursor!='default')
	{	document.getElementById("myDiv").style.cursor='default';
    canvasreDrawNamedLabel(coordBkGroundColor,coordColor,"          ",0,-17,150,16,'center','cursorpos');
	}
	jg.paint();
}

//canvas functions =======================================================================================================

function canvasDrawEllipseTilt(cx,cy,w,h,angle,name,seen)
{	angle=-angle;
  jg.drawEllipseTiltLimited(canvasOffsetXpx+cx,canvasOffsetYpx+canvasDimensionYpx-cy,w,h,angle,canvasOffsetXpx+gridOffsetXpx,canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,canvasOffsetYpx+gridOffsetYpx,name,seen);
}
function canvasDrawFilledCircle(cx,cy,d)
{	return jg.fillEllipse(Math.round(canvasOffsetXpx+cx-(d/2)),Math.round(canvasOffsetYpx+canvasDimensionYpx-cy-(d/2)),d,d);
}
function canvasDrawLabel(bkGrdColor,textColor,theLabel,a,b,w,h,align)
{ jg.setColor(bkGrdColor);
	canvasFillRect(a,b,w,h);
	jg.setColor(textColor);
	canvasDrawStringRect(theLabel,a,b,w,h,align);
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
function canvasDrawLine(A1px,B1px,A2px,B2px)
{ return jg.drawLine(canvasOffsetXpx+A1px,canvasOffsetYpx+canvasDimensionYpx-B1px,canvasOffsetXpx+A2px,canvasOffsetYpx+canvasDimensionYpx-B2px);
}
function canvasDrawPoint(Apx,Bpx)
{ return jg.drawLine(canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function canvasDrawRectangleCWH(cx,cy,w,h,angle,name,seen)
{	angle=-angle;
  jg.drawRectangleLimited(canvasOffsetXpx+cx,
  		                    canvasOffsetYpx+canvasDimensionYpx-cy,
  		                    w,h,angle,
  		                    canvasOffsetXpx+gridOffsetXpx,
  		                    canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,
  		                    canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,
  		                    canvasOffsetYpx+gridOffsetYpx,
  		                    name,seen);
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
{ canvasDimensionXpx=((div5Width-divOffsetXpx-divOffsetX2px)-(2*canvasOffsetXpx)-2);
  canvasDimensionYpx=((div5Height-divOffsetYpx-divOffsetY2px)-(2*canvasOffsetYpx)-2);
  cnv=document.getElementById(canvas);
  if(!cnv) return false;
	  jg=new jsGraphics(cnv);
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
  if(event.wheelDelta)
  { delta=event.wheelDelta/120;
    if(window.opera) delta=-delta;
    else if(event.detail) delta=-event.detail/3;
    if(delta)
    	if(delta<0)
    		atlasPageZoomInBtnFn();
    	else
    		atlasPageZoomOutBtnFn();
  }
}
function divInit(theDiv)
{ document.getElementById(theDiv).style.left  =divOffsetXpx+'px';
  document.getElementById(theDiv).style.top   =divOffsetYpx+'px';
  document.getElementById(theDiv).style.width =(div5Width-divOffsetXpx-divOffsetX2px)+'px';
  document.getElementById(theDiv).style.height=(div5Height-divOffsetYpx-divOffsetY2px)+'px';
  if(window.addEventListener)
  	window.addEventListener('DOMMouseScroll',wheel,false);
  window.onmousewheel=documentonmousewheel=wheel;
  	
}

//grid functions =========================================================================================================
function gridClearBorder()
{ gridBorder=false;
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
{ gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx);
  luLrad=gridLxRad;
  gridluLhr=luLrad/Math.PI*12;
  luDrad=gridDyRad;
  gridluDdeg=luDrad/Math.PI*180;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx);
  ruLrad=gridLxRad;
  gridruLhr=ruLrad/Math.PI*12;
  ruDrad=gridDyRad;
  gridruDdeg=ruDrad/Math.PI*180;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  ldLrad=gridLxRad;
  gridldLhr=ldLrad/Math.PI*12;
  ldDrad=gridDyRad;
  gridldDdeg=ldDrad/Math.PI*180;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  rdLrad=gridLxRad;
  gridrdLhr=rdLrad/Math.PI*12;
  rdDrad=gridDyRad;
  gridrdDdeg=rdDrad/Math.PI*180;
  
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+Math.round(gridWidthXpx/2),div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx);
  griduDdeg=gridDyRad/Math.PI*180;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  griddDdeg=gridDyRad/Math.PI*180;
   
  if(((gridD0rad+gridSpanDrad)<(Math.PI/2))&&((gridD0rad-gridSpanDrad)>-(Math.PI/2)))
	{	if(gridD0rad>0)
	  { Lrad=luLrad;
	    Rrad=ruLrad;
    }
		else
	  { Lrad=ldLrad;
	    Rrad=rdLrad;
    }
		if(Lrad<Rrad)
		  Rrad-=(Math.PI*2);
		Urad=Math.max(gridD0rad+gridSpanDrad,Math.max(luDrad,ruDrad));
	  Drad=Math.min(gridD0rad-gridSpanDrad,Math.min(ldDrad,rdDrad));
		Lhr=Lrad/Math.PI*12;
		RhrNeg=Rrad/Math.PI*12;
		Udeg=Urad/Math.PI*180;
		Ddeg=Drad/Math.PI*180;
		coordGridsH=Math.max(coordGridsH,1);
		coordGridsV=Math.max(coordGridsV,1);
	}
  else if((gridD0rad+gridSpanDrad)>=(Math.PI/2))
	{ Lhr=gridL0rad/Math.PI*12+12+.01;
	  RhrNeg=gridL0rad/Math.PI*12-12;
		Udeg=90;
		Ddeg=Math.min(gridD0rad-gridSpanDrad,Math.min(ldDrad,rdDrad))/Math.PI*180;
	}
  else if((gridD0rad-gridSpanDrad)<=-(Math.PI/2))
	{ Lhr=gridL0rad/Math.PI*12+12+.01;
	  RhrNeg=gridL0rad/Math.PI*12-12;
		Udeg=Math.max(gridD0rad+gridSpanDrad,Math.max(luDrad,ruDrad))/Math.PI*180;
		Ddeg=-90;
	}
	
	DLhr=(Lhr-RhrNeg);
	LStep=Math.min(Math.round((gridDimensions[gridActualDimension][2]/Math.cos(gridD0rad))*60)/60,2);
	DDdeg=(Udeg-Ddeg);
	DStep=gridDimensions[gridActualDimension][1];
	
	LhrStart=(Math.floor(Lhr/LStep)+1)*LStep;
 	DdegStart=(Math.floor(Ddeg/DStep)+1)*DStep;

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
	      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx-17,60,15,'center');
			else if(canvasX2px&&(canvasX2px<=gridOffsetXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx-62,canvasY2px-8,60,15,'right');
			else if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
			else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
	      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
	  }
  }
	else
	{ for(l=LhrStart;l>RhrNeg;l-=LStep)
    {	l=Math.round(l*60)/60;
	    canvasX2px=0;
	    jg.setColor(coordLineColor);
      for(d=Udeg;d>Ddeg;d-=DStep/Dsteps)
	      gridDrawLineLD(l,d,l,(d-(DStep/Dsteps)));
      if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
	      canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx-17,60,15,'center');
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
function gridDrawFilledCirclePx(Lhr,Ddeg,DiamPx)
{ gridLDrad(Lhr,Ddeg); 
	cx=gridCenterOffsetXpx+gridXpx(gridLxRad);
	cy=gridCenterOffsetYpx+gridYpx(gridDyRad);
	if((cx-DiamPx<gridOffsetXpx)||(cx+DiamPx>gridOffsetXpx+gridWidthXpx)) return false;
	if((cy+DiamPx>gridOffsetYpx+gridHeightYpx)||(cy-DiamPx<gridOffsetYpx)) return false;
  canvasDrawFilledCircle(cx,cy,DiamPx,DiamPx);
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
function gridDrawLineLD(Lhr1,Ddeg1,Lhr2,Ddeg2)
{ gridLDrad(Lhr1,Ddeg1); x1=gridLxRad; y1=gridDyRad;
	gridLDrad(Lhr2,Ddeg2); x2=gridLxRad; y2=gridDyRad;
	if((x1<-gridSpanLrad)&&(x2<-gridSpanLrad)) return;
	if((x1>gridSpanLrad)&&(x2>gridSpanLrad))   return;
	if((y1<-gridSpanDrad)&&(y2<-gridSpanDrad)) return;
	if((y1>gridSpanDrad)&&(y2>gridSpanDrad))   return;
	if(x1<-gridSpanLrad) if(x2==x1) return; else {y1=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x1=-gridSpanLrad;}
	if(x1>gridSpanLrad)  if(x2==x1) return; else	{y1=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridSpanLrad; }
	if(y1>gridSpanDrad)  if(y2==y1) return; else	{x1=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridSpanDrad; }
	if(y1<-gridSpanDrad) if(y2==y1) return; else {x1=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y1=-gridSpanDrad;}
	if((y1<-gridSpanDrad)||(y1>gridSpanDrad)||(x1<-gridSpanLrad)||(x1>gridSpanLrad)) return;	
	if(x2<-gridSpanLrad) if(x2==x1) return; else {y2=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x2=-gridSpanLrad;}
	if(x2>gridSpanLrad)  if(x2==x1) return; else	{y2=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridSpanLrad;	}
  if(y2>gridSpanDrad)  if(y2==y1) return; else	{x2=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridSpanDrad;	}
	if(y2<-gridSpanDrad) if(y2==y1) return; else	{x2=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y2=-gridSpanDrad;}
  if((y2<-gridSpanDrad)||(y2>gridSpanDrad)||(x2<-gridSpanLrad)||(x2>gridSpanLrad)) return;
	
	canvasX1px=gridCenterOffsetXpx+gridXpx(x1);
  canvasY1px=gridCenterOffsetYpx+gridYpx(y1);
  canvasX2px=gridCenterOffsetXpx+gridXpx(x2);
  canvasY2px=gridCenterOffsetYpx+gridYpx(y2);
	gridLx1rad=x1;gridDy1rad=y1;gridLx2rad=x2;gridDy2rad=y2;
	canvasDrawLine(canvasX1px,canvasY1px,canvasX2px,canvasY2px);
}
function gridDrawPointLD(Lhr,Ddeg)
{ gridLDrad(Lhr,Ddeg);
  if((gridLxRad>-gridSpanLrad)&&(gridLxRad<gridSpanLrad)&&(gridDyRad>-gridSpanDrad)&&(gridDyRad<gridSpanDrad))
	  return canvasDrawPoint(gridCenterOffsetXpx+gridXpx(gridLxRad),gridCenterOffsetYpx+gridYpx(gridDyRad));
  else return 0;
}
function gridDrawRectangleCWH(Lhr,Ddeg,wSec,hSec,pa,nm,seen)
{ gridLDrad(Lhr,Ddeg);
  canvasDrawRectangleCWH(gridCenterOffsetXpx+gridXpx(gridLxRad),
  		                   gridCenterOffsetYpx+gridYpx(gridDyRad),
  		                   Math.round((gridWidthXpx2*(wSec/3600)/gridSpanL)),
  		                   Math.round((gridHeightYpx2*(hSec/3600)/gridSpanD)),pa,nm,seen);
}
function gridInit()
{	gridWidthXpx=canvasDimensionXpx-gridOffsetXpx-gridOffsetXpx;
	gridWidthXpx2=Math.round(gridWidthXpx/2);
	gridCenterOffsetXpx=gridOffsetXpx+Math.round(gridWidthXpx/2);
	gridHeightYpx=canvasDimensionYpx-gridOffsetYpx-gridOffsetYpx;
	gridHeightYpx2=Math.round(gridHeightYpx/2);
	gridCenterOffsetYpx=gridOffsetYpx+Math.round(gridHeightYpx/2);
}
function gridInitScale(gridLHr,gridDdeg,desiredScale)
{ gridActualDimension=gridMaxDimension;
	while((gridActualDimension>gridMinDimension)&&(gridDimensions[gridActualDimension][0]<desiredScale))
	  gridActualDimension--;
	gridL0rad=gridLHr/12*Math.PI;
	gridD0rad=gridDdeg/180*Math.PI;
  if(gridWidthXpx<gridHeightYpx)
  {	gridSpanD=gridDimensions[gridActualDimension][0]*(gridHeightYpx/gridWidthXpx);
		gridSpanL=gridDimensions[gridActualDimension][0];
	}
	else
	{ gridSpanD=gridDimensions[gridActualDimension][0];
		gridSpanL=gridDimensions[gridActualDimension][0]*(gridWidthXpx/gridHeightYpx);
	}
	gridSpanLrad=gridSpanL/180*Math.PI;
	gridSpanDrad=gridSpanD/180*Math.PI;
	atlaspagezoomdeg=gridDimensions[gridActualDimension][0];
  atlasmagnitude = gridDimensions[gridActualDimension][3]+atlasmagnitudedelta;
}
function gridInitScaleFixed(gridLHr,gridDdeg,desiredScale)
{ gridActualDimension=desiredScale;
  gridL0rad=gridLHr/12*Math.PI;
	gridD0rad=gridDdeg/180*Math.PI;
  if(gridWidthXpx<gridHeightYpx)
  {	gridSpanD=gridDimensions[gridActualDimension][0]*(gridHeightYpx/gridWidthXpx);
		gridSpanL=gridDimensions[gridActualDimension][0];
	}
	else
	{ gridSpanD=gridDimensions[gridActualDimension][0];
		gridSpanL=gridDimensions[gridActualDimension][0]*(gridWidthXpx/gridHeightYpx);
	}
	gridSpanLrad=gridSpanL/180*Math.PI;
	gridSpanDrad=gridSpanD/180*Math.PI;
	atlaspagezoomdeg=gridDimensions[gridActualDimension][0];
  atlasmagnitude = gridDimensions[gridActualDimension][3]+atlasmagnitudedelta;
//  alert(gridDimensions[gridActualDimension][0]);
}
function gridLDrad(Lhr,Ddeg)
{ Lrad=Lhr/12*Math.PI; Drad=Ddeg/180*Math.PI;
	if(Lrad>gridL0rad+Math.PI) Lrad=Lrad-(Math.PI*2);
	if(Lrad<gridL0rad-Math.PI) Lrad=Lrad+(Math.PI*2);
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
		gridDyRad=((Math.PI/2)-Dacc);
	}
	else
	{ gridLxRad=gridL0rad;
	  gridDyRad=gridD0rad;
	}
	if((gridDyRad)>(Math.PI/2))
	  gridDyRad=(Math.PI/2);
	if((gridDyRad)<(-Math.PI/2))
	  gridDyRad=(-Math.PI/2);
	if((gridLxRad)<0)
	  gridLxRad=gridLxRad+(2*Math.PI);
	if((gridLxRad)>=(2*Math.PI))
	  gridLxRad=gridLxRad-(2*Math.PI);
}
function gridSetBorderColor(theColor)
{ gridBorder=true;
  gridBorderColor=theColor;
}
function gridSetHotZones()
{ for(i in hotZones)
  { if(hotZones[i]!="space")
  	{ var element = document.createElement('input');
      element.setAttribute('type','image');
      element.setAttribute('id',hotZones[i]);
      element.setAttribute('name',hotZones[i]);
      element.setAttribute('src','styles/images/'+hotZones[i]+'.png');
      element.setAttribute('style','position:absolute;top:0px;left:'+(i*16)+'px;width:16px;height:16px;');
      element.setAttribute('title',eval(hotZones[i]+'Txt'));
      element.setAttribute('onclick',hotZones[i]+'Fn();');
      div=document.getElementById('myDiv');
      div.appendChild(element);
  	}
  }
}
function gridXpx(Lrad) 
{ return Math.round((gridWidthXpx2*Lrad/gridSpanLrad));
}
function gridYpx(Drad)
{ return Math.round((gridHeightYpx2*Drad/gridSpanDrad));
}
function gridZoom(zoomFactor)
{ gridInitScaleFixed(gridL0rad/Math.PI*12,gridD0rad/Math.PI*180,Math.max(Math.min(gridActualDimension+zoomFactor,gridMaxDimension),gridMinDimension));
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
	  return dsl_deg+'°'+dsl_amn+'\'';
  return dsl_deg+'°';
}
function coordGridLxDyToString()
{ coordHrDecToHrMinSec(gridLxRad/Math.PI*12);
  coordDeclDecToDegMin(gridDyRad/Math.PI*180);
  return '('+sprintf('%02d',dsl_hr)+'h'+sprintf('%02d',dsl_min)+'m'+sprintf('%02d',dsl_sec)+'s, '+sprintf('%02d',dsl_deg)+'°'+sprintf('%02d',dsl_amn)+'\')';
}
// jg functions


// Utility functions
function roundPrecision(theValue,thePrecision)
{ return(Math.round(theValue/thePrecision)*thePrecision);
}