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

    public function advancedGroupSearch($searchString, $orderBy = null, $offset = 0, $itemsPerPage = 50)
    {
        $db = new DB();
        $orderByClause = $this->getOrderBy($orderBy);
        $sql = "SELECT groups.*, COALESCE(rel.num, 0) AS num_releases
							FROM groups
							LEFT OUTER JOIN
							(SELECT groupID, COUNT(ID) AS num FROM releases group by groupID) rel ON rel.groupID = groups.ID
							WHERE 1=1 ".$searchString.$orderByClause;
        $returnObject = [];
        $returnObject['resultSet'] = $db->queryDirect($sql." LIMIT ".$offset.",".$itemsPerPage);
        $returnObject['resultCount'] = $db->getNumRows($db->queryDirect($sql));

        file_put_contents(WWW_DIR."lib/logging/group-search.log",$sql."\n-------------------------------------------\n", FILE_APPEND);
        return $returnObject;
    }
    
    public function getOrderBy($orderBy) {
        
        $orderBy = explode('_',$orderBy);
        
        switch ($orderBy[0])
        {
            case 'name':
                return ' ORDER BY name '.$orderBy[1];
                break;
            case 'description':
                return ' ORDER BY description '.$orderBy[1];
                break;
            case 'firstPost':
                return ' ORDER BY first_record_postdate '.$orderBy[1];
                break;
            case 'lastPost':
                return ' ORDER BY last_record_postdate '.$orderBy[1];
                break;
            case 'lastUpdated':
                return ' ORDER BY last_updated '.$orderBy[1];
                break;
            case 'active':
                return ' ORDER BY active '.$orderBy[1];
                break;
            case 'backfill':
                return ' ORDER BY backfill '.$orderBy[1];
                break;
            case 'releases':
                return ' ORDER BY num_releases '.$orderBy[1];
                break;
            case 'minFiles':
                return ' ORDER BY minfilestoformrelease '.$orderBy[1];
                break;
            case 'minSize':
                return ' ORDER BY minsizetoformrelease '.$orderBy[1];
                break;
            case 'backfillDays':
                return ' ORDER BY backfill_target '.$orderBy[1];
                break;
            default:
                return ' ORDER BY name ASC';
        }
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

    public function getTotalCount()
    {
        $db = new DB();
        $res = $db->queryOneRow("SELECT count(*) AS num FROM groups");

        return $res['num'];
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
        $newGroup = new clsGroup();
        $newGroup->name = $group['name'];

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
        $checkExisting = $db->queryOneRow("SELECT ID, name FROM groups WHERE name = ".$db->escapeString($group['name']));
        if($checkExisting && $group['updateExisting']=='false')
        {
            $newGroup->status = clsGroup::ERROR_GROUP_EXISTS;
            $newGroup->id = 0;
        }
        elseif($checkExisting && $group['updateExisting']=='true')
        {
            $sql = sprintf("UPDATE groups SET name=%s, description=%s, active=%s, minfilestoformrelease=%s, minsizetoformrelease=%s, backfill_target=%s, backfill=%s WHERE ID=%s",
                $db->escapeString($group["name"]), $db->escapeString($group["description"]), $group["active"], $minfiles, $minsizetoformrelease, $db->escapeString($group['backfill_target']), $db->escapeString($group['backfill']), $db->escapeString($checkExisting['ID']));
            $db->query($sql);
            if($db->getAffectedRows()>0)
            {
            $newGroup->status = clsGroup::GROUP_UPDATED;
            $newGroup->id = $checkExisting['ID'];
            }
            else
            {
                $newGroup->status = clsGroup::ERROR_GROUP_NOT_UPDATED;
                $newGroup->id = $checkExisting['ID'];
            }
        }
        else
        {
		$sql = sprintf("INSERT INTO groups (name, description, first_record, last_record, last_updated, active, minfilestoformrelease, minsizetoformrelease, backfill_target, backfill) values (%s, %s, %s, %s, null, %d, %s, %s, %s, %s) ",
            $db->escapeString($group["name"]), $db->escapeString($group["description"]), $db->escapeString($first), $db->escapeString($last), $group["active"], $minfiles, $minsizetoformrelease, $db->escapeString($group['backfill_target']), $db->escapeString($group['backfill']));
        $newGroup->id = $db->queryInsert($sql);
        $newGroup->status = clsGroup::GROUP_CREATED;
        }
        return $newGroup;
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

    public function pruneOldCollections($groupID)
    {
        if (empty($groupID) || $groupID === 0 || $groupID == '')
            return 'ERROR: (deleteGroupCollections) No group ID provided.';
        if (!(is_array($groupID)))
            $where = " groupID=" . $groupID;
        else
        {
            $inClause = '(';
            foreach ($groupID as $id)
                $inClause .= $id . ",";
            $where = " groupID IN " . substr($inClause, 0, -1) . ")";
        }
        $db = new DB();
        $sql = "SELECT ID FROM collections WHERE " . $where . " AND newestBinary < NOW() - INTERVAL 24 HOUR";
        $collections = $db->queryDirect($sql);
        file_put_contents(WWW_DIR . 'lib/logging/pruneGroups.log', $sql . "\n----------------------------\n", FILE_APPEND);
        if($db->getNumRows($collections) > 0)
        {
            $collectionsProcessed = 0;
            while ($colRow = $db->fetchAssoc($collections))
            {
                $db->query("DELETE FROM binaries WHERE collectionID=" . $colRow['ID']);
                $db->query("DELETE FROM parts WHERE collectionID=" . $colRow['ID']);
                $collectionsProcessed ++;
            }
            $sql = "DELETE FROM collections WHERE " . $where . " AND newestBinary < NOW() - INTERVAL 24 HOUR";
            file_put_contents(WWW_DIR . 'lib/logging/pruneGroups.log', $sql . "\n----------------------------\n", FILE_APPEND);
            $db->query($sql);
        }
        else
            return "No collections to purge.";

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
    /**
     * @param array $groupList
     * @return array(clsGroup)
     *
     */
    function addBulk($groupList=array())
	{
        $groupArr = array();
		if ($groupList == "")
		{
			$groupArr[] = "No group list provided.";
		}
		else
		{
			$groupsFile = WWW_DIR."/admin/newsgroups.txt";
            if(!file_exists($groupsFile))
                $this->updateNNTPnewgroups();
            $groups = array();
            $handle = @fopen($groupsFile, "r");
            if ($handle) {
                while (($buffer = fgets($handle, 4096)) !== false) {
                   $groups[] = explode(" ", $buffer);
                }
                if (!feof($handle)) {
                    $groupArr[]= "Error: unexpected fgets() fail";
                    return $groupArr;
                }
                fclose($handle);
            }
            // $regfilter = "/(" . str_replace (array ('.','*'), array ('\.','.*?'), trim($groupList['groupName'][0])) . ")$/i";

            $regfilter = "/(" .preg_replace('/(\w)\.(\w)/i', '$1\\.$2', trim($groupList['groupName'][0])). ")$/i";

            foreach($groups AS $group)
			{
				if (preg_match ($regfilter, $group[0]) > 0)
				{
                    $newGroup = [];
                    $newGroup['name'] = $group[0];
                    $newGroup['description'] = $groupList['description'];
                    $newGroup['backfill_target'] = $groupList['backfillTarget'];
                    $newGroup['minfilestoformrelease'] = $groupList['minFiles'];
                    $newGroup['minsizetoformrelease'] = $groupList['minSize'];
                    $newGroup['active'] = $groupList['active'];
                    $newGroup['backfill'] = $groupList['backfill'];
                    $newGroup['updateExisting'] = $groupList['updateExisting'];
                    $groupArr[] = $this->add($newGroup);
				}
			}
		}
		return $groupArr;
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
            case 'pruneGroups':
                $error = $this->pruneOldCollections($groupIDs);
                if ($error === 0 || $error='' || empty($error))
                {
                    $sql = false;
                    $temp = "SELECT oldBins.groupID, oldBins.oldestBinary FROM " .
                        "(SELECT groupID, oldestBinary FROM collections ORDER BY oldestBinary) AS oldBins " .
                        "WHERE groupID IN " . $inClause . ") GROUP BY groupID";
                    $groupsPruned = $db->queryDirect($temp);

                    if($db->getNumRows($groupsPruned) > 0)
                    {
                        while($prunedGroup = $db->fetchAssoc($groupsPruned))
                        {
                            $db->query("UPDATE groups SET first_record_postdate = " . $db->escapeString($prunedGroup['oldestBinary']) . " WHERE ID = " . $prunedGroup['groupID']);
                        }
                    }
                    $msg = "Selected groups have been PRUNED.";
                }
                else
                    return 'ERROR: (pruneOldCollections) ' . $error;
                break;
            default:
                return "ERROR IN multiGroupAction. Invalid action.";
        }

        file_put_contents(WWW_DIR."lib/logging/multiGroupAction.log",$sql."\n-------------------------------\n", FILE_APPEND);
        if($sql !== false)
            $db->query($sql);
        return $msg;
    }

    public function updateNNTPnewgroups ()
    {
        try
        {
            $nntp = new Nntp();

            $nntp->doConnect();
            ob_start();
            $groups = $nntp->getGroups();
            $crap = ob_get_contents();      // Had to do this, and the the ob_clean statement, to completely prevent the Received XXXX... output
            ob_clean();
            ob_end_clean();

            $nntp->doQuit();

            $groupFile = WWW_DIR."/admin/newsgroups.txt";
            if(file_exists($groupFile))
                unlink($groupFile);

            foreach($groups as $group)
                file_put_contents($groupFile,implode(' ',$group)."\n", FILE_APPEND);
            return number_format(count($groups))." Groups Received";
        }
        catch (Exception $e)
        {
            return "#! ".$e;
        }
    }
}

class clsGroup {

    const ERROR_GROUP_EXISTS = '#!GROUP EXISTS';
    const ERROR_GROUP_NOT_INSERTED = '#!GROUP NOT CREATED';
    const ERROR_GROUP_NOT_UPDATED = '#!ERROR UPDATING GROUP';
    const GROUP_CREATED = 'Created';
    const GROUP_UPDATED = 'Updated';

    public $id = '';
    public $name = '';
    public $status = '';
}