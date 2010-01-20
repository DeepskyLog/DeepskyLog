<?php
include_once "class.ezpdf.php";
class PrintAtlas
{ var $atlasmagnitude=0,
      $atlaspagedecldeg=0,
      $atlaspagerahr=0,
      $atlaspagezoomdeg=2,
      $by=0,
      $canvasDimensionXpx, 
      $canvasDimensionYpx,
      $canvasX2px,
      $coordGridsH,
      $diam1SecToPxCt = 1,
      $diam2SecToPxCt = 1,
      $f12OverPi  = 3.8197186342054880584532103209403,
      $f180OverPi = 57.295779513082320876798154814105,
      $fPiOver2   = 1.5707963267948966192313216916398,
      $fPiOver12  = 0.26179938779914943653855361527329,
      $fPiOver180 = 0.017453292519943295769236907684886,
      $f2Pi       = 6.283185307179586476925286766559,
      $fontSize1a =10, 
      $fontSize1b =6,
      $gridActualDimension=16,
      $gridCenterOffsetXpx,
      $gridCenterOffsetYpx,
      $gridD0rad,
      $griddDdeg,
      $gridDyRad,
      $gridL0rad,
      $gridldDdeg,
      $gridldLhr,
      $gridlLhr,
      $gridluDdeg,
      $gridluLhr,
      $gridLxRad,
      $gridMaxDimension=23,
      $gridMinDimension=0,
      $gridOffsetXpx=50, 
      $gridOffsetYpx=50,
      $gridrdDdeg,
      $gridrdLhr,
      $gridruDdeg,
      $gridruLhr,
      $gridSpanD,
      $gridSpanDrad,
      $gridSpanL,
      $gridSpanLrad,
      $griduDdeg,
      $gridWidthXpx, 
      $gridWidthXpx2=0, 
      $gridHeightYpx, 
      $gridHeightYpx2=0,
      $lx=0,
      $rx=0,
      $starsmagnitude,
      $ty=0; 
  
  var $pdf;
    
  var $gridDimensions=  Array(
    Array(180,80.00,2.000,3),                                                 // FoV, L grid distance in deg, D grid distance in deg, default limiting star magnitude level for this zoom level 
    Array(150,60.00,2.000,3),
    Array(120,50.00,2.000,3),
    Array( 90,40.00,2.666,4),
    Array( 75,30.00,2.000,4),
    Array( 60,24.50,1.666,5),
    Array( 45,20.00,1.333,5),
    Array( 35,15.00,1.000,6),
    Array( 30,12.00,0.800,6),
    Array( 25,10.00,0.666,6),
    Array( 20, 8.00,0.633,6),
    Array( 15, 6.00,0.400,7),
    Array( 10, 4.00,0.266,7),
    Array(  7, 3.00,0.200,8),
    Array(  5, 2.00,0.133,8),
    Array(  4, 1.50,0.100,9),
    Array(  3, 1.00,0.066,9),
    Array(  2, 0.80,0.050,10),
    Array(  1, 0.40,0.026,10),
    Array(0.5, 0.20,0.012,12),
    Array(0.25,0.20,0.012,14),
    Array(0.2 ,0.20,0.012,16),
    Array(0.15 ,0.20,0.012,16),
    Array(0.1 ,0.20,0.012,16)
  );

  function canvasDrawLabel($bkGrdColor,$textColor,$theLabel,$a,$b,$w,$h,$align)
  { //jg.setColor(bkGrdColor);
    //canvasFillRect(a,b,w,h);
    //jg.setColor(textColor);
    //canvasDrawStringRect(theLabel,a,b,w,h,align);
    $this->pdf->addTextWrap($a,$b,$w,$this->fontSize1a,$theLabel);
  }  
  
  
  function gridDrawCoordLines()
  { //jg.setFont("Lucida Console", fontSize1a+"px", Font.PLAIN);
    $this->gridLDinvRad($this->gridOffsetXpx,$this->gridOffsetYpx);
    $luLrad=$this->gridLxRad;
    $this->gridluLhr=$luLrad*$this->f12OverPi;
    $luDrad=$this->gridDyRad;
    $this->gridluDdeg=$luDrad*$this->f180OverPi;
    $this->gridLDinvRad($this->gridOffsetXpx+$this->gridWidthXpx,$this->gridOffsetYpx);
    $ruLrad=$this->gridLxRad;
    $this->gridruLhr=$ruLrad*$this->f12OverPi;
    $ruDrad=$this->gridDyRad;
    $this->gridruDdeg=$ruDrad*$this->f180OverPi;
    gridLDinvRad($this->gridOffsetXpx,$this->gridOffsetYpx+$this->gridHeightYpx);
    $ldLrad=$this->gridLxRad;
    $this->gridldLhr=$ldLrad*$this->f12OverPi;
    $ldDrad=$this->gridDyRad;
    $this->gridldDdeg=$ldDrad*$this->f180OverPi;
    gridLDinvRad($this->gridOffsetXpx+$this->gridWidthXpx,$this->gridOffsetYpx+$this->gridHeightYpx);
    $rdLrad=$this->gridLxRad;
    $this->gridrdLhr=$rdLrad*$this->f12OverPi;
    $rdDrad=$this->gridDyRad;
    $this->gridrdDdeg=$rdDrad*$this->f180OverPi;
    
    gridLDinvRad($this->gridOffsetXpx+(($this->gridWidthXpx+1)>>1),$this->gridOffsetYpx);
    $this->griduDdeg=$this->gridDyRad*$this->f180OverPi;
    gridLDinvRad($this->gridOffsetXpx+$this->gridWidthXpx,$this->gridOffsetYpx+$this->gridHeightYpx);
    $this->griddDdeg=$this->gridDyRad*$this->f180OverPi;
  
    if((($this->gridD0rad+$this->gridSpanDrad)<($this->fPiOver2))&&(($this->gridD0rad-$this->gridSpanDrad)>-($this->fPiOver2)))
    { if($this->gridD0rad>0)
      { $Lrad=$luLrad;
        $Rrad=$ruLrad;
      }
      else
      { $Lrad=$ldLrad;
        $Rrad=$rdLrad;
      }
      if($Lrad<$Rrad)
        $Rrad-=($this->f2Pi);
      $Urad=Max($this->gridD0rad+$this->gridSpanDrad,Max($luDrad,$ruDrad));
      $Drad=Min($this->gridD0rad-$this->gridSpanDrad,Min($ldDrad,$rdDrad));
      $Lhr=$Lrad*$this->f12OverPi;
      $RhrNeg=$Rrad*$this->f12OverPi;
      $Udeg=$Urad*$this->f180OverPi;
      $Ddeg=$Drad*$this->f180OverPi;
    }
    else if(($this->gridD0rad+$this->gridSpanDrad)>=($this->fPiOver2))
    { $Lhr=24;
      $RhrNeg=0;
      $Udeg=90;
      $Ddeg=Min($this->gridD0rad-$this->gridSpanDrad,Min($ldDrad,$rdDrad))*$this->f180OverPi;
      $griduDdeg=90;
    }
    else if(($this->gridD0rad-$this->gridSpanDrad)<=-($this->fPiOver2))
    { $Lhr=24;
      $RhrNeg=0;
      $Udeg=Max($this->gridD0rad+$this->gridSpanDrad,Max($luDrad,$ruDrad))*$this->f180OverPi;
      $Ddeg=-90;
      $griddDdeg=-90;
    }
  

    $griduDdeg=$Udeg;
    $griddDdeg=$Ddeg;
    
    $DLhr=($Lhr-$RhrNeg);
    $LStep=Min(round((($this->gridDimensions[$this->gridActualDimension][2]/cos($this->gridD0rad))*60)/60),2);
    $DDdeg=($Udeg-$Ddeg);
    $DStep=$this->gridDimensions[$this->gridActualDimension][1];
    
    $LhrStart=(floor($Lhr/$LStep)+1)*$LStep;
    $DdegStart=(floor($Ddeg/$DStep)+1)*$DStep;
  
    $this->gridlLhr=$Lhr;
    $gridrLhr=($RhrNeg<0?($RhrNeg+24):$RhrNeg);
  
    for($d=$DdegStart;$d<=$Udeg;$d+=$DStep)
    { $d=round(d*60)/60;
      $this->canvasX2px=0;
      //jg.setColor(coordLineColor);
      for($l=$Lhr;$l>$RhrNeg;$l-=$LStep/$Lsteps)
        $this->gridDrawLineLD($l,$d,($l-($LStep/$Lsteps)),$d);
      if($canvasX2px&&($canvasX2px>=$this->gridOffsetXpx+$this->gridWidthXpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
      else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
      else if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,gridOffsetYpx-8,60,15,'center');
      else if(canvasX2px)
        canvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,canvasY2px-17,60,15,'center');
    }
    /*
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
    }*/
  }
  

  
function gridDrawLineLD($Lhr1,$Ddeg1,$Lhr2,$Ddeg2)
{ /* gridLDrad(Lhr1,Ddeg1); x1=gridLxRad; y1=gridDyRad;
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
  return 1;*/

}
  
  private function gridInit()
  { $this->canvasDimensionXpx=$this->pdf->ez['pageWidth']; 
    $this->canvasDimensionYpx=$this->pdf->ez['pageHeight'];
    $this->gridOffsetXpx=50; $this->gridOffsetYpx=50;
    
    $this->gridWidthXpx=$this->canvasDimensionXpx-($this->gridOffsetXpx<<1);
    $this->gridWidthXpx2=(($this->gridWidthXpx+1)>>1);
    $this->gridCenterOffsetXpx=$this->gridOffsetXpx+(($this->gridWidthXpx+1)>>1);
    $this->gridHeightYpx=$this->canvasDimensionYpx-($this->gridOffsetYpx<<1);
    $this->gridHeightYpx2=(($this->gridHeightYpx+1)>>1);
    $this->gridCenterOffsetYpx=$this->gridOffsetYpx+(($this->gridHeightYpx+1)>>1);
    $this->lx = $this->gridOffsetXpx;
    $this->rx = $this->gridOffsetXpx+$this->gridWidthXpx;
    $this->ty = $this->gridOffsetYpx+$this->gridHeightYpx;
    $this->by = $this->gridOffsetYpx;
  }
  
  function gridInitScale($gridLHr,$gridDdeg,$desiredScale)
  { $this->gridActualDimension=$this->gridMaxDimension;
    while(($this->gridActualDimension>$this->gridMinDimension)&&($this->gridDimensions[$this->gridActualDimension][0]<$desiredScale))
      $this->gridActualDimension=$this->gridActualDimension-1;
    $this->gridL0rad=$gridLHr*$this->fPiOver12;
    $this->gridD0rad=$gridDdeg*$this->fPiOver180;
    if($this->gridWidthXpx<$this->gridHeightYpx)
    { $this->gridSpanD=$this->gridDimensions[$this->gridActualDimension][0]*($this->gridHeightYpx/$this->gridWidthXpx);
      $this->gridSpanL=$this->gridDimensions[$this->gridActualDimension][0];
    }
    else
    { $this->gridSpanD=$this->gridDimensions[$this->gridActualDimension][0];
      $this->gridSpanL=$this->gridDimensions[$this->gridActualDimension][0]*($this->gridWidthXpx/$this->gridHeightYpx);
    }
    $this->gridSpanLrad=$this->gridSpanL*$this->fPiOver180;
    $this->gridSpanDrad=$this->gridSpanD*$this->fPiOver180;
    $this->atlaspagezoomdeg=$this->gridDimensions[$this->gridActualDimension][0];
    $this->diam1SecToPxCt=(($this->gridWidthXpx2/3600)/$this->gridSpanL);
    $this->diam2SecToPxCt=(($this->gridHeightYpx2/3600)/$this->gridSpanD);
  }
  
  function gridLDinvRad($XpxAbsScr,$YpxAbsScr)
  { /*var $xRad=-(($XpxAbsScr-$this->gridCenterOffsetXpx)/gridWidthXpx2*gridSpanLrad);
    var $yRad=((gridCenterOffsetYpx+canvasOffsetYpx+divOffsetYpx+div5Top-YpxAbsScr)/gridHeightYpx2*gridSpanDrad);
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
      gridLxRad=gridLxRad-(f2Pi);*/
  }  
  
  public  function pdfAtlas($rarad, $declrad, $raspanrad, $declspanrad, $dsomag, $starmag)  // Creates a pdf atlas page
  { global $objUtil;
  
    $this->atlaspagerahr=$objUtil->checkRequestKey('atlaspagerahr',0);
    $this->atlaspagedecldeg=$objUtil->checkRequestKey('atlaspagedecldeg',0);
    $this->atlaspagezoomdeg=$objUtil->checkRequestKey('atlaspagezoomdeg',1);
    $this->atlasmagnitude=$objUtil->checkRequestKey('atlasmagnitude',10);
    $this->starsmagnitude=$objUtil->checkRequestKey('starsmagnitude',10);
    
    $this->pdf = new Cezpdf('a4', 'landscape');
    $this->pdf->selectFont($instDir.'lib/fonts/Helvetica.afm');
    $this->gridInit();
    $this->gridInitScale($this->atlaspagerahr,$this->atlaspagedecldeg,$this->atlaspagezoomdeg);

    $this->pdf->rectangle($this->gridOffsetXpx,$this->gridOffsetYpx,
                         ($this->canvasDimensionXpx-($this->gridOffsetXpx<<1)),($this->canvasDimensionYpx-($this->gridOffsetYpx<<1)));
    $this->pdf->addText(50,$this->gridHeightYpx-10,10,"DeepskyLog Atlas Page for location ".$this->atlaspagerahr.' '.$this->atlaspagedecldeg.' to magnitude '.$this->atlasmagnitude);
    
    
    $this->pdf->Stream(); 
  }

}
$objPrintAtlas = new PrintAtlas;
?>