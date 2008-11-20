<?php

//CLASS util
// INTERFACE
//   public function utiltiesDispatchIndexActionDS() -> returns the file to be included to execute the action specified in $_GET['indexAcction']
//   public function utilitiesSetModuleCookie($module)
//	 public function checkGetKey($key,$default='')
//   public function checkGetDate($year,$month,$day)
//   public function checkGetTimeOrDegrees($hr,$min,$sec)
// ..
// PUBLIC OBJECT
//  $objUtil  


if (!function_exists('fnmatch'))
{
  function fnmatch($pattern, $string)
  {return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
  }
}

include_once "setup/vars.php";
include_once "class.ezpdf.php";

class util
{
  public function __construct()
	{ $this->checkUserInput();
  }
  private function utilitiesGetIndexActionCommonDefaultAction()
  { return 'error.php';
  }
  private function utilitiesGetIndexActionDSdefaultAction()
  { $_GET['catalogue']='%';
  	$theDate = date('Ymd', strtotime('-1 year'));
    $_GET['minyear'] = substr($theDate,0,4);
    $_GET['minmonth'] = substr($theDate,4,2);
    $_GET['minday'] = substr($theDate,6,2);  
  	return 'content/selected_observations2.php';
  }
  private function utilitiesCheckIndexActionDSquickPick()
  { if(array_key_exists('indexAction',$_GET)&&($_GET['indexAction'] == 'quickpick'))
    { $indexAction='quickpick';
      $objects = $GLOBALS['objObject'];
      $temp = $objects->getExactDsObject($_GET['object']);
      if($temp)
      { $_GET['object'] = $temp;
        if(array_key_exists('searchObservations', $_GET))
          return 'content/selected_observations2.php';  
        elseif(array_key_exists('newObservation', $_GET))
          return 'content/new_observation.php';   
        else
          return 'content/view_object.php';  
      }
      else
      { $_SID=time();
    		$_GET['SID']=$_SID;
    	  $_GET['catNumber']=ucwords(trim($_GET['object']));
        return 'content/setup_objects_query.php';  	
      }
    }
  }
  private function utilitiesCheckIndexActionAll($action, $includefile)
  { if(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == $action))
      return $includefile;
  }
  private function utilitiesCheckIndexActionMember($action, $includefile)
  { if(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == $action) && 
       array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id']!=""))
      return $includefile; 
  }
  private function utilitiesCheckIndexActionAdmin($action, $includefile)
  { if(array_key_exists('indexAction',$_GET) && ($_GET['indexAction'] == $action) && 
       array_key_exists('admin', $_SESSION) && ($_SESSION['admin'] == "yes"))
      return $includefile; 
  }
	
  function printNewListHeader(&$list, $link, $min, $step, $total)
  { global $baseURL;
	  $pages = ceil(count($list) / $step);       // total number of pages
    if($min)                                   // minimum value
    { $min = $min - ($min % $step);            // start display from number of $steps
      if ($min < 0)                            // minimum value smaller than 0
        $min = 0;
      if ($min > count($list))                 // minimum value bigger than number of elements
        $min = count($list) - (count($list) % $step);
    }
    else                                       // no minimum value defined
      $min = 0;
    $max = $min + $step;                       // maximum number to be displayed
    if(count($list) > $step)
    { $currentpage = ceil($min / $step) + 1;
      echo("<table>");
		  
			echo("<td>");	
      echo("<a href=\"".$link."&amp;multiplepagenr=0\">");
      echo "<img src=\"".$baseURL."/styles/images/allleft20.gif\" border=\"0\">"; // link to last page
      echo("</a>\n");
	    echo"</td>";
			
		  echo"<td>";
	    echo("<a href=\"".$link."&amp;multiplepagenr=".($currentpage-1) . "\">");
      echo "<img src=\"".$baseURL."/styles/images/left20.gif\" border=\"0\">"; // link to last page
      echo("</a>\n");
		  echo"</td>";
		  
			echo"<td align=\"center\">";
      echo("<form action=\"".$link."\" method=\"post\">");
      echo("<input type=\"text\" name=\"multiplepagenr\" size=\"4\" class=\"inputfield\" style=\"text-align:center\" value=\"".$currentpage."\"></input>");
	    echo("</form>");
    	echo"</td>";	
	
		  echo"<td>";
      echo("<a href=\"".$link."&amp;multiplepagenr=".($currentpage+1) . "\">");
      echo "<img src=\"".$baseURL."/styles/images/right20.gif\" border=\"0\">"; // link to last page
      echo("</a>\n");
		  echo"</td>";

		  echo("<td>");				
		  echo("<a href=\"".$link."&amp;multiplepagenr=9999999\">");
      echo "<img src=\"".$baseURL."/styles/images/allright20.gif\" border=\"0\">"; // link to last page
      echo("</a>\n");
	    echo"</td>";

  		echo"<td>";
			if(($total == "") || $total==count($list))
        echo("&nbsp;&nbsp;(" . count($list) . "&nbsp;" . LangNumberOfRecords );
      else
        echo("&nbsp;&nbsp;(" . count($list) . "&nbsp;" . LangNumberOfRecords . " / " . $total );
      echo(" in " . $pages . " pages)</p>\n");
      echo"</td>";
	
	    echo"</table>";    
	  }
    return array($min, $max);
  }


	// printListHeader prints the list header of $list if the list has more than
  // $step entries. The first item from the list that should be shown is $min.
  // All numbers use the given link. An array is given back, with the min and
  // max value. Example :
  // list($min, $max) = $util->printListHeader($obs, $link, $_GET['min'], 25, 1221);
  function printListHeader($list, $link, $min, $step, $total)
  {
    $pages = ceil(count($list) / $step); // total number of pages
    if($min) // minimum value
    { $min = $min - ($min % $step); // start display from number of $steps
      if ($min < 0)  // minimum value smaller than 0
        $min = 0;
      if ($min > count($list)) // minimum value bigger than number of elements
        $min = count($list) - (count($list) % $step);
    }
    else // no minimum value defined
      $min = 0;
    $max = $min + $step; // maximum number to be displayed
    if(count($list) > $step)
    {
      $currentpage = ceil($min / $step) + 1;
      echo("<p>\n");
      echo("<a href=\"".$link."&amp;min=0\">");
      echo LangOverviewObjectsFirstlink; // link to first page
      echo("</a>&nbsp;&nbsp;&nbsp;\n");

      if ($currentpage <= 7)
        $start = -$currentpage + 1;
      else if ($currentpage >= $pages - 7)
        $start = -14 + ($pages - $currentpage);
      else
        $start = -7;

      for ($i = $start; $i <= $start + 14; $i++)
      {
        $pagenumber = ($min + ($step * $i));
        if((($pagenumber/$step) >= 0) && (($pagenumber/$step) < $pages))
        { if($i != 0) // not current page
            echo("<a href=\"".$link."&amp;min=" . $pagenumber . "\">" . ($pagenumber/$step + 1) . "</a>&nbsp;"); // link to other page
          else
            echo(($pagenumber/$step + 1) . "&nbsp;"); // current page
        }
      }
      echo("&nbsp;&nbsp;");
			
			echo("<a href=\"".$link."&amp;min=".(($pages*$step) - 1) . "\">");
      echo LangOverviewObjectsLastlink; // link to last page
      echo("</a>\n");
      
			if ($total == "")
        echo("&nbsp;&nbsp;(" . count($list) . "&nbsp;" . LangNumberOfRecords . ")");
      else
        echo("&nbsp;&nbsp;(" . count($list) . "&nbsp;" . LangNumberOfRecords . " / " . $total . ")");
      echo("</p>\n");
    }
    return array($min, $max);
  }

  // Array slice, but uses also keys.
  function array_slice_key($array, $offset, $len=-1)
  {
    if (!is_array($array))
    return FALSE;

    $length = $len >= 0? $len: count($array);
    $keys = array_slice(array_keys($array), $offset, $length);
    foreach($keys as $key)
    {
      $return[$key] = $array[$key];
    }
    return $return;
  }

  // function to correct data input of users to eliminate XSS exploits
  function checkUserInput()
  {
    foreach($_POST as $foo => $bar)
    {
      $_POST[$foo] = htmlentities($bar, ENT_COMPAT, "ISO-8859-15", 0);
    }
    foreach($_GET as $foo => $bar)
    {
      $_GET[$foo] = htmlentities($bar, ENT_COMPAT, "ISO-8859-15", 0);
    }
  }

  // Creates a pdf document from an array of objects
  function pdfObjects($result)
  { global $deepskylive, $dateformat;
		$atlasses = $GLOBALS['objAtlas']->getSortedAtlasses();
		
    while(list ($key, $valueA) = each($result))
    { $mag = $valueA[5];
      if ($mag == 99.9)
      $mag = "";
      else if ($mag - (int)$mag == 0.0)
      $mag = $mag.".0";

      $sb = $valueA[6];
      if ($sb == 99.9)
      $sb = "";
      else if ($sb - (int)$sb == 0.0)
      $sb = $sb.".0";

      $pa = $valueA[20];
      if($pa==999)
      $pa="-";

      $con = $valueA['objectconstellation'];
      $type = $valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $diam1 = $valueA[18];
      $diam2 = $valueA[19];
      $size = "";
      if ($diam1 != 0.0)
      if ($diam1 >= 40.0)
      {
        if (round($diam1 / 60.0) == ($diam1 / 60.0))
        if ($diam1 / 60.0 > 30.0)
        $size = sprintf("%.0f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        if ($diam2 != 0.0)
        if (round($diam2 / 60.0) == ($diam2 / 60.0))
        if ($diam2 / 60.0 > 30.0)
        $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
      }
      else
      {
        $size = sprintf("%.1f''", $diam1);
        if ($diam2 != 0.0)
        $size = $size.sprintf("x%.1f''", $diam2);
      }
      $contrast = $valueA[21];
      if ($contrast == "-")
      {
        $magnifi = "-";
      } else {
        $magnifi = (int)$valueA[25];
      }

      $temp = array("Name" => $valueA[4],
                 "ra" => raToString($valueA[7]),
                 "decl" => decToString($valueA[8], 0),
                 "mag" => $mag,
                 "sb" => $sb,
                 "con" => $GLOBALS[$con],
                 "diam" => $size,
                 "pa" => $pa, 
                 "type" => $GLOBALS[$type],
                 "page" => $page,
                 "contrast" => $contrast,
                 "magnification" => $magnifi,
                 "seen" => $valueA[3],
								 "seendate" => $valueA[28]
      );
      $obs1[] = $temp;
    }

    // Create pdf file
    $pdf = new Cezpdf('a4', 'landscape');
    $pdf->ezStartPageNumbers(450, 15, 10);

    $fontdir = /*realpath*/('../lib/fonts/Helvetica.afm');
    //  $pdf->selectFont($fontdir);
    $pdf->selectFont('../lib/fonts/Helvetica.afm');

    $pdf->ezTable($obs1,
    array("Name" => html_entity_decode(LangPDFMessage1),
                      "ra" =>   html_entity_decode(LangPDFMessage3),
                      "decl" => html_entity_decode(LangPDFMessage4),
                      "type" => html_entity_decode(LangPDFMessage5),
                      "con" =>  html_entity_decode(LangPDFMessage6),
                      "mag" =>  html_entity_decode(LangPDFMessage7),
                      "sb" =>   html_entity_decode(LangPDFMessage8),
                      "diam" => html_entity_decode(LangPDFMessage9),
                      "pa" =>   html_entity_decode(LangPDFMessage16),  
                      "page" => html_entity_decode($atlasses[$atlas]),
                      "contrast" => html_entity_decode(LangPDFMessage17),
                      "magnification" => html_entity_decode(LangPDFMessage18),
                      "seen" => html_entity_decode(LangOverviewObjectsHeader7),
                      "seendate" => html_entity_decode(LangOverviewObjectsHeader8)
    ),
    $_GET['pdfTitle'],
    array("width" => "750",
			                "cols" => array("Name" => array('justification'=>'left',  'width'=>100),
			                                "ra" =>   array('justification'=>'center','width'=>65),
		              									  "decl" => array('justification'=>'center','width'=>50),
									              		  "type" => array('justification'=>'left',  'width'=>110),
              											  "con" =>  array('justification'=>'left',  'width'=>90),
							              				  "mag" =>  array('justification'=>'center','width'=>35),
              											  "sb" =>   array('justification'=>'center','width'=>35),
							              			  	"diam" => array('justification'=>'center','width'=>65),
       											          "pa" =>   array('justification'=>'center','width'=>30),
				              							  "page" => array('justification'=>'center', 'width'=>45),
          														"contrast" => array('justification'=>'center', 'width'=>35),
          														"magnification" => array('justification'=>'center', 'width'=>35),
											                "seen" => array('justification'=>'center','width'=>50),
											                "seendate" => array('justification'=>'center','width'=>50)
    ),
											"fontSize" => "7"				         
											)
											);
											$pdf->ezStream();
  }

  // Creates a pdf document from an array of objects
  function pdfObjectnames($result)
  {
    $page=1;
    $i=0;
    while(list($key, $valueA) = each($result))
    $obs1[] = array($valueA[4]);
    // Create pdf file
    $pdf = new Cezpdf('a4', 'landscape');
    $pdf->ezStartPageNumbers(450, 15, 10);

    $fontdir = /*realpath*/('../lib/fonts/Helvetica.afm');
    //  $pdf->selectFont($fontdir);
    $pdf->selectFont('../lib/fonts/Helvetica.afm');
    $pdf->ezText($_GET['pdfTitle'],18);
    $pdf->ezColumnsStart(array('num'=>10));
    $pdf->ezTable($obs1,
                '', 
	              '',
    array("width" => "750",
			                "cols" => array(array('justification'=>'left', 'width'=>80)
    ),
											"fontSize" => "7",
											"showLines" => "0",
											"showHeadings" => "0",
											"rowGap" => "0",
											"colGap" => "0"				         
											)
											);
											$pdf->ezStream();
  }


  // Creates a pdf document from an array of objects
  function pdfObjectsDetails($result, $sort='')
  { if($sort=='objectconstellation') $sort='con'; else $sort='';
	  global $deepskylive, $dateformat;
		global $baseURL, $dbname;
		
		$atlasses = $GLOBALS['objAtlas']->getSortedAtlasses();

    // Create pdf file
    $pdf = new Cezpdf('a4', 'landscape');
    $fontdir = ('../lib/fonts/Helvetica.afm');
    $pdf->selectFont('../lib/fonts/Helvetica.afm');

    $actualsort='';
    $y = 0;
    $bottom = 40;
    $bottomsection = 30;
    $top = 550;
    $header = 570;
    $footer = 10;
		$xleft = 20;
    $xmid = 431;
    $fontSizeSection = 10;
    $fontSizeText = 8;
    $deltaline = $fontSizeText+4;
		$deltalineSection = 2;
		$pagenr = 0;
    $xbase = $xmid;
		$sectionBarHeight = $fontSizeSection + 4;
		$descriptionLeadingSpace = 20;
		$sectionBarSpace = 3;
		$SectionBarWidth = 400+$sectionBarSpace;
    $theDate=date('d/m/Y');
		$pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
		$pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
		    LangPDFMessage19 . $GLOBALS['objObserver']->getFirstName($_SESSION['deepskylog_id']) . ' ' . 
				                   $GLOBALS['objObserver']->getObserverName($_SESSION['deepskylog_id']) . ' ' .
		    LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentName($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
				LangPDFMessage21 . $GLOBALS['objLocation']->getLocationName($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
		$pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, $_GET['pdfTitle'], 'center' );
		$pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . '1', 'right');
		while(list($key, $valueA) = each($result))
    {
			$mag = round($valueA[5],1); if ($mag == 99.9) $mag = ""; else if ($mag - (int)$mag == 0.0) $mag = $mag.".0";
      $sb = round($valueA[6],1);  if ($sb == 99.9)  $sb = "";  else if ($sb - (int)$sb == 0.0)   $sb = $sb.".0";
      $pa = $valueA[20];          if($pa==999)      $pa="-";
			
      $con = $valueA['objectconstellation'];
      $type = $valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $diam1 = $valueA[18];
      $diam2 = $valueA[19];
      $size = "";
      if ($diam1 >= 40.0)
      {
        if (round($diam1 / 60.0) == ($diam1 / 60.0))
				{ if ($diam1 / 60.0 > 30.0)
            $size = sprintf("%.0f'", $diam1 / 60.0);
          else
            $size = sprintf("%.1f'", $diam1 / 60.0);
        }
				else
          $size = sprintf("%.1f'", $diam1 / 60.0);
        
				if ($diam2 != 0.0)
        { if (round($diam2 / 60.0) == ($diam2 / 60.0))
          { if ($diam2 / 60.0 > 30.0)
              $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
            else
              $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
          }
				  else
            $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
				}
      }
      elseif ($diam1 != 0.0) 
      {
        $size = sprintf("%.1f''", $diam1);
        if ($diam2 != 0.0)
          $size = $size.sprintf("x%.1f''", $diam2);
      }
			$contrast = $valueA[21];
      if ($contrast == "-")
      {
        $magnifi = "-";
      } 
			else {
        $magnifi = (int)$valueA[25];
        $contrast = round($valueA[21],1);  if ($contrast - (int)$contrast == 0.0) $contrast = $contrast.".0";
      }
			
      if(!$sort || ($actualsort!=$$sort))
			{
  			if($y<$bottom) 
  			{ $y=$top;
  			  if($xbase==$xmid)
  				{ if($pagenr++) 
					  { $pdf->newPage();
						  $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
          		$pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
		                   LangPDFMessage19 . $GLOBALS['objObserver']->getObserverName($_SESSION['deepskylog_id']) . ' ' . 
		                                      $GLOBALS['objObserver']->getFirstName($_SESSION['deepskylog_id']) . ' ' .
                       LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentName($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
				               LangPDFMessage21 . $GLOBALS['objLocation']->getLocationName($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
		          $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, $_GET['pdfTitle'], 'center' );
		          $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenr, 'right');
  					}
						$xbase = $xleft;
  				}
  				else
  				{ $xbase = $xmid;
  				}
  			}
				if($sort)
				{ $y-=$deltalineSection;
          $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
          $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);  
          $y-=$deltaline+$deltalineSection;
				}
			}
      elseif($y<$bottomsection) 
			{ $y=$top;
			  if($xbase==$xmid)
				{ if($pagenr++) 
				  { $pdf->newPage();
					  $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
        		$pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
	                   LangPDFMessage19 . $GLOBALS['objObserver']->getObserverName($_SESSION['deepskylog_id']) . ' ' .
	                                      $GLOBALS['objObserver']->getFirstName($_SESSION['deepskylog_id']) . ' ' .
                     LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentName($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
			               LangPDFMessage21 . $GLOBALS['objLocation']->getLocationName($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
            $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, $_GET['pdfTitle'], 'center' );
	          $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenr, 'right');
					}
					$xbase = $xleft;
          if($sort)
					{ $y-=$deltalineSection;
            $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
            $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
            $y-=$deltaline+$deltalineSection;
					}
				}
				else
				{ $xbase = $xmid;
          if($sort)
					{ $y-=$deltalineSection;
            $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
					  $pdf->addText($xbase, $y, $fontSizeSection, $GLOBALS[$$sort]);
            $y-=$deltaline+$deltalineSection;
					}
				}
			}
			if(!$sort)
			{ $pdf->addTextWrap($xbase    , $y,  30, $fontSizeText, $valueA[3]);			                   // seen
			  $pdf->addTextWrap($xbase+ 30, $y,  40, $fontSizeText, $valueA[28]);		                     // last seen	
			  $pdf->addTextWrap($xbase+ 70, $y,  85, $fontSizeText, '<b>'.
				  '<c:alink:'.$baseURL.'deepsky/index.php?indexAction=detail_object&amp;object='.
					urlencode($valueA['objectname']).'>'.$valueA[4]);		               //	object
			  $pdf->addTextWrap($xbase+150, $y,  30, $fontSizeText, '</c:alink></b>'.$type);			                 // type
			  $pdf->addTextWrap($xbase+180, $y,  20, $fontSizeText, $con);			                         // constellation
			  $pdf->addTextWrap($xbase+200, $y,  17, $fontSizeText, $mag, 'left');  	                 // mag
			  $pdf->addTextWrap($xbase+217, $y,  18, $fontSizeText, $sb, 'left');		                   // sb
			  $pdf->addTextWrap($xbase+235, $y,  60, $fontSizeText, raToStringHM($valueA[7]) . ' '.
				                                                      decToString($valueA[8],0));	 // ra - decl
			  $pdf->addTextWrap($xbase+295, $y,  55, $fontSizeText, $size . '/' . $pa);			             // size
	  		$pdf->addTextWrap($xbase+351, $y,  17, $fontSizeText, $contrast, 'left');			             // contrast				
	  		$pdf->addTextWrap($xbase+368, $y,  17, $fontSizeText, $magnifi, 'left');			             // magnification				
			  $pdf->addTextWrap($xbase+380, $y,  20, $fontSizeText, '<b>'.$page.'</b>', 'right');			   // atlas page
      }
      else
			{ $pdf->addTextWrap($xbase    , $y,  30, $fontSizeText, $valueA[3]);			                   // seen
			  $pdf->addTextWrap($xbase+ 30, $y,  40, $fontSizeText, $valueA[28]);		                     // last seen	
			  $pdf->addTextWrap($xbase+ 70, $y, 100, $fontSizeText, '<b>'.
				  '<c:alink:'.$baseURL.'deepsky/index.php?indexAction=detail_object&amp;object='.
					urlencode($valueA['objectname']).'>'.$valueA[4]);		                                       //	object
			  $pdf->addTextWrap($xbase+170, $y,  30, $fontSizeText, '</c:alink></b>'.$type);			                 // type
			  $pdf->addTextWrap($xbase+200, $y,  17, $fontSizeText, $mag, 'left');			                 // mag
			  $pdf->addTextWrap($xbase+217, $y,  18, $fontSizeText, $sb, 'left');			                   // sb
			  $pdf->addTextWrap($xbase+235, $y,  60, $fontSizeText, raToStringHM($valueA[7]) . ' '.
				                                                      decToString($valueA[8],0));	 // ra - decl
			  $pdf->addTextWrap($xbase+295, $y,  55, $fontSizeText, $size . '/' . $pa);         			   // size
	  		$pdf->addTextWrap($xbase+351, $y,  17, $fontSizeText, $contrast, 'left');			             // contrast				
	  		$pdf->addTextWrap($xbase+368, $y,  17, $fontSizeText, $magnifi, 'left');		               // magnification				
			  $pdf->addTextWrap($xbase+380, $y,  20, $fontSizeText, '<b>'.$page.'</b>', 'right');			   // atlas page
      }
			$y-=$deltaline;
      if($sort)
			  $actualsort = $$sort;
			if(array_key_exists(30,$valueA) && $valueA[30])
      { $theText= $valueA[30];
			  $theText= $pdf->addTextWrap($xbase+$descriptionLeadingSpace, $y, $xmid-$xleft-$descriptionLeadingSpace-10 ,$fontSizeText, '<i>'.$theText);
  			$y-=$deltaline;	
        while($theText)
				{ if($y<$bottomsection) 
			    { $y=$top;
			      if($xbase==$xmid)
				    { if($pagenr++)
						  { $pdf->newPage();
							  $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
          		  $pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
		                   LangPDFMessage19 . $GLOBALS['objObserver']->getObserverName($_SESSION['deepskylog_id']) . ' ' . 
		                                      $GLOBALS['objObserver']->getFirstName($_SESSION['deepskylog_id']) . 
                       LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentName($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
				               LangPDFMessage21 . $GLOBALS['objLocation']->getLocationName($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
		            $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, $_GET['pdfTitle'], 'center' );
		            $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenr, 'right');
          	  }
						  $xbase = $xleft;
              if($sort)
							{ $y-=$deltalineSection;
                $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
                $pdf->addText($xbase, $y, $fontSizeSection, $$$sort);
                $y-=$deltaline+$deltalineSection;
							}
				    }
				    else
				    { $xbase = $xmid;
              if($sort)
							{ $y-=$deltalineSection;
                $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
					      $pdf->addText($xbase, $y, $fontSizeSection, $$$sort);
                $y-=$deltaline+$deltalineSection;
							}
				    }
			    }
				$theText= $pdf->addTextWrap($xbase+$descriptionLeadingSpace, $y, $xmid-$xleft-$descriptionLeadingSpace-10 ,$fontSizeText, $theText);
  			$y-=$deltaline;	
				}
			  $pdf->addText(0,0,10,'</i>');
			}
			elseif(array_key_exists(27,$valueA) && $valueA[27])
      { $theText= $valueA[27];
			  $theText= $pdf->addTextWrap($xbase+$descriptionLeadingSpace, $y, $xmid-$xleft-$descriptionLeadingSpace-10 ,$fontSizeText, '<i>'.$theText);
  			$y-=$deltaline;	
        while($theText)
				{ if($y<$bottomsection) 
			    { $y=$top;
			      if($xbase==$xmid)
				    { if($pagenr++)
						  { $pdf->newPage();
							  $pdf->addTextWrap($xleft, $header, 100, 8, $theDate);
          		  $pdf->addTextWrap($xleft, $footer, $xmid+$SectionBarWidth, 8, 
		                   LangPDFMessage19 . $GLOBALS['objObserver']->getObserverName($_SESSION['deepskylog_id']) . ' ' . 
		                                      $GLOBALS['objObserver']->getFirstName($_SESSION['deepskylog_id']) . 
                       LangPDFMessage20 . $GLOBALS['objInstrument']->getInstrumentName($GLOBALS['objObserver']->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
				               LangPDFMessage21 . $GLOBALS['objLocation']->getLocationName($GLOBALS['objObserver']->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
		            $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, $_GET['pdfTitle'], 'center' );
		            $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenr, 'right');
          	  }
						  $xbase = $xleft;
              if($sort)
							{ $y-=$deltalineSection;
                $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
                $pdf->addText($xbase, $y, $fontSizeSection, $$$sort);
                $y-=$deltaline+$deltalineSection;
							}
				    }
				    else
				    { $xbase = $xmid;
              if($sort)
							{ $y-=$deltalineSection;
                $pdf->rectangle($xbase-$sectionBarSpace, $y-$sectionBarSpace, $SectionBarWidth, $sectionBarHeight);
					      $pdf->addText($xbase, $y, $fontSizeSection, $$$sort);
                $y-=$deltaline+$deltalineSection;
							}
				    }
			    }
				$theText= $pdf->addTextWrap($xbase+$descriptionLeadingSpace, $y, $xmid-$xleft-$descriptionLeadingSpace-10 ,$fontSizeText, $theText);
  			$y-=$deltaline;	
				}
			  $pdf->addText(0,0,10,'</i>');
			}			
		}		
    $pdf->Stream(); 
  }

  // Creates a pdf document from an array of objects
  function pdfObjectsDetails2($result)
  { global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
    $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
    $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL;

    global $ASTER,$BRTNB,$CLANB,$DRKNB,$GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,
    $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$QUASR,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

    global $EMINB,$REFNB,$ENRNN,$ENSTR,$HII,$RNHII,$STNEB,$WRNEB;

    global $deepskylive, $dateformat;

    while(list($key, $valueA) = each($result))
    {
      $mag = round($valueA[5],1);
      if ($mag == 99.9)
      $mag = "";
      else if ($mag - (int)$mag == 0.0)
      $mag = $mag.".0";

      $sb = round($valueA[6],1);
      if ($sb == 99.9)
      $sb = "";
      else if ($sb - (int)$sb == 0.0)
      $sb = $sb.".0";

      $pa = $valueA[20];
      if($pa==999)
      $pa="-";

      $con = $valueA['objectconstellation'];
      $type = $valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $diam1 = $valueA[18];
      $diam2 = $valueA[19];
      $size = "";
      if ($diam1 != 0.0)
      if ($diam1 >= 40.0)
      {
        if (round($diam1 / 60.0) == ($diam1 / 60.0))
        if ($diam1 / 60.0 > 30.0)
        $size = sprintf("%.0f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        if ($diam2 != 0.0)
        if (round($diam2 / 60.0) == ($diam2 / 60.0))
        if ($diam2 / 60.0 > 30.0)
        $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
      }
      else
      {
        $size = sprintf("%.1f''", $diam1);
        if ($diam2 != 0.0)
        $size = $size.sprintf("x%.1f''", $diam2);
      }
      $contrast = $valueA[21];
      if ($contrast == "-")
      {
        $magnifi = "-";
      } else {
        $magnifi = (int)$valueA[25];
      }

      $tempA = array("Name" => $valueA[4],
                 "ra" => "0",//$this->raToStringHM($valueA[7]),
                 "decl" => "0",//$this->decToString($valueA[8],0),
                 "mag" => $mag,
                 "sb" => $sb,
                 "con" => $con,
                 "diam" => $size,
                 "pa" => $pa, 
                 "type" => $type,
                 "page" => $page,
                 "contrast" => $contrast,
                 "magnification" => $magnifi,
                 "seen" => $valueA[3]
      );


      if(list($key, $valueA) = each($result))
      {
        $mag = round($valueA[5],1);
        if ($mag == 99.9)
        $mag = "";
        else if ($mag - (int)$mag == 0.0)
        $mag = $mag.".0";

        $sb = round($valueA[6],1);
        if ($sb == 99.9)
        $sb = "";
        else if ($sb - (int)$sb == 0.0)
        $sb = $sb.".0";

        $pa = $valueA[20];
        if($pa==999)
        $pa="-";

        $con = $valueA['objectconstellation'];
        $type = $valueA['objecttype'];
        $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
        $page = $valueA[$atlas];
        $diam1 = $valueA[18];
        $diam2 = $valueA[19];
        $size = "";
        if ($diam1 != 0.0)
        if ($diam1 >= 40.0)
        {
          if (round($diam1 / 60.0) == ($diam1 / 60.0))
          if ($diam1 / 60.0 > 30.0)
          $size = sprintf("%.0f'", $diam1 / 60.0);
          else
          $size = sprintf("%.1f'", $diam1 / 60.0);
          else
          $size = sprintf("%.1f'", $diam1 / 60.0);
          if ($diam2 != 0.0)
          if (round($diam2 / 60.0) == ($diam2 / 60.0))
          if ($diam2 / 60.0 > 30.0)
          $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
          else
          $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
          else
          $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
        }
        else
        {
          $size = sprintf("%.1f''", $diam1);
          if ($diam2 != 0.0)
          $size = $size.sprintf("x%.1f''", $diam2);
        }
        $contrast = $valueA[21];
        if ($contrast == "-")
        {
          $magnifi = "-";
        } else {
          $magnifi = (int)$valueA[25];
        }
        $tempB = array("NameB" => $valueA[4],
                   "raB" => "0",//$this->raToStringHM($valueA[7]),
                   "declB" => "0",//$this->decToString($valueA[8],0),
                   "magB" => $mag,
                   "sbB" => $sb,
                   "conB" => $con,
                   "diamB" => $size,
                   "paB" => $pa, 
                   "typeB" => $type,
                   "pageB" => $page,
                   "contrastB" => $contrast,
                   "magnificationB" => $magnifi,
                   "seenB" => $valueA[3]
        );

      }
      else
      {
        $tempB = array("NameB" => '',
                   "raB" => '',
                   "declB" => '',
                   "magB" => '',
                   "sbB" => '',
                   "conB" => '',
                   "diamB" => '',
                   "paB" => '', 
                   "typeB" => '',
                   "pageB" => '',
                   "contrastB" => '',
                   "magnificationB" => '',
                   "seenB" => ''
                   );
      }
      $obs1[] = array_merge($tempA, array(' '), $tempB);
    }

    // Create pdf file
    $pdf = new Cezpdf('a4', 'landscape');
    $pdf->ezStartPageNumbers(450, 15, 10);

    $fontdir = /*realpath*/('../lib/fonts/Helvetica.afm');
    $pdf->selectFont($fontdir);
    $pdf->selectFont('../lib/fonts/Helvetica.afm');

    $pdf->ezTable($obs1,
    array(
								      "seen"           => html_entity_decode(LangOverviewObjectsHeader7),
											"Name"           => html_entity_decode(LangPDFMessage1), 
                      "type"           => html_entity_decode(LangPDFMessage5),
                      "mag"            => html_entity_decode(LangPDFMessage7),
                      "sb"             => html_entity_decode(LangPDFMessage8),
                      "ra"             => html_entity_decode(LangPDFMessage3),
                      "decl"           => html_entity_decode(LangPDFMessage4),
                      "con"            => html_entity_decode(LangPDFMessage6),
                      "diam"           => html_entity_decode(LangPDFMessage9),
                      "pa"             => html_entity_decode(LangPDFMessage16),  
                      "contrast"       => html_entity_decode(LangPDFMessage17),
                      "magnification"  => html_entity_decode(LangPDFMessage18),
                      "page"           => html_entity_decode($page),
                      "separator"      => ' ',
								      "seenB"          => html_entity_decode(LangOverviewObjectsHeader7),
											"NameB"          => html_entity_decode(LangPDFMessage1), 
                      "typeB"          => html_entity_decode(LangPDFMessage5),
                      "magB"           => html_entity_decode(LangPDFMessage7),
                      "sbB"            => html_entity_decode(LangPDFMessage8),
                      "raB"            => html_entity_decode(LangPDFMessage3),
                      "declB"          => html_entity_decode(LangPDFMessage4),
                      "conB"           => html_entity_decode(LangPDFMessage6),
                      "diamB"          => html_entity_decode(LangPDFMessage9),
                      "paB"            => html_entity_decode(LangPDFMessage16),  
                      "contrastB"      => html_entity_decode(LangPDFMessage17),
                      "magnificationB" => html_entity_decode(LangPDFMessage18),
                      "pageB"          => html_entity_decode($page)
    ),
    $_GET['pdfTitle'],

    array("width" => "750",
			                "cols" => array(
											                "seen"           => array('justification'=>'center','width'=>30),
											                "Name"           => array('justification'=>'left',  'width'=>50),
									              		  "type"           => array('justification'=>'left',  'width'=>60),
							              				  "mag"            => array('justification'=>'center','width'=>17),
              											  "sb"             => array('justification'=>'center','width'=>17),
			                                "ra"             => array('justification'=>'center','width'=>32),
		              									  "decl"           => array('justification'=>'center','width'=>25),
              											  "con"            => array('justification'=>'center','width'=>25),
							              			  	"diam"           => array('justification'=>'center','width'=>35),
       											          "pa"             => array('justification'=>'center','width'=>17),
          														"contrast"       => array('justification'=>'center','width'=>17),
          														"magnification"  => array('justification'=>'center','width'=>17),
				              							  "page"           => array('justification'=>'center','width'=>35),
				              							  "separator"      => array('justification'=>'center','width'=>17),
											                "seenB"          => array('justification'=>'center','width'=>30),
											                "NameB"          => array('justification'=>'left',  'width'=>50),
									              		  "typeB"          => array('justification'=>'left',  'width'=>60),
							              				  "magB"           => array('justification'=>'center','width'=>17),
              											  "sbB"            => array('justification'=>'center','width'=>17),
			                                "raB"            => array('justification'=>'center','width'=>32),
		              									  "declB"          => array('justification'=>'center','width'=>25),
              											  "conB"           => array('justification'=>'center','width'=>25),
							              			  	"diamB"          => array('justification'=>'center','width'=>35),
       											          "paB"            => array('justification'=>'center','width'=>17),
          														"contrastB"      => array('justification'=>'center','width'=>17),
          														"magnificationB" => array('justification'=>'center','width'=>17),
				              							  "pageB"          => array('justification'=>'center','width'=>35)
    ),
											"fontSize" => "6",
											"showLines" => "0",
											"showHeadings" => "0",
											"rowGap" => "0",
											"colGap" => "0"				         
											)
											);

											$pdf->ezStream();
  }


  // The opposite of nl2br
  function br2nl($data)
  { return preg_replace( '!<br.*>!iU', " ", $data );
  }

  // Creates a csv file from an array of observations
  function csvObservations($result)
  { print LangCSVMessage3."\n";
    while(list ($key, $value) = each($result))
    { $obs = $GLOBALS['objObservation']->getAllInfoDsObservation($value['observationid']);
      $objectname = $obs["name"];
      $observerid = $obs["observer"];
      $inst = $obs["instrument"];
      $loc = $obs["location"];
      $date = sscanf($obs["date"], "%4d%2d%2d");
      $time = $obs["time"];
      $langObs = $obs["language"];
      $filt = $obs["filter"];
      $eyep = $obs["eyepiece"];
      $lns = $obs["lens"];

      if ($time >= "0")
      {
        $hours = (int)($time / 100);
        $minutes = $time - 100 * $hours;
        $time = sprintf("%d:%02d", $hours, $minutes);
      }
      else
      {
        $time = "";
      }
      $description = $this->br2nl(html_entity_decode($obs["description"]));
      $description = preg_replace("/;/", ",", $description);
      $visibility = $obs["visibility"];
      if ($visibility == "0")
      {
        $visibility = "";
      }
      $name = $GLOBALS['objObserver']->getFirstname($obs["observer"]). " ".$GLOBALS['objObserver']->getObserverName($obs["observer"]);
      $seeing = $GLOBALS['objObservation']->getSeeing($value);
      $limmag = $GLOBALS['objObservation']->getLimitingMagnitude($value);
      $description = preg_replace("/(\r\n|\n|\r)/", "", $description);
      $description = preg_replace("/(\")/", "", $description);
      echo (html_entity_decode($objectname) . ";" . html_entity_decode($name) . ";" . $date[2] . "-" . $date[1] . "-" . $date[0] . ";" . $time . ";" . html_entity_decode($GLOBALS['objLocation']->getLocationName($loc)) . ";" . html_entity_decode($GLOBALS['objInstrument']->getInstrumentName($inst)) . ";" . html_entity_decode($GLOBALS['objEyepiece']->getEyepieceName($eyep)) . ";" . html_entity_decode($GLOBALS['objFilter']->getFilterName($filt)) . ";" . html_entity_decode($GLOBALS['objLens']->getLensName($lns)) . ";" . $seeing . ";" . $limmag . ";" . $visibility . ";" . $langObs . ";" . $description . "\n");
    }
  }

  // Creates a csv file from an array of objects
  function csvObjects($result)
  { print html_entity_decode(LangCSVMessage7)."\n";

    while(list ($key, $valueA) = each($result))
    { $alt="";
      $alts = $GLOBALS['objObject']->getAlternativeNames($valueA['objectname']);
      $first = true;
      while(list($key,$value)=each($alts))
      if($value!=$valueA['objectname'])
      if ($first)
      {
        $alt = $value;
        $first = false;
      } else
      {
  		    $alt = $alt . " - " . $value;
      }
      $mag = $valueA[5];
      if ($mag == 99.9)
      $mag = "";
      else if ($mag - (int)$mag == 0.0)
      $mag = $mag.".0";
      $sb = $valueA[6];
      if ($sb == 99.9)
      $sb = "";
      else if ($sb - (int)$sb == 0.0)
      $sb = $sb.".0";
      $con = $valueA['objectconstellation'];
      $pa = $valueA[20];
      if($pa==999)
      $pa="";
      $type = $valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $diam1 = $valueA[18];
      $diam2 = $valueA[19];
      $size = "";
      if ($diam1 != 0.0)
      if ($diam1 >= 40.0)
      {
        if (round($diam1 / 60.0) == ($diam1 / 60.0))
        if ($diam1 / 60.0 > 30.0)
        $size = sprintf("%.0f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        else
        $size = sprintf("%.1f'", $diam1 / 60.0);
        if ($diam2 != 0.0)
        if (round($diam2 / 60.0) == ($diam2 / 60.0))
        if ($diam2 / 60.0 > 30.0)
        $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
        else
        $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
      }
      else
      {
        $size = sprintf("%.1f''", $diam1);
        if ($diam2 != 0.0)
        $size = $size.sprintf("x%.1f''", $diam2);
      }

      if ($valueA[21] == "-")
      {
        $magnifi = "-";
      } else {
        $magnifi = (int)$valueA[25];
      }

      echo $valueA['objectname'].";". $alt .";".raToString($valueA[7]).";".decToString($valueA[8], 0).";".$GLOBALS[$con].";".$GLOBALS[$type].";".$mag.";".$sb.";".$size.";".$pa.";".$page.";".$valueA[21].";".$magnifi.";".$valueA[3].";".$valueA[28]."\n";
    }
  }

  // Creates an argo navis file from an array of objects
  function argoObjects($result)
  { $counter = 0;
    while(list ($key, $valueA) = each($result))
    { $mag = $valueA['objectmagnitude'];
      if ($mag == 99.9)
        $mag = "";
      else if ($mag - (int)$mag == 0.0)
        $mag = $mag.".0";
      $sb = $valueA[6];
      if ($sb == 99.9)
        $sb = "";
      else if ($sb - (int)$sb == 0.0)
        $sb = $sb.".0";
      $con = $valueA['objectconstellation'];
      $argotype = "argo".$valueA['objecttype'];
      $atlas = $GLOBALS['objObserver']->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $size = "";
			
      $diam1 = $valueA['objectdiam1'];
      $diam2 = $valueA['objectdiam2'];
      if ($diam1!=0.0)
        if ($diam1>=40.0)
        { if (round($diam1 / 60.0) == ($diam1 / 60.0))
            if ($diam1 / 60.0 > 30.0)
              $size = sprintf("%.0f'", $diam1 / 60.0);
            else
              $size = sprintf("%.1f'", $diam1 / 60.0);
          else
            $size = sprintf("%.1f'", $diam1 / 60.0);
          if ($diam2 != 0.0)
            if (round($diam2 / 60.0) == ($diam2 / 60.0))
              if ($diam2 / 60.0 > 30.0)
                $size = $size.sprintf("x%.0f'", $diam2 / 60.0);
              else
                $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
            else
              $size = $size.sprintf("x%.1f'", $diam2 / 60.0);
        }
        else
        { $size = sprintf("%.1f''", $diam1);
          if ($diam2 != 0.0)
            $size = $size.sprintf("x%.1f''", $diam2);
        }
      echo "DSL " . sprintf("%03d", $counter) . " " . $valueA['objectname']."|".raArgoToString($valueA['objectra'])."|".decToArgoString($valueA['objectdecl'], 0)."|".$GLOBALS[$argotype]."|".$mag."|".$size.";".$atlas." ".$page.";CR ".$valueA['objectconstrast'].";".$valueA['objectseen'].";".$valueA['seendate']."\n";
      $counter++;
    }
  }

  // Creates a pdf document from an array of observations
  function pdfObservations($result)
  { global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
    $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
    $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL;

    global $ASTER,$BRTNB,$CLANB,$DRKNB,$GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,
    $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$QUASR,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

    global $EMINB,$REFNB,$ENRNN,$ENSTR,$HII,$RNHII,$STNEB,$WRNEB;

    global $deepskylive, $dateformat;

    // Create pdf file
    $pdf = new Cezpdf('a4', 'portrait');
    $pdf->ezStartPageNumbers(300, 30, 10);

    $fontdir = realpath('../lib/fonts/Helvetica.afm');
    //$pdf->selectFont($fontdir);
    $pdf->selectFont('../lib/fonts/Helvetica.afm');
    $pdf->ezText(LangPDFTitle2."\n");

    while(list ($key, $value) = each($result))
    { $obs = $GLOBALS['objObservation']->getAllInfoDsObservation($value['observationid']);
      $objectname = $obs["name"];
      $object = $GLOBALS['objObject']->getAllInfoDsObject($objectname);
      $type = $object["type"];
      $con = $object["con"];
      $observerid = $obs["observer"];
      $inst = $obs["instrument"];
      $loc = $obs["location"];
      $visibility = $obs["visibility"];
      $seeing = $obs["seeing"];
      $limmag = $obs["limmag"];
      $filt = $obs["filter"];
      $eyep = $obs["eyepiece"];
      $lns = $obs["lens"];

      if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'] && ($GLOBALS['objObserver']->getUseLocal($_SESSION['deepskylog_id'])))
      {
        $date = sscanf($obs["localdate"], "%4d%2d%2d");
      }
      else
      {
        $date = sscanf($obs["date"], "%4d%2d%2d");
      }

      $description = $this->br2nl(html_entity_decode($obs["description"]));

      $formattedDate = date($dateformat, mktime(0,0,0,$date[1],$date[2],$date[0]));

      if ($seeing == 1)
      {
        $seeingstr = SeeingExcellent;
      }
      elseif ($seeing == 2)
      {
        $seeingstr = SeeingGood;
      }
      elseif ($seeing == 3)
      {
        $seeingstr = SeeingModerate;
      }
      elseif ($seeing == 4)
      {
        $seeingstr = SeeingPoor;
      }
      elseif ($seeing == 5)
      {
        $seeingstr = SeeingBad;
      }
      $visstr="";
      if ($visibility == 1)
      {
        $visstr = LangVisibility1;
      }
      elseif ($visibility == 2)
      {
        $visstr = LangVisibility2;
      }
      elseif ($visibility == 3)
      {
        $visstr = LangVisibility3;
      }
      elseif ($visibility == 4)
      {
        $visstr = LangVisibility4;
      }
      elseif ($visibility == 5)
      {
        $visstr = LangVisibility5;
      }
      elseif ($visibility == 6)
      {
        $visstr = LangVisibility6;
      }
      elseif ($visibility == 7)
      {
        $visstr = LangVisibility7;
      }

      if ($seeing != "")
      {
        $sstr = LangViewObservationField6." : ".$seeingstr;
      }
      else
      {
        $sstr = "";
      }

      if ($limmag != "")
      {
        $lstr = LangViewObservationField7." : ".$limmag;
      }
      else
      {
        $lstr = "";
      }
       
      $filtstr="";
      $eyepstr="";
      $lnsstr="";

      if ($filt > 0)
      {
        $filtername = $GLOBALS['objFilter']->getFilterName($filt);
        $filtstr = LangViewObservationField31. " : " . $filtername;
      }

      if ($eyep > 0)
      {
        $eyepiecename = $GLOBALS['objEyepiece']->getEyepieceName($eyep);
        $eyepstr = LangViewObservationField30. " : " . $eyepiecename;
      }

      if ($lns > 0)
      {
        $lensname = $GLOBALS['objLens']->getLensName($lns);
        $lnsstr = LangViewObservationField32 . " : " . $lensname;
      }

      $temp = array("Name" => html_entity_decode(LangPDFMessage1)." : ".$objectname,
                 "altname" => html_entity_decode(LangPDFMessage2)." : ".$object["altname"],
                 "type" => $$type.html_entity_decode(LangPDFMessage12).$$con,
                 "visibility" => html_entity_decode(LangViewObservationField22)." : ".$visstr,
                 "seeing" => $sstr,
                 "limmag" => $lstr, 
                 "filter" => $filtstr,
                 "eyepiece" => $eyepstr,
								 "lens" => $lnsstr,
                 "observer" => html_entity_decode(LangPDFMessage13).$GLOBALS['objObserver']->getFirstName($observerid)." ".$GLOBALS['objObserver']->getObserverName($observerid).html_entity_decode(LangPDFMessage14).$formattedDate,
                 "instrument" => html_entity_decode(LangPDFMessage11)." : ".$GLOBALS['objInstrument']->getInstrumentName($inst),
                 "location" => html_entity_decode(LangPDFMessage10)." : ".$GLOBALS['objLocation']->getLocationName($loc),
                 "description" => $description,
                 "desc" => html_entity_decode(LangPDFMessage15)
      );
      $obs1[] = $temp;

      $nm = $objectname;
      if ($object["altname"] != "")
      {
        $nm = $nm." (".$object["altname"].")";
      }

      $pdf->ezText($nm, "14");

      $tmp=array(array("type"=>$temp["type"]));
      $pdf->ezTable($tmp,array("type" => html_entity_decode(LangPDFMessage5)),"", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));

      $tmp=array(array("location"=>$temp["location"], "instrument"=>$temp["instrument"]));
      $pdf->ezTable($tmp, array("location" => html_entity_decode(LangPDFMessage1), "instrument" => html_entity_decode(LangPDFMessage2)), "",  array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));

      if ($eyep > 0)
      {
        $tmp=array(array("eyepiece"=>$temp["eyepiece"]));
        $pdf->ezTable($tmp, array("eyepiece" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      }
      if ($filt > 0)
      {
        $tmp=array(array("filter"=>$temp["filter"]));
        $pdf->ezTable($tmp, array("filter" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      }
      if ($lns > 0)
      {
        $tmp=array(array("lens"=>$temp["lens"]));
        $pdf->ezTable($tmp, array("lens" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      }
      if ($seeing != "")
      {
        $tmp=array(array("seeing"=>$temp["seeing"]));
        $pdf->ezTable($tmp, array("seeing" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      }

      if ($limmag != "")
      {
        $tmp=array(array("limmag"=>$temp["limmag"]));
        $pdf->ezTable($tmp, array("limmag" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      }

      if ($visibility != "0")
      {
        $tmp=array(array("visibility"=>$temp["visibility"]));
        $pdf->ezTable($tmp, array("visibility" => "test"), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));
      }

      $tmp=array(array("observer"=>$temp["observer"]));
      $pdf->ezTable($tmp, array("observer" => html_entity_decode(LangPDFMessage1)), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));

      //   $pdf->ezText(LangPDFMessage15, "12");
      //   $pdf->ezTable($obs1,
      //         array("desc" => LangPDFMessage1), "",
      //               array("width" => "500", "showHeadings" => "0",
      //                     "showLines" => "0", "shaded" => "0", "fontSize" => "12"));
      $pdf->ezText("");

      $tmp=array(array("description"=>$temp["description"]));
      $pdf->ezTable($tmp, array("description" => html_entity_decode(LangPDFMessage1)), "", array("width" => "500", "showHeadings" => "0", "showLines" => "0", "shaded" => "0"));

      $upload_dir = 'drawings';
      $dir = opendir($upload_dir);

      while (FALSE !== ($file = readdir($dir)))
      {
        if ("." == $file OR ".." == $file)
        {
          continue; // skip current directory and directory above
        }
        if(fnmatch($value . ".gif", $file) ||
        fnmatch($value . ".jpg", $file) ||
        fnmatch($value. ".png", $file))
        {
          $pdf->ezText("");
          $pdf->ezImage($upload_dir . "/" . $value . ".jpg", 0, 500, "none", "left");
        }
      }


      $obs1 = array("");
      $temp = array("");

      $pdf->ezText("");
    }
    $pdf->ezStream();
  }

  // Creates a pdf document from an array of comet observations
  function pdfCometObservations($result)
  { include_once "cometobjects.php";
    include_once "observers.php";
    include_once "instruments.php";
    include_once "locations.php";
    include_once "cometobservations.php";
    include_once "ICQMETHOD.php";
    include_once "ICQREFERENCEKEY.php";
    include "setup/vars.php";
    include "setup/databaseInfo.php";

    $objects = new CometObjects;
    $observer = new Observers;
    $instrument = new Instruments;
    $observation = new CometObservations;
    $location = new Locations;
    $util = new Util;
    $ICQMETHODS = new ICQMETHOD();
    $ICQREFERENCEKEYS = new ICQREFERENCEKEY();

    // Create pdf file
    $pdf = new Cezpdf('a4', 'portrait');
    $pdf->ezStartPageNumbers(300, 30, 10);

    $fontdir = realpath('../lib/fonts/Helvetica.afm');
    $pdf->selectFont($fontdir);
    $pdf->ezText(html_entity_decode(LangPDFTitle3)."\n");

    while(list ($key, $value) = each($result))
    {
      $objectname = $objects->getName($observation->getId($value));

      $pdf->ezText($objectname, "14");

      $observerid = $observation->getObserverid($value);

      if ($observer->getUseLocal($_SESSION['deepskylog_id']))
      {
        $date = sscanf($observation->getLocalDate($value), "%4d%2d%2d");
        $time = $observation->getLocalTime($value);
      }
      else
      {
        $date = sscanf($observation->getDate($value), "%4d%2d%2d");
        $time = $observation->getTime($value);
      }

      $hour = (int)($time / 100);
      $minute = $time - $hour * 100;
      $formattedDate = date($dateformat, mktime(0,0,0,$date[1],$date[2],$date[0]));

      if ($minute < 10)
      {
        $minute = "0".$minute;
      }

      $observername = LangPDFMessage13.$observer->getFirstName($observerid)." ".$observer->getObserverName($observerid).html_entity_decode(LangPDFMessage14).$formattedDate." (".$hour.":".$minute.")";

       
      $pdf->ezText($observername, "12");


      // Location and instrument
      if (($observation->getLocationId($value) != 0 && $observation->getLocationId($value) != 1) || $observation->getInstrumentId($value) != 0)
      {
        if ($observation->getLocationId($value) != 0 && $observation->getLocationId($value) != 1)
        {
          $locationname = LangPDFMessage10." : ".$location->getLocationName($observation->getLocationId($value));
          $extra = ", ";
        }
        else
        {
          $locationname = "";
        }

        if ($observation->getInstrumentId($value) != 0)
        {
          $instr = $instrument->getInstrumentName($observation->getInstrumentId($value));
          if ($instr == "Naked eye")
          {
            $instr = InstrumentsNakedEye;
          }

          $locationname = $locationname.$extra.html_entity_decode(LangPDFMessage11)." : ".$instr;

          if (strcmp($observation->getMagnification($value), "") != 0)
          {
            $locationname = $locationname." (".$observation->getMagnification($value)." x)";
          }
        }

        $pdf->ezText($locationname, "12");
      }

      // Methode
      $method = $observation->getMethode($value);

      if (strcmp($method, "") != 0)
      {
        $methodstr = html_entity_decode(LangViewObservationField15)." : ".$method." - ".$ICQMETHODS->getDescription($method);

        $pdf->ezText($methodstr, "12");
      }

      // Used chart
      $chart = $observation->getChart($value);

      if (strcmp($chart, "") != 0)
      {
        $chartstr = html_entity_decode(LangViewObservationField17)." : ".$chart." - ".$ICQREFERENCEKEYS->getDescription($chart);

        $pdf->ezText($chartstr, "12");
      }

      // Magnitude
      $magnitude = $observation->getMagnitude($value);

      if ($magnitude != -99.9)
      {
        $magstr = "";

        if ($observation->getMagnitudeWeakerThan($value))
        {
          $magstr = $magstr.LangNewComet3." ";
        }
        $magstr = $magstr.html_entity_decode(LangViewObservationField16)." : ".sprintf("%.01f", $magnitude);

        if ($observation->getMagnitudeUncertain($value))
        {
          $magstr = $magstr." (".LangNewComet2.")";
        }

        $pdf->ezText($magstr, "12");
      }
       
      // Degree of condensation
      $dc = $observation->getDc($value);
      $coma = $observation->getComa($value);

      $dcstr = "";
      $extra = "";

      if (strcmp($dc, "") != 0 || $coma != -99)
      {
        if (strcmp($dc, "") != 0)
        {
          $dcstr = $dcstr.html_entity_decode(LangNewComet8)." : ".$dc;
          $extra = ", ";
        }

        // Coma

        if ($coma != -99)
        {
          $dcstr = $dcstr.$extra.html_entity_decode(LangNewComet9)." : ".$coma."'";
        }

        $pdf->ezText($dcstr, "12");
      }

      // Tail
      $tail = $observation->getTail($value);
      $pa = $observation->getPa($value);

      $tailstr = "";
      $extra = "";

      if ($tail != -99 || $pa != -99)
      {
        if ($tail != -99)
        {
          $tailstr = $tailstr.html_entity_decode(LangNewComet10)." : ".$tail."'";
          $extra = ", ";
        }

        if ($pa != -99)
        {
          $tailstr = $tailstr.$extra.html_entity_decode(LangNewComet11)." : ".$pa."";
        }

        $pdf->ezText($tailstr, "12");
      }

      // Description
      $description = $observation->getDescriptionDsObservation($value);

      if (strcmp($description, "") != 0)
      {
        $descstr = html_entity_decode(LangPDFMessage15)." : ".strip_tags($description);
        $pdf->ezText($descstr, "12");
      }


      $upload_dir = 'cometdrawings';
      $dir = opendir($upload_dir);

      while (FALSE !== ($file = readdir($dir)))
      {
        if ("." == $file OR ".." == $file)
        {
          continue; // skip current directory and directory above
        }
        if(fnmatch($value . ".gif", $file) ||
        fnmatch($value . ".jpg", $file) ||
        fnmatch($_value. ".png", $file))
        {
          $pdf->ezImage($upload_dir . "/" . $value . ".jpg", 0, 500, "none", "left");
        }
      }

      $pdf->ezText("");
    }

    $pdf->ezStream();
  }
  public function utiltiesDispatchIndexActionDS()
  { if(!($indexActionInclude=$this->utilitiesCheckIndexActionAdmin('manage_csv_object','content/manage_objects_csv.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_observation','content/change_observation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_observation','content/change_observation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_csv','content/new_observationcsv.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('import_csv_list','content/new_listdatacsv.php')))  
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_object','content/new_object.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_observation','content/new_observation.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('detail_object','content/view_object.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('detail_observation','content/view_observation.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('rank_observers','content/top_observers.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('result_query_objects','content/execute_query_objects.php'))) 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('result_selected_observations','content/selected_observations2.php')))  
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('query_observations','content/setup_observations_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('query_objects','content/setup_objects_query.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('rank_objects','content/top_objects.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('view_image','content/show_image.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('listaction','content/tolist.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('view_observer_catalog','content/details_observer_catalog.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionDSquickPick()))
      $indexActionInclude=$this->utilitiesGetIndexActionDSdefaultAction();
    return $indexActionInclude;
  }
	public function utiltiesDispatchIndexActionCommon()
  { if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('account_details','content/change_account.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_eyepiece','content/change_eyepiece.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_filter','content/change_filter.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_instrument','content/change_instrument.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_lens','content/change_lens.php')))	  
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('adapt_site','content/change_site.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_eyepiece','content/new_eyepiece.php')))		 
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_filter','content/new_filter.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_instrument','content/new_instrument.php'))) 		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_lens','content/new_lens.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('add_site','content/new_site.php'))) 		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('confirm_subscribe','content/confirm.php'))) 		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_eyepiece','content/view_eyepiece.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_filter','content/view_filter.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_instrument','content/view_instrument.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_lens','content/view_lens.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_location','content/view_location.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('detail_observer','content/view_observer.php')))		
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('search_sites','content/search_locations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('site_result','content/getLocation.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionAll('subscribe','content/register.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_eyepieces','content/overview_eyepieces.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_filters','content/overview_filters.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_instruments','content/overview_instruments.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_lenses','content/overview_lenses.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_locations','content/overview_locations.php')))
    if(!($indexActionInclude=$this->utilitiesCheckIndexActionMember('view_observers','content/overview_observers.php')))
      $indexActionInclude=$this->utilitiesGetIndexActionCommonDefaultAction();
    return $indexActionInclude;
  }
  public function utilitiesSetModuleCookie($module)
  { if((!array_key_exists('module',$_SESSION)) ||
     (array_key_exists('module',$_SESSION) && ($_SESSION['module'] != $module)))
    { $_SESSION['module'] = $module;
      $cookietime = time() + 365 * 24 * 60 * 60;     // 1 year
      setcookie("module",$module, $cookietime, "/");
    }
  }
	public function checkGetKey($key,$default='')
  { return (array_key_exists($key,$_GET)&&$_GET[$key])?$_GET[$key]:$default;
  }
	public function checkArrayKey($theArray,$key,$default='')
  { return (array_key_exists($key,$theArray)&&$theArray[$key])?$theArray[$key]:$default;
  }
	public function checkPostKey($key,$default='')
  { return (array_key_exists($key,$_POST)&&$_POST[$key])?$_POST[$key]:$default;
  }
	public function checkGetKeyReturnString($key,$string,$default='')
  { return array_key_exists($key,$_GET)?$string:$default;
  }
  public function checkGetDate($year,$month,$day)
  { if($year=$this->checkGetKey($year))
      return $year.$this->checkGetKey($month,'00').$this->checkGetKey($day,'00');
    elseif($month=$this->checkGetKey($month))
      return $month.$this->checkGetKey($day,'00');
    else
  	  return '';
  }
  public function checkGetTimeOrDegrees($hr,$min,$sec)
  { if($this->checkGetKey($hr).$this->checkGetKey($min).$this->checkGetKey($sec))
      if(substr($this->checkGetKey($hr),0,1)=="-")
	      return -(abs($this->checkGetKey($hr,0))+($this->checkGetKey($min,0)/60)+($this->checkGetKey($sec,0)/3600));
			else
	      return $this->checkGetKey($hr,0)+($this->checkGetKey($min,0)/60)+($this->checkGetKey($sec,0)/3600);
  }
  public function promptWithLink($prompt,$promptDefault,$javaLink,$text)
	{ echo "<a href=\"\" onclick=\"thetitle = prompt(".$prompt.",".$promptDefault."); location.href='".$javaLink."&amp;pdfTitle='+thetitle+''; return false;\"	target=\"new_window\">".$text."</a>";
  }
}
$objUtil=new Util();
?>
