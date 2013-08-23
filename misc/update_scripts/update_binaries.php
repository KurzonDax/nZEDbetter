<?php

require(dirname(__FILE__)."/config.php");
require_once(WWW_DIR."/lib/binaries.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/framework/db.php");

$binaries = new Binaries();

if (isset($argv[1]))
{
	if($argv[1]=="--newgroups")
	{
		$timestart = microtime(true);
		GetGroupsNotUpdated();		
		$timetotal = number_format(microtime(true) - $timestart, 2);
		Echo "\n\nAll groups processd in ".$timetotal." seconds\n\n";
		
	}
	else 
	{
	$groupName = $argv[1];
		echo "Updating group: $groupName\n";
		$grp = new Groups();
		$group = $grp->getByName($groupName);
		$binaries->updateGroup($group);
	}
}
else
{
	$binaries->updateAllGroups();
}
die;

function GetGroupsNotUpdated()
{
	$db = new DB();
	$groups = array();
	$groups = $db->querydirect("SELECT * FROM groups WHERE groups.last_updated IS NULL ORDER BY groups.ID ASC");	
	while ($r = $db->fetchAssoc($groups))
	{
		echo "Updating group: ".$r['name']."\n";
		$binaries->updateGroup($r);
		
	}
	return;
}
?>
