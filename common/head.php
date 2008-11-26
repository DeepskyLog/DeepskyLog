<?php
// head.php
// prints the html headers and the main menu

class head
{
   function printHeader($title)
   {
   include "lib/setup/databaseInfo.php";
	 echo("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	 <html xmlns=\"http://www.w3.org/1999/xhtml\">
	 <head>
	 <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
	 <meta name=\"revisit-after\" content=\"1 day\">
	 <META NAME=\"copyright\" CONTENT=\"Copyright &copy; 2005-2006 VVS. Alle Rechten Voorbehouden.\">
	 <META NAME=\"author\" CONTENT=\"DeepskyLog - VVS\">
	 <title>DeepskyLog ". $GLOBALS['objUtil']->checkGetKey('indexAction','')."</title>
	 <meta name=\"description\" content=\"Vereniging voor sterrenkunde\" />
	 <meta name=\"keywords\" content=\"VVS, Vereniging Voor Sterrenkunde, astronomie, sterrenkunde, JVS, Heelal, Astra, Hemelkalender, Sterrenkijkdag, Sterrenkijkdagen, sterr, Nieuws, Laatste nieuws\" />
	 <meta name=\"Generator\" content=\"Mambo - Copyright 2000 - 2005 Miro International Pty Ltd.  All rights reserved.\" />
	 <meta name=\"robots\" content=\"index, follow\" />
	 <base href=\"" . $baseURL . "\" />
	 <link rel=\"shortcut icon\" href=\"/vvs/images/favicon.ico\" />
	 <link href=\"vvs/css/template_css.css\" rel=\"stylesheet\" type=\"text/css\" />
	 <link href=\"styles/style.css\" rel=\"stylesheet\" type=\"text/css\" />
	 </head>");
   }

   function printMenu()
   {
   }

   function printMeta($meta)
   {
   }

}

?>
