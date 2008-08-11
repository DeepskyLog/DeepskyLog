<?php
// $$ ok
?>
            </table>
          </td>
	      </td>
   	  </tr>
	  </table>
  </td>
  <td colspan="3" align="right" valign="top" style="background:url(vvs/images/toolbar_bg.jpg) no-repeat top left; background-color:#FFFFFF">			
    <table cellpadding="0" cellspacing="0" width="100%">
	    <tr width="100%">
	  	  <td width="50%">
		      <span class="pathway"> &nbsp; <?php echo LangYouAreHere; ?>
          <span class="pathway"> <a href="http://www.vvs.be/" class="pathway"> <?php echo LangHome; ?></a> 
				                         <img src="vvs/images/arrow.png" alt="arrow" /> 
				                         <a href="http://www.deepsky.be/" class="pathway">Deepsky</a> 
				                         <img src="vvs/images/arrow.png" alt="arrow" />   DeepskyLog 
				  </span>
          </span>
        </td>
				<td align="right" width="50%" nowrap="nowrap"><span class="mainlevel"> VVS: </span>
				  <span class="mainlevel"> | </span>
			    <a href="http://www.vvs.be/component/option,com_frontpage/Itemid,1/" class="mainlevel" ><?php echo LangHome; ?></a>
					<span class="mainlevel"> | </span>
		  		<a href="http://www.vvs.be/component/option,com_wrapper/Itemid,348/?" class="mainlevel" ><?php echo LangBecomeMember; ?></a>
	  			<span class="mainlevel"> | </span>
  				<a href="http://www.vvs.be/component/option,com_search/Itemid,81/" class="mainlevel" ><?php echo LangSearch; ?></a>
					<span class="mainlevel"> | </span>
					<a href="http://www.vvs.be/component/option,com_contact/Itemid,80/" class="mainlevel" ><?php echo LangContact; ?></a>
					<span class="mainlevel"> | </span>
        </td>
      </tr>
	  </table>
  </td>
  <td height="28" colspan="3" align="right" valign="top" style="background:url(vvs/images/rb_bg.gif) no-repeat top right; background-color:#FFFFFF">
    <img src="vvs/images/rb.gif" width="28" height="28" />
  </td>
  <td width="151" rowspan="3" align="left" valign="top" bgcolor="#5C7D9D">
    <table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td bgcolor="#003466">&nbsp;<br />
          <table cellpadding="0" cellspacing="0" class="moduletable">
				    <tr>
					    <th valign="top"><?php echo LangDeepskyLogModules ?></th> 
				    </tr>
            <tr>
              <!-- set height -->
              <td height="30" valign="top">
                <?php
                include_once "../lib/setup/databaseInfo.php"; 
                for ($i = 0; $i < count($modules);$i++)
                {
                  $mod = $modules[$i];
                  print "<a href=\"".$mod."/\">".$$mod."</a><br />";
                }
                ?>
              </td>
            </tr>
				    <tr>
							  <?php
                  if ($_SESSION['module'] == "deepsky")
                  {
								?>	
					    <th valign="top"> <?php echo(LangQuickPickTitle); ?></th> 
								<?php
								  }
								?>
				    </tr>
						<tr>
						  <td valign="top" height="175">
							  <?php
                  if ($_SESSION['module'] == "deepsky")
                  {
								?>	
                <FORM action="deepsky/index.php" method="get">
								<?php echo LangQuickPickHelp; ?>
								<input type="hidden" name="indexAction" value="quickpick"></input>
                <input type="text" class="inputfield" maxlength="255" name="object" cvalue="" 
								       value=<?php echo "\"";
								                   if(array_key_exists('object',$_GET) && ($_GET['object'] != '* '))
																	   echo $_GET['object'];
																	 echo "\"";  ?> >
                <input type="submit" name="searchObject" value=<?php echo LangQuickPickSearchObject; ?> style="width: 147px" >
                <input type="submit" name="searchObservations" value=<?php echo LangQuickPickSearchObservations; ?> style="width: 147px" >
							  <?php
                  if (array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id'])
                  {
								?>	
                <input type="submit" name="newObservation" value=<?php echo LangQuickPickNewObservation; ?> style="width: 147px" >
								<?php
								  }
								?>
                </FORM>
								<?php
								  }
								?>
							</td>
						</tr>	
						
						
				    <tr>
							  <?php
                  if ($_SESSION['module'] == "deepsky")
                  {
								?>	
					    <th valign="top"> <?php echo(LangListsTitle); ?></th> 
								<?php
								  }
								?>
				    </tr>
						<tr>
						  <td valign="top" height="300">
							  <?php
                  if (($_SESSION['module'] == "deepsky") && (array_key_exists('deepskylog_id',$_SESSION) && $_SESSION['deepskylog_id']))
                  {
								?>
                    <FORM action="deepsky/index.php" method="get">
								    <input type="hidden" name="indexAction" value="listaction"></input>
                    <input type="submit" name="manage" value="<?php echo LangListManage; ?>" style="width: 147px" >
                    </FORM>
                <?php
								    include_once "../lib/database.php";
										include_once "../lib/lists.php";
										$list=new Lists;
                    
                    if(array_key_exists('addList',$_GET) && array_key_exists('addlistname',$_GET))
                    {
                      if(array_key_exists('QOL',$_SESSION))
											  unset($_SESSION['QOL']);
                     	$listnameToAdd = $_GET['addlistname'];
                    	if(array_key_exists("PublicList",$_GET))
                    		if(substr($listnameToAdd,0,7)!="Public:")
                          $listnameToAdd = "Public: " . $listnameToAdd;  
                      if($list->checkList($_GET['addlistname'])!=0)
                    	  $_GET['listnameMessage'] = LangToListList . $listnameToAdd . LangToListExists;
                    	else
                    	{
                    	  $list->addList($listnameToAdd);
                        if(array_key_exists('QOL',$_SESSION))
												  unset($_SESSION['QOL']);
                    	  $_SESSION['listname'] = $listnameToAdd;
                    	  $_GET['listnameMessage'] = LangToListList . $_SESSION['listname'] . LangToListAdded;
                    	}                    	echo "<hr>";
                    }
										if(array_key_exists('renameList',$_GET) && array_key_exists('addlistname',$_GET))
									  { 
                      if(array_key_exists('QOL',$_SESSION))
											  unset($_SESSION['QOL']);
                      $listnameFrom = $_SESSION['listname'];
                     	$listnameTo = $_GET['addlistname'];
                      if(array_key_exists("PublicList",$_GET))
                        if(substr($listnameTo,0,7)!="Public:")
                          $listnameTo = "Public: " . $listnameTo;  
                      if($list->checkList($listnameTo)!=0)
                     	  $_GET['listnameMessage'] =  LangToListList . $listnameTo . LangToListExists;
                      else
                      {
                        $list->renameList($listnameFrom, $listnameTo);
                        $_SESSION['listname'] = $listnameTo;
                        $_GET['listnameMessage'] = LangToListList . $_SESSION['listname'] . LangToListAdded;
                      }
									  }
                    if(array_key_exists('removeList',$_GET) && ($list->checkList($_SESSION['listname'])==2))
                    {
                      if(array_key_exists('QOL',$_SESSION))
											  unset($_SESSION['QOL']);
                      $list->removeList($_SESSION['listname']);
                    	$_GET['listnameMessage'] = LangToListRemoved . $_SESSION['listname'] . ".";
                      $_SESSION['listname']="----------";
											unset($_GET['removeList']);
                    }
                    if(array_key_exists('activateList',$_GET) && array_key_exists('listname',$_GET))
                    {
                      if(array_key_exists('QOL',$_SESSION))
											  unset($_SESSION['QOL']);
                      $_SESSION['listname'] = $_GET['listname'];
											if($_GET['listname']<>"----------")
                      	$_GET['listnameMessage'] = LangToListList . $_SESSION['listname'] . LangToListActivation1 . LangBack . LangToListActivation2;
                    }
										
										$result1=array();
										$result2=array();
								    $db = new database;
								    $db->login();
						    		$sql = "SELECT DISTINCT observerobjectlist.listname " .
										       "FROM observerobjectlist " .
													 "WHERE observerid = \"" . $_SESSION['deepskylog_id'] . "\" ORDER BY observerobjectlist.listname";
										$run = mysql_query($sql) or die(mysql_error());
										$get = mysql_fetch_object($run);
										while($get)
										{
	  								  $result1[]=$get->listname;
											$get = mysql_fetch_object($run);
										}
										$sql = "SELECT DISTINCT observerobjectlist.listname " .
										       "FROM observerobjectlist " .
													 "WHERE observerid <> \"" . $_SESSION['deepskylog_id'] . "\"" . 
													 "AND listname LIKE \"Public: %\" ORDER BY observerobjectlist.listname";
										$run = mysql_query($sql) or die(mysql_error());
										$get = mysql_fetch_object($run);
										while($get)
										{
											$result2[]=$get->listname;
											$get = mysql_fetch_object($run);
										}
										$db->logout();
										$result1[]='----------';
										$result=array_merge($result1,$result2);
                    echo("<form name=\"listform\">\n ");		
                    if(count($result)>0)
										{
											echo("<select style=\"width: 147px\" onchange=\"location = this.options[this.selectedIndex].value;\" name=\"activatelist\">\n");
											if((!array_key_exists('listname',$_SESSION)) || (!$_SESSION['listname']))
											  $_SESSION['listname']="----------";
                      while(list($key, $value) = each($result))
											{
                        if($value==$_SESSION['listname'])
												  echo("<option selected value=\""  . $baseURL . "deepsky/index.php?indexAction=listaction&activateList=true&listname=$value\">$value</option>\n");
                        elseif (!(array_key_exists('removeList',$_GET) && ($_SESSION['listname']==$value)))
												  echo("<option value=\""  . $baseURL . "deepsky/index.php?indexAction=listaction&activateList=true&listname=$value\">$value</option>\n");
                    	}
											echo("</select>\n");
								    }
										echo("</form>");				
									}
								else
								  echo(LangListOnlyMembers);
								?>
							</td>
						</tr>							
						
				    <?php
				    if ($_SESSION['lang'] == "nl")
				    {
				    print "<tr>
				           <th valign=\"top\">Help</th>
				           </tr><tr>
				           <td valign=\"top\" height=\"60\">
				           <a href=\"http://www.astrowiki.nl/index.php/Deepskylog\" target=\"_blank\">Handleiding</a>
				           </td>
				           </tr>";
				    }
				    ?>
				    <tr>
				    
							  <?php
                  if ($_SESSION['module'] == "deepsky")
                  {
								?>	
					    <th valign="top"> <?php echo(LangMailtoTitle); ?></th> 
								<?php
								  }
								?>
				    </tr>
						<tr>
						  <td valign="top" height="120">
							  <?php
                  if ($_SESSION['module'] == "deepsky")
                  {
								?>	
								<?php echo LangMailtoLink; ?>
								<?php
								  }
								?>
							</td>
						</tr>							
						
						
						
						
						
			    </table>
			  </td>
      </tr>
      <tr>
        <td height="13" align="left" valign="bottom" background="vvs/images/rightcolumn_2.gif">
			    <img src="vvs/images/rightcolumn_1.gif" width="10" height="13" />
				</td>
      </tr>
      <tr>
        <td height="12" background="vvs/images/rightcolumn_4.gif">
			    <img src="vvs/images/rightcolumn_3.gif" width="12" height="12" />
				  <img src="vvs/images/rightcolumn_4.gif" width="1" height="12" />
		    </td>
      </tr>
      <tr>
        <td align="center" bgcolor="#333333">			
			    <table cellpadding="0" cellspacing="0" class="moduletable">
					  <tr>
					    <th valign="top">Teller</th>
				    </tr>
			    </table>
    <!-- Start of StatCounter Code -->
          <a href="http://my6.statcounter.com/project/standard/stats.php?project_id=1347986&guest=1">
				    <script type="text/javascript" language="javascript">
              var sc_project=1347986;
              var sc_invisible=0;
              var sc_partition=12;
              var sc_security="155f4e3f";
              var sc_remove_link=1;
            </script>
            <script type="text/javascript" language="javascript" src="http://www.statcounter.com/counter/counter.js">
				    </script>
				    <noscript>
				      <img  src="http://c13.statcounter.com/counter.php?sc_project=1347986&amp;java=0&amp;security=155f4e3f&amp;invisible=0" alt="free webpage counters" border="0"> 
			  	  </noscript>
		  		</a>
    <!-- End of StatCounter Code -->
        </td>
      </tr>
      <tr>
        <td align="left" valign="top" background="vvs/images/rightcolumn_6.gif">
			    <img src="vvs/images/rightcolumn_5.gif" width="12" height="12" />
			  </td>
      </tr>
    </table>
  </td>
</tr>
<tr>
  <td bgcolor="#FFFFFF"></td>
	<td colspan="3" valign="top" bgcolor="#FFFFFF">
	  <table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
      <tr>
        <td>
          <table class="blog2" cellpadding="0" cellspacing="0">
					  <tr>
						  <td valign="top">
							  <div>			
								  <table class="contentpaneopen">
			              <tr>
								      <td class="contentheading" width="100%">
				                http://www.deepskylog.org 
												<div style="text-align:right">
												  <?php 
													  $mod = $_SESSION['module']; 
														echo $mod; 
													?>
												</div>
											</td>
							      </tr>
			            </table>
			              <table class="contentpaneopen">
				            <tr>
					            <td valign="top" colspan="2">
