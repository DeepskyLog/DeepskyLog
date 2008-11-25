<?php
// change_lens.php
// form which allows the administrator to change a lens
// version 3.2: WDM 11/05/2008

echo("<div id=\"main\">\n<h2>");

echo stripslashes($lenses->getLensName($_GET['lens']));

echo("</h2>"); ?>
   <form action=$baseURL."common/control/indexCommon.php?indexAction=validate_lens" method="post">
   <table>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddLensField1);
			 ?></td>
   <td><input type="text" class="inputfield" maxlength="64" name="lensname" size="30" value="<?php echo stripslashes($lenses->getLensName($_GET['lens']));?>" /></td>
   <td class="explanation"><?php echo(LangAddLensField1Expl); ?></td>
   </tr>
   <tr>
   <td class="fieldname">
	     <?php 
			 echo(LangAddLensField2); 
			 ?></td>
   <td>
   <input type="text" class="inputfield" maxlength="5" name="factor" size="5" value="<?php 
	    echo stripslashes($lenses->getFactor($_GET['lens']));
			 ?>" />
   </td> 
   <td class="explanation"><?php echo(LangAddLensField2Expl); ?></td>
   </tr>

   <tr>
   <td></td>
   <td><input type="submit" name="change" value="<?php echo (LangChangeLensButton);

echo("\" /><input type=\"hidden\" name=\"id\" value=\"");

echo ($_GET['lens']);

echo("\"></input>"); ?></td>
   <td></td>
   </tr>
   </table>
   </form>
</div>
</div>
</body></html>


