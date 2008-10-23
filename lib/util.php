<?php

// The util class is a collection of usefull functions, mostly needed for the
// user interface.
//
// Version 0.5 : 21/08/2005, WDM
// version 3.1, DE 20061119
//
// $$ ok

if (!function_exists('fnmatch'))
{
  function fnmatch($pattern, $string)
  {
    return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
  }
}


include_once "setup/vars.php";
include_once "class.ezpdf.php";

class util
{
  // printListHeader prints the list header of $list if the list has more than
  // $step entries. The first item from the list that should be shown is $min.
  // All numbers use the given link. An array is given back, with the min and
  // max value. Example :
  // list($min, $max) = $util->printListHeader($obs, $link, $_GET['min'], 25, 1221);
  function printListHeader($list, $link, $min, $step, $total)
  {
    $pages = ceil(count($list) / $step); // total number of pages
    if($min) // minimum value
    {
      $min = $min - ($min % $step); // start display from number of $steps
      if ($min < 0)  // minimum value smaller than 0
      {
        $min = 0;
      }
      if ($min > count($list)) // minimum value bigger than number of elements
      {
        $min = count($list) - (count($list) % $step);
      }
    }
    else // no minimum value defined
    {
      $min = 0;
    }

    $max = $min + $step; // maximum number to be displayed

    if(count($list) > $step)
    {
      $currentpage = ceil($min / $step) + 1;
      echo("<p>\n");
      echo("<a href=\"".$link."&amp;min=0\">");
      echo LangOverviewObjectsFirstlink; // link to first page
      echo("</a>&nbsp;&nbsp;&nbsp;\n");

      if ($currentpage <= 7)
      {
        $start = -$currentpage + 1;
      }
      else if ($currentpage >= $pages - 7)
      {
        $start = -14 + ($pages - $currentpage);
      }
      else
      {
        $start = -7;
      }

      for ($i = $start; $i <= $start + 14; $i++)
      {
        $pagenumber = ($min + ($step * $i));
        if((($pagenumber/$step) >= 0) && (($pagenumber/$step) < $pages))
        {
          if($i != 0) // not current page
          {
            echo("<a href=\"".$link."&amp;min=" . $pagenumber . "\">" . ($pagenumber/$step + 1) . "</a>&nbsp;"); // link to other page
          }
          else
          {
            echo(($pagenumber/$step + 1) . "&nbsp;"); // current page
          }
        }
      }
      echo("&nbsp;&nbsp;<a href=\"".$link."&amp;min=".(($pages*$step) - 1) . "\">");
      echo LangOverviewObjectsLastlink; // link to last page
      echo("</a>\n");
      if ($total == "")
      {
        echo("&nbsp;&nbsp;(" . count($list) . "&nbsp;" . LangNumberOfRecords . ")");
      }
      else
      {
        echo("&nbsp;&nbsp;(" . count($list) . "&nbsp;" . LangNumberOfRecords . " / " . $total . ")");
      }
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
      $_POST[$foo] = htmlentities($bar, ENT_COMPAT, "ISO-8859-15");
    }
    foreach($_GET as $foo => $bar)
    {
      $_GET[$foo] = htmlentities($bar, ENT_COMPAT, "ISO-8859-15");
    }
  }

  // raToString converts ra to a String representation
  function raToString($ra)
  {
    $ra_hours = floor($ra);
    $subminutes = 60 * ($ra - $ra_hours);
    $ra_minutes = floor($subminutes);
    $ra_seconds = round(60 * ($subminutes - $ra_minutes));

    if($ra_seconds == 60)
    {
      $ra_seconds = 0;
      $ra_minutes++;
    }
    if($ra_minutes == 60)
    {
      $ra_minutes = 0;
      $ra_hours++;
    }
    if($ra_hours == 24)
    {
      $ra_hours = 0;
    }

    if($ra_hours <= 9)
    {
      $ra_hours = "0" . $ra_hours;
    }
    if($ra_minutes <= 9)
    {
      $ra_minutes = "0" . $ra_minutes;
    }
    if($ra_seconds <= 9)
    {
      $ra_seconds = "0" . $ra_seconds;
    }

    return("$ra_hours" . "h" . "$ra_minutes" . "m" . "$ra_seconds" . "s");
  }

  // raToString converts ra to a String representation
  function raToStringHM($ra)
  {
    $ra_hours = floor($ra);
    $subminutes = 60 * ($ra - $ra_hours);
    $ra_minutes = floor($subminutes);
    $ra_seconds = round(60 * ($subminutes - $ra_minutes));

    if($ra_seconds >= 30)
    $ra_minutes++;
    if($ra_minutes == 60)
    {
      $ra_minutes = 0;
      $ra_hours++;
    }
    if($ra_hours == 24)
    $ra_hours = 0;

    if($ra_hours <= 9)
    {
      $ra_hours = "0" . $ra_hours;
    }

    return("$ra_hours" . "h" . "$ra_minutes" . "m");
  }
  // raArgoToString converts ra to a String representation
  function raArgoToString($ra)
  {
    $ra_hours = floor($ra);
    $subminutes = 60 * ($ra - $ra_hours);
    $ra_minutes = floor($subminutes);
    $ra_seconds = round(60 * ($subminutes - $ra_minutes));

    if($ra_seconds == 60)
    {
      $ra_seconds = 0;
      $ra_minutes++;
    }
    if($ra_minutes == 60)
    {
      $ra_minutes = 0;
      $ra_hours++;
    }
    if($ra_hours == 24)
    {
      $ra_hours = 0;
    }

    if($ra_hours <= 9)
    {
      $ra_hours = "0" . $ra_hours;
    }
    if($ra_minutes <= 9)
    {
      $ra_minutes = "0" . $ra_minutes;
    }
    if($ra_seconds <= 9)
    {
      $ra_seconds = "0" . $ra_seconds;
    }

    return("$ra_hours" . ":" . "$ra_minutes" . ":" . "$ra_seconds");
  }

  // decToString converts dec to a String representation
  function decToString($decl, $web = 1)
  {
    $sign =0;
    if($decl < 0)
    {
      $sign = -1;
      $decl = -$decl;
    }
    $decl_degrees = floor($decl);
    $subminutes = 60 * ($decl - $decl_degrees);
    $decl_minutes = round($subminutes);

    if($decl_minutes == 60)
    {
      $decl_minutes = 0;
      $decl_degrees++;
    }

    if($decl_degrees >= 0 && $decl_degrees <= 9)
    {
      $decl_degrees = "0" . $decl_degrees;
    }

    if ($sign == -1)
    {
      $decl_degrees = "-" . $decl_degrees;
    }
    else
    {
      if ($web == 1)
      {
        //$decl_degrees = "&nbsp;" . $decl_degrees; // add white space for overview locations
        $decl_degrees = $decl_degrees; // remove white space for object details
      }
      else
      {
        $decl_degrees = " " . $decl_degrees;
      }
    }

    if($decl_minutes <= 9)
    {
      $decl_minutes = "0" . $decl_minutes;
    }

    if ($web == 1)
    {
      $d = "&deg;";
      $m = "&#39;";
    }
    else
    {
      $d = "d";
      $m = "'";
    }
    return("$decl_degrees" .$d. "$decl_minutes" . $m);
  }

  // decToArgoString converts dec to a String representation
  function decToArgoString($decl)
  {
    $sign =0;
    if($decl < 0)
    {
      $sign = -1;
      $decl = -$decl;
    }
    $decl_degrees = floor($decl);
    $subminutes = 60 * ($decl - $decl_degrees);
    //  $decl_minutes = round($subminutes);
    $decl_minutes = floor($subminutes);
    $subseconds = round(60 * ($subminutes - $decl_minutes));

    if($subseconds == 60)
    {
      $subseconds = 0;
      $decl_minutes++;
    }

    if($decl_minutes == 60)
    {
      $decl_minutes = 0;
      $decl_degrees++;
    }

    if($decl_degrees >= 0 && $decl_degrees <= 9)
    {
      $decl_degrees = "0" . $decl_degrees;
    }

    if ($sign == -1)
    {
      $decl_degrees = "-" . $decl_degrees;
    }
    else
    {
      $decl_degrees = "+" . $decl_degrees;
    }

    if($decl_minutes <= 9)
    {
      $decl_minutes = "0" . $decl_minutes;
    }

    return("$decl_degrees" . ":" . "$decl_minutes" . ":" . sprintf("%02d", $subseconds));
  }

  // Creates a pdf document from an array of objects
  function pdfObjects($result)
  {
    include_once "observers.php";
    $observer = new Observers;

    global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
    $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
    $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL;

    global $ASTER,$BRTNB,$CLANB,$DRKNB,$GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,
    $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$QUASR,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

    global $EMINB,$REFNB,$ENRNN,$ENSTR,$HII,$RNHII,$STNEB,$WRNEB;

    global $deepskylive, $dateformat;
		
		include_once "../lib/atlasses.php";
		$atlas = new Atlasses;
		$atlasses = $atlas->getSortedAtlasses();

    while(list ($key, $valueA) = each($result))
    {
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

      $pa = $valueA[20];
      if($pa==999)
      $pa="-";

      $con = $valueA[2];
      $type = $valueA[1];
      $atlas = $observer->getStandardAtlasCode($_SESSION['deepskylog_id']);
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
                 "ra" => $this->raToString($valueA[7]),
                 "decl" => $this->decToString($valueA[8], 0),
                 "mag" => $mag,
                 "sb" => $sb,
                 "con" => $$con,
                 "diam" => $size,
                 "pa" => $pa, 
                 "type" => $$type,
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
  { if($sort!='con') $sort='';
    include_once "observers.php";
    $observer = new Observers;
    include_once "instruments.php";
    $instrument = new Instruments;
    include_once "../lib/locations.php";
    $location=new Locations;

    global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
    $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
    $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL;

    global $ASTER,$BRTNB,$CLANB,$DRKNB,$GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,
    $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$QUASR,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

    global $EMINB,$REFNB,$ENRNN,$ENSTR,$HII,$RNHII,$STNEB,$WRNEB;

    global $deepskylive, $dateformat;
		
		global $baseURL, $dbname;
		
		include_once "atlasses.php";
		$atlas = new Atlasses;
		$atlasses = $atlas->getSortedAtlasses();

    // Create pdf file
    $pdf = new Cezpdf('a4', 'landscape');
    $fontdir = ('../lib/fonts/Helvetica.afm');
    $pdf->selectFont('../lib/fonts/Helvetica.afm');

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
		    LangPDFMessage19 . $observer->getFirstName($_SESSION['deepskylog_id']) . ' ' . 
				                   $observer->getObserverName($_SESSION['deepskylog_id']) . ' ' .
		    LangPDFMessage20 . $instrument->getInstrumentName($observer->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
				LangPDFMessage21 . $location->getLocationName($observer->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
		$pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, $_GET['pdfTitle'], 'center' );
		$pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . '1', 'right');
		while(list($key, $valueA) = each($result))
    {
			$mag = round($valueA[5],1); if ($mag == 99.9) $mag = ""; else if ($mag - (int)$mag == 0.0) $mag = $mag.".0";
      $sb = round($valueA[6],1);  if ($sb == 99.9)  $sb = "";  else if ($sb - (int)$sb == 0.0)   $sb = $sb.".0";
      $pa = $valueA[20];          if($pa==999)      $pa="-";
			
      $con = $valueA[2];
      $type = $valueA[1];
      $atlas = $observer->getStandardAtlasCode($_SESSION['deepskylog_id']);
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
		                   LangPDFMessage19 . $observer->getObserverName($_SESSION['deepskylog_id']) . ' ' . 
		                                      $observer->getFirstName($_SESSION['deepskylog_id']) . ' ' .
                       LangPDFMessage20 . $instrument->getInstrumentName($observer->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
				               LangPDFMessage21 . $location->getLocationName($observer->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
		          $pdf->addTextWrap($xleft, $header, $xmid+$SectionBarWidth, 10, $_GET['pdfTitle'], 'center' );
		          $pdf->addTextWrap($xmid+$SectionBarWidth-$sectionBarSpace-100, $header, 100, 8, LangPDFMessage22 . $pagenrv, 'right');
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
          $pdf->addText($xbase, $y, $fontSizeSection, $$$sort);  
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
	                   LangPDFMessage19 . $observer->getObserverName($_SESSION['deepskylog_id']) . ' ' .
	                                      $observer->getFirstName($_SESSION['deepskylog_id']) . ' ' .
                     LangPDFMessage20 . $instrument->getInstrumentName($observer->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
			               LangPDFMessage21 . $location->getLocationName($observer->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
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
			if(!$sort)
			{ $pdf->addTextWrap($xbase    , $y,  30, $fontSizeText, $valueA[3]);			                   // seen
			  $pdf->addTextWrap($xbase+ 30, $y,  40, $fontSizeText, $valueA[28]);		                     // last seen	
			  $pdf->addTextWrap($xbase+ 70, $y,  85, $fontSizeText, '<b>'.
				  '<c:alink:'.$baseURL.'deepsky/index.php?indexAction=detail_object&amp;object='.
					urlencode($valueA[0]).'>'.$valueA[4]);		               //	object
			  $pdf->addTextWrap($xbase+150, $y,  30, $fontSizeText, '</c:alink></b>'.$type);			                 // type
			  $pdf->addTextWrap($xbase+180, $y,  20, $fontSizeText, $con);			                         // constellation
			  $pdf->addTextWrap($xbase+200, $y,  17, $fontSizeText, $mag, 'left');  	                 // mag
			  $pdf->addTextWrap($xbase+217, $y,  18, $fontSizeText, $sb, 'left');		                   // sb
			  $pdf->addTextWrap($xbase+235, $y,  60, $fontSizeText, $this->raToStringHM($valueA[7]) . ' '.
				                                                      $this->decToString($valueA[8],0));	 // ra - decl
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
					urlencode($valueA[0]).'>'.$valueA[4]);		                                       //	object
			  $pdf->addTextWrap($xbase+170, $y,  30, $fontSizeText, '</c:alink></b>'.$type);			                 // type
			  $pdf->addTextWrap($xbase+200, $y,  17, $fontSizeText, $mag, 'left');			                 // mag
			  $pdf->addTextWrap($xbase+217, $y,  18, $fontSizeText, $sb, 'left');			                   // sb
			  $pdf->addTextWrap($xbase+235, $y,  60, $fontSizeText, $this->raToStringHM($valueA[7]) . ' '.
				                                                      $this->decToString($valueA[8],0));	 // ra - decl
			  $pdf->addTextWrap($xbase+295, $y,  55, $fontSizeText, $size . '/' . $pa);         			   // size
	  		$pdf->addTextWrap($xbase+351, $y,  17, $fontSizeText, $contrast, 'left');			             // contrast				
	  		$pdf->addTextWrap($xbase+368, $y,  17, $fontSizeText, $magnifi, 'left');		               // magnification				
			  $pdf->addTextWrap($xbase+380, $y,  20, $fontSizeText, '<b>'.$page.'</b>', 'right');			   // atlas page
      }
			$y-=$deltaline;
      if($sort)
			  $actualsort = $$sort;
			if($valueA[30])
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
		                   LangPDFMessage19 . $observer->getObserverName($_SESSION['deepskylog_id']) . ' ' . 
		                                      $observer->getFirstName($_SESSION['deepskylog_id']) . 
                       LangPDFMessage20 . $instrument->getInstrumentName($observer->getStandardTelescope($_SESSION['deepskylog_id'])) . ' ' . 
				               LangPDFMessage21 . $location->getLocationName($observer->getStandardLocation($_SESSION['deepskylog_id'])), 'center' );
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
  {
    include_once "observers.php";
    $observer = new Observers;

    global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
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

      $con = $valueA[2];
      $type = $valueA[1];
      $atlas = $observer->getStandardAtlasCode($_SESSION['deepskylog_id']);
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

        $con = $valueA[2];
        $type = $valueA[1];
        $atlas = $observer->getStandardAtlasCode($_SESSION['deepskylog_id']);
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
  {
    return preg_replace( '!<br.*>!iU', " ", $data );
  }

  // Creates a csv file from an array of observations
  function csvObservations($result)
  {
    include_once "objects.php";
    include_once "observers.php";
    include_once "instruments.php";
    include_once "eyepieces.php";
    include_once "filters.php";
    include_once "lenses.php";
    include_once "locations.php";
    include_once "setup/vars.php";
    include_once "setup/databaseInfo.php";
    $objects = new Objects;
    $observer = new Observers;
    $instrument = new Instruments;
    $eyepiece = new Eyepieces;
    $filter = new Filters;
    $lens = new Lenses;
    $observation = new Observations;
    $location = new Locations;
    $util = new Util;

    print LangCSVMessage3."\n";

    while(list ($key, $value) = each($result))
    {
      $obs = $observation->getAllInfoDsObservation($value);
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
      $description = $util->br2nl(html_entity_decode($obs["description"]));
      $description = preg_replace("/;/", ",", $description);
      $visibility = $obs["visibility"];
      if ($visibility == "0")
      {
        $visibility = "";
      }
      $name = $observer->getFirstname($obs["observer"]). " ".$observer->getObserverName($obs["observer"]);
      $seeing = $observation->getSeeing($value);
      $limmag = $observation->getLimitingMagnitude($value);
      $description = preg_replace("/(\r\n|\n|\r)/", "", $description);
      $description = preg_replace("/(\")/", "", $description);
      echo (html_entity_decode($objectname) . ";" . html_entity_decode($name) . ";" . $date[2] . "-" . $date[1] . "-" . $date[0] . ";" . $time . ";" . html_entity_decode($location->getLocationName($loc)) . ";" . html_entity_decode($instrument->getInstrumentName($inst)) . ";" . html_entity_decode($eyepiece->getEyepieceName($eyep)) . ";" . html_entity_decode($filter->getFilterName($filt)) . ";" . html_entity_decode($lens->getLensName($lns)) . ";" . $seeing . ";" . $limmag . ";" . $visibility . ";" . $langObs . ";" . $description . "\n");
    }
  }

  // Creates a csv file from an array of objects
  function csvObjects($result)
  {
    include_once "observers.php";
    include_once "objects.php";
    include "setup/vars.php";
    include "setup/databaseInfo.php";
    $object = new objects;
    $observer = new Observers;
    print html_entity_decode(LangCSVMessage7)."\n";

    while(list ($key, $valueA) = each($result))
    {
      $alt="";
      $alts = $object->getAlternativeNames($valueA[0]);
      $first = true;
      while(list($key,$value)=each($alts))
      if($value!=$valueA[0])
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
      $con = $valueA[2];
      $pa = $valueA[20];
      if($pa==999)
      $pa="";
      $type = $valueA[1];
      $atlas = $observer->getStandardAtlasCode($_SESSION['deepskylog_id']);
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

      echo $valueA[0].";". $alt .";".$this->raToString($valueA[7]).";".$this->decToString($valueA[8], 0).";".$$con.";".$$type.";".$mag.";".$sb.";".$size.";".$pa.";".$page.";".$valueA[21].";".$magnifi.";".$valueA[3].";".$valueA[28]."\n";
    }
  }

  // Creates an argo navis file from an array of objects
  function argoObjects($result)
  {
    include_once "objects.php";
    include_once "observers.php";
    include "setup/vars.php";
    include "setup/databaseInfo.php";

    $objects = new Objects;
    $observer = new Observers;

    $counter = 0;

    while(list ($key, $valueA) = each($result))
    {
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
      $con = $valueA[2];
      $argotype = "argo".$valueA[1];
      $atlas = $observer->getStandardAtlasCode($_SESSION['deepskylog_id']);
      $page = $valueA[$atlas];
      $size = "";
      $diam1 = $valueA[18];
      $diam2 = $valueA[19];

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
      echo "DSL " . sprintf("%03d", $counter) . " " . $valueA[0]."|".$this->raArgoToString($valueA[7])."|".$this->decToArgoString($valueA[8], 0)."|".$$argotype."|".$mag."|".$size.";".$atlas." ".$page.";CR ".$valueA[21].";".$valueA[3].";".$valueA[28]."\n";
      $counter++;
    }
  }

  // Creates a pdf document from an array of observations
  function pdfObservations($result)
  {
    include_once "objects.php";
    include_once "observers.php";
    include_once "instruments.php";
    include_once "locations.php";
    include_once "eyepieces.php";
    include_once "filters.php";
    include_once "lenses.php";

    global $AND,$ANT,$APS,$AQR,$AQL,$ARA,$ARI,$AUR,$BOO,$CAE,$CAM,$CNC,$CVN,$CMA,$CMI,$CAP,$CAR,$CAS,$CEN,$CEP,$CET,$CHA,$CIR,$COL,$COM,$CRA,$CRB,$CRV,$CRT,$CRU,
    $CYG,$DEL,$DOR,$DRA,$EQU,$ERI,$FOR,$GEM,$GRU,$HER,$HOR,$HYA,$HYI,$IND,$LAC,$LEO,$LMI,$LEP,$LIB,$LUP,$LYN,$LYR,$MEN,$MIC,$MON,$MUS,$NOR,$OCT,$OPH,
    $ORI,$PAV,$PEG,$PER,$PHE,$PIC,$PSC,$PSA,$PUP,$PYX,$RET,$SGE,$SGR,$SCO,$SCL,$SCT,$SER,$SEX,$TAU,$TEL,$TRA,$TRI,$TUC,$UMA,$UMI,$VEL,$VIR,$VOL,$VUL;

    global $ASTER,$BRTNB,$CLANB,$DRKNB,$GALCL,$GALXY,$GLOCL,$GXADN,$GXAGC,$GACAN,$LMCCN,$LMCDN,$LMCGC,$LMCOC,$NONEX,$OPNCL,$PLNNB,
    $SMCCN,$SMCDN,$SMCGC,$SMCOC,$SNREM,$QUASR,$AA1STAR,$AA2STAR,$AA3STAR,$AA4STAR,$AA8STAR;

    global $EMINB,$REFNB,$ENRNN,$ENSTR,$HII,$RNHII,$STNEB,$WRNEB;

    global $deepskylive, $dateformat;

    $objects = new Objects;
    $observer = new Observers;
    $instrument = new Instruments;
    $observation = new Observations;
    $location = new Locations;
    $util = new Util;
    $eyepiece = new Eyepieces;
    $filter = new Filters;
    $lens = new Lenses;

    // Create pdf file
    $pdf = new Cezpdf('a4', 'portrait');
    $pdf->ezStartPageNumbers(300, 30, 10);

    $fontdir = realpath('../lib/fonts/Helvetica.afm');
    //$pdf->selectFont($fontdir);
    $pdf->selectFont('../lib/fonts/Helvetica.afm');
    $pdf->ezText(LangPDFTitle2."\n");

    while(list ($key, $value) = each($result))
    {
      $obs = $observation->getAllInfoDsObservation($value);
      $objectname = $obs["name"];
      $object = $objects->getAllInfoDsObject($objectname);
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

      if(array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'] && ($observer->getUseLocal($_SESSION['deepskylog_id'])))
      {
        $date = sscanf($obs["localdate"], "%4d%2d%2d");
      }
      else
      {
        $date = sscanf($obs["date"], "%4d%2d%2d");
      }

      $description = $util->br2nl(html_entity_decode($obs["description"]));

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
        $filtername = $filter->getFilterName($filt);
        $filtstr = LangViewObservationField31. " : " . $filtername;
      }

      if ($eyep > 0)
      {
        $eyepiecename = $eyepiece->getEyepieceName($eyep);
        $eyepstr = LangViewObservationField30. " : " . $eyepiecename;
      }

      if ($lns > 0)
      {
        $lensname = $lens->getLensName($lns);
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
                 "observer" => html_entity_decode(LangPDFMessage13).$observer->getFirstName($observerid)." ".$observer->getObserverName($observerid).html_entity_decode(LangPDFMessage14).$formattedDate,
                 "instrument" => html_entity_decode(LangPDFMessage11)." : ".$instrument->getInstrumentName($inst),
                 "location" => html_entity_decode(LangPDFMessage10)." : ".$location->getLocationName($loc),
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
  {
    include_once "cometobjects.php";
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
}
?>
