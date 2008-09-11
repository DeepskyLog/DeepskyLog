<?php
 include_once "../lib/database.php";
 include_once "../lib/objects.php";

 $db = new database;
 $db->login();

 // Add a new table cometobservations
 $sql = "CREATE TABLE cometobservations (
           id int(20) NOT NULL auto_increment,
           objectid int(20) NOT NULL,
           observerid varchar(255) NOT NULL,
           instrumentid int(11) NOT NULL,
           locationid int(11) NOT NULL,
           date int(15) NOT NULL,
           time int(6),
           description longtext not null,
           methode char(1),
           mag float(4) default -99.9,
           chart char(2),
           magnification char(3),
           maguncertain int NOT NULL default '0',
           lessmagnitude int NOT NULL default '0',
           dc char(1),
           coma int(4) default -99,
           tail int(4) default -99,
           pa int(3) default -99,
           primary key(id)
         ) TYPE=MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());

 // Add a new table cometobjects
 $sql = "CREATE TABLE cometobjects (
           id int(20) NOT NULL auto_increment,
           name varchar(255) NOT NULL default '',
           icqname char(11) NOT NULL default '',
           PRIMARY KEY (id)
         ) TYPE=MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "ALTER TABLE observers ADD icqname varchar(6) default ''";
 $run = mysql_query($sql) or die(mysql_error());

 // Add a new table ICQ_METHOD
 $sql = "CREATE TABLE ICQ_METHOD (
           id char(1) BINARY NOT NULL,
           description varchar(100) NOT NULL,
           PRIMARY KEY (id)
         ) TYPE=MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());


 // Add a new table ICQ_REFERENCE_KEY
 $sql = "CREATE TABLE ICQ_REFERENCE_KEY (
           id char(2) BINARY NOT NULL,
           description varchar(100) NOT NULL,
           PRIMARY KEY (id)
         ) TYPE=MyISAM;";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"a\", \"Orange filter used on SOHO spacecraft with C2 and C3 coronagraphs\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"B\", \"VBM (Van Biesbroeck-Bobrovnikoff-Meisel) or simple Out-Out method\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"b\", \"VBM method using RCA #4549 image intensifier\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"C\", \"Unfiltered total CCD magnitude\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"c\", \"Unfiltered nuclear CCD magnitude\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"D\", \"Cousins B filter\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"E\", \"Extrafocal-Extinction (or Beyer) method\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"e\", \"Extrafocal-Extinction (or Beyer) method using RCA #4549 image\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"F\", \"Total magnitude using Meade CCD interference filter CM-500 Visible\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"f\", \"Nuclear magnitude using Meade CCD interference filter CM-500 Visible\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"G\", \"CCD magnitude with a Corion NR-400 minus-infrared filter\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"g\", \"CCD magnitude with Gunn g filter\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"H\", \"Cousins I filter with CCD\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"I\", \"In-focus\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"i\", \"Visual observation using an image intensifier\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"J\", \"Kron-Cousins V filter employed\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"j\", \"Kron-Cousins V filter employed (m2 estimate)\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"K\", \"Clear filter used on SOHO spacecraft with C3 coronagraph\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"k\", \"CCD magnitude with Cousins R filter\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"L\", \"Photoelectric B\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"l\", \"CCD magnitude with a Wratten 25 (red) filter\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"M\", \"Modified-Out method\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"N\", \"Magnitude of nucleus or condensation\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"O\", \"Out-of-focus method\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"o\", \"Visual extrafocal comparison using RCA #4549 image intensifier\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"P\", \"Photographic\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"p\", \"Photographic with Kodak 2415 film\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"Q\", \"Out-out\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"q\", \"R-band magnitude for nuclear condensation\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"R\", \"Photoelectric R\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"r\", \"CCD magnitude with Gunn r filter\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"S\", \"VSS (Vsekhsvyatskii-Steavenson-Sidgwick) or In-Out method\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"s\", \"VSS method using image intensifier\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"T\", \"Magnitude estimated from a TV monitor\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"t\", \"Total visual magnitude (historical obs. only)\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"U\", \"Photoelectric U\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"u\", \"Photoelectric U\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"V\", \"CCD-derived V nuclear magnitudes\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"v\", \"Photoelectric V\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"W\", \"Photoelectric with filters to match visual\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"w\", \"Photoelectric\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"X\", \"Ortochromatic film + yellow filter\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_METHOD (id, description) VALUES (\"Y\", \"CCD magnitude with Wratten No. 15 (yellow) filter\")";
 $run = mysql_query($sql) or die(mysql_error());


 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"AE\", \"Planetary magnitudes from American Ephemeris and Nautical Almanac\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"AT\", \"Arizona-Tonantzintla Catalog\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"AU\", \"ASAS-3 V magnitudes\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"BR\", \"V magnitude sequence for stars in the Coma cluster of galaxies\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"BS\", \"Johnson V photometry by Brian Skiff\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"C\", \"Photovisual magnitudes from Cape Photographic Catalogue for 1950.0\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CA\", \"M44 standard sequence\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CD\", \"Open star cluster NGC 225 photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CE\", \"Open star cluster NGC 1647 photometry \")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CF\", \"Open star cluster NGC 2129 photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CG\", \"Open star cluster NGC 2422 (M47) photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CH\", \"Open star cluster NGC 6494 (M23) photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CI\", \"Open star cluster NGC 6823 photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CJ\", \"Open star cluster NGC 6910 photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CK\", \"Open star cluster NGC 7031 photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CL\", \"Photometry by Hoag et al. (1961)\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CM\", \"Photovisual and photoelectric-V magnitudes from Cape Mimeograms\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CN\", \"Open star cluster NGC 7235 photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CO\", \"UBV photometry for 39 stars in the range 11.7 < V < 18.7\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CR\", \"V magnitudes of 13 stars surrounding NGC 3627 (M66)\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"CS\", \"Catalogue of Stellar Identifications\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"D\", \"Dutch Comet Halley Handbook\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"E\", \"One of Everhart's 3 Selected Area charts\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"EA\", \"Selected Area 51:  From Everhart\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"EB\", \"Selected Area 57:  From Everhart\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"EC\", \"Selected Area 68:  From Everhart\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"FA\", \"V photometry by Harold Ables, U.S. Naval Observatory\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"GA\", \"Guide Star Photometric Catalog - I\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"GP\", \"Harvard E Regions\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HD\", \"Henry Draper Catalog\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HE\", \"Harvard E Regions\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HI\", \"Hipparcos Input Catalogue\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HJ\", \"Magnitudes in the Hipparcos photometric system, Hp\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HK\", \"H_p magnitudes from the Hipparcos Catalogue\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HN\", \"Arne Henden's photometric sequences\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HP\", \"Harvard Photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HR\", \"Harvard Revised Photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"HV\", \"Johnson V magnitudes from Hipparcos Catalogue\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"JT\", \"Cousins VRI magnitudes of stars in M67\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"L\", \"Landolt V Photoelectric Sequences\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"LA\", \"Landolt photoelectric sequences\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"LB\", \"Landolt sequences as published by Christian Buil\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"LC\", \"Landolt magnitude sequence for 33 stars near V1057 Cyg\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"MC\", \"Carlsberg Meridian Catalogue\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"ME\", \"V photometry by Tedesco, Tholen, and Zellner\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"MK\", \"V magnitudes for M67 in LE GUIDE PRATIQUE DE L'ASTRONOMIE CCD\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"MP\", \"McCormick Photovisual Sequence\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"MS\", \"McCormick Photovisual Sequences\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"MT\", \"Visual magnitudes of stars in M67\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"MV\", \"From Publ. Leander McCormick Obs.\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"NH\", \"North Polar Sequence as published by Henden and Kaitchuck\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"NN\", \"NGC 2129/6531/1342 cluster photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"NO\", \"U.S.N.O. Photoelectric Photometry Catalogue\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"NP\", \"North Polar Sequence\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"NS\", \"Magnitudes and Colors of Stars North of +80\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"OB\", \"Magnitudes for faint cluster stars\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"PA\", \"M45 sequence\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"PB\", \"Pleiades chart in Sky and Telescope 70\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"PC\", \"Pleiades sequence, Henden and Kaitchuck\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"PI\", \"IC 4665 sequence as found in Henden and Kaitchuck\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"RB\", \"Photoelectric Magnitudes and Colours of Southern Stars\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"RC\", \"Standard Magnitudes in the E Regions\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SD\", \"V magnitudes of members of the globular cluster M15\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SE\", \"V magnitudes of 134 stars of the II Persei Association\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SK\", \"Brian Skiff's compilation of magnitudes as part of the LONEOS project\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SM\", \"V magnitudes from A Visual Atlas of the Small Magellanic Cloud\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SP\", \"Skalnate-Pleso Atlas Catalog\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SS\", \"Various regions covering declination -60 deg to +10 deg\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SW\", \"Four half-degree fields with finder charts and UBV photometry\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"TA\", \"Comparison-star magnitudes from The Amateur Sky Survey\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"TG\", \"CCD magnitudes on the Thuan-Gunn system\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"TI\", \"Tycho Input Catalogue\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"TJ\", \"Tycho Catalogue Johnson V magnitudes\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"TK\", \"Tycho-2 Catalogue\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"TS\", \"Field of 13 stars\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"TT\", \"Tycho/Hipparcos Catalogue V_T magnitudes\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"VG\", \"Japanese variable-star charts edited by K. Gomi\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"Y\", \"Yale Bright Star Catalogue\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"YF\", \"Yale Bright Star Catalogue, fourth edition\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"YG\", \"Yale Bright Star Catalogue, fifth edition\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"AA\", \"A.A.V.S.O. Variable Star Atlas\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"AC\", \"Charts of the Amer. Assn. of Var. Star Observers\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"AP\", \"Atlas Photometrique des Constellations\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"AS\", \"AAVSO chart for M81 (NGC 3031) in Ursa Major\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"FD\", \"A photoelectric BVRI sequence in the field of NGC 6205 (M13)\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"FG\", \"A Field Guide to the Stars and Planets\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"LM\", \"A Visual Atlas of the Large Magellanic Cloud\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"ML\", \"V magnitudes on chart of Large Magellanic Cloud by Mati Morel\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"MM\", \"V magnitudes on chart of Small Magellanic Cloud by Mati Morel\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"OH\", \"From listing of bright stars in Observers' Handbook, R.A.S.C.\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"PK\", \"From the Soviet Program for Comet Halley\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"S\", \"Smithsonian Astrophysical Obs. Star Catalog\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SA\", \"M67 sequence by R. E. Schild\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"SC\", \"Sky Catalogue 2000.0\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"VB\", \"Variable star charts of the British Astr. Assn.\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"VF\", \"Variable star charts of the A.F.O.E.V. (France)\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"VN\", \"Variable star charts of the R.A.S. of New Zealand\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"W\", \"International Halley Watch (IHW) version of an unspecified AAVSO chart\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WA\", \"Special IHW version of AAVSO chart for SU Tauri\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WB\", \"Special IHW version of AAVSO chart for CZ Orionis\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WC\", \"Special IHW version of AAVSO chart for Y Tauri\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WD\", \"Special IHW version of AAVSO chart for V Tauri\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WE\", \"IHW version of AAVSO chart for X Sextantis\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WF\", \"IHW version of AAVSO chart for S Sextantis\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WG\", \"IHW version of AAVSO chart for SX Leonis\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WH\", \"Unspecified IHW charts\")";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "INSERT INTO ICQ_REFERENCE_KEY (id, description) VALUES (\"WW\", \"B.A.A. Charts as published in the IHW Observers' Manual\")";
 $run = mysql_query($sql) or die(mysql_error());

 echo "Database was updated succesfully!\n";
?>
