<?php
// headmenu.php
// VVS Header and our 3 dropdown boxes if logged in
if ((! isset ( $inIndex )) || (! $inIndex))
	include "../../redirect.php";
else
	headmenu ();
function headmenu() {
	global $baseURL, $leftmenu, $loggedUser, $modules, $thisDay, $thisMonth, $thisYear, $topmenu, $register, $objUtil, $objLocation, $objInstrument, $objObserver, $objMessages, $instDir, $objDatabase;

	// Here, we set the drop down menu
	// Make the drop down menu
	echo "<nav class=\"navbar navbar-inverse navbar-fixed-top\" role=\"navigation\">
        <div class=\"container-fluid\">
          <div class=\"navbar-header\">
            <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\"#bs-example-navbar-collapse-1\">
              <span class=\"sr-only\">Toggle navigation</span>
              <span class=\"icon-bar\"></span>
              <span class=\"icon-bar\"></span>
              <span class=\"icon-bar\"></span>
            </button>
            <a class=\"navbar-brand\" href=\"" . $baseURL . "index.php?title=Home\"><div class=\"hidden-sm\">DeepskyLog</div><span class=\"glyphicon glyphicon-home visible-sm\"></span></a>
          </div>
		  <div class=\"collapse navbar-collapse main-nav\" id=\"bs-example-navbar-collapse-1\">";

	require_once $instDir . $_SESSION ['module'] . '/menu/search.php'; // Overview MENU
	if ($_SESSION ['module'] == 'deepsky') {
		require_once $instDir . $_SESSION ['module'] . '/menu/quickpickDropDown.php'; // Search MENU
	}
	if ($loggedUser) {
		require_once $instDir . $_SESSION ['module'] . '/menu/change.php'; // CHANGE MENU
		if (array_key_exists ( 'admin', $_SESSION ) && ($_SESSION ['admin'] == 'yes'))
			require_once $instDir . 'common/menu/admin.php'; // ADMINISTRATION MENU
	}
	if ($_SESSION ['module'] == 'deepsky')
		require_once $instDir . 'deepsky/menu/downloads.php';
	require_once $instDir . 'common/menu/help.php'; // HELP MENU

	// Select the modules
	echo "<ul class=\"nav navbar-nav navbar-right\">
			  <li class=\"dropdown\">
	       <a href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . $GLOBALS [$_SESSION ['module']] . "<b class=\"caret\"></b></a>";
	echo " <ul class=\"dropdown-menu\">";

	for($i = 0; $i < count ( $modules ); $i ++) {
		$mod = $modules [$i];
		if ($mod != $_SESSION ['module']) {
			echo " <li><a href=\"" . $baseURL . "index.php?indexAction=module" . $mod . "\">" . $GLOBALS [$mod] . "</a></li>";
		}
	}
	echo " </ul>";
	echo "</li>
			  </ul>";

	// Show inbox and number of messages
	if ($loggedUser) {
		echo "<ul class=\"nav navbar-nav navbar-right\">";
		$unreadMails = $objMessages->getNumberOfUnreadMails ();
		$unreadMailsSplit = explode ( "/", $unreadMails );
		echo "<li><a class=\"tour7\" href=\"" . $baseURL . "index.php?indexAction=show_messages\"><span class=\"glyphicon glyphicon-inbox\"></span>&nbsp;<span class=\"badge\">" . $unreadMailsSplit [0] . "</span></a></li>";
		echo "</ul>";
	}

	echo "<ul class=\"nav navbar-nav navbar-right\">";
	if ($loggedUser) {
		echo "<li class=\"dropdown\">
	         <a class=\"tour5\" href=\"http://" . $_SERVER ['SERVER_NAME'] . $_SERVER ["REQUEST_URI"] . "#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">" . $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . "<b class=\"caret\"></b></a>";
		echo " <ul class=\"dropdown-menu\">";
		echo " <li><a href=\"" . $baseURL . "index.php?indexAction=detail_observer&user=" . $loggedUser . "\">" . LangDetails . "</a></li>";
		echo " <li><a href=\"" . $baseURL . "index.php?indexAction=change_account\">" . LangChangeMenuItem1 . "</a></li>";
		echo "  <li class=\"disabled\">─────────────────</li>";
		echo " <li><a href=\"" . $baseURL . "index.php?indexAction=logout&amp;title=" . urlencode ( LangLogoutMenuItem1 ) . "\">" . LangLogoutMenuItem1 . "</a></li>";
		echo " </ul>";
		echo "</li>";
		echo "</ul>";
	} else {
		// Let's make a sign in / register tab
		echo "<span class=\"pull-right\">";
		echo "<button type=\"button\" class=\"btn btn-default navbar-btn\" data-toggle=\"modal\" data-target=\"#login\">" . $objObserver->getObserverProperty ( $loggedUser, 'firstname' ) . "&nbsp;" . LangLoginMenuTitle . "</button>&nbsp;";
		if ($register == "yes") { // includes register link
			echo "<a class=\"btn btn-success navbar-btn\" href=\"" . $baseURL . "index.php?indexAction=subscribe&amp;title=" . urlencode ( LangLoginMenuRegister ) . "\">" . LangLoginMenuRegister . "</a>&nbsp;";
		}
		echo "</span>";
		echo "</ul>";
	}
	// Closing the menu
	echo "	</div>
        </div>
      </nav>";

	// The navbar with the date and the lists
	echo "<nav class=\"navbar navbar-default navbar-lower navbar-fixed-top second-navbar\" role=\"navigation\">
         <div class=\"container-fluid\">
          <div class=\"navbar-header\">
            <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\"#bs-example-navbar-collapse-2\">
              <span class=\"sr-only\">Toggle navigation</span>
              <span class=\"icon-bar\"></span>
              <span class=\"icon-bar\"></span>
              <span class=\"icon-bar\"></span>
            </button>
          </div>
		      <div class=\"collapse navbar-collapse \" id=\"bs-example-navbar-collapse-2\">";

	echo "<div class=\"container-fluid\">";

	if ($loggedUser) {
		// Select the standard location and instrument
		if (array_key_exists ( 'admin', $_SESSION ) && ($_SESSION ['admin'] != 'yes')) {
			require_once 'common/menu/location.php';
			require_once 'common/menu/instrument.php';
		}
	}
	require_once 'deepsky/menu/date.php';
	require_once $_SESSION ['module'] . '/menu/list.php';
	echo "</div>";
	echo "	</div>
        </div>
      </nav>";
	if (!$loggedUser) {
		// The log in modal box
		echo "<div class=\"modal fade\" id=\"login\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
            <div class=\"modal-dialog\">
              <div class=\"modal-content\">
							<div class=\"modal-header\">
								<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
								<h1 class=\"text-center login-title\">DeepskyLog</h1>
							</div>
  		        <div class=\"modal-body\">
                  <div class=\"account-wall\">
                    <form class=\"form-signin\" action=\"" . $baseURL . "index.php\" method=\"post\">
                    	<input type=\"hidden\" name=\"indexAction\" value=\"check_login\" />
                      <input type=\"hidden\" name=\"title\"       value=\"" . LangLoginMenuTitle . "\" />
                      <input type=\"text\" class=\"form-control\" placeholder=\"" . LangLoginMenuItem1 . "\" required autofocus maxlength=\"64\" name=\"deepskylog_id\" id=\"deepskylog_id\">
                      <input type=\"password\" class=\"form-control\" placeholder=\"" . LangLoginMenuItem2 . "\" required maxlength=\"64\" name=\"passwd\" id=\"passwd\">
                      <button class=\"btn btn-lg btn-primary btn-block\" type=\"submit\">" . LangLoginMenuTitle . "</button>
											<br />
                    </form>
										<div class=\"text-center\">
											<a href=\"" . $baseURL . "index.php?indexAction=subscribe&amp;title=" . urlencode ( LangLoginMenuRegister ) . "\">" . LangLoginMenuRegister . "</a>
											&nbsp;&nbsp;-&nbsp;&nbsp;
											<a href=\"\" data-toggle=\"modal\" data-target=\"#forgotPassword\">" . LangForgotPassword . "</a>
										</div>
  		          </div>
  	  	        </div>
              </div>
            </div>
          </div>";


					// The Forgot password modal box
					echo "<div class=\"modal fade\" id=\"forgotPassword\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
					        <div class=\"modal-dialog\">
					          <div class=\"modal-content\">
											<div class=\"modal-header\">
												<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
												<h1 class=\"text-center login-title\">" . LangForgotPassword . "</h1>
											</div>
								  		<div class=\"modal-body\">" .
												LangForgotPasswordText1 . "
								        <form class=\"form-signin\" action=\"" . $baseURL . "index.php\" method=\"post\">
								        	<input type=\"hidden\" name=\"indexAction\" value=\"requestPassword\" />
													<div class=\"form-group\">
														<label for=\"deepskylog_id\">" . LangUserId . "</label>
								          	<input type=\"text\" class=\"form-control\" autofocus name=\"deepskylog_id\" id=\"deepskylog_id\">
													</div>
													<div class=\"form-group\">
														<label for=\"mail\">". LangChangeAccountField2 . "</label>
								          	<input type=\"email\" class=\"form-control\" name=\"mail\" id=\"mail\">
													</div>
								          <button class=\"btn btn-lg btn-primary btn-block\" type=\"submit\">" . LangRequestNewPassword . "</button>
													<br />
								        </form>
								  	  </div>
								    </div>
								  </div>
				        </div>";
	}
}
?>
