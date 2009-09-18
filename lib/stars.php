<?php
interface iStars
{ public function getStars($lLhr,$rLhr,$dDdeg,$uDdeg,$mag);      // returns an array containing all star data between the specified coordinates
	
}
class Stars implements iStars
{
 public function getStars6($lLhr,$rLhr,$dDdeg,$uDdeg,$mag)
 { global$objDatabase;
   $stars=array();
   $dDmas=round($dDdeg*3600000);
   $uDmas=round($uDdeg*3600000);
   $lLmas=round($lLhr*54000000);
   $rLmas=round($rLhr*54000000);
   if($lLhr<$rLhr)
   { $sql="SELECT * FROM stars WHERE (RA2000mas<".$lLmas.") AND (DE2000mas>".$dDmas.") AND (DE2000mas<".$uDmas.") AND (vMag<=600) ORDER BY vMag;";
     $stars=$objDatabase->selectRecordsetArray($sql);  
     $sql="SELECT * FROM stars WHERE (RA2000mas>".$rLmas.") AND (DE2000mas>".$dDmas.") AND (DE2000mas<".$uDmas.") AND (vMag<=600) ORDER BY vMag;";
     $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));  
   }
   else
   { $sql="SELECT * FROM stars WHERE (RA2000mas<".$lLmas.") AND (RA2000mas>".$rLmas.") AND (DE2000mas>".$dDmas.") AND (DE2000mas<".$uDmas.") AND (vMag<=600) ORDER BY vMag;";
     $stars=$objDatabase->selectRecordsetArray($sql);
   }
   return $stars;
 }
   public function getStars($lLhr,$rLhr,$dDdeg,$uDdeg,$mag)
 { global$objDatabase;
   $stars=array();
   $dDmas=round($dDdeg*3600000);
   $uDmas=round($uDdeg*3600000);
   $lLmas=round($lLhr*54000000);
   $rLmas=round($rLhr*54000000);
   if($lLhr<$rLhr)
   { $sql="SELECT * FROM stars WHERE (RA2000mas<".$lLmas.") AND (DE2000mas>".$dDmas.") AND (DE2000mas<".$uDmas.") AND (vMag<=".($mag*100).") ORDER BY vMag;";
     $stars=$objDatabase->selectRecordsetArray($sql);  
     $sql="SELECT * FROM stars WHERE (RA2000mas>".$rLmas.") AND (DE2000mas>".$dDmas.") AND (DE2000mas<".$uDmas.") AND (vMag<=".($mag*100).") ORDER BY vMag;";
     $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));  
   }
   else
   { $sql="SELECT * FROM stars WHERE (RA2000mas<".$lLmas.") AND (RA2000mas>".$rLmas.") AND (DE2000mas>".$dDmas.") AND (DE2000mas<".$uDmas.") AND (vMag<=".($mag*100).") AND (vMag>600) ORDER BY vMag;";
     $stars=$objDatabase->selectRecordsetArray($sql);
   }
   return $stars;
 }
}
$objStar=new Stars;
?>