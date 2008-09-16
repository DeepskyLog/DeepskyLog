CREATE TABLE `atlasses` (
  `atlasCode` VARCHAR(100) NOT NULL DEFAULT 'Atlas',
  `atlasNr` INTEGER NOT NULL DEFAULT -1,
  PRIMARY KEY (`atlasCode`)
);

INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('urano',        0);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('urano_new',    1);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('sky',          2);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('milleniumbase',3);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('taki',         4);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('psa',          5);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('torresB',      6);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('torresBC',     7);
INSERT INTO atlasses(atlasCode, atlasNr) VALUES ('torresC',      8);

ALTER TABLE `objects` ADD COLUMN `milleniumbase` VARCHAR(4) NOT NULL AFTER `torresC`;

UPDATE objects SET milleniumbase=(LEFT(objects.millenium, INSTR(objects.millenium, '/')-1));