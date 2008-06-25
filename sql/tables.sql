DROP DATABASE IF EXISTS `DeepSkyLog`;
CREATE DATABASE IF NOT EXISTS `DeepSkyLog`
  CHARACTER SET latin1 COLLATE latin1_bin;
USE `DeepSkyLog`;

CREATE TABLE observers (
  id varchar(255) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  firstname varchar(255) NOT NULL default '',
  email varchar(255) NOT NULL default '',
  icqname varchar(6) NOT NULL default '',
  stdlocation int(11) NOT NULL default '0',
  stdtelescope int(11) NOT NULL default '0',
  password varchar(32) NOT NULL default '',
  role int(3) NOT NULL default '2',
  language varchar(255) NOT NULL default 'en',
  observationlanguage varchar(255) NOT NULL default 'en',
  usedLanguages varchar(255) NOT NULL default 'a:2:{i:0;s:2:"en";i:1;s:2:"nl";}',
  club varchar(20) NOT NULL default '',
  stdatlas int(3) NOT NULL default '0',
  UT BOOL default '1',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE locations (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  longitude float NOT NULL default '0',
  latitude float NOT NULL default '0',
  timezone varchar(30) NOT NULL default 'UTC',
  region varchar(255) NOT NULL default '',
  country varchar(255) NOT NULL default '',
  limitingMagnitude float NOT NULL default '-999',
  skyBackground float NOT NULL default '-999',
  observer varchar(255) default '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE instruments (
  id int(11) NOT NULL auto_increment,
  diameter float NOT NULL default '0',
  f int NOT NULL default '0',
  type int NOT NULL default '0',
  mag int default '',
  PRIMARY KEY  (id)
) ENGINE=MyISAM;

CREATE TABLE objects (
  name varchar(255) NOT NULL default '',
  alternative_name varchar(255) NOT NULL default '',
  type varchar(8) NOT NULL default '',
  con varchar(5) NOT NULL default '',
  ra float NOT NULL default '0',
  decl float NOT NULL default '0',
  mag float NOT NULL default '0',
  subr float NOT NULL default '0',
  diam1 float NOT NULL default '0',
  diam2 float NOT NULL default '0',
  pa int,
  catalogs varchar(6) NOT NULL default '',
  datasource varchar(15) NOT NULL default '',
  urano int NOT NULL default '0',
  urano_new int NOT NULL default '0',
  sky int NOT NULL default '0', 
  millenium varchar(9) NOT NULL default '',
  taki varchar(2) NOT NULL default '',
  PRIMARY KEY (name)
) ENGINE=MyISAM;

CREATE TABLE observations (
  id int(20) NOT NULL auto_increment,
  objectname varchar(255) NOT NULL,
  observerid varchar(255) NOT NULL,
  instrumentid int(11) NOT NULL default 0,
  locationid int(11) NOT NULL default 0,
  date int(15) NOT NULL default 0,
  time int(6) default -9999,
  description longtext not null default '',
  seeing int(1) default NULL,
  limmag float default NULL,
  visibility int(1) default 0,
  language varchar(255) NOT NULL default 'nl',
  primary key(id)
) ENGINE=MyISAM;

CREATE TABLE cometobservations (
  id int(20) NOT NULL auto_increment,
  objectid int(20) NOT NULL,
  observerid varchar(255) NOT NULL,
  instrumentid int(11) NOT NULL default 0,
  locationid int(11) NOT NULL default 0,
  date int(15) NOT NULL default 0,
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
) ENGINE=MyISAM;

CREATE TABLE cometobjects (
  id int(20) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  icqname varchar(11) NOT NULL default '',
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE ICQ_METHOD (
  id char(1) NOT NULL default 0,
  description varchar(100) NOT NULL,
  primary key(id)
) ENGINE=MyISAM;

CREATE TABLE ICQ_REFERENCE_KEY (
  id char(2) BINARY NOT NULL default '',
  description varchar(100) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

INSERT INTO locations VALUES('0', ' ', '-999', '-999', '', '');
INSERT INTO observers VALUES('admin', 'admin', '', '', '', '0', '0', 'b5cd0045a46b7068d3f0c0ce57dbfbce', '0', 'English', '', '0');
INSERT INTO instruments VALUES ('0', 'Naked eye', '7', '0', '0');
INSERT INTO ICQ_METHOD VALUES ("a", "Orange filter used on SOHO spacecraft with C2 and C3 coronagraphs");
INSERT INTO ICQ_METHOD VALUES ("B", "VBM (Van Biesbroeck-Bobrovnikoff-Meisel) or simple Out-Out method");
INSERT INTO ICQ_METHOD VALUES ("b", "VBM method using RCA #4549 image intensifier");
INSERT INTO ICQ_METHOD VALUES ("C", "Unfiltered total CCD magnitude");
INSERT INTO ICQ_METHOD VALUES ("c", "Unfiltered nuclear CCD magnitude");
INSERT INTO ICQ_METHOD VALUES ("D", "Cousins B filter");
INSERT INTO ICQ_METHOD VALUES ("E", "Extrafocal-Extinction (or Beyer) method");
INSERT INTO ICQ_METHOD VALUES ("e", "Extrafocal-Extinction (or Beyer) method using RCA #4549 image");
INSERT INTO ICQ_METHOD VALUES ("F", "Total magnitude using Meade CCD interference filter CM-500 Visible");
INSERT INTO ICQ_METHOD VALUES ("f", "Nuclear magnitude using Meade CCD interference filter CM-500 Visible");
INSERT INTO ICQ_METHOD VALUES ("G", "CCD magnitude with a Corion NR-400 minus-infrared filter");
INSERT INTO ICQ_METHOD VALUES ("g", "CCD magnitude with Gunn g filter");
INSERT INTO ICQ_METHOD VALUES ("H", "Cousins I filter with CCD");
INSERT INTO ICQ_METHOD VALUES ("I", "In-focus");
INSERT INTO ICQ_METHOD VALUES ("i", "Visual observation using an image intensifier");
INSERT INTO ICQ_METHOD VALUES ("J", "Kron-Cousins V filter employed");
INSERT INTO ICQ_METHOD VALUES ("j", "Kron-Cousins V filter employed (m2 estimate)");
INSERT INTO ICQ_METHOD VALUES ("K", "Clear filter used on SOHO spacecraft with C3 coronagraph");
INSERT INTO ICQ_METHOD VALUES ("k", "CCD magnitude with Cousins R filter");
INSERT INTO ICQ_METHOD VALUES ("L", "Photoelectric B");
INSERT INTO ICQ_METHOD VALUES ("l", "CCD magnitude with a Wratten 25 (red) filter");
INSERT INTO ICQ_METHOD VALUES ("M", "Modified-Out method");
INSERT INTO ICQ_METHOD VALUES ("N", "Magnitude of nucleus or condensation");
INSERT INTO ICQ_METHOD VALUES ("O", "Out-of-focus method");
INSERT INTO ICQ_METHOD VALUES ("o", "Visual extrafocal comparison using RCA #4549 image intensifier");
INSERT INTO ICQ_METHOD VALUES ("P", "Photographic");
INSERT INTO ICQ_METHOD VALUES ("p", "Photographic with Kodak 2415 film");
INSERT INTO ICQ_METHOD VALUES ("Q", "Out-out");
INSERT INTO ICQ_METHOD VALUES ("q", "R-band magnitude for nuclear condensation");
INSERT INTO ICQ_METHOD VALUES ("R", "Photoelectric R");
INSERT INTO ICQ_METHOD VALUES ("r", "CCD magnitude with Gunn r filter");
INSERT INTO ICQ_METHOD VALUES ("S", "VSS (Vsekhsvyatskii-Steavenson-Sidgwick) or In-Out method");
INSERT INTO ICQ_METHOD VALUES ("s", "VSS method using image intensifier");
INSERT INTO ICQ_METHOD VALUES ("T", "Magnitude estimated from a TV monitor");
INSERT INTO ICQ_METHOD VALUES ("t", "Total visual magnitude (historical obs. only)");
INSERT INTO ICQ_METHOD VALUES ("U", "Photoelectric U");
INSERT INTO ICQ_METHOD VALUES ("u", "Photoelectric U");
INSERT INTO ICQ_METHOD VALUES ("V", "CCD-derived V nuclear magnitudes");
INSERT INTO ICQ_METHOD VALUES ("v", "Photoelectric V");
INSERT INTO ICQ_METHOD VALUES ("W", "Photoelectric with filters to match visual");
INSERT INTO ICQ_METHOD VALUES ("w", "Photoelectric");
INSERT INTO ICQ_METHOD VALUES ("X", "Ortochromatic film + yellow filter");
INSERT INTO ICQ_METHOD VALUES ("Y", "CCD magnitude with Wratten No. 15 (yellow) filter");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("AE", "Planetary magnitudes from American Ephemeris and Nautical Almanac");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("AT", "Arizona-Tonantzintla Catalog");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("AU", "ASAS-3 V magnitudes");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("BR", "V magnitude sequence for stars in the Coma cluster of galaxies");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("BS", "Johnson V photometry by Brian Skiff");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("C", "Photovisual magnitudes from Cape Photographic Catalogue for 1950.0");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CA", "M44 standard sequence");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CD", "Open star cluster NGC 225 photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CE", "Open star cluster NGC 1647 photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CF", "Open star cluster NGC 2129 photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CG", "Open star cluster NGC 2422 (M47) photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CH", "Open star cluster NGC 6494 (M23) photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CI", "Open star cluster NGC 6823 photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CJ", "Open star cluster NGC 6910 photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CK", "Open star cluster NGC 7031 photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CL", "Photometry by Hoag et al. (1961)");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CM", "Photovisual and photoelectric-V magnitudes from Cape Mimeograms");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CN", "Open star cluster NGC 7235 photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CO", "UBV photometry for 39 stars in the range 11.7 < V < 18.7");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CR", "V magnitudes of 13 stars surrounding NGC 3627 (M66)");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("CS", "Catalogue of Stellar Identifications");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("D", "Dutch Comet Halley Handbook");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("E", "One of Everhart's 3 Selected Area charts");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("EA", "Selected Area 51:  From Everhart");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("EB", "Selected Area 57:  From Everhart");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("EC", "Selected Area 68:  From Everhart");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("FA", "V photometry by Harold Ables, U.S. Naval Observatory");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("GA", "Guide Star Photometric Catalog - I");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("GP", "Harvard E Regions");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HD", "Henry Draper Catalog");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HE", "Harvard E Regions");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HI", "Hipparcos Input Catalogue");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HJ", "Magnitudes in the Hipparcos photometric system, Hp");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HK", "H_p magnitudes from the Hipparcos Catalogue");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HN", "Arne Henden's photometric sequences");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HP", "Harvard Photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HR", "Harvard Revised Photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("HV", "Johnson V magnitudes from Hipparcos Catalogue");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("JT", "Cousins VRI magnitudes of stars in M67");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("L", "Landolt V Photoelectric Sequences");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("LA", "Landolt photoelectric sequences");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("LB", "Landolt sequences as published by Christian Buil");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("LC", "Landolt magnitude sequence for 33 stars near V1057 Cyg");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("MC", "Carlsberg Meridian Catalogue");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("ME", "V photometry by Tedesco, Tholen, and Zellner");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("MK", "V magnitudes for M67 in LE GUIDE PRATIQUE DE L'ASTRONOMIE CCD");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("MP", "McCormick Photovisual Sequence");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("MS", "McCormick Photovisual Sequences");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("MT", "Visual magnitudes of stars in M67");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("MV", "From Publ. Leander McCormick Obs.");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("NH", "North Polar Sequence as published by Henden and Kaitchuck");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("NN", "NGC 2129/6531/1342 cluster photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("NO", "U.S.N.O. Photoelectric Photometry Catalogue");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("NP", "North Polar Sequence");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("NS", "Magnitudes and Colors of Stars North of +80");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("OB", "Magnitudes for faint cluster stars");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("PA", "M45 sequence");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("PB", "Pleiades chart in Sky and Telescope 70");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("PC", "Pleiades sequence, Henden and Kaitchuck");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("PI", "IC 4665 sequence as found in Henden and Kaitchuck");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("RB", "Photoelectric Magnitudes and Colours of Southern Stars");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("RC", "Standard Magnitudes in the E Regions");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SD", "V magnitudes of members of the globular cluster M15");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SE", "V magnitudes of 134 stars of the II Persei Association");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SK", "Brian Skiff's compilation of magnitudes as part of the LONEOS project");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SM", "V magnitudes from A Visual Atlas of the Small Magellanic Cloud");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SP", "Skalnate-Pleso Atlas Catalog");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SS", "Various regions covering declination -60 deg to +10 deg");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SW", "Four half-degree fields with finder charts and UBV photometry");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("TA", "Comparison-star magnitudes from The Amateur Sky Survey");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("TG", "CCD magnitudes on the Thuan-Gunn system");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("TI", "Tycho Input Catalogue");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("TJ", "Tycho Catalogue Johnson V magnitudes");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("TK", "Tycho-2 Catalogue");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("TS", "Field of 13 stars");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("TT", "Tycho/Hipparcos Catalogue V_T magnitudes");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("VG", "Japanese variable-star charts edited by K. Gomi");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("Y", "Yale Bright Star Catalogue");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("YF", "Yale Bright Star Catalogue, fourth edition");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("YG", "Yale Bright Star Catalogue, fifth edition");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("AA", "A.A.V.S.O. Variable Star Atlas");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("AC", "Charts of the Amer. Assn. of Var. Star Observers");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("AP", "Atlas Photometrique des Constellations");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("AS", "AAVSO chart for M81 (NGC 3031) in Ursa Major");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("FD", "A photoelectric BVRI sequence in the field of NGC 6205 (M13)");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("FG", "A Field Guide to the Stars and Planets");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("LM", "A Visual Atlas of the Large Magellanic Cloud");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("ML", "V magnitudes on chart of Large Magellanic Cloud by Mati Morel");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("MM", "V magnitudes on chart of Small Magellanic Cloud by Mati Morel");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("OH", "From listing of bright stars in Observers' Handbook, R.A.S.C.");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("PK", "From the Soviet Program for Comet Halley");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("S", "Smithsonian Astrophysical Obs. Star Catalog");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SA", "M67 sequence by R. E. Schild");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("SC", "Sky Catalogue 2000.0");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("VB", "Variable star charts of the British Astr. Assn.");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("VF", "Variable star charts of the A.F.O.E.V. (France)");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("VN", "Variable star charts of the R.A.S. of New Zealand");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("W", "International Halley Watch (IHW) version of an unspecified AAVSO chart");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WA", "Special IHW version of AAVSO chart for SU Tauri");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WB", "Special IHW version of AAVSO chart for CZ Orionis");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WC", "Special IHW version of AAVSO chart for Y Tauri");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WD", "Special IHW version of AAVSO chart for V Tauri");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WE", "IHW version of AAVSO chart for X Sextantis");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WF", "IHW version of AAVSO chart for S Sextantis");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WG", "IHW version of AAVSO chart for SX Leonis");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WH", "Unspecified IHW charts");
INSERT INTO ICQ_REFERENCE_KEY VALUES ("WW", "B.A.A. Charts as published in the IHW Observers' Manual");
