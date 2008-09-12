<?php
// login.php
// menu which allows the user to log in  

include_once "lib/observers.php";

$obs = new Observers();
$logged_in = 0;

if (isset($_COOKIE['deepskylog']))
{
 	 setcookie("deepskylog","",time()-3600,"/");
	 // hier kan evenuteel een mededeling dat voor security reasons opnieuw moet ingelogd worden!
}

$_SESSION['admin'] = "no";
if (array_key_exists('deepskylogsec', $_COOKIE) && $_COOKIE['deepskylogsec'])
{
   if (strlen($_COOKIE['deepskylogsec']) > 32)
   { 
	    $login  = substr($_COOKIE['deepskylogsec'],32,255);
	    $passwd = substr($_COOKIE['deepskylogsec'],0,32);
      // get password from database 
      $passwd_db = $obs->getPassword($login);
     
		  if ($passwd_db == $passwd)                  // check if passwords match
      {  
		     $_SESSION['deepskylog_id'] = $login;
				 if(array_key_exists('lco',$_COOKIE))
				   $_SESSION['lco'] = $_COOKIE['lco'];
				else
				{
          $cookietime = time() + 365 * 24 * 60 * 60;            // 1 year
				  $_SESSION['lco'] = 'L';
          setcookie("lco","L",$cookietime, "/");
		 		}		 			 				 
				 if($obs->getRole($login) == "0")         // administrator logs in 
         {
            $_SESSION['admin'] = "yes";           // set session variable 
         }
         $logged_in = 1;
	    }
			else
			{												 	 									// invalidate cookie
		     $_SESSION['deepskylog_id'] = '';
			 	 setcookie("deepskylogsec","",time()-3600,"/");
			}	 																					
   }
	 else
	 {
		 $_SESSION['deepskylog_id'] = '';
		 setcookie("deepskylogsec","",time()-3600,"/");
	 }
}
else
{
	$_SESSION['deepskylog_id'] = '';
	setcookie("deepskylogsec","",time()-3600,"/");
}

if (isset($_COOKIE['module']))
{
	$_SESSION['module'] = $_COOKIE['module'];
}
else
{
//  $_SESSION['module'] = $defaultmodule;
  $_SESSION['module'] = $modules[0];
}

?>
