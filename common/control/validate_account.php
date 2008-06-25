<?php

// validate_account.php
// checks if the change account or register form is correctly filled in

session_start(); // start session

include "../../lib/observers.php";
include_once "../../lib/setup/vars.php"; // necessary to set language
include_once "../../lib/util.php";

$util = new Util();
$util->checkUserInput();

$obs = new Observers; // create new Observers object


if (!$_POST['email'] || !$_POST['firstname'] || !$_POST['name'] || !$_POST['passwd'] || !$_POST['passwd_again'])
{
      $_SESSION['message'] = LangValidateAccountMessage1;
      header("Location:../error.php");
}

else // all fields filled in
{
   if ($_POST['passwd'] != $_POST['passwd_again']) // passwd and passwd_again don't match
   {
      $_SESSION['message'] = LangValidateAccountMessage2;
      header("Location:../error.php");
   }

   else // password confirmed
   {
      // check if email address is legal (contains @ symbol)

      if (!preg_match("/.*@.*..*/", $_POST['email']) | preg_match("/(<|>)/", $_POST['email']))
      {
      $_SESSION['message'] = LangValidateAccountMessage3;
      header("Location:../error.php");
      }

      // check if username doesn't exist yet

      if(array_key_exists('register',$_POST) && array_key_exists('deepskylog_id', $_POST) && ($_POST['register'] && $_POST['deepskylog_id'])) // pressed register button and username filled in 
      {
         if(!$obs->getName($_POST['deepskylog_id'])) // user doesn't exist yet
         {
            // fill database
            $obs->addObserver($_POST['deepskylog_id'], $_POST['name'], $_POST['firstname'], $_POST['email'], md5($_POST['passwd']));

            // READ ALL THE LANGUAGES FROM THE CHECKBOXES
            $allLanguages = $_SESSION['alllanguages'];

            while(list ($key, $value) = each($allLanguages))
            {
              if(array_key_exists($key, $_POST))
              {
                $usedLanguages[] = $key;
              }
            }

            $obs->setUsedLanguages($_POST['deepskylog_id'], $usedLanguages);

            $obs->setObservationLanguage($_POST['deepskylog_id'], $_POST['description_language']);

            $obs->setLanguage($_POST['deepskylog_id'], $_POST['language']);

            // send mail to administrator

            // message body

            $body = LangValidateAccountEmailLine1 . "\n"
                  . "\n" . LangValidateAccountEmailLine1bis
                  . $_POST['deepskylog_id']
                  . "\n" . LangValidateAccountEmailLine2
                  . $_POST['email']
                  . "\n" . LangValidateAccountEmailLine3
                  . $_POST['firstname'] . " " . $_POST['name']
                  . "\n\n" . LangValidateAccountEmailLine4;

            // message recipient(s)
            $obs = new Observers;
            $admins = $obs->getAdministrators();

            while(list ($key, $value) = each($admins))
            {
             if ($obs->getEmail($value) != "")
             {
              $adminMails[] = $obs->getEmail($value);
             }
            }
            $to = implode(",", $adminMails);

            // message subject

            $subject = LangValidateAccountEmailTitle;

            // other headers

            $administrators = $obs->getAdministrators();
            $fromMail = $obs->getEmail($administrators[0]);
            $headers = "From:".$fromMail;
            // send message
           if(mail($to, $subject, $body, $headers))
           {
              // send mail succeeded
              header("Location: ../confirm_subscribe.php");

           }
        }  
        else // user already exists
        {
        $_SESSION['message'] = LangValidateAccountMessage4; 
        header("Location:../error.php");
        }
    }  
    elseif(array_key_exists('change', $_POST) && $_POST['change']) // pressed change button
    {

       if (!$_SESSION['deepskylog_id']) // extra control on login
       {
          $_SESSION['message'] = LangValidateAccountMessage1;
          header("Location:../error.php");
       }
       else
       {
          // READ ALL THE LANGUAGES FROM THE CHECKBOXES
          $allLanguages = $_SESSION['alllanguages'];

          while(list ($key, $value) = each($allLanguages))
          {
            if(array_key_exists($key, $_POST))
            {
              $usedLanguages[] = $key;
            }
          }
          $obs->setUsedLanguages($_SESSION['deepskylog_id'], $usedLanguages);

          $obs->setName($_SESSION['deepskylog_id'], $_POST['name']);  
          $obs->setFirstName($_SESSION['deepskylog_id'], $_POST['firstname']);
          $obs->setEmail($_SESSION['deepskylog_id'], $_POST['email']);
          $obs->setPassword($_SESSION['deepskylog_id'], md5($_POST['passwd'])); 
          $obs->setLanguage($_SESSION['deepskylog_id'], $_POST['language']);
          $obs->setObservationLanguage($_SESSION['deepskylog_id'], $_POST['description_language']);
          $obs->setStandardLocation($_SESSION['deepskylog_id'], $_POST['site']);
          $obs->setStandardTelescope($_SESSION['deepskylog_id'], $_POST['instrument']);
          $obs->setStandardAtlas($_SESSION['deepskylog_id'], $_POST['atlas']);
          if (array_key_exists('local_time', $_POST) && ($_POST['local_time'] == "on"))
          {
            $obs->setUseLocal($_SESSION['deepskylog_id'], 1);
          }
          else
          {
            $obs->setUseLocal($_SESSION['deepskylog_id'], 0);
          }

          if ($_POST['icq_name'] != "")
          {
             $obs->setIcqName($_SESSION['deepskylog_id'], $_POST['icq_name']);
          }
          $_SESSION['lang'] = $_POST['language'];

          if($_FILES['picture']['tmp_name'] != "")
          {
             $upload_dir = '../observer_pics';
             $dir = opendir($upload_dir);

// resize code

             include "resize.php";

	     $original_image = $_FILES['picture']['tmp_name'];
	     $destination_image = $upload_dir . "/" . $_SESSION['deepskylog_id'] . ".jpg"; 
	     $max_width = "300";
	     $max_height = "300";
	     $resample_quality = "75";

	     $new_image = image_createThumb($original_image, $destination_image, $max_width, $max_height, $resample_quality);

          }
          $_SESSION['message'] = LangValidateAccountMessage5;
	  			$_SESSION['title'] = LangValidateAccountMessage;
          header("Location:../message.php");
          }
       }
    }
  }
?>
