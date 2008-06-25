<?php
 include_once "../lib/database.php";
 include_once "../lib/objects.php";

 $object = new Objects;

 $db = new database;
 $db->login();

 $sql = "delete from objects where name = \"NGC 4560\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4610\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4407\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2372\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4667\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4884\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6975\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6976\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2244\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2237\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2520\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5490A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3371\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3373\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3389\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"NGC 6039\" where name = \"NGC 6040B\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6039\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6042\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6053\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6057\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3575\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3760\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4228\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4409\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5390\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6952\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6008A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5438\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5446\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3218\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5834\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3632\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4212\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4208\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4565A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4338\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4310\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7627\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7641\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7605\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7583\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7472\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7334\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7257\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7254\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7140\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7108\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 7021\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7477\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 7173\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6845A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"Ru 147\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6861A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6763\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"Pal 9\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6689\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6668\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6667\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6678\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6660\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6574\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6610\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6599\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6600\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6550\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6510\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6497\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6498\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6468\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6427\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6431\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6374\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6216\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6222\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6189\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6191\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6170\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6176\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6125\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"NGC 6128\" where name = \"NGC 6127\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 6128\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"PK 342+10.1\" where name = \"NGC 6072\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6028\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6046\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5907\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5826\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5870\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5868\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5825\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5778\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5841\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5808\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5819\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5785\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5796\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5699\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5706\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5704\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5708\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5703\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5709\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5652\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5650\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5649\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5589\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5588\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5580\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5590\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5575\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5578\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5570\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5519\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5564\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5554\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5552\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5558\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5375\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5396\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5502\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5503\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5317\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5364\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5219\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5244\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5174\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5162\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5109\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5113\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5070\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5072\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5110\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 5111\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4993\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4994\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 5069\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4960\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4961\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4952\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4962\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4972\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4882\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4888\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4797\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4798\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4804\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where alternative_name = \"NGC 4759-1\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4759\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4624\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4728A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4650B\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4537\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4542\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4521\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4512\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4437\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4357\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4381\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4355\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4505\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4496B\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4364\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4362\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4325\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4368\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4354\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4323\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4265\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4303A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4301\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4211A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4243\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4163\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4167\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4180\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4182\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4140\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4149\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4154\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4119\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4124\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4130\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4107\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4078\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4122\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4113\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4055\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4061\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4057\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4065\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4059\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4070\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4014\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 4028\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4046\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 4007\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3980\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3971\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3984\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3966\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3986\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3922\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3924\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3917A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3890\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3939\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3858\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3899\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3912\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3854\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3856\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3826\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3830\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3822\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3848\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3825\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3852\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3807\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3794\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3795A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"UGC 6591\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3704\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3695\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3698\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3645\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3630\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3544\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3611\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3604\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3559\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3560\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3566\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3548\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3540\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3557A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3480\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3476\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3497\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3531\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3110\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3518\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3505\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3508\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3479\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3502\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"NGC 3500\" where name = \"NGC 3465\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3500\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3460\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3428\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3429\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3388\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3425\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3402\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3411\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3397\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3332\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3342\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3339\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3340\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3322\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3284\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3286\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3234\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3235\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set name = \"He 2-55\" where name = \"PK 286-4.1\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"He 2-55\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3194\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3191\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3192\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3189\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3174\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 3122\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3121\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"UGC 5425\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3103\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 3050\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2999\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2972\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2869\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2869\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2863\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2816\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2742\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2733\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2727\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"UGC 4506\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2475\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2443\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2431\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2436\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2382\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2356\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2316\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2317\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2302\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 2299\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2273A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 2239\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1995\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1974\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1991\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1911\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1915\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"IC 2118\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1909\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1882\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1884\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1854\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1781\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1689\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1649\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1652\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1626\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1593\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1577\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1575\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1570\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1571\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1551\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1471\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1457\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1455\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1457\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1452\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1446\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1442\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1436\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1437\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1424\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1380B\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1367\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1340\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1318\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1307\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1269\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1233\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1235\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1205\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1174\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1142\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1144\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1143\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1141\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1123\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 961\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1051\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 1006\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 983\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 1002\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 994\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 930\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 885\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 867\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 866\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 859\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 847\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 755\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 763\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 757\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 731\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 727\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 729\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 724\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 674\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 697\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 614\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 627\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 618\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 608\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 580\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 539\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 563\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 523\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 537\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 341A\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 203\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 211\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 171\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 153\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 58\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "delete from objects where name = \"NGC 17\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 29\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 21\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 20\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"NGC 6\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"IC 1274\";";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "update objects set alternative_name = \"\" where name = \"IC 1275\";";
 $run = mysql_query($sql) or die(mysql_error());

 // Splitting size in diam1 and diam2
 // Add diam1 and diam2 to the object
 $sql = "ALTER TABLE objects ADD diam1 float NOT NULL default ''";
 $run = mysql_query($sql) or die(mysql_error());

 $sql = "ALTER TABLE objects ADD diam2 float NOT NULL default ''";
 $run = mysql_query($sql) or die(mysql_error());


 // Correct the diameter of the objects
 $sql = "SELECT * FROM objects;";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
  $diameter1 = 0.0;
  $diameter2 = 0.0;

  $diam = explode("x", $get->size);

  if ($diam[0] != "")
  {
   if ($diam[0][strlen($diam[0]) - 2] == "'")
   {
    $diam1 = explode("'", $diam[0]);
    $diameter1 = $diam1[0];
   }
   else
   {
    $diam1 = explode("'", $diam[0]);
    $diameter1 = $diam1[0] * 60.0;
   }
  }
  if ($diam[1] != "")
  {
   if ($diam[1][strlen($diam[1]) - 2] == "'")
   {
    $diam2 = explode("'", $diam[1]);
    $diameter2 = $diam2[0];
   }
   else
   {
    $diam2 = explode("'", $diam[1]);
    $diameter2 = $diam2[0] * 60.0;
   }
  }
  $name = $get->name;

  if ($diameter1 != 0.0)
  {
   $sql2 = "UPDATE objects SET diam1 = \"$diameter1\" WHERE name = \"$name\"";
   $run2 = mysql_query($sql2) or die(mysql_error());
  }
  if ($diameter2 != 0.0)
  {
   $sql2 = "UPDATE objects SET diam2 = \"$diameter2\" WHERE name = \"$name\"";
   $run2 = mysql_query($sql2) or die(mysql_error());
  }
 }


 $sql = "SELECT * FROM objects where diam2 = \"0.0\";";
 $run = mysql_query($sql) or die(mysql_error());

 while($get = mysql_fetch_object($run))
 {
  $name = $get->name;
  $sql2 = "UPDATE objects SET diam2 = \"\" WHERE name = \"$name\"";
  $run2 = mysql_query($sql2) or die(mysql_error());
 }

 $sql = "ALTER TABLE objects DROP size";
 $run = mysql_query($sql) or die(mysql_error());



 $db->logout();

 echo "Database was updated succesfully!\n";
?>

