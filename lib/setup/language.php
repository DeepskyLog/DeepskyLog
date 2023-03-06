<?php
/**
 * The language class collects all functions needed to work with different languages.
 *
 * PHP Version 7
 *
 * @category Utilities/Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */

global $inIndex;
if ((!isset($inIndex))||(!$inIndex)) {
    include "../../redirect.php";
}

/**
 * All functions needed to work with different languages.
 *
 * @category Utilities/Deepsky
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
class Language
{
    /**
     * Returns a list of all available translations.
     *
     * @return array A list of all available translations
     */
    function getLanguages()
    {
        $mydir = opendir("locale/");
        $exclude = array("messages.pot", ".", "..", ".DS_Store");
        $langs = array();

        while ($fn=readdir($mydir)) {
            if (!in_array($fn, $exclude)) {
                $langs[] = $fn;
            }
        }
        $langs[] = "en";
        closedir($mydir);
        for ($i=0;$i<count($langs);$i++) {
            // $langs is now a list of all available translations (en, nl, de, ...)
            $countrylist = $this->getAllLanguages($langs[$i]);
            $languages[$langs[$i]] = $countrylist[$langs[$i]];
        }
        return $languages;
    }

    /**
     * Returns all the available languages, in the given language.
     *
     * @param string $cur_language The current language.
     *
     * @return array A list of all available translations, in the given language.
     */
    function getAllLanguages($cur_language)
    {
        global $instDir;
        $xml = simplexml_load_file($instDir."lib/setup/language/languages.xml");
        $lang = $xml->lang;
        $countrylist = array();
        $country = "name_".$cur_language;
        foreach ($lang as $item=>$data) {
            $countrylist[sprintf($data->attributes()->code)]
                = sprintf($data->attributes()->$country);
        }
        asort($countrylist);
        return $countrylist;
    }

    /**
     * Returns all keys of the available languages, in the given language.
     *
     * @param string $cur_language The current language.
     *
     * @return array A list of all keys of the available translations,
     *               in the given language.
     */
    function getLanguageKeys($cur_language)
    {
        $xml = simplexml_load_file("lib/setup/language/languages.xml");
        $lang = $xml->lang;
        $countrylist = array();
        $country="name_".$cur_language;
        foreach ($lang as $item=>$data) {
            $countrylist[]=sprintf($data->attributes()->code);
        }
        return $countrylist;
    }

    /**
     * Sets the locale for DeepskyLog. This sets the language for the dates!
     *
     * @return None
     */
    function setLocale()
    {
        // When adding a new language, also add the correct locale here!
        // LOCALES: NL: nl_NL, EN: en_US, FR: fr_FR,
        //          DE: de_DE, ES: es_ES, SE: sv_SE
        if (strcmp($_SESSION['lang'], "nl") == 0) {
            setlocale(LC_ALL, 'nl_NL');
        } else if (strcmp($_SESSION['lang'], "fr") == 0) {
            setlocale(LC_ALL, 'fr_FR');
        } else if (strcmp($_SESSION['lang'], "de") == 0) {
            setlocale(LC_ALL, 'de_DE');
        } else if (strcmp($_SESSION['lang'], "es") == 0) {
            setlocale(LC_ALL, 'es_ES');
        } else if (strcmp($_SESSION['lang'], "sv") == 0) {
            setlocale(LC_ALL, 'sv_SE');
        } else {
            setlocale(LC_ALL, 'en_EN');
        }
    }
}
?>
