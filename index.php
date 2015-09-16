<?php
// index.php
// main entrance to DeepskyLog
try {
	global $loggedUser;
	$inIndex = true;
	$language = "nl";
	if (! array_key_exists ( 'indexAction', $_GET ) && array_key_exists ( 'indexAction', $_POST ))
		$_GET ['indexAction'] = $_POST ['indexAction'];
	date_default_timezone_set ( 'UTC' );
	require_once 'common/entryexit/globals.php'; // Includes of all classes and assistance files
	require_once 'common/entryexit/preludes.php'; // Includes of all classes and assistance files
	require_once 'common/entryexit/instructions.php'; // Execution of all non-layout related instructions (login, add objects to lists, etc.)

	$includeFile = $objUtil->utilitiesDispatchIndexAction (); // Determine the page to show
	require_once 'common/entryexit/data.php'; // Get data for the form, object data, observation data, etc.
	echo "<!DOCTYPE html>";
	echo "<html>";
	require_once 'common/menu/head.php'; // HTML head
	echo "<body onkeydown=\"bodyOnKeyDown(event);\">";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "common/entryexit/globals.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/jsenvironment.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/wz_tooltip.js\"></script>";
	echo "<script type=\"text/javascript\" src=\"" . $baseURL . "lib/javascript/ajaxbase.js\"></script>";
	echo "<script type=\"text/javascript\"
              src=\"http://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js\"></script>";
	require_once 'common/menu/headmenu.php'; // div1&2 = Page Title and welcome line - modules choices

	// Container-fluid makes the container the full width of the screen.
	echo "<div class=\"container-fluid\">
         <div class=\"row\">";

	require_once 'common/entryexit/menu.php';
	echo "   <div class=\"col-sm-10\">";
	require_once $includeFile;
	echo "    </div>
  		     </div>
  		    </div>";

	echo "<div class=\"navbar navbar-default navbar-fixed-bottom\">
  		   <div class=\"container-fluid\">
  		    <div class=\"collapse navbar-collapse\" id=\"bs-example-navbar-collapse-1\">
  		     <ul class=\"nav navbar-nav navbar-left\">
  		  		<p class=\"navbar-text\">" . $copyrightInfo . " - " . $vvsInfo . $dslInfo . $versionInfo . " - " . $objectInfo . "</p>
  		     </ul>";

	// Add fork me on GitHub button
	echo "<ul class=\"nav navbar-nav navbar-right\">";

	echo "<li><a href=\"https://github.com/DeepskyLog/DeepskyLog/fork\" rel=\"external\">
  		      <img src=\"" . $baseURL . "images/GitHub.png\" alt=\"Fork me on GitHub\"/>
		    </a>
  		</li>";

	// Add logo for oal
	echo "<li><a href=\"http://groups.google.com/group/openastronomylog\" rel=\"external\">";
	echo "<img width=\"24\" height=\"24\" src=\"" . $baseURL . "styles/images/oallogo_small.jpg\" alt=\"OAL\"/>";
	echo "</a></li>";

	// Add link to google+ page
	echo "<li><a href=\"https://plus.google.com/+DeepskylogOrg/\" style=\"text-decoration: none; color: #333;\"><img src=\"https://ssl.gstatic.com/images/icons/gplus-16.png\" width=\"24\" height=\"24\" style=\"border: 0;\"/></a></li>";

	// Add link to facebook page
	echo "<li><a href=\"https://www.facebook.com/deepskylog\" style=\"text-decoration: none; color: #333;\"><img src=\"" . $baseURL . "img/FB-f-Logo__blue_29.png\" width=\"24\" height=\"24\" style=\"border: 0;\"/></a></li>";

	// Add link to twitter account
	echo "<li><a href=\"https://twitter.com/DeepskyLog\"><img width=\"24\" height=\"24\" src=\"" . $baseURL . "img/Twitter_logo_blue.png\"></a></li>";

	echo "</ul>";
	echo "  </div>
  		   </div>
  		  </div>";
} catch ( Exception $e ) {
	$entryMessage .= "<p>DeepskyLog encountered a problem. Could you please report it to the Developers?</p>";
	$entryMessage .= "<p>Report problem with error message: " . $e->getMessage () . "</p>";
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
if ($_SESSION ['module'] == 'deepsky' && $loggedUser) {
	echo "<div class=\"modal fade\" id=\"addList\">
        <div class=\"modal-dialog\">
         <div class=\"modal-content\">
          <div class=\"modal-header\">
           <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
           <h4 class=\"modal-title\">" . LangAddObservingList . "</h4>
          </div>
          <div class=\"modal-body\">
           <!-- Ask for the name of the list. -->
           <h1 class=\"text-center login-title\">" . LangNameNewList . "</h1>
            <form action=\"".$baseURL."index.php?indexAction=listaction\">
             <input type=\"hidden\" name=\"indexAction\" value=\"listaction\" />
             <input type=\"text\" name=\"addlistname\" class=\"form-control\" required autofocus>
             <br /><br />
             <input type=\"checkbox\" name=\"PublicList\" value=\"1\" />&nbsp;" . LangToListPublic . "
            </div>
            <div class=\"modal-footer\">
            <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>
            <input class=\"btn btn-success\" type=\"submit\" name=\"addList\" value=\"" . LangAddList . "\" />
		   </form>
          </div>
         </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
       </div><!-- /.modal -->";
}

if (isset ( $entryMessage ) && $entryMessage) { // dispays $entryMessage if any
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
echo "</body>";
echo "</html>";
?>
