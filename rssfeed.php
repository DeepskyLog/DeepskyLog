<?php 
// rssfeed.php
// How to set up the DeepskyLog rss feed...
// Create a small script like
// cd /lhome/wim/sourcecode/eclipse\ DeepskyLog/DeepskyLog\ trunk/
// php rssfeed.php > observations.rss
//
// Add this to your crontab
// 0,5,10,15,20,25,30,35,40,45,50,55 * * * *       Name_of_your_script  >/dev/null 2>&1

header ("Content-Type: application/rss+xml");
header ("Content-Disposition: attachment; filename=\"observations.rss\"");

$inIndex = true;
require_once 'common/entryexit/preludes.php';

rssObservations();

function rssObservations()
{ global $objUtil;
  $objUtil->rssObservations();
}
?>
