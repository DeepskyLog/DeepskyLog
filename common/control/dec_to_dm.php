<?php
// utility functions in representation transformations

function decToStringDegMin($decl)
{
   $sign = 0;
   if($decl < 0)
   {
    $sign = -1;
    $decl = -$decl;
   }
   $decl_degrees = floor($decl);
   $subminutes = 60 * ($decl - $decl_degrees);
   $decl_minutes = round($subminutes);

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
    $decl_degrees = "&nbsp;" . $decl_degrees;
   }

   if($decl_minutes <= 9)
   {
      $decl_minutes = "0" . $decl_minutes;
   }

   return("$decl_degrees" . "&deg;" . "$decl_minutes" . "&#39;");
}

function decToString($decl,$web=1)
{ $sign =0;
  if($decl < 0)
  { $sign = -1;
    $decl = -$decl;
  }
  $decl_degrees = floor($decl);
  $subminutes = 60 * ($decl - $decl_degrees);
  $decl_minutes = round($subminutes);
  if($decl_minutes == 60)
  { $decl_minutes = 0;
    $decl_degrees++;
  }
  if($decl_degrees >= 0 && $decl_degrees <= 9)
    $decl_degrees = "0" . $decl_degrees;
  if ($sign == -1)
    $decl_degrees = "-" . $decl_degrees;
  else
  { if ($web == 1)
    { //$decl_degrees = "&nbsp;" . $decl_degrees; // add white space for overview locations
      $decl_degrees = $decl_degrees; // remove white space for object details
    }
    else
    { $decl_degrees = " " . $decl_degrees;
    }
  }
  if($decl_minutes <= 9)
  { $decl_minutes = "0" . $decl_minutes;
  }
  if ($web == 1)
  { $d = "&deg;";
    $m = "&#39;";
  }
  else
  { $d = "d";
    $m = "'";
  }
  return("$decl_degrees" .$d. "$decl_minutes" . $m);
}

function decToTrimmedString($decl)
{
   $sign = 1;
	 if($decl < 0)
   {
    $sign = -1;
    $decl = -$decl;
   }
   $decl_degrees = floor($decl);
   $subminutes = 60 * ($decl - $decl_degrees);
   $decl_minutes = round($subminutes);
 
   if ($sign == -1)
   {
    $decl_degrees = "-" . $decl_degrees;
   }
 
   if($decl_minutes <= 9)
   {
      $decl_minutes = "0" . $decl_minutes;
   }
 
   return("$decl_degrees" . "&deg;" . "$decl_minutes" . "&#39;");

}

function decToStringDSL($decl)
{
   if($decl < 0)
   {
    $sign = "m";
    $decl = -$decl;
   }
   else
   {
    $sign = "p";
   }
   $decl_degrees = floor($decl);
   $subminutes = 60 * ($decl - $decl_degrees);
   $decl_minutes = round($subminutes);

   return($sign.sprintf("%02d", "$decl_degrees") . sprintf("%02d", "$decl_minutes")."00");

}

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

?>
