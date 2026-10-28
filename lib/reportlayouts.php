<?php
// reportlayouts
// functions for managing the report layouts
//
// The layouts are the report_layouts of the new DB: one row per layout, of a
// user (user_id null: the DeepskyLog defaults), with its settings and fields
// as JSON. The pages and PDFs here use them as the rows of the old
// reportlayouts table: one per field, and one per setting (fieldstyle
// LAYOUTMETADATA, the value in fieldposition). The old table named the owner
// by display name ("Deepskylog default" for the defaults).

global $inIndex;
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";

class reportLayouts
{ const DEFAULT_OWNER = 'Deepskylog default';
  // The only report with layouts.
  const REPORT = 'ReportQueryOfObjects';

  public function loadLayout($formName,$layoutName)
  { 
  }
  public function deleteLayout($reportname,$reportlayout)
  { global $objDatabase_new;
    if(($userId=$this->loggedUserId())&&($reportname==self::REPORT))
      $objDatabase_new->execSQL("DELETE FROM report_layouts WHERE user_id=".$userId." AND name=".$objDatabase_new->quote($reportlayout));
  	return $this->getLayoutListJavascript($reportname);
  }
  public function saveLayoutField($reportname,$reportlayout,$fieldname,$fieldline,$fieldposition,$fieldwidth,$fieldheight,$fieldstyle,$fieldbefore,$fieldafter,$fieldlegend)
  { $this->saveLayout($reportname,$reportlayout,array(array(
      'fieldname'=>$fieldname,'fieldline'=>$fieldline,'fieldposition'=>$fieldposition,'fieldwidth'=>$fieldwidth,
      'fieldstyle'=>$fieldstyle,'fieldbefore'=>$fieldbefore,'fieldafter'=>$fieldafter,'fieldlegend'=>$fieldlegend)));
  }
  public function getLayoutListDefault($reportName)
  { return $this->getLayoutListJavascript($reportName);
  }
  public function getLayoutListJavascript($reportName)
  { global $objDatabase_new;
	  if($reportName!=self::REPORT)
      return array();
    return $objDatabase_new->selectRecordsetArray(
      "SELECT DISTINCT COALESCE(u.name, ".$objDatabase_new->quote(self::DEFAULT_OWNER).") AS observerid, rl.name AS reportlayout"
      ." FROM report_layouts rl LEFT JOIN users u ON u.id = rl.user_id"
      ." WHERE rl.user_id IS NULL OR u.id IS NOT NULL ORDER BY observerid, reportlayout;");
  }
  public function getLayoutField($observer,$reportname,$reportlayoutname,$fieldname)
  { foreach($this->rows($observer,$reportname,$reportlayoutname) as $row)
      if($row['fieldname']==$fieldname)
        return $row;
    return array();
  }
  public function getLayoutFieldPosition($observer,$reportname,$reportlayoutname,$fieldname)
  { $row=$this->getLayoutField($observer,$reportname,$reportlayoutname,$fieldname);
    return $row?$row['fieldposition']:'';
  }
  public function getReportData($observer,$reportname,$layoutname)
  { $rows=array();
    foreach($this->rows($observer,$reportname,$layoutname) as $row)
      if($row['fieldstyle']!='LAYOUTMETADATA')
        $rows[]=$row;
    usort($rows,function($a,$b){ return $a['fieldline']-$b['fieldline']; });
	return $rows;
  }
  public function getReportAll($observer,$reportname,$layoutname)
  { if($result=$this->rows($observer,$reportname,$layoutname))
      return $result;
    // The first layout of DeepskyLog.
    $defaults=$this->getLayoutListJavascript($reportname);
    foreach($defaults as $default)
      if($default['observerid']==self::DEFAULT_OWNER)
        return $this->rows(self::DEFAULT_OWNER,$reportname,$default['reportlayout']);
    return array();
  }
  public function getLayoutListObserver($reportName)
  { global $objDatabase_new;
	  if(($reportName==self::REPORT)&&($userId=$this->loggedUserId()))
      return $objDatabase_new->selectSingleArray("SELECT name AS reportlayout FROM report_layouts WHERE user_id=".$userId.";","reportlayout");
    return array();
  }
  // $reportdata: the rows of the layout as JSON (lib/javascript/reportlayouts.js), or an array of rows.
  // A field or setting not in it keeps its value.
  public function saveLayout($reportname,$reportlayout,$reportdata)
  { global $objDatabase_new;
    $userId=$this->loggedUserId();
    $rows=is_array($reportdata)?$reportdata:json_decode($reportdata,true);
    $reportlayout=trim(html_entity_decode($reportlayout,ENT_QUOTES,'UTF-8'));
    if($userId&&($reportname==self::REPORT)&&($reportlayout!='')&&is_array($rows))
    { $layout=$objDatabase_new->selectRecordArray("SELECT id, settings, fields FROM report_layouts WHERE user_id=".$userId." AND name=".$objDatabase_new->quote($reportlayout));
      $settings=$layout?(array)json_decode($layout['settings'],true):array();
      $fields=$layout?(array)json_decode($layout['fields'],true):array();
      foreach($rows as $row)
      { if(!is_array($row)||!isset($row['fieldname'])||($row['fieldname']==''))
          continue;
        if(isset($row['fieldstyle'])&&($row['fieldstyle']=='LAYOUTMETADATA'))
          $settings[$row['fieldname']]=(string)$row['fieldposition'];
        else
          $fields[$row['fieldname']]=array(
            'line'=>(int)$row['fieldline'],
            'x'=>(float)$row['fieldposition'],
            'width'=>(float)$row['fieldwidth'],
            'style'=>(string)$row['fieldstyle'],
            'before'=>(string)$row['fieldbefore'],
            'after'=>(string)$row['fieldafter'],
            'legend'=>(string)$row['fieldlegend']);
      }
      $json=function($value) use ($objDatabase_new) { return $objDatabase_new->quote(json_encode($value,JSON_UNESCAPED_UNICODE)); };
      if($layout)
        $objDatabase_new->execSQL("UPDATE report_layouts SET settings=".$json($settings).", fields=".$json($fields).", updated_at=NOW() WHERE id=".(int)$layout['id']);
      else
        $objDatabase_new->execSQL("INSERT INTO report_layouts (user_id, name, settings, fields, created_at, updated_at) VALUES ("
          .$userId.", ".$objDatabase_new->quote(mb_substr($reportlayout,0,255)).", ".$json($settings).", ".$json($fields).", NOW(), NOW())");
    }
    return $this->getLayoutListJavascript($reportname);
  }

  private function loggedUserId()
  { global $objDatabase_new, $loggedUser;
    if(!$loggedUser)
      return 0;
    return (int)$objDatabase_new->selectSingleValue("SELECT id FROM users WHERE username=".$objDatabase_new->quote(html_entity_decode($loggedUser,ENT_QUOTES,'UTF-8')),'id',0);
  }

  // The layout as rows of the old table.
  private function rows($observer,$reportname,$layoutname)
  { global $objDatabase_new;
    if($reportname!=self::REPORT)
      return array();
    $observer=html_entity_decode($observer,ENT_QUOTES,'UTF-8');
    $owner=($observer==self::DEFAULT_OWNER)?"rl.user_id IS NULL":"u.name=".$objDatabase_new->quote($observer);
    $layout=$objDatabase_new->selectRecordArray("SELECT rl.id, rl.settings, rl.fields FROM report_layouts rl LEFT JOIN users u ON u.id = rl.user_id"
      ." WHERE ".$owner." AND rl.name=".$objDatabase_new->quote(html_entity_decode($layoutname,ENT_QUOTES,'UTF-8'))." ORDER BY rl.id LIMIT 1");
    if(!$layout)
      return array();
    $base=array('reportlayoutpk'=>$layout['id'],'observerid'=>$observer,'reportname'=>$reportname,'reportlayout'=>$layoutname);
    $rows=array();
    foreach((array)json_decode($layout['fields'],true) as $name=>$field)
      $rows[]=$base+array('fieldname'=>$name,'fieldline'=>$field['line'],'fieldposition'=>$field['x'],'fieldwidth'=>$field['width'],
        'fieldheight'=>0,'fieldstyle'=>$field['style'],'fieldbefore'=>$field['before'],'fieldafter'=>$field['after'],'fieldlegend'=>$field['legend']);
    foreach((array)json_decode($layout['settings'],true) as $name=>$value)
      $rows[]=$base+array('fieldname'=>$name,'fieldline'=>0,'fieldposition'=>$value,'fieldwidth'=>0,
        'fieldheight'=>0,'fieldstyle'=>'LAYOUTMETADATA','fieldbefore'=>'','fieldafter'=>'','fieldlegend'=>'');
    return $rows;
  }
}
?>
