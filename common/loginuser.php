<?php
// loginuser.php
// checks if the user is logged in based on cookie

if (isset($_COOKIE['deepskylog']))
{	 setcookie("deepskylog","",time()-3600,"/");
}
$_SESSION['admin']="no";
if(array_key_exists('deepskylogsec', $_COOKIE)&&$_COOKIE['deepskylogsec'])
{ if(strlen($_COOKIE['deepskylogsec'])>32)
  { if(substr($_COOKIE['deepskylogsec'],0,32)==$objObserver->getPassword(substr($_COOKIE['deepskylogsec'],32,255)))
    { $_SESSION['deepskylog_id']=substr($_COOKIE['deepskylogsec'],32,255);
		  $_SESSION['lang']=$objObserver->getLanguage($_SESSION['deepskylog_id']);
			if($objObserver->getRole($_SESSION['deepskylog_id'])=="0")                // administrator logs in 
        $_SESSION['admin']="yes";
	  }
		else
		{ $_SESSION['deepskylog_id']='';
			setcookie("deepskylogsec","",time()-3600,"/");
		}	 																					
  }
	else
	{ $_SESSION['deepskylog_id']='';
		setcookie("deepskylogsec","",time()-3600,"/");
	}
}
elseif(array_key_exists('indexAction',$_GET)&&($_GET['indexAction']=='check_login')&&isset($_POST['submit']))                                                     // pushed submit button
{ if(array_key_exists('deepskylog_id', $_POST)&&$_POST['deepskylog_id']&&array_key_exists('passwd', $_POST)&&$_POST['passwd'])              // all fields filled in
  { $login  = $_POST['deepskylog_id'];                                          // get password from form and encrypt
	  $passwd = md5($_POST['passwd']);
    $passwd_db = $GLOBALS['objObserver']->getPassword($login);                  // get password from database 
    if($passwd_db==$passwd)                                                     // check if passwords match
    { $_SESSION['lang']=$GLOBALS['objObserver']->getLanguage($login);
      if($GLOBALS['objObserver']->getRole($login)=="2")                        // user in waitlist already tries to log in
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
else
{	$_SESSION['deepskylog_id']='';
	setcookie("deepskylogsec","",time()-3600,"/");
}
if(isset($_COOKIE['module']))
	$_SESSION['module']=$_COOKIE['module'];
else
  $_SESSION['module']=$modules[0];
if(!array_key_exists('lang',$_SESSION))
 $_SESSION['lang']=$defaultLanguage;
if(array_key_exists('indexAction',$_POST)&&($_POST['indexAction']=="setLanguage"))
{ if(array_key_exists('language',$_POST)&&$_POST['language']&&array_key_exists($_POST['language'],$objLanguage->getLanguages()))
    $_SESSION['lang']=$_POST['language'];
}
$language=$objLanguage->getPath($_SESSION['lang']);
include "../lib/setup/"."$language";
?>
