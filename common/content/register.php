<?php

// register.php
// allows the user to apply for an deepskylog account

include_once "../lib/observers.php";
include_once "../lib/setup/language.php";
include_once "../lib/util.php";

$util = new Util();
$util->checkUserInput();

$obs=new Observers;

echo("<div id=\"main\">\n
      <h2>" . LangRegisterNewTitle . "</h2>");        

?>

   <form action="common/control/validate_account.php" method="post">
   <table>
   <tr>
   <td class="fieldname"><?php echo(LangChangeAccountField1); ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="deepskylog_id" size="30" value="" /></td>
   <td class="explanation"><?php echo(LangChangeAccountField1Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangChangeAccountField2); ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="email" size="30" value="" /></td>
   <td class="explanation"><?php echo(LangChangeAccountField2Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangChangeAccountField3); ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="firstname" size="30" value="" /></td>
   <td class="explanation"><?php echo(LangChangeAccountField3Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangChangeAccountField4); ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="name" size="30" value="" /></td>
   <td class="explanation"><?php echo(LangChangeAccountField4Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangChangeAccountField5); ?></td>
   <td><input type="password" class="inputfield" maxlength="64" name="passwd" size="30" value="" /></td>
   <td class="explanation"><?php echo(LangChangeAccountField5Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname"><?php echo(LangChangeAccountField6); ?></td>
   <td><input type="password" class="inputfield" maxlength="64" name="passwd_again" size="30" value="" /></td>
   <td class="explanation"><?php echo(LangChangeAccountField6Expl); ?></td>
   </tr>
<?php
 echo("<tr><td>");
 echo(LangChangeAccountObservationLanguage);

 echo("</td><td>");

 $allLanguages = $language->getAllLanguages($obs->getLanguage($_SESSION['deepskylog_id']));

 echo("<select name=\"description_language\">");

 while(list ($key, $value) = each($allLanguages))
 {
   if($standardLanguagesForObservationsDuringRegistration == $key)
   {
     print("<option value=\"".$key."\" selected=\"selected\">".$value."</option>\n");
   }
   else 
   {
     print("<option value=\"".$key."\">".$value."</option>\n");
   }
 }

 print(" 
    </select>
    </td>   <td class=\"explanation\">");

 echo(LangChangeAccountObservationLanguageExpl);


echo("</td>");
echo("</tr>");

echo("<tr>");
echo("<td>");

 echo(LangChangeAccountLanguage);

 echo("</td>
    <td>   <select name=\"language\">");
 

      $language = new Language(); 
      $languages = $language->getLanguages(); 

      while(list ($key, $value) = each($languages))
      {
         if($obs->getLanguage($_SESSION['deepskylog_id']) == $key)
         {
            print("<option value=\"" . $key . "\" selected=\"selected\">$value</option>\n");
         }
         else 
         {
            print("<option value=\"" . $key . "\">$value</option>\n");
         }
      }

 print(" 
    </select>
    </td>   <td class=\"explanation\">");

 echo(LangChangeAccountLanguageExpl);
echo("</td>");
 
echo("</tr>");   
	 
echo("<tr><td class=\"fieldname\">");

   echo(LangChangeVisibleLanguages);

 echo("</td><td>");
 include_once "../lib/setup/language.php";

 $allLanguages = $language->getAllLanguages($_SESSION['lang']);
 $_SESSION['alllanguages'] = $allLanguages; 

 $usedLanguages = $languagesDuringRegistration;

 while(list ($key, $value) = each($allLanguages))
 {
   echo("<input type=\"checkbox\" ");

   for ($i = 0;$i < count($usedLanguages);$i++)
   {
     if ($key == $usedLanguages[$i])
     {
       echo("checked ");
     }
   }
   echo ("name=\"" . $key . "\" value=\"" . $key . "\" />". $value . "<br />\n");
 }
 print("</td>   <td class=\"explanation\">");

 echo(LangChangeVisibleLanguagesExpl);

echo("</td>
   </tr>

   <tr>
   <td></td>
   <td><input type=\"submit\" name=\"register\" value=\"" . LangRegisterNewTitle . "\" /></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>");
?>