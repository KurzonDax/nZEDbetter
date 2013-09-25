<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Randy
 * Date: 8/31/13
 * Time: 8:27 PM
 *
 */
require(dirname(__FILE__) . "/config.php");
require_once(WWW_DIR . "/lib/framework/db.php");
require_once(WWW_DIR . "/lib/consoletools.php");


$consoletools = new ConsoleTools();

echo "\nAttempting to rebuild the groupID column in the parts table.\n";
echo "This process could take quite a while to complete.\n";
$confirmation = $consoletools->getUserInput("To continue with this process, please type YES: ");
if($confirmation==='YES')
{
    echo "\n";
    rebuildGroupID();
}
exit ("\nThanks for playing.  We're all done now.\n");

function rebuildGroupID()
{
    $db = new DB();
    $consoletools = new ConsoleTools();
    $collections = $db->queryDirect("SELECT ID, groupID FROM collections");
    $colCount = $db->getNumRows($collections);
    $colsProccessed = 0;
    $partsUpdated = 0;
    $timeToComplete = microtime(true);
    while($colRow = $db->fetchAssoc($collections))
    {
        $colsProccessed ++;
        $consoletools->overWrite("Working on collection ".$consoletools->percentString($colsProccessed, $colCount));
        $db->query("UPDATE parts SET groupID=".$colRow['groupID']." WHERE collectionID=".$colRow['ID']);
        $partsUpdated += $db->getAffectedRows();
    }
    $timeToComplete = microtime(true) - $timeToComplete;
    echo "\nWhew... that was a lot of work...\n";
    echo "Total parts updated: ".number_format($partsUpdated)."\n";
    echo "Time to complete: ".number_format($timeToComplete,2)." seconds\n";

}