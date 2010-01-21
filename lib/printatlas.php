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
      $canvasX1px, 
      $canvasX2px,
      $canvasY1px,
      $canvasY2px,
      $coordGridsH,
      $diam1SecToPxCt = 1,
      $diam2SecToPxCt = 1,
      $dsl_amn,
      $dsl_asc,
      $dsl_deg,                                                   // fn coordDeclDecToDegMin results
      $dsl_hr,     
      $dsl_min,         
      $dsl_sec,                                       // fn coordHrDecToHrMin    results
      $Dsteps=10,                                                     // Number of steps for drawing coordinate lines between major steps
      $f12OverPi  = 3.8197186342054880584532103209403,
      $f180OverPi = 57.295779513082320876798154814105,
      $fPi        = 3.1415926535,
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
      $gridDy1rad,      
      $gridDy2rad,
      $gridL0rad,
      $gridldDdeg,
      $gridldLhr,
      $gridlLhr,
      $gridluDdeg,
      $gridluLhr,
      $gridLxRad,
      $gridLx1rad, 
      $gridLx2rad,     
      $gridMaxDimension=23,
      $gridMinDimension=0,
      $gridOffsetXpx=50, 
      $gridOffsetYpx=50,
      $gridrdDdeg,
      $gridrdLhr,
      $gridrLhr,
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
      $Lsteps=10,
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

  function canvasDrawLabel($x,$y,$w,$s,$theLabel,$align)
  { //jg.setColor(bkGrdColor);
    //canvasFillRect(a,b,w,h);
    //jg.setColor(textColor);
    //canvasDrawStringRect(theLabel,a,b,w,h,align);
    $this->pdf->addTextWrap($x,$y,$w,$s,$theLabel,$align);
  }  
	  
	function coordDeclDecToDegMin($theDeg)
	{ if($theDeg>90) $theDeg=90;
	  if($theDeg<-90) $theDeg=-90;
	  $sign='';
	  if($theDeg<0) {$theDeg=-$theDeg; $sign='-';}
	  $this->dsl_deg=floor($theDeg);
	  $this->dsl_amn=round(($theDeg-$this->dsl_deg)*60);
	  if($this->dsl_amn==60)
	  { $this->dsl_amn=0;
	    ++$this->dsl_deg;
	  }
	  $this->dsl_deg=$sign.$this->dsl_deg;
	  if($this->dsl_amn>0)
	    return $this->dsl_deg.'°'.$this->dsl_amn.'\'';
	  return $this->dsl_deg.'°';
	}  
	
  function coordHrDecToHrMin($theHr)
  { while(($theHr)>24) $theHr-=24;
    while(($theHr)<0)  $theHr+=24;
    $dsl_hr=floor($theHr);
    $dsl_min=round(($theHr-$dsl_hr)*60);
    if($dsl_min==60)
    { $dsl_min=0;
      ++$dsl_hr;
      if($dsl_hr==24)
        $dsl_hr=0;
    }
    if($dsl_min>0)
     return $dsl_hr.'h'.$dsl_min.'m';
    return $dsl_hr.'h';
  }
	  
	function coordHrDecToHrMinSec($theHr)
	{ while(($theHr)>24) $theHr-=24;
		while(($theHr)<0)  $theHr+=24;
		$this->dsl_hr=floor($theHr);
		$this->dsl_min=floor(($theHr-$this->dsl_hr)*60);
		$this->dsl_sec=$this->roundPrecision(($theHr-$this->dsl_hr-($this->dsl_min/60))*3600,10);
		if($this->dsl_sec==60)
		{ ++$this->dsl_min;
		  $this->dsl_sec=0;
		}
		if($this->dsl_min==60)
		{ $this->dsl_min=0;
		  ++$this->dsl_hr;
		}
		if($this->dsl_hr==24)
		 $this->dsl_hr=0;
		if($this->dsl_sec>0)
		 return $this->dsl_hr.'h'.$this->dsl_min.'m'.$this->dsl_sec.'s';
		else if($this->dsl_min>0)
		 return $this->dsl_hr.'h'.$this->dsl_min.'m';
		return $this->dsl_hr.'h';
	}
	
	function coordGridLxDyToString()
	{ $this->coordHrDecToHrMinSec($this->gridLxRad*$this->f12OverPi);
	  $this->coordDeclDecToDegMin($this->gridDyRad*$this->f180OverPi);
	  return sprintf('%02d',$this->dsl_hr).'h'.sprintf('%02d',$this->dsl_min).'m'.sprintf('%02d',$this->dsl_sec).'s,'.sprintf('%02d',$this->dsl_deg).'°'.sprintf('%02d',$this->dsl_amn).'\'';
	}
  
  function gridDrawCoordLines()
  { //jg.setFont("Lucida Console", fontSize1a."px", Font.PLAIN);
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
    $this->gridLDinvRad($this->gridOffsetXpx,$this->gridOffsetYpx+$this->gridHeightYpx);
    $ldLrad=$this->gridLxRad;
    $this->gridldLhr=$ldLrad*$this->f12OverPi;
    $ldDrad=$this->gridDyRad;
    $this->gridldDdeg=$ldDrad*$this->f180OverPi;
    $this->gridLDinvRad($this->gridOffsetXpx+$this->gridWidthXpx,$this->gridOffsetYpx+$this->gridHeightYpx);
    $rdLrad=$this->gridLxRad;
    $this->gridrdLhr=$rdLrad*$this->f12OverPi;
    $rdDrad=$this->gridDyRad;
    $this->gridrdDdeg=$rdDrad*$this->f180OverPi;
    
    $this->gridLDinvRad($this->gridOffsetXpx+(($this->gridWidthXpx+1)>>1),$this->gridOffsetYpx);
    $this->griduDdeg=$this->gridDyRad*$this->f180OverPi;
    $this->gridLDinvRad($this->gridOffsetXpx+$this->gridWidthXpx,$this->gridOffsetYpx+$this->gridHeightYpx);
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
      $Urad=max($this->gridD0rad+$this->gridSpanDrad,max($luDrad,$ruDrad));
      $Drad=min($this->gridD0rad-$this->gridSpanDrad,min($ldDrad,$rdDrad));
      $Lhr=$Lrad*$this->f12OverPi;
      $RhrNeg=$Rrad*$this->f12OverPi;
      $Udeg=$Urad*$this->f180OverPi;
      $Ddeg=$Drad*$this->f180OverPi;
    }
    else if(($this->gridD0rad+$this->gridSpanDrad)>=($this->fPiOver2))
    { $Lhr=24;
      $RhrNeg=0;
      $Udeg=90;
      $Ddeg=min($this->gridD0rad-$this->gridSpanDrad,min($ldDrad,$rdDrad))*$this->f180OverPi;
      $griduDdeg=90;
    }
    else if(($this->gridD0rad-$this->gridSpanDrad)<=-($this->fPiOver2))
    { $Lhr=24;
      $RhrNeg=0;
      $Udeg=max($this->gridD0rad+$this->gridSpanDrad,max($luDrad,$ruDrad))*$this->f180OverPi;
      $Ddeg=-90;
      $griddDdeg=-90;
    }
  

    $griduDdeg=$Udeg;
    $griddDdeg=$Ddeg;
    
    $DLhr=($Lhr-$RhrNeg);
    $LStep=min(round((($this->gridDimensions[$this->gridActualDimension][2])/cos($this->gridD0rad))*60)/60,2);
    $DDdeg=($Udeg-$Ddeg);
    $DStep=$this->gridDimensions[$this->gridActualDimension][1];
    
    $LhrStart=(floor($Lhr/$LStep)+1)*$LStep;
    $DdegStart=(floor($Ddeg/$DStep)+1)*$DStep;
  
    $this->gridlLhr=$Lhr;
    $gridrLhr=($RhrNeg<0?($RhrNeg+24):$RhrNeg);
  
    for($d=$DdegStart;$d<=$Udeg;$d+=$DStep)
    { $d=round($d*60)/60;
      $this->canvasX2px=0;
      //jg.setColor(coordLineColor);
      for($l=$Lhr;$l>$RhrNeg;$l-=$LStep/$this->Lsteps)
        $this->gridDrawLineLD($l,$d,($l-($LStep/$this->Lsteps)),$d);
      if($this->canvasX2px&&($this->canvasX2px>=$this->gridOffsetXpx+$this->gridWidthXpx))
        $this->canvasDrawLabel($this->gridOffsetXpx+$this->gridWidthXpx+2,$this->canvasY2px-($this->fontSize1a>>1),60,8,$this->coordDeclDecToDegMin($d),'left');
      else if($this->canvasX2px&&($this->canvasY2px>=$this->gridOffsetYpx+$this->gridHeightYpx))
        $this->canvasDrawLabel($this->canvasX2px-30,$this->gridOffsetYpx+$this->gridHeightYpx+2,60,8,$this->coordDeclDecToDegMin($d),'center');
      else if($this->canvasX2px&&($this->canvasY2px<=$this->gridOffsetYpx))
        $this->canvasDrawLabel($this->canvasX2px-30,$this->gridOffsetYpx-8,60,8,$this->coordDeclDecToDegMin($d),'center');
      else if($this->canvasX2px)
        $this->canvasDrawLabel($this->canvasX2px-30,$this->canvasY2px-17,60,8,$this->coordDeclDecToDegMin($d),'center');
    }
    if($this->gridD0rad<0)
    { for($l=$LhrStart;$l>$RhrNeg;$l-=$LStep)
      { $l=round($l*60)/60;
        $this->canvasX2px=0;
        //jg.setColor(coordLineColor);
        for($d=$Ddeg;$d<$Udeg;$d+=$DStep/$this->Dsteps)
          $this->gridDrawLineLD($l,$d,$l,($d+($DStep/$this->Dsteps)));
        if($this->canvasX2px&&($this->canvasY2px<=$this->gridOffsetYpx))
          $this->canvasDrawLabel($this->canvasX2px-30,$this->gridOffsetYpx-$this->fontSize1a,60,8,$this->coordHrDecToHrMin($l),'center');
        else if($this->canvasX2px&&($this->canvasX2px<=$this->gridOffsetXpx)&&($this->canvasY2px<$this->gridOffsetYpx+$this->gridHeightYpx))
          $this->canvasDrawLabel($this->gridOffsetXpx-62,$this->canvasY2px-($this->fontSize1a>>2),60,8,$this->coordHrDecToHrMin($l),'right');
        else if($this->canvasX2px&&($this->canvasX2px>=$this->gridOffsetXpx+$this->gridWidthXpx)&&($this->canvasY2px<$this->gridOffsetYpx+$this->gridHeightYpx))
          $this->canvasDrawLabel($this->gridOffsetXpx+$this->gridWidthXpx+2,$this->canvasY2px-($this->fontSize1a>>2),60,8,$this->coordHrDecToHrMin($l),'left');
        else if($this->canvasX2px&&($this->canvasY2px>=$this->gridOffsetYpx+$this->gridHeightYpx))
          $this->canvasDrawLabel($this->canvasX2px-30,$this->gridOffsetYpx+$this->gridHeightYpx+3,60,8,$this->coordHrDecToHrMin($l),'center');
      }
    }
    else
    { for($l=$LhrStart;$l>$RhrNeg;$l-=$LStep)
      { $l=round($l*60)/60;
        $this->canvasX2px=0;
        //jg.setColor(coordLineColor);
        for($d=$Udeg;$d>$Ddeg;$d-=$DStep/$this->Dsteps)
          $this->gridDrawLineLD($l,$d,$l,($d-($DStep/$this->Dsteps)));
        if($this->canvasX2px&&($this->canvasY2px<=$this->gridOffsetYpx))
          $this->canvasDrawLabel($this->canvasX2px-30,$this->gridOffsetYpx-($this->fontSize1a),60,8,$this->coordHrDecToHrMin($l),'center');
        else if($this->canvasX2px&&($this->canvasX2px<=$this->gridOffsetXpx)&&($this->canvasY2px<$this->gridOffsetYpx+$this->gridHeightYpx))
          $this->canvasDrawLabel($this->gridOffsetXpx-62,$this->canvasY2px-($this->fontSize1a>>2),60,8,$this->coordHrDecToHrMin($l),'right');
        else if($this->canvasX2px&&($this->canvasX2px>=$this->gridOffsetXpx+$this->gridWidthXpx)&&($this->canvasY2px<$this->gridOffsetYpx+$this->gridHeightYpx))
          $this->canvasDrawLabel($this->gridOffsetXpx+$this->gridWidthXpx+2,$this->canvasY2px-($this->fontSize1a>>2),60,8,$this->coordHrDecToHrMin($l),'left');
        else if($this->canvasX2px&&($this->canvasY2px>=$this->gridOffsetYpx+$this->gridHeightYpx))
          $this->canvasDrawLabel($this->canvasX2px-30,$this->gridOffsetYpx+$this->gridHeightYpx+2,60,8,$this->coordHrDecToHrMin($l),'center');
      }
    }
  }
  

  
  function gridDrawLineLD($Lhr1,$Ddeg1,$Lhr2,$Ddeg2)
  { $this->gridLDrad($Lhr1,$Ddeg1); $x1=$this->gridLxRad; $y1=$this->gridDyRad;
    $this->gridLDrad($Lhr2,$Ddeg2); $x2=$this->gridLxRad; $y2=$this->gridDyRad;
    if(($x1<-($this->gridSpanLrad))&&($x2<-($this->gridSpanLrad))) return 0;
    if(($x1>$this->gridSpanLrad)&&($x2>$this->gridSpanLrad))       return 0;
    if(($y1<-($this->gridSpanDrad))&&($y2<-($this->gridSpanDrad))) return 0;
    if(($y1>$this->gridSpanDrad)&&($y2>$this->gridSpanDrad))       return 0;
    if($x1<-($this->gridSpanLrad)) if($x2==$x1) return 0; else {$y1=(((-($this->gridSpanLrad)-$x1)/($x2-$x1))*($y2-$y1))+$y1; $x1=-($this->gridSpanLrad);}
    if($x1>($this->gridSpanLrad))  if($x2==$x1) return 0; else  {$y1=(((($this->gridSpanLrad)-$x1)/($x2-$x1))*($y2-$y1))+$y1;  $x1=($this->gridSpanLrad); }
    if($y1>($this->gridSpanDrad))  if($y2==$y1) return 0; else  {$x1=(((($this->gridSpanDrad)-$y1)/($y2-$y1))*($x2-$x1))+$x1;  $y1=($this->gridSpanDrad); }
    if($y1<-($this->gridSpanDrad)) if($y2==$y1) return 0; else {$x1=(((-($this->gridSpanDrad)-$y1)/($y2-$y1))*($x2-$x1))+$x1; $y1=-($this->gridSpanDrad);}
    if(($y1<-($this->gridSpanDrad))||($y1>($this->gridSpanDrad))||($x1<-($this->gridSpanLrad))||($x1>($this->gridSpanLrad))) return 0;  
    if($x2<-($this->gridSpanLrad)) if($x2==$x1) return 0; else {$y2=(((-($this->gridSpanLrad)-$x1)/($x2-$x1))*($y2-$y1))+$y1; $x2=-($this->gridSpanLrad);}
    if($x2>($this->gridSpanLrad))  if($x2==$x1) return 0; else  {$y2=(((($this->gridSpanLrad)-$x1)/($x2-$x1))*($y2-$y1))+$y1;  $x2=($this->gridSpanLrad);  }
    if($y2>($this->gridSpanDrad))  if($y2==$y1) return 0; else  {$x2=(((($this->gridSpanDrad)-$y1)/($y2-$y1))*($x2-$x1))+$x1;  $y2=($this->gridSpanDrad);  }
    if($y2<-($this->gridSpanDrad)) if($y2==$y1) return 0; else  {$x2=(((-($this->gridSpanDrad)-$y1)/($y2-$y1))*($x2-$x1))+$x1; $y2=-($this->gridSpanDrad);}
    if(($y2<-($this->gridSpanDrad))||($y2>($this->gridSpanDrad))||($x2<-($this->gridSpanLrad))||($x2>($this->gridSpanLrad))) return 0;
    
    $this->canvasX1px=$this->gridCenterOffsetXpx+$this->gridXpx($x1);
    $this->canvasY1px=$this->gridCenterOffsetYpx+$this->gridYpx($y1);
    $this->canvasX2px=$this->gridCenterOffsetXpx+$this->gridXpx($x2);
    $this->canvasY2px=$this->gridCenterOffsetYpx+$this->gridYpx($y2);
    $this->gridLx1rad=$x1;$this->gridDy1rad=$y1;$this->gridLx2rad=$x2;$this->gridDy2rad=$y2;
    $this->pdf->line($this->canvasX1px,$this->canvasY1px,$this->canvasX2px,$this->canvasY2px);
    return 1;
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
  { $xRad=-(($XpxAbsScr-$this->gridCenterOffsetXpx)/$this->gridWidthXpx2*$this->gridSpanLrad);
    $yRad=(($this->gridCenterOffsetYpx-$YpxAbsScr)/$this->gridHeightYpx2*$this->gridSpanDrad);
    $drad=sqrt(($xRad*$xRad)+($yRad*$yRad));
    if($drad>0)
    { $sinalpha=$xRad/$drad;
      $cosalpha=$yRad/$drad;
      $Dacc=acos((cos($drad)*sin($this->gridD0rad))+(sin($drad)*cos($this->gridD0rad)*$cosalpha));
      $cosLacc=(cos($drad)-(sin($this->gridD0rad)*cos($Dacc)))/(cos($this->gridD0rad)*sin($Dacc));
      if($cosLacc>=0)
        $this->gridLxRad=$this->gridL0rad+(asin(sin($drad)*$sinalpha/sin($Dacc)));
      else
        $this->gridLxRad=$this->gridL0rad+$this->fPi-(asin(sin($drad)*$sinalpha/sin($Dacc)));    
      $this->gridDyRad=(($this->fPiOver2)-$Dacc);
    }
    else
    { $this->gridLxRad=$this->gridL0rad;
      $this->gridDyRad=$this->gridD0rad;
    }
    if(($this->gridDyRad)>($this->fPiOver2))
      $this->gridDyRad=($this->fPiOver2);
    if(($this->gridDyRad)<(-($this->fPiOver2)))
      $this->gridDyRad=(-($this->fPiOver2));
    if(($this->gridLxRad)<0)
      $this->gridLxRad=$this->gridLxRad+($this->f2Pi);
    if(($this->gridLxRad)>=($this->f2Pi))
      $this->gridLxRad=$this->gridLxRad-($this->f2Pi);
  }  

  function gridLDrad($Lhr,$Ddeg)
  { $Lrad=$Lhr*$this->fPiOver12; $Drad=$Ddeg*$this->fPiOver180;
    if($Lrad>$this->gridL0rad+$this->fPi) $Lrad=$Lrad-($this->f2Pi);
    if($Lrad<$this->gridL0rad-$this->fPi) $Lrad=$Lrad+($this->f2Pi);
    $drad=acos((sin($this->gridD0rad)*sin($Drad))+(cos($this->gridD0rad)*cos($Drad)*cos($Lrad-$this->gridL0rad)));
    if($drad>0)
    { $this->gridLxRad=-($drad*(sin($Lrad-$this->gridL0rad)*cos($Drad)/sin($drad)));
      $this->gridDyRad=($drad*(sin($Drad)-(sin($this->gridD0rad)*cos($drad)))/(cos($this->gridD0rad)*sin($drad)));
    }
    else
    { $this->gridLxRad=0;
      $this->gridDyRad=0;
    }
  }

  function gridShowInfo()
  { $t1 =atlasPageFoV.' '.(round($this->gridSpanL*20)/10)." x ".(round($this->gridSpanD*20)/10)."° - ";
	  $t1.=atlasPageDSLM.' '.$this->atlasmagnitude." - ";
	  $t1.=atlasPageStarLM.' '.$this->starsmagnitude;
	  $this->pdf->addText(10,10,8,$t1);
	}
  
  function gridXpx($Lrad) 
  { return round(($this->gridWidthXpx2*$Lrad/$this->gridSpanLrad));
  }
  
  function gridYpx($Drad)
  { return round(($this->gridHeightYpx2*$Drad/$this->gridSpanDrad));
  }

  public  function pdfAtlas($rarad, $declrad, $raspanrad, $declspanrad, $dsomag, $starmag)  // Creates a pdf atlas page
  { global $objUtil,$instDir;
  
    $this->atlaspagerahr=$objUtil->checkRequestKey('atlaspagerahr',0);
    $this->atlaspagedecldeg=$objUtil->checkRequestKey('atlaspagedecldeg',0);
    $this->atlaspagezoomdeg=$objUtil->checkRequestKey('atlaspagezoomdeg',1);
    $this->atlasmagnitude=$objUtil->checkRequestKey('atlasmagnitude',10);
    $this->starsmagnitude=$objUtil->checkRequestKey('starsmagnitude',10);
    
    $this->pdf = new Cezpdf('a4', 'landscape');
    $this->pdf->selectFont($instDir.'lib/fonts/Helvetica.afm');
    $this->gridInit();
    $this->gridInitScale($this->atlaspagerahr,$this->atlaspagedecldeg,$this->atlaspagezoomdeg);
    $this->gridDrawCoordLines();
    $this->gridShowInfo();
    
    $this->pdf->rectangle($this->gridOffsetXpx,$this->gridOffsetYpx,
                         ($this->canvasDimensionXpx-($this->gridOffsetXpx<<1)),($this->canvasDimensionYpx-($this->gridOffsetYpx<<1)));
        
    /*
    $this->pdf->addText(50,$this->gridHeightYpx-10,10,"DeepskyLog Atlas Page for location ".$this->atlaspagerahr.' '.$this->atlaspagedecldeg.' to magnitude '.$this->atlasmagnitude);
    
    $this->pdf->addText(50,20,10,"DeepskyLog Atlas Page for location");
    */
    $this->pdf->Stream(); 
  }
  
	function roundPrecision($theValue,$thePrecision)
	{ return(round($theValue/$thePrecision)*$thePrecision);
	}

}
$objPrintAtlas = new PrintAtlas;
?>