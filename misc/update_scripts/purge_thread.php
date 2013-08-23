<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Randy
 * Date: 8/19/13
 * Time: 2:12 PM
 *
 *
 * TODO: Need to somehow put code in that detects whether FULL_PURGE_FREQ has changed
 */

If (!isset($argv[1]))
{
    exit ("Maximum number of collections to delete is not set.  Exiting.\n");
}

require(dirname(__FILE__)."/config.php");
require_once(WWW_DIR."lib/releases.php");
require_once(WWW_DIR."lib/framework/db.php");
require_once(WWW_DIR."lib/consoletools.php");

$db = new DB();
$releases = new Releases();

$next_full_purge = $db->queryOneRow("SELECT VALUE AS next_purge FROM `tmux` WHERE SETTING = 'NEXT_FULL_PURGE'");

$maxdeletions = $argv[1];

echo "\n".date("H:i:s A")." Purge thread starting with maximum \ncollections to delete limit of ".number_format($maxdeletions)."\n";
if($next_full_purge['next_purge'] == null)
{
    setNextPurge(false);
}
else
    echo "\n Next full purge will be on ".date("M d H:i A", $next_full_purge['next_purge'])."\n";

$timestart = TIME();
$releases->processReleasesStage7a('', true, $maxdeletions);
if ($next_full_purge['next_purge'] != null && time() > $next_full_purge['next_purge'])
{
    echo "\n\033[01;31m[".date("H:i:s")."] Beginning full purge...\n\033[00;37m";
    $releases->processReleasesStage7b('', true);
    setNextPurge(true);
}

$consoletools = new ConsoleTools();
$time = $consoletools->convertTime(TIME() - $timestart);
exit ("\n\nPurge thread completed in ".$time." seconds.\nHave a nice day...\n");

function setNextPurge($scheduled=true)
{
    $db = new DB();
    $full_purge_freq = $db->queryOneRow("SELECT VALUE AS purge_freq FROM `tmux` WHERE SETTING = 'FULL_PURGE_FREQ'");
    $timeNextPurge=(time()+($full_purge_freq['purge_freq']*3600));
    // echo "\nPURGE_FREQ = ".$full_purge_freq['purge_freq']." Next Purge = ".date("M d H:i", $timeNextPurge)."\nCurrent time = ".date("M d H:i", time())."\n";
    $db->query("UPDATE tmux SET VALUE=".$timeNextPurge." WHERE SETTING='NEXT_FULL_PURGE'");
    sleep(45);
    if(!$scheduled)
        echo "\n\033[01;33mNext full purge was not scheduled in database\nNow scheduled for ".date("M d H:i A", $timeNextPurge)."\n\033[00;37m";
    else
        echo "\nNext full purge will be on ".date("M d H:i A", $timeNextPurge)."\n";
}