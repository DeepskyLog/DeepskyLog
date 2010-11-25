<?php
// catalogs.php
// code for uniform index generation and ofr translating catalog names (eg Messier -> M)

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class catalogs
{ public function getCatalogData($thecatalog)
  { global $objDatabase;
    $sql="SELECT DISTINCT objects.*, objectnames.objectname, objectnames.altname FROM objects JOIN objectnames ON objects.name = objectnames.objectname WHERE catalog='".$thecatalog."' ORDER BY objectname;";
    $result=$objDatabase->selectRecordsetArray($sql);
    $t=count($result);
    $n="";
    for($i=0,$k='';$i<$t;$i++)
    { if($result[$i]['objectname']!=$n)
      { $result2[$k=$result[$i]['altname']]=$result[$i];
        $n=$result[$i]['objectname'];
      }
      else
      { $result2[$k]['objectname']=$result2[$k]['objectname'].'/'.$result[$i]['altname'];
      }
    }
    uksort($result2,"strnatcasecmp");
    $t=count($result2);
    while(list($key,$value)=each($result2))
      $result3[]=$value;
    return $result3;  
  }
  private function formatIndex($theformat,$theindex)
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
	  if($theformat=="IRAS")
	  { $returnindex='';
	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<5;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);

	    $temp=strlen($theindex);
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";

	    return $returnindex.$theindex;  
	  }
	  if($theformat=="KUG")
	  { $returnindex='';
	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);

	    $temp=strlen($theindex);
	    for($i=$temp;$i<3;$i++)
	      $returnindex.="0";
      if(($temp<4)&&substr($theindex,-1)>"9")
	      $returnindex.="0";
      return $returnindex.$theindex;  
	  }
	  if($theformat=="MAC")
	  { $returnindex='';
	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);

	    $temp=strlen($theindex);
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";
      if(($temp<5)&&substr($theindex,-1)>"9")
	      $returnindex.="0";
      return $returnindex.$theindex;  
	  }
	  if($theformat=="NPM1G")
	  { $temp=substr($theindex,0,1);
	    if(($temp!="-")&&($temp!="+"))
	      return $theindex;
	    $returnindex=$temp;
	    $theindex=substr($theindex,1);
	      
	  	$temp=strpos($theindex,'.');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<2;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);
	    
	    $temp=strlen($theindex);
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";
	    return $returnindex.$theindex; 
	  	
	  }
	  if($theformat=="PK")
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
	  	$temp=strpos($theindex,'.');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<2;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);
	    
	    $temp=strlen($theindex);
	    for($i=$temp;$i<2;$i++)
	      $returnindex.="0";
	    return $returnindex.$theindex; 
 	  }
	  if($theformat=="PKS")
	  { $returnindex='';
	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);

	    $temp=strlen($theindex);
	    for($i=$temp;$i<2;$i++)
	      $returnindex.="0";

	    return $returnindex.$theindex;  
	  }
    if($theformat=="QSO")
    { $returnindex='';
	    if(strtoupper(substr($theindex,0,1))=="J")
	    { $returnindex='J';
	      $theindex=substr($theindex,1);
	    }
	    if(strtoupper(substr($theindex,0,1))=="B")
	    { $returnindex='B';
	      $theindex=substr($theindex,1);
	    }
	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);

	    $temp=strlen($theindex);
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";

	    return $returnindex.$theindex;  
    }
    if($theformat=="SAO")
    { $returnindex='';
	    $temp=strlen($theindex);
	    for($i=$temp;$i<6;$i++)
	      $returnindex.="0";

	    return $returnindex.$theindex;  
    }
	  if($theformat=="SBS")
	  { $returnindex='';
	    $temp=strpos($theindex,'+');
	    if($temp===FALSE)
	      $temp=strpos($theindex,'-');
	    if($temp===FALSE)
	      return $returnindex.$theindex;
	    for($i=$temp;$i<4;$i++)
	      $returnindex.="0";
	    $returnindex.=substr($theindex,0,$temp+1);
	    $theindex=substr($theindex,$temp+1);

	    $temp=strlen($theindex);
	    for($i=$temp;$i<3;$i++)
	      $returnindex.="0";

	    return $returnindex.$theindex;  
	  }
	  if($theformat=="SDSS")
	  { $returnindex='J';
	    if(strtoupper(substr($theindex,0,1))=="J")
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
	  if($theformat=="WRAY")
	  { $returnindex='';
	    if(strtoupper(substr($theindex,0,3))=="15-")
	    { $theindex=substr($theindex,3);
	      $temp=strlen($theindex);
	      for($i=$temp;$i<4;$i++)
	        $returnindex.="0";
	      return '15-'.$returnindex.$theindex;  
	    }
      if(strtoupper(substr($theindex,0,3))=="16-")
	    { $theindex=substr($theindex,3);
	      $temp=strlen($theindex);
	      for($i=$temp;$i<3;$i++)
	        $returnindex.="0";
	      return '16-'.$returnindex.$theindex;  
	    }
	    if(strtoupper(substr($theindex,0,3))=="17-")
	    { $theindex=substr($theindex,3);
	      $temp=strlen($theindex);
	      for($i=$temp;$i<3;$i++)
	        $returnindex.="0";
	      return '17-'.$returnindex.$theindex;  
	    }
	    if(strtoupper(substr($theindex,0,3))=="18-")
	    { $theindex=substr($theindex,3);
	      $temp=strlen($theindex);
	      for($i=$temp;$i<3;$i++)
	        $returnindex.="0";
	      return '18-'.$returnindex.$theindex;  
	    }
	    if(strtoupper(substr($theindex,0,3))=="19-")
	    { $theindex=substr($theindex,3);
	      $temp=strlen($theindex);
	      for($i=$temp;$i<2;$i++)
	        $returnindex.="0";
	      return '19-'.$returnindex.$theindex;  
	    }
	    return $theindex;
	  }
  }
  public function checkObject($theobject)
  { $firstspace=strpos($theobject,' ',0);
    if($firstspace!==FALSE)
    { $thenewcatalog0=trim(substr($theobject,0,$firstspace));
      $theindex=trim(substr($theobject,$firstspace+1));
    }
    else
    { if(strtoupper(substr($theobject,0,3))=='PKS')
      { $thenewcatalog0='PKS';
        $theindex=substr($theobject,3);
      }
    	elseif(strtoupper(substr($theobject,0,2))=='PK')
      { $thenewcatalog0='PK';
        $theindex=substr($theobject,2);
      }
    	else
    	{ $thenewcatalog0=$theobject;
        $theindex='';
    	}
    }
    $thenewcatalog=strtoupper($thenewcatalog0);
    if($thenewcatalog=='ACG')
      $thenewcatalog0='AGC';
    if($thenewcatalog=='BARNARD')
      $thenewcatalog0='B';
    if($thenewcatalog=='BERNARD')
      $thenewcatalog0='B';
    if($thenewcatalog=='BERKELEY')
      $thenewcatalog0='Berk';
    if($thenewcatalog=='CEDERBALD')
      $thenewcatalog0='Ced';
    if($thenewcatalog=='CZ')
      $thenewcatalog0='Czernik';
    if($thenewcatalog=='DOLIDZE')
      $thenewcatalog0='Do';
    if($thenewcatalog=='DOLIDZE-D...')
      $thenewcatalog0='DoDz';
    if($thenewcatalog=='FEINSTEIN')
      $thenewcatalog0='Fein';
    if($thenewcatalog=='HGC')
      $thenewcatalog0='Hickson';
    if($thenewcatalog=='HCG')
      $thenewcatalog0='Hickson';
    if($thenewcatalog=='MESSIER')
      $thenewcatalog0='M';
    if($thenewcatalog=='MARKARIAN')
      $thenewcatalog0='Mrk';
      if($thenewcatalog=='MARK')
      $thenewcatalog0='Mrk';
    if($thenewcatalog=='MELOTTE')
      $thenewcatalog0='MEL';
    if($thenewcatalog=='PALOMAR')
      $thenewcatalog0='Pal';
    if($thenewcatalog=='PEREZ-P...')
      $thenewcatalog0='PK';
    if($thenewcatalog=='RUBRECHT') // ? Ruprecht ?
      $thenewcatalog0='Ru';
    if($thenewcatalog=='ST')
      $thenewcatalog0='Stock';
    if($thenewcatalog=='SANDULEAK')
      $thenewcatalog0='Sa';
    if($thenewcatalog=='TRUMPLER')
      $thenewcatalog0='Tr';
    if($thenewcatalog=='WRAY')
      $thenewcatalog0='Wray';  
      
      
    if((strpos($theobject,"%")!==FALSE)||(strpos($theobject,"?")!==FALSE))
      return trim($thenewcatalog0.' '.$theindex);
  	else
  	{ $tocheck=array("2MASX","MCG","APMUKS(BJ)","BD",'CGCG','IRAS','KUG','MAC','NPM1G','PK','PKS','QSO','SAO','SBS','SDSS','WRAY');
      while(list($key,$value)=each($tocheck))
        if(strtoupper($thenewcatalog0)==$value)
          return $value.' '.$this->formatIndex($value,trim($theindex));
	    return trim($thenewcatalog0.' '.$theindex);
  	}
  }
  public function getCatalogs()
  { global $objDatabase;
    $sql="SELECT DISTINCT catalog FROM objectnames;";
    return $objDatabase->selectSingleArray($sql,'catalog');
  }
}