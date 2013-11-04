ALTER TABLE  `parts` 	DROP PRIMARY KEY ,
						ADD PRIMARY KEY (  `ID` ) COMMENT  '',
						ADD INDEX  `ix_binID_size` (  `binaryID` ,  `size` ) COMMENT  '',
						DROP INDEX  `ix_colID_size`,
						DROP  `binarySize` ,
						DROP  `PartsInDB` ;
ALTER TABLE  `binaries` ADD  `postDate` DATETIME NULL AFTER  `collectionID`,
						ADD  `originalSubject` VARCHAR( 400 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
						DROP  `partsize`,
						ADD INDEX  `ix_colID_postDate` (  `collectionID` ,  `postDate` ) COMMENT  '';
ALTER TABLE  `collections` 	ADD  `oldestBinary` DATETIME NULL AFTER  `xref` ,
							ADD  `newestBinary` DATETIME NULL AFTER  `oldestBinary`,
							ADD INDEX (  `oldestBinary` ),
							ADD INDEX (  `newestBinary` ) ;
INSERT INTO `tmux` (`ID`, `setting`, `value`, `updateddate`) VALUES (NULL, 'NEXT_DEAD_COLLECTION_CHECK', '0', CURRENT_TIMESTAMP), (NULL, 'DEAD_COLLECTION_CHECK_HOURS', '6', CURRENT_TIMESTAMP);
UPDATE `site` SET `value` = 'v0001' WHERE `setting` = 'sqlpatch';
