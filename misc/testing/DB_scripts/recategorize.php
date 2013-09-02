<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 9/1/13
 * Time: 6:34 PM
 * File: recategorize.php
 * This script allows you to specify a category ID in which all releases will be reprocessed through the
 * determineCategory function.  This is handy if you've been tweaking the regex in category.php and want
 * to reprocess the Other->Misc or Other->Hashed categories, or if you've ended up with a bunch of releases
 * in a wrong category for some reason.
 *
 * NOTE: You must have a directory called logging under your www/lib directory, with full write access
 *
 * Added ability to enter a parent category ID to reprocess all categories under the parent (i.e. 2000 for
 * all movies)
 */

require(dirname(__FILE__)."/../../../www/config.php");
require_once(WWW_DIR."lib/framework/db.php");
require_once(WWW_DIR."lib/category.php");
require_once(WWW_DIR."lib/consoletools.php");

$db = new DB();
$category = new Category();
$consoletools = new ConsoleTools();
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
    $sql = "SELECT ID, name, searchname, groupID, categoryID FROM releases WHERE categoryID BETWEEN ".$catToProcess." AND ".($catToProcess+999);
else
    $sql = "SELECT ID, name, searchname, groupID, categoryID FROM releases WHERE categoryID=".$catToProcess;

if($relsToProcess = $db->queryDirect($sql))
{
    echo "\n";
    $relsCount = $db->getNumRows($relsToProcess);
    $relsProcessed = 0;
    $relsChanged = 0;
    while($relRow=$db->fetchAssoc($relsToProcess))
    {
        $relsProcessed ++;
        $newCategory = $category->determineCategory($relRow['name'],$relRow['groupID']);
        $consoletools->overWrite("Working on release ".$consoletools->percentString($relsProcessed, $relsCount)." Updates: ".$relsChanged);
        if($newCategory != $relRow['categoryID'])
        {
            $db->query("UPDATE releases SET categroyID=".$newCategory." WHERE ID=".$relRow['ID']);
            file_put_contents(WWW_DIR."lib/logging/recategorize.log",$relRow['ID'].", '".$relRow['searchname']."', ".$newCategory."\n", FILE_APPEND);
            $relsChanged ++;
        }
    }
}
exit ("\n\nThanks for playing. Total releases changed: ".number_format($relsChanged)."\nThe log file can be found at: ".WWW_DIR."lib/logging/recategorize.log\n");
