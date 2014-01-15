<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 9/7/13
 * Time: 11:26 PM
 * File: musicBrainz_test.php
 *
 */
require(dirname(__FILE__) . "/config.php");
require_once(WWW_DIR . "/lib/framework/db.php");
require_once(WWW_DIR . "/lib/consoletools.php");
require_once(WWW_DIR . "/lib/namecleaning.php");
require_once(WWW_DIR . "/lib/MusicBrainz.php");
require_once(WWW_DIR . "/lib/music.php");
require_once(WWW_DIR . "lib/category.php");
/**
 *
 */
define('DEBUG_ECHO', false);

$consoleTools = new ConsoleTools();
$db = new DB();
$category = new Category();
$musicBrainz = new MusicBrainz();
$matchedReleases = 0;
$singleReleases = 0;
echo "\nWelcome to the MusicBrainz test script.\n";

$catToProcess = 3000;
$offset = '';
$relID = $consoleTools->getUserInput("Enter a release ID, or press enter to search category 3000: ");
if(!is_numeric($relID))
    $offset = $consoleTools->getUserInput("\nPlease enter the offset to begin at: ");
if($offset == '' || !is_numeric($offset))
    $offset = 1;
if(!is_numeric($relID))
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE categoryID BETWEEN ".$catToProcess." AND ".($catToProcess+999)." LIMIT ".$offset.",100";
else
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE ID=".$relID;

$musicReleases = $db->queryDirect($sql);
$totalReleases = $db->getNumRows($musicReleases);
echo "\nWe found ".number_format($totalReleases)." music releases to process.\n";
$nameCleaning = new nameCleaning();
while($musicRow = $db->fetchAssoc($musicReleases))
{
    echo "\n";
    if(preg_match('/bootleg/i', $musicRow['name']) === 1)
    {
        if(DEBUG_ECHO)
        {
            echo "Skipping bootleg release: " . $musicRow['name'] . "\n";
            $consoleTools->getUserInput('Press enter to continue...');
        }
        continue;
    }

    $musicBrainz->processMusicRelease($musicRow);
    // if(DEBUG_ECHO)
        $consoleTools->getUserInput("\nPress enter to continue: ");
}
echo "\nSingles Found: ".$singleReleases;
echo "\nTotal Releases Matched: ".$matchedReleases."\n";
echo "Match Percentage: ".($totalReleases > 0 ? (($matchedReleases/($totalReleases-$singleReleases)*100)) : '0%')."\n";
exit ("\nThanks for playing...\n");

