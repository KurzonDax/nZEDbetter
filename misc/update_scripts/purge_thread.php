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
$next_dead_check = $db->queryOneRow("SELECT VALUE AS next_dead_check FROM `tmux` WHERE SETTING = 'NEXT_DEAD_COLLECTION_CHECK'");
$dead_hours = $db->queryOneRow("SELECT VALUE AS dead_hours FROM `tmux` WHERE SETTING = 'DEAD_COLLECTION_CHECK_HOURS'");

$maxdeletions = $argv[1];

echo "\n".date("H:i:s A")." Purge thread starting with maximum \ncollections to delete limit of ".number_format($maxdeletions)."\n";
if($next_full_purge['next_purge'] == null)
{
    setNextPurge(false);
}
else
    echo "\n Next full purge will be on ".date("M d H:i A", $next_full_purge['next_purge'])."\n";
if(($next_dead_check['next_dead_check']==0 || is_null($next_dead_check['next_dead_check'])) && $dead_hours['dead_hours'] > 0)
{
    setNextDeadCheck(false);
}
elseif($dead_hours['dead_hours']>0)
    echo "\n Next stale collections purge will be at ".date("H:i A", $next_dead_check['next_dead_check'])."\n";
else
    echo "\n Stale collection purge has been disabled.\n";
$timestart = TIME();
$releases->processReleasesStage7a('', true, $maxdeletions);
if ($next_dead_check['next_dead_check'] != null && time() > $next_dead_check['next_dead_check'] - 1800 && $dead_hours['dead_hours'] > 0)
    $releases->removeIncompleteReleases(true);
if ($next_dead_check['next_dead_check'] !=null && time() > $next_dead_check['next_dead_check'] && $dead_hours['dead_hours'] > 0)
{
    echo "\n\033[01;31m[".date("H:i:s")."] Beginning stale collection check...\n\033[00;37m";
    $releases->checkDeadCollections($dead_hours['dead_hours']);
    echo "\n\033[01;31m[" . date("H:i:s") . "] Beginning unknown files collection check...\n\033[00;37m";
    $releases->checkZeroTotalFilesCollections();
    setNextDeadCheck(true);
}

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
        echo "\n\033[01;33mNext full purge was not scheduled in database.\nNow scheduled for ".date("M d H:i A", $timeNextPurge)."\n\033[00;37m";
    else
        echo "\nNext full purge will be on ".date("M d H:i A", $timeNextPurge)."\n";
}
function setNextDeadCheck($scheduled=true)
{
    $db = new DB();
    $timeNextCheck=(time()+3600); // Set for one hour from now
    $db->query("UPDATE tmux SET VALUE=".$timeNextCheck." WHERE SETTING='NEXT_DEAD_COLLECTION_CHECK'");
    sleep(45);
    if(!$scheduled)
        echo "\n\033[01;33mNext dead collection check was not scheduled in database.\nNow scheduled for ".date("M d H:i A", $timeNextCheck)."\n\033[00;37m";
    else
        echo "\nNext dead collection check will be at ".date("H:i A", $timeNextCheck)."\n";
}