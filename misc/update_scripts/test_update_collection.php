<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Randy
 * Date: 8/17/13
 * Time: 9:30 PM
 * To change this template use File | Settings | File Templates.
 */
require(dirname(__FILE__)."/config.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/consoletools.php");

$db = new DB();
$consoletools = new ConsoleTools();

$collectionHash = $consoletools->getUserInput("\nEnter a known existing hash for a collection: ");
$subject = "Test Subject";
$from = "test@test.com";
$xref = "alt.binaries.test";
$date = "2013-08-08 09:47:16";
$groupID = 1;
$maxfiles = 199;


$csql = sprintf("INSERT INTO collections (subject, fromname, date, xref, groupID, totalFiles, collectionhash, dateadded) VALUES (%s, %s, %s, %s, %d, %s, %s, now()) ON DUPLICATE KEY UPDATE dateadded=now()", $db->escapeString($subject), $db->escapeString($from), $db->escapeString($date), $db->escapeString($xref), $groupID, $db->escapeString($maxfiles), $db->escapeString($collectionHash));
$colInsertResult = $db->queryInsert($csql);
$colrow = $db->getAffectedRows();
echo "\nGet affected row = ".$colrow."\n";
exit;

