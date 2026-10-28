<?php
// formlayouts.php
// code for column arrangements on forms

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class formLayouts
{ public function loadLayout($formName,$layoutName)
  { global $objDatabase_new;
    $restoreColumns=$objDatabase_new->selectSingleValue("SELECT restoreColumns FROM legacy_formlayouts WHERE formName=\"".$formName."\" AND layoutName=\"".$layoutName."\";",'restoreColumns','');
    $orderColumns=$objDatabase_new->selectSingleValue("SELECT orderColumns FROM legacy_formlayouts WHERE formName=\"".$formName."\" AND layoutName=\"".$layoutName."\";",'orderColumns','');
    echo "setColumnsOrder(\"".$orderColumns."\");";                                                  //collapse columns
    echo "setColumnsRestore(\"".$restoreColumns."\");";                                              //collapse columns
  }
  public function saveLayout($formName,$layoutName,$restoreColumns,$orderColumns)
  { global $loggedUser, $objDatabase_new;
	  if($layoutName&&($layoutName!='null'))
    { $objDatabase_new->execSQL("DELETE FROM legacy_formlayouts WHERE observerid='".$loggedUser."' AND formName='".$formName."' AND layoutName='".$layoutName."';");
      $objDatabase_new->execSQL("INSERT INTO legacy_formlayouts VALUES(\"".$loggedUser."\",\"".$formName."\",\"".$layoutName."\",\"".$restoreColumns."\",\"".$orderColumns."\");");
    }
  }
  public function removeLayout($formName,$layoutName)
  { global $loggedUser, $objDatabase_new;
    if($layoutName&&($layoutName!='null'))
    { $objDatabase_new->execSQL("DELETE FROM legacy_formlayouts WHERE observerid='".$loggedUser."' AND formName='".$formName."' AND layoutName='".$layoutName."';");
    }
  }
  public function getLayoutList($formName)
  { global $loggedUser, $objDatabase_new;
	  if($formName)
    return $objDatabase_new->selectSingleArray("SELECT layoutName FROM legacy_formlayouts WHERE observerid='".$loggedUser."' AND formName='".$formName."';","layoutName");
  }
}
?>