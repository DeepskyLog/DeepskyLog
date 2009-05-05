<?php
ALTER TABLE `dsltrunk`.`observations` ADD COLUMN `magnification` VARCHAR(6) NOT NULL DEFAULT '' AFTER `dateDec`;
?>