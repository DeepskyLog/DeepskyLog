<?php
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else
{
if(isset($messageLines))
  while(list($key,$line)=each($messageLines))
    echo $line;
}
?>