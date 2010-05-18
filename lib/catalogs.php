<?php
class catalogs
{ private function formatMCGIndex($theindex)
  { $nextindex=0;
    if((($temp=substr($theindex,$nextindex,1))=="+")||($temp=="-"))
    { $returnindex=$temp;
      $nextindex++;
    }
    else
      $returnindex='+';
    if((($temp=substr($theindex,$nextindex+1,1))=='+')||($temp=='-'))
    { $returnindex.='0'.substr($theindex,$nextindex,2);
      $nextindex+=2;
    }
    else
    { $returnindex.=substr($theindex,$nextindex,3);
    	$nextindex+=3;
    }
    if((($temp=substr($theindex,$nextindex+1,1))=='+')||($temp=='-'))
    { $returnindex.='0'.substr($theindex,$nextindex,2);
      $nextindex+=2;
    }
    else
    { $returnindex.=substr($theindex,$nextindex,3);
    	$nextindex+=3;
    }
    if(strlen(substr($theindex,$nextindex))==1)
      $returnindex.='00'.substr($theindex,$nextindex);
    elseif(strlen(substr($theindex,$nextindex))==2)
      $returnindex.='0'.substr($theindex,$nextindex);
    else
      $returnindex.=substr($theindex,$nextindex);
    return $returnindex;
  }
  public function checkObject($theobject)
  { if((strpos($theobject,"%")!==FALSE)||(strpos($theobject,"?")!==FALSE))
      return $theobject;
  	else
  	{ $thenewobject=$theobject;
      $firstspace=strpos($theobject,' ',0);
      $thecatalog=strtoupper(substr($theobject,0,$firstspace));
      $theindex=substr($theobject,$firstspace+1);
      if($thecatalog=='MCG')
	      return 'MCG '.$this->formatMCGIndex($theindex);
	    else
	      return $theobject;
  	}
  }
  public function checkCatalogIndex($thecatalog,$theindex)
  { if((strpos($thecatalog,"%")!==FALSE)||(strpos($thecatalog,"?")!==FALSE)||(strpos($theindex,"%")!==FALSE)||(strpos($theindex,"?")!==FALSE))
      return array($thecatalog,$theindex);
  	else
  	{ $thenewcatalog=strtoupper($thecatalog);
      if($thenewcatalog=='MCG')
	      return array('MCG',$this->formatMCGIndex($theindex));
	    else
	      return array($thecatalog,$theindex);
  	}
  }
}
$objCatalog=new catalogs;