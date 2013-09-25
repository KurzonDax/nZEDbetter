<?php
/**
 * Project: nZEDb
 * User: Randy
 * Date: 9/4/13
 * Time: 5:37 PM
 * File: recat_rename.php
 * Similar to the recategorize.php, but this one will also run the release back through the name cleaning script as well
 * 
 */

require(dirname(__FILE__)."/../../../www/config.php");
require_once(WWW_DIR."lib/framework/db.php");
require_once(WWW_DIR."lib/category.php");
require_once(WWW_DIR."lib/consoletools.php");
require_once(WWW_DIR."/lib/namecleaning.php");

$db = new DB();
$category = new Category();
$consoletools = new ConsoleTools();
$namecleaner = new nameCleaning();

$gotIt = false;
do {

    $catToProcess = $consoletools->getUserInput("\nPlease enter the category ID or Parent ID that you want to reprocess, or type quit to exit: ");
    if(is_numeric($catToProcess) && $category->getById($catToProcess) != false)
        $gotIt = true;
    elseif($catToProcess=='quit')
        exit ("\nThanks for playing.  We'll see you next time.\n");
    else
        echo "\n\nYou specified an invalid category ID.  Please try again.\n";

} while ($gotIt==false);

if($catToProcess % 1000 === 0)
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE categoryID BETWEEN ".$catToProcess." AND ".($catToProcess+999);
else
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE categoryID=".$catToProcess;
$resetMusic = false;
if (isset($argv[1]) && $argv[1]=="--resetmusic")
{
    $resetMusic = true;
    echo "\nReset music option is enabled. Only applies to music that has already been processed.";
}
if($relsToProcess = $db->queryDirect($sql))
{
    echo "\n";
    $relsCount = $db->getNumRows($relsToProcess);
    $relsProcessed = 0;
    $relsChanged = 0;
    $relsRenamed = 0;
    while($relRow=$db->fetchAssoc($relsToProcess))
    {
        $relsProcessed ++;

        $consoletools->overWrite("Working on release ".$consoletools->percentString($relsProcessed, $relsCount)." Updates: ".$relsChanged);
        $newSearchName = $namecleaner->releaseCleaner($relRow['name']);
        if($newSearchName != $relRow['searchname'])
        {
            $db->query("UPDATE releases SET searchname=".$db->escapeString($newSearchName)." WHERE ID=".$relRow['ID']);
            $relsRenamed ++;
        }
        $newCategory = $category->determineCategory($relRow['name'],$relRow['groupID']);
        if($newCategory != $relRow['categoryID'])
        {
            $db->query("UPDATE releases SET categroyID=".$newCategory." WHERE ID=".$relRow['ID']);
            file_put_contents(WWW_DIR."lib/logging/recategorize.log",$relRow['ID'].", '".$relRow['searchname']."', ".$newCategory."\n", FILE_APPEND);
            $relsChanged ++;
        }
        if($resetMusic && $relRow['musicinfoID']=='-2')
            $db->query("UPDATE releases SET musicinfoID=0 WHERE ID=".$relRow['ID']);

    }
}
exit ("\n\nThanks for playing. Total releases changed: ".number_format($relsChanged)."\nTotal releases renamed: ".$relsRenamed."\n\nThe log file can be found at: ".WWW_DIR."lib/logging/recategorize.log\n");