<?php
// reportlayouts
// functions for managing the report layouts

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class reportLayouts
{ public function loadLayout($formName,$layoutName)
  { global $objDatabase;
  }
  public function deleteLayout($reportname,$reportlayout)
  { global $objDatabase,$loggedUserName;
    $sql="DELETE FROM reportlayouts WHERE observerid='".$loggedUserName."' AND reportname='".$reportname."' AND reportlayout='".$reportlayout."';";
    $objDatabase->execSQL($sql);
  	return $this->getLayoutListJavascript($reportname);
  }
  public function saveLayoutField($reportname,$reportlayout,$fieldname,$fieldline,$fieldposition,$fieldwidth,$fieldheight,$fieldstyle,$fieldbefore,$fieldafter,$fieldlegend)
  { global $loggedUserName, $objDatabase;
	  if($reportname&&$reportlayout&&$fieldname)
      if($thepk=$objDatabase->selectSingleValue("SELECT reportlayoutpk FROM reportlayouts WHERE observerid='".$loggedUserName."' AND reportname='".$reportname."' AND reportlayout='".$reportlayout."' and fieldname='".$fieldname."';","reportlayoutpk",''))
        $objDatabase->execSQL("UPDATE reportlayouts 
                               SET    fieldline='".$fieldline."',
                                      fieldposition='".$fieldposition."',
                                      fieldwidth='".$fieldwidth."',
                                      fieldheight='".$fieldheight."',
                                      fieldstyle='".$fieldstyle."',
                                      fieldbefore='".$fieldbefore."',
                                      fieldafter='".$fieldafter."',
                                      fieldlegend='".$fieldlegend."'
                                      WHERE  reportlayoutpk=".$thepk.";");
      else
        $objDatabase->execSQL("INSERT INTO reportlayouts (observerid, reportname,reportlayout,fieldname,fieldline,fieldposition,fieldwidth,fieldheight,fieldstyle,fieldbefore,fieldafter,fieldlegend)
                               VALUES('".$loggedUserName."','".$reportname."','".$reportlayout."','".$fieldname."','".$fieldline."','".$fieldposition."','".$fieldwidth."','".$fieldheight."','".$fieldstyle."','".$fieldbefore."','".$fieldafter."','".$fieldlegend."');");   
  }
  public function getLayoutListDefault($reportName)
  { global $loggedUser, $objDatabase;
	  if($reportName)
      return $objDatabase->selectRecordsetArray("SELECT DISTINCT observerid, reportlayout FROM reportlayouts WHERE reportname='".$reportName."' ORDER BY observerid, reportlayout;","reportlayout");
    else
      return array();
  }
  public function getLayoutListJavascript($reportName)
  { global $loggedUser, $objDatabase;
	  if($reportName)
	  { $temp=$objDatabase->selectRecordsetArray("SELECT DISTINCT observerid, reportlayout FROM reportlayouts WHERE reportname='".$reportName."' ORDER BY observerid, reportlayout;","reportlayout");
	    for($i=0;$i<count($temp);$i++)
	    { $temp[$i]['observerid']=$temp[$i]['observerid'];
	      $temp[$i]['reportlayout']=$temp[$i]['reportlayout'];
	    }
	    return $temp;
	  }
	  else
      return array();
  }
  public function getLayoutField($observer,$reportname,$reportlayoutname,$fieldname)
  { global $objDatabase;
    $sql="SELECT * FROM reportlayouts WHERE observerid='".$observer."' AND reportname='".$reportname."' AND reportlayout='".$reportlayoutname."' AND fieldname='".$fieldname."';";
    return $objDatabase->selectRecordArray($sql);
  }
  public function getLayoutFieldPosition($observer,$reportname,$reportlayoutname,$fieldname)
  { global $objDatabase;
    $sql="SELECT * FROM reportlayouts WHERE observerid='".$observer."' AND reportname='".$reportname."' AND reportlayout='".$reportlayoutname."' AND fieldname='".$fieldname."';";
    return $objDatabase->selectSingleValue($sql,"fieldposition");
  }
  public function getReportData($observer,$reportname,$layoutname)
  { global $objDatabase;
    $sql="SELECT * FROM reportlayouts WHERE observerid='".$observer."' AND reportname='".$reportname."' AND reportlayout='".$layoutname."' AND fieldstyle!='LAYOUTMETADATA' ORDER BY fieldline;";
	return $objDatabase->selectRecordsetArray($sql);
  }
  public function getReportAll($observer,$reportname,$layoutname)
  { global $objDatabase;
    $sql="SELECT * FROM reportlayouts WHERE observerid='".$observer."' AND reportname='".$reportname."' AND reportlayout='".$layoutname."' ORDER BY reportlayoutpk;";
    if($result=$objDatabase->selectRecordsetArray($sql))
      return $result;
    else
      $sql="SELECT * FROM reportlayouts WHERE observerid='Deepskylog default' AND reportname='".$reportname."' AND reportlayout='Default';";
    return $objDatabase->selectRecordsetArray($sql);
  }
  public function getLayoutListObserver($reportName)
  { global $loggedUser, $objDatabase;
	  if($reportName)
      return $objDatabase->selectSingleArray("SELECT reportlayout FROM reportlayouts WHERE observerid='".$loggedUser."' AND reportname='".$reportName."';","reportlayout");
    else
      return array();
  }
  public function saveLayout($reportname,$reportlayout,$reportdata)
  { //echo $reportdata;
    //return;
    $reportdata=eval('return '.$reportdata.';');
    while(list($key,$data)=each($reportdata)) {
      $this->saveLayoutField($reportname,$reportlayout,$data['fieldname'],$data['fieldline'],$data['fieldposition'],$data['fieldwidth'],$data['fieldheight'],$data['fieldstyle'],$data['fieldbefore'],$data['fieldafter'],$data['fieldlegend']);
    }
    return $this->getLayoutListJavascript($reportname);
  }
}
?>