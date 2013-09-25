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

$consoletools = new ConsoleTools();
$db = new DB();
$category = new Category();

echo "\nWelcome to the MusicBrainz test script.\n";
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

$musicReleases = $db->queryDirect($sql);
$totalReleases = $db->getNumRows($musicReleases);
echo "\nWe found ".number_format($totalReleases)." music releases to process.\n";

while($musicRow = $db->fetchAssoc($musicReleases))
{
    echo "\nRelease ID: ".$musicRow['ID']."\n";
    echo "Release name: ".$musicRow['name']."\n";
    echo "Search name: ".$musicRow['searchname']."\n";
    $result = getArtist($musicRow['searchname']);
    echo "\nHere's what we got for an Artist lookup:\n";
    print_r($result);
    $consoletools->getUserInput("\nPress enter to continue: ");
    echo "\nHere's what we got for a Release lookup:\n";
    $result = getReleaseName($musicRow['searchname']);
    print_r($result);
    $consoletools->getUserInput("\nPress enter to continue: ");
    echo "\nHere's what we got for a Recording lookup:\n";
    $result = getRecording($musicRow['searchname']);
    print_r($result);
    $consoletools->getUserInput("\nPress enter to continue: ");
}

exit ("\nThanks for playing...\n");

function getArtist($query)
{
    $mb = new MusicBrainz();
    return $mb->searchArtist($query);

}
function getReleaseName($query)
{
    $mb = new MusicBrainz();
    return $mb->searchRelease($query);
}
function getRecording($query)
{
    $mb = new MusicBrainz();
    return $mb->searchRecording($query);
}