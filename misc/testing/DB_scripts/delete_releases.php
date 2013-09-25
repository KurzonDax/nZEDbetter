<?php

define('FS_ROOT', realpath(dirname(__FILE__)));
require_once(FS_ROOT."/../../../www/config.php");
require_once(FS_ROOT."/../../../www/lib/framework/db.php");
require_once(FS_ROOT."/../../../www/lib/releases.php");
require_once(FS_ROOT."/../../../www/lib/site.php");
require_once(FS_ROOT."/../../../www/lib/consoletools.php");
require_once(FS_ROOT."/../../../www/lib/groups.php");

$db = new Db;
$s = new Sites();
$consoletools = new ConsoleTools();
$site = $s->get();
$timestart = TIME();
$relcount = 0;

//
//	This script removes all releases and nzb files based on poster, searchname, name or guid
//
if (sizeof($argv) == 4)
{
	if ($argv[2] == "equals" && ($argv[1] == "searchname" || $argv[1] == "name" || $argv[1] == "guid" || $argv[1] == "fromname" || $argv[1] == "categoryID"))
	{
		$relids = $db->queryDirect(sprintf("SELECT ID, guid FROM releases where %s = %s", $argv[1], $db->escapeString($argv[3])));
		printf("SELECT ID, guid FROM releases where %s = %s", $argv[1], $db->escapeString($argv[3]));
	}
	elseif ($argv[2] == "like" && ($argv[1] == "searchname" || $argv[1] == "name" || $argv[1] == "guid" || $argv[1] == "fromname"))
	{
		$relids = $db->queryDirect("SELECT ID, guid FROM releases where ".$argv[1]." like '%".$argv[3]."%'");
		print("SELECT ID, guid FROM releases where ".$argv[1]." like '%".$argv[3]."%'");
	}
    elseif ($argv[1] == "group")
    {
        $groups = new Groups();
        if(!is_numeric($argv[3]) && $argv[2] != "like")
        {
            $groupIDs = $groups->getByName($argv[3]);

            if($groupIDs == false)
            {
                Echo "\nGroup ".$argv[3]." not found in the database.  Please try again.\n";
                exit;
            }
            $groupID = $groupIDs['ID'];
            $groupname = $argv[3];
        }
        else
        {
            $groupnames = $groups->getByID($argv[3]);
            if(!$groupnames)
            {
                echo "\n Group ID ".$argv[3]." not found in database.  Please try again.\n";
                exit;
            }
            $groupID = $argv[3];
        }
        if($argv[2] == "equals")
        {
            $relids = $db->queryDirect("SELECT ID, guid FROM releases where groupID=".$groupID);

        }
        elseif($argv[2] == "like")
        {
            echo "\n Unfortunately, the \"like\" keyword is not currently supported when deleting releases by group.\n";
            exit;
        }
    }
	else
	{
		exit("This script removes all releases and nzb files from a poster or by searchname, name or guid.\nIf you are sure you want to run it, type php delete_releases.php [ fromname, searchname, name, guid ] equals [ name/guid ]\nYou can also use like instead of = by doing type php delete_releases.php [ fromname, searchname, name, guid ] like [ name/guid ]\n");
	}
    $reltotalcount = $db->getNumRows($relids);

    echo "\nDeleting ".$reltotalcount." releases and NZB's for ".$argv[3]."\n";
	$releases = new Releases();

	foreach ($relids as $relid)
	{
		$releases->fastDelete($relid['ID'], $relid['guid'], $site);
		$relcount++;
		$consoletools->overWrite("Deleting:".$consoletools->percentString($relcount,$reltotalcount)." Time:".$consoletools->convertTimer(TIME() - $timestart));
	}

	if ($relcount > 0)
		echo "\n";
	echo "Deleted ".$relcount." release(s). This script ran for ";
	echo $consoletools->convertTime(TIME() - $timestart);
	echo ".\n";
}
else
{
	exit("This script removes all releases and nzb files from a poster or by searchname, name or guid.\nIf you are sure you want to run it, type php delete_releases.php [ fromanme, searchname, name, guid ] equals [ name/guid ]\nYou can also use like instead of = by doing type php delete_releases.php [ fromname, searchname, name, guid ] like [ name/guid ]\n");
}
