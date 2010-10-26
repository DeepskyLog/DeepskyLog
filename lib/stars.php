<?php
// stars.php
// functions for retieving the star data from the database

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class Stars
{ public function getStarsMagnitude($lLhr,$rLhr,$dDdeg,$uDdeg,$frommag,$tomag)
  { global$objDatabase;
    $stars=array();
    if($lLhr<$rLhr)
    { if($tomag<14)
      { $sql="SELECT * FROM stars".$tomag." WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      elseif($tomag==14)
      { $sql="SELECT * FROM stars140 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars145 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      elseif($tomag==15)
      { $sql="SELECT * FROM stars1500 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1525 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1550 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1575 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      elseif($tomag==16)
      { $sql="SELECT * FROM stars1600 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1625 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1650 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1675 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      elseif($tomag==17)
      { $sql="SELECT * FROM stars1700 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1725 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1750 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1775 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      else
      { $sql="SELECT * FROM stars".$tomag." WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      
    }
    else
    { if($tomag<14)
      { $sql="SELECT * FROM stars".$tomag." WHERE (RA2000<".$lLhr.") AND (RA2000>".$rLhr.") AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=$objDatabase->selectRecordsetArray($sql);
      }
      elseif($tomag==14)
      { $sql="SELECT * FROM stars140 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars145 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      elseif($tomag==15)
      { $sql="SELECT * FROM stars1500 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1525 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1550 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1575 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      elseif($tomag==16)
      { $sql="SELECT * FROM stars1600 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1625 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1650 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1675 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      elseif($tomag==17)
      { $sql="SELECT * FROM stars1700 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1725 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1750 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
        $sql="SELECT * FROM stars1775 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
      else
      { $sql="SELECT * FROM stars".$tomag." WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase->selectRecordsetArray($sql));
      }
    }
    return $stars;
  }
}
?>