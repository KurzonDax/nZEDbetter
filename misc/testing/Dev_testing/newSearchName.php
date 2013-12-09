<?php
/**
 * Project: nZEDb
 * User: Randy
 * Date: 9/20/13
 * Time: 1:14 PM
 * File: newSearchName.php
 * 
 */
require(dirname(__FILE__) . "/config.php");
require_once(WWW_DIR . "/lib/framework/db.php");
require_once(WWW_DIR . "/lib/consoletools.php");
require_once(WWW_DIR . "/lib/namecleaning.php");

$db = new DB();
$category = new Category();
$consoletools = new ConsoleTools();
$gotIt = false;
$msg = '';
do {

    $catToProcess = $consoletools->getUserInput("\nPlease enter the category ID or Parent ID that you want to reprocess, or type quit to exit: ");
    if(is_numeric($catToProcess) && $category->getById($catToProcess) != false)
        $gotIt = true;
    elseif($catToProcess=='quit')
        exit ("\nThanks for playing.  We'll see you next time.\n");
    else
        echo "\n\nYou specified an invalid category ID.  Please try again.\n";

} while ($gotIt==false);

if($category->isParent($catToProcess))
    $sql = "SELECT ID, name, searchname, groupID, categoryID FROM releases WHERE categoryID BETWEEN ".$catToProcess." AND ".($catToProcess+999);
else
    $sql = "SELECT ID, name, searchname, groupID, categoryID FROM releases WHERE categoryID=".$catToProcess;

if($relsToProcess = $db->queryDirect($sql))
{
    echo "\n";
    $totalRows = $db->getNumRows($relsToProcess);
    $relsProcessed = 0;
    $relsChanged = 0;
    $namecleaner = new nameCleaning();
    while($currentRow = $db->fetchAssoc($relsToProcess))
    {
        $newSearchName = $namecleaner->releaseCleaner($currentRow['name']);
        $relsProcessed ++;
        $consoletools->overWrite("Working on release ".$consoletools->percentString($relsProcessed, $totalRows)." ID: ".$currentRow['ID']." Changed: ".$relsChanged);
        if($db->escapeString($newSearchName) != $db->escapeString($currentRow['searchname']))
        {
            $msg = $currentRow['searchname'] . "\n";
            $msg .= $newSearchName . "\n";
            $msg .= '--------------------------------------------------------------\n';
            file_put_contents(WWW_DIR . "/lib/logging/searchnameFix.log", $msg, FILE_APPEND);
            $db->query("UPDATE releases SET searchname=".$db->escapeString($newSearchName)." WHERE ID=".$currentRow['ID']);
            $relsChanged ++;
        }
    }
}
exit ("\nAll done.  Release whose searchname was changed: ".$relsChanged."\n");

