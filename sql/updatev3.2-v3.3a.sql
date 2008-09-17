CREATE TABLE `atlasses` (
  `atlasCode` VARCHAR(100) NOT NULL DEFAULT 'Atlas',
  PRIMARY KEY (`atlasCode`)
);

INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('urano');
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('urano_new');
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('sky');
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('milleniumbase');
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('taki');
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('psa');
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('torresB',);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('torresBC');
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('torresC');

ALTER TABLE `objects` ADD COLUMN `milleniumbase` VARCHAR(4) NOT NULL AFTER `torresC`;

UPDATE objects SET milleniumbase=(LEFT(objects.millenium, INSTR(objects.millenium, '/')-1));