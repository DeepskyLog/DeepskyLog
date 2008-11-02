<?php

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

function raToStringDSS($ra)
{

   $ra_hours = floor($ra);
   $subminutes = 60 * ($ra - $ra_hours);
   $ra_minutes = floor($subminutes);
   $ra_seconds = round(60 * ($subminutes - $ra_minutes));

   return("$ra_hours" . "&#43;" . "$ra_minutes" . "&#43;" . "$ra_seconds" . "");

}

function raToStringDSL($ra)
{
   $ra_hours = floor($ra);
   $subminutes = 60 * ($ra - $ra_hours);
   $ra_minutes = floor($subminutes);
   $ra_seconds = round(60 * ($subminutes - $ra_minutes));

   return(sprintf("%02d", "$ra_hours") . sprintf("%02d", "$ra_minutes") . sprintf("%02d", "$ra_seconds") . "");
}


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


?>
