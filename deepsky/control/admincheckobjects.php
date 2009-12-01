<?php
echo "Checking ".($objCnt=count($_SESSION['Qobj']))." objects.<br />";
$correct=0;
for($i=0;$i<$objCnt;$i++)
{ if($_SESSION['Qobj'][$i]['objectconstellation']==$objConstellation->getConstellationFromCoordinates($_SESSION['Qobj'][$i]['objectra'],$_SESSION['Qobj'][$i]['objectdecl']))
    $correct++;
  else
    echo "- ".$_SESSION['Qobj'][$i]['objectname']." constellation ".$_SESSION['Qobj'][$i]['objectconstellation']." should be ".$objConstellation->getConstellationFromCoordinates($_SESSION['Qobj'][$i]['objectra'],$_SESSION['Qobj'][$i]['objectdecl'])."<br />"; 
}
echo "Correct ".$correct."<br />";
?>