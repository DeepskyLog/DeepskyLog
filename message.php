<?php
session_start();
echo "<html>";
echo "<head>";
echo "</head>";
echo "<body>";
echo "<center>".$_SESSION['message']."</center>";
echo "</body>";
echo "</html>";
?>