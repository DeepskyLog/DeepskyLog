<?php
interface iConstellation
{ public function getAllBoundries();
  public function getConstellationFromCoordinates($thera,$thedecl);
}
class Constellation implements iConstellation
{ public $conBoundries;
  public function __construct()
  { $this->conBoundries=$this->getAllBoundries();
  }
  public function getAllBoundries()
  { global $objDatabase;
    return $objDatabase->selectRecordsetArray("SELECT * FROM conlines;");
  }
  public function getConstellationFromCoordinates($thera,$thedecl)
  { $tempdecl=-90;
    $tempcon="OCT";
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
      if((($thera0<=$thera)&&($thera1>=$thera)||($thera1<=$thera)&&($thera0>=$thera))&&
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
}
$objConstellation=new Constellation;
?>