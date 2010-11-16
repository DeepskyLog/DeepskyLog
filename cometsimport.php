<?php
require_once "lib/cometobjects.php";

$obj = new CometObjects;
$observations = new CometObservations;

// Reading the file with the country codes.
$filename = "mycomsql.sql";
$fh = fopen($filename, "r") or die("Could not open comets file");

while (!feof($fh))
{
  $data = fgets($fh);
  $vars = explode("', '", $data);

  if ($vars[13] == "ADIE")
  {
   // Alfons Diepvens
   $observerid = 'vvs00346';
  }
  else if($vars[13] == "FLOO")
  {
   // Frans van Loo
   $observerid = 'vvs00824';
  }
  else if($vars[13] == "DVST")
  {
   // David Vansteelant
   $observerid = 'ambetanterik';
  }
  else if($vars[13] == "KVDA")
  {
   // Koen Van der Auwera
   $observerid = 'kvda';
  }
  else if($vars[13] == "PSCH")
  {
   // Peter De Schrijver
   $observerid = 'vvs03972';
  }
  else if($vars[13] == "GFLE")
  {
   // Gunther Fleerackers
   $observerid = 'vvs04095';
  }
  else if($vars[13] == "GGUB")
  {
   // Guido Gubbels
   $observerid = 'vvs00946';
  }
  else if($vars[13] == "HHAU")
  {
   // Hubert Hautecler
   $observerid = 'vvs02959';
  }
  else if($vars[13] == "EBRO")
  {
   // Eric Broens
   $observerid = 'vvs00330';
  }
  else if($vars[13] == "JVDL")
  {
   // Johan Van der Looy
   $observerid = 'vvs00347';
  }
  else if($vars[13] == "SVIM")
  {
   // Steven Van Impe
   $observerid = 'vvs03475';
  }
  else if($vars[13] == "LSTE")
  {
   // Lode Stevens
   $observerid = 'vvs03896';
  }
  else if($vars[13] == "MVDP")
  {
   // Michel Vandeputte
   $observerid = 'vvs01944';
  }
  else if($vars[13] == "PVDE")
  {
   // Peter Van den Eijnde
   $observerid = 'vvs00196';
  }
  else if($vars[13] == "CSTE")
  {
   // Christian Steyaert
   $observerid = 'vvs00337';
  }
  else if($vars[13] == "PHMO")
  {
   // Philippe Mollet
   $observerid = 'vvs00138';
  }
  else
  {
   print "UNKNOWN OBSERVER : ".$vars[13]."\n";
  }

  if ($observerid != '')
  {
   $objectid = $obj->getId($vars[0]);
   
   if ($objectid == '')
   {
    print "UNKNOWN COMET : " . $vars[0]."\n";
   }
   $date = floor($vars[1]);

   $time = floor(($vars[1] - $date) * 24) * 100 + floor((($vars[1] - $date) * 24 - floor(($vars[1] - $date) * 24)) * 60);

   if ($vars[2] != '/')
   {
    $methode = $vars[2];
   }
   $mag = -99.9;

   if ($vars[3] != '/')
   {
    $mag = $vars[3];
   }
 
   $uncertain = 0;

   $weaker = 0;

   if (ereg('\[([0-9]{1,2})[.,]([0-9]{1})([:]{0,1})', $mag, $matches))
   {
    $mag = $matches[1].".".$matches[2].$matches[3];

    // Magnitude is weaker than the given magnitude
    $weaker = 1;
   }
   if (ereg('([0-9]{1,2})[.,]([0-9]{1}):', $mag, $matches))
   {

    $mag = $matches[1].".".$matches[2];
    // Observation is uncertain!!!!

    $uncertain = 1;
   }

   if ($vars[4] != '/')
   {
    $chart = $vars[4];
   }

   // Instrumenten aanpassen...
   $instrumentid = 1;

   if ($vars[6] != 'E' && $vars[6] != '/')
   {
    if ($vars[5] == '0.15')
    {
     if ($vars[7] == '15' && $vars[6] == 'R')
     {
      // 15cm F/15
      $instrumentid = 104;
     }
     else if ($vars[7] == '8' && $vars[6] == 'R')
     {
      // 15cm F/8
      $instrumentid = 105;
     }
     else if ($vars[7] == '8' && $vars[6] == 'L')
     {
      // 15cm F/8
      $instrumentid = 121;
     }
     else if ($vars[7] == '5' && $vars[6] == 'L')
     {
      // 15cm F/5 reflector
      $instrumentid = 109;
     }
     else if ($vars[6] == 'R')
     {
      // 15cm F/5 refractor
      $instrumentid = 122;
     }
     else
     {
      print "UNKNOWN 15cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.31')
    {
     if ($vars[7] == '8')
     {
      // 31cm F/8 reflector
      $instrumentid = 132;
     }
     else
     {
      print "UNKNOWN 31cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.30')
    {
     if ($vars[7] == '4')
     {
      // 30cm F/4
      $instrumentid = 106;
     }
     else if ($vars[7] == '10' && $vars[6] = 'B')
     {
      // 30cm F/10
      $instrumentid = 72;
     }
     else
     {
      print "UNKNOWN 30cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.25')
    {
     if ($vars[7] == '4')
     {
      // 25cm F/4
      $instrumentid = 107;
     }
     else if ($vars[7] == '5')
     {
      // 25cm F/5
      $instrumentid = 113;
     }
     else
     {
      print "UNKNOWN 25cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.22')
    {
     if ($vars[6] == 'L' && $vars[7] == '6')
     {
      // 22cm F/6 refractor
      $instrumentid = 117;
     }
     else if ($vars[6] == 'l' && $vars[7] == '6')
     {
      // 22cm F/6 refractor
      $instrumentid = 117;
     }
     else if ($vars[6] == 'L')
     {
      // 22cm F/6 refractor
      $instrumentid = 117;
     }
     else
     {
      print "UNKNOWN 22cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.20')
    {
     if ($vars[6] == 'R')
     {
      // 20cm F/15 refractor
      $instrumentid = 118;
     }
     else
     {
      print "UNKNOWN 20cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.18')
    {
     if ($vars[6] == 'L' && $vars[7] == '5')
     {
      // 18cm F/5 reflector
      $instrumentid = 123;
     }
     else
     {
      print "UNKNOWN 18cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.12')
    {
     if ($vars[6] == 'B' && $vars[8] = '20')
     {
      // 20 x 120 verrekijker
      $instrumentid = 110;
     }
     else if ($vars[6] == 'L')
     {
      // 12cm reflector
      $instrumentid = 126;
     }
     else
     {
      print "UNKNOWN 12cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.11')
    {
     if ($vars[6] == 'R' && $vars[7] = '8')
     {
      // 11cm refractor (F/8)
      $instrumentid = 115;
     }
     else if ($vars[6] == 'L' && $vars[7] = '8')
     {
      // 11cm reflector (F/8)
      $instrumentid = 62;
     }
     else if ($vars[6] == 'B')
     {
      // 11cm verrekijker
      $instrumentid = 116;
     }
     else
     {
      print "UNKNOWN 11cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.10')
    {
     if ($vars[6] == 'R' && $vars[7] = '10')
     {
      // 10 cm refractor F/10
      $instrumentid = 39;
     }
     else if ($vars[6] == 'B')
     {
      // 10 cm verrekijker
      $instrumentid = 68;
     }
     else
     {
      print "UNKNOWN 10cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.09')
    {
     if ($vars[6] == 'B' && $vars[8] = '20')
     {
      // 20 x 90 verrekijker
      $instrumentid = 111;
     }
     else
     {
      print "UNKNOWN 9cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.08')
    {
     if ($vars[6] == 'B' && $vars[8] == '15')
     {
      // 15 x 80 verrekijker
      $instrumentid = 108;
     }
     else if ($vars[6] == 'B')
     {
      // 80mm verrekijker
      $instrumentid = 114;
     }
     else if ($vars[6] == 'R' && $vars[7] == '5')
     {
      // 80mm F/5 refractor
      $instrumentid = 129;
     }
     else if ($vars[6] == 'R' && $vars[7] == '6')
     {
      // 80mm F/6 refractor
      $instrumentid = 130;
     }
     else
     {
      print "UNKNOWN 8cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.07')
    {
     if ($vars[6] == 'B')
     {
      // 70mm verrekijker
      $instrumentid = 127;
     }
     else if ($vars[6] == 'R' && $vars[7] == '6')
     {
      // 70mm f/6 refractor
      $instrumentid = 134;
     }
     else
     {
      print "UNKNOWN 7cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.06')
    {
     if ($vars[6] == 'B' && $vars[8] == '20')
     {
      // 20 x 60 verrekijker
      $instrumentid = 120;
     }
     else if ($vars[6] == 'B' && $vars[8] == '12')
     {
      // 12 x 60 verrekijker
      $instrumentid = 119;
     }
     else if ($vars[6] == 'B')
     {
      // 60mm verrekijker
      $instrumentid = 133;
     }
     else
     {
      print "UNKNOWN 6cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.05')
    {
     if ($vars[8] == '20')
     {
      // 20x50 bino
      $instrumentid = 103;
     }
     else if ($vars[8] == '8')
     {
      // 8 x 50 bino
      $instrumentid = 102;
     }
     else if ($vars[8] == '7')
     {
      // 7 x 50 bino
      $instrumentid = 8;
     }
     else if ($vars[8] == '10')
     {
      // 10 x 50 bino
      $instrumentid = 22;
     }
     else if ($vars[8] == '12')
     {
      // 12 x 50 bino
      $instrumentid = 112;
     }
     else if ($vars[6] == 'B')
     {
      // 50 bino
      $instrumentid = 125;
     }
     else 
     {
      print "UNKNOWN 5cm instrument (".$vars[6]."): F/".$vars[7]." ".$vars[8]."x"."\n";
     }
    }
    else if ($vars[5] == '0.04')
    {
     if ($vars[6] == 'B' && $vars[8] == '8')
     {
      // 8 x 40 verrekijker
      $instrumentid = 131;
     }
     else
     {
      print "UNKNOWN 4cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else if ($vars[5] == '0.03')
    {
     if ($vars[6] == 'B' && $vars[8] == '8')
     {
      // 8 x 30 verrekijker
      $instrumentid = 124;
     }
     else
     {
      print "UNKNOWN 3cm instrument (".$vars[6]."): F/".$vars[7]."\n";
     }
    }
    else
    {
     print "UNKNOWN ".($vars[5])."mm instrument (".$vars[6].") : F/".$vars[7]." ".$vars[8]."x"."\n";
    }
   }
   $magnification = 1;
   if ($vars[8] != '/')
   {
    $magnification = $vars[8];
   }

   $dc = -99;
   if ($vars[9] != '/' && $vars[9] != "-")
   {
    $dc = $vars[9];
   }

   $coma = -99;
   if ($vars[10] != '/')
   {
    $coma = $vars[10];
   }

   $tail = -99;
   if ($vars[11] != '/' && $vars[11] != '-')
   {
    $tail = $vars[11];
   }

   $pa = -99;
   if ($vars[12] != '/' && $vars[12] != '-')
   {
    $pa = $vars[12];
   }

   $desc = explode("');", $vars[14]);
   $description = $desc[0];

   $id = $observations->addObservation($objectid, $observerid, $date, $time);
   $observations->setInstrumentId($id, $instrumentid);
   $observations->setDescription($id, $description);
   $observations->setMethode($id, $methode);
   $observations->setMagnitude($id, $mag);
   $observations->setMagnitudeUncertain($id, $uncertain);
   $observations->setMagnitudeWeakerThan($id, $weaker);
   $observations->setChart($id, $chart);
   $observations->setMagnification($id, $magnification);
   $observations->setDc($id, $dc);
   $observations->setComa($id, $coma);
   $observations->setTail($id, $tail);
   $observations->setPa($id, $pa);
 }
}

fclose($fh);

?>
