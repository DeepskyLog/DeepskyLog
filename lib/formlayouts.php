<?php
class formLayouts
{ public function loadLayout($formName,$layoutName)
  { global $objDatabase;
    $restoreColumns=$objDatabase->selectSingleValue("SELECT restoreColumns FROM formlayouts WHERE formName=\"".$formName."\" AND layoutName=\"".$layoutName."\";",'restoreColumns','');
    $orderColumns=$objDatabase->selectSingleValue("SELECT orderColumns FROM formlayouts WHERE formName=\"".$formName."\" AND layoutName=\"".$layoutName."\";",'orderColumns','');
    //echo "<script type=\"text/javascript\">";
    echo "setColumnsOrder(\"".$orderColumns."\");";                                                  //collapse columns
    echo "setColumnsRestore(\"".$restoreColumns."\");";                                              //collapse columns
    //echo "</script>";
  }
  public function saveLayout($formName,$layoutName,$restoreColumns,$orderColumns)
  { global $loggedUser, $objDatabase;
	  echo "saving layout";
    if($layoutName&&($layoutName!='null'))
    { $objDatabase->execSQL("DELETE FROM formlayouts WHERE observerid='".$loggedUser."' AND formName='".$formName."' AND layoutName='".$layoutName."';");
      $objDatabase->execSQL("INSERT INTO formlayouts VALUES(\"".$loggedUser."\",\"".$formName."\",\"".$layoutName."\",\"".$restoreColumns."\",\"".$orderColumns."\");");
    }
  }
}
$objFormLayout = new formLayouts
?>