CREATE TABLE `atlasses` (
  `atlasCode` VARCHAR(100) NOT NULL DEFAULT 'Atlas',
  PRIMARY KEY (`atlasCode`)
);

INSERT INTO atlasses(atlasCode) VALUES ('urano');
INSERT INTO atlasses(atlasCode) VALUES ('urano_new');
INSERT INTO atlasses(atlasCode) VALUES ('sky');
INSERT INTO atlasses(atlasCode) VALUES ('milleniumbase');
INSERT INTO atlasses(atlasCode) VALUES ('taki');
INSERT INTO atlasses(atlasCode) VALUES ('psa');
INSERT INTO atlasses(atlasCode) VALUES ('torresB');
INSERT INTO atlasses(atlasCode) VALUES ('torresBC');
INSERT INTO atlasses(atlasCode) VALUES ('torresC');

ALTER TABLE `objects` ADD COLUMN `milleniumbase` VARCHAR(4) NOT NULL AFTER `torresC`;

UPDATE objects SET milleniumbase=(LEFT(objects.millenium, INSTR(objects.millenium, '/')-1));