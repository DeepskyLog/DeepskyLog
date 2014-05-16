<?php
// printaltas.php
// functions for printing the atlas

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class PrintAtlas
{ var $astroObjectsArr,
      $atlasmagnitude=0,
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
      $conBoundries = Array(),
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
      $fontSizeAck=4,
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
      $labelsArr=array(),
      $Legend1x=25,
      $Legend1y=20, 
      $Legend2x=55,
      $Legend2y=35,
      $Lsteps=10,
      $lx=0,
      $maxshowndsomag=-99,
      $minObjectSize=2.5,
      $nsegmente=8,
      $rx=0,
      $starsmagnitude,
      $theItemPage,
      $theOrientation,
      $thePageSize,
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
    Array(  1, 0.40,0.026,11),
    Array(0.5, 0.20,0.012,12),
    Array(0.25,0.20,0.012,14),
    Array(0.2 ,0.20,0.012,16),
    Array(0.15 ,0.20,0.012,16),
    Array(0.1 ,0.20,0.012,16)
  );
  
  function astroDrawBRTNBObject($i)
  { $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $d1=$this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"]*.5);
    $this->pdf->rectangle($cx-$d1,$cy-$d1,$d1*2,$d1*2);
    $this->astroDrawObjectLabel($cx,$cy,$d1,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
  }

  function astroDrawCLANBObject($i)
	{ $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $d1=$this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"]*.5);
    $d2=$this->gridDiam2SecToPxMin($this->astroObjectsArr[$i]["diam2"]*.5);
    $this->pdf->rectangle($cx-$d1+1,$cy-$d1+1,$d1*2-2,$d1*2-2);
    $this->pdf->setLineStyle(0.5,'','',array(3));
    $this->pdf->rectangle($cx-$d1-1,$cy-$d1-1,$d1*2+2,$d1*2+2);
    $this->pdf->setLineStyle(0.5,'','',array());
    $this->astroDrawObjectLabel($cx,$cy,$d1+1,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);	
	}
  
  function astroDrawConstellations()
  { global $objConstellation;
    $this->pdf->setLineStyle(2,'round');
    $this->conBoundries=$objConstellation->getAllBoundries();
    $cons = Array();
    for($i=0;$i<count($this->conBoundries);$i++)
    { if($this->gridDrawLongLineLD($this->conBoundries[$i]['ra0'], $this->conBoundries[$i]['decl0'], $this->conBoundries[$i]['ra1'], $this->conBoundries[$i]['decl1']))
      { if((!in_array($this->conBoundries[$i]['con0'],$cons))||
           (!in_array($this->conBoundries[$i]['con1'],$cons)))
        { $cons[count($cons)]=($this->conBoundries[$i]['con0']);
          $cons[count($cons)]=($this->conBoundries[$i]['con1']);
          $tempx=max(min((($this->canvasX1px+$this->canvasX2px)/2)-($this->fontSize1b*2.75),$this->gridOffsetXpx+$this->gridWidthXpx-($this->fontSize1b*3)),$this->gridOffsetXpx+1);
          $tempx2=max(min((($this->canvasX1px+$this->canvasX2px)/2)-($this->fontSize1b*1.5),$this->gridOffsetXpx+$this->gridWidthXpx-($this->fontSize1b*3)),$this->gridOffsetXpx+1);
          $tempy=min(max(($this->canvasY1px+$this->canvasY2px)/2,$this->gridOffsetYpx+1),$this->gridOffsetYpx+$this->gridHeightYpx-($this->fontSize1b>>1)-2);
          $tempy2=min(max(($this->canvasY1px+$this->canvasY2px)/2,$this->gridOffsetYpx-($this->fontSize1a>>1)-2),$this->gridOffsetYpx+$this->gridHeightYpx);
          if($this->conBoundries[$i]['con0pos']=="L")
            $this->labelsArr[]=array($tempx,$tempy,50,$this->fontSize1b,$this->conBoundries[$i]['con0'].' '.$this->conBoundries[$i]['con1'],'left');
          if($this->conBoundries[$i]['con0pos']=="R")
            $this->labelsArr[]=array($tempx,$tempy,50,$this->fontSize1b,$this->conBoundries[$i]['con1'].' '.$this->conBoundries[$i]['con0'],'left');
          if($this->conBoundries[$i]['con0pos']=="A")
          { $this->labelsArr[]=array($tempx2,$tempy2+2,20,$this->fontSize1b,$this->conBoundries[$i]['con0'],'left');
            $this->labelsArr[]=array($tempx2,$tempy2-($this->fontSize1a>>1)-2,20,$this->fontSize1b,$this->conBoundries[$i]['con1'],'left');
          }
          if($this->conBoundries[$i]['con0pos']=="B")
          { $this->labelsArr[]=array($tempx2,$tempy2+2,20,$this->fontSize1b,$this->conBoundries[$i]['con1'],'left');
            $this->labelsArr[]=array($tempx2,$tempy2-($this->fontSize1a>>1)-2,20,$this->fontSize1b,$this->conBoundries[$i]['con0'],'left');
          }
        }
        /*if(($this->conBoundries[$i]['con1']) && (!in_array($this->conBoundries[$i]['con1'],$cons)))
        { $cons[count($cons)]=($this->conBoundries[$i]['con1']);
          if($this->conBoundries[$i]['con1pos']=="L")
            $this->labelsArr[]=array((($this->canvasX1px+$this->canvasX2px)/2)-($this->fontSize1b*3)-5,($this->canvasY1px+$this->canvasY2px)/2-($this->fontSize1a>>1),20,$this->fontSize1b,$this->conBoundries[$i]['con1'],'left');
          if($this->conBoundries[$i]['con1pos']=="R")
            $this->labelsArr[]=array((($this->canvasX1px+$this->canvasX2px)/2)+5,($this->canvasY1px+$this->canvasY2px)/2-($this->fontSize1a>>1),20,$this->fontSize1b,$this->conBoundries[$i]['con1'],'left');
          if($this->conBoundries[$i]['con1pos']=="A")
            $this->labelsArr[]=array((($this->canvasX1px+$this->canvasX2px)/2)-($this->fontSize1b*1),($this->canvasY1px+$this->canvasY2px)/2+2,20,$this->fontSize1b,$this->conBoundries[$i]['con1'],'left');
          if($this->conBoundries[$i]['con1pos']=="B")
            $this->labelsArr[]=array((($this->canvasX1px+$this->canvasX2px)/2)-($this->fontSize1b*1),($this->canvasY1px+$this->canvasY2px)/2 - $this->fontSize1a-2,20,$this->fontSize1b,$this->conBoundries[$i]['con1'],'left');
        }*/
      }
    }
    if(count($cons)==0)
    { $this->gridLxRad=$this->gridL0rad;
      $this->gridDyRad=$this->gridD0rad;
      $this->labelsArr[]=array($this->canvasDimensionXpx-$this->gridOffsetXpx-(3*$this->fontSize1b), $this->gridOffsetYpx+3,20, $this->fontSize1b, $this->astroGetConstellationFromCoordinates($this->gridL0rad*$this->f12OverPi,$this->gridD0rad*$this->f180OverPi),'left');
    }
    $this->pdf->setLineStyle(0.5,'round');
  }
  
  function astroDrawDRKNBObject($i)
  { $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $d1=$this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"]*.5);
    $this->pdf->setLineStyle(0.5,'','',array(3));
		$this->pdf->rectangle($cx-$d1,$cy-$d1,$d1*2,$d1*2);
    $this->astroDrawObjectLabel($cx,$cy,$d1,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
	  $this->pdf->setLineStyle(0.5,'','',array());
  }
	  
	function astroDrawGCObject($i)
	{ $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $d1=$this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"]*.5);
    $this->pdf->ellipse($cx,$cy,$d=($this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"])*0.5),($this->gridDiam2SecToPxMin($this->astroObjectsArr[$i]["diam1"])*0.5),0,$this->nsegmente);
    $this->pdf->line($cx-$d, $cy, $cx+$d, $cy);
    $this->pdf->line($cx, $cy-$d, $cx, $cy+$d);
    $this->astroDrawObjectLabel($cx,$cy,$d,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
	}
  
  function astroDrawGXCLObject($i)
	{ $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $d1=$this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"]*.5);
    $x=$cx;
    $y=$cy;
    $d1=max($d1,12);
    $d2=max($d1,12);
    $x1=0;
    $x2=0;
    $y1=0;
    $y2=0;
    $x1=$x;
    $y1=$y+(($d2+1)>>1);
    $x2=$x+(($d1+1)>>1);
    $y2=$y+(($d2+1)>>3);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x+(($d1+1)>>1);
    $y1=$y+(($d2+1)>>3);
    $x2=$x+(3*(($d1+1)>>3));
    $y2=$y-(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x+(3*(($d1+1)>>3));
    $y1=$y-(($d2+1)>>1);
    $x2=$x-(3*(($d1+1)>>3));
    $y2=$y-(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x-(3*(($d1+1)>>3));
    $y1=$y-(($d2+1)>>1);
    $x2=$x-(($d1+1)>>1);
    $y2=$y+(($d2+1)>>3);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x-(($d1+1)>>1);
    $y1=$y+(($d2+1)>>3);
    $x2=$x;
    $y2=$y+(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $this->astroDrawObjectLabel($cx,$cy,($d1>>1),$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
	}
  
  function astroDrawGXObject($i)
  { $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $d1=nzx($this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"]*.5),5);
    if(($pa=$this->astroObjectsArr[$i]["pa"])==999)
    { $d2=$d1;
      $pa=0;
    }
    else
      $d2=$this->gridDiam2SecToPxMin($this->astroObjectsArr[$i]["diam2"]*0.5);
		$this->pdf->ellipse($cx,$cy,$d1,$d2,($pa=Nzx($this->astroObjectsArr[$i]["pa"],0))-90);
    $this->astroDrawObjectLabel($cx,$cy-(cos($pa*$this->fPiOver180)*$d1),(5*abs(cos($pa*$this->fPiOver180)))+abs($d1*sin($pa*$this->fPiOver180)),$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
  }
  
  function atlasDrawLegend()
  { for($i=11,$legendx=30;$i>=0;$i--)                    
	  { $this->pdf->filledEllipse($this->Legend1x+$legendx,$this->canvasDimensionYpx-$this->Legend1y-3,(.25*$i + 0.5),(.25*$i + 0.5),0,$this->nsegmente);
	    $legendx+=(0.5*$i)+5;
	    $this->pdf->addText($this->Legend1x+$legendx, $this->canvasDimensionYpx-$this->Legend1y-5, $this->fontSize1b, ((($this->starsmagnitude))-(.5*$i)));
	    $legendx+=$this->pdf->getTextWidth($this->fontSize1b, ((($this->starsmagnitude))-(.5*$i)));
	    $legendx+=10;
     }
	  $legendx-=$this->pdf->getTextWidth($this->fontSize1b, ((($this->starsmagnitude))-(.5*$i)));
	  $legendx-=10;
    $this->pdf->setColor(0.7,0.7,0.7);
    $this->pdf->filledEllipse($this->Legend1x+$legendx,$this->canvasDimensionYpx-$this->Legend1y-3,.25,.25,0,$this->nsegmente);
    $this->pdf->setColor(0,0,0);
    
    
    
    
    
    $this->pdf->ellipse($this->Legend2x+0, $this->canvasDimensionYpx-$this->Legend2y+3, 5, 2.5, -45);
    $this->pdf->addTextWrap($this->Legend2x+10, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'GALXY', 'left');
    
    $this->pdf->ellipse($this->Legend2x+50, $this->canvasDimensionYpx-$this->Legend2y+3, 2.5, 2.5, 0);
    $this->pdf->line($this->Legend2x+55, $this->canvasDimensionYpx-$this->Legend2y+3, $this->Legend2x+52.5, $this->canvasDimensionYpx-$this->Legend2y+3);
    $this->pdf->line($this->Legend2x+45, $this->canvasDimensionYpx-$this->Legend2y+3, $this->Legend2x+47.5, $this->canvasDimensionYpx-$this->Legend2y+3);
    $this->pdf->line($this->Legend2x+50, $this->canvasDimensionYpx-$this->Legend2y+5.5, $this->Legend2x+50, $this->canvasDimensionYpx-$this->Legend2y+8);
    $this->pdf->line($this->Legend2x+50, $this->canvasDimensionYpx-$this->Legend2y+0.5, $this->Legend2x+50, $this->canvasDimensionYpx-$this->Legend2y-2);
    $this->pdf->addTextWrap($this->Legend2x+60, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'PLANB', 'left');
    
    $this->pdf->ellipse($this->Legend2x+100, $this->canvasDimensionYpx-$this->Legend2y+3, 5, 5, 0);
    $this->pdf->line($this->Legend2x+95, $this->canvasDimensionYpx-$this->Legend2y+3, $this->Legend2x+105, $this->canvasDimensionYpx-$this->Legend2y+3);
    $this->pdf->line($this->Legend2x+100, $this->canvasDimensionYpx-$this->Legend2y+8, $this->Legend2x+100, $this->canvasDimensionYpx-$this->Legend2y-2);
    $this->pdf->addTextWrap($this->Legend2x+110, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'GLOCL', 'left');
     
    $this->pdf->setLineStyle(0.5,'','',array(3));
    $this->pdf->ellipse($this->Legend2x+150, $this->canvasDimensionYpx-$this->Legend2y+3, 5, 5, 0);
    $this->pdf->addTextWrap($this->Legend2x+160, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'OPNCL', 'left');
    
    $this->pdf->rectangle($this->Legend2x+195, $this->canvasDimensionYpx-$this->Legend2y-2, 10, 10);
    $this->pdf->addTextWrap($this->Legend2x+210, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'DRKNB', 'left');
    
    $this->pdf->setLineStyle(0.5,'','',array());
    
    $this->pdf->rectangle($this->Legend2x+245, $this->canvasDimensionYpx-$this->Legend2y-2, 10, 10);
    $this->pdf->addTextWrap($this->Legend2x+260, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'NEB', 'left');
    
    $this->pdf->rectangle($this->Legend2x+295.5, $this->canvasDimensionYpx-$this->Legend2y-1.5, 9, 9);
    $this->pdf->setLineStyle(0.5,'','',array(3));
    $this->pdf->rectangle($this->Legend2x+294.5, $this->canvasDimensionYpx-$this->Legend2y-2.5, 11, 11);
    $this->pdf->addTextWrap($this->Legend2x+310, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'CLANB', 'left');
    $this->pdf->setLineStyle(0.5,'','',array());
    
    $x=$this->Legend2x+350;
    $y=$this->canvasDimensionYpx-$this->Legend2y+3;
    $d1=12;
    $d2=12;
    $x1=0;
    $x2=0;
    $y1=0;
    $y2=0;
    $x1=$x;
    $y1=$y+(($d2+1)>>1);
    $x2=$x+(($d1+1)>>1);
    $y2=$y+(($d2+1)>>3);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x+(($d1+1)>>1);
    $y1=$y+(($d2+1)>>3);
    $x2=$x+(3*(($d1+1)>>3));
    $y2=$y-(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x+(3*(($d1+1)>>3));
    $y1=$y-(($d2+1)>>1);
    $x2=$x-(3*(($d1+1)>>3));
    $y2=$y-(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x-(3*(($d1+1)>>3));
    $y1=$y-(($d2+1)>>1);
    $x2=$x-(($d1+1)>>1);
    $y2=$y+(($d2+1)>>3);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x-(($d1+1)>>1);
    $y1=$y+(($d2+1)>>3);
    $x2=$x;
    $y2=$y+(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $this->pdf->addTextWrap($this->Legend2x+360, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'GALCL', 'left');
    
    $x=$this->Legend2x+400;
    $y=$this->canvasDimensionYpx-$this->Legend2y+3;
    $d1=3;
    $d2=3;
    $this->pdf->line($x-2, $y, $x-$d1-2, $y);
    $this->pdf->line($x, $y-2-$d2, $x, $y-2);
    $this->pdf->line($x, $y+2+$d2, $x, $y+2);
    $this->pdf->line($x+2, $y, $x+2+$d1, $y);
    $this->pdf->addTextWrap($this->Legend2x+410, $this->canvasDimensionYpx-$this->Legend2y, 30, $this->fontSize1b, 'QUASR', 'left');
    
    
    
    
    
  }
    	
  function astroDrawObjectLabel($cx, $cy, $d, $name, $seen)
  { $this->pdf->addText(($cx+4+$d), $cy-($this->fontSize1a>>2), $this->fontSize1b, $name);
    if(substr($seen,0,2)=='YD')
      $this->pdf->line($cx+$d+3, $cy+($this->fontSize1a>>2)+1.5, $cx+4+$d+(strlen($name)*0.6*$this->fontSize1b), $cy+($this->fontSize1a>>2)+1.5);
    if(substr($seen,0,1)=='Y')
      $this->pdf->line($cx+$d+3, $cy-($this->fontSize1a>>2)-2, $cx+4+$d+(strlen($name)*0.6*$this->fontSize1b), $cy-($this->fontSize1a>>2)-2);
    if(substr($seen,0,1)=='X')
    { $this->pdf->setLineStyle(0.5,'','',array(3));
      $this->pdf->line($cx+$d+3, $cy-($this->fontSize1a>>2)-2, $cx+4+$d+(strlen($name)*0.6*$this->fontSize1b), $cy-($this->fontSize1a>>2)-2);
      $this->pdf->setLineStyle(0.5,'','',array());
    }
 }
  
  
  function astroDrawOCObject($i)
  { $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $this->pdf->setLineStyle(0.5,'','',array(3));
    $this->pdf->ellipse($cx,$cy,$d=($this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"])*0.5),($this->gridDiam2SecToPxMin($this->astroObjectsArr[$i]["diam1"])*0.5),0,$this->nsegmente);
    $this->pdf->setLineStyle(0.5,'','',array());
    $this->astroDrawObjectLabel($cx,$cy,$d,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
  }
  
  function astroDrawStarObject($i)
  { //$d=floor(2*(($this->gridDimensions[$this->gridActualDimension][3])-($this->astroObjectsArr[$i]["mag"]/100))+1);
    $d=floor(2*(($this->starsmagnitude)-($this->astroObjectsArr[$i]["mag"]/100))+1);
    $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    if((!((($cx-$d<$this->lx)||($cx+$d>$this->rx))))&&
       (!((($cy+$d>$this->ty)||($cy-$d<$this->by)))))
    { $this->pdf->filledEllipse($cx,$cy,(.5*$d),(.5*$d),0,$this->nsegmente);
      $this->astroDrawObjectLabel($cx,$cy,(($d+1)>>1),$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
    }     
  }
  function astroDrawStar1Object($i)
  { $d=max(floor(2*(($this->starsmagnitude)-($this->astroObjectsArr[$i]["mag"]))+1),2);
    $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    if((!((($cx-$d<$this->lx)||($cx+$d>$this->rx))))&&
       (!((($cy+$d>$this->ty)||($cy-$d<$this->by)))))
    { //$this->pdf->filledEllipse($cx,$cy,(.5*$d),(.5*$d),0,$this->nsegmente);
      //$this->astroDrawObjectLabel($cx,$cy,(($d+1)>>1),$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
    }     
  }
  function astroDrawStarxObject($i)
	{ //$d=max(floor(2*(($this->gridDimensions[$this->gridActualDimension][3])-($this->astroObjectsArr[$i]["mag"]))+1),3);
    $d=max(floor(2*(($this->starsmagnitude)-($this->astroObjectsArr[$i]["mag"]))+1),3);
    $this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    if((!((($cx-$d-2<$this->lx)||($cx+$d+2>$this->rx))))&&
       (!((($cy+$d>$this->ty)||($cy-$d<$this->by)))))
    { //$this->pdf->filledEllipse($cx,$cy,(.5*$d),(.5*$d),0,$this->nsegmente);
      $d=round($d*0.75);
	    //$this->pdf->line($cx-$d,$cy,$cx+$d,$cy);
      //$this->astroDrawObjectLabel($cx,$cy,$d,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
    }     
	}
	
  
	function astroDrawObjects($theobject='')
	{ global $objObject,$objUtil,$objObjectOutlines;
	  $excludelist=Array('Sh','Simeis','Dwb');
	  $this->astroObjectsArr=$objObject->getObjectsMag($this->gridlLhr,$this->gridrLhr,$this->griddDdeg,$this->griduDdeg,-999999,$this->atlasmagnitude,$objObject->getExactDsObject($objUtil->checkRequestKey('object'),$theobject));
	  $z=count($this->astroObjectsArr);
	  for($i=0;$i<$z;$i++)
	  { $thecat = substr($this->astroObjectsArr[$i]["name"],0,strrpos($this->astroObjectsArr[$i]["name"]," "));
	  	if (in_array($this->astroObjectsArr[$i]["name"], $objObjectOutlines->getAllObjects())) {
  	    	$this->astroDrawObjectOutline($i);
  	    } else if((!(in_array($thecat,$excludelist)))&&($this->astroObjectsArr[$i]["type"]!='AASTAR1')) {
  	      if(($this->astroObjectsArr[$i]["mag"]>$this->maxshowndsomag)&&($this->astroObjectsArr[$i]["mag"]<99))
  	      		$this->maxshowndsomag=$this->astroObjectsArr[$i]["mag"];
		  else if($this->astroObjectsArr[$i]["type"]=='AA1STAR')
	        $this->astroDrawStar1Object($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('AA2STAR','AA3STAR','ASTAR','AA5STAR','AA6STAR','AA7STAR','AA8STAR','DS')))
	        $this->astroDrawStarxObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('ASTER','LMCOC','OPNCL','SMCOC')))
	        $this->astroDrawOCObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('BRTNB','EMINB','ENRNN','ENSTR','GXADN','LMCDN','REFNB','RNHII','SMCDN','SNREM','STNEB','WRNEB')))
	        $this->astroDrawBRTNBObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('CLANB','GACAN','LMCCN','SMCCN','GXADN','LMCDN','HII')))
	        $this->astroDrawCLANBObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('DRKNB')))
	        $this->astroDrawDRKNBObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('GALCL')))
	        $this->astroDrawGXCLObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('GALXY')))
	        $this->astroDrawGXObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('GLOCL','GXAGC','LMCGC','SMCGC')))
	        $this->astroDrawGCObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('PLNNB')))
	        $this->astroDrawPNObject($i);
	      else if(in_array($this->astroObjectsArr[$i]["type"],array('QUASR')))
	        $this->astroDrawQSRObject($i);
	      else 
	        $this->astroDrawBRTNBObject($i); 
  	    if((isset($_SESSION['atlasPagesIndex']))&&(!(in_array($this->astroObjectsArr[$i]["type"],array('AA1STAR','AA2STAR','AA3STAR','ASTAR','AA5STAR','AA6STAR','AA7STAR','AA8STAR','DS')))))
  	    { $indexDistance=sqrt((abs($this->gridD0rad - $this->gridDyRad))^2 + ((abs($this->gridL0rad - $this->gridLxRad)*cos($this->gridLxRad))^2));
  	    	if(!(in_array($this->astroObjectsArr[$i]["name"],$_SESSION['atlasPagesIndex'])))
  	      { $_SESSION['atlasPagesIndex'][$this->astroObjectsArr[$i]["name"]]=Array($indexDistance,$this->theItemPage);
  	      }
  	      else 
  	      { if($_SESSION['atlasPagesIndex'][$this->astroObjectsArr[$i]["name"]][0]>$indexDistance)
  	          $_SESSION['atlasPagesIndex'][$this->astroObjectsArr[$i]["name"]]=Array($indexDistance,$this->theItemPage);
  	      }
  	    }
  	  }
	  }
	}

	function astroDrawObject($theobject='')
	{ global $objObject,$objUtil,$objObjectOutlines;
	  $this->astroObjectsArr=$objObject->getObject($theobject);
	  $z=count($this->astroObjectsArr);
	  for($i=0;$i<$z;$i++)
	  { if($this->astroObjectsArr[$i]["type"]!='AASTAR1')
  	  { if (in_array($this->astroObjectsArr[$i]["name"], $objObjectOutlines->getAllObjects())) {
  	    	$this->astroDrawObjectOutline($i);
  	    } else {
  	  		if(($this->astroObjectsArr[$i]["mag"]>$this->maxshowndsomag)&&($this->astroObjectsArr[$i]["mag"]<99))
  	      		$this->maxshowndsomag=$this->astroObjectsArr[$i]["mag"];
			if($this->astroObjectsArr[$i]["type"]=='AA1STAR')
	        	$this->astroDrawStar1Object($i);
		    else if(in_array($this->astroObjectsArr[$i]["type"],array('AA2STAR','AA3STAR','ASTAR','AA5STAR','AA6STAR','AA7STAR','AA8STAR','DS')))
		        $this->astroDrawStarxObject($i);
	    	else if(in_array($this->astroObjectsArr[$i]["type"],array('ASTER','LMCOC','OPNCL','SMCOC')))
	        	$this->astroDrawOCObject($i);
		    else if(in_array($this->astroObjectsArr[$i]["type"],array('BRTNB','EMINB','ENRNN','ENSTR','GXADN','LMCDN','REFNB','RNHII','SMCDN','SNREM','STNEB','WRNEB')))
		        $this->astroDrawBRTNBObject($i);
	    	else if(in_array($this->astroObjectsArr[$i]["type"],array('CLANB','GACAN','LMCCN','SMCCN','GXADN','LMCDN','HII')))
	        	$this->astroDrawCLANBObject($i);
		    else if(in_array($this->astroObjectsArr[$i]["type"],array('DRKNB')))
		        $this->astroDrawDRKNBObject($i);
	    	else if(in_array($this->astroObjectsArr[$i]["type"],array('GALCL')))
	        	$this->astroDrawGXCLObject($i);
		    else if(in_array($this->astroObjectsArr[$i]["type"],array('GALXY')))
		        $this->astroDrawGXObject($i);
	    	else if(in_array($this->astroObjectsArr[$i]["type"],array('GLOCL','GXAGC','LMCGC','SMCGC')))
	        	$this->astroDrawGCObject($i);
		    else if(in_array($this->astroObjectsArr[$i]["type"],array('PLNNB')))
		        $this->astroDrawPNObject($i);
	    	else if(in_array($this->astroObjectsArr[$i]["type"],array('QUASR')))
	        	$this->astroDrawQSRObject($i);
		    else 
		        $this->astroDrawBRTNBObject($i); 
  	  	}
  	  }
	  }
	}
	
	function astroDrawObjectOutline($i) {
		global $objObject,$objUtil,$objObjectOutlines;
		
		$outlines = $objObjectOutlines->getOutlines($this->astroObjectsArr[$i]["name"]);
		
		// Check the number of different parts of the nebula
 		$numberOfVertices = 0;
 		for ($j=0;$j<count($outlines);$j++) {
   			if($outlines[$j]['type'] == "start") {
     			$numberOfVertices++;
   			}
 		}

 		$counter = 0;
 		for ($j=0;$j<$numberOfVertices;$j++) {
  			$polygonCoordinates = Array();
  			while($outlines[$counter]['type'] != "end") {
  				$this->gridLDrad($outlines[$counter]['ra'], $outlines[$counter]['decl']);
  				$cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
  				$cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
   				array_push($polygonCoordinates, $cx, $cy);
   				$counter++;
  			}
			$this->gridLDrad($outlines[$counter]['ra'], $outlines[$counter]['decl']);
  			$cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
  			$cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
   			array_push($polygonCoordinates, $cx, $cy);
  			$counter++;

			// Close the polygon
			array_push($polygonCoordinates, $polygonCoordinates[0], $polygonCoordinates[1]);
			$this->pdf->setLineStyle(0.5,'','');
		    $this->pdf->polygon($polygonCoordinates, count($polygonCoordinates) / 2);
		    $this->pdf->setLineStyle(0.5,'','');
	 	}
	    $d = 10;
	    $this->astroDrawObjectLabel($cx,$cy,$d,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
	}
	
	function astroDrawPNObject($i)
	{ $this->pdf->addText(10,10,$this->fontSize1b,"PN");
		$this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $d=$this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"]*.5);
    $this->pdf->ellipse($cx,$cy,$d,$d,0,$this->nsegmente);
    $this->pdf->line($cx-$d, $cy, $cx-($d<<1), $cy);
    $this->pdf->line($cx+$d, $cy, $cx+($d<<1), $cy);
    $this->pdf->line($cx, $cy-$d, $cx, $cy-($d<<1));
    $this->pdf->line($cx, $cy+$d, $cx, $cy+($d<<1));
    $this->astroDrawObjectLabel($cx,$cy,$d<<1,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
	}
	
	function astroDrawQSRObject($i)
	{ $this->pdf->addText(10,10,$this->fontSize1b,"PN");
		$this->gridLDrad($this->astroObjectsArr[$i]["ra"],$this->astroObjectsArr[$i]["decl"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    $d=$this->gridDiam1SecToPxMin($this->astroObjectsArr[$i]["diam1"]*.5);
    $this->pdf->line($cx-$d, $cy, $cx-($d>>1), $cy);
    $this->pdf->line($cx+$d, $cy, $cx+($d>>1), $cy);
    $this->pdf->line($cx, $cy-$d, $cx, $cy-($d>>1));
    $this->pdf->line($cx, $cy+$d, $cx, $cy+($d>>1));
    $this->astroDrawObjectLabel($cx,$cy,$d<<1,$this->astroObjectsArr[$i]["name"],$this->astroObjectsArr[$i]["seen"]);
			}

  function astroDrawStarsArr()
  { global $objStar;
    for($m=8;$m<=$this->starsmagnitude;$m++)
    { //if($m>$this->gridDimensions[$this->gridActualDimension][3])
      if($m>$this->starsmagnitude-0.5)
        $this->pdf->setColor(0.7,0.7,0.7);
      else
        $this->pdf->setColor(0,0,0);
      if($this->gridD0rad>0)
    	{ if(($this->gridD0rad+($this->gridSpanDrad/1))>=($this->fPiOver2))
    		  $this->astroObjectsArr=$objStar->getStarsMagnitude(24,0,$this->gridldDdeg,90,$m,$m);
    		else
    		  $this->astroObjectsArr=$objStar->getStarsMagnitude($this->gridlLhr,$this->gridrLhr,$this->gridldDdeg,$this->griduDdeg,$m,$m);
    	}
    	else
    	{ if(($this->gridD0rad-($this->gridSpanDrad/1))<=(-$this->fPiOver2))
    	    $this->astroObjectsArr=$objStar->getStarsMagnitude(24,0,-90,$this->gridluDdeg,$m,$m);
    	  else
    	    $this->astroObjectsArr=$objStar->getStarsMagnitude($this->gridldLhr,$this->gridrdLhr,$this->griddDdeg,$this->gridluDdeg,$m,$m);
    	}
    	  
    	$z=count($this->astroObjectsArr); 
      for($i=0;$i<$z;$i++)
        if(($this->starsmagnitude)>($this->astroObjectsArr[$i]["vMag"]/100.0))
          $this->canvasDrawStar($i);
    }
    $this->pdf->setColor(0,0,0);
  }
    
  function astroGetConstellationFromCoordinates($thera,$thedecl)
  { $tempdecl=-90;
    $tempcon="";
    $thera0=0.0;
    $thera1=0.0;
    $thedecl0=0.0;
    $thedecl1=0.0;
    for($i=0;$i<count($this->conBoundries);$i++)
    { $thera0=$this->conBoundries[$i]['ra0'];
      $thera1=$this->conBoundries[$i]['ra1'];
      $thedecl0=$this->conBoundries[$i]['decl0'];
      $thedecl1=$this->conBoundries[$i]['decl1'];
      if(abs($this->conBoundries[$i]['ra0']-$this->conBoundries[$i]['ra1'])>12)
      { if(abs($thera-$this->conBoundries[$i]['ra0'])>12)
          $thera0+=(($this->conBoundries[$i]['ra0']<12)?24.0:-24.0); 
        else
          $thera1+=(($this->conBoundries[$i]['ra1']<12)?24.0:-24.0); 
      }
      if(abs($thera1-$thera0)>0)
        $thedecl01=$thedecl0+(($thera-$thera0)/($thera1-$thera0)*($thedecl1-$thedecl0));
      else
        $thedecl01=($thedecl0+$thedecl1)/2;
    if((($thera0<=$thera)&&($thera<=$thera1)||($thera1<=$thera)&&($thera<=$thera0))&&
       ($thedecl01<$thedecl)&&($thedecl01>$tempdecl))
      { $tempdecl=$thedecl01;
        if($this->conBoundries[$i]['con0pos']=="A")
          $tempcon=$this->conBoundries[$i]['con0'];
        if($this->conBoundries[$i]['con0pos']=="B")
          $tempcon=$this->conBoundries[$i]['con1'];
        if($this->conBoundries[$i]['con0pos']=="L")
          if((($thedecl1-$thedecl0)/($thera1-$thera0))>0)
            $tempcon=$this->conBoundries[$i]['con1'];
          else
            $tempcon=$this->conBoundries[$i]['con0'];
        if($this->conBoundries[$i]['con0pos']=="R")
          if((($thedecl1-$thedecl0)/($thera1-$thera0))>0)
            $tempcon=$this->conBoundries[$i]['con0'];
          else
            $tempcon=$this->conBoundries[$i]['con1'];
      }
    }
    return $tempcon;
  }
  
  function canvasDrawStar($i)
  { $name='';
    if($this->astroObjectsArr[$i]["vMag"]<1200)
    { $name=$this->astroObjectsArr[$i]["nameBayer"].' '.$this->astroObjectsArr[$i]["nameBayer2"].' '; 
      if($name!="  ") 
        $name.=$this->astroObjectsArr[$i]["nameCon"];
    }
    //$d=floor(2*max(($this->gridDimensions[$this->gridActualDimension][3])-($this->astroObjectsArr[$i]["vMag"]/100.0),0)+1);
    $d=floor(2.0*max(($this->starsmagnitude)-($this->astroObjectsArr[$i]["vMag"]/100.0),0.5));
//    $d=floor(2.0*max(($this->starsmagnitude)-($this->astroObjectsArr[$i]["vMag"]/100.0),0.5));
    $this->gridLDrad($this->astroObjectsArr[$i]["RA2000"],$this->astroObjectsArr[$i]["DE2000"]); 
    $cx=$this->gridCenterOffsetXpx+$this->gridXpx($this->gridLxRad);
    $cy=$this->gridCenterOffsetYpx+$this->gridYpx($this->gridDyRad);
    if((!((($cx-$d<$this->lx)||($cx+$d>$this->rx))))&&
       (!((($cy+$d>$this->ty)||($cy-$d<$this->by)))))
    { $this->pdf->filledEllipse($cx,$cy,(.25*$d + 0.5),(.25*$d + 0.5),0,$this->nsegmente);
      if((($cx+4+(($d+1)>>1))>$this->lx)&&(($cx+4+(($d+1)>>1)+(strlen($name)*$this->fontSize1b))<$this->rx)&&(($cy-($this->fontSize1a>>1))<$this->ty)&&(($cy+($this->fontSize1a>>1))>$this->by))
        $this->pdf->addText(($cx+4+(($d+1)>>1)), $cy-($this->fontSize1a>>1), $this->fontSize1b, $name);
    }     
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
	  return sprintf('%02d',$this->dsl_hr).'h'.sprintf('%02d',$this->dsl_min).'m'.sprintf('%02d',$this->dsl_sec).'s,'.sprintf('%02d',$this->dsl_deg).'ï¿½'.sprintf('%02d',$this->dsl_amn).'\'';
	}
  
	function gridDiam1SecToPxMin($Diam1Sec)
  { return max(round($this->diam1SecToPxCt*$Diam1Sec),$this->minObjectSize);
  }

  function gridDiam2SecToPxMin($Diam2Sec)
  { return max(round($this->diam2SecToPxCt*$Diam2Sec),$this->minObjectSize);
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
    $this->gridLDinvRad($this->gridOffsetXpx+(($this->gridWidthXpx+1)>>1),$this->gridOffsetYpx+$this->gridHeightYpx);
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
      $griduDdeg=$Udeg;
      $griddDdeg=$Ddeg;
    }
    else if(($this->gridD0rad+$this->gridSpanDrad)>=($this->fPiOver2))
    { $Lhr=24;
      $RhrNeg=0;
      $Udeg=90;
      $Ddeg=min($this->gridD0rad-$this->gridSpanDrad,min($ldDrad,$rdDrad))*$this->f180OverPi;
      $griddDdeg=$Ddeg;
      $griduDdeg=90;
    }
    else if(($this->gridD0rad-$this->gridSpanDrad)<=-($this->fPiOver2))
    { $Lhr=24;
      $RhrNeg=0;
      $Udeg=max($this->gridD0rad+$this->gridSpanDrad,max($luDrad,$ruDrad))*$this->f180OverPi;
      $Ddeg=-90;
      $griduDdeg=$Udeg;
      $griddDdeg=-90;
    }
      
    $DLhr=($Lhr-$RhrNeg);
    $LStep=min(round((($this->gridDimensions[$this->gridActualDimension][2])/cos($this->gridD0rad))*60)/60,2);
    $DDdeg=($Udeg-$Ddeg);
    $DStep=$this->gridDimensions[$this->gridActualDimension][1];
    
    $LhrStart=(floor($Lhr/$LStep)+1)*$LStep;
    $DdegStart=(floor($Ddeg/$DStep)+1)*$DStep;
  
    $this->gridlLhr=$Lhr;
    $this->gridrLhr=($RhrNeg<0?($RhrNeg+24):$RhrNeg);
	      
    for($d=$DdegStart;$d<=$Udeg;$d+=$DStep)
    { $d=round($d*60)/60;
      $this->canvasX2px=0;
      //jg.setColor(coordLineColor);
      for($l=$Lhr;$l>$RhrNeg;$l-=$LStep/$this->Lsteps)
        $this->gridDrawLineLD($l,$d,($l-($LStep/$this->Lsteps)),$d);
      if($this->canvasX2px&&($this->canvasX2px+15>=$this->gridOffsetXpx+$this->gridWidthXpx))
        $this->labelsArr[]=array($this->gridOffsetXpx+$this->gridWidthXpx+4,$this->canvasY2px-($this->fontSize1a>>2),60,$this->fontSize1b,$this->coordDeclDecToDegMin($d),'left');
      else if($this->canvasX2px&&($this->canvasY2px>=$this->gridOffsetYpx+$this->gridHeightYpx-($this->fontSize1a)))
        $this->labelsArr[]=array($this->canvasX2px-30,$this->gridOffsetYpx+$this->gridHeightYpx+4,60,$this->fontSize1b,$this->coordDeclDecToDegMin($d),'center');
      else if($this->canvasX2px&&($this->canvasY2px<=$this->gridOffsetYpx))
        $this->labelsArr[]=array($this->canvasX2px-30,$this->gridOffsetYpx-($this->fontSize1a),60,$this->fontSize1b,$this->coordDeclDecToDegMin($d),'center');
      else if($this->canvasX2px)
        $this->labelsArr[]=array($this->canvasX2px-30,$this->canvasY2px,60,$this->fontSize1b,$this->coordDeclDecToDegMin($d),'center');
    }
    if($this->gridD0rad<0)
    { for($l=$LhrStart;$l>$RhrNeg;$l-=$LStep)
      { $l=round($l*60)/60;
        $this->canvasX2px=0;
        //jg.setColor(coordLineColor);
        for($d=$Ddeg;$d<$Udeg;$d+=$DStep/$this->Dsteps)
          $this->gridDrawLineLD($l,$d,$l,($d+($DStep/$this->Dsteps)));
        if($this->canvasX2px&&($this->canvasX2px-15<=$this->gridOffsetXpx))
          $this->labelsArr[]=array($this->gridOffsetXpx-64,$this->canvasY2px-($this->fontSize1a>>2),60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'right');
        else if($this->canvasX2px&&($this->canvasX2px+15>=$this->gridOffsetXpx+$this->gridWidthXpx))
          $this->labelsArr[]=array($this->gridOffsetXpx+$this->gridWidthXpx+4,$this->canvasY2px-($this->fontSize1a>>2),60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'left');
        else if($this->canvasY2px&&($this->canvasY2px-15<=$this->gridOffsetYpx))
          $this->labelsArr[]=array($this->canvasX1px-30,$this->gridOffsetYpx-10,60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'center');
        else if(($this->canvasX2px)&&($this->canvasY2px>($this->canvasDimensionYpx>>1)))
          $this->labelsArr[]=array($this->canvasX1px-30,$this->gridOffsetYpx+$this->gridHeightYpx+4,60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'center');
        else if(($this->canvasX2px)&&($this->canvasY2px<($this->canvasDimensionYpx>>1)))
          $this->labelsArr[]=array($this->canvasX1px-30,$this->gridOffsetYpx+$this->gridHeightYpx+4,60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'center');
      }
    }
    else
    { for($l=$LhrStart;$l>$RhrNeg;$l-=$LStep)
      { $l=round($l*60)/60;
        $this->canvasX1px=0;
        //jg.setColor(coordLineColor);
        for($d=$Udeg;$d>$Ddeg;$d-=$DStep/$this->Dsteps)
          $this->gridDrawLineLD($l,$d,$l,($d-($DStep/$this->Dsteps)));
        if($this->canvasX1px&&($this->canvasX1px-15<=$this->gridOffsetXpx))
          $this->labelsArr[]=array($this->gridOffsetXpx-64,$this->canvasY2px-($this->fontSize1a>>2),60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'right');
        else if($this->canvasX1px&&($this->canvasX1px+15>=$this->gridOffsetXpx+$this->gridWidthXpx))
          $this->labelsArr[]=array($this->gridOffsetXpx+$this->gridWidthXpx+4,$this->canvasY2px-($this->fontSize1a>>2),60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'left');
        else if(($this->canvasX1px)&&($this->canvasY2px>($this->canvasDimensionYpx>>1)))
          $this->labelsArr[]=array($this->canvasX1px-30,$this->canvasDimensionYpx-$this->gridOffsetYpx+5,60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'center');
        else if(($this->canvasX1px)&&($this->canvasY2px<($this->canvasDimensionYpx>>1)))
          $this->labelsArr[]=array($this->canvasX1px-30,$this->gridOffsetYpx-($this->fontSize1b)-5,60,$this->fontSize1b,$this->coordHrDecToHrMin($l),'center');
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
  
  function gridDrawLongLineLD($Lhr1,$Ddeg1,$Lhr2,$Ddeg2)
  { $this->gridLDrad($Lhr1,$Ddeg1); $x1=$this->gridLxRad; $y1=$this->gridDyRad;
    $this->gridLDrad($Lhr2,$Ddeg2); $x2=$this->gridLxRad; $y2=$this->gridDyRad;
    if((abs($x1)>$this->fPiOver2)||(abs($y1)>$this->fPiOver2)||(abs($y1)>$this->fPiOver2)||(abs($y2)>$this->fPiOver2)) return 0;
    if(($x1<-$this->gridSpanLrad)&&($x2<-$this->gridSpanLrad)) return 0;
    if(($x1>$this->gridSpanLrad)&&($x2>$this->gridSpanLrad))   return 0;
    if(($y1<-$this->gridSpanDrad)&&($y2<-$this->gridSpanDrad)) return 0;
    if(($y1>$this->gridSpanDrad)&&($y2>$this->gridSpanDrad))   return 0;
  /*  if(x1<-gridSpanLrad) if(x2==x1) return 0; else {y1=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x1=-gridSpanLrad;}
    if(x1>gridSpanLrad)  if(x2==x1) return 0; else {y1=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridSpanLrad; }
    if(y1>gridSpanDrad)  if(y2==y1) return 0; else {x1=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridSpanDrad; }
    if(y1<-gridSpanDrad) if(y2==y1) return 0; else {x1=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y1=-gridSpanDrad;}
    if(x2<-gridSpanLrad) if(x2==x1) return 0; else {y2=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x2=-gridSpanLrad;}
    if(x2>gridSpanLrad)  if(x2==x1) return 0; else {y2=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridSpanLrad;  }
    if(y2>gridSpanDrad)  if(y2==y1) return 0; else {x2=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridSpanDrad;  }
    if(y2<-gridSpanDrad) if(y2==y1) return 0; else {x2=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y2=-gridSpanDrad;}
  */
    if(abs($Lhr1-$Lhr2)>12)
    { if($Lhr1>$Lhr2)
        return max($this->gridDrawLongLineLD($Lhr1,$Ddeg1,24,($Ddeg1+(($Ddeg2-$Ddeg1)*(24-$Lhr1)/($Lhr2+24-$Lhr1)))),$this->gridDrawLongLineLD($Lhr2,$Ddeg2,0,($Ddeg2-(($Ddeg2-$Ddeg1)*($Lhr2)/($Lhr2+24-$Lhr1)))));
      else
        return max($this->gridDrawLongLineLD($Lhr2,$Ddeg2,24,($Ddeg2+(($Ddeg1-$Ddeg2)*(24-$Lhr2)/($Lhr1+24-$Lhr2)))),$this->gridDrawLongLineLD($Lhr1,$Ddeg1,0,($Ddeg1-(($Ddeg1-$Ddeg2)*($Lhr1)/($Lhr1+24-$Lhr2)))));
    }
    //alert(y1+' '+y2+' '+(-gridSpanDrad)+' '+(gridSpanDrad)+' '+x1+' '+x2+' '+(-gridSpanLrad)+' '+(gridSpanLrad));
    $retval = 0;
    $ds = 10.0+max(ceil(1000*abs($x1-$x2) / $this->gridSpanLrad * $this->fPiOver12),ceil(1000*abs($y1-$y2) / $this->gridSpanDrad * $this->fPiOver180));
    for($i=0.0;$i<$ds;)
    { $this->gridLDrad($Lhr1+(($Lhr2-$Lhr1)*$i/$ds),$Ddeg1+(($Ddeg2-$Ddeg1)*$i/$ds)); $x1=$this->gridLxRad; $y1=$this->gridDyRad;
      $i++;
      $this->gridLDrad($Lhr1+(($Lhr2-$Lhr1)*$i/$ds),$Ddeg1+(($Ddeg2-$Ddeg1)*$i/$ds)); $x2=$this->gridLxRad; $y2=$this->gridDyRad;
      if(($x1<-($this->gridSpanLrad))&&($x2<-$this->gridSpanLrad)) continue;
      if(($x1>($this->gridSpanLrad))&&($x2>($this->gridSpanLrad)))   continue;
      if(($y1<-($this->gridSpanDrad))&&($y2<-($this->gridSpanDrad))) continue;
      if(($y1>($this->gridSpanDrad))&&($y2>($this->gridSpanDrad)))   continue;
      if($x1<-($this->gridSpanLrad)) if($x2==$x1) continue; else {$y1=(((-($this->gridSpanLrad)-$x1)/($x2-$x1))*($y2-$y1))+$y1; $x1=-($this->gridSpanLrad);}
      if($x1>($this->gridSpanLrad))  if($x2==$x1) continue; else  {$y1=(((($this->gridSpanLrad)-$x1)/($x2-$x1))*($y2-$y1))+$y1;  $x1=($this->gridSpanLrad); }
      if($y1>($this->gridSpanDrad))  if($y2==$y1) continue; else  {$x1=(((($this->gridSpanDrad)-$y1)/($y2-$y1))*($x2-$x1))+$x1;  $y1=($this->gridSpanDrad); }
      if($y1<-($this->gridSpanDrad)) if($y2==$y1) continue; else {$x1=(((-($this->gridSpanDrad)-$y1)/($y2-$y1))*($x2-$x1))+$x1; $y1=-($this->gridSpanDrad);}
      if(($y1<-($this->gridSpanDrad))||($y1>($this->gridSpanDrad))||($x1<-($this->gridSpanLrad))||($x1>($this->gridSpanLrad))) continue;  
      if($x2<-($this->gridSpanLrad)) if($x2==$x1) continue; else {$y2=(((-($this->gridSpanLrad)-$x1)/($x2-$x1))*($y2-$y1))+$y1; $x2=-($this->gridSpanLrad);}
      if($x2>($this->gridSpanLrad))  if($x2==$x1) continue; else  {$y2=(((($this->gridSpanLrad)-$x1)/($x2-$x1))*($y2-$y1))+$y1;  $x2=($this->gridSpanLrad);  }
      if($y2>($this->gridSpanDrad))  if($y2==$y1) continue; else  {$x2=(((($this->gridSpanDrad)-$y1)/($y2-$y1))*($x2-$x1))+$x1;  $y2=($this->gridSpanDrad);  }
      if($y2<-($this->gridSpanDrad)) if($y2==$y1) continue; else  {$x2=(((-($this->gridSpanDrad)-$y1)/($y2-$y1))*($x2-$x1))+$x1; $y2=-($this->gridSpanDrad);}
      if(($y2<-($this->gridSpanDrad))||($y2>($this->gridSpanDrad))||($x2<-($this->gridSpanLrad))||($x2>($this->gridSpanLrad))) continue;
      $this->canvasX1px=$this->gridCenterOffsetXpx+$this->gridXpx($x1);
      $this->canvasY1px=$this->gridCenterOffsetYpx+$this->gridYpx($y1);
      $this->canvasX2px=$this->gridCenterOffsetXpx+$this->gridXpx($x2);
      $this->canvasY2px=$this->gridCenterOffsetYpx+$this->gridYpx($y2);
      $this->gridLx1rad=$x1;$this->gridDy1rad=$y1;$this->gridLx2rad=$x2;$this->gridDy2rad=$y2;
      $this->pdf->line($this->canvasX1px,$this->canvasY1px,$this->canvasX2px,$this->canvasY2px);
      $retval = 1;
    } 
    return $retval;
  }  
  
  private function gridInit()
  { $this->canvasDimensionXpx=$this->pdf->ez['pageWidth']; 
    $this->canvasDimensionYpx=$this->pdf->ez['pageHeight'];
    $this->gridOffsetXpx=50; 
    $this->gridOffsetYpx=50;
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
  private function gridInitSpecial($thex,$they,$w,$h)
  { $this->canvasDimensionXpx=$this->pdf->ez['pageWidth']; 
    $this->canvasDimensionYpx=$this->pdf->ez['pageHeight'];
    $this->gridOffsetXpx=$thex; $this->gridOffsetYpx=$they;
    
    $this->gridWidthXpx=$w;
    $this->gridWidthXpx2=(($this->gridWidthXpx+1)>>1);
    $this->gridCenterOffsetXpx=$this->gridOffsetXpx+(($this->gridWidthXpx+1)>>1);
    $this->gridHeightYpx=$h;
    $this->gridHeightYpx2=(($this->gridHeightYpx+1)>>1);
    $this->gridCenterOffsetYpx=$this->gridOffsetYpx+(($this->gridHeightYpx+1)>>1);
    $this->lx = $this->gridOffsetXpx;
    $this->rx = $this->gridOffsetXpx+$this->gridWidthXpx;
    $this->ty = $this->gridOffsetYpx+$this->gridHeightYpx;
    $this->by = $this->gridOffsetYpx;
  }
  
  function gridInitScale($gridLHr,$gridDdeg)
  { $this->gridL0rad=$gridLHr*$this->fPiOver12;
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
      //if($Dacc=0) 
      //  echo " res:".$Dacc." ".(sin($Dacc));
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
    $drad=((sin($this->gridD0rad)*sin($Drad))+(cos($this->gridD0rad)*cos($Drad)*cos($Lrad-$this->gridL0rad)));
    if($drad>1) {
      $drad = 1;
    }
    if($drad<-1) {
      $drad = -1;
    }
    $drad = acos($drad);
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
  { $t1 =html_entity_decode(atlasPageFoV).' '.(round($this->gridSpanL*20)/10)." x ".(round($this->gridSpanD*20)/10)."° - ";
	  $t1.=html_entity_decode(atlasPageDSLM).' '.($this->maxshowndsomag==-99?'-':$this->maxshowndsomag)." - ";
	  $t1.=html_entity_decode(atlasPageStarLM).' '.$this->starsmagnitude.' - ';
	  $t1.='('.html_entity_decode(atlasPageCenteredOn).$this->coordHrDecToHrMin($this->atlaspagerahr).','.$this->coordDeclDecToDegMin($this->atlaspagedecldeg).')';
	  $this->pdf->addText($this->gridOffsetXpx,20,$this->fontSize1a,$t1);
	}
  
  function gridXpx($Lrad) 
  { return (($this->gridWidthXpx2*$Lrad/$this->gridSpanLrad));
  }
  
  function gridYpx($Drad)
  { return (($this->gridHeightYpx2*$Drad/$this->gridSpanDrad));
  }

  public  function pdfAtlasTitlePage($nostream=false)  // Creates a pdf atlas page
  { global $objUtil,$instDir,$loggedUser,$objObserver,$objObject;
    $this->theOrientation = $objUtil->checkGetKey('pageorientation','landscape');    
    $this->thePageSize = $objUtil->checkGetKey('pagesize','a4');    
    $this->pdf = new Cezpdf($this->thePageSize, $this->theOrientation);
    $this->pdf->selectFont($instDir.'lib/fonts/Courier.afm');
    $temp='Deepskylog Atlas';
    $this->pdf->addText(100,$this->pdf->ez['pageHeight']-100,32,$temp);
    $this->pdf->line(100,$this->pdf->ez['pageHeight']-45,750,$this->pdf->ez['pageHeight']-45);
    
    global $GALXY,$PLNNB,$GLOCL,$OPNCL,$DRKNB,$EMINB,$CLANB,$GALCL,$QUASR;
    $this->pdf->ellipse(50, 53, 5, 2.5, -45);
    $this->pdf->addTextWrap(60, 50, 300, $this->fontSize1b, html_entity_decode($GALXY), 'left');
    
    $this->pdf->ellipse(50, 70+3, 2.5, 2.5, 0);
    $this->pdf->line(55, 70+3, 52.5, 70+3);
    $this->pdf->line(45, 70+3, 47.5, 70+3);
    $this->pdf->line(50, 70+5.5, 50, 70+8);
    $this->pdf->line(50, 70+0.5, 50, 70-2);
    $this->pdf->addTextWrap(60, 70, 300, $this->fontSize1b, html_entity_decode($PLNNB), 'left');
    
    $this->pdf->ellipse(50, 90+3, 5, 5, 0);
    $this->pdf->line(45, 90+3, 55, 90+3);
    $this->pdf->line(50, 90+8, 50, 90-2);
    $this->pdf->addTextWrap(60, 90, 300, $this->fontSize1b, html_entity_decode($GLOCL), 'left');
     
    $this->pdf->setLineStyle(0.5,'','',array(3));
    $this->pdf->ellipse(50, 110+3, 5, 5, 0);
    $this->pdf->addTextWrap(60, 110, 300, $this->fontSize1b, html_entity_decode($OPNCL), 'left');
    
    $this->pdf->rectangle(45, 130-2, 10, 10);
    $this->pdf->addTextWrap(60, 130, 300, $this->fontSize1b, html_entity_decode($DRKNB), 'left');
    
    $this->pdf->setLineStyle(0.5,'','',array());
    
    $this->pdf->rectangle(45, 210-2, 10, 10);
    $this->pdf->addTextWrap(60, 210, 300, $this->fontSize1b, html_entity_decode($EMINB), 'left');
    
    $this->pdf->rectangle(45.5, 150-1.5, 9, 9);
    $this->pdf->setLineStyle(0.5,'','',array(3));
    $this->pdf->rectangle(44.5, 150-2.5, 11, 11);
    $this->pdf->addTextWrap(60, 150, 300, $this->fontSize1b, html_entity_decode($CLANB), 'left');
    $this->pdf->setLineStyle(0.5,'','',array());
    
    $x=50;
    $y=170+3;
    $d1=12;
    $d2=12;
    $x1=0;
    $x2=0;
    $y1=0;
    $y2=0;
    $x1=$x;
    $y1=$y+(($d2+1)>>1);
    $x2=$x+(($d1+1)>>1);
    $y2=$y+(($d2+1)>>3);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x+(($d1+1)>>1);
    $y1=$y+(($d2+1)>>3);
    $x2=$x+(3*(($d1+1)>>3));
    $y2=$y-(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x+(3*(($d1+1)>>3));
    $y1=$y-(($d2+1)>>1);
    $x2=$x-(3*(($d1+1)>>3));
    $y2=$y-(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x-(3*(($d1+1)>>3));
    $y1=$y-(($d2+1)>>1);
    $x2=$x-(($d1+1)>>1);
    $y2=$y+(($d2+1)>>3);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $x1=$x-(($d1+1)>>1);
    $y1=$y+(($d2+1)>>3);
    $x2=$x;
    $y2=$y+(($d2+1)>>1);
    $this->pdf->line($x1,$y1,$x2,$y2);
    $this->pdf->addTextWrap(60, 170, 300, $this->fontSize1b, html_entity_decode($GALCL), 'left');
    
    $x=50;
    $y=190+3;
    $d1=3;
    $d2=3;
    $this->pdf->line($x-2, $y, $x-$d1-2, $y);
    $this->pdf->line($x, $y-2-$d2, $x, $y-2);
    $this->pdf->line($x, $y+2+$d2, $x, $y+2);
    $this->pdf->line($x+2, $y, $x+2+$d1, $y);
    $this->pdf->addTextWrap(60, 190, 300, $this->fontSize1b, html_entity_decode($QUASR), 'left');
    
    if(!$nostream)
      $this->pdf->Stream(); 
    return 'stepra:999,stepdecl:999,raright:999,declbottom:999';
  }
  public  function pdfAtlas($nostream=false)  // Creates a pdf atlas page
  { global $objUtil,$instDir,$loggedUser,$objObserver,$objObject,$objAtlas;
    if(!(isset($_SESSION['atlasPagesIndex'])))
      $_SESSION['atlasPagesIndex']=Array();
    set_time_limit(120);
	  $this->theItemPage=$objUtil->checkRequestKey('item',0);
    $object='';
    $this->atlaspagerahr=$objUtil->checkRequestKey('ra',0);
	  $this->atlaspagedecldeg=$objUtil->checkRequestKey('decl',0);
	  if($this->atlaspagedecldeg==90)
	    $this->atlaspagedecldeg=89.99;
	  if($this->atlaspagedecldeg==-90)
	    $this->atlaspagedecldeg=-89.99;
    if($object=$objObject->getExactDsObject($objUtil->checkRequestKey('object'),''))
    { $this->atlaspagerahr=$objObject->getDsoProperty($object,'ra',0);
      $this->atlaspagedecldeg=$objObject->getDsoProperty($object,'decl',0);
    }
    $this->gridActualDimension=max(min($objUtil->checkRequestKey('zoom',18),$this->gridMaxDimension),10);
    $this->atlasmagnitude=max(min((int)($objUtil->checkRequestKey('dsos',$this->gridDimensions[$this->gridActualDimension][3])),99),8);
    $this->starsmagnitude=max(min((int)($objUtil->checkRequestKey('stars',$this->gridDimensions[$this->gridActualDimension][3])),16),8);
    $this->fontSize1b=max(min($objUtil->checkRequestKey('fontsize',$this->fontSize1b),9),6);
    $this->fontSize1a=round($this->fontSize1b*1.666);
    $this->theOrientation = $objUtil->checkGetKey('pageorientation','landscape');    
    $this->thePageSize = $objUtil->checkGetKey('pagesize','a4');    
    $this->pdf = new Cezpdf($this->thePageSize, $this->theOrientation);
    $this->pdf->selectFont($instDir.'lib/fonts/Courier.afm');
    $this->pdf->setLineStyle(0.5);
    $this->gridInit();
    $this->gridInitScale($this->atlaspagerahr,$this->atlaspagedecldeg,$this->atlaspagezoomdeg);
    $this->pdf->setStrokeColor(0.9,0.9,0.9);
    $this->pdf->setLineStyle(0.5,'','',array(1));
    $this->gridDrawCoordLines();
    $this->pdf->setLineStyle(0.5,'','',array());
    $this->pdf->setStrokeColor(0.7,0.7,0.7);
    $this->astroDrawConstellations();
    $this->pdf->setStrokeColor(0,0,0);
    $this->astroDrawStarsArr();
    $this->astroDrawObjects($object);
    
    $this->pdf->setColor(1,1,1);
    $this->pdf->filledRectangle(0,0,$this->gridOffsetXpx,$this->canvasDimensionYpx);
    $this->pdf->filledRectangle(0,0,$this->canvasDimensionXpx,$this->gridOffsetYpx);
    $this->pdf->filledRectangle($this->canvasDimensionXpx-$this->gridOffsetXpx,0,$this->gridOffsetXpx,$this->canvasDimensionYpx);
    $this->pdf->filledRectangle(0,$this->canvasDimensionYpx-$this->gridOffsetYpx,$this->canvasDimensionXpx,$this->gridOffsetYpx);
    $this->pdf->setColor(0,0,0);
    $this->gridShowInfo();
    $this->atlasDrawLegend();
    $temp=date('d M Y');
    if($loggedUser)
      $temp=$objObserver->getObserverProperty($loggedUser,'firstname')." ".$objObserver->getObserverProperty($loggedUser,'name')." - ".$temp;
    $this->pdf->addTextWrap($this->canvasDimensionXpx-$this->gridOffsetXpx-(strlen($temp)*5),$this->canvasDimensionYpx-$this->Legend1y-5,strlen($temp)*5,$this->fontSize1b,$temp,'right');
    $this->pdf->setLineStyle(2,'round');
    $this->pdf->rectangle($this->gridOffsetXpx-1,$this->gridOffsetYpx-1,
                         ($this->canvasDimensionXpx-($this->gridOffsetXpx<<1))+2,($this->canvasDimensionYpx-($this->gridOffsetYpx<<1))+2);
        
    for($i=0,$z=count($this->labelsArr);$i<$z;$i++)   
      $this->pdf->addTextWrap($this->labelsArr[$i][0],$this->labelsArr[$i][1],$this->labelsArr[$i][2],$this->labelsArr[$i][3],$this->labelsArr[$i][4],$this->labelsArr[$i][5]);                  
    $temp=html_entity_decode(LangAtlasDataSource);
    $this->pdf->addText($this->gridOffsetXpx,7,$this->fontSizeAck,$temp);
    if($objUtil->checkRequestKey('item',0)!='0')
    { $this->pdf->addText($this->canvasDimensionXpx-30,13,$this->fontSize1a,$objUtil->checkRequestKey('item',0));
      $this->pdf->addText(15,13,$this->fontSize1a,$objUtil->checkRequestKey('item',0));
      $this->pdf->addText($this->canvasDimensionXpx-30,$this->canvasDimensionYpx-20,$this->fontSize1a,$objUtil->checkRequestKey('item',0));
      $this->pdf->addText(15,$this->canvasDimensionYpx-20,$this->fontSize1a,$objUtil->checkRequestKey('item',0));
    }
    $this->pdf->setLineStyle(1);
    
    $atlastype='';
    if($objUtil->checkRequestKey('atlastype',0)==0) $atlastype='DSLOP';
    if($objUtil->checkRequestKey('atlastype',0)==1) $atlastype='DSLLP';
    if($objUtil->checkRequestKey('atlastype',0)==2) $atlastype='DSLDP';
    if($objUtil->checkRequestKey('atlastype',0)==3) $atlastype='DSLOL';
    if($objUtil->checkRequestKey('atlastype',0)==4) $atlastype='DSLLL';
    if($objUtil->checkRequestKey('atlastype',0)==5) $atlastype='DSLDL';
    
    if($this->atlaspagedecldeg>0)
      $pageleft=$objAtlas->calculateAtlasPage($atlastype,$this->gridldLhr,$this->atlaspagedecldeg);
    else
      $pageleft=$objAtlas->calculateAtlasPage($atlastype,$this->gridluLhr,$this->atlaspagedecldeg);
    $thetextsize=$this->pdf->getTextWidth($this->fontSize1a,$pageleft);
    $this->pdf->setColor(1,1,1);
    $this->pdf->filledrectangle($this->gridOffsetXpx,$this->gridCenterOffsetYpx-($this->fontSize1a>>1)-1,$thetextsize+6,$this->fontSize1a+2);
    $this->pdf->setColor(0,0,0);
    $this->pdf->rectangle($this->gridOffsetXpx,$this->gridCenterOffsetYpx-($this->fontSize1a>>1)-1,$thetextsize+6,$this->fontSize1a+2);
    $this->pdf->addText($this->gridOffsetXpx+2,$this->gridCenterOffsetYpx-($this->fontSize1a>>1)+1,$this->fontSize1a,$pageleft);
    
    if($this->atlaspagedecldeg>0)
      $pageright=$objAtlas->calculateAtlasPage($atlastype,$this->gridrdLhr,$this->atlaspagedecldeg);
    else
      $pageright=$objAtlas->calculateAtlasPage($atlastype,$this->gridruLhr,$this->atlaspagedecldeg);
    $thetextsize=$this->pdf->getTextWidth($this->fontSize1a,$pageright);
    $this->pdf->setColor(1,1,1);
    $this->pdf->filledrectangle($this->canvasDimensionXpx-$this->gridOffsetXpx-$thetextsize-6,$this->gridCenterOffsetYpx-($this->fontSize1a>>1)-1,$thetextsize+6,$this->fontSize1a+2);
    $this->pdf->setColor(0,0,0);
    $this->pdf->rectangle($this->canvasDimensionXpx-$this->gridOffsetXpx-$thetextsize-6,$this->gridCenterOffsetYpx-($this->fontSize1a>>1)-1,$thetextsize+6,$this->fontSize1a+2);
    $this->pdf->addText($this->canvasDimensionXpx-$this->gridOffsetXpx-$thetextsize-3,$this->gridCenterOffsetYpx-($this->fontSize1a>>1)+1,$this->fontSize1a,$pageright);
        
    if($this->atlaspagedecldeg>0)
      $this->gridLDinvRad($this->gridCenterOffsetXpx,$this->gridOffsetYpx);
    else
      $this->gridDyRad=$this->gridluDdeg*$this->fPiOver180;
    $pageupper=$objAtlas->calculateAtlasPage($atlastype,$this->atlaspagerahr,$this->gridDyRad*$this->f180OverPi);
    if($pageupper!=$this->theItemPage)
    {	$thetextsize=$this->pdf->getTextWidth($this->fontSize1a,$pageupper);
    	$this->pdf->setColor(1,1,1);
    	$this->pdf->filledrectangle($this->gridCenterOffsetXpx+-($thetextsize>>1)-3,$this->canvasDimensionYpx-$this->gridOffsetYpx-$this->fontSize1a-2,$thetextsize+6,$this->fontSize1a+2);
    	$this->pdf->setColor(0,0,0);
    	$this->pdf->rectangle($this->gridCenterOffsetXpx+-($thetextsize>>1)-3,$this->canvasDimensionYpx-$this->gridOffsetYpx-$this->fontSize1a-2,$thetextsize+6,$this->fontSize1a+2);
    	$this->pdf->addText($this->gridCenterOffsetXpx-($thetextsize>>1),$this->canvasDimensionYpx-$this->gridOffsetYpx-$this->fontSize1a+1,$this->fontSize1a,$pageupper); 
    }
    
    if($this->atlaspagedecldeg>0)
      $this->gridDyRad=$this->gridldDdeg*$this->fPiOver180;
    else
      $this->gridLDinvRad($this->gridCenterOffsetXpx,$this->gridOffsetYpx+$this->gridHeightYpx);
    $pageunder=$objAtlas->calculateAtlasPage($atlastype,$this->atlaspagerahr,$this->gridDyRad*$this->f180OverPi);
    if($pageunder!=$this->theItemPage)
    { $thetextsize=$this->pdf->getTextWidth($this->fontSize1a,$pageunder);
      $this->pdf->setColor(1,1,1);
      $this->pdf->filledrectangle($this->gridCenterOffsetXpx+-($thetextsize>>1)-3,$this->gridOffsetYpx,$thetextsize+6,$this->fontSize1a+2);
      $this->pdf->setColor(0,0,0);
      $this->pdf->rectangle($this->gridCenterOffsetXpx+-($thetextsize>>1)-3,$this->gridOffsetYpx,$thetextsize+6,$this->fontSize1a+2);
      $this->pdf->addText($this->gridCenterOffsetXpx-($thetextsize>>1),$this->gridOffsetYpx+3,$this->fontSize1a,$pageunder);
    } 
    if(!$nostream)
      $this->pdf->Stream(); 
    return;
  }
  public function pdfAtlasIndex()  // Creates a pdf atlas page
  { global $objUtil,$instDir,$loggedUser,$objObserver,$objObject;
    set_time_limit(1200);
   
    $indexFontSize = 8;
  
    $topborderIndexWidth=50;
    $sideborderIndexWidth=50;
    $columnIndexWidth=200; 
    $nameIndexMaxWidth=160;
    $columnIndexSeparation=25;
    
    $extraspacerdotline=10;
    
    $this->theOrientation = $objUtil->checkGetKey('pageorientation','landscape');    
    $this->thePageSize = $objUtil->checkGetKey('pagesize','a4');    
    $this->pdf = new Cezpdf($this->thePageSize, $this->theOrientation);
    $this->pdf->selectFont($instDir.'lib/fonts/Courier.afm');
    $this->canvasDimensionXpx=$this->pdf->ez['pageWidth']; 
    $this->canvasDimensionYpx=$this->pdf->ez['pageHeight'];
    $this->pdf->selectFont($instDir.'lib/fonts/Courier.afm');
    $this->pdf->addText($sideborderIndexWidth, $this->canvasDimensionYpx-$topborderIndexWidth, 12, "Index");
    $this->pdf->line($sideborderIndexWidth,$this->canvasDimensionYpx-$topborderIndexWidth-5,$this->canvasDimensionXpx-$sideborderIndexWidth,$this->canvasDimensionYpx-$topborderIndexWidth-5);
    $this->pdf->newPage();
    if(count($_SESSION['atlasPagesIndex'])>0)
    { $theindex=$_SESSION['atlasPagesIndex'];
	    uksort($theindex,"strnatcmp");
	    
	    $j=0;
	    $columnX=0;
	    while(list($theobject,$theobjectdata)=each($theindex))
	    { if($this->canvasDimensionYpx-$topborderIndexWidth-($j*10)<$topborderIndexWidth)
	      { $j=0;
	        $columnX+=($columnIndexWidth+$columnIndexSeparation);
	        if(($columnX+$columnIndexWidth)>($this->canvasDimensionXpx-$sideborderIndexWidth))
	        { $this->pdf->newPage();
	        	$columnX=0;
	        }
	      }
	    	$thetextwidth0=min($this->pdf->getTextWidth($indexFontSize,$theobject),$nameIndexMaxWidth);
	      $thetextwidth3=$this->pdf->getTextWidth($indexFontSize,$theobjectdata[1]);
	      $this->pdf->addTextWrap($sideborderIndexWidth+$columnX+$thetextwidth0+$extraspacerdotline,$this->canvasDimensionYpx-$topborderIndexWidth-($j*10),$columnIndexWidth-$thetextwidth0-$thetextwidth3-$extraspacerdotline-$extraspacerdotline,$indexFontSize,'......................................................................................................................................................');
	    	$this->pdf->addTextWrap($sideborderIndexWidth+$columnX,$this->canvasDimensionYpx-$topborderIndexWidth-($j*10),$thetextwidth0,$indexFontSize,$theobject);
	    	$this->pdf->addTextWrap($sideborderIndexWidth+$columnX+$columnIndexWidth-100,$this->canvasDimensionYpx-$topborderIndexWidth-($j*10),100,$indexFontSize,$theobjectdata[1],'right');
	    	$j++;
	    }
    }
    else
      $this->pdf->addText(100,100,12,LangNoIndexEntries);
    $this->pdf->Stream(); 
  }
  public function pdfAtlasObjectSets($item,$theSet,$thedsos,$thestars,$thephotos,$datapage='false',$reportlayoutselect='',$ephemerides='true',$yearephemerides=false)
  { global $objUtil,$instDir,$loggedUser,$objObserver,$objObject,$tmpDir;
    if($item<count($_SESSION['Qobj']))
    { $this->pdfAtlasObjectSet($_SESSION['Qobj'][$item]['objectname'],$_SESSION['Qobj'][$item]['showname'],$theSet,$thedsos,$thestars,$thephotos,$datapage,$reportlayoutselect,$ephemerides,$yearephemerides,true);
      $_SESSION['allonepass'.$item]=$this->pdf->output();
      echo $item;
    }
    else
      echo -1;
  }
  public  function pdfAtlasAtlasPages($item)
  { global $objUtil,$instDir,$loggedUser,$objObserver,$objObject,$tmpDir;
    if($item==0)
      $result=$this->pdfAtlasTitlePage(true);
    else
      $result=$this->pdfAtlas(true);
    $_SESSION['allonepass'.$item]=$this->pdf->output();
    echo '{item:'.$item.','.$result.'}';
  }
  private function filterdegpart($thevalue)
  { return substr($thealtitude=html_entity_decode($thevalue),0,strpos($thealtitude,'°')+1);
  }
  public function pdfAtlasObjectSet($theobject,$theShowname,$theSet,$thedsos,$thestars,$thephotos,$datapage='false',$reportlayoutselect='',$ephemerides='true',$yearephemerides=false,$nostream=false)
  { global $theMonth,$theDay,$theYear,$dateformat,$baseURL,$objList,$objInstrument,$objLocation,$objUtil,$instDir,$loggedUser,$loggedUserName,$objObserver,$objObject,$objPresentations,$objReportLayout,$listname,$myList;
    $firstpage = true;
    $astroObjects=array();
    $indexlist=array();
    set_time_limit(300);
    $raDSS=$objPresentations->raToStringDSS2($objObject->getDsoProperty($theobject,'ra'));
    $declDSS=$objPresentations->decToStringDSS2($objObject->getDsoProperty($theobject,'decl'));
    $radeclALADIN=$objPresentations->radeclToStringALADIN($objObject->getDsoProperty($theobject,'ra'),$objObject->getDsoProperty($theobject,'decl'));
    $_GET['pdfTitle']=$theShowname;
    $this->pdf = new Cezpdf('a4', 'landscape');
    $this->canvasDimensionXpx=$this->pdf->ez['pageWidth']; 
    $this->canvasDimensionYpx=$this->pdf->ez['pageHeight'];
    $this->pdf->selectFont($instDir.'lib/fonts/Courier.afm');
    $this->fontSize1b=max(min($objUtil->checkRequestKey('fontsize',$this->fontSize1b),9),6);
    $this->fontSize1a=round($this->fontSize1b*1.666);
    $this->atlaspagerahr=$objObject->getDsoProperty($theobject,'ra',0);
    $this->atlaspagedecldeg=$objObject->getDsoProperty($theobject,'decl',0);
    $this->pdf->addTextWrap(0,10,$this->pdf->ez['pageWidth']-10,10,$theShowname,'right');
    if($datapage=='true')
    { $firstpage=false;
    	$theobjectdata=$objObject->getSeenObjectDetails(array($theobject => array(0,$theobject)));
      $theobjectdata=$theobjectdata[0];
      $liney=$this->canvasDimensionYpx-50;
      $this->pdf->addTextWrap( 50, $liney, $this->canvasDimensionXpx-100, 15, 'Atlas pages for '.$theShowname,  'center');
      $liney-=5;
      $this->pdf->line(50,$liney,$this->canvasDimensionXpx-50,$liney);
      $liney-=15;
      $this->pdf->addTextWrap(50, $liney, 450, 10, Reportaltname.": ".($theobjectdata['altname']?$theobjectdata['altname']:'-'),  'left');
      if($loggedUser)
      { $this->pdf->addTextWrap( 550, $liney, 200, 10, LangViewObservationField2.': '.$loggedUserName,  'left');
      }
      $liney-=25;
      $this->pdf->addTextWrap( 50, $liney, 200, 10, Reportobjectra.': '.$theobjectdata['objectrahms'],  'left');
      $this->pdf->addTextWrap(300, $liney, 200, 10, Reportobjectmagnitude.': '.($theobjectdata['objectmagnitude']?$theobjectdata['objectmagnitude']:'-'),  'left');
      $this->pdf->addTextWrap(550, $liney, 200, 10, Reportobjectconstellationfull.': '.$theobjectdata['objectconstellationfull'],  'left');
      $liney-=15;
      $this->pdf->addTextWrap( 50, $liney, 200, 10, Reportobjectdecl.': '.$theobjectdata['objectdecldms'],  'left');
      $this->pdf->addTextWrap(300, $liney, 200, 10, Reportobjectsurfacebrightness.': '.($theobjectdata['objectsurfacebrightness']?$theobjectdata['objectsurfacebrightness']:'-'),  'left');
      $this->pdf->addTextWrap(550, $liney, 200, 10, Reportobjecttypefull.': '.$theobjectdata['objecttypefull'],  'left');
      $liney-=15;
      $this->pdf->addTextWrap( 50, $liney, 200, 10, LangViewObjectField9.': '.($theobjectdata['objectsize']?$theobjectdata['objectsize']:'-'),  'left');
      $this->pdf->addTextWrap(300, $liney, 200, 10, LangViewObjectField12.': '.(($pa=$theobjectdata['objectpa'])==999?'-':$pa),  'left');
      $this->pdf->addTextWrap(550, $liney, 200, 10, LangOverviewObjectsHeader7.': '.$theobjectdata['objectseen'],  'left');
      $liney-=20;
      if($loggedUser)
      { $this->pdf->addTextWrap( 50, $liney, 4500, 10, LangViewObjectFieldOptimumDetectionMagnification.': '.$theobjectdata['objectoptimalmagnification'],  'left');
        $this->pdf->addTextWrap(550, $liney, 200, 10, LangOverviewObjectsHeader8.': '.($theobjectdata['objectlastseen']?$theobjectdata['objectlastseen']:'-'),  'left');
        $liney-=15;
      	$this->pdf->addTextWrap( 50, $liney, 750, 10, Reportobjectcontrast.': '.($theobjectdata['objectcontrast']!='0.0'?$theobjectdata['objectcontrast'].' - ':'').stripslashes($theobjectdata['objectcontrastpopup']),  'left');
      	$liney-=15;
        if($ephemerides=='true')
        { $liney-=15;
          $theYear=$objUtil->checkSessionKey('globalYear',date("Y"));
          $theMonth=$objUtil->checkSessionKey('globalMonth',date("n"));
          $theDay=$objUtil->checkSessionKey('globalDay',date('j'));
          $dateTimeText0=date($dateformat, mktime(0, 0, 0, $theMonth, $theDay, $theYear));
          $dateTimeText1=date($dateformat, mktime(0, 0, 0, $theMonth, $theDay, $theYear)+(60*60*24));
        	if($dateformat=='d-M-Y')
        	{ if(substr($dateTimeText0,-8)==substr($dateTimeText1,-8))
              $dateTimeText0=substr($dateTimeText0,0,2);
            elseif(substr($dateTimeText0,-5)==substr($dateTimeText1,-5))
              $dateTimeText0=substr($dateTimeText0,0,5);
          }
					elseif($dateformat=="d/m/Y")
					{ if(substr($dateTimeText0,-7)==substr($dateTimeText1,-7))
					    $dateTimeText0=substr($dateTimeText0,0,2);
					  elseif(substr($dateTimeText0,-5)==substr($dateTimeText1,-5))
					    $dateTimeText0=substr($dateTimeText0,0,5);
					}
          elseif($dateformat=='d-m-Y')
        	{ if(substr($dateTimeText0,-7)==substr($dateTimeText1,-7))
              $dateTimeText0=substr($dateTimeText0,0,2);
            elseif(substr($dateTimeText0,-5)==substr($dateTimeText1,-5))
              $dateTimeText0=substr($dateTimeText0,0,5);
          }
          elseif($dateformat=='M-d-Y')
        	{ if(substr($dateTimeText0,0,3)==substr($dateTimeText1,0,3))
        	  { $dateTimeText0=substr($dateTimeText0,0,6);
        	    $dateTimeText1=substr($dateTimeText1,-7);
        	  }
        	  elseif(substr($dateTimeText0,-5)==substr($dateTimeText1,-5))
              $dateTimeText0=substr($dateTimeText0,0,6);
          }
          $this->pdf->addTextWrap( 50, $liney, 600, 10, ReportEpehemeridesFor.' '.$dateTimeText0."-".$dateTimeText1.' '.ReportEpehemeridesIn.' '.$objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser,'stdlocation'),'name'). ReportInLocalTime ,  'left');
          $liney-=5;
          $this->pdf->line(50,$liney,$this->canvasDimensionXpx-50,$liney);
        	$liney-=15;
          $this->pdf->addTextWrap( 50, $liney, 150, 10, LangMoonSun.' '.LangMoonSet.': '.$_SESSION['efemerides']['sset'],  'left');
          $this->pdf->addTextWrap(250, $liney, 150, 10, LangMoonSun.' '.LangMoonRise.': '.$_SESSION['efemerides']['srise'],  'left');
          $this->pdf->addTextWrap(450, $liney, 150, 10, LangMoon.' '.LangMoonRise.': '.$_SESSION['efemerides']['moon0'],  'left');
          $this->pdf->addTextWrap(650, $liney, 150, 10, LangMoon.' '.LangMoonSet.': '.$_SESSION['efemerides']['moon2'],  'left');
          $liney-=15;
          $this->pdf->addTextWrap( 50, $liney, 350, 10, LangAstronomicalTwilight.' '.LangFrom.' '.$_SESSION['efemerides']['astroe'].LangTo.$_SESSION['efemerides']['astrob'],  'left');
          $this->pdf->addTextWrap(450, $liney, 350, 10, LangNauticalTwilight.' '.LangFrom.' '.$_SESSION['efemerides']['naute'].LangTo.$_SESSION['efemerides']['nautb'],  'left');        
          $liney-=15;
          $this->pdf->addTextWrap( 50, $liney, 150, 10, LangpdfseriesObject.' '.LangMoonRise.': '.$theobjectdata['objectrise'],  'left');
          $this->pdf->addTextWrap(250, $liney, 150, 10, LangTransit.': '.$theobjectdata['objecttransit'],  'left');
          $this->pdf->addTextWrap(450, $liney, 150, 10, LangMoonSet.': '.$theobjectdata['objectset'],  'left');
          $this->pdf->addTextWrap(650, $liney, 150, 10, LangMaxAltitude.": ".$objPresentations->decToString($theobjectdata['objectmaxaltitude'],0),  'left');
        
          $liney-=15;
        }

        $liney-=10;
        if($objObserver->getObserverProperty($loggedUser, 'stdLocation') && ($yearephemerides=='true'))
        { $this->pdf->addTextWrap( 50, $liney, $this->canvasDimensionXpx-100, 10, ReportEpehemeridesFor.' '.$theShowname.' '.ReportEpehemeridesIn.' '.$objLocation->getLocationPropertyFromId($objObserver->getObserverProperty($loggedUser,'stdlocation'),'name'). ReportInLocalTime,  'left');
          $liney-=5;
          $this->pdf->line(50,$liney,$this->canvasDimensionXpx-50,$liney);
          $theLocation=$objObserver->getObserverProperty($loggedUser, 'stdLocation');	   
          $liney-=15;
          $object=$theobject;
          $longitude = 1.0 * $objLocation->getLocationPropertyFromId($theLocation, 'longitude');
          $latitude = 1.0 * $objLocation->getLocationPropertyFromId($theLocation, 'latitude');
          $timezone=$objLocation->getLocationPropertyFromId($theLocation,'timezone');
          $dateTimeZone=new DateTimeZone($timezone);
          for($i=1;$i<13;$i++)
			    { $datestr=sprintf("%02d",$i)."/".sprintf("%02d",1)."/".$_SESSION['globalYear'];
	          $dateTime = new DateTime($datestr, $dateTimeZone);
	          $timedifference = $dateTimeZone->getOffset($dateTime);
	          if (strncmp($timezone, "Etc/GMT", 7)==0) 
	            $timedifference = -$timedifference;
			      date_default_timezone_set ("UTC");
						$theTimeDifference1[$i]=$timedifference;
			      $theEphemerides1[$i]=$objObject->getEphemerides($object,1,$i,2010);
			      $theNightEphemerides1[$i]=date_sun_info(strtotime("2010"."-".$i."-"."1"), $latitude, $longitude);
						$datestr=sprintf("%02d",$i)."/".sprintf("%02d",1)."/".$_SESSION['globalYear'];
			      $dateTime = new DateTime($datestr, $dateTimeZone);
			      $timedifference = $dateTimeZone->getOffset($dateTime);
			      if (strncmp($timezone, "Etc/GMT", 7)==0) 
			        $timedifference = -$timedifference;
			      date_default_timezone_set ("UTC");
						$theTimeDifference15[$i]=$timedifference;
			      $theEphemerides15[$i]=$objObject->getEphemerides($object,15,$i,2010);
			      $theNightEphemerides15[$i]=date_sun_info(strtotime("2010"."-".$i."-"."15"), $latitude, $longitude);	
					}
			    $this->pdf->addTextWrap( 50, $liney, 100, 8, LangMonth,'center');
			    for($i=1;$i<7;$i++)
			    { $this->pdf->addTextWrap( 100+(100*$i), $liney, 50, 8, $i,'center');
			    }
			    $liney-=15;
			    $this->pdf->addTextWrap( 50, $liney, 100, 8, LangMaxAltitude,'center');
				  for($i=1;$i<7;$i++)
				  { $colorclass="";
					  $colorclass2="";
					  if($i==1)
					  { if(($theEphemerides1[$i]['altitude']!='-') &&
					       (($theEphemerides1[$i]['altitude']==$theEphemerides15[$i]['altitude']) ||
					        ($theEphemerides1[$i]['altitude']==$theEphemerides15[12]['altitude'])))
					    { $colorclass="<c:uline><b>";
					      $colorclass2="</c:uline></b>";
					    }
					  }
			      else
			        if(($theEphemerides1[$i]['altitude']!='-') && 
			           (($theEphemerides1[$i]['altitude']==$theEphemerides15[$i]['altitude']) ||
					        ($theEphemerides1[$i]['altitude']==$theEphemerides15[$i-1]['altitude'])))
					    { $colorclass="<c:uline><b>";
					      $colorclass2="</c:uline></b>";
					    }
			      $this->pdf->addTextWrap( 50+(100*$i), $liney, 50, 8, $colorclass.$this->filterdegpart($theEphemerides1[$i]['altitude']).$colorclass2,'center');
			      $colorclass="";
					  $colorclass2="";
  	        if(($theEphemerides15[$i]['altitude']!='-') && 
	           (($theEphemerides15[$i]['altitude']==$theEphemerides1[$i]['altitude']) ||
			        ($theEphemerides15[$i]['altitude']==$theEphemerides1[$i+1]['altitude'])))
			      { $colorclass="<c:uline><b>";
			        $colorclass2="</c:uline></b>";
			      }
			      $this->pdf->addTextWrap( 100+(100*$i), $liney, 50, 8, $colorclass.$this->filterdegpart($theEphemerides15[$i]['altitude']).$colorclass2,'center');
					}
		      $colorclass="";
		      $colorclass2="";
					if(($theEphemerides1[7]['altitude']!='-') &&
					       (($theEphemerides1[7]['altitude']==$theEphemerides15[7]['altitude']) ||
					        ($theEphemerides1[7]['altitude']==$theEphemerides15[6]['altitude'])))
		      { $colorclass="<c:uline><b>";
		        $colorclass2="</c:uline></b>";
		      }
			    $this->pdf->addTextWrap( 50+(100*7), $liney, 50, 8,  $colorclass.$this->filterdegpart($theEphemerides1[7]['altitude']).$colorclass2,'center');

          $liney-=15;
					$this->pdf->addTextWrap( 50, $liney, 100, 8, LangTransit,'center');
					for($i=1;$i<7;$i++)
					{ $colorclass="";
					  $colorclass2="";
					  if((date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"])!="00:00") && 
					      $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides1[$i]['transit'],
					                               date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"]+$theTimeDifference1[$i]),
					                               date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_begin"]+$theTimeDifference1[$i])))
					  { $colorclass="<c:uline><b>";
					    $colorclass2="</c:uline></b>";
					  }
					  $this->pdf->addTextWrap(50+(100*$i), $liney  , 50, 8, $colorclass.$theEphemerides1[$i]['transit'].$colorclass2,"center");
					  $colorclass="";
					  $colorclass2="";
					  if((date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])!="00:00") && 
					      $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides15[$i]['transit'],
					                               date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"]+$theTimeDifference15[$i]),
					                               date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_begin"]+$theTimeDifference15[$i])))
					  { $colorclass="<c:uline><b>";
					    $colorclass2="</c:uline></b>";
					  }
					  $this->pdf->addTextWrap(100+(100*$i), $liney, 50, 8, $colorclass.$theEphemerides15[$i]['transit'].$colorclass2,"center");
					}
		      $colorclass="";
		      $colorclass2="";
					if((date("H:i", $theNightEphemerides1[7]["astronomical_twilight_end"])!="00:00") && 
					     $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides1[7]['transit'],
					                              date("H:i", $theNightEphemerides1[7]["astronomical_twilight_end"]+$theTimeDifference1[7]),
					                              date("H:i", $theNightEphemerides1[7]["astronomical_twilight_begin"]+$theTimeDifference1[7])))
					{ $colorclass="<c:uline><b>";
					  $colorclass2="</c:uline></b>";
					}
					$this->pdf->addTextWrap(50+(100*$i), $liney  , 50, 8, $colorclass.$theEphemerides1[7]['transit'].$colorclass2,"center");
			  			    
			    $liney-=15;
			    $this->pdf->addTextWrap( 50, $liney-4, 100, 8, LangAstroNight,'center');
			    for($i=1;$i<7;$i++)
			    { if(date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"])=="00:00")
			        $this->pdf->addTextWrap(50+(100*$i), $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap(50+(100*$i), $liney  , 50, 8,date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"]  +$theTimeDifference1[$i]),"center");
			        $this->pdf->addTextWrap(50+(100*$i), $liney-7, 50, 8,date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_begin"]+$theTimeDifference1[$i]),'center');
			      }
			      if(date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"])=="00:00")
			        $this->pdf->addTextWrap(100+(100*$i), $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap(100+(100*$i), $liney  , 50, 8,date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"]  +$theTimeDifference15[$i]),"center");
			        $this->pdf->addTextWrap(100+(100*$i), $liney-7, 50, 8,date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_begin"]+$theTimeDifference15[$i]),'center');
			      }
			    }
			    if(date("H:i", $theNightEphemerides1[7]["astronomical_twilight_end"])=="00:00")
			      $this->pdf->addTextWrap(50+(100*7), $liney-7, 50, 8, "-", "center");
			    else
			    { $this->pdf->addTextWrap(50+(100*7), $liney  , 50, 8,date("H:i", $theNightEphemerides1[7]["astronomical_twilight_end"]  +$theTimeDifference1[7]),"center");
			      $this->pdf->addTextWrap(50+(100*7), $liney-7, 50, 8,date("H:i", $theNightEphemerides1[7]["astronomical_twilight_begin"]+$theTimeDifference1[7]),'center');
			    }

			    $liney-=25;
			    $this->pdf->addTextWrap( 50, $liney-4, 100, 8, LangNauticalNight,'center');
			    for($i=1;$i<7;$i++)
			    { if(date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"])=="00:00")
			        $this->pdf->addTextWrap(50+(100*$i), $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap(50+(100*$i), $liney  , 50, 8,date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"]  +$theTimeDifference1[$i]),"center");
			        $this->pdf->addTextWrap(50+(100*$i), $liney-7, 50, 8,date("H:i", $theNightEphemerides1[$i]["nautical_twilight_begin"]+$theTimeDifference1[$i]),'center');
			      }
			      if(date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])=="00:00")
			        $this->pdf->addTextWrap(100+(100*$i), $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap(100+(100*$i), $liney  , 50, 8,date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"]  +$theTimeDifference15[$i]),"center");
			        $this->pdf->addTextWrap(100+(100*$i), $liney-7, 50, 8,date("H:i", $theNightEphemerides15[$i]["nautical_twilight_begin"]+$theTimeDifference15[$i]),'center');
			      }
			    }
			    if(date("H:i", $theNightEphemerides1[7]["nautical_twilight_end"])=="00:00")
			      $this->pdf->addTextWrap(50+(100*7), $liney-7, 50, 8, "-", "center");
			    else
			    { $this->pdf->addTextWrap(50+(100*7), $liney  , 50, 8,date("H:i", $theNightEphemerides1[7]["nautical_twilight_end"]  +$theTimeDifference1[7]),"center");
			      $this->pdf->addTextWrap(50+(100*7), $liney-7, 50, 8,date("H:i", $theNightEphemerides1[7]["nautical_twilight_begin"]+$theTimeDifference1[7]),'center');
			    }
			    
			    $liney-=25;
			    $this->pdf->addTextWrap( 50, $liney-4, 100, 8, LangObjectRiseSet2,'center');
          for($i=1;$i<7;$i++)
			    { if($theEphemerides1[$i]['rise']=='-')
			        $this->pdf->addTextWrap(50+(100*$i), $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap(50+(100*$i), $liney  , 50, 8,$theEphemerides1[$i]['rise'],"center");
			        $this->pdf->addTextWrap(50+(100*$i), $liney-7, 50, 8,$theEphemerides1[$i]['set'],'center');
			      }
	          if($theEphemerides1[$i]['rise']=='-')
			        $this->pdf->addTextWrap(100+(100*$i), $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap(100+(100*$i), $liney  , 50, 8,($theEphemerides15[$i]['rise']),"center");
			        $this->pdf->addTextWrap(100+(100*$i), $liney-7, 50, 8,($theEphemerides15[$i]['set']),'center');
			      }
			    }       
          if($theEphemerides1[7]['rise']=='-')
		        $this->pdf->addTextWrap(50+(100*7), $liney-4, 50, 8, "-", "center");
		      else
		      { $this->pdf->addTextWrap(50+(100*7), $liney  , 50, 8,$theEphemerides1[7]['rise'],"center");
		        $this->pdf->addTextWrap(50+(100*7), $liney-7, 50, 8,$theEphemerides1[7]['set'],'center');
		      }
			    
			    $liney-=35;
			    $this->pdf->addTextWrap( 50, $liney, 100, 8, LangMonth,'center');
			    for($i=7;$i<13;$i++)
			    { $this->pdf->addTextWrap( 100+(100*$i)-600, $liney, 50, 8, $i,'center');
			    }
			    $liney-=15;
			    $this->pdf->addTextWrap( 50, $liney, 100, 8, LangMaxAltitude,'center');
			    for($i=7;$i<13;$i++)
				  { $colorclass="";
					  $colorclass2="";
					  if(($theEphemerides1[$i]['altitude']!='-') && 
			         (($theEphemerides1[$i]['altitude']==$theEphemerides15[$i]['altitude']) ||
					      ($theEphemerides1[$i]['altitude']==$theEphemerides15[$i-1]['altitude'])))
					  { $colorclass="<c:uline><b>";
					    $colorclass2="</c:uline></b>";
					  }
			      $this->pdf->addTextWrap((100*$i)-550, $liney, 50, 8, $colorclass.$this->filterdegpart($theEphemerides1[$i]['altitude']).$colorclass2,'center');
		        $colorclass="";
		        $colorclass2="";
			      if($i==12)
					  { if(($theEphemerides15[$i]['altitude']!='-') &&
					       (($theEphemerides15[$i]['altitude']==$theEphemerides1[$i]['altitude']) ||
					        ($theEphemerides15[$i]['altitude']==$theEphemerides1[1]['altitude'])))
					      { $colorclass="<c:uline><b>";
					        $colorclass2="</c:uline></b>";
					      }
					  }
			      else
			        if(($theEphemerides15[$i]['altitude']!='-') && 
			           (($theEphemerides15[$i]['altitude']==$theEphemerides1[$i]['altitude']) ||
					        ($theEphemerides15[$i]['altitude']==$theEphemerides1[$i+1]['altitude'])))
					      { $colorclass="<c:uline><b>";
					        $colorclass2="</c:uline></b>";
					      }
			      $this->pdf->addTextWrap((100*$i)-500, $liney, 50, 8, $colorclass.$this->filterdegpart($theEphemerides15[$i]['altitude']).$colorclass2,'center');
					}
		      $colorclass="";
		      $colorclass2="";
					if(($theEphemerides1[1]['altitude']!='-') &&
					       (($theEphemerides1[1]['altitude']==$theEphemerides15[12]['altitude']) ||
					        ($theEphemerides1[1]['altitude']==$theEphemerides15[1]['altitude'])))
		      { $colorclass="<c:uline><b>";
		        $colorclass2="</c:uline></b>";
		      }
			    $this->pdf->addTextWrap(50+(100*7), $liney, 50, 8,  $colorclass.$this->filterdegpart($theEphemerides1[1]['altitude']).$colorclass2,'center');

			    
          $liney-=15;
					$this->pdf->addTextWrap( 50, $liney, 100, 8, LangTransit,'center');
					for($i=7;$i<13;$i++)
					{ $colorclass="";
					  $colorclass2="";
					  if((date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"])!="00:00") && 
					        $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides1[$i]['transit'],
					                                  date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"]+$theTimeDifference1[$i]),
					                                  date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_begin"]+$theTimeDifference1[$i])))
					  { $colorclass="<c:uline><b>";
					    $colorclass2="</c:uline></b>";
					  }
					  $this->pdf->addTextWrap((100*$i)-550, $liney  , 50, 8, $colorclass.$theEphemerides1[$i]['transit'].$colorclass2,"center");
		        $colorclass="";
		        $colorclass2="";
					  if((date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])!="00:00") && 
					        $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides15[$i]['transit'],
					                                  date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"]+$theTimeDifference15[$i]),
					                                  date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_begin"]+$theTimeDifference15[$i])))
					  { $colorclass="<c:uline><b>";
					    $colorclass2="</c:uline></b>";
					  }
					  $this->pdf->addTextWrap((100*$i)-500, $liney, 50, 8, $colorclass.$theEphemerides15[$i]['transit'].$colorclass2,"center");
					}
		      $colorclass="";
		      $colorclass2="";
		      if((date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"])!="00:00") && 
		            $objUtil->checkNightHourMinuteBetweenOthers($theEphemerides1[1]['transit'],
		                                     date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"]+$theTimeDifference1[1]),
		                                     date("H:i", $theNightEphemerides1[1]["astronomical_twilight_begin"]+$theTimeDifference1[1])))
					  { $colorclass="<c:uline><b>";
					    $colorclass2="</c:uline></b>";
					  }
					$this->pdf->addTextWrap((100*$i)-550, $liney  , 50, 8, $colorclass.$theEphemerides1[1]['transit'].$colorclass2,"center");
			    
			    $liney-=15;
			    $this->pdf->addTextWrap( 50, $liney-4, 100, 8, LangAstroNight,'center');
			    for($i=7;$i<13;$i++)
			    { if(date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"])=="00:00")
			        $this->pdf->addTextWrap((100*$i)-550, $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap((100*$i)-550, $liney  , 50, 8,date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_end"]  +$theTimeDifference1[$i]),"center");
			        $this->pdf->addTextWrap((100*$i)-550, $liney-7, 50, 8,date("H:i", $theNightEphemerides1[$i]["astronomical_twilight_begin"]+$theTimeDifference1[$i]),'center');
			      }
			      if(date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"])=="00:00")
			        $this->pdf->addTextWrap((100*$i)-500, $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap((100*$i)-500, $liney  , 50, 8,date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_end"]  +$theTimeDifference15[$i]),"center");
			        $this->pdf->addTextWrap((100*$i)-500, $liney-7, 50, 8,date("H:i", $theNightEphemerides15[$i]["astronomical_twilight_begin"]+$theTimeDifference15[$i]),'center');
			      }
			    }
			    if(date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"])=="00:00")
			      $this->pdf->addTextWrap((100*13)-550, $liney-7, 50, 8, "-", "center");
			    else
			    { $this->pdf->addTextWrap((100*13)-550, $liney  , 50, 8,date("H:i", $theNightEphemerides1[1]["astronomical_twilight_end"]  +$theTimeDifference1[1]),"center");
			      $this->pdf->addTextWrap((100*13)-550, $liney-7, 50, 8,date("H:i", $theNightEphemerides1[1]["astronomical_twilight_begin"]+$theTimeDifference1[1]),'center');
			    }
			    
			    $liney-=25;
			    $this->pdf->addTextWrap( 50, $liney-4, 100, 8, LangNauticalNight,'center');
			    for($i=7;$i<13;$i++)
			    { if(date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"])=="00:00")
			        $this->pdf->addTextWrap((100*$i)-550, $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap((100*$i)-550, $liney  , 50, 8,date("H:i", $theNightEphemerides1[$i]["nautical_twilight_end"]  +$theTimeDifference1[$i]),"center");
			        $this->pdf->addTextWrap((100*$i)-550, $liney-7, 50, 8,date("H:i", $theNightEphemerides1[$i]["nautical_twilight_begin"]+$theTimeDifference1[$i]),'center');
			      }
			      if(date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"])=="00:00")
			        $this->pdf->addTextWrap((100*$i)-500, $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap((100*$i)-500, $liney  , 50, 8,date("H:i", $theNightEphemerides15[$i]["nautical_twilight_end"]  +$theTimeDifference15[$i]),"center");
			        $this->pdf->addTextWrap((100*$i)-500, $liney-7, 50, 8,date("H:i", $theNightEphemerides15[$i]["nautical_twilight_begin"]+$theTimeDifference15[$i]),'center');
			      }
			    }
			    if(date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"])=="00:00")
			      $this->pdf->addTextWrap((100*13)-550, $liney-7, 50, 8, "-", "center");
			    else
			    { $this->pdf->addTextWrap((100*13)-550, $liney  , 50, 8,date("H:i", $theNightEphemerides1[1]["nautical_twilight_end"]  +$theTimeDifference1[1]),"center");
			      $this->pdf->addTextWrap((100*13)-550, $liney-7, 50, 8,date("H:i", $theNightEphemerides1[1]["nautical_twilight_begin"]+$theTimeDifference1[1]),'center');
			    }
			    
			    $liney-=25;
			    $this->pdf->addTextWrap( 50, $liney-4, 100, 8, LangObjectRiseSet2,'center');
          for($i=7;$i<13;$i++)
			    { if($theEphemerides1[$i]['rise']=='-')
			        $this->pdf->addTextWrap((100*$i)-550, $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap((100*$i)-550, $liney  , 50, 8,$theEphemerides1[$i]['rise'],"center");
			        $this->pdf->addTextWrap((100*$i)-550, $liney-7, 50, 8,$theEphemerides1[$i]['set'],'center');
			      }
	          if($theEphemerides1[$i]['rise']=='-')
			        $this->pdf->addTextWrap((100*$i)-500, $liney-4, 50, 8, "-", "center");
			      else
			      { $this->pdf->addTextWrap((100*$i)-500, $liney  , 50, 8,($theEphemerides15[$i]['rise']),"center");
			        $this->pdf->addTextWrap((100*$i)-500, $liney-7, 50, 8,($theEphemerides15[$i]['set']),'center');
			      }
			    }       
          if($theEphemerides1[1]['rise']=='-')
		        $this->pdf->addTextWrap(50+(100*7), $liney-4, 50, 8, "-", "center");
		      else
		      { $this->pdf->addTextWrap(50+(100*7), $liney  , 50, 8,$theEphemerides1[1]['rise'],"center");
		        $this->pdf->addTextWrap(50+(100*7), $liney-7, 50, 8,$theEphemerides1[1]['set'],'center');
		      }
		      
        }	        
        
      }
      $liney-=35;$textextra='';
      if(($listname=$objUtil->checkSessionKey('listname'))&&($objList->checkObjectInMyActiveList($theobject)))
      { $textextra=$this->pdf->addTextWrap( 50, $liney, 750, 10, LangViewObservationField8,  'left');
        $liney-=5;
      	$this->pdf->line(50,$liney,$this->canvasDimensionXpx-50,$liney);
        $liney-=15;
      	$textextra=$this->pdf->addTextWrap( 50, $liney, 750, 10, $objList->getListObjectDescription($theobject),  'left');
      }
      elseif($theobjectdata['objectdescription'])
      { $textextra=$this->pdf->addTextWrap( 50, $liney, 750, 10, LangViewObservationField8,  'left');
        $liney-=5;
      	$this->pdf->line(50,$liney,$this->canvasDimensionXpx-50,$liney);
      	$liney-=15;
      	$this->pdf->addTextWrap( 50, $liney, 750, 10, $theobjectdata['objectdescription'],  'left');
      }
      $liney-=15;
      while($textextra)
      { $textextra=$this->pdf->addTextWrap( 50, $liney, 750, 10, $textextra,  'left');
        $liney-=15;
        if($liney<30)
        { $this->pdf->newPage();
          $this->pdf->addTextWrap(0,10,$this->pdf->ez['pageWidth']-10,10,$theShowname,'right');
          $liney=$this->canvasDimensionYpx-50;
        }
      }
    }

    if(is_array($thephotos) && array_key_exists(0,$thephotos) && ($thephotos[0]>0))
    { if($firstpage)
      { $firstpage=false;
    	}
    	else
    	{ $this->pdf->newPage();
    	}
    	
    	// show small lookup map
      $theView=120;
      $minDegs=$theView/120;
    	$i=$this->gridMaxDimension;
      while($i && ($this->gridDimensions[$i][0]<$minDegs))
        $i--;
      $this->gridActualDimension=$i;
      $this->atlasmagnitude=10;
      $this->starsmagnitude=11;
      $tempw=200;
      $temph=200;
      $tempx=$this->pdf->ez['pageWidth']-$tempw-30;
      $tempy=$this->pdf->ez['pageHeight']-$temph-15;
      $this->pdf->setLineStyle(0.5);
      $this->gridInitSpecial($tempx,$tempy,$tempw,$temph);
      $this->gridInitScale($this->atlaspagerahr,$this->atlaspagedecldeg,$this->atlaspagezoomdeg);
      $this->pdf->setStrokeColor(0.9,0.9,0.9);
      $this->pdf->setLineStyle(0.5,'','',array(1));
      $this->gridDrawCoordLines();
      $this->pdf->setLineStyle(0.5,'','',array());
      $this->pdf->setStrokeColor(0.7,0.7,0.7);
      $this->pdf->setStrokeColor(0,0,0);
      $this->astroDrawStarsArr();
      $this->astroDrawObjects($theobject);
      $this->pdf->setStrokeColor(0,0,0);
      $this->pdf->setColor(1,1,1);
      $this->pdf->filledRectangle(0,0,$this->pdf->ez['pageWidth'],$tempy);
      $this->pdf->filledRectangle(0,0,$tempx,$this->pdf->ez['pageHeight']);
      $this->pdf->filledRectangle($tempx+$tempw,0,$this->pdf->ez['pageWidth']-($tempx+$tempw),$this->pdf->ez['pageHeight']);
      $this->pdf->filledRectangle(0,$tempy+$temph,$this->pdf->ez['pageWidth'],$this->pdf->ez['pageHeight']-($tempy+$temph));
      $this->pdf->setColor(0,0,0);
      $this->pdf->rectangle($tempx,$tempy,$tempw,$temph);
      $this->pdf->addTextWrap(0,10,$this->pdf->ez['pageWidth']-10,10,$theShowname,'right');	
      
    	$liney=25;
      if($thephotos[0]>75)
       $thephotos[0]=75;
    	$url='http://archive.stsci.edu/cgi-bin/dss_search?v=poss2ukstu_red&r='.$raDSS.'.0&d='.$declDSS.'&e=J2000&h='.$thephotos[0].'.0&w='.$thephotos[0].'&f=gif&c=none&fov=NONE&v3=';
      $this->pdf->addText(50, $liney-15, 10, LangViewDSSImageTitle.$thephotos[0].'x'.$thephotos[0].' '.LangNewObjectSizeUnits1);
    	if($img=@imagecreatefromgif($url))
      { imagefilter($img, IMG_FILTER_NEGATE);
        $this->pdf->addImage($img,50,$liney,350);
        
        if($thephotos[0]==60)
        { $theView=60;
	      	$minDegs=$theView/120;
		    	$i=$this->gridMaxDimension;
		      while($i && ($this->gridDimensions[$i][0]<$minDegs))
		        $i--;
		      $this->gridActualDimension=$i;
		      $this->atlasmagnitude=-10;
		      
		      $tempw=350;
		      $temph=350;
		      $tempx=50;
		      $tempy=$liney;
		      $this->pdf->setLineStyle(0.5);
		      $this->gridInitSpecial($tempx,$tempy,$tempw,$temph);
		      $this->gridInitScale($this->atlaspagerahr,$this->atlaspagedecldeg,$this->atlaspagezoomdeg);
	        $this->astroDrawObject($theobject);	        
	        $this->pdf->setStrokeColor(0,0,0);
        }       
      }
      $this->pdf->rectangle(48,$liney-2,354,354);
      
      if(is_array($thephotos) && array_key_exists(1,$thephotos) && ($thephotos[1]>0))
      { if($thephotos[1]>75)
         $thephotos[1]=75;
        $url='http://archive.stsci.edu/cgi-bin/dss_search?v=poss2ukstu_red&r='.$raDSS.'.0&d='.$declDSS.'&e=J2000&h='.$thephotos[1].'.0&w='.$thephotos[1].'&f=gif&c=none&fov=NONE&v3=';
        $this->pdf->rectangle(448,$liney-2,354,354);
        $this->pdf->addText(450, $liney-15, 10, LangViewDSSImageTitle.$thephotos[1].'x'.$thephotos[1].' '.LangNewObjectSizeUnits1);
        if($img=@imagecreatefromgif($url))
        { imagefilter($img, IMG_FILTER_NEGATE);
          $this->pdf->addImage($img,450,$liney,350);
        }
      }
      $this->pdf->rectangle(448,$liney-2,354,354);
      
		  $this->pdf->setColor(0,0,0);		            
     	$liney=$this->canvasDimensionYpx-50;
      $this->pdf->addTextWrap( 50, $liney, $this->canvasDimensionXpx-100, 15, ReportImagesFor.$theShowname,  'center');
      $liney-=5;
      $this->pdf->line(50,$liney,$this->canvasDimensionXpx-250,$liney);
      $liney-=15;
      $this->pdf->addTextWrap(50, $liney, 450, 10, '(c) STScI Digitized Sky Survey',  'left');
      $liney-=25;
      if($datapage!='true')
      { $theobjectdata=$objObject->getSeenObjectDetails(array($theobject => array(0,$theobject)));
        $theobjectdata=$theobjectdata[0];
      	$this->pdf->addTextWrap(50, $liney, 450, 10, Reportaltname.": ".($theobjectdata['altname']?$theobjectdata['altname']:'-'),  'left');
      	$liney-=25;
	      $this->pdf->addTextWrap( 50, $liney, 200, 10, Reportobjecttypefull.': '.$theobjectdata['objecttypefull'],  'left');
	      $this->pdf->addTextWrap(300, $liney, 200, 10, Reportobjectmagnitude.': '.($theobjectdata['objectmagnitude']?$theobjectdata['objectmagnitude']:'-'),  'left');
	      $liney-=15;
	      $this->pdf->addTextWrap( 50, $liney, 200, 10, Reportobjectconstellationfull.': '.$theobjectdata['objectconstellationfull'],  'left');
	      $this->pdf->addTextWrap(300, $liney, 200, 10, Reportobjectsurfacebrightness.': '.($theobjectdata['objectsurfacebrightness']?$theobjectdata['objectsurfacebrightness']:'-'),  'left');
	      $liney-=15;
	      $this->pdf->addTextWrap( 50, $liney, 200, 10, Reportobjectra.': '.$theobjectdata['objectrahms'],  'left');
	      $this->pdf->addTextWrap(300, $liney, 200, 10, LangViewObjectField9.': '.($theobjectdata['objectsize']?$theobjectdata['objectsize']:'-'),  'left');
	      $liney-=15;
	      $this->pdf->addTextWrap( 50, $liney, 200, 10, Reportobjectdecl.': '.$theobjectdata['objectdecldms'],  'left');
	      $this->pdf->addTextWrap(300, $liney, 200, 10, LangViewObjectField12.': '.(($pa=$theobjectdata['objectpa'])==999?'-':$pa),  'left');
      }      
    $this->labelsArr=array();
    }
    
    for($k=0;$k<count($theSet);$k++)
    { if(is_numeric($theSet[$k]) && is_numeric($thedsos[$k]) && is_numeric($thestars[$k]))
      { if($firstpage)
        { $firstpage=false;
    	  }
    	  else
    	  { $this->pdf->newPage();
    	  }
      	$minDegs=$theSet[$k]/120;
	    	$i=$this->gridMaxDimension;
	      while($i && ($this->gridDimensions[$i][0]<$minDegs))
	        $i--;
	      $this->gridActualDimension=$i;
	      $this->atlasmagnitude=max(min((int)(($thedsos[$k])),99),8);
	      $this->starsmagnitude=max(min((int)(($thestars[$k])),16),8);
	      
	      $this->pdf->setLineStyle(0.5);
	      $this->gridInit();
	      $this->gridInitScale($this->atlaspagerahr,$this->atlaspagedecldeg,$this->atlaspagezoomdeg);
	      $this->pdf->setStrokeColor(0.9,0.9,0.9);
	      $this->pdf->setLineStyle(0.5,'','',array(1));
	      $this->gridDrawCoordLines();
	      $this->pdf->setLineStyle(0.5,'','',array());
	      $this->pdf->setStrokeColor(0.7,0.7,0.7);
	      $this->astroDrawConstellations();
	      $this->pdf->setStrokeColor(0,0,0);
	      $this->astroDrawStarsArr();
	      $this->astroDrawObjects($theobject);
	      
	      $this->pdf->setColor(1,1,1);
	      $this->pdf->filledRectangle(0,0,$this->gridOffsetXpx,$this->canvasDimensionYpx);
	      $this->pdf->filledRectangle(0,0,$this->canvasDimensionXpx,$this->gridOffsetYpx);
	      $this->pdf->filledRectangle($this->canvasDimensionXpx-$this->gridOffsetXpx,0,$this->gridOffsetXpx,$this->canvasDimensionYpx);
	      $this->pdf->filledRectangle(0,$this->canvasDimensionYpx-$this->gridOffsetYpx,$this->canvasDimensionXpx,$this->gridOffsetYpx);
	      $this->pdf->setColor(0,0,0);
	      $this->gridShowInfo();
	      $this->atlasDrawLegend();
	      $temp=$objObserver->getObserverProperty($loggedUser,'firstname')." ".$objObserver->getObserverProperty($loggedUser,'name')." - ".date('d M Y');
	      $this->pdf->addText($this->canvasDimensionXpx-$this->gridOffsetXpx-(strlen($temp)*5),$this->canvasDimensionYpx-$this->Legend1y-10,$this->fontSize1b,$temp);
	      $this->pdf->setLineStyle(2,'round');
	      $this->pdf->rectangle($this->gridOffsetXpx-1,$this->gridOffsetYpx-1,
	                           ($this->canvasDimensionXpx-($this->gridOffsetXpx<<1))+2,($this->canvasDimensionYpx-($this->gridOffsetYpx<<1))+2);
	         
	      for($i=0,$z=count($this->labelsArr);$i<$z;$i++)   
	        $this->pdf->addTextWrap($this->labelsArr[$i][0],$this->labelsArr[$i][1],$this->labelsArr[$i][2],$this->labelsArr[$i][3],$this->labelsArr[$i][4],$this->labelsArr[$i][5]);                  
	      $this->labelsArr=array();
	      $temp='(c) www.deepskylog.org - No publishing without written autorisation - Object Database originally based on Eye&Telescope - Star Database by Tycho 2+ and USNO UCAC3 (Zacharia).';
	        $this->pdf->addTextWrap(0,10,$this->pdf->ez['pageWidth']-10,10,$theShowname,'right');
	      $this->pdf->addText($this->gridOffsetXpx,13,$this->fontSize1b,$temp);
	      $astroObjects[$k]=$objObject->getSeenObjectDetails($this->astroObjectsArr);
      }
    }
    if($reportlayoutselect)
    { if($firstpage)
      { $firstpage=false;
    	}
    	else
    	{ $this->pdf->newPage();
	      $this->pdf->addTextWrap(0,10,$this->pdf->ez['pageWidth']-10,10,$theShowname,'right');
    	}
      $this->pdf->setLineStyle(1);
      $reportuser=substr($reportlayoutselect,0,strpos($reportlayoutselect,": "));
      $reportname='ReportQueryOfObjects';
      $reportlayout=substr($reportlayoutselect,strpos($reportlayoutselect,": ")+2);
      $reportdata=$objReportLayout->getReportData($reportuser,$reportname,$reportlayout);
	    $pagesize         = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'pagesize');
	    $pageorientation  = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'pageorientation');
	    $bottom           = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'bottom');
	    $top              = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'top');
	    $header           = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'header');
	    $footer           = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'footer');
	    $xleft            = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'xleft');
	    $xmid             = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'xmid');
	    $fontSizeSection  = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'fontSizeSection');
	    $fontSizeText     = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'fontSizeText');
	    $sectionBarSpace  = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'sectionbarspace');
	    $deltalineSection = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'deltalineSection');    
	    $deltaline        = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'deltalineExtra')+$fontSizeText;
	    $deltaobjectline  = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'deltaobjectline');
	    $pagenr           = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'startpagenumber');
			$sectionBarHeight = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'sectionBarHeightextra')+$fontSizeSection;
			$SectionBarWidth  = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'SectionBarWidthbase')+$sectionBarSpace;
	    $showelements     = $objReportLayout->getLayoutFieldPosition($reportuser,$reportname,$reportlayout,'showelements');
			
	    $this->pdf->selectFont($instDir.'lib/fonts/Helvetica.afm');
			$sort='';    
	    $actualsort='';
			$theDate=date('d/m/Y');
	    $objUtil->firstpage($y,$bottom,$top,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,'',$showelements,$reportdata);		
	    for($j=0;$j<$k;$j++)
	    { $y-=$deltalineSection;
		    $this->pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
		    $this->pdf->addText($xbase, $y, $fontSizeSection, 'Chart '.($j+1));
		    $y-=$deltaline+$deltalineSection; 
		    $result=$astroObjects[$j];
		    while(list($key, $valueA) = each($result))
		    { $con = $valueA['objectconstellation'];
		      $deltaymax=0;
		      reset($reportdata);
				  while(list($key,$dataelement)=each($reportdata))
				  { if($dataelement['fieldwidth'])
				    { if(($dataelement['fieldname']=="objectlistdescription"))
			        { if(array_key_exists('objectlistdescription',$valueA) && $valueA['objectlistdescription'])
			            $deltaymax=max($deltaymax,$dataelement['fieldline']); 
				 		  }
				      elseif($dataelement['fieldname']=="objectdescription")
		  	      { if(array_key_exists('objectdescription',$valueA) && ($valueA['objectdescription']!=''))
		  	          $deltaymax=max($deltaymax,$dataelement['fieldline']);
				      }
				      else
				        $deltaymax=max($deltaymax,$dataelement['fieldline']);
				    }
				  }
				  $deltaymax++;
		      if(($y-($deltaline*$deltaymax)<$bottom) && $sort)
		      { $this->$objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,$sort,$con,$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,"","",$showelements,$reportdata);      
		      }
		      elseif(($y-($deltaline*$deltaymax)<$bottom) && (!($sort)))
		      { $objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,$sort,$con,$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,"","",$showelements,$reportdata);      
		        if(strpos($showelements,'s')!==FALSE)
		        { $this->pdf->setLineStyle(0.5);
		          $this->pdf->line($xbase-$sectionBarSpace, $y+(($deltaline+$deltaobjectline)*.75), $xbase+$SectionBarWidth, $y+(($deltaline+$deltaobjectline)*.75));
		          $this->pdf->setLineStyle(1);
		        }
		      }
		      elseif($sort && ($$sort!=$actualsort))
					{ if(($y-($deltaline*$deltaymax)-$sectionBarSpace-$deltalineSection)<$bottom) 
					  { $objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,$sort,$con,$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,"","",$showelements,$reportdata);      
					  }
					  else
		        { $y-=$deltalineSection;
		          $this->pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
		          $this->pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
		          $y-=$deltaline+$deltalineSection;
		        }
		        $indexlist[$$sort]=$pagenr;
					}
					else if(strpos($showelements,'s')!==FALSE)
		      { $this->pdf->setLineStyle(0.5);
		        $this->pdf->line($xbase-$sectionBarSpace, $y+(($deltaline+$deltaobjectline)*.75), $xbase+$SectionBarWidth, $y+(($deltaline+$deltaobjectline)*.75));
		        $this->pdf->setLineStyle(1);
		      }
					reset($reportdata);
					$deltaymax=0;
					while(list($key,$dataelement)=each($reportdata))
					{ if($dataelement['fieldwidth'])
					  { if($y-($deltaline*$dataelement['fieldline'])<$bottom) 
		          { $objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,$sort,$con,$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,"","",$showelements,$reportdata);
		          }
		          $justification='left';
					  	$i='';$b='';
					  	if(strpos($dataelement['fieldstyle'],'r')!==FALSE)
					      $justification='right';
					  	if(strpos($dataelement['fieldstyle'],'c')!==FALSE)
					      $justification='center';
					  	if(strpos($dataelement['fieldstyle'],'b')!==FALSE)
					  	{ $b="<c:uline><b>";
					  	  $this->pdf->addText(0,0,$fontSizeText,'<b>');
					  	}
					  	if(strpos($dataelement['fieldstyle'],'i')!==FALSE)
					    { $i='<i>';
					      $this->pdf->addText(0,0,$fontSizeText,'<i>');
					    }
					    if($dataelement['fieldname']=="showname")
					    { if($valueA[$dataelement['fieldname']])
					      { $this->pdf->addText(0,0,$fontSizeText,'<c:alink:'.$baseURL.'index.php?indexAction=detail_object&amp;object='.urlencode($valueA['objectname']).'>');
					        $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'] , $y-($deltaline*$dataelement['fieldline']),  $dataelement['fieldwidth'], $fontSizeText, $dataelement['fieldafter'].html_entity_decode($valueA[$dataelement['fieldname']]).$dataelement['fieldafter'],$justification);
				  		    $this->pdf->addText(0,0,$fontSizeText,'</c:alink>');
					        $deltaymax=max($deltaymax,$dataelement['fieldline']);
					      }
					    }			                 
					    else if($dataelement['fieldname']=="objectuseratlaspage")
					    { $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'] , $y-($deltaline*$dataelement['fieldline']),  $dataelement['fieldwidth'], $fontSizeText,$dataelement['fieldbefore'].html_entity_decode($valueA[($loggedUser?$objObserver->getObserverProperty($loggedUser,'standardAtlasCode','urano'):'urano')]).$dataelement['fieldafter'],$justification);			                  				  
		  			    $deltaymax=max($deltaymax,$dataelement['fieldline']);
					    }
					    else if(($dataelement['fieldname']=="objectlistdescription"))
			        { if(array_key_exists('objectlistdescription',$valueA) && ($valueA['objectlistdescription']!=''))
			          { $theText= $dataelement['fieldbefore'].html_entity_decode($objPresentations->br2nl($valueA['objectlistdescription'])).$dataelement['fieldafter'];
				  		    $theText= $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'], $y-($deltaline*$dataelement['fieldline']), $dataelement['fieldwidth'] ,$fontSizeText, $theText,$justification);
			  	  		  while($theText)
						  	  { $y-=$deltaline;	
			              if($y-($deltaline*$dataelement['fieldline'])<$bottom) 
			              { $objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,$sort,$con,$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,$i,$b,$showelements,$reportdata);
			                $y+=($deltaline*$dataelement['fieldline']);
			              }
							     $theText= $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'], $y-($deltaline*$dataelement['fieldline']), $dataelement['fieldwidth'] ,$fontSizeText, $theText,$justification);
			  		  	  }
					        $deltaymax=max($deltaymax,$dataelement['fieldline']);
			          } 
				   		}
					    elseif($dataelement['fieldname']=="objectdescription")
		  	      { if(array_key_exists('objectlistdescription',$valueA) && ($valueA['objectlistdescription']!=''))
			          { $theText= $dataelement['fieldbefore'].html_entity_decode($objPresentations->br2nl($valueA['objectlistdescription'])).$dataelement['fieldafter'];
				  		    $theText= $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'], $y-($deltaline*$dataelement['fieldline']), $dataelement['fieldwidth'] ,$fontSizeText, $theText,$justification);
			  	  		  while($theText)
						  	  { $y-=$deltaline;	
			              if($y-($deltaline*$dataelement['fieldline'])<$bottom) 
			              { $objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,$sort,$con,$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,$i,$b,$showelements,$reportdata);
			                $y+=($deltaline*$dataelement['fieldline']);
			              }
							     $theText= $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'], $y-($deltaline*$dataelement['fieldline']), $dataelement['fieldwidth'] ,$fontSizeText, $theText,$justification);
			  		  	  }
					        $deltaymax=max($deltaymax,$dataelement['fieldline']);
			          }
			          else if(array_key_exists('objectdescription',$valueA) && ($valueA['objectdescription']!=''))
		  	        { $theText= $dataelement['fieldbefore'].html_entity_decode($objPresentations->br2nl($valueA['objectdescription'])).$dataelement['fieldafter'];
			   			    $theText= $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'], $y-($deltaline*$dataelement['fieldline']), $dataelement['fieldwidth'] ,$fontSizeText, $theText,$justification);
			  	  		  while($theText)
		              { $y-=$deltaline;	
			              if($y-($deltaline*$dataelement['fieldline'])<$bottom) 
			              { $objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,$sort,$con,$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,$i,$b,$showelements,$reportdata);
			                $y+=($deltaline*$dataelement['fieldline']);
			              }
			              $theText= $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'], $y-($deltaline*$dataelement['fieldline']), $dataelement['fieldwidth'] ,$fontSizeText, $theText,$justification);
			  			    }
					        $deltaymax=max($deltaymax,$dataelement['fieldline']);
		  	        }
						  }
					    else
					    { if($valueA[$dataelement['fieldname']]!='')
					      { $this->pdf->addTextWrap($xbase+$dataelement['fieldposition'] , $y-($deltaline*$dataelement['fieldline']),  $dataelement['fieldwidth'], $fontSizeText, $dataelement['fieldbefore'].html_entity_decode($valueA[$dataelement['fieldname']]).$dataelement['fieldafter'],$justification);
					        $deltaymax=max($deltaymax,$dataelement['fieldline']);
					      }
					    }
					    if(strpos($dataelement['fieldstyle'],'b')!==FALSE)
					      $this->pdf->addText(0,0,$fontSizeText,'</b>');
					    if(strpos($dataelement['fieldstyle'],'i')!==FALSE)
					      $this->pdf->addText(0,0,$fontSizeText,'</i>');
					  }
					}			
					$y-=$deltaline*($deltaymax);
		      $y-=($deltaline+$deltaobjectline);
					if($sort)
					  $actualsort = $$sort;
				}
				if((strpos($showelements,'i')!==FALSE)&&(count($indexlist)>0)&&($sort))
				{ $base=$xmid;
				  $objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,'','',$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,"","",$showelements,$reportdata);      
				  $this->pdf->setLineStyle(0.5);
		      $y=$top;
					while(list($key,$value)=each($indexlist))	  
					{ $this->pdf->line($xbase-$sectionBarSpace, $y+(($deltaline+$deltaobjectline)*.75), $xbase+$SectionBarWidth, $y+(($deltaline+$deltaobjectline)*.75));
		        $this->pdf->addTextWrap($xbase,$y,50,$fontSizeText,$key,'left');
		        $this->pdf->addTextWrap($xbase+$SectionBarWidth-$sectionBarSpace-50,$y,50,$fontSizeText,trim($value),'right');
		        
					  $y-=($deltaline+$deltaobjectline);
					  if(($y-($deltaline+$deltaobjectline))<$bottom)
					  { $objUtil->newpage($y,$bottom,$top,$bottom,$xbase,$xmid,$pagenr,$this->pdf,$xleft,$header,$fontSizeText,$theDate,$footer,$SectionBarWidth,$sectionBarSpace,'','',$deltalineSection,$sectionBarHeight,$fontSizeSection,$deltaline,$deltalineSection,"","",$showelements,$reportdata);      
					    $this->pdf->setLineStyle(0.5);
					  }
		      }
				}    
	    }
    }
    if(!$nostream)
      $this->pdf->Stream(); 
  }
  
	function roundPrecision($theValue,$thePrecision)
	{ return(round($theValue/$thePrecision)*$thePrecision);
	}
}
?>
