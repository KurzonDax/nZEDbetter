<?php
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/nntp.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/binaries.php");

class Groups
{
	public function getAll()
	{
		$db = new DB();

		return $db->query("SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(
							SELECT groupID, COUNT(ID) AS num FROM releases group by groupID
							) rel ON rel.groupID = groups.ID ORDER BY groups.name");
	}

    public function getAllNames()
    {
        $db = new DB();
        $nameArr = $db->queryDirect("SELECT name FROM groups");

        return $nameArr['name'];
    }

	public function getGroupsForSelect()
	{
		$db = new DB();
		$categories = $db->query("SELECT * FROM groups WHERE active = 1 ORDER BY name");
		$temp_array = array();

		$temp_array[-1] = "--Please Select--";

		foreach($categories as $category)
			$temp_array[$category["name"]] = $category["name"];

		return $temp_array;
	}

	public function getByID($id)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from groups where ID = %d ", $id));
	}

	public function getActive()
	{
		$db = new DB();
		return $db->query("SELECT * FROM groups WHERE active = 1 ORDER BY name");
	}

	public function getActiveBackfill()
	{
		$db = new DB();
		return $db->query("SELECT * FROM groups WHERE backfill = 1 ORDER BY name");
	}

	public function getActiveByDateBackfill()
	{
		$db = new DB();
		return $db->query("SELECT * FROM groups WHERE backfill = 1 ORDER BY first_record_postdate DESC");
	}

	public function getActiveIDs()
	{
		$db = new DB();
		return $db->query("SELECT ID FROM groups WHERE active = 1 ORDER BY name");
	}

    public function getActiveIDsWithSizes()
    {
        $db = new DB();
        return $db->query("SELECT ID, minfilestoformrelease, minsizetoformrelease FROM groups WHERE active = 1 ORDER BY name");
    }

	public function getByName($grp)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from groups where name = '%s' ", $grp));
	}

	public function getByNameByID($id)
	{
		$db = new DB();
		$res = $db->queryOneRow(sprintf("select name from groups where ID = %d ", $id));
		return $res["name"];
	}

	public function getIDByName($name)
	{
		$db = new DB();
		$res = $db->queryOneRow(sprintf("select ID from groups where name = %s", $name));
		return $res["ID"];
	}

	// Set the backfill to 0 when the group is backfilled to max.
	public function disableForPost($name)
	{
		$db = new DB();
		$db->queryOneRow(sprintf("UPDATE groups SET backfill = 0 WHERE name = %s", $db->escapeString($name)));
	}

	public function getCount($groupname="")
	{
		$db = new DB();

		$grpsql = '';
		if ($groupname != "")
			$grpsql .= sprintf("and groups.name like %s ", $db->escapeString("%".$groupname."%"));

		$res = $db->queryOneRow(sprintf("select count(ID) as num from groups where 1=1 %s", $grpsql));
		return $res["num"];
	}

	public function getCountActive($groupname="")
	{
		$db = new DB();

		$grpsql = '';
		if ($groupname != "")
			$grpsql .= sprintf("and groups.name like %s ", $db->escapeString("%".$groupname."%"));

		$res = $db->queryOneRow(sprintf("select count(ID) as num from groups where 1=1 %s and active = 1", $grpsql));
		return $res["num"];
	}

	public function getCountInactive($groupname="")
	{
		$db = new DB();

		$grpsql = '';
		if ($groupname != "")
			$grpsql .= sprintf("and groups.name like %s ", $db->escapeString("%".$groupname."%"));

		$res = $db->queryOneRow(sprintf("select count(ID) as num from groups where 1=1 %s and active = 0", $grpsql));
		return $res["num"];
	}

	public function getRange($start, $num, $groupname="")
	{
		$db = new DB();

		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;

		$grpsql = '';
		if ($groupname != "")
			$grpsql .= sprintf("and groups.name like %s ", $db->escapeString("%".$groupname."%"));

		$sql = sprintf("SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(
							SELECT groupID, COUNT(ID) AS num FROM releases group by groupID
							) rel ON rel.groupID = groups.ID WHERE 1=1 %s ORDER BY groups.name ".$limit, $grpsql);
		return $db->query($sql);
	}

	public function getRangeActive($start, $num, $groupname="")
	{
		$db = new DB();

		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;

		$grpsql = '';
		if ($groupname != "")
			$grpsql .= sprintf("and groups.name like %s ", $db->escapeString("%".$groupname."%"));

		$sql = sprintf("SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(
							SELECT groupID, COUNT(ID) AS num FROM releases group by groupID
							) rel ON rel.groupID = groups.ID WHERE 1=1 %s and active = 1 ORDER BY groups.name ".$limit, $grpsql);
		return $db->query($sql);
	}

	public function getRangeInactive($start, $num, $groupname="")
	{
		$db = new DB();

		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;

		$grpsql = '';
		if ($groupname != "")
			$grpsql .= sprintf("and groups.name like %s ", $db->escapeString("%".$groupname."%"));

		$sql = sprintf("SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(
							SELECT groupID, COUNT(ID) AS num FROM releases group by groupID
							) rel ON rel.groupID = groups.ID WHERE 1=1 %s and active = 0 ORDER BY groups.name ".$limit, $grpsql);
		return $db->query($sql);
	}

	public function add($group)
	{
		$db = new DB();

		if ($group["minfilestoformrelease"] == "" || $group["minfilestoformrelease"] == "0")
			$minfiles = '0';
		else
			$minfiles = $group["minfilestoformrelease"] + 0;

		if ($group["minsizetoformrelease"] == "" || $group["minsizetoformrelease"] == "0")
			$minsizetoformrelease = '0';
		else
			$minsizetoformrelease = $db->escapeString($group["minsizetoformrelease"]);

		$first = (isset($group["first_record"]) ? $group["first_record"] : "0");
		$last = (isset($group["last_record"]) ? $group["last_record"] : "0");

		$sql = sprintf("insert into groups (name, description, first_record, last_record, last_updated, active, minfilestoformrelease, minsizetoformrelease) values (%s, %s, %s, %s, null, %d, %s, %s) ",$db->escapeString($group["name"]), $db->escapeString($group["description"]), $db->escapeString($first), $db->escapeString($last), $group["active"], $minfiles, $minsizetoformrelease);
		return $db->queryInsert($sql);
	}

	public function delete($id)
	{
		$db = new DB();
		return $db->query(sprintf("delete from groups where ID = %d", $id));
	}

	public function reset($id)
	{
		$db = new DB();
		return $db->query(sprintf("update groups set backfill_target=0, first_record=0, first_record_postdate=null, last_record=0, last_record_postdate=null, active = 0, last_updated=null where ID = %d", $id));
	}

	public function resetall()
	{
		$db = new DB();
		return $db->query("update groups set backfill_target=0, first_record=0, first_record_postdate=null, last_record=0, last_record_postdate=null, last_updated=null, active = 0");
	}

	public function purge($groupID)
	{
        if(empty($groupID) || $groupID===0 || $groupID=='')
            return 'ERROR: (purge) No group ID provided.';
        $db = new DB();
		$releases = new Releases();

		$error = $this->deleteGroupCollections($groupID);
        if($error != '')
            return $error;

        if(!(is_array($groupID)))
            $where = " groupID=".$groupID;
        else
        {
            $inClause = '(';
            foreach($groupID as $id)
                $inClause .= $id.",";
            $where = " groupID IN ".substr($inClause,0,-1).")";
        }

        // The following will not work when the tmux scripts are being run
        // under a user other than www-data because the nzb directories were
        // created by the tmux user and www-data does not have permissions to
        // delete files from those directories.
        /*
         *  $rels = $db->query("SELECT ID FROM releases WHERE".$where);
                foreach ($rels as $rel)
			$releases->delete($rel["ID"]);
         */
        // As an interim solution, we'll set the groupID to 999999 and add
        // a routine into stage7a in releases.php to check for these releases
        // during the purge thread.  If any are found, we'll delete the release
        // and associated database entries and nzb file then.
        $db->query("UPDATE releases SET groupID=999999 WHERE".$where);
		return 0;
	}

	public function purgeall()
	{
		$db = new DB();
		$releases = new Releases();
		$binaries = new Binaries();

		$this->resetall();

		$rels = $db->query("select ID from releases");
		foreach ($rels as $rel)
			$releases->delete($rel["ID"]);

		$cols = $db->query("select ID from collections");
		foreach ($cols as $col)
			$binaries->delete($col["ID"]);
	}

    public function deleteGroupCollections($groupID)
    {
        if(empty($groupID) || $groupID===0 || $groupID=='')
            return 'ERROR: (deleteGroupCollections) No group ID provided.';
        if(!(is_array($groupID)))
            $where = " groupID=".$groupID;
        else
        {
            $inClause = '(';
            foreach($groupID as $id)
                $inClause .= $id.",";
            $where = " groupID IN ".substr($inClause,0,-1).")";
        }
        $db = new DB();
        $collections = $db->queryDirect("SELECT ID FROM collections WHERE".$where);
        while($colRow = $db->fetchAssoc($collections))
        {
            $db->query("DELETE FROM binaries WHERE collectionID=".$colRow['ID']);
            if($db->Error()){return $db->Error();}
            $db->query("DELETE FROM parts WHERE collectionID=".$colRow['ID']);
            if($db->Error()){return $db->Error();}
        }
        $db->query("DELETE FROM collections WHERE".$where);
        return $db->Error();
    }

	public function update($group)
	{
		$db = new DB();

		if ($group["minfilestoformrelease"] == "" || $group["minfilestoformrelease"] == "0")
			$minfiles = '0';
		else
			$minfiles = $group["minfilestoformrelease"] + 0;

		if ($group["minsizetoformrelease"] == "" || $group["minsizetoformrelease"] == "0")
			$minsizetoformrelease = '0';
		else
			$minsizetoformrelease = $db->escapeString($group["minsizetoformrelease"]);

		return $db->query(sprintf("update groups set name=%s, description = %s, backfill_target = %s , active=%d, backfill=%d, minfilestoformrelease=%s, minsizetoformrelease=%s where ID = %d ",$db->escapeString($group["name"]), $db->escapeString($group["description"]), $db->escapeString($group["backfill_target"]),$group["active"], $group["backfill"] , $minfiles, $minsizetoformrelease, $group["id"] ));
	}

	//
	// update the list of newsgroups and return an array of messages.
	//
	function addBulk($groupList, $active = 1)
	{
		$ret = array();

		if ($groupList == "")
		{
			$ret[] = "No group list provided.";
		}
		else
		{
			$db = new DB();
			$nntp = new Nntp();
			$nntp->doConnect();
			$groups = $nntp->getGroups();
			$nntp->doQuit();

			$regfilter = "/(" . str_replace (array ('.','*'), array ('\.','.*?'), trim($groupList)) . ")$/";
            echo $regfilter;
			foreach($groups AS $group)
			{
				if (preg_match ($regfilter, $group['group']) > 0)
				{
					$res = $db->queryOneRow(sprintf("SELECT ID FROM groups WHERE name = %s ", $db->escapeString($group['group'])));
					if($res)
					{

						$db->query(sprintf("UPDATE groups SET active = %d where ID = %d", $active, $res["ID"]));
						$ret[] = array ('group' => $group['group'], 'msg' => 'Updated');
					}
					else
					{
						$desc = "";
						$db->queryInsert(sprintf("INSERT INTO groups (name, description, active, minfilestoformrelease, minsizetoformrelease) VALUES (%s, %s, %d, 0, 0)", $db->escapeString($group['group']), $db->escapeString($desc), $active));
						$ret[] = array ('group' => $group['group'], 'msg' => 'Created');
					}
				}
			}
		}
		return $ret;
	}

    /**
     * @param $id
     * @param int $status=0
     * @return string
     */
    public function updateGroupStatus($id, $status = 0)
	{
		$db = new DB();
		$db->query(sprintf("UPDATE groups SET active = %d WHERE id = %d", $status, $id));
		$status = ($status == 0) ? 'deactivated' : 'activated';
		return "Group ".$this->getByNameByID($id)." has been ".$status.".";
	}

	public function updateBackfillStatus($id, $status = 0)
	{
		$db = new DB();
		$db->query(sprintf("UPDATE groups SET backfill = %d WHERE id = %d", $status, $id));
		$status = ($status == 0) ? 'deactivated' : 'activated';
		return "Backfill for group ".$this->getByNameByID($id)." has been ".$status.".";
	}


    /**
     * Multiple group actions.  Possible values for $action are:
     * allActive, allInactive, toggleActive, allBackfillActive,
     * allBackfillInactive, allBackfillToggle, deleteGroups,
     * and resetGroups
     *
     * @param array $groupIDs
     * @param string $action
     * @param bool $deleteCollections
     * @param bool $deleteReleases
     * @return string
     */
    public function multiGroupAction($groupIDs, $action, $deleteCollections=false, $deleteReleases=false)
    {
        $db = new DB();
        $inClause = '(';
        foreach($groupIDs as $id)
            $inClause .= $id.",";
        switch ($action)
        {
            case 'allActive':
                $sql = "UPDATE groups AS g SET g.active = 1 WHERE g.id IN ".substr($inClause,0,-1).')';
                $msg = "Selected groups have been set to ACTIVE status.";
                break;
            case 'allInactive':
                $sql = "UPDATE groups AS g SET g.active = 0 WHERE g.id IN ".substr($inClause,0,-1).')';
                $msg = "Selected groups have been set to an INACTIVE status.";
                break;
            case 'toggleActive':
                $sql = "UPDATE groups AS g JOIN groups AS g1 ON g.ID=g1.ID SET g.active = IF(g1.active=0,1,0) WHERE g.id IN ".substr($inClause,0,-1).')';
                $msg = "Selected groups ACTIVE status has been toggled.";
                break;
            case 'allBackfillActive':
                $sql = "UPDATE groups AS g SET g.backfill = 1 WHERE g.id IN ".substr($inClause,0,-1).')';
                $msg = "The backfill status for selected groups has been set to ACTIVE.";
                break;
            case 'allBackfillInactive':
                $sql = "UPDATE groups AS g SET g.backfill = 0 WHERE g.id IN ".substr($inClause,0,-1).')';
                $msg = "The backfill status for selected groups has been set to INACTIVE.";
                break;
            case 'toggleBackfill':
                $sql = "UPDATE groups AS g JOIN groups AS g1 ON g.ID=g1.ID SET g.backfill = IF(g1.backfill=0,1,0) WHERE g.id IN ".substr($inClause,0,-1).')';
                $msg = "Selected groups backfill status has been toggled.";
                break;
            case 'deleteGroups':
                $error = '';
                $msg = "Selected groups have been DELETED";
                if($deleteCollections)
                {
                    $db->query("UPDATE groups SET active = 6 WHERE ID IN ".substr($inClause,0,-1).')');
                    $error = $this->deleteGroupCollections($groupIDs);
                    if($error != '') {return 'ERROR: (deleteGroupCollections) '.$error;}
                    $msg .= " (including collections)";
                }
                if($deleteReleases)
                {
                    $db->query("UPDATE groups SET active = 6 WHERE ID IN ".substr($inClause,0,-1).')');
                    $db->query("UPDATE releases SET groupID=999999 WHERE groupID IN ".substr($inClause,0,-1).')');
                    if($error != '') {return 'ERROR: (purge) '.$error;}
                    $msg .= " (including releases)";
                }
                $sql = "DELETE groups FROM groups WHERE ID IN ".substr($inClause,0,-1).')';

                break;
            case 'resetGroups':
                $db->query("UPDATE groups SET active = 7 WHERE ID IN ".substr($inClause,0,-1).')');
                $msg = "Selected groups have been RESET ";
                if($deleteCollections)
                {
                    $error = $this->deleteGroupCollections($groupIDs);
                    if($error != '') {return 'ERROR: (deleteGroupCollections) '.$error;}
                    $msg .= " (including collections)";
                }
                $sql = "UPDATE groups SET first_record=0, first_record_postdate=null, last_record=0, last_record_postdate=null, active = 0, backfill = 0, last_updated=null WHERE ID IN ".substr($inClause,0,-1).')';
                break;
            case 'purgeGroups':
                $db->query("UPDATE groups SET active = 8 WHERE ID IN ".substr($inClause,0,-1).')');
                $error = $this->purge($groupIDs);
                if($error === 0)
                {
                    $sql = "UPDATE groups SET first_record=0, first_record_postdate=null, last_record=0, last_record_postdate=null, active = 0, backfill = 0, last_updated=null WHERE ID IN ".substr($inClause,0,-1).')';
                    $msg = "Selected groups have been PURGED.";
                }
                else
                    return 'ERROR: (deleteGroupCollections) '.$error;
                break;
            default:
                return "ERROR IN multiGroupAction. Invalid action.";
        }

        file_put_contents(WWW_DIR."lib/logging/multiGroupAction.log",$sql."\n-------------------------------\n", FILE_APPEND);
        $db->query($sql);
        return $msg;
    }

}
