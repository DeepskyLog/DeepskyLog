<?php

// check_login.php
// check whether user is allowed to login 
// version 2.0, JV 20050821
// version 3.1, DE 20061124

session_start(); // start session

include_once "../../lib/observers.php";
include_once "../../lib/setup/vars.php";
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$obs = new Observers; 

$_SESSION['admin'] = "no";
$_SESSION['deepskylog_id'] = "";

if(!(array_key_exists('module',$_SESSION)))
{
  $_SESSION['module']="deepsky";
}

if(isset($_POST['submit']))                                     // pushed submit button
{
   if(array_key_exists('deepskylog_id', $_POST) && $_POST['deepskylog_id'] && array_key_exists('passwd', $_POST) && $_POST['passwd'])              // all fields filled in
   {
      // get password from form and encrypt
      $login  = $_POST['deepskylog_id'];
			$passwd = md5($_POST['passwd']);

      // get password from database 
      $passwd_db = $obs->getPassword($login);

      if ($passwd_db == $passwd)                                 // check if passwords match
      {
         $_SESSION['lang'] = $obs->getLanguage($login);
         if($obs->getRole($login) == "2")                        // user in waitlist already tries to log in
         {
            $_SESSION['waitlist'] = "yes";
            $_SESSION['message'] = LangErrorPasswordNotValidated;
	          $_SESSION['title'] = "Error: not registered";
            header("Location: ../error.php");
         }

         elseif($obs->getRole($login) == "1")                    // validated user
         {
            $_SESSION['deepskylog_id'] = $login;                  // set session variable
            $_SESSION['admin'] = "no";                           // set session variable
	
	          $cookietime = time() + 365 * 24 * 60 * 60;            // 1 year
	          setcookie("deepskylogsec",$passwd.$login,$cookietime, "/");
            header("Location: ../../".$_SESSION['module']."/index.php"); 
																																	// redirect to my observations page
         }

         else // administrator logs in 
         {
            $_SESSION['deepskylog_id'] = $login;                  // set session variable
            $_SESSION['admin'] = "yes";                           // set session variable
            if(array_key_exists('deepskylog_id', $_SESSION) && ($_SESSION['deepskylog_id'] == "admin"))             // administrator with id == admin
            {
               header("Location: ../view_observers.php");         // redirect to view_observers page
            }
            else
            {
               $cookietime = time() + 365 * 24 * 60 * 60;         // 1 year
               setcookie("deepskylogsec",$passwd.$login,$cookietime, "/");
							 header("Location: ../../".$_SESSION['module']."/index.php"); 
							  								 																  // redirect to my observations page
            }
         }
      }
      else // passwords don't match
      {
	      unset($_SESSION['deepskylog_id']);
        $_SESSION['message'] = LangErrorWrongPassword;
        $_SESSION['title'] = "Wrong password";
        header("Location: ../error.php");
      }
   }
   else // not all fields are filled in
   {
      unset($_SESSION['deepskylog_id']);
      $_SESSION['message'] = LangErrorEmptyPassword;
      $_SESSION['title'] = "Empty field";
      header("Location: ../error.php"); // error page
   }
}
?>
