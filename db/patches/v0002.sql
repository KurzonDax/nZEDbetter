INSERT INTO `nzedbetter` .`tmux` ( `ID` , `setting` , `value` , `updateddate` ) VALUES (NULL, 'REMOVE_CRAP_HOURS', '6', CURRENT_TIMESTAMP);
INSERT INTO `nzedbetter` .`site` ( `ID` , `setting` , `value` , `updateddate` ) VALUES (NULL, 'NZEDBETTER_VERSION', '0.6', CURRENT_TIMESTAMP);
UPDATE `site` SET `VALUE = 'v0002' WHERE `setting` = 'sqlpatch';