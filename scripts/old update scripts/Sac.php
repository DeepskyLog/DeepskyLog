<?php
 include "objects.php";
 
 $obj = new Objects;

 // Reading the file with the country codes.
 $filename = "Sac72.txt";
 $fh = fopen($filename, "r") or die("Could not open countries file");

 while (!feof($fh))
 {
  $data = fgets($fh);
  $vars = explode("\",\"", $data);

  $tmp = explode("\"", $vars[0]);
  $object = rtrim($tmp[1]);

  $other = rtrim($vars[1]);

  $type = rtrim($vars[2]);

  $con = rtrim($vars[3]);

  $a = sscanf(rtrim($vars[4]), "%d %f");
  $ra = $a[0] + $a[1] / 60.0;

  $a = sscanf(rtrim($vars[5]), "%c%d %d");
  $dec = $a[1] + $a[2] / 60.0;

  if ($a[0] == "-")
  {
   $dec = -$dec;
  }
 
  $mag = rtrim($vars[6]);

  $subr = rtrim($vars[7]);

  $a = sscanf(rtrim($vars[10]), "%f %c");
  if ($a[1] == "m")
  {
   $size_max = $a[0] * 60.0;
  }
  else if ($a[1] == "s")
  {
   $size_max = $a[0];
  }
  else
  {
   $size_max = "";
  }
  

  $a = sscanf(rtrim($vars[11]), "%f %c");
  if ($a[1] == "m")
  {
   $size_min = $a[0] * 60.0;
  }
  else if ($a[1] == "s")
  {
   $size_min = $a[0];
  }
  else
  {
   $size_min = "";
  }

  $pa = rtrim($vars[12]);
 
  if ($pa == "")
  {
   $pa = 999;
  }

  $bchm = rtrim($vars[16]);

  if ($object != "OBJECT" && $object != "")
  {
   $object = preg_replace("/  /", " ", $object);
   $object = preg_replace("/  /", " ", $object);
   $object = preg_replace("/B(\d+)/", "B $1", $object);
   $object = preg_replace("/Sh2- (\d+)/", "Sh2-$1", $object);
   $object = preg_replace("/Sh2-(\d+)/", "Sh 2-$1", $object);
   $object = preg_replace("/PK (\d+)\+ (\w)/", "PK $1+$2", $object);
   $object = preg_replace("/PK (\d+)- (\w)/", "PK $1-$2", $object);
   $object = preg_replace("/M(\d)-(\d+)/", "Mi $1-$2", $object);
   $object = preg_replace("/Abell (\d)/", "AGC $1", $object);

   $other = preg_replace("/  /", " ", $other);
   $other = preg_replace("/  /", " ", $other);
   $other = preg_replace("/B(\d+)/", "B $1", $other);
   $other = preg_replace("/Sh2- (\d+)/", "Sh2-$1", $other);
   $other = preg_replace("/Sh2-(\d+)/", "Sh 2-$1", $other);
   $other = preg_replace("/PK (\d+)\+ (\w)/", "PK $1+$2", $other);
   $other = preg_replace("/PK (\d+)- (\w)/", "PK $1-$2", $other);
   $other = preg_replace("/BD\+30 (\d+)/", "BD +30 $1", $other);
   $other = preg_replace("/D2/", "", $other);
   $other = preg_replace("/DHW/", "", $other);
   $other = preg_replace("/H1- 9/", "", $other);
   $other = preg_replace("/H1-50/", "", $other);
   $other = preg_replace("/H2-46/", "", $other);
   $other = preg_replace("/H2-43/", "", $other);
   $other = preg_replace("/H4- 1/", "", $other);
   $other = preg_replace("/Hercules Galxy Cl/", "", $other);
   $other = preg_replace("/Aquarius Dwarf/", "", $other);
   $other = preg_replace("/IV Zw 67;CRL 2688/", "", $other);
   $other = preg_replace("/CRL 618/", "", $other);
   $other = preg_replace("/Circinus Dwarf/", "", $other);
   $other = preg_replace("/Coma I/", "", $other);
   $other = preg_replace("/Draco Dwarf/", "", $other);
   $other = preg_replace("/ESO (\d+)- (\d+)/", "ESO $1-$2", $other);
   $other = preg_replace("/He(\w)/", "He $1", $other);
   $other = preg_replace("/Fleming 3;V V 133/", "", $other);
   $other = preg_replace("/Fleming 1/", "", $other);
   $other = preg_replace("/Fornax Dwarf/", "", $other);
   $other = preg_replace("/Gum (\d+);(\w+) (\d+)/", "Gum $1", $other);
   $other = preg_replace("/Gum 2-296;RCW1/", "Gum 2-296", $other);
   $other = preg_replace("/Gum 77b;RCW 151/", "Gum 77b", $other);
   $other = preg_replace("/Haro (\d)- (\d)/", "Haro $1-$2", $other);
   $other = preg_replace("/Cr 36;Harvard 1/", "Cr 36", $other);
   $other = preg_replace("/Cr 168;Harvard 2/", "Cr 168", $other);
   $other = preg_replace("/He (\d)- (\d+)/", "He $1-$2", $other);
   $other = preg_replace("/IC 4643;H IV 57/", "IC 4643", $other);
   $other = preg_replace("/PK 2+5.1;H IV 11/", "PK 2+5.1", $other);
   $other = preg_replace("/PK 69-2.1;H IV 13/", "PK 69-2.1", $other);
   $other = preg_replace("/PK 61-9.1;H IV 16/", "PK 61-9.1", $other);
   $other = preg_replace("/K(\d)-(\d+)/", "K $1-$2", $other);
   $other = preg_replace("/K(\d)- (\d+)/", "K $1-$2", $other);
   $other = preg_replace("/M(\d)-(\d+)/", "Mi $1-$2", $other);
   $other = preg_replace("/M(\d)- (\d+)/", "Mi $1-$2", $other);
   $other = preg_replace("/MCG - (\d)- (\d)- (\d+)/", "MCG -$1-$2-$3", $other);
   $other = preg_replace("/MCG (\d)- (\d)- (\d+)/", "MCG $1-$2-$3", $other);
   $other = preg_replace("/MCG (\d)- (\d)-(\d+)/", "MCG $1-$2-$3", $other);
   $other = preg_replace("/MCG (\d)-(\d+)- (\d+)/", "MCG $1-$2-$3", $other);
   $other = preg_replace("/MCG - (\d)-(\d+)- (\d+)/", "MCG -$1-$2-$3", $other);
   $other = preg_replace("/Menzel 2;V V 78/", "Menzel 2", $other);
   $other = preg_replace("/UGC 499;Mrk 348/", "UGC 499", $other);
   $other = preg_replace("/UGC 645;Mrk 353/", "UGC 645", $other);
   $other = preg_replace("/V V 81;Arp 140/", "Arp 140", $other);
   $other = preg_replace("/Pegasus Dwarf/", "", $other);
   $other = preg_replace("/Perek (\d)- (\d)/", "Perek $1-$2", $other);
   $other = preg_replace("/Sculptor Dwarf/", "", $other);
   $other = preg_replace("/Sh(\d)- (\d+)/", "Sh $1-$2", $other);
   $other = preg_replace("/Sh(\d)-(\d+)/", "Sh $1-$2", $other);
   $other = preg_replace("/UGCA 444;WLM/", "UGCA 444", $other);
   $other = preg_replace("/Ursa Major I/", "", $other);
   $other = preg_replace("/Ursa Minor Dwarf/", "", $other);
   $other = preg_replace("/UGC 173;V V 166/", "UGC 173", $other);
   $other = preg_replace("/UGC 176;V V 166/", "UGC 176", $other);
   $other = preg_replace("/PK 2+5.1;H IV 11/", "PK 2+5.1", $other);
   $other = preg_replace("/V V (\d+)/", "VV $1", $other);
   $other = preg_replace("/V V (\d+)-(\d+)/", "VV $1-$2", $other);
   $other = preg_replace("/Vd1-1/", "", $other);
   $other = preg_replace("/IC 4955;Ced 175/", "IC 4955", $other);
   $other = preg_replace("/UGC 174;IC 1539/", "IC 1539", $other);
   $other = preg_replace("/UGC 326;D3/", "UGC 326", $other);
   $other = preg_replace("/UGC 365;Arp 282/", "UGC 365", $other);
   $other = preg_replace("/UGC 601;4Z 35/", "UGC 601", $other);
   $other = preg_replace("/;Arp 331/", "", $other);
   $other = preg_replace("/UGC 815;Arp 164/", "UGC 815", $other);
   $other = preg_replace("/UGC 915;Arp 8/", "UGC 915", $other);
   $other = preg_replace("/IC 1696; UGC 965/", "IC 1696", $other);
   $other = preg_replace("/IC 1712;H I 100/", "IC 1712", $other);
   $other = preg_replace("/IC 191;H III 207/", "IC 191", $other);
   $other = preg_replace("/3C84;UGC 2669/", "UGC 2669", $other);
   $other = preg_replace("/IC 2529;H III 596/", "IC 2529", $other);
   $other = preg_replace("/IC 2968;UGC 6851/", "IC 2968", $other);
   $other = preg_replace("/IC 2976;UGC 6907/", "IC 2976", $other);
   $other = preg_replace("/UGC 6954;Arp 305a/", "UGC 6954", $other);
   $other = preg_replace("/IC 2997;UGC 7077/", "IC 2997", $other);
   $other = preg_replace("/IC 3035;UGC 7201/", "IC 3035", $other);
   $other = preg_replace("/IC 3042;UGC 7215/", "IC 3042", $other);
   $other = preg_replace("/UGC 7223;Ho 348b/", "UGC 7223", $other);
   $other = preg_replace("/UGC 7372; Ho 365a/", "UGC 7372", $other);
   $other = preg_replace("/UGC 7404; Ho 375a/", "UGC 7404", $other);
   $other = preg_replace("/UGC 7432;Ho 381a/", "UGC 7432", $other);
   $other = preg_replace("/IC 3211;UGC 7430/", "IC 3211", $other);
   $other = preg_replace("/UGC 7685; R 80/", "UGC 7685", $other);
   $other = preg_replace("/IC 3592;UGC 7789/", "IC 3592", $other);
   $other = preg_replace("/Ho 423c;IC 3593/", "IC 3593", $other);
   $other = preg_replace("/IC 3593;Ho 426?/", "IC 3593", $other);
   $other = preg_replace("/IC 3688;UGC 7874/", "IC 3688", $other);
   $other = preg_replace("/UGC 7977;Ho 468b/", "UGC 7977", $other);
   $other = preg_replace("/3C278;H I 136/", "3C 278", $other);
   $other = preg_replace("/UGC 8034;Ho 486b/", "UGC 8034", $other);
   $other = preg_replace("/IC 3935;UGC 8086/", "IC 3935", $other);
   $other = preg_replace("/UGC 8092;II Zw 67/", "UGC 8092", $other);
   $other = preg_replace("/UGC 8941;Arp 111/", "UGC 8941", $other);
   $other = preg_replace("/UGC 10033;Arp 72/", "UGC 10033", $other);
   $other = preg_replace("/PK 2\+5.1;H IV 11/", "PK 2+5.1", $other);
   $other = preg_replace("/ZWG (\d+). (\d+)/", "ZWG $1.$2", $other);
   $other = preg_replace("/PK 8\+3.1;H II 586/", "PK 8+3.1", $other);
   $other = preg_replace("/IC 1283;IC 4690/", "IC 1283", $other);
   $other = preg_replace("/PK 9-5.1;H II 204/", "PK 9-5.1", $other);
   $other = preg_replace("/K 2-1;SS 38/", "K 2-1", $other);
   $other = preg_replace("/Abell 27; K 1-1/", "Abell 27", $other);
   $other = preg_replace("/3C(\d+)/", "3C $1", $other);
   $other = preg_replace("/47 Tucanae/", "", $other);
   $other = preg_replace("/50 CAS/", "", $other);
   $other = preg_replace("/Omega Centauri/", "", $other);
   $other = preg_replace("/IRAS(\d+)-(\d+)/", "IRAS $1-$2", $other);
   $other = preg_replace("/IRAS(\d+)+(\d+)/", "IRAS $1+$2", $other);
   $other = preg_replace("/NGC 1893/", "", $other);
   $other = preg_replace("/IC 410/", "", $other);
   $other = preg_replace("/Hyades/", "", $other);
   $other = preg_replace("/Small Magellanc Cl/", "", $other);
   $other = preg_replace("/ARP 274/", "Arp 274", $other);
   $other = preg_replace("/AP 2-1/", "Ap 2-1", $other);
   $other = preg_replace("/VDB 66/", "vdB 66", $other);
   $other = preg_replace("/CED 55K/", "Ced 55k", $other);
   $other = preg_replace("/CED 55Q/", "Ced 55q", $other);
   $other = preg_replace("/CED 55C/", "Ced 55c", $other);
   $other = preg_replace("/CED 55B/", "Ced 55b", $other);
   $other = preg_replace("/CED 55P/", "Ced 55p", $other);
   $other = preg_replace("/CED 92/", "Ced 92", $other);
   $other = preg_replace("/CED 208/", "Ced 208", $other);
   $other = preg_replace("/CED 19I/", "Ced 19i", $other);
   $other = preg_replace("/CED 67A/", "Ced 67a", $other);
   $other = preg_replace("/CED 89B/", "Ced 89b", $other);
   $other = preg_replace("/CED 182B/", "Ced 182b", $other);
   $other = preg_replace("/CED 182C/", "Ced 182c", $other);
   $other = preg_replace("/Pal 15/", "", $other);

   if ($other == "M 102?")
   {
    $other = "M 102";
   }



   $messier = sscanf($other, "%s %d");

   if ($messier[0] == "M")
   {
    $hulp = $other;
    $other = $object;
    $object = $hulp;
   }

   if ($mag == 79.9)
   {
    $mag = "99.9";
   }

   if ($subr == 79.9)
   {
    $subr = "99.9";
   }

   if ($type == "CL+NB")
   {
    $type = "CLANB";
   }
   else if ($type == "GX+DN")
   {
    $type = "GXADN";
   }
   else if ($type == "GX+GC")
   {
    $type = "GXAGC";
   }
   else if ($type == "G+C+N")
   {
    $type = "GACAN";
   }
   else if ($type == "1STAR")
   {
    $type = "AA1STAR";
   }
   else if ($type == "2STAR")
   {
    $type = "AA2STAR";
   }
   else if ($type == "3STAR")
   {
    $type = "AA3STAR";
   }
   else if ($type == "4STAR")
   {
    $type = "AA4STAR";
   }
   else if ($type == "8STAR")
   {
    $type = "AA8STAR";
   }


   if ($previous != $object)
   {
    if ($object != "Eridanus Cluster" && $object != "Coalsack" && $object != "Lg Magellanic Cl" && $object != "Wild's triplet" && $object != "Zwicky's triplet" && $object != "Pal 9" && $object != "NGC 4153")
    {
    $obj->addDSObject($object, $other, $type, $con, $ra, $dec, $mag, $subr, $size_max, $size_min, $pa, $bchm, "SAC7.2");
    }
    $previous = $object;
   }

  }
 }
 fclose($fh);

 include_once "../lib/database.php";

 $db = new database;
 $db->login();

 $sql = "delete from objects where name = \"NGC 4560\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4610\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4407\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2372\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4667\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4884\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6975\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6976\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2244\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2237\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2520\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5490A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3371\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3373\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3389\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"NGC 6039\" where name = \"NGC 6040B\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6039\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6042\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6053\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6057\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3575\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3760\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4228\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4409\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5390\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6952\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6008A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5438\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5446\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3218\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5834\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3632\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4212\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4208\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4565A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4338\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4310\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7627\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7641\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7605\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7583\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7472\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7334\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7257\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7254\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7140\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7108\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7021\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7477\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7173\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6845A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"Ru 147\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6861A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6763\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"Pal 9\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6689\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6668\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6667\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6678\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6660\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6574\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6610\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6599\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6600\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6550\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6510\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6497\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6498\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6468\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6427\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6431\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6374\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6216\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6222\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6189\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6191\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6170\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6176\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6125\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"NGC 6128\" where name = \"NGC 6127\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6128\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"PK 342+10.1\" where name = \"NGC 6072\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6028\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6046\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5907\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5826\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5870\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5868\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5825\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5778\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5841\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5808\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5819\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5785\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5796\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5699\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5706\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5704\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5708\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5703\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5709\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5652\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5650\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5649\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5589\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5588\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5580\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5590\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5575\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5578\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5570\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5519\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5564\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5554\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5552\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5558\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5375\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5396\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5502\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5503\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5317\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5364\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5219\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5244\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5174\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5162\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5109\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5113\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5070\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5072\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5110\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5111\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4993\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4994\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5069\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4960\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4961\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4952\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4962\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4972\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4882\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4888\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4797\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4798\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4804\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where alternative_name = \"NGC 4759-1\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4759\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4624\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4728A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4650B\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4537\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4542\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4521\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4512\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4437\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4357\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4381\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4355\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4505\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4496B\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4364\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4362\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4325\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4368\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4354\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4323\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4265\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4303A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4301\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4211A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4243\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4163\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4167\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4180\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4182\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4140\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4149\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4154\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4119\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4124\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4130\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4107\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4078\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4122\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4113\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4055\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4061\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4057\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4065\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4059\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4070\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4014\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4028\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4046\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4007\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3980\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3971\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3984\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3966\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3986\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3922\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3924\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3917A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3890\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3939\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3858\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3899\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3912\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3854\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3856\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3826\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3830\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3822\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3848\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3825\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3852\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3807\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3794\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3795A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"UGC 6591\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3704\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3695\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3698\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3645\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3630\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3544\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3611\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3604\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3559\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3560\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3566\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3548\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3540\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3557A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3480\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3476\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3497\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3531\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3110\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3518\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3505\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3508\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3479\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3502\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"NGC 3500\" where name = \"NGC 3465\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3500\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3460\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3428\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3429\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3388\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3425\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3402\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3411\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3397\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3332\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3342\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3339\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3340\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3322\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3284\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3286\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3234\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3235\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set name = \"He 2-55\" where name = \"PK 286-4.1\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"He 2-55\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3194\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3191\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3192\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3189\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3174\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3122\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3121\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"UGC 5425\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3103\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3050\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2999\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2972\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2869\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2869\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2863\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2816\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2742\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2733\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2727\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"UGC 4506\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2475\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2443\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2431\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2436\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2382\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2356\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2316\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2317\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2302\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2299\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2273A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2239\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1995\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1974\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1991\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1911\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1915\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"IC 2118\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1909\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1882\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1884\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1854\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1781\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1689\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1649\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1652\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1626\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1593\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1577\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1575\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1570\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1571\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1551\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1471\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1457\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1455\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1457\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1452\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1446\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1442\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1436\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1437\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1424\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1380B\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1367\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1340\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1318\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1307\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1269\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1233\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1235\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1205\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1174\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1142\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1144\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1143\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1141\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1123\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 961\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1051\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1006\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 983\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1002\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 994\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 930\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 885\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 867\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 866\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 859\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 847\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 755\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 763\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 757\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 731\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 727\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 729\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 724\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 674\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 697\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 614\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 627\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 618\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 608\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 580\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 539\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 563\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 523\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 537\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 341A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 203\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 211\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 171\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 153\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 58\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 17\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 29\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 21\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 20\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"IC 1274\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"IC 1275\";";
 $run = mysql_query($sql) or die(mysql_error());

 $db->logout();

 echo "Sac objects succesfully written to database!\n";

?>
