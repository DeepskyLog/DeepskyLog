<?php
// Shows a message to the user

if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else message();

function message()
{ if(isset($messageLines))
    while(list($key,$line)=each($messageLines))
      echo $line;
}
?>