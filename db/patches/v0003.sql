ALTER TABLE parts DROP `ID`, DROP INDEX parthash, ADD PRIMARY KEY (`parthash`);
ALTER TABLE `movieinfo` CHANGE `imdbID` `imdbID` INT ( 8 ) NOT NULL;
UPDATE `site` SET `VALUE` = '0.7' WHERE `setting` = 'NZEDBETTER_VERSION';
UPDATE `site` SET `VALUE` = 'v0003' WHERE `setting` = 'sqlpatch';