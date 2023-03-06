<?php
/**
 * GUI to add new observations from an invalid xml file to the database.
 *
 * @category Deepsky/import
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
if ((!isset($inIndex)) || (!$inIndex)) {
    include '../../redirect.php';
} else {
    newObservationInvalidXml();
}

/**
 * Show the page where an invalid openAstronomyLog XML file can be imported.
 *
 * @return Nothing
 */
function newObservationInvalidXml()
{
    global $baseURL, $objPresentations;
    echo '<div id="main">';
    echo '<h3>' . _('Invalid XML file!') . '</h3>';
    echo '<hr />';
    print _('The XML file you are trying to import is invalid.  DeepskyLog can try to import this file, but it is possible that the import will not work.') . '<br />';
    print _('XML files from <a href="https://skysafariastronomy.com/">SkySafari 6</a> are invalid, but normally DeepskyLog will be able to import these files without problems.') . '<br />';

    // TODO: Let the user change the user name
    $xmlfile = $_FILES['xml']['tmp_name'];

    // Make a DomDocument from the file.
    $dom = new DomDocument();
    $xmlfile = realpath($xmlfile);

    // Load the xml document in the DOMDocument object
    $dom->Load($xmlfile);

    $searchNode = $dom->getElementsByTagName('observations');
    $version = $searchNode->item(0)->getAttribute('version');

    // Use the correct schema definition to check the xml file.
    $xmlschema = str_replace(
        ' ',
        '/',
        $searchNode->item(0)->getAttribute('xsi:schemaLocation')
    );

    // Use the correct oal definitions.
    if ($version == '2.0') {
        $xmlschema = $baseURL . 'xml/oal20/oal20.xsd';
    } elseif ($version == '2.1') {
        $xmlschema = $baseURL . 'xml/oal21/oal21.xsd';
    }

    $searchNode = $dom->getElementsByTagName('observers');

    $observer = $searchNode->item(0)->getElementsByTagName('observer');
    $observerArray = [];

    $id = '';
    $tmpObserverArray = [];
    // Get the id and the name of the observers in the comast file
    $oalid = $observer[0]->getAttribute('id');
    $name = htmlentities(
        ($observer[0]->getElementsByTagName('name')->item(0)->nodeValue),
        ENT_COMPAT,
        'UTF-8',
        0
    );
    $tmpObserverArray['name'] = $name;

    $surname = htmlentities(
        ($observer[0]->getElementsByTagName('surname')->item(0)->nodeValue),
        ENT_COMPAT,
        'UTF-8',
        0
    );
    $tmpObserverArray['surname'] = $surname;

    // Get the deepskyLog id if the account is given and the
    // name is www.deepskylog.org
    $observerid = $observer[0]->getElementsByTagName('account');
    $obsid = '';
    foreach ($observerid as $observerid) {
        if ($observerid->getAttribute('name') == 'www.deepskylog.org') {
            $obsid = $observerid->nodeValue;
        }
    }

    if ($obsid == '') {
        $obsid = $_SESSION['deepskylog_id'];
    }

    // Upload the file again
    $uniqName = uniqid('upload_', true);
    $tmpFile = "/tmp/$uniqName";

    move_uploaded_file($_FILES['xml']['tmp_name'], $tmpFile);

    echo '<hr />';
    echo '<form action="' . $baseURL . 'index.php?indexAction=addXmlObservations" enctype="multipart/form-data" method="post"><div>';
    echo '<input type="hidden" name="uniqName" value="' . $uniqName . '" />';
    echo '<input type="hidden" name="obsid" value="' . $obsid . '" />';
    echo '<input class="btn btn-danger" type="submit" name="change" value="' . _('Import invalid XML file!') . '" />';
    echo '</div></form>';
    echo '<hr />';
    echo '</div>';
}
