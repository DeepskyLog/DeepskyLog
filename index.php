<?php
/** 
 * Main entrance to DeepskyLog
 * 
 * PHP Version 7
 * 
 * @category Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <developers@deepskylog.be>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     https://www.deepskylog.org
 */
try {
    global $loggedUser, $toastMessage;
    $inIndex = true;
    $language = "nl";
    if (!array_key_exists('indexAction', $_GET) 
        && array_key_exists('indexAction', $_POST)
    ) {
        $_GET ['indexAction'] = $_POST ['indexAction'];
    }
    date_default_timezone_set('UTC');
    // Includes of all classes and assistance files
    include_once 'common/entryexit/globals.php'; 
    include_once 'common/entryexit/preludes.php';
    // Execution of all non-layout related instructions
    // (login, add objects to lists, etc.)
    include_once 'common/entryexit/instructions.php';

    // Determine the page to show
    $includeFile = $objUtil->utilitiesDispatchIndexAction();
    // Get data for the form, object data, observation data, etc.
    include_once 'common/entryexit/data.php'; 
    echo "<!DOCTYPE html>";
    echo "<html>";
    // HTML head
    include_once 'common/menu/head.php';
    echo "<body onkeydown=\"bodyOnKeyDown(event);\">";
    echo "<script type=\"text/javascript\" src=\"" . $baseURL 
        . "common/entryexit/globals.js\"></script>";
    echo "<script type=\"text/javascript\" src=\"" . $baseURL 
        . "lib/javascript/jsenvironment.js\"></script>";
    echo "<script type=\"text/javascript\" src=\"" . $baseURL 
        . "lib/javascript/ajaxbase.js\"></script>";

    // div1&2 = Page Title and welcome line - modules choices
    include_once 'common/menu/headmenu.php'; 

    // Container-fluid makes the container the full width of the screen.
    echo "<div class=\"container-fluid\">
    <div class=\"row\">";

    include_once 'common/entryexit/menu.php';
    echo "   <div class=\"col-sm-10 move\">";
    include_once $includeFile;
    echo "    </div>
    </div>
    </div>";

    echo "<div class=\"navbar navbar-default navbar-bottom\">
    <div class=\"container-fluid\">
    <div class=\"collapse navbar-collapse\" id=\"bs-example-navbar-collapse-1\">
    <ul class=\"nav navbar-nav navbar-left\">
    <p class=\"navbar-text\">" . COPYRIGHTINFO . " - " . DSLINFO 
        . " <a href=\"https://github.com/DeepskyLog/DeepskyLog/wiki/What's-New-in-DeepskyLog\">" 
        . VERSIONINFO . "</a>" . " - " . OBJECTINFO  . " - "
        . "<a href='" . $baseURL . "/index.php?indexAction=privacy'>" 
        . _("Privacy Policy") . "</a></p>
    </ul>";

    // Add fork me on GitHub button
    echo "<ul class=\"nav navbar-nav navbar-right\">";

    echo "<li><a href=\"https://github.com/DeepskyLog/DeepskyLog/fork\" rel=\"external\">
    <img src=\"" . $baseURL . "images/GitHub.png\" alt=\"Fork me on GitHub\"/>
    </a>
    </li>";

    // Add logo for oal
    echo "<li><a href=\"https://github.com/openastronomylog/openastronomylog\" rel=\"external\">";
    echo "<img width=\"24\" height=\"24\" src=\"" . $baseURL 
        . "styles/images/oallogo_small.jpg\" alt=\"OAL\"/>";
    echo "</a></li>";

    // Add link to facebook page
    echo "<li><a href=\"https://www.facebook.com/deepskylog\" style=\"text-decoration: none; color: #333;\">" 
        . "<img src=\"" . $baseURL 
        . "img/FB-f-Logo__blue_29.png\" width=\"24\" height=\"24\" style=\"border: 0;\"/>" 
        . "</a></li>";

    // Add link to instagram page
    echo "<li><a href=\"https://www.instagram.com/deepskylog.be\" style=\"text-decoration: none; color: #333;\">" 
        . "<img src=\"" . $baseURL 
        . "img/instagram-logo.png\" width=\"24\" height=\"24\" style=\"border: 0;\"/>" 
        . "</a></li>";

    // Add link to twitter account
    echo "<li><a href=\"https://twitter.com/DeepskyLog\"><img width=\"24\" height=\"24\" src=\"" 
        . $baseURL . "img/Twitter_logo_blue.png\"></a></li>";

    // Add link to youtube channel
    echo "<li><a href=\"https://www.youtube.com/channel/UC66H7w2Fl9q3krRy_tHRK5g\">" 
        . "<img height=\"24\" src=\"" . $baseURL 
        . "img/youtube_social_icon_red.png\"></a></li>";

    echo "</ul>";
    echo "  </div>
    </div>
    </div>";
} catch ( Exception $e ) {
    $entryMessage .= "<p>DeepskyLog encountered a problem. Could you please report it to the Developers?</p>";
    $entryMessage .= "<p>Report problem with error message: " 
        . $e->getMessage() . "</p>";
    $entryMessage .= "<p>You can report the problem by sending an email to developers@deepskylog.be.</p>";
    $entryMessage .= "<p>Thank you.</p>";
    // EMAIL developers with error codes
}
require_once 'lib/introductionTour.php';
echo "<script type=\"text/javascript\">";
if ($loadAtlasPage) {
    echo "atlasFillPage();";
}
if ($includeFile == 'deepsky/content/view_catalogs.php') {
    echo "view_catalogs('','');";
}
echo "</script>";

// Modal to make a new list
if ($_SESSION['module'] == 'deepsky' && $loggedUser) {
    echo "<div class=\"modal fade\" id=\"addList\">
    <div class=\"modal-dialog\">
    <div class=\"modal-content\">
    <div class=\"modal-header\">
    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">" 
    . "<span aria-hidden=\"true\">&times;</span></button>
    <h4 class=\"modal-title\">" . _("Create a new observing list") . "</h4>
    </div>
    <div class=\"modal-body\">
    <!-- Ask for the name of the list. -->
    <h1 class=\"text-center login-title\">" . _("Name for the new observing list") . "</h1>
    <form action=\"".$baseURL."index.php?indexAction=listaction\">
    <input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />
    <input type=\"text\" name=\"addlistname\" class=\"form-control\" required autofocus>
    <br /><br />
    <input type=\"checkbox\" name=\"PublicList\" value=\"1\" />&nbsp;" 
    . _("Make this list a public list") . "
    </div>
    <div class=\"modal-footer\">
    <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>
    <input class=\"btn btn-success\" type=\"submit\" name=\"addList\" value=\"" 
    . _("Create list") . "\" />
    </form>
    </div>
    </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->";
}

// dispays $entryMessage if any
if (isset($entryMessage) && $entryMessage) {
    echo "<div class=\"modal fade\" id=\"errorModal\" tabindex=\"-1\">
    <div class=\"modal-dialog\">
    <div class=\"modal-content\">
    <div class=\"modal-header\">
    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>
    <h4 class=\"modal-title\" id=\"myModalLabel\">DeepskyLog</h4>
    </div>
    <div class=\"modal-body\">" . $entryMessage . "
    </div>
    </div>
    </div>
    </div>";

    echo "<script type=\"text/javascript\">";
    echo "$(document).ready(function() {
        $('#errorModal').modal('show')
    });";
    echo "</script>";
}

// Show the cookies warning
if (strcmp($loggedUser, "") == 0) {
    echo '<!-- Begin Cookie Consent plugin by Silktide - http://silktide.com/cookieconsent -->
    <script type="text/javascript">
        window.cookieconsent_options = {"message":"This website uses cookies to ensure you get the best experience on our website","dismiss":"Got it!","learnMore":"More info","link":null,"theme":"dark-bottom"};
    </script>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.9/cookieconsent.min.js"></script>
    <!-- End Cookie Consent plugin -->';
}

echo '<script>
$(document).ready(function() {
    $("select").select2();
});
</script>';

// Show the toast message if the message is available.
if ($toastMessage != "") {
    echo '<script>
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-full-width",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
    toastr["success"]("' . $toastMessage . '")

    </script>';
}

echo '<script>
                $("textarea").maxlength({
                    alwaysShow: true
                });
            </script>';

echo "<link rel=\"stylesheet\" href=\"" . $baseURL . "styles/lightbox.css\" />
        <script src=\"" . $baseURL . "lib/javascript/lightbox.min.js\"></script>";

echo "</body>";
echo "</html>";
?>
