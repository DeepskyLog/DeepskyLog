ALTER TABLE `observers` ADD COLUMN `standardAtlasCode` VARCHAR(100) NOT NULL DEFAULT '' AFTER `usedLanguages`;

UPDATE observers SET standardAtlasCode = 'urano' WHERE stdAtlas=0;
UPDATE observers SET standardAtlasCode = 'urano_new' WHERE stdAtlas=1;
UPDATE observers SET standardAtlasCode = 'sky' WHERE stdAtlas=2;
UPDATE observers SET standardAtlasCode = 'milleniumbase' WHERE stdAtlas=3;
UPDATE observers SET standardAtlasCode = 'taki' WHERE stdAtlas=4;
UPDATE observers SET standardAtlasCode = 'psa' WHERE stdAtlas=5;
UPDATE observers SET standardAtlasCode = 'torresB' WHERE stdAtlas=6;
UPDATE observers SET standardAtlasCode = 'torresBC' WHERE stdAtlas=7;
UPDATE observers SET standardAtlasCode = 'torresC' WHERE stdAtlas=8;

