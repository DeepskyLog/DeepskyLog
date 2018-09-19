<?php
/** 
 * Contains a series of definitions
 * 
 * PHP version 7
 * 
 * @category Utilities
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
global $inIndex;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}

define("VERSIONINFO", "2018.9");
define("COPYRIGHTINFO", "©2004 - 2018, DeepskyLog developers");

define("ATLASOVERVIEWZOOM", 17);
define("ATLASLOOKUPZOOM", 18);
define("ATLASDETAILZOOM", 20);

define("ROLEADMIN", 0);
define("ROLEUSER", 1);
define("ROLEWAITLIST", 2);
define("ROLECOMETADMIN", 4);

define("INSTRUMENTOTHER", - 1);
define("INSTRUMENTNAKEDEYE", 0);
define("INSTRUMENTBINOCULARS", 1);
define("INSTRUMENTREFRACTOR", 2);
define("INSTRUMENTREFLECTOR", 3);
define("INSTRUMENTFINDERSCOPE", 4);
define("INSTRUMENTREST", 5);
define("INSTRUMENTCASSEGRAIN", 6);
define("INSTRUMENTKUTTER", 7);
define("INSTRUMENTMAKSUTOV", 8);
define("INSTRUMENTSCHMIDTCASSEGRAIN", 9);

define("FILTEROTHER", 0);
define("FILTERBROADBAND", 1);
define("FILTERNARROWBAND", 2);
define("FILTEROIII", 3);
define("FILTERHBETA", 4);
define("FILTERHALPHA", 5);
define("FILTERCOLOR", 6);
define("FILTERNEUTRAL", 7);
define("FILTERCORRECTIVE", 8);
define("FILTERCOLORLIGHTRED", "1");
define("FILTERCOLORRED", "2");
define("FILTERCOLORDEEPRED", "3");
define("FILTERCOLORORANGE", "4");
define("FILTERCOLORLIGHTYELLOW", "5");
define("FILTERCOLORDEEPYELLOW", "6");
define("FILTERCOLORYELLOW", "7");
define("FILTERCOLORYELLOWGREEN", "8");
define("FILTERCOLORLIGHTGREEN", "9");
define("FILTERCOLORGREEN", "10");
define("FILTERCOLORMEDIUMBLUE", "11");
define("FILTERCOLORPALEBLUE", "12");
define("FILTERCOLORBLUE", "13");
define("FILTERCOLORDEEPBLUE", "14");
define("FILTERCOLORDEEPVIOLET", "15");

//Cluster types
$GLOBALS['ClusterTypeA'] = _("Rich, poorly conc., diff. magnit., loose");
$GLOBALS['ClusterTypeB'] = _("Poorly conc., diff. magnit., loose");
$GLOBALS['ClusterTypeC'] = _("Poor, poorly conc., diff. magnit., loose");
$GLOBALS['ClusterTypeD'] = _("Rich, concentrated, loose");
$GLOBALS['ClusterTypeE'] = _("Rich, poorly conc., same magnit., loose");
$GLOBALS['ClusterTypeF'] = _("Only same color or magnit., not loose");
$GLOBALS['ClusterTypeG'] = _("High magnif. compact cl., rich, weak");
$GLOBALS['ClusterTypeH'] = _("Very poor, not loose at all");
$GLOBALS['ClusterTypeI'] = _("High magnif. compact cl., poor, weak");
$GLOBALS['ClusterTypeX'] = _("No classification possible");


//Visibility
$GLOBALS['Visibility1'] = _("Very simple, prominent object");
$GLOBALS['Visibility2'] = _("Object easily percepted with direct vision");
$GLOBALS['Visibility3'] = _("Object perceptable with direct vision");
$GLOBALS['Visibility4'] = _("Averted vision required to percept object");
$GLOBALS['Visibility5'] = _("Object barely perceptable with averted vision");
$GLOBALS['Visibility6'] = _("Perception of object is very questionable");
$GLOBALS['Visibility7'] = _("Object definitely not seen");

// Visibility for resolved open clusters
$GLOBALS['VisibilityOC1'] = _("Very prominent and very beautiful cluster");
$GLOBALS['VisibilityOC2'] = _("Prominent and beautiful cluster");
$GLOBALS['VisibilityOC3'] = _("Conspicuously seen cluster");
$GLOBALS['VisibilityOC4'] = _("Cluster hardly attracts attention");
$GLOBALS['VisibilityOC5'] = _("Very unconspicuously, easily overlooked when slewing");
$GLOBALS['VisibilityOC6'] = _("Questionable sighting; star density similar to neighborhood");
$GLOBALS['VisibilityOC7'] = _("Virtually no stars at catalog position");

// Visibility for double stars
$GLOBALS['VisibilityDS1'] = _("Binary can be resolved");
$GLOBALS['VisibilityDS2'] = _("Binary appears as 8");
$GLOBALS['VisibilityDS3'] = _("Binary cannot be resolved");

//Seeing definitions
$GLOBALS['Seeing0'] = "-----";
$GLOBALS['Seeing1'] = _("Excellent");
$GLOBALS['Seeing2'] = _("Good");
$GLOBALS['Seeing3'] = _("Moderate");
$GLOBALS['Seeing4'] = _("Poor");
$GLOBALS['Seeing5'] = _("Bad");

//Names of Months
$GLOBALS['Month1'] = _("January");
$GLOBALS['Month2'] = _("February");
$GLOBALS['Month3'] = _("March");
$GLOBALS['Month4'] = _("April");
$GLOBALS['Month5'] = _("May");
$GLOBALS['Month6'] = _("June");
$GLOBALS['Month7'] = _("July");
$GLOBALS['Month8'] = _("August");
$GLOBALS['Month9'] = _("September");
$GLOBALS['Month10'] = _("October");
$GLOBALS['Month11'] = _("November");
$GLOBALS['Month12'] = _("December");

define("MONTH1", $GLOBALS['Month1']);
define("MONTH2", $GLOBALS['Month2']);
define("MONTH3", $GLOBALS['Month3']);
define("MONTH4", $GLOBALS['Month4']);
define("MONTH5", $GLOBALS['Month5']);
define("MONTH6", $GLOBALS['Month6']);
define("MONTH7", $GLOBALS['Month7']);
define("MONTH8", $GLOBALS['Month8']);
define("MONTH9", $GLOBALS['Month9']);
define("MONTH10", $GLOBALS['Month10']);
define("MONTH11", $GLOBALS['Month11']);
define("MONTH12", $GLOBALS['Month12']);

$GLOBALS['Month1Short'] = _("Jan");
$GLOBALS['Month2Short'] = _("Feb");
$GLOBALS['Month3Short'] = _("Mar");
$GLOBALS['Month4Short'] = _("Apr");
$GLOBALS['Month5Short'] = _("May");
$GLOBALS['Month6Short'] = _("Jun");
$GLOBALS['Month7Short'] = _("Jul");
$GLOBALS['Month8Short'] = _("Aug");
$GLOBALS['Month9Short'] = _("Sep");
$GLOBALS['Month10Short'] = _("Oct");
$GLOBALS['Month11Short'] = _("Nov");
$GLOBALS['Month12Short'] = _("Dec");

// Names of Atlases
$GLOBALS['AtlasNameurano'] = "Uranometria";
$GLOBALS['AtlasNameurano_new'] = "Uranometria (2nd edition)";
$GLOBALS['AtlasNamesky'] = "Sky Atlas";
$GLOBALS['AtlasNamemilleniumbase'] = "Millenium Star Atlas";
$GLOBALS['AtlasNametaki'] = "Taki Atlas";
$GLOBALS['AtlasNamepsa'] = "Pocket Sky Atlas";
$GLOBALS['AtlasNametorresB'] = "Triatlas B (Torres)";
$GLOBALS['AtlasNametorresBC'] = "Triatlas BC (Torres)";
$GLOBALS['AtlasNametorresC'] = "Triatlas C (Torres)";
$GLOBALS['AtlasNameDSLDL'] = "Deepskylog Detail Landscape";
$GLOBALS['AtlasNameDSLDP'] = "Deepskylog Detail Portrait";
$GLOBALS['AtlasNameDSLLL'] = "Deepskylog Lookup Landscape";
$GLOBALS['AtlasNameDSLLP'] = "Deepskylog Lookup Portrait";
$GLOBALS['AtlasNameDSLOL'] = "Deepskylog Overview Landscape";
$GLOBALS['AtlasNameDSLOP'] = "Deepskylog Overview Portrait";
$GLOBALS['AtlasNameDeepskyHunter'] = "Deep Sky Hunter";
$GLOBALS['AtlasNameInterstellarum'] = "Interstellarum Deep Sky Atlas";

// Types of Observations
$GLOBALS['ASTER'] = _("Asterism");
$GLOBALS['BRTNB'] = _("Bright nebula");
$GLOBALS['CLANB'] = _("Cluster with nebulosity");
$GLOBALS['DS'] = _("Double Star");
$GLOBALS['DRKNB'] = _("Dark nebula");
$GLOBALS['EMINB'] = _("Emission nebula");
$GLOBALS['ENRNN'] = _("Emission and Reflection nebula");
$GLOBALS['ENSTR'] = _("Emission nebula around a star");
$GLOBALS['GALCL'] = _("Galaxy cluster");
$GLOBALS['GALXY'] = _("Galaxy");
$GLOBALS['GLOCL'] = _("Globular cluster");
$GLOBALS['GXADN'] = _("Diffuse nebula in galaxy");
$GLOBALS['GXAGC'] = _("Globular cluster in galaxy");
$GLOBALS['GACAN'] = _("Cluster with nebulosity in galaxy");
$GLOBALS['HII'] = _("H-II");
$GLOBALS['LMCCN'] = _("Cluster with nebulosity in LMC");
$GLOBALS['LMCDN'] = _("Diffuse nebula in LMC");
$GLOBALS['LMCGC'] = _("Globular cluster in LMC");
$GLOBALS['LMCOC'] = _("Open cluster in LMC");
$GLOBALS['NONEX'] = _("Nonexistent");
$GLOBALS['OPNCL'] = _("Open cluster");
$GLOBALS['PLNNB'] = _("Planetary nebula");
$GLOBALS['REFNB'] = _("Reflection nebula");
$GLOBALS['RNHII'] = _("Reflection nebula and H-II");
$GLOBALS['SMCCN'] = _("Cluster with nebulosity in SMC");
$GLOBALS['SMCDN'] = _("Diffuse nebula in SMC");
$GLOBALS['SMCGC'] = _("Globular cluster in SMC");
$GLOBALS['SMCOC'] = _("Open cluster in SMC");
$GLOBALS['SNOVA'] = _("Supernova");
$GLOBALS['SNREM'] = _("Supernova remnant");
$GLOBALS['STNEB'] = _("Nebula around star");
$GLOBALS['QUASR'] = _("Quasar");
$GLOBALS['WRNEB'] = _("Wolf Rayet nebula");
$GLOBALS['AA1STAR'] = _("Star");
$GLOBALS['AA3STAR'] = _("3 stars");
$GLOBALS['AA4STAR'] = _("4 stars");
$GLOBALS['AA8STAR'] = _("8 stars");
$GLOBALS['REST'] = _("Rest");


// Types of Observations
$GLOBALS['argoASTER'] = "ASTERISM";
$GLOBALS['argoBRTNB'] = "BRIGHT";
$GLOBALS['argoCLANB'] = "NEBULA";
$GLOBALS['argoDRKNB'] = "DARK";
$GLOBALS['argoEMINB'] = "NEBULA";
$GLOBALS['argoENRNN'] = "NEBULA";
$GLOBALS['argoENSTR'] = "NEBULA";
$GLOBALS['argoGALCL'] = "GALAXY CL";
$GLOBALS['argoGALXY'] = "GALAXY";
$GLOBALS['argoGLOCL'] = "GLOBULAR";
$GLOBALS['argoGXADN'] = "NEBULA";
$GLOBALS['argoGXAGC'] = "GLOBULAR";
$GLOBALS['argoGACAN'] = "NEBULA";
$GLOBALS['argoHII'] = "NEBULA";
$GLOBALS['argoLMCCN'] = "NEBULA";
$GLOBALS['argoLMCDN'] = "NEBULA";
$GLOBALS['argoLMCGC'] = "GLOBULAR";
$GLOBALS['argoLMCOC'] = "OPEN";
$GLOBALS['argoNONEX'] = "USER";
$GLOBALS['argoOPNCL'] = "OPEN";
$GLOBALS['argoPLNNB'] = "PLANETARY";
$GLOBALS['argoREFNB'] = "NEBULA";
$GLOBALS['argoRNHII'] = "NEBULA";
$GLOBALS['argoSMCCN'] = "OPEN";
$GLOBALS['argoSMCDN'] = "NEBULA";
$GLOBALS['argoSMCGC'] = "GLOBULAR";
$GLOBALS['argoSMCOC'] = "OPEN";
$GLOBALS['argoSNREM'] = "NEBULA";
$GLOBALS['argoSTNEB'] = "NEBULA";
$GLOBALS['argoQUASR'] = "USER";
$GLOBALS['argoWRNEB'] = "NEBULA";
$GLOBALS['argoAA1STAR'] = "STAR";
$GLOBALS['argoDS'] = "DOUBLE";
$GLOBALS['argoAA3STAR'] = "TRIPLE";
$GLOBALS['argoAA4STAR'] = "ASTERISM";
$GLOBALS['argoAA8STAR'] = "ASTERISM";

// Constellations
$GLOBALS['AND'] = "Andromeda";
$GLOBALS['ANT'] = "Antlia";
$GLOBALS['APS'] = "Apus";
$GLOBALS['AQR'] = "Aquarius";
$GLOBALS['AQL'] = "Aquila";
$GLOBALS['ARA'] = "Ara";
$GLOBALS['ARI'] = "Aries";
$GLOBALS['AUR'] = "Auriga";
$GLOBALS['BOO'] = "Bootes";
$GLOBALS['CAE'] = "Caelum";
$GLOBALS['CAM'] = "Camelopardalis";
$GLOBALS['CNC'] = "Cancer";
$GLOBALS['CVN'] = "Canes Venatici";
$GLOBALS['CMA'] = "Canis Major";
$GLOBALS['CMI'] = "Canis Minor";
$GLOBALS['CAP'] = "Capricornus";
$GLOBALS['CAR'] = "Carina";
$GLOBALS['CAS'] = "Cassiopeia";
$GLOBALS['CEN'] = "Centaurus";
$GLOBALS['CEP'] = "Cepheus";
$GLOBALS['CET'] = "Cetus";
$GLOBALS['CHA'] = "Chamaeleon";
$GLOBALS['CIR'] = "Circinus";
$GLOBALS['COL'] = "Columba";
$GLOBALS['COM'] = "Coma Berenices";
$GLOBALS['CRA'] = "Corona Australis";
$GLOBALS['CRB'] = "Corona Borealis";
$GLOBALS['CRV'] = "Corvus";
$GLOBALS['CRT'] = "Crater";
$GLOBALS['CRU'] = "Crux";
$GLOBALS['CYG'] = "Cygnus";
$GLOBALS['DEL'] = "Delphinus";
$GLOBALS['DOR'] = "Dorado";
$GLOBALS['DRA'] = "Draco";
$GLOBALS['EQU'] = "Equuleus";
$GLOBALS['ERI'] = "Eridanus";
$GLOBALS['FOR'] = "Fornax";
$GLOBALS['GEM'] = "Gemini";
$GLOBALS['GRU'] = "Grus";
$GLOBALS['HER'] = "Hercules";
$GLOBALS['HOR'] = "Horologium";
$GLOBALS['HYA'] = "Hydra";
$GLOBALS['HYI'] = "Hydrus";
$GLOBALS['IND'] = "Indus";
$GLOBALS['LAC'] = "Lacerta";
$GLOBALS['LEO'] = "Leo";
$GLOBALS['LMI'] = "Leo Minor";
$GLOBALS['LEP'] = "Lepus";
$GLOBALS['LIB'] = "Libra";
$GLOBALS['LUP'] = "Lupus";
$GLOBALS['LYN'] = "Lynx";
$GLOBALS['LYR'] = "Lyra";
$GLOBALS['MEN'] = "Mensa";
$GLOBALS['MIC'] = "Microscopium";
$GLOBALS['MON'] = "Monoceros";
$GLOBALS['MUS'] = "Musca";
$GLOBALS['NOR'] = "Norma";
$GLOBALS['OCT'] = "Octans";
$GLOBALS['OPH'] = "Ophiuchus";
$GLOBALS['ORI'] = "Orion";
$GLOBALS['PAV'] = "Pavo";
$GLOBALS['PEG'] = "Pegasus";
$GLOBALS['PER'] = "Perseus";
$GLOBALS['PHE'] = "Phoenix";
$GLOBALS['PIC'] = "Pictor";
$GLOBALS['PSC'] = "Pisces";
$GLOBALS['PSA'] = "Pisces Austrinus";
$GLOBALS['PUP'] = "Puppis";
$GLOBALS['PYX'] = "Pyxis";
$GLOBALS['RET'] = "Reticulum";
$GLOBALS['SGE'] = "Sagitta";
$GLOBALS['SGR'] = "Sagittarius";
$GLOBALS['SCO'] = "Scorpius";
$GLOBALS['SCL'] = "Sculptor";
$GLOBALS['SCT'] = "Scutum";
$GLOBALS['SER'] = "Serpens";
$GLOBALS['SEX'] = "Sextans";
$GLOBALS['TAU'] = "Taurus";
$GLOBALS['TEL'] = "Telescopium";
$GLOBALS['TRA'] = "Triangulum Australe";
$GLOBALS['TRI'] = "Triangulum";
$GLOBALS['TUC'] = "Tucana";
$GLOBALS['UMA'] = "Ursa Major";
$GLOBALS['UMI'] = "Ursa Minor";
$GLOBALS['VEL'] = "Vela";
$GLOBALS['VIR'] = "Virgo";
$GLOBALS['VOL'] = "Volans";
$GLOBALS['VUL'] = "Vulpecula";

$GLOBALS['deepsky'] = _("Deepsky");
$GLOBALS['comets'] = _("Comets");
?>