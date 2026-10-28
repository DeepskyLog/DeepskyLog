<?php
// stars.php
// functions for retieving the star data from the database

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

// The star tables of the old database (stars8 .. stars1775, one magnitude range
// each), moved unchanged into the new DB as legacy_stars8 .. legacy_stars1775.
// The new application's stars table holds the same stars, but in one table,
// which is much slower for these queries of one magnitude in a large area.
class Stars
{ public function getStarsMagnitude($lLhr,$rLhr,$dDdeg,$uDdeg,$frommag,$tomag)
  { global $objDatabase_new;
    $stars=array();
    if($lLhr<$rLhr)
    { if($tomag<14)
      { $sql="SELECT * FROM legacy_stars".$tomag." WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      elseif($tomag==14)
      { $sql="SELECT * FROM legacy_stars140 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars145 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      elseif($tomag==15)
      { $sql="SELECT * FROM legacy_stars1500 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1525 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1550 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1575 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      elseif($tomag==16)
      { $sql="SELECT * FROM legacy_stars1600 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1625 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1650 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1675 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      elseif($tomag==17)
      { $sql="SELECT * FROM legacy_stars1700 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1725 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1750 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1775 WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      else
      { $sql="SELECT * FROM legacy_stars".$tomag." WHERE ((RA2000<".$lLhr.") OR (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      
    }
    else
    { if($tomag<14)
      { $sql="SELECT * FROM legacy_stars".$tomag." WHERE (RA2000<".$lLhr.") AND (RA2000>".$rLhr.") AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=$objDatabase_new->selectRecordsetArray($sql);
      }
      elseif($tomag==14)
      { $sql="SELECT * FROM legacy_stars140 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars145 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      elseif($tomag==15)
      { $sql="SELECT * FROM legacy_stars1500 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1525 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1550 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1575 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      elseif($tomag==16)
      { $sql="SELECT * FROM legacy_stars1600 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1625 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1650 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1675 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      elseif($tomag==17)
      { $sql="SELECT * FROM legacy_stars1700 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1725 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1750 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
        $sql="SELECT * FROM legacy_stars1775 WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
      else
      { $sql="SELECT * FROM legacy_stars".$tomag." WHERE ((RA2000<".$lLhr.") AND (RA2000>".$rLhr.")) AND (DE2000>".$dDdeg.") AND (DE2000<".$uDdeg.");";
        $stars=array_merge($stars,$objDatabase_new->selectRecordsetArray($sql));
      }
    }
    return $stars;
  }
}
?>