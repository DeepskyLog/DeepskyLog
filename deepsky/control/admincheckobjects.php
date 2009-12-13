<?php
echo "Checking ".($objCnt=count($_SESSION['Qobj']))." objects.<br />";
echo "<br />";
echo "Checking objects constellation:<br />";
echo "<br />";
$correct=0;
for($i=0;$i<$objCnt;$i++)
{ if($_SESSION['Qobj'][$i]['objectconstellation']==$objConstellation->getConstellationFromCoordinates($_SESSION['Qobj'][$i]['objectra'],$_SESSION['Qobj'][$i]['objectdecl']))
    $correct++;
  else
    echo "- ".$_SESSION['Qobj'][$i]['objectname']." constellation ".$_SESSION['Qobj'][$i]['objectconstellation']." should be ".$objConstellation->getConstellationFromCoordinates($_SESSION['Qobj'][$i]['objectra'],$_SESSION['Qobj'][$i]['objectdecl']).' '.$_SESSION['Qobj'][$i]['objectra'].' '.$_SESSION['Qobj'][$i]['objectdecl']."<br />"; 
}
echo "<br />";
echo "Correct ".$correct.".<br />";
?>