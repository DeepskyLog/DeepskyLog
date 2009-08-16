


// function canvasOnClick(canvas)
// function canvasOnMouseMove(canvas)

// function coordHrDecToHrMin(theHr)
// function coordDegDecToDegMin(theDeg)


// function jgCanvasDrawLine(X1,Y1,X2,Y2);										  								// Draws a line in absolute coordinates relative to the canvas;
// function jgCanvasDrawPoint(X,Y);																							// Draws a point in absolute coordinates relative to the canvas;
// function jgCanvasDrawString(theString,Apx,Bpx);
// function jgCanvasInit(canvas,originX,originY,widthX,heightY,BkGroundColor);  // Initialises the canvas;
// function jgGridClearBorder();																								// Clears the grid border;
// function jgGridDraw();																												// draws the grid;
// function jgGridDrawLine(X1,Y1,X2,Y2);																				// draws a line in the grid coordinates
// function jgGridInit(goX,goY,gX0,gY0,gX1,gY1);																// Initialises the grid offsets in X and Y, and the grid coordinate limits (X0,Y0) (X1,Y1);
// function jgGridLDRad(L,D);																										// Calculate (ra,decl) to (X,Y)
// function jgGridSetBorderColor(theColor);																			// Sets the grid border color;
// function jgGridSetLD(theL0,theD0,theL1,theD1);                               // Sets the grid ra and decl coordinates;
// function jgPaint();																													// Draws the canvas and contents;
// function jgSetDrawColor(theColor);																						// Sets the drawing color, new objects will be drawn in this color;

var cnv;
var jg;	

var astroObjects = new Array();
var astroObjectsHotZones = new Array();

var onClickHandling=false;	                                                                      // Java Graphics object;

// Positioning Parameters
var canvasDimensionXpx,  canvasDimensionYpx;                                    // Canvas Dimension X and Dimension Y: width and height of the canvas;
var gridBorder=true;     gridCoordLines=true;						            // Grid border (true or false)
var gridCenterOffsetXpx, gridCenterOffsetYpx;                                   // Grid center offset X and offset Y: distance obetween the grid and the canvas border;
var gridL0rad,           gridD0rad;		                                        // Grid center coordinates ra and decl
var gridWidthXpx,        gridHeightYpx;                                         // Grid dimensions in X and Y;
var gridSpanLrad,        gridSpanDrad;  			                            // Grid span in L and D in rad
var gridXpx,             gridYpx;

var atlaspagera=0, atlaspagedecl=0, atlaspagezoom=10;

var divOffsetXpx =0;
var divOffsetYpx =0;
var divOffsetX2px=0;
var divOffsetY2px=0;
var canvasOffsetXpx=0;
var canvasOffsetYpx=0;
var gridOffsetXpx=80;
var gridOffsetYpx=50;

// Color Parameters
var buttonBkColor          ='#AAAAAA';
var buttonTextColor        ='#FFFF00';
var canvasBkGroundColor    ='#000000';                                          // Background color of the canvas;
var coordLineColor         ='#EE0000';                                          // Coordinate grid line colors
var coordLblBkGroundColor  ='#000000';                                          // Background Color of coordinate Labels
var coordLblColor          ='#DDDDDD';                                          // Color of coordinate labels
var coordBkGroundColor     ='#000000';                                          // Background color of coordinates of mouse position
var coordColor             ='#AAAAAA';                                          // Color of coordinates of mouse position
var gridBorderColor        ='#FFFF00';                                          // Color of the grid border
var starColor              ='#FFFF00';
var gridHeightYpx2         =0;

// Layout Parameters
var coordGridsH, coordGridsV;                                                   // Obsolete - Number of grid lines H and V
var coordCnvXpx, coorCnvYpx;                                                    // Location of mouse coordinate positions
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
var canvasX1px, canvasY1px,      canvasX2px,     canvasY2px;                    // fn jgGridDrawLineLD     points from - to where the line is drawn (= intersection with the grid)
var gridLx1rad, gridDy1rad,      gridLx2rad,     gridDy2rad;                    // fn jgGridDrawLineLD     points from - to where the line is drawn (= intersection with the grid)
var gridLxRad,  gridDyRad;



// Astro functions
function astroDrawStar(Lhr,Ddeg,mag)
{ d=Math.round((gridActualDimension-mag-1)*1.5);
	if(d<=0) return;
	jgSetDrawColor(starColor);
	jgGridDrawFilledCirclePx(Lhr,Ddeg,d);
}
function astroDrawDStar(Lhr,Ddeg,mag)
{ d=Math.round((gridActualDimension-mag-1)*1.5);
	e=Math.max(Math.round(d*1.2),d+2);
	if(d<=0) return false;
	jgSetDrawColor(starColor);
	if(jgGridDrawFilledCirclePx(Lhr,Ddeg,d))
  	jgGridDrawLinePxLR(Lhr,Ddeg,d);
	return true;
}

function atlasPageUpBtnFn()
{ atlaspagedecl=atlaspagedecl+(gridSpanD * 1.6);
  if(atlaspagedecl>(90-(gridSpanD/2))) atlaspagedecl=90-(gridSpanD/2);
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageSmallUpBtnFn()
{ atlaspagedecl=atlaspagedecl+(gridSpanD * 0.4);
  if(atlaspagedecl>(90-(gridSpanD/2))) atlaspagedecl=90-(gridSpanD/2);
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageDownBtnFn()
{ atlaspagedecl=atlaspagedecl-(gridSpanD * 1.6);
  if(atlaspagedecl<(-90+(gridSpanD/2))) atlaspagedecl=(-90+(gridSpanD/2));
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageSmallDownBtnFn()
{ atlaspagedecl=atlaspagedecl-(gridSpanD * 0.4);
  if(atlaspagedecl<(-90+(gridSpanD/2))) atlaspagedecl=(-90+(gridSpanD/2));
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageLeftBtnFn()
{ atlaspagera=atlaspagera+(gridSpanL * 0.05333);
  if(atlaspagera<0) atlaspagera+=24;
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageSmallLeftBtnFn()
{ atlaspagera=atlaspagera+(gridSpanL * 0.01333);
  if(atlaspagera<0) atlaspagera+=24;
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageRightBtnFn()
{ atlaspagera=atlaspagera-(gridSpanL * 0.05333);
  if(atlaspagera>24) atlaspagera-=24;
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageSmallRightBtnFn()
{ atlaspagera=atlaspagera-(gridSpanL * 0.01333);
  if(atlaspagera>24) atlaspagera-=24;
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageZoomInBtnFn()
{ atlaspagezoom/=2;
  if(atlaspagezoom<1) atlaspagezoom=1;
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function atlasPageZoomOutBtnFn()
{ atlaspagezoom*=2;
  if(atlaspagezoom>120) atlaspagezoom=120;
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}



// Canvas actions
function canvasCursor(theCursor)
{ cnv.style.cursor = theCursor;
}
function canvasOnClick(event)
{ if(onClickHandling==true)
    return;
	onClickHandling=true;
	//alert('X:'+event.clientX);
  x=event.clientX-canvasOffsetXpx-div5Left-2;
  y=canvasDimensionYpx-event.clientY+div5Top+2;
	jgPaint();
  onClickHandling=false;
}
function canvasOnMouseMove(event)
{ x=event.clientX-cnv.offsetLeft-div5Left;
  y=event.clientY-cnv.offsetTop-div5Top;
	if((x>canvasOffsetXpx+gridOffsetXpx)&&(x<canvasOffsetXpx+gridOffsetXpx+gridWidthXpx)&&(y>canvasOffsetYpx+gridOffsetYpx)&&(y<canvasOffsetYpx+gridOffsetYpx+gridHeightYpx))
	{	jgGridLDinvRad(x,y);
	  jgCanvasDrawLabel(coordBkGroundColor,coordColor,coordGridLxDyToString(),2,2,150,15,'center')
	}
  jgPaint();
}
// Presentation functions
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
function fillAtlasPage()
{ thelocation="index.php?indexAction=atlaspage&object=M+71";
  initDiv('myDiv');
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(atlaspagera,atlaspagedecl,atlaspagezoom);
  jgGridSetHotZones();
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
}
function initDiv(theDiv)
{ document.getElementById(theDiv).style.left  =divOffsetXpx+'px';
  document.getElementById(theDiv).style.top   =divOffsetYpx+'px';
  document.getElementById(theDiv).style.width =(div5Width-divOffsetXpx-divOffsetX2px)+'px';
  document.getElementById(theDiv).style.height=(div5Height-divOffsetYpx-divOffsetY2px)+'px';
}
// Canvas graphics functions
function jgCanvasDrawButton(x,y,w,h,theText)
{ jgSetDrawColor(buttonBkColor);
  jgCanvasFillRect(x,y,w,h);
	jgCanvasDrawLabel(buttonBkColor,buttonTextColor,theText,x+2,y+2,w-4,h-4,'center');
}
function jgCanvasDrawEllipseTilt(cx,cy,w,h,angle)
{	angle=-angle;
  jg.drawEllipseTiltLimited(canvasOffsetXpx+cx,canvasOffsetYpx+canvasDimensionYpx-cy,w,h,angle,canvasOffsetXpx+gridOffsetXpx,canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,canvasOffsetYpx+gridOffsetYpx);
}
function jgCanvasDrawFilledCircle(cx,cy,d)
{	return jg.fillEllipse(Math.round(canvasOffsetXpx+cx-(d/2)),Math.round(canvasOffsetYpx+canvasDimensionYpx-cy-(d/2)),d,d);
}
function jgCanvasDrawLabel(bkGrdColor,textColor,theLabel,a,b,w,h,align)
{ jgSetDrawColor(bkGrdColor);
	jgCanvasFillRect(a,b,w,h);
	jgSetDrawColor(textColor);
	jgCanvasDrawStringRect(theLabel,a,b,w,h,align);
}
function jgCanvasDrawLine(A1px,B1px,A2px,B2px)
{ return jg.drawLine(canvasOffsetXpx+A1px,canvasOffsetYpx+canvasDimensionYpx-B1px,canvasOffsetXpx+A2px,canvasOffsetYpx+canvasDimensionYpx-B2px);
}
function jgCanvasDrawPoint(Apx,Bpx)
{ return jg.drawLine(canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function jgCanvasDrawString(theString,Apx,Bpx)
{ return jg.drawString(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function jgCanvasDrawStringRect(theString,Apx,Bpx,w,h,theAlignment)
{ return jg.drawStringRect(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-h,w,theAlignment);
}
function jgCanvasFillRect(Apx,Bpx,Widthpx,Heightpx)
{ return jg.fillRect(canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-Heightpx,Widthpx,Heightpx);
}
function jgCanvasInit(canvas)
{ canvasDimensionXpx=((div5Width-divOffsetXpx-divOffsetX2px)-(2*canvasOffsetXpx)-2);
  canvasDimensionYpx=((div5Height-divOffsetYpx-divOffsetY2px)-(2*canvasOffsetYpx)-2);
  cnv=document.getElementById(canvas);
  if(!cnv) return false;
	return jg=new jsGraphics(cnv);
}
function jgCanvasRedraw()
{ jg.setColor(canvasBkGroundColor);
	jg.fillRect(canvasOffsetXpx,canvasOffsetYpx,canvasDimensionXpx,canvasDimensionYpx);
  jg.drawRect(canvasOffsetXpx,canvasOffsetYpx,canvasDimensionXpx,canvasDimensionYpx);
	return jg;
}
// Grid graphics functions
function jgGridClearBorder()
{ gridBorder=false;
}
function jgGridDrawBorder()
{ if(gridBorder)
  { jgSetDrawColor(gridBorderColor);
	  jgCanvasDrawLine(gridOffsetXpx,gridOffsetYpx,canvasDimensionXpx-gridOffsetXpx,gridOffsetYpx);
	  jgCanvasDrawLine(canvasDimensionXpx-gridOffsetXpx,gridOffsetYpx,canvasDimensionXpx-gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx);
	  jgCanvasDrawLine(canvasDimensionXpx-gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx,gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx);
	  jgCanvasDrawLine(gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx,gridOffsetXpx,gridOffsetYpx);
	}
}
function jgGridDrawCoordLines()
{ jgGridLDinvRad(canvasOffsetXpx+gridOffsetXpx,canvasOffsetYpx+gridOffsetYpx);
  luLrad=gridLxRad;
  luDrad=gridDyRad;
  jgGridLDinvRad(canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx);
  ruLrad=gridLxRad;
  ruDrad=gridDyRad;
  jgGridLDinvRad(canvasOffsetXpx+gridOffsetXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  ldLrad=gridLxRad;
  ldDrad=gridDyRad;
  jgGridLDinvRad(canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
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
	{ canvasX2px=0;
	  jgSetDrawColor(coordLineColor);
    for(l=Lhr;l>RhrNeg;l-=LStep/Lsteps)
      jgGridDrawLineLD(l,d,(l-(LStep/Lsteps)),d);
    if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx))
      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
		else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
	    jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
		else if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,gridOffsetYpx-8,60,15,'center');
    else if(canvasX2px)
	    jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,canvasY2px-17,60,15,'center');
	}
  if(gridD0rad<0)
	{ for(l=LhrStart;l>RhrNeg;l-=LStep)
    {	canvasX2px=0;
	    jgSetDrawColor(coordLineColor);
      for(d=Ddeg;d<Udeg;d+=DStep/Dsteps)
	      jgGridDrawLineLD(l,d,l,(d+(DStep/Dsteps)));
      if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx-17,60,15,'center');
			else if(canvasX2px&&(canvasX2px<=gridOffsetXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx-62,canvasY2px-8,60,15,'right');
			else if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
			else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
	  }
  }
	else
	{ for(l=LhrStart;l>RhrNeg;l-=LStep)
    {	canvasX2px=0;
	    jgSetDrawColor(coordLineColor);
      for(d=Udeg;d>Ddeg;d-=DStep/Dsteps)
	      jgGridDrawLineLD(l,d,l,(d-(DStep/Dsteps)));
      if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx-17,60,15,'center');
			else if(canvasX2px&&(canvasX2px<=gridOffsetXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx-62,canvasY2px-8,60,15,'right');
			else if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
			else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
	  }
	}
}
function jgGridDrawEllipseTilt(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg)
{ AngleDeg=(AngleDeg+90)%180;
  jgGridLDrad(Lhr,Ddeg); 
	x1=gridLxRad; y1=gridDyRad;
	jgCanvasDrawEllipseTilt(gridCenterOffsetXpx+jgGridXpx(x1),gridCenterOffsetYpx+jgGridYpx(y1),Math.round((gridHeightYpx2*(Diam1Sec/3600/12*Math.PI)/gridSpanDrad)),Math.round((gridHeightYpx2*(Diam2Sec/3600/12*Math.PI)/gridSpanDrad)),(AngleDeg/180*Math.PI));
}
function jgGridDrawFilledCirclePx(Lhr,Ddeg,DiamPx)
{ jgGridLDrad(Lhr,Ddeg); 
	cx=gridCenterOffsetXpx+jgGridXpx(gridLxRad);
	cy=gridCenterOffsetYpx+jgGridYpx(gridDyRad);
  if((cx-DiamPx<gridOffsetXpx)||(cx+DiamPx>gridOffsetXpx+gridWidthXpx)) return false;
	if((cy+DiamPx>gridOffsetYpx+gridHeightYpx)||(cy-DiamPx<gridOffsetYpx)) return false;
  jgCanvasDrawFilledCircle(cx,cy,DiamPx,DiamPx);
	return true;
}
function jgGridDrawLinePxLR(Lhr,Ddeg,d)
{ jgGridLDrad(Lhr,Ddeg); 
	cx=gridCenterOffsetXpx+jgGridXpx(gridLxRad);
	cy=gridCenterOffsetYpx+jgGridYpx(gridDyRad);
  if((cx-d<gridOffsetXpx)||(cx+d>gridOffsetXpx+gridWidthXpx)) return;
	if((cy>gridOffsetYpx+gridHeightYpx)||(cy<gridOffsetYpx)) return;
  jgCanvasDrawLine(cx-d,cy,cx+d,cy);
}
function jgGridDrawLineLD(Lhr1,Ddeg1,Lhr2,Ddeg2)
{ jgGridLDrad(Lhr1,Ddeg1); x1=gridLxRad; y1=gridDyRad;
	jgGridLDrad(Lhr2,Ddeg2); x2=gridLxRad; y2=gridDyRad;
	
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
	
	canvasX1px=gridCenterOffsetXpx+jgGridXpx(x1);
  canvasY1px=gridCenterOffsetYpx+jgGridYpx(y1);
  canvasX2px=gridCenterOffsetXpx+jgGridXpx(x2);
  canvasY2px=gridCenterOffsetYpx+jgGridYpx(y2);
	gridLx1rad=x1;gridDy1rad=y1;gridLx2rad=x2;gridDy2rad=y2;
	jgCanvasDrawLine(canvasX1px,canvasY1px,canvasX2px,canvasY2px);
}
function jgGridDrawPointLD(Lhr,Ddeg)
{ jgGridLDrad(Lhr,Ddeg);
  if((gridLxRad>-gridSpanLrad)&&(gridLxRad<gridSpanLrad)&&(gridDyRad>-gridSpanDrad)&&(gridDyRad<gridSpanDrad))
	  return jgCanvasDrawPoint(gridCenterOffsetXpx+jgGridXpx(gridLxRad),gridCenterOffsetYpx+jgGridYpx(gridDyRad));
  else return 0;
}
function jgGridInit()
{	gridWidthXpx=canvasDimensionXpx-gridOffsetXpx-gridOffsetXpx;
	gridWidthXpx2=Math.round(gridWidthXpx/2);
	gridCenterOffsetXpx=gridOffsetXpx+Math.round(gridWidthXpx/2);
	gridHeightYpx=canvasDimensionYpx-gridOffsetYpx-gridOffsetYpx;
	gridHeightYpx2=Math.round(gridHeightYpx/2);
	gridCenterOffsetYpx=gridOffsetYpx+Math.round(gridHeightYpx/2);
}
function jgGridInitScale(gridLHr,gridDdeg,desiredScale)
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
function jgGridInitScaleFixed(gridLHr,gridDdeg,desiredScale)
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
function jgGridLDrad(Lhr,Ddeg)
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
function jgGridLDinvRad(Xpx,Ypx)
{ xRad=-((Xpx-gridCenterOffsetXpx-canvasOffsetXpx)/gridWidthXpx2*gridSpanLrad);
  yRad=((gridCenterOffsetYpx+canvasOffsetYpx-Ypx)/gridHeightYpx2*gridSpanDrad);
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
function jgGridMove(Direction)
{ if(Direction=='Left')
  { gridL0rad+=gridSpanLrad;
	  if(gridL0rad>=2*Math.PI)
		  gridL0rad-=2*Math.PI;
  }
  if(Direction=='Right')
  { gridL0rad-=gridSpanLrad;
	  if(gridL0rad<0)
		  gridL0rad+=2*Math.PI;
	}
  if(Direction=='Up')
  { gridD0rad+=gridSpanDrad;
	  if(gridD0rad>=Math.PI)
		  gridD0rad=Math.PI;
	}
  if(Direction=='Down')
  { gridD0rad-=gridSpanDrad;
	  if(gridD0rad<-Math.PI)
		  gridD0rad=-Math.PI;
	}
}
function jgGridRedraw()
{ jgCanvasRedraw();
  jgGridSetBorderColor(gridBorderColor);
  if(gridCoordLines)
	  jgGridDrawCoordLines();
	jgDrawAstroObjects();
	jgGridDrawBorder();
}
function jgGridSetBorderColor(theColor)
{ gridBorder=true;
  gridBorderColor=theColor;
}
function jgGridXpx(Lrad) 
{ return Math.round((gridWidthXpx2*Lrad/gridSpanLrad));
}
function jgGridYpx(Drad)
{ return Math.round((gridHeightYpx2*Drad/gridSpanDrad));
}
function jgGridZoomIn(zoomFactor)
{ jgGridInitScaleFixed(gridL0rad/Math.PI*12,gridD0rad/Math.PI*180,Math.max(Math.min(gridActualDimension+zoomFactor,gridMaxDimension),gridMinDimension));
  jgGridRedraw();
}
function jgGridSetHotZones()
{ for(i in hotZones)
  { var element = document.createElement('input');
    element.setAttribute('type','image');
    element.setAttribute('name','Up');
    element.setAttribute('src','styles/images/'+hotZones[i]+'.png');
    element.setAttribute('style','position:absolute;top:2px;left:'+(i*18+2)+'px;width:16px;height:16px;z-index:2;');
    element.setAttribute('title','page up');
    element.setAttribute('onclick',hotZones[i]+'Fn();');
    div=document.getElementById('myDiv');
    div.appendChild(element);
  }
}

// jg functions
function jgDrawAstroObjects()
{ for(i=0;i<astroObjects.length;i++)
  { if(astroObjects[i][0]=='Star')
      astroDrawStar(astroObjects[i][1],astroObjects[i][2],astroObjects[i][6]);
    if(astroObjects[i][0]=='DStar')
      astroDrawDStar(astroObjects[i][1],astroObjects[i][2],astroObjects[i][6]);
	}
}
function jgPaint()
{ return jg.paint();
}
function jgSetDrawColor(theColor)
{ return jg.setColor(theColor);
}
function jgSetAstro(objectType,x,y,diam1,diam2,pa,mag,sb,objectname,altnames,seen,lastseendate)
{ astroObjects[astroObjects.length]=new Array(objectType,x,y,diam1,diam2,pa,mag,sb,objectname,altnames,seen,lastseendate);
}

function jgGridSetAstroObjects()
{  // Demo data
  jgSetAstro('Star',0,    0.0,0,0,0,8, 0,'Alcor','B UMa', 'X(5)',   '');
	jgSetAstro('Star',0.12, 0.1,0,0,0,10,0,'Mizar','24 UMa','X(4)',   '');
	jgSetAstro('Star',0.14, 0.2,0,0,0,6, 0,'Deneb','A Cyg', 'Y(12/5)','20090105');
	jgSetAstro('Star',0.16, 0.3,0,0,0,4, 0,'Dubhe','D UMa', 'Y(56/3)','20090230');
	jgSetAstro('Star',23.95,0.3,0,0,0,12,0,'Alfar','C Her', 'X(6)',   '');
	jgSetAstro('Star',23.90,0.2,0,0,0,14,0,'Vega' ,'D Vul', '-',      '');
	jgSetAstro('Star',23.97,0.1,0,0,0,16,0,'Arctu','A Boo', '-',      '');

  jgSetAstro('DStar',0.06,-0.1,0,0,0,10,0,'Arctu2','B Boo', '-',      '');
}
function roundPrecision(theValue,thePrecision)
{ return(Math.round(theValue/thePrecision)*thePrecision);
}

