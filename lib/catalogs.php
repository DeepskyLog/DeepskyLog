<?php
class catalogs
{ private function formatIndex($theformat,$theindex)
  { if($theformat=="2MASX")
    { $returnindex='J';
	    if(strtoupper(substr($theindex,0,1))=="J")
	      $theindex=substr($theindex,1);

	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<8;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);

	    $temp=strlen($theindex);
	    for($i=$temp;$i<7;$i++)
	      $returnindex.="0";

	    return $returnindex.$theindex;  
    }
    if($theformat=="MCG")
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
	  if($theformat=="APMUKS(BJ)")
	  { $returnindex='B';
	    if(strtoupper(substr($theindex,0,1))=="B")
	      $theindex=substr($theindex,1);

	    $temp=strpos($theindex,'.');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<6;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);
	    
	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<2;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);
	    
	    $temp=strpos($theindex,'.');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<6;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);
	    
	    $temp=strlen($theindex);
	    for($i=$temp;$i<1;$i++)
	      $returnindex.="0";
	    return $returnindex.$theindex;  
	  }
	  if($theformat=="BD")
	  { $temp=substr($theindex,0,1);
	    if(($temp!="-")&&($temp!="+"))
	      return $theindex;
	    $returnindex=$temp;
	    $theindex=substr($theindex,1);
	      
	  	$temp=strpos($theindex,' ');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<2;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);
	    
	    $temp=strlen($theindex);
	    for($i=$temp;$i<5;$i++)
	      $returnindex.="0";
	    return $returnindex.$theindex; 
	  	
	  }
	  if($theformat=="CGCG")
	  { $returnindex='';
	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<3;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);

	    $temp=strlen($theindex);
	    for($i=$temp;$i<3;$i++)
	      $returnindex.="0";

	    return $returnindex.$theindex;  
	  }
  }
  public function checkObject($theobject)
  { $firstspace=strpos($theobject,' ',0);
    if($firstspace!==FALSE)
    { $thenewcatalog=trim(strtoupper(substr($theobject,0,$firstspace)));
      $theindex=trim(substr($theobject,$firstspace+1));
    }
    else
    { $thenewcatalog=$theobject;
      $theindex='';
    }
    if($thenewcatalog=='MELOTTE')
      $thenewcatalog='MEL';
    if($thenewcatalog=='ACG')
      $thenewcatalog='AGC';
    if($thenewcatalog=='BARNARD')
      $thenewcatalog='B';
    if($thenewcatalog=='BERNARD')
      $thenewcatalog='B';
    if($thenewcatalog=='BERKELEY')
      $thenewcatalog='Berk';
    if($thenewcatalog=='CEDERBALD')
      $thenewcatalog='Ced';
    if($thenewcatalog=='CZ')
      $thenewcatalog='Czernik';
    if($thenewcatalog=='DOLIDZE')
      $thenewcatalog='Do';
    if($thenewcatalog=='DOLIDZE-D...')
      $thenewcatalog='DoDz';
    if($thenewcatalog=='FEINSTEIN')
      $thenewcatalog='Fein';
    if($thenewcatalog=='HGC')
      $thenewcatalog='Hickson';
    if($thenewcatalog=='MARKARIAN')
      $thenewcatalog='Mrk';
    if($thenewcatalog=='MARK')
      $thenewcatalog='Mrk';
    if($thenewcatalog=='PALOMAR')
      $thenewcatalog='Pal';
    if($thenewcatalog=='PEREZ-P...')
      $thenewcatalog='PK';
    if($thenewcatalog=='RRUBRECHT') // ? Ruprecht ?
      $thenewcatalog='Ru';
    if($thenewcatalog=='ST')
      $thenewcatalog='Stock';
    if($thenewcatalog=='TRUMPLER')
      $thenewcatalog='Tr';
      
      
    if((strpos($theobject,"%")!==FALSE)||(strpos($theobject,"?")!==FALSE))
      return trim($thenewcatalog.' '.$theindex);
  	else
  	{ $tocheck=array("2MASX","APMUKS(BJ)","BD",'CGCG');
      while(list($key,$value)=each($tocheck))
        if($thenewcatalog==$value)
          return $value.' '.$this->formatIndex($value,trim($theindex));
	    return trim($thenewcatalog.' '.$theindex);
  	}
  }
}
$objCatalog=new catalogs;