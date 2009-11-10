<?php
interface iConstellation
{ public function getAllBoundries();
}
class Constellation implements iConstellation
{ public function getAllBoundries()
  { global $objDatabase;
    return $objDatabase->selectRecordsetArray("SELECT * FROM conlines;");
  }
}
$objConstellation=new Constellation;
?>