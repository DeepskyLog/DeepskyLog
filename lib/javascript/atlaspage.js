var cnv;
var jg;	

var astroObjects = new Array();
var astroObjectsHotZones = new Array();

var onClickHandling=false;	                                                                      // Java Graphics object;

// Positioning Parameters

var divOffsetXpx =0;
var divOffsetYpx =0;
var divOffsetX2px=0;
var divOffsetY2px=0;

var canvasDimensionXpx,  canvasDimensionYpx;                        // Canvas Dimension X and Dimension Y: width and height of the canvas;
var canvasOffsetXpx=0;
var canvasOffsetYpx=0;

var gridBorder=true;     gridCoordLines=true;						            // Grid border (true or false)
var gridCenterOffsetXpx, gridCenterOffsetYpx;                       // Grid center offset X and offset Y: distance obetween the grid and the canvas border;
var gridOffsetXpx=80;                                               // Grid offset relative to the canvas
var gridOffsetYpx=50;                                               // Grid offset relative to the canvas
var gridWidthXpx,        gridHeightYpx;                             // Grid dimensions in X and Y;
var gridWidthXpx2=0,     gridHeightYpx2=0;                          // Half-width or height

var gridL0rad,           gridD0rad;		                              // Grid center coordinates ra and decl
var gridSpanLrad,        gridSpanDrad;  			                      // Grid span in L and D in rad
var gridSpanL,           gridSpanD;                                 // Grid span in L and D in deg

var atlaspagerahr=0, atlaspagedecldeg=0, atlaspagezoomdeg=10;


// Color Parameters
var canvasBkGroundColor    ='#000000';                                          // Background color of the canvas;
var coordLineColor         ='#EE0000';                                          // Coordinate grid line colors
var coordLblBkGroundColor  ='#000000';                                          // Background Color of coordinate Labels
var coordLblColor          ='#DDDDDD';                                          // Color of coordinate labels
var coordBkGroundColor     ='#000000';                                          // Background color of coordinates of mouse position
var coordColor             ='#AAAAAA';                                          // Color of coordinates of mouse position
var gridBorderColor        ='#FFFF00';                                          // Color of the grid border
var starColor              ='#FFFF00';

// Layout Parameters
var coordGridsH, coordGridsV;                                                   // Obsolete - Number of grid lines H and V
var coordCnvXpx, coorCnvYpx;                                                    // Location of mouse coordinate positions relative to canvas
var Lsteps=10,   Dsteps=10;                                                     // Number of steps for drawing coordinate lines between major steps
var gridDimensions=new Array(
  new Array(180,80.00,6.000),
  new Array(150,60.00,4.000),
  new Array(120,50.00,3.000),
	new Array( 90,40.00,2.666),
	new Array( 75,30.00,2.000),
	new Array( 60,24.50,1.666),
	new Array( 45,20.00,1.333),
	new Array( 35,15.00,1.000),
	new Array( 30,12.00,0.800),
	new Array( 25,10.00,0.666),
	new Array( 20, 8.00,0.633),
	new Array( 15, 6.00,0.400),
	new Array( 10, 4.00,0.266),
	new Array(  7, 3.00,0.200),
	new Array(  5, 2.00,0.133),
	new Array(  4, 1.50,0.100),
	new Array(  3, 1.00,0.066),
	new Array(  2, 0.80,0.050),
	new Array(  1, 0.40,0.026),
	new Array(0.5, 0.20,0.012),
	new Array(0.25,0.10,0.006)
	);
var gridActualDimension=14;
var gridMaxDimension=20;
var gridMinDimension=0;
var hotZones= new Array(
  'atlasPageUpBtn','atlasPageSmallUpBtn','atlasPageDownBtn','atlasPageSmallDownBtn',
  'atlasPageLeftBtn','atlasPageSmallLeftBtn','atlasPageRightBtn','atlasPageSmallRightBtn',
  'atlasPageZoomInBtn','atlasPageZoomOutBtn'  
  );

// Help parameters for parameter passing
var dsl_hr,     dsl_min,         dsl_sec;                                       // fn coordHrDecToHrMin    results
var dsl_deg,    dsl_amn,         dsl_asc;                                       // fn coordDeclDecToDegMin results
var canvasX1px, canvasY1px,      canvasX2px,     canvasY2px;                    // fn gridDrawLineLD     points from - to where the line is drawn (= intersection with the grid)
var gridLx1rad, gridDy1rad,      gridLx2rad,     gridDy2rad;                    // fn gridDrawLineLD     points from - to where the line is drawn (= intersection with the grid)
var gridLxRad,  gridDyRad;                                                      // several opertions help parameter

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

//astro functions ========================================================================================================
//function astroDrawStar(Lhr,Ddeg,mag)                    Draw a star on the grid
//function astroDrawDStar(Lhr,Ddeg,mag)                   Draw a double star on the grid
//function astroDrawObjects()                             Draw astro objects
//function astroSetObject(objectType,x,y,diam1,diam2,pa,mag,sb,objectname,altnames,seen,lastseendate)

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



//atlas Functions =========================================================================================================
function atlasFillPage()
{ divInit('myDiv');
  canvasInit('myDiv');
  gridInit();
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  gridSetHotZones();
  atlasRedraw();
	jg.setColor("#00FF00");
  gridDrawLineLD(14.9,36,14,40);
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
{ atlaspagerahr=atlaspagerahr+(gridSpanL * 0.05333);
  if(atlaspagerahr<0) atlaspagerahr+=24;
  gridInitScale(atlaspagerahr,atlaspagedecldeg,atlaspagezoomdeg);
  atlasRedraw();
}
function atlasPageSmallLeftBtnFn()
{ atlaspagerahr=atlaspagerahr+(gridSpanL * 0.01333);
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
function atlasPageZoomOutBtnFn()
{ gridZoom(-1);
  atlasRedraw();
}
function atlasRedraw()
{ canvasRedraw();
  gridSetBorderColor(gridBorderColor);
  if(gridCoordLines)
	  gridDrawCoordLines();
  astroGetObjects();
  astroDrawObjects();
	gridDrawBorder();
	jg.paint();
}

//astro functions ========================================================================================================
function astroDrawStar(Lhr,Ddeg,mag)
{ d=Math.round((gridActualDimension-mag-1)*1.5);
	if(d<=0) return;
	jg.setColor(starColor);
	gridDrawFilledCirclePx(Lhr,Ddeg,d);
}
function astroDrawDStar(Lhr,Ddeg,mag)
{ d=Math.round((gridActualDimension-mag-1)*1.5);
	e=Math.max(Math.round(d*1.2),d+2);
	if(d<=0) return false;
	jg.setColor(starColor);
	if(gridDrawFilledCirclePx(Lhr,Ddeg,d))
  	gridDrawLinePxLR(Lhr,Ddeg,d);
	return true;
}
function astroDrawObjects()
{ for(i=0;i<astroObjects.length;i++)
  { if(astroObjects[i][0]=='Star')
      astroDrawStar(astroObjects[i][1],astroObjects[i][2],astroObjects[i][6]);
    if(astroObjects[i][0]=='DStar')
      astroDrawDStar(astroObjects[i][1],astroObjects[i][2],astroObjects[i][6]);
	}
}
function astroGetObjects()
{  // Demo data
  //astroSetObject('Star',14.89722,36.53333,0,0,0,8, 0,'Alcor','B UMa', 'X(5)',   '');
  astroSetObject('Star',14.9, 36.0       ,0,0,0,8, 0,'Mizar','24 UMa','X(4)',   '');
	//astroSetObject('Star',0.14, 0.2,0,0,0,6, 0,'Deneb','A Cyg', 'Y(12/5)','20090105');
	//astroSetObject('Star',0.16, 0.3,0,0,0,4, 0,'Dubhe','D UMa', 'Y(56/3)','20090230');
	//astroSetObject('Star',23.95,0.3,0,0,0,12,0,'Alfar','C Her', 'X(6)',   '');
	//astroSetObject('Star',23.90,0.2,0,0,0,14,0,'Vega' ,'D Vul', '-',      '');
	//astroSetObject('Star',23.97,0.1,0,0,0,16,0,'Arctu','A Boo', '-',      '');
	//astroSetObject('DStar',0.06,-0.1,0,0,0,10,0,'Arctu2','B Boo', '-',      '');
}
function astroSetObject(objectType,x,y,diam1,diam2,pa,mag,sb,objectname,altnames,seen,lastseendate)
{ astroObjects[astroObjects.length]=new Array(objectType,x,y,diam1,diam2,pa,mag,sb,objectname,altnames,seen,lastseendate);
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
  gridLDinvRad(x,y);
  gridDrawPointLD(gridLxRad/(2*Math.PI)*24, gridDyRad/(Math.PI)*180);
  jg.paint();
  onClickHandling=false;
}
function canvasOnMouseMove(event)
{ x=event.clientX;
  y=event.clientY+1;
	if((x>div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx)&&(x<div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx)&&
		 (y>div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx)&&(y<div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx))
	{	gridLDinvRad(x,y);
	  canvasDrawLabel(coordBkGroundColor,coordColor,coordGridLxDyToString(),2,2,150,15,'center');
	}
	jg.paint();
}

//canvas functions =======================================================================================================

function canvasDrawEllipseTilt(cx,cy,w,h,angle)
{	angle=-angle;
  jg.drawEllipseTiltLimited(canvasOffsetXpx+cx,canvasOffsetYpx+canvasDimensionYpx-cy,w,h,angle,canvasOffsetXpx+gridOffsetXpx,canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,canvasOffsetYpx+gridOffsetYpx);
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
function canvasDrawLine(A1px,B1px,A2px,B2px)
{ return jg.drawLine(canvasOffsetXpx+A1px,canvasOffsetYpx+canvasDimensionYpx-B1px,canvasOffsetXpx+A2px,canvasOffsetYpx+canvasDimensionYpx-B2px);
}
function canvasDrawPoint(Apx,Bpx)
{ return jg.drawLine(canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function canvasDrawString(theString,Apx,Bpx)
{ return jg.drawString(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function canvasDrawStringRect(theString,Apx,Bpx,w,h,theAlignment)
{ return jg.drawStringRect(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-h,w,theAlignment);
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
{ jg.setColor(canvasBkGroundColor);
	jg.fillRect(canvasOffsetXpx,canvasOffsetYpx,canvasDimensionXpx,canvasDimensionYpx);
  jg.drawRect(canvasOffsetXpx,canvasOffsetYpx,canvasDimensionXpx,canvasDimensionYpx);
	return 1;
}

//div functions ===========================================================================================================
function divInit(theDiv)
{ document.getElementById(theDiv).style.left  =divOffsetXpx+'px';
  document.getElementById(theDiv).style.top   =divOffsetYpx+'px';
  document.getElementById(theDiv).style.width =(div5Width-divOffsetXpx-divOffsetX2px)+'px';
  document.getElementById(theDiv).style.height=(div5Height-divOffsetYpx-divOffsetY2px)+'px';
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
	  canvasDrawLine(canvasDimensionXpx-gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx,gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx);
	  canvasDrawLine(gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx,gridOffsetXpx,gridOffsetYpx);
	}
}
function gridDrawCoordLines()
{ gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx);
  luLrad=gridLxRad;
  luDrad=gridDyRad;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx);
  ruLrad=gridLxRad;
  ruDrad=gridDyRad;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  ldLrad=gridLxRad;
  ldDrad=gridDyRad;
  gridLDinvRad(div5Left+divOffsetXpx+canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,div5Top+divOffsetYpx+canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  rdLrad=gridLxRad;
  rdDrad=gridDyRad;
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
	LStep=gridDimensions[gridActualDimension][2];
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
	{ for(l=LhrStart;l>RhrNeg;l-=LStep)
    {	l=Math.round(l*60)/60;
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
function gridDrawEllipseTilt(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg)
{ AngleDeg=(AngleDeg+90)%180;
  gridLDrad(Lhr,Ddeg); 
	x1=gridLxRad; y1=gridDyRad;
	canvasDrawEllipseTilt(gridCenterOffsetXpx+gridXpx(x1),gridCenterOffsetYpx+gridYpx(y1),Math.round((gridHeightYpx2*(Diam1Sec/3600/12*Math.PI)/gridSpanDrad)),Math.round((gridHeightYpx2*(Diam2Sec/3600/12*Math.PI)/gridSpanDrad)),(AngleDeg/180*Math.PI));
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
	
	var intersect=true;
	if((x1<-gridSpanLrad)&&(x2<-gridSpanLrad)) return;
	if((x1>gridSpanLrad)&&(x2>gridSpanLrad))   return;
	if((y1<-gridSpanDrad)&&(y2<-gridSpanDrad)) return;
	if((y1>gridSpanDrad)&&(y2>gridSpanDrad))   return;
	if(intersect&&(x1<-gridSpanLrad)) if(x2==x1) return; else {y1=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x1=-gridSpanLrad;}
	if(intersect&&(x1>gridSpanLrad))  if(x2==x1) return; else	{y1=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridSpanLrad; }
	if(intersect&&(y1>gridSpanDrad))  if(y2==y1) return; else	{x1=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridSpanDrad; }
	if(intersect&&(y1<-gridSpanDrad)) if(y2==y1) return; else {x1=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y1=-gridSpanDrad;}
	if((y1<-gridSpanDrad)||(y1>gridSpanDrad)||(x1<-gridSpanLrad)||(x1>gridSpanLrad)) return;	
	if(intersect&&(x2<-gridSpanLrad)) if(x2==x1) return; else {y2=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x2=-gridSpanLrad;}
	if(intersect&&(x2>gridSpanLrad))  if(x2==x1) return; else	{y2=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridSpanLrad;	}
  if(intersect&&(y2>gridSpanDrad))  if(y2==y1) return; else	{x2=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridSpanDrad;	}
	if(intersect&&(y2<-gridSpanDrad)) if(y2==y1) return; else	{x2=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y2=-gridSpanDrad;}
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
  { var element = document.createElement('input');
    element.setAttribute('type','image');
    element.setAttribute('name',hotZones[i]);
    element.setAttribute('src','styles/images/'+hotZones[i]+'.png');
    element.setAttribute('style','position:absolute;top:2px;left:'+(i*18+2)+'px;width:16px;height:16px;z-index:2;');
    element.setAttribute('title',eval(hotZones[i]+'Txt'));
    element.setAttribute('onclick',hotZones[i]+'Fn();');
    div=document.getElementById('myDiv');
    div.appendChild(element);
  }
}function gridXpx(Lrad) 
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
  return '('+dsl_hr+'h'+dsl_min+'m'+dsl_sec+'s, '+dsl_deg+'°'+dsl_amn+'\')';
}
// jg functions


// Utility functions
function roundPrecision(theValue,thePrecision)
{ return(Math.round(theValue/thePrecision)*thePrecision);
}