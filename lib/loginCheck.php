<?php

include "observers.php";

 // checks if someone has logged in and has user rights
 function checkLogin()
 {
   session_start(); // start session

   if(!isset($_SESSION['deepskylog_id']))
   {
   unset($_SESSION['deepskylog_id']);
   include "../logout.php";
   
   }
 }

 // checks if someone has logged in and has administrator rights
 function checkLoginAdministrator()
 {
    checkLogin();
 }

?>
