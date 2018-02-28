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
 * @link     http://www.deepslylog.org
 */
global $inIndex;
if ((!isset($inIndex)) || (!$inIndex)) {
    include "../../redirect.php";
}

define("VERSIONINFO", "2018.3");
define("COPYRIGHTINFO", "&copy;2004&nbsp;-&nbsp;2018 ,&nbsp;DeepskyLog developers");

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

?>
