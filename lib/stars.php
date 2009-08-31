<?php
interface iStars
{ public function getStars($lLhr,$rLhr,$dDdeg,$uDdeg,$mag);      // returns an array containing all star data between the specified coordinates
	
}
class Stars implements iStars
{
 public function getStars($lLhr,$rLhr,$dDdeg,$uDdeg,$mag)
 { global$objDatabase;
 	 $stars=array();
   if($lLhr<$rLhr)
   { $sql="SELECT * FROM stars WHERE (RA2000<".$lLhr.") AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.") AND (vMag<=".($mag*100).") ORDER BY vMag;";
     $stars=$objDatabase->selectRecordsetArray($sql);  
     $sql="SELECT * FROM stars WHERE (RA2000>".$rLhr.") AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.") AND (vMag<=".($mag*100).") ORDER BY vMag;";
     $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));  
   }
   else
   { $sql="SELECT * FROM stars WHERE (RA2000<".$lLhr.") AND (RA2000>".$rLhr.") AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.") AND (vMag<=".($mag*100).") ORDER BY vMag;";
     $stars=$objDatabase->selectRecordsetArray($sql);
   }
   return $stars;
 }
}
$objStar=new Stars;
?>