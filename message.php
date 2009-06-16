<?php
session_start();
echo "<html>";
echo "<head>";
echo "</head>";
echo "<body>";
echo "<p class=\"centered\">".$_SESSION['message']."</p>";
echo "</body>";
echo "</html>";
?>