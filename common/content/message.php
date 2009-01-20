<?php
if(isset($messageLines))
  while(list($key,$line)=each($messageLines))
    echo $line;
?>