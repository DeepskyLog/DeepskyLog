CREATE TABLE `deepskylog`.`observerobjectlist` (
  `observerid` VARCHAR(255) NOT NULL,
  `objectname` VARCHAR(255) NOT NULL,
  `listname` VARCHAR(255) NOT NULL,
  INDEX Index_observer(`observerid`),
  INDEX Index_list(`observerid`, `listname`)
)
ENGINE = MyISAM;