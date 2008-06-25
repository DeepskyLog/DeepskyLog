<?php

// Version 3.1, DE 20061119

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

function decToStringDSS($decl)
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

   if ($sign == -1)
   {
    $decl_minutes = "-" . $decl_minutes;
    $decl_degrees = "-" . $decl_degrees;
   }
   return("$decl_degrees" . "&#43;" . "$decl_minutes");

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


?>
