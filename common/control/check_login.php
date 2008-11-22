<?php
// check_login.php
// check whether user is allowed to login 

$_SESSION['admin'] = "no";
$_SESSION['deepskylog_id'] = "";
if(isset($_POST['submit']))                                                     // pushed submit button
{ if(array_key_exists('deepskylog_id', $_POST)&&$_POST['deepskylog_id']&&array_key_exists('passwd', $_POST)&&$_POST['passwd'])              // all fields filled in
  { $login  = $_POST['deepskylog_id'];                                          // get password from form and encrypt
	  $passwd = md5($_POST['passwd']);
    $passwd_db = $GLOBALS['objObserver']->getPassword($login);                  // get password from database 
    if($passwd_db==$passwd)                                                     // check if passwords match
    { $_SESSION['lang']=$GLOBALS['objObserver']->getLanguage($login);
      if($GLOBALS['objObserver']->getRole($login) == "2")                        // user in waitlist already tries to log in
      { $_SESSION['waitlist'] = "yes";
        $_SESSION['message'] = LangErrorPasswordNotValidated;
        $_SESSION['title'] = "Error: not registered";
        throw new Exception("check_login: user in waitlist");
      }
      elseif($GLOBALS['objObserver']->getRole($login) == "1")                   // validated user
      { session_regenerate_id(true);
			  $_SESSION['deepskylog_id'] = $login;                                    // set session variable
        $_SESSION['admin'] = "no";                           // set session variable
	      $cookietime = time() + 365 * 24 * 60 * 60;            // 1 year	      
				setcookie("deepskylogsec",$passwd.$login,$cookietime, "/");
	    }
      else // administrator logs in 
      { session_regenerate_id(true);
			  $_SESSION['deepskylog_id'] = $login;                  // set session variable
        $_SESSION['admin'] = "yes";                           // set session variable
        if(!array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id'] == "admin"))             // administrator with id == admin
        { $cookietime = time() + 365 * 24 * 60 * 60;         // 1 year
          setcookie("deepskylogsec",$passwd.$login,$cookietime, "/");
	      }
      }
    }
    else // passwords don't match
    { unset($_SESSION['deepskylog_id']);
      $_SESSION['message'] = LangErrorWrongPassword;
      $_SESSION['title'] = "Wrong password";
      throw new Exception("check_login: Passwords don't match");
    }
  }
  else // not all fields are filled in
  { unset($_SESSION['deepskylog_id']);
    $_SESSION['message'] = LangErrorEmptyPassword;
    $_SESSION['title'] = "Empty field";
    throw new Exception("check_login: not all fields are filled in"); // error page
  }
}
?>
