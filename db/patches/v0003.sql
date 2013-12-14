ALTER TABLE parts DROP `ID`, DROP INDEX parthash, ADD PRIMARY KEY (`parthash`);
ALTER TABLE `movieinfo` CHANGE `imdbID` `imdbID` INT ( 8 ) NOT NULL;
UPDATE `site` SET `VALUE` = '0.7' WHERE `setting` = 'NZEDBETTER_VERSION';
ALTER TABLE `predb`
    ADD `md2` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `md4` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `sha1` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `ripemd128` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `ripemd160` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `tiger128_3` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `tiger160_3` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `tiger128_4` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `tiger160_4` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `haval128_3` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `haval160_3` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `haval128_4` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `haval160_4` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `haval128_5` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `haval160_5` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD `releaseGroup` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    ADD INDEX `ix_md2` (`md2`(8)), ADD INDEX `ix_md4` (`md4`(8)),  ADD INDEX `ix_sha1` (`sha1`(8)),
    ADD INDEX `ix_ripemd128` (`ripemd128`(8)), ADD INDEX `ix_ripemd160` (`ripemd160`(8)),  ADD INDEX `ix_tiger128_3` (`tiger128_3`(8)),
    ADD INDEX `tiger160_3` (`tiger160_3`(8)), ADD INDEX `ix_tiger128_4` (`tiger128_4`(8)), ADD INDEX `ix_tiger160_4` (`tiger160_4`(8)),
    ADD INDEX `ix_haval128_3` (`haval128_3`(8)), ADD INDEX `ix_haval160_3` (`haval160_3`(8)), ADD INDEX `ix_haval128_4` (`haval128_4`(8)),
    ADD INDEX `ix_haval160_4` (`haval160_4`(8)), ADD INDEX `ix_haval128_5` (`haval128_5`(8)), ADD INDEX `ix_haval160_5` (`haval160_5`(8)),
    ADD INDEX `ix_releaseGroup` (`releaseGroup`);
CREATE TABLE IF NOT EXISTS `movieGenres` (
  `ID` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `similarGenres` varchar(400) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dateUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `movieIDtoGenre` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `movieID` int(11) NOT NULL,
  `genreID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `movieID` (`movieID`,`genreID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
UPDATE `site` SET `VALUE` = 'v0003' WHERE `setting` = 'sqlpatch';