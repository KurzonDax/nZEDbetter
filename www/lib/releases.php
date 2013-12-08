<?php
require_once(WWW_DIR."lib/framework/db.php");
require_once(WWW_DIR."lib/page.php");
require_once(WWW_DIR."lib/binaries.php");
require_once(WWW_DIR."lib/users.php");
require_once(WWW_DIR."lib/category.php");
require_once(WWW_DIR."lib/consoletools.php");
require_once(WWW_DIR."lib/nzb.php");
require_once(WWW_DIR."lib/nfo.php");
require_once(WWW_DIR."lib/zipfile.php");
require_once(WWW_DIR."lib/site.php");
require_once(WWW_DIR . "lib/tmux.php");
require_once(WWW_DIR."lib/util.php");
require_once(WWW_DIR."lib/releasefiles.php");
require_once(WWW_DIR."lib/releaseextra.php");
require_once(WWW_DIR."lib/releaseimage.php");
require_once(WWW_DIR."lib/releasecomments.php");
require_once(WWW_DIR."lib/postprocess.php");
require_once(WWW_DIR."lib/groups.php");
require_once(WWW_DIR."lib/namecleaning.php");
require_once(WWW_DIR."lib/predb.php");


class Releases
{
	//
	// passworded indicator
	//
	const PASSWD_NONE = 0;
	const PASSWD_POTENTIAL = 1;
	const BAD_FILE = 2;
	const PASSWD_RAR = 10;

	function Releases($echooutput=false)
	{
		$this->echooutput = $echooutput;
		$s = new Sites();
		$this->site = $s->get();
        $t = new Tmux();
        $this->tmux = $t->get();
        $this->noMiscPurgeBeforeFix = (isset($this->tmux->NO_PURGE_MISC_BEFORE_FIX) && !empty($this->tmux->NO_PURGE_MISC_BEFORE_FIX)) ? $this->tmux->NO_PURGE_MISC_BEFORE_FIX : 'FALSE';
		$this->stage5limit = (!empty($this->site->maxnzbsprocessed)) ? $this->site->maxnzbsprocessed : 1000;
		$this->completion = (!empty($this->site->releasecompletion)) ? $this->site->releasecompletion : 0;
		$this->crosspostt = (!empty($this->site->crossposttime)) ? $this->site->crossposttime : 2;
		$this->updategrabs = ($this->site->grabstatus == "0") ? false : true;
		$this->requestids = $this->site->lookup_reqids;
		$this->hashcheck = (!empty($this->site->hashcheck)) ? $this->site->hashcheck : 0;
		$this->debug = ($this->site->debuginfo == "0") ? false : true;
        $this->siteMinFileSize = ($this->site->minsizetoformrelease) ? $this->site->minsizetoformrelease : 0;
        $this->siteMinFileCount = ($this->site->minfilestoformrelease) ? $this->site->minfilestoformrelease : 0;
        $this->siteMaxFileSize = ($this->site->maxsizetoformrelease) ? $this->site->maxsizetoformrelease : 0;
	    $this->lastFullCollectionCheck = ($this->site->lastFullCollectionCheck) ? $this->site->lastFullCollectionCheck : 0;
        $this->nextCrosspostCheck = ($this->site->nextCrosspostCheck == "0") ? 0 : $this->site->nextCrosspostCheck;
    }

	public function get()
	{
		$db = new DB();
		return $db->query("select releases.*, g.name as group_name, c.title as category_name  from releases left outer join category c on c.ID = releases.categoryID left outer join groups g on g.ID = releases.groupID");
	}

	public function getRange($start, $num)
	{
		$db = new DB();

		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;

		return $db->query(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID order by postdate desc".$limit);
	}

	public function getBrowseCount($cat, $maxage=-1, $excludedcats=array(), $grp = "")
	{
		$db = new DB();

		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
				if ($category != -1)
				{
					$categ = new Category();
					if ($categ->isParent($category))
					{
						$children = $categ->getChildren($category);
						$chlist = "-99";
						foreach ($children as $child)
							$chlist.=", ".$child['ID'];

						if ($chlist != "-99")
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}

		if ($maxage > 0)
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";

		$grpsql = "";
		if ($grp != "")
			$grpsql = sprintf(" and groups.name = %s ", $db->escapeString($grp));

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and categoryID not in (".implode(",", $excludedcats).")";
        $sqlQuery = sprintf("select count(releases.ID) as num from releases left outer join groups on groups.ID = releases.groupID where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s", $catsrch, $maxage, $exccatlist, $grpsql);
		// file_put_contents(WWW_DIR."lib/logging/browse_sql.log","---------------Browse Count--------------------\n".$sqlQuery."\n", FILE_APPEND);
        $res = $db->queryOneRow($sqlQuery);
		return $res['num'];
	}

	public function getBrowseRange($cat, $start, $num, $orderby, $maxage=-1, $excludedcats=array(), $grp="")
	{
		$db = new DB();

		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;

		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
				if ($category != -1)
				{
					$categ = new Category();
					if ($categ->isParent($category))
					{
						$children = $categ->getChildren($category);
						$chlist = "-99";
						foreach ($children as $child)
							$chlist.=", ".$child['ID'];

						if ($chlist != "-99")
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}

		$maxagesql = "";
		if ($maxage > 0)
			$maxagesql = sprintf(" and postdate > now() - interval %d day ", $maxage);

		$grpsql = "";
		if ($grp != "")
			$grpsql = sprintf(" and groups.name = %s ", $db->escapeString($grp));

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";

		$order = $this->getBrowseOrder($orderby);
		$sqlQuery = sprintf(" SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID, re.releaseID as reID from releases left outer join groups on groups.ID = releases.groupID left outer join releasevideo re on re.releaseID = releases.ID left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s order by %s %s" . $limit, $catsrch, $maxagesql, $exccatlist, $grpsql, $order[0], $order[1]);
        // file_put_contents(WWW_DIR . "lib/logging/browse_sql.log", "---------------Browse Range--------------------\n" . $sqlQuery . "\n", FILE_APPEND);
        return $db->query($sqlQuery);
	}

	public function getBrowseOrder($orderby)
	{
		$order = ($orderby == '') ? 'posted_desc' : $orderby;
		$orderArr = explode("_", $order);
		switch($orderArr[0]) {
			case 'cat':
				$orderfield = 'categoryID';
			break;
			case 'name':
				$orderfield = 'searchname';
			break;
			case 'size':
				$orderfield = 'size';
			break;
			case 'files':
				$orderfield = 'totalpart';
			break;
			case 'stats':
				$orderfield = 'grabs';
			break;
			case 'posted':
			default:
				$orderfield = 'postdate';
			break;
		}
		$ordersort = (isset($orderArr[1]) && preg_match('/^asc|desc$/i', $orderArr[1])) ? $orderArr[1] : 'desc';
		return array($orderfield, $ordersort);
	}

	public function getBrowseOrdering()
	{
		return array('name_asc', 'name_desc', 'cat_asc', 'cat_desc', 'posted_asc', 'posted_desc', 'size_asc', 'size_desc', 'files_asc', 'files_desc', 'stats_asc', 'stats_desc');
	}

	public function getForExport($postfrom, $postto, $group)
	{
		$db = new DB();
		if ($postfrom != "")
		{
			$dateparts = explode("/", $postfrom);
			if (count($dateparts) == 3)
				$postfrom = sprintf(" and postdate > %s ", $db->escapeString($dateparts[2]."-".$dateparts[1]."-".$dateparts[0]." 00:00:00"));
			else
				$postfrom = "";
		}

		if ($postto != "")
		{
			$dateparts = explode("/", $postto);
			if (count($dateparts) == 3)
				$postto = sprintf(" and postdate < %s ", $db->escapeString($dateparts[2]."-".$dateparts[1]."-".$dateparts[0]." 23:59:59"));
			else
				$postto = "";
		}

		if ($group != "" && $group != "-1")
			$group = sprintf(" and groupID = %d ", $group);
		else
			$group = "";

		return $db->query(sprintf("SELECT searchname, guid, CONCAT(cp.title,'_',category.title) as catName FROM releases INNER JOIN category ON releases.categoryID = category.ID LEFT OUTER JOIN category cp ON cp.ID = category.parentID where 1 = 1 %s %s %s", $postfrom, $postto, $group));
	}

	public function getEarliestUsenetPostDate()
	{
		$db = new DB();
		$row = $db->queryOneRow("SELECT DATE_FORMAT(min(postdate), '%d/%m/%Y') as postdate from releases");
		return $row['postdate'];
	}

	public function getLatestUsenetPostDate()
	{
		$db = new DB();
		$row = $db->queryOneRow("SELECT DATE_FORMAT(max(postdate), '%d/%m/%Y') as postdate from releases");
		return $row['postdate'];
	}

	public function getReleasedGroupsForSelect($blnIncludeAll = true)
	{
		$db = new DB();
		$groups = $db->query("select distinct groups.ID, groups.name from releases inner join groups on groups.ID = releases.groupID");
		$temp_array = array();

		if ($blnIncludeAll)
			$temp_array[-1] = "--All Groups--";

		foreach($groups as $group)
			$temp_array[$group['ID']] = $group['name'];

		return $temp_array;
	}

	public function getRss($cat, $num, $uid=0, $rageid, $anidbid, $airdate=-1)
	{
		$db = new DB();

		$limit = " LIMIT 0,".($num > 100 ? 100 : $num);

		$catsrch = "";
		$cartsrch = "";

		$catsrch = "";
		if (count($cat) > 0)
		{
			if ($cat[0] == -2)
			{
				$cartsrch = sprintf(" inner join usercart on usercart.userID = %d and usercart.releaseID = releases.ID ", $uid);
			}
			else
			{
				$catsrch = " and (";
				foreach ($cat as $category)
				{
					if ($category != -1)
					{
						$categ = new Category();
						if ($categ->isParent($category))
						{
							$children = $categ->getChildren($category);
							$chlist = "-99";
							foreach ($children as $child)
								$chlist.=", ".$child['ID'];

							if ($chlist != "-99")
									$catsrch .= " releases.categoryID in (".$chlist.") or ";
						}
						else
						{
							$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
						}
					}
				}
				$catsrch.= "1=2 )";
			}
		}


		$rage = ($rageid > -1) ? sprintf(" and releases.rageID = %d ", $rageid) : '';
		$anidb = ($anidbid > -1) ? sprintf(" and releases.anidbID = %d ", $anidbid) : '';
		$airdate = ($airdate > -1) ? sprintf(" and releases.tvairdate >= DATE_SUB(CURDATE(), INTERVAL %d DAY) ", $airdate) : '';

		$sql = sprintf(" SELECT releases.*, m.cover, m.imdbID, m.rating, m.plot, m.year, m.genre, m.director, m.actors, g.name as group_name, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, coalesce(cp.ID,0) as parentCategoryID, mu.title as mu_title, mu.url as mu_url, mu.artist as mu_artist, mu.publisher as mu_publisher, mu.releasedate as mu_releasedate, mu.review as mu_review, mu.tracks as mu_tracks, mu.cover as mu_cover, mug.title as mu_genre, co.title as co_title, co.url as co_url, co.publisher as co_publisher, co.releasedate as co_releasedate, co.review as co_review, co.cover as co_cover, cog.title as co_genre  from releases left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID left outer join groups g on g.ID = releases.groupID left outer join movieinfo m on m.imdbID = releases.imdbID and m.title != '' left outer join musicinfo mu on mu.ID = releases.musicinfoID left outer join genres mug on mug.ID = mu.genreID left outer join consoleinfo co on co.ID = releases.consoleinfoID left outer join genres cog on cog.ID = co.genreID %s where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s order by postdate desc %s" ,$cartsrch, $catsrch, $rage, $anidb, $airdate, $limit);
		return $db->query($sql);
	}

	public function getShowsRss($num, $uid=0, $excludedcats=array(), $airdate=-1)
	{
		$db = new DB();

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";

		$usershows = $db->query(sprintf("select rageID, categoryID from userseries where userID = %d", $uid), true);
		$usql = '(1=2 ';
		foreach($usershows as $ushow)
		{
			$usql .= sprintf('or (releases.rageID = %d', $ushow['rageID']);
			if ($ushow['categoryID'] != '')
			{
				$catsArr = explode('|', $ushow['categoryID']);
				if (count($catsArr) > 1)
					$usql .= sprintf(' and releases.categoryID in (%s)', implode(',',$catsArr));
				else
					$usql .= sprintf(' and releases.categoryID = %d', $catsArr[0]);
			}
			$usql .= ') ';
		}
		$usql .= ') ';

		$airdate = ($airdate > -1) ? sprintf(" and releases.tvairdate >= DATE_SUB(CURDATE(), INTERVAL %d DAY) ", $airdate) : '';

		$limit = " LIMIT 0,".($num > 100 ? 100 : $num);

		$sql = sprintf(" SELECT releases.*, tvr.rageID, tvr.releasetitle, g.name as group_name, concat(cp.title, '-', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, coalesce(cp.ID,0) as parentCategoryID
						FROM releases
						left outer join category c on c.ID = releases.categoryID
						left outer join category cp on cp.ID = c.parentID
						left outer join groups g on g.ID = releases.groupID
						left outer join tvrage tvr on tvr.rageID = releases.rageID
						where %s %s %s
						and releases.passwordstatus <= (select value from site where setting='showpasswordedrelease')
						order by postdate desc %s" , $usql, $exccatlist, $airdate, $limit);
		return $db->query($sql);
	}

	public function getMyMoviesRss($num, $uid=0, $excludedcats=array())
	{
		$db = new DB();

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";

		$usermovies = $db->query(sprintf("select imdbID, categoryID from usermovies where userID = %d", $uid), true);
		$usql = '(1=2 ';
		foreach($usermovies as $umov)
		{
			$usql .= sprintf('or (releases.imdbID = %d', $umov['imdbID']);
			if ($umov['categoryID'] != '')
			{
				$catsArr = explode('|', $umov['categoryID']);
				if (count($catsArr) > 1)
					$usql .= sprintf(' and releases.categoryID in (%s)', implode(',',$catsArr));
				else
					$usql .= sprintf(' and releases.categoryID = %d', $catsArr[0]);
			}
			$usql .= ') ';
		}
		$usql .= ') ';

		$limit = " LIMIT 0,".($num > 100 ? 100 : $num);

		$sql = sprintf(" SELECT releases.*, mi.title as releasetitle, g.name as group_name, concat(cp.title, '-', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, coalesce(cp.ID,0) as parentCategoryID
						FROM releases
						left outer join category c on c.ID = releases.categoryID
						left outer join category cp on cp.ID = c.parentID
						left outer join groups g on g.ID = releases.groupID
						left outer join movieinfo mi on mi.imdbID = releases.imdbID
						where %s %s
						and releases.passwordstatus <= (select value from site where setting='showpasswordedrelease')
						order by postdate desc %s" , $usql, $exccatlist, $limit);
		return $db->query($sql);
	}


	public function getShowsRange($usershows, $start, $num, $orderby, $maxage=-1, $excludedcats=array())
	{
		$db = new DB();

		if ($start === false)
			$limit = "";
		else
			$limit = " LIMIT ".$start.",".$num;

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";

		$usql = '(1=2 ';
		foreach($usershows as $ushow)
		{
			$usql .= sprintf('or (releases.rageID = %d', $ushow['rageID']);
			if ($ushow['categoryID'] != '')
			{
				$catsArr = explode('|', $ushow['categoryID']);
				if (count($catsArr) > 1)
					$usql .= sprintf(' and releases.categoryID in (%s)', implode(',',$catsArr));
				else
					$usql .= sprintf(' and releases.categoryID = %d', $catsArr[0]);
			}
			$usql .= ') ';
		}
		$usql .= ') ';

		$maxagesql = "";
		if ($maxage > 0)
			$maxagesql = sprintf(" and releases.postdate > now() - interval %d day ", $maxage);

		$order = $this->getBrowseOrder($orderby);
		$sql = sprintf(" SELECT releases.*, concat(cp.title, '-', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID, re.releaseID as reID from releases left outer join releasevideo re on re.releaseID = releases.ID left outer join groups on groups.ID = releases.groupID left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where %s %s and releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s order by %s %s".$limit, $usql, $exccatlist, $maxagesql, $order[0], $order[1]);
		return $db->query($sql, true);
	}

	public function getShowsCount($usershows, $maxage=-1, $excludedcats=array())
	{
		$db = new DB();

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";

		$usql = '(1=2 ';
		foreach($usershows as $ushow)
		{
			$usql .= sprintf('or (releases.rageID = %d', $ushow['rageID']);
			if ($ushow['categoryID'] != '')
			{
				$catsArr = explode('|', $ushow['categoryID']);
				if (count($catsArr) > 1)
					$usql .= sprintf(' and releases.categoryID in (%s)', implode(',',$catsArr));
				else
					$usql .= sprintf(' and releases.categoryID = %d', $catsArr[0]);
			}
			$usql .= ') ';
		}
		$usql .= ') ';

		$maxagesql = "";
		if ($maxage > 0)
			$maxagesql = sprintf(" and releases.postdate > now() - interval %d day ", $maxage);

		$res = $db->queryOneRow(sprintf(" SELECT count(releases.ID) as num from releases where %s %s and releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s", $usql, $exccatlist, $maxagesql), true);
		return $res['num'];
	}

	public function getCount()
	{
		$db = new DB();
		$res = $db->queryOneRow("select count(ID) as num from releases");
		return $res['num'];
	}

	public function delete($id, $isGuid=false)
	{
		$db = new DB();
		$nzb = new NZB();
		$s = new Sites();
		$site = $s->get();

		$ri = new ReleaseImage();

		if (!is_array($id))
			$id = array($id);

		foreach($id as $identifier)
		{
			//
			// delete from disk.
			//
			$rel = $this->getById($identifier);
			$this->fastDelete($rel['ID'], $rel['guid'], $this->site);
		}
	}

	public function fastDelete($id, $guid, $site)
	{
		$db = new DB();
		$nzb = new NZB();
		$ri = new ReleaseImage();


		//
		// delete from disk.
		//
		$nzbpath = $nzb->getNZBPath($guid, $site->nzbpath, false, $site->nzbsplitlevel);

		if (file_exists($nzbpath))
			unlink($nzbpath);

		$db->query(sprintf("delete releases, releasenfo, releasecomment, usercart, releasefiles, releaseaudio, releasesubs, releasevideo, releaseextrafull
							from releases
								LEFT OUTER JOIN releasenfo on releasenfo.releaseID = releases.ID
								LEFT OUTER JOIN releasecomment on releasecomment.releaseID = releases.ID
								LEFT OUTER JOIN usercart on usercart.releaseID = releases.ID
								LEFT OUTER JOIN releasefiles on releasefiles.releaseID = releases.ID
								LEFT OUTER JOIN releaseaudio on releaseaudio.releaseID = releases.ID
								LEFT OUTER JOIN releasesubs on releasesubs.releaseID = releases.ID
								LEFT OUTER JOIN releasevideo on releasevideo.releaseID = releases.ID
								LEFT OUTER JOIN releaseextrafull on releaseextrafull.releaseID = releases.ID
							where releases.ID = %d", $id));

		$ri->delete($guid); // This deletes a file so not in the query
	}

	// For the site delete button.
	public function deleteSite($id, $isGuid=false)
	{
		if (!is_array($id))
			$id = array($id);

		foreach($id as $identifier)
		{
			//
			// delete from disk.
			//
			if ($isGuid !== false)
				$rel = $this->getById($identifier);
			else
				$rel = $this->getByGuid($identifier);
			$this->fastDelete($rel['ID'], $rel['guid'], $this->site);
		}
	}

	public function update($id, $name, $searchname, $fromname, $category, $parts, $grabs, $size, $posteddate, $addeddate, $rageid, $seriesfull, $season, $episode, $imdbid, $anidbid)
	{
		$db = new DB();

		$db->query(sprintf("update releases set name=%s, searchname=%s, fromname=%s, categoryID=%d, totalpart=%d, grabs=%d, size=%s, postdate=%s, adddate=%s, rageID=%d, seriesfull=%s, season=%s, episode=%s, imdbID=%d, anidbID=%d where id = %d",
			$db->escapeString($name), $db->escapeString($searchname), $db->escapeString($fromname), $category, $parts, $grabs, $db->escapeString($size), $db->escapeString($posteddate), $db->escapeString($addeddate), $rageid, $db->escapeString($seriesfull), $db->escapeString($season), $db->escapeString($episode), $imdbid, $anidbid, $id));
	}

	public function updatemulti($guids, $category, $grabs, $rageid, $season, $imdbid)
	{
		if (!is_array($guids) || sizeof($guids) < 1)
			return false;

		$update = array(
			'categoryID'=>(($category == '-1') ? '' : $category),
			'grabs'=>$grabs,
			'rageID'=>$rageid,
			'season'=>$season,
			'imdbID'=>$imdbid
		);

		$db = new DB();
		$updateSql = array();
		foreach($update as $updk=>$updv) {
			if ($updv != '')
				$updateSql[] = sprintf($updk.'=%s', $db->escapeString($updv));
		}

		if (sizeof($updateSql) < 1) {
			//echo 'no field set to be changed';
			return -1;
		}

		$updateGuids = array();
		foreach($guids as $guid) {
			$updateGuids[] = $db->escapeString($guid);
		}

		$sql = sprintf('update releases set '.implode(', ', $updateSql).' where guid in (%s)', implode(', ', $updateGuids));
		return $db->query($sql);
	}

	public function searchadv($searchname, $usenetname, $postername, $groupname, $cat, $sizefrom, $sizeto, $hasnfo, $hascomments, $daysnew, $daysold, $offset=0, $limit=1000, $orderby='', $excludedcats=array())
	{
		$db = new DB();
		$groups = new Groups();
        $searchnamesql = "";
		if ($cat == "-1"){$catsrch = ("");}
		else{$catsrch = sprintf(" and (releases.categoryID = %d) ", $cat);}

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		if ($searchname == "-1"){$searchnamesql.= ("");}
		else
		{
			$words = explode(" ", $searchname);
			$searchnamesql = "";
			$intwordcount = 0;
			if (count($words) > 0)
			{
				foreach ($words as $word)
				{
					if ($word != "")
					{
						//
						// see if the first word had a caret, which indicates search must start with term
						//
						if ($intwordcount == 0 && (strpos($word, "^") === 0))
							$searchnamesql = sprintf(" and releases.searchname like %s", $db->escapeString(substr($word, 1)."%"));
						elseif (substr($word, 0, 2) == '--')
							$searchnamesql = sprintf(" and releases.searchname not like %s", $db->escapeString("%".substr($word, 2)."%"));
						else
							$searchnamesql = sprintf(" and releases.searchname like %s", $db->escapeString("%".$word."%"));

						$intwordcount++;
					}
				}
			}
		}
        $usenetnamesql="";
		if ($usenetname == "-1"){$usenetnamesql.= ("");}
		else
		{
			$words = explode(" ", $usenetname);
			$usenetnamesql = "";
			$intwordcount = 0;
			if (count($words) > 0)
			{
				foreach ($words as $word)
				{
					if ($word != "")
					{
						//
						// see if the first word had a caret, which indicates search must start with term
						//
						if ($intwordcount == 0 && (strpos($word, "^") === 0))
							$usenetnamesql = sprintf(" and releases.name like %s", $db->escapeString(substr($word, 1)."%"));
						elseif (substr($word, 0, 2) == '--')
							$usenetnamesql = sprintf(" and releases.name not like %s", $db->escapeString("%".substr($word, 2)."%"));
						else
							$usenetnamesql = sprintf(" and releases.name like %s", $db->escapeString("%".$word."%"));

						$intwordcount++;
					}
				}
			}
		}

		if ($postername == "-1"){$posternamesql = ("");}
		else
		{
			$words = explode(" ", $postername);
			$posternamesql = "";
			$intwordcount = 0;
			if (count($words) > 0)
			{
				foreach ($words as $word)
				{
					if ($word != "")
					{
						//
						// see if the first word had a caret, which indicates search must start with term
						//
						if ($intwordcount == 0 && (strpos($word, "^") === 0))
							$posternamesql = sprintf(" and releases.fromname like %s", $db->escapeString(substr($word, 1)."%"));
						elseif (substr($word, 0, 2) == '--')
							$posternamesql = sprintf(" and releases.fromname not like %s", $db->escapeString("%".substr($word, 2)."%"));
						else
							$posternamesql = sprintf(" and releases.fromname like %s", $db->escapeString("%".$word."%"));

						$intwordcount++;
					}
				}
			}
		}

		if ($groupname == "-1"){$groupIDsql = ("");}
		else
		{
			$groupID = $groups->getIDByName($db->escapeString($groupname));
			$groupIDsql = sprintf(" and releases.groupID = %d ", $groupID);
		}

		if ($sizefrom == "-1"){$sizefromsql= ("");}
		if ($sizefrom == "1"){$sizefromsql= (" and releases.size > 104857600 ");}
		if ($sizefrom == "2"){$sizefromsql= (" and releases.size > 262144000 ");}
		if ($sizefrom == "3"){$sizefromsql= (" and releases.size > 524288000 ");}
		if ($sizefrom == "4"){$sizefromsql= (" and releases.size > 1073741824 ");}
		if ($sizefrom == "5"){$sizefromsql= (" and releases.size > 2147483648 ");}
		if ($sizefrom == "6"){$sizefromsql= (" and releases.size > 3221225472 ");}
		if ($sizefrom == "7"){$sizefromsql= (" and releases.size > 4294967296 ");}
		if ($sizefrom == "8"){$sizefromsql= (" and releases.size > 8589934592 ");}
		if ($sizefrom == "9"){$sizefromsql= (" and releases.size > 17179869184 ");}
		if ($sizefrom == "10"){$sizefromsql= (" and releases.size > 34359738368 ");}
		if ($sizefrom == "11"){$sizefromsql= (" and releases.size > 68719476736 ");}

		if ($sizeto == "-1"){$sizetosql= ("");}
		if ($sizeto == "1"){$sizetosql= (" and releases.size < 104857600 ");}
		if ($sizeto == "2"){$sizetosql= (" and releases.size < 262144000 ");}
		if ($sizeto == "3"){$sizetosql= (" and releases.size < 524288000 ");}
		if ($sizeto == "4"){$sizetosql= (" and releases.size < 1073741824 ");}
		if ($sizeto == "5"){$sizetosql= (" and releases.size < 2147483648 ");}
		if ($sizeto == "6"){$sizetosql= (" and releases.size < 3221225472 ");}
		if ($sizeto == "7"){$sizetosql= (" and releases.size < 4294967296 ");}
		if ($sizeto == "8"){$sizetosql= (" and releases.size < 8589934592 ");}
		if ($sizeto == "9"){$sizetosql= (" and releases.size < 17179869184 ");}
		if ($sizeto == "10"){$sizetosql= (" and releases.size < 34359738368 ");}
		if ($sizeto == "11"){$sizetosql= (" and releases.size < 68719476736 ");}

		if ($hasnfo == "0"){$hasnfosql= ("");}
		else{$hasnfosql= (" and releases.nfostatus = 1 ");}

		if ($hascomments == "0"){$hascommentssql= ("");}
		else{$hascommentssql= (" and releases.comments > 0 ");}

		if ($daysnew == "-1"){$daysnewsql= ("");}
		else{$daysnewsql= sprintf(" and releases.postdate < now() - interval %d day ", $daysnew);}

		if ($daysold == "-1"){$daysoldsql= ("");}
		else{$daysoldsql= sprintf(" and releases.postdate > now() - interval %d day ", $daysold);}

		$exccatlist = "";
		if (count($excludedcats) > 0){$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";}

		if ($orderby == "")
		{
			$order[0] = " postdate ";
			$order[1] = " desc ";
		}
		else{$order = $this->getBrowseOrder($orderby);}

		$sql = sprintf("SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID, re.releaseID as reID, cp.ID as categoryParentID from releases left outer join releasevideo re on re.releaseID = releases.ID left outer join releasenfo rn on rn.releaseID = releases.ID left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s %s %s %s %s %s %s %s %s order by %s %s limit %d, %d ", $searchnamesql, $usenetnamesql, $posternamesql, $groupIDsql, $sizefromsql, $sizetosql, $hasnfosql, $hascommentssql, $catsrch, $daysnewsql, $daysoldsql, $exccatlist, $order[0], $order[1], $offset, $limit);
		$orderpos = strpos($sql, "order by");
		$wherepos = strpos($sql, "where");
		$sqlcount = "select count(releases.ID) as num from releases ".substr($sql, $wherepos,$orderpos-$wherepos);

		$countres = $db->queryOneRow($sqlcount);
		$res = $db->query($sql);
		if (count($res) > 0){$res[0]['_totalrows'] = $countres['num'];}

		return $res;
	}

	public function search($search, $cat=array(-1), $offset=0, $limit=1000, $orderby='', $maxage=-1, $excludedcats=array())
	{
		$db = new DB();
		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
				if ($category != -1)
				{
					$categ = new Category();
					if ($categ->isParent($category))
					{
						$children = $categ->getChildren($category);
						$chlist = "-99";
						foreach ($children as $child)
							$chlist.=", ".$child['ID'];

						if ($chlist != "-99")
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $search);
		$searchsql = "";
		$intwordcount = 0;
		if (count($words) > 0)
		{
			foreach ($words as $word)
			{
				if ($word != "")
				{
					//
					// see if the first word had a caret, which indicates search must start with term
					//
					if ($intwordcount == 0 && (strpos($word, "^") === 0))
						$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString(substr($word, 1)."%"));
					elseif (substr($word, 0, 2) == '--')
						$searchsql.= sprintf(" and releases.searchname not like %s", $db->escapeString("%".substr($word, 2)."%"));
					else
						$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString("%".$word."%"));

					$intwordcount++;
				}
			}
		}

		if ($maxage > 0)
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";

		if ($orderby == "")
		{
			$order[0] = " postdate ";
			$order[1] = " desc ";
		}
		else
			$order = $this->getBrowseOrder($orderby);

		$sql = sprintf("SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID, re.releaseID as reID, cp.ID as categoryParentID from releases left outer join releasevideo re on re.releaseID = releases.ID left outer join releasenfo rn on rn.releaseID = releases.ID left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s order by %s %s limit %d, %d ", $searchsql, $catsrch, $maxage, $exccatlist, $order[0], $order[1], $offset, $limit);
		$orderpos = strpos($sql, "order by");
		$wherepos = strpos($sql, "where");
		$sqlcount = "select count(releases.ID) as num from releases ".substr($sql, $wherepos,$orderpos-$wherepos);

		$countres = $db->queryOneRow($sqlcount);
		$res = $db->query($sql);
		if (count($res) > 0)
			$res[0]['_totalrows'] = $countres['num'];

		return $res;
	}

	public function searchsubject($search, $cat=array(-1), $offset=0, $limit=1000, $orderby='', $maxage=-1, $excludedcats=array())
	{
		$db = new DB();
		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
				if ($category != -1)
				{
					$categ = new Category();
					if ($categ->isParent($category))
					{
						$children = $categ->getChildren($category);
						$chlist = "-99";
						foreach ($children as $child)
							$chlist.=", ".$child['ID'];

						if ($chlist != "-99")
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $search);
		$searchsql = "";
		$intwordcount = 0;
		if (count($words) > 0)
		{
			foreach ($words as $word)
			{
				if ($word != "")
				{
					//
					// see if the first word had a caret, which indicates search must start with term
					//
					if ($intwordcount == 0 && (strpos($word, "^") === 0))
						$searchsql.= sprintf(" and releases.name like %s", $db->escapeString(substr($word, 1)."%"));
					elseif (substr($word, 0, 2) == '--')
						$searchsql.= sprintf(" and releases.name not like %s", $db->escapeString("%".substr($word, 2)."%"));
					else
						$searchsql.= sprintf(" and releases.name like %s", $db->escapeString("%".$word."%"));

					$intwordcount++;
				}
			}
		}

		if ($maxage > 0)
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and releases.categoryID not in (".implode(",", $excludedcats).")";

		if ($orderby == "")
		{
			$order[0] = " postdate ";
			$order[1] = " desc ";
		}
		else
			$order = $this->getBrowseOrder($orderby);

		$sql = sprintf("SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID, re.releaseID as reID, cp.ID as categoryParentID from releases left outer join releasevideo re on re.releaseID = releases.ID left outer join releasenfo rn on rn.releaseID = releases.ID left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s order by %s %s limit %d, %d ", $searchsql, $catsrch, $maxage, $exccatlist, $order[0], $order[1], $offset, $limit);
		$orderpos = strpos($sql, "order by");
		$wherepos = strpos($sql, "where");
		$sqlcount = "select count(releases.ID) as num from releases ".substr($sql, $wherepos,$orderpos-$wherepos);

		$countres = $db->queryOneRow($sqlcount);
		$res = $db->query($sql);
		if (count($res) > 0)
			$res[0]['_totalrows'] = $countres['num'];

		return $res;
	}

	public function searchbyRageId($rageId, $series="", $episode="", $offset=0, $limit=100, $name="", $cat=array(-1), $maxage=-1)
	{
		$db = new DB();

		if ($rageId != "-1")
			$rageId = sprintf(" and rageID = %d ", $rageId);
		else
			$rageId = "";

		if ($series != "")
		{
			//
			// Exclude four digit series, which will be the year 2010 etc
			//
			if (is_numeric($series) && strlen($series) != 4)
				$series = sprintf('S%02d', $series);

			$series = sprintf(" and upper(releases.season) = upper(%s)", $db->escapeString($series));
		}
		if ($episode != "")
		{
			if (is_numeric($episode))
				$episode = sprintf('E%02d', $episode);

			$episode = sprintf(" and releases.episode like %s", $db->escapeString('%'.$episode.'%'));
		}

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $name);
		$searchsql = "";
		$intwordcount = 0;
		if (count($words) > 0)
		{
			foreach ($words as $word)
			{
				if ($word != "")
				{
					//
					// see if the first word had a caret, which indicates search must start with term
					//
					if ($intwordcount == 0 && (strpos($word, "^") === 0))
						$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString(substr($word, 1)."%"));
					elseif (substr($word, 0, 2) == '--')
						$searchsql.= sprintf(" and releases.searchname not like %s", $db->escapeString("%".substr($word, 2)."%"));
					else
						$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString("%".$word."%"));

					$intwordcount++;
				}
			}
		}

		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
				if ($category != -1)
				{
					$categ = new Category();
					if ($categ->isParent($category))
					{
						$children = $categ->getChildren($category);
						$chlist = "-99";
						foreach ($children as $child)
							$chlist.=", ".$child['ID'];

						if ($chlist != "-99")
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}

		if ($maxage > 0)
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";

		$sql = sprintf("SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID, re.releaseID as reID from releases left outer join category c on c.ID = releases.categoryID left outer join groups on groups.ID = releases.groupID left outer join releasevideo re on re.releaseID = releases.ID left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s %s %s order by postdate desc limit %d, %d ", $rageId, $series, $episode, $searchsql, $catsrch, $maxage, $offset, $limit);
		$orderpos = strpos($sql, "order by");
		$wherepos = strpos($sql, "where");
		$sqlcount = "select count(releases.ID) as num from releases ".substr($sql, $wherepos,$orderpos-$wherepos);

		$countres = $db->queryOneRow($sqlcount);
		$res = $db->query($sql);
		if (count($res) > 0)
			$res[0]['_totalrows'] = $countres['num'];

		return $res;
	}

	public function searchbyAnidbId($anidbID, $epno='', $offset=0, $limit=100, $name='', $cat=array(-1), $maxage=-1)
	{
		$db = new DB();

		$anidbID = ($anidbID > -1) ? sprintf(" AND anidbID = %d ", $anidbID) : '';

		is_numeric($epno) ? $epno = sprintf(" AND releases.episode LIKE '%s' ", $db->escapeString('%'.$epno.'%')) : '';

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $name);
		$searchsql = "";
		$intwordcount = 0;
		if (count($words) > 0)
		{
			foreach ($words as $word)
			{
				if ($word != "")
				{
					//
					// see if the first word had a caret, which indicates search must start with term
					//
					if ($intwordcount == 0 && (strpos($word, "^") === 0))
						$searchsql.= sprintf(" AND releases.searchname LIKE '%s' ", $db->escapeString(substr($word, 1)."%"));
					elseif (substr($word, 0, 2) == '--')
						$searchsql.= sprintf(" AND releases.searchname NOT LIKE '%s' ", $db->escapeString("%".substr($word, 2)."%"));
					else
						$searchsql.= sprintf(" AND releases.searchname LIKE '%s' ", $db->escapeString("%".$word."%"));

					$intwordcount++;
				}
			}
		}

		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
				if ($category != -1)
				{
					$categ = new Category();
					if ($categ->isParent($category))
					{
						$children = $categ->getChildren($category);
						$chlist = "-99";
						foreach ($children as $child)
							$chlist.=", ".$child['ID'];

						if ($chlist != "-99")
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}

		$maxage = ($maxage > 0) ? sprintf(" and postdate > now() - interval %d day ", $maxage) : '';

		$sql = sprintf("SELECT releases.*, concat(cp.title, ' > ', c.title)
			AS category_name, concat(cp.ID, ',', c.ID) AS category_ids, groups.name AS group_name, rn.ID AS nfoID
			FROM releases LEFT OUTER JOIN category c ON c.ID = releases.categoryID LEFT OUTER JOIN groups ON groups.ID = releases.groupID
			LEFT OUTER JOIN releasenfo rn ON rn.releaseID = releases.ID and rn.nfo IS NOT NULL LEFT OUTER JOIN category cp ON cp.ID = c.parentID
			WHERE releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s %s ORDER BY postdate desc LIMIT %d, %d ",
			$anidbID, $epno, $searchsql, $catsrch, $maxage, $offset, $limit);
		$orderpos = strpos($sql, "ORDER BY");
		$wherepos = strpos($sql, "WHERE");
		$sqlcount = "SELECT count(releases.ID) AS num FROM releases ".substr($sql, $wherepos,$orderpos-$wherepos);

		$countres = $db->queryOneRow($sqlcount);
		$res = $db->query($sql);
		if (count($res) > 0)
			$res[0]['_totalrows'] = $countres['num'];

		return $res;
	}

	public function searchbyImdbId($imdbId, $offset=0, $limit=100, $name="", $cat=array(-1), $maxage=-1)
	{
		$db = new DB();

		if ($imdbId != "-1" && is_numeric($imdbId))
		{
			//pad id with zeros just in case
			$imdbId = str_pad($imdbId, 7, "0",STR_PAD_LEFT);
			$imdbId = sprintf(" and imdbID = %d ", $imdbId);
		}
		else
		{
			$imdbId = "";
		}

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the fulltext match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $name);
		$searchsql = "";
		$intwordcount = 0;
		if (count($words) > 0)
		{
			foreach ($words as $word)
			{
				if ($word != "")
				{
					//
					// see if the first word had a caret, which indicates search must start with term
					//
					if ($intwordcount == 0 && (strpos($word, "^") === 0))
						$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString(substr($word, 1)."%"));
					elseif (substr($word, 0, 2) == '--')
						$searchsql.= sprintf(" and releases.searchname not like %s", $db->escapeString("%".substr($word, 2)."%"));
					else
						$searchsql.= sprintf(" and releases.searchname like %s", $db->escapeString("%".$word."%"));

					$intwordcount++;
				}
			}
		}

		$catsrch = "";
		if (count($cat) > 0 && $cat[0] != -1)
		{
			$catsrch = " and (";
			foreach ($cat as $category)
			{
				if ($category != -1)
				{
					$categ = new Category();
					if ($categ->isParent($category))
					{
						$children = $categ->getChildren($category);
						$chlist = "-99";
						foreach ($children as $child)
							$chlist.=", ".$child['ID'];

						if ($chlist != "-99")
								$catsrch .= " releases.categoryID in (".$chlist.") or ";
					}
					else
					{
						$catsrch .= sprintf(" releases.categoryID = %d or ", $category);
					}
				}
			}
			$catsrch.= "1=2 )";
		}

		if ($maxage > 0)
			$maxage = sprintf(" and postdate > now() - interval %d day ", $maxage);
		else
			$maxage = "";

		$sql = sprintf("SELECT releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name, rn.ID as nfoID from releases left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join releasenfo rn on rn.releaseID = releases.ID and rn.nfo is not null left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') %s %s %s %s order by postdate desc limit %d, %d ", $searchsql, $imdbId, $catsrch, $maxage, $offset, $limit);
		$orderpos = strpos($sql, "order by");
		$wherepos = strpos($sql, "where");
		$sqlcount = "select count(releases.ID) as num from releases ".substr($sql, $wherepos,$orderpos-$wherepos);

		$countres = $db->queryOneRow($sqlcount);
		$res = $db->query($sql);
		if (count($res) > 0)
			$res[0]['_totalrows'] = $countres['num'];

		return $res;
	}

	public function searchSimilar($currentid, $name, $limit=6, $excludedcats=array())
	{
		$name = $this->getSimilarName($name);
		$results = $this->search($name, array(-1), 0, $limit, '', -1, $excludedcats);
		if (!$results)
			return $results;

		//
		// Get the category for the parent of this release
		//
		$currRow = $this->getById($currentid);
		$cat = new Category();
		$catrow = $cat->getById($currRow['categoryID']);
		$parentCat = $catrow['parentID'];

		$ret = array();
		foreach ($results as $res)
			if ($res['ID'] != $currentid && $res['categoryParentID'] == $parentCat)
				$ret[] = $res;

		return $ret;
	}

	public function getSimilarName($name)
	{
		$words = str_word_count(str_replace(array(".","_"), " ", $name), 2);
		$firstwords = array_slice($words, 0, 2);
		return implode(' ', $firstwords);
	}

	public function getByGuid($guid)
	{
		$db = new DB();
		if (is_array($guid))
		{
			$tmpguids = array();
			foreach($guid as $g)
				$tmpguids[] = $db->escapeString($g);
			$gsql = sprintf('guid in (%s)', implode(',',$tmpguids));
		} else {
			$gsql = sprintf('guid = %s', $db->escapeString($guid));
		}
		$sql = sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, concat(cp.ID, ',', c.ID) as category_ids, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where %s ", $gsql);
		return (is_array($guid)) ? $db->query($sql) : $db->queryOneRow($sql);
	}

	//
	// writes a zip file of an array of release guids directly to the stream
	//
	public function getZipped($guids)
	{
		$nzb = new NZB();
		$zipfile = new zipfile();

		foreach ($guids as $guid)
		{
			$nzbpath = $nzb->getNZBPath($guid, $this->site->nzbpath, false, $this->site->nzbsplitlevel);

			if (file_exists($nzbpath))
			{
				ob_start();
				@readgzfile($nzbpath);
				$nzbfile = ob_get_contents();
				ob_end_clean();

				$filename = $guid;
				$r = $this->getByGuid($guid);
				if ($r)
					$filename = $r['searchname'];

				$zipfile->addFile($nzbfile, $filename.".nzb");
			}
		}

		return $zipfile->file();
	}

	public function getbyRageId($rageid, $series = "", $episode = "")
	{
		$db = new DB();

		if ($series != "")
		{
			//
			// Exclude four digit series, which will be the year 2010 etc
			//
			if (is_numeric($series) && strlen($series) != 4)
				$series = sprintf('S%02d', $series);

			$series = sprintf(" and upper(releases.season) = upper(%s)", $db->escapeString($series));
		}

		if ($episode != "")
		{
			if (is_numeric($episode))
				$episode = sprintf('E%02d', $episode);

			$episode = sprintf(" and upper(releases.episode) = upper(%s)", $db->escapeString($episode));
		}

		return $db->queryOneRow(sprintf("select releases.*, concat(cp.title, ' > ', c.title) as category_name, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID  left outer join category c on c.ID = releases.categoryID left outer join category cp on cp.ID = c.parentID where releases.passwordstatus <= (select value from site where setting='showpasswordedrelease') and rageID = %d %s %s", $rageid, $series, $episode));
	}

	public function removeRageIdFromReleases($rageid)
	{
		$db = new DB();
		$res = $db->queryOneRow(sprintf("select count(ID) as num from releases where rageID = %d", $rageid));
		$ret = $res['num'];
		$res = $db->query(sprintf("update releases set rageID = -1, seriesfull = null, season = null, episode = null where rageID = %d", $rageid));
		return $ret;
	}

	public function removeAnidbIdFromReleases($anidbID)
	{
		$db = new DB();
		$res = $db->queryOneRow(sprintf("SELECT count(ID) AS num FROM releases WHERE anidbID = %d", $anidbID));
		$ret = $res['num'];
		$res = $db->query(sprintf("UPDATE releases SET anidbID = -1, episode = null, tvtitle = null, tvairdate = null where anidbID = %d", $anidbID));
		return $ret;
	}

	public function getById($id)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("select releases.*, groups.name as group_name from releases left outer join groups on groups.ID = releases.groupID where releases.ID = %d ", $id));
	}

	public function getReleaseNfo($id, $incnfo=true)
	{
		$db = new DB();
		$selnfo = ($incnfo) ? ', uncompress(nfo) as nfo' : '';
		return $db->queryOneRow(sprintf("SELECT ID, releaseID".$selnfo." FROM releasenfo where releaseID = %d AND nfo IS NOT NULL", $id));
	}

	public function updateGrab($guid)
	{
		if ($this->updategrabs)
		{
			$db = new DB();
			$db->queryOneRow(sprintf("update releases set grabs = grabs + 1 where guid = %s", $db->escapeString($guid)));
		}
	}

	//
	// Sends releases back to other->misc.
	//
	public function resetCategorize($where="")
	{
		$db = new DB();
		$db->queryDirect("UPDATE releases set categoryID = 7010, relnamestatus = 0 ".$where);
	}

	//
	// Categorizes releases.
	// $type = name or searchname
	// Returns the quantity of categorized releases.
	//
	public function categorizeRelease($type, $where="", $echooutput=true)
	{
		$db = new DB();
		$cat = new Category();
		$consoletools = new consoleTools();
		$relcount = 0;

		$resrel = $db->queryDirect("SELECT ID, ".$type.", groupID FROM releases ".$where);
        $totalRel = $db->getNumRows($resrel);
		while ($rowrel = $db->fetchAssoc($resrel))
		{
			$catId = $cat->determineCategory($rowrel[$type], $rowrel['groupID']);
			$db->queryDirect(sprintf("UPDATE releases SET categoryID = %d, relnamestatus = 1 WHERE ID = %d", $catId, $rowrel['ID']));
			$relcount ++;
			if ($echooutput)
				$consoletools->overWrite("Categorizing: ".$consoletools->percentString($relcount,$totalRel));
		}
		return $relcount;
	}

	public function processReleasesStage1($groupID, $echooutput=true)
	{

        // TODO: Need to handle the case where a groupID is passed to this routine.

        $db = new DB();
		$consoletools = new ConsoleTools();
		$n = "\n";



		if ($echooutput)
			echo "\033[1;33m[".date("H:i:s A")."] Stage 1 -> Try to find complete collections.\033[0m".$n;
		$stage1 = TIME();
		$where = (!empty($groupID)) ? " AND groupID = ".$groupID : "";

        /* The new, newer, newest way of doing things:
         * Collection total size and binary parts are updated during the parts insertion process
         * of binaries.php (the scan function specifically)
         * Now we just need to look at the binaries and see if the total parts in the database
         * for each binaryID is equal to the number of parts that are supposed to be associated
         * with each binary. If so, we'll set partcheck to 1, if not partcheck will stay at 0
         * so we can scan again next time around.
         *
         * */

        $db->setAutoCommit(false);
        if($echooutput)
            Echo "Updating binaries that are complete...\n";
        $totalBinUpdateTime = microtime(true);
        $db->query("UPDATE binaries AS b SET partcheck = 1 WHERE b.partsInDB >= b.totalParts AND partcheck=0");
        $binaryrowcount = $db->getAffectedRows();


        if($echooutput)
        {
            $avgBinUpdateTime = 0;
            $totalBinUpdateTime = microtime(true) - $totalBinUpdateTime;
            if ($binaryrowcount>0) {$avgBinUpdateTime = $totalBinUpdateTime/$binaryrowcount;}
            if ($avgBinUpdateTime && $binaryrowcount)
            {
                Echo "\nTotal update time: ".number_format($totalBinUpdateTime, 4)."   Average update time: ".number_format($avgBinUpdateTime, 4);
                Echo "\nCommitting database changes... ".$binaryrowcount." binaries were updated.\n";
            }
            else echo "\nCommitting database changes... ".$binaryrowcount." binaries were updated.\n";
        }
        $db->Commit();
        $db->setAutoCommit(TRUE);
        if((time()-5400) > $this->lastFullCollectionCheck)
            $doFullCheck = true;
        else
            $doFullCheck = false;
        if($binaryrowcount>0 || $doFullCheck)
        {
            if($doFullCheck)
                $collectionsresult=$db->queryDirect("SELECT ID, totalFiles FROM collections WHERE totalFiles!=0 AND filecheck IN (0, 1) ".$where);
            else
                $collectionsresult=$db->queryDirect("SELECT ID, totalFiles FROM collections WHERE totalFiles!=0 AND filecheck=1 ".$where);

            $collectionstotal=$db->getNumRows($collectionsresult);
            if($echooutput)
                Echo "\nFound ".$collectionstotal." collections to check.\n";

            if ($collectionstotal>0)
            {
                $colsupdated=0;
                $colsprocessed=0;
                $totalColsUpdateTime = microtime(true);
                $db->setAutoCommit(FALSE);
                while($collectionrow=$db->fetchAssoc($collectionsresult))
                {
                    $colsprocessed++;
                    if($echooutput)
                        $consoletools->overWrite("Collections processed: ".$consoletools->percentString($colsprocessed,$collectionstotal));
                    $binaryrows=$db->queryDirect("SELECT ID FROM binaries WHERE collectionID=".$collectionrow["ID"]." AND partCheck=1");
                    $binarycount=$db->getNumRows($binaryrows);

                    if($binarycount>=$collectionrow["totalFiles"])
                    {
                        $db->query("UPDATE collections SET filecheck=25 WHERE ID=".$collectionrow["ID"]);
                        $colsupdated++;
                    }
                    else
                    {
                        $db->query("UPDATE collections SET filecheck=0 WHERE ID=".$collectionrow["ID"]);
                    }
                }
                $totalColsUpdateTime = microtime(true) - $totalColsUpdateTime;
                if($echooutput)
                {
                    echo "\nTotal update time: ".number_format($totalColsUpdateTime,4)." Average update time: ".number_format(($totalColsUpdateTime/$collectionstotal),4);
                    Echo "\nCommitting database changes... ".$colsupdated." total collections were updated.\n";
                }
                $db->Commit();
                $db->setAutoCommit(TRUE);

            }
        }
        else
            echo "\nNo binaries updated. Skipping collection checks.\n";
        unset($colsupdated, $colsprocessed, $collectionrow, $binaryrows, $binarycount, $binrow);
        unset($collectionsresult, $collectionstotal);
        // Set the next time to do a full check here in case doing the full check took a really long time
        if($doFullCheck)
            $db->query("UPDATE site SET value=".$db->escapeString(time())." WHERE setting='lastFullCollectionCheck'");

        if ($echooutput)
			echo "\n".$consoletools->convertTime(TIME() - $stage1)."\n";
	}

	public function processReleasesStage2($groupID, $echooutput=true)
	{
        // TODO: Fix function to handle when a value is passed in $groupID

        $db = new DB();
        $consoletools = new ConsoleTools();
        $n = "\n";
        $where = (!empty($groupID)) ? " AND groupID = " . $groupID : "";

        if ($echooutput)
            echo $n."\033[1;33m[".date("H:i:s A")."] Stage 2 -> Validating binary counts of collections.\033[0m".$n;
        $stage2 = TIME();

        Echo "\nSelecting completed collections...\n";
        $collectionsresult=$db->queryDirect("SELECT ID, totalFiles FROM collections AS c WHERE c.filecheck = 2");
        $collectioncount=$db->getNumRows($collectionsresult);
        $colsupdated=0;
        if($collectioncount)
        {
            if($echooutput)
                echo "Verifying binaries for ".$collectioncount." collections.\n";
            $colstotal=0;
            $totalColsUpdateTime = microtime(true);
            $db->setAutoCommit(FALSE);
            while($collectionsrow=$db->fetchAssoc($collectionsresult))
            {
                $colstotal++;
                if($echooutput)
                    $consoletools->overWrite("Collections processed: ".$consoletools->percentString($colstotal, $collectioncount));

                $collectionsize=$db->queryOneRow("SELECT COUNT(*) as binNumber FROM binaries AS b WHERE b.collectionID=".$collectionsrow["ID"]);

                If($collectionsize['binNumber']>=$collectionsrow['totalFiles'])
                {
                    $db->query("UPDATE collections SET filecheck=25 WHERE ID=".$collectionsrow["ID"]);
                    $colsupdated++;
                }
                $binaryrows=$db->queryDirect("SELECT ID FROM binaries WHERE collectionID=".$collectionsrow["ID"]." AND partCheck=1");
                $binarycount=$db->getNumRows($binaryrows);

                if($binarycount>=$collectionsrow["totalFiles"])
                {
                    $db->query("UPDATE collections SET filecheck=25 WHERE ID=".$collectionsrow["ID"]);
                    $colsupdated++;
                }

            }
            $totalColsUpdateTime = microtime(true) - $totalColsUpdateTime;
            if($echooutput)
            {
                echo "\nTotal update time: ".number_format($totalColsUpdateTime,4)." Average update time: ".number_format(($totalColsUpdateTime/$collectioncount), 4);
                echo "\nCommitting database changes... ".$colsupdated." collections were updated.\n";
            }
            $db->Commit();
            $db->setAutoCommit(TRUE);

            $db->query("UPDATE collections SET filecheck=3 WHERE filecheck=25 and filesize>0 LIMIT ".($this->stage5limit * 3));
            if($echooutput)
                echo "Queueing up ".$db->getAffectedRows()." collections to be processed.\n";
            sleep(1);
        }
        else
        {
            if ($echooutput)
                echo "\nNo collections found to update at this time.\n";
            $db->query("UPDATE collections SET filecheck=3 WHERE filecheck=25 and filesize>0 LIMIT ".($this->stage5limit * 3));
            $colsupdated = $db->getAffectedRows();
            if($echooutput && $colsupdated)
                echo "Queueing up ".$colsupdated." old collections to be processed.\n";

        }
		if ($echooutput)
            echo "\n".$consoletools->convertTime(TIME() - $stage2)."\n";

        if($colsupdated < 1)
        {
            // $db->query("UPDATE collections SET filecheck=3 WHERE filecheck=2 AND filesize>0");
            $filecheck3 = $db->queryDirect("SELECT ID FROM collections where filecheck IN (2,25,3,4) AND filesize>0");
            $colsupdated = $db->getNumRows($filecheck3);
            if($colsupdated)
            {
                if ($echooutput)
                    echo "\nFound ".$colsupdated." collections awaiting processing.";
            }
        }
        return $colsupdated;
	}

	public function processReleasesStage3($groupID, $echooutput=true)
	{

        $db = new DB();
		$consoletools = new ConsoleTools();

        if ($echooutput)
			echo "\n\033[1;33m[".date("H:i:s A")."] Stage 3 -> Delete collections smaller/larger than minimum size/file count from group/site setting.\033[0m\n";
		$stage3 = TIME();
        $where = (!empty($groupID)) ? " AND c.groupID=".$groupID : '';
        if ($echooutput)
            echo "\nProcessing collections...\n";
        $collectionsresult=$db->queryDirect("SELECT c.ID AS colID, c.filesize AS colfilesize, c.totalFiles AS coltotalFiles, g.minsizetoformrelease AS groupsize, g.minfilestoformrelease AS groupfiles FROM collections AS c INNER JOIN groups as g ON c.groupID=g.ID WHERE c.filecheck=3");
        $collectionstotal=$db->getNumRows($collectionsresult);
        if($echooutput)
            Echo "\nFound ".$collectionstotal." collections to check.\n";

        if ($collectionstotal>0)
        {
            $colsDeleted=0;
            $colsprocessed=0;
            $totalColsProcessTime = microtime(true);
            $db->setAutoCommit(FALSE);
            while($collectionrow=$db->fetchAssoc($collectionsresult))
            {
                $colsprocessed++;
                if($echooutput)
                    $consoletools->overWrite("Collections processed: ".$consoletools->percentString($colsprocessed,$collectionstotal)." ID = ".$collectionrow['colID']);
                $tooLittleTooMuch = false;
                if($collectionrow['groupsize'] != 0  && !is_null($collectionrow['groupsize']) && $collectionrow['colfilesize'] < $collectionrow['groupsize'])
                    $tooLittleTooMuch = true;
                elseif($this->siteMinFileSize != 0 && !is_null($this->siteMinFileSize) && $collectionrow['colfilesize'] < $this->siteMinFileSize)
                    $tooLittleTooMuch = true;
                elseif($collectionrow['groupfiles'] != 0 && !is_null($collectionrow['groupfiles']) && $collectionrow['coltotalFiles'] < $collectionrow['groupfiles'])
                    $tooLittleTooMuch = true;
                elseif($this->siteMinFileCount != 0 && !is_null($this->siteMinFileCount) && $collectionrow['coltotalFiles'] < $this->siteMinFileCount)
                    $tooLittleTooMuch = true;
                elseif($this->siteMaxFileSize !=0 && !is_null($this->siteMaxFileSize) && $collectionrow['colfilesize'] > $this->siteMaxFileSize)
                    $tooLittleTooMuch = true;
                // See if we got a hit
                if($tooLittleTooMuch)
                {
                    $db->query("UPDATE collections SET filecheck=5 WHERE ID=".$collectionrow['colID']);
                    $colsDeleted ++;
                }
            }
            $db->Commit();
            $db->setAutoCommit(true);
            $totalColsProcessTime = microtime(true) - $totalColsProcessTime;
            if ($echooutput)
            {
                echo "\n\nTotal collections marked for deletion: ".$colsDeleted."\n";
                echo "Total processing time: ".number_format($totalColsProcessTime,4)."\nAverage per collection: ".number_format(($totalColsProcessTime/$colsprocessed),4)."\n";
            }
        }
        // Nothing more to see here... moving on
        $db->query("UPDATE collections SET filecheck=4 WHERE filecheck=3");
        if ($echooutput)
		echo $consoletools->convertTime(TIME() - $stage3);

	}

	public function processReleasesStage4($groupID, $echooutput=true)
	{
		$db = new DB();
		$page = new Page();
		$consoletools = new ConsoleTools();
		$n = "\n";
		$retcount = 0;
		$where = (!empty($groupID)) ? " AND groupID = " . $groupID : "";
		$namecleaning = new nameCleaning();
		// $predb = new  Predb();

		if ($echooutput)
			echo $n."\033[1;33m[".date("H:i:s A")."] Stage 4 -> Create releases.\033[0m".$n;
		$stage4 = TIME();
        $releasesAdded = 0;
		if($rescol = $db->queryDirect("SELECT * FROM collections WHERE filecheck = 4 " . $where . " LIMIT ".$this->stage5limit))
		{
			$totalStage4cols = $db->getNumRows($rescol);
            while ($rowcol = $db->fetchAssoc($rescol))
			{
				$cleanArr = array('#', '@', '$', '%', '^', '§', '¨', '©', 'Ö');
				$cleanRelName = str_replace($cleanArr, '', $rowcol['subject']);
				$cleanerName = $namecleaning->releaseCleaner($rowcol['subject'], $rowcol['groupID']);
				$relguid = sha1(uniqid());
				if($db->queryInsert(sprintf("INSERT IGNORE INTO releases (name, searchname, totalpart, groupID, adddate, guid, rageID, postdate, fromname, size, passwordstatus, haspreview, categoryID, nfostatus, relnamestatus)
											VALUES (%s, %s, %d, %d, now(), %s, -1, %s, %s, %s, %d, -1, 7010, -1, 0)",
											$db->escapeString($cleanRelName), $db->escapeString($cleanerName), $rowcol['totalFiles'], $rowcol['groupID'], $db->escapeString($relguid),
											$db->escapeString($rowcol['date']), $db->escapeString($rowcol['fromname']), $db->escapeString($rowcol['filesize']), ($page->site->checkpasswordedrar == "1" ? -1 : 0))))
				{
					$relid = $db->getInsertID();
                    // TODO: Look at better ways to implement the predb search.
					// $predb->matchPre($cleanRelName, $relid);
					// Update collections table to say we inserted the release.
					$db->queryDirect(sprintf("UPDATE collections SET filecheck = 45, releaseID = %d WHERE ID = %d", $relid, $rowcol['ID']));
					$retcount ++;
					if ($echooutput)
						$consoletools->overWrite("Creating release ".$consoletools->percentString($retcount, $totalStage4cols));
                        // echo "Added release ".$cleanRelName.$n;
				}
				else
				{
					if ($echooutput)
						echo "\033[01;31mError Inserting Release: \033[0m[".$relid."] " . $cleanerName . ": " . $db->Error() . $n;
				}
			}
		}

		$timing = $consoletools->convertTime(TIME() - $stage4);
		if ($echooutput)
			echo "\n".$retcount . " Releases added in " . $timing . ".";
		return $retcount;
	}

	public function processReleasesStage4_loop($groupID, $echooutput=false)
	{
		$tot_retcount = 0;
		do
		{
			$retcount = $this->processReleasesStage4($groupID);
			$tot_retcount = $tot_retcount + $retcount;
		} while ($retcount > 0);

		return $tot_retcount;
	}

	/*
	 *	Adding this in to delete releases before NZB's are created.
	 */
	public function processReleasesStage4dot5($groupID, $echooutput=true)
	{

        // TODO: Another stage that to look at rewriting, same reasons as Stage 3 (see comments)
        // Going to remove this stage from the 4567loop function.  I don't really see a need
        // to perform this stage during create release process, since anything to big or small should have been caught
        // in stage 3.  Not going to remove the function though, because I can see a use for
        // it to process NZB's that have been imported.  Will need to look at the NZB import
        // process in more detail to see what happens currently.

        // Does removeCrapReleases use this function? Need to look

        $db = new DB();
		$consoletools = new ConsoleTools();
		$n = "\n";
		$minsizecount = 0;
		$maxsizecount = 0;
		$minfilecount = 0;
		$catminsizecount = 0;

		if ($echooutput)
			echo $n."\033[1;33m[".date("H:i:s A")."] Stage 4.5 -> Delete releases smaller/larger than minimum size/file count from group/site setting.\033[0m".$n;
		$stage4dot5 = TIME();

		$catresrel = $db->query("select c.ID as ID, CASE WHEN c.minsize = 0 THEN cp.minsize ELSE c.minsize END as minsize from category c left outer join category cp on cp.ID = c.parentID where c.parentID is not null");

		foreach ($catresrel as $catrowrel) {
			$resrel = $db->query(sprintf("SELECT r.ID, r.guid from releases r where r.categoryID = %d AND r.size < %d and nzbstatus = 0", $catrowrel['ID'], $catrowrel['minsize']));
			foreach ($resrel as $rowrel)
			{
				$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
				$catminsizecount ++;
			}
		}

		if ($groupID == "")
		{
			$groups = new Groups();
			$groupIDs = $groups->getActiveIDs();

			foreach ($groupIDs as $groupID)
			{
				if ($resrel = $db->query("SELECT r.ID, r.guid FROM releases r LEFT JOIN
							(SELECT g.ID, coalesce(g.minsizetoformrelease, s.minsizetoformrelease)
							as minsizetoformrelease FROM groups g INNER JOIN ( SELECT value as minsizetoformrelease
							FROM site WHERE setting = 'minsizetoformrelease' ) s ) g ON g.ID = r.groupID WHERE
							g.minsizetoformrelease != 0 AND r.size < minsizetoformrelease and nzbstatus = 0 AND groupID = ".$groupID['ID']))
				{
					foreach ($resrel as $rowrel)
					{
						$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
						$minsizecount ++;
					}
				}

				$maxfilesizeres = $db->queryOneRow("SELECT value FROM site WHERE setting = maxsizetoformrelease");
				if ($maxfilesizeres['value'] != 0)
				{
					if ($resrel = $db->query(sprintf("SELECT ID, guid from releases where groupID = %d AND filesize > %d and nzbstatus = 0 ", $groupID['ID'], $maxfilesizeres['value'])))
					{
						foreach ($resrel as $rowrel)
						{
							$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
							$maxsizecount ++;
						}
					}
				}

				if ($resrel = $db->query("SELECT r.ID FROM releases r LEFT JOIN
							(SELECT g.ID, guid, coalesce(g.minfilestoformrelease, s.minfilestoformrelease)
							as minfilestoformrelease FROM groups g INNER JOIN ( SELECT value as minfilestoformrelease
							FROM site WHERE setting = 'minfilestoformrelease' ) s ) g ON g.ID = r.groupID WHERE
							g.minfilestoformrelease != 0 AND r.totalpart < minfilestoformrelease and nzbstatus = 0 AND groupID = ".$groupID['ID']))
				{
					foreach ($resrel as $rowrel)
					{
						$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
						$minfilecount ++;
					}
				}
			}
		}
		else
		{
			if ($resrel = $db->query("SELECT r.ID FROM releases r LEFT JOIN
						(SELECT g.ID, guid, coalesce(g.minsizetoformrelease, s.minsizetoformrelease)
						as minsizetoformrelease FROM groups g INNER JOIN ( SELECT value as minsizetoformrelease
						FROM site WHERE setting = 'minsizetoformrelease' ) s ) g ON g.ID = r.groupID WHERE
						g.minsizetoformrelease != 0 AND r.size < minsizetoformrelease and nzbstatus = 0 AND groupID = ".$groupID))
			{
				foreach ($resrel as $rowrel)
				{
					$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
					$minsizecount ++;
				}
			}

			$maxfilesizeres = $db->queryOneRow("SELECT value FROM site WHERE setting = maxsizetoformrelease");
			if ($maxfilesizeres['value'] != 0)
			{
				if ($resrel = $db->query(sprintf("SELECT ID, guid from releases where groupID = %d AND filesize > %d and nzbstatus = 0 ", $groupID, $maxfilesizeres['value'])))
				{
					foreach ($resrel as $rowrel)
					{
						$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
						$maxsizecount ++;
					}
				}
			}

			if ($resrel = $db->query("SELECT r.ID, guid FROM releases r LEFT JOIN
						(SELECT g.ID, coalesce(g.minfilestoformrelease, s.minfilestoformrelease)
						as minfilestoformrelease FROM groups g INNER JOIN ( SELECT value as minfilestoformrelease
						FROM site WHERE setting = 'minfilestoformrelease' ) s ) g ON g.ID = r.groupID WHERE
						g.minfilestoformrelease != 0 AND r.totalpart < minfilestoformrelease and nzbstatus = 0 AND groupID = ".$groupID))
			{
				foreach ($resrel as $rowrel)
				{
					$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
					$minfilecount ++;
				}
			}
		}

		$delcount = $minsizecount+$maxsizecount+$minfilecount+$catminsizecount;
		if ($echooutput && $delcount > 0)
				echo "Deleted ".$minsizecount+$maxsizecount+$minfilecount." releases smaller/larger than group/site settings.".$n;
		if ($echooutput)
			echo $consoletools->convertTime(TIME() - $stage4dot5);
	}

	public function processReleasesStage5($groupID, $echooutput=true)
	{
		$db = new DB();
		$nzb = new Nzb();
		$page = new Page();
		$cat = new Category();
		$s = new Sites();
		$version = $s->version();
		$site = $s->get();
		$nzbsplitlevel = $site->nzbsplitlevel;
		$nzbpath = $site->nzbpath;
		$consoletools = new ConsoleTools();
		$n = "\n";
		$nzbcount = 0;
		$where = (!empty($groupID)) ? " AND groupID = " . $groupID : "";

		// Create NZB.
		if ($echooutput)
			echo $n."\033[1;33m[".date("H:i:s A")."] Stage 5 -> Create the NZB, mark collections as ready for deletion.\033[0m".$n;
		$stage5 = TIME();
		// $start_nzbcount = $nzbcount;  // Unused variable
		if($resrel = $db->queryDirect("SELECT ID, guid, name, categoryID FROM releases WHERE nzbstatus = 0 " . $where . " LIMIT ".$this->stage5limit))
		{
			$totalNZBTime = microtime(true);
            while ($rowrel = $db->fetchAssoc($resrel))
			{

                $nzbcount++;
                if ($echooutput)
                    $consoletools->overWrite("Creating NZBs: ".$consoletools->percentString($nzbcount,mysqli_num_rows($resrel))." Processing ReleaseID: ".$rowrel['ID']);
				$nzb_guid = $nzb->writeNZBforReleaseId($rowrel['ID'], $rowrel['guid'], $rowrel['name'], $rowrel['categoryID'], $nzb->getNZBPath($rowrel['guid'], $nzbpath, true, $nzbsplitlevel), false, $version, $cat);
				if($nzb_guid !== false)
				{
					$db->queryDirect(sprintf("UPDATE releases SET nzbstatus = 1, nzb_guid = %s, relnamestatus=0 WHERE ID = %d", $db->escapestring(md5($nzb_guid)), $rowrel['ID']));
					$db->queryDirect(sprintf("UPDATE collections SET filecheck = 5 WHERE releaseID = %s", $rowrel['ID']));
				}
                else
                {
                    // Something went wrong.  This will need to be checked by the site admin

                    $db->queryDirect("UPDATE collections SET filecheck=999 WHERE releaseID=".$rowrel['ID']);

                }
			}
            $totalNZBTime = microtime(true) - $totalNZBTime;

		}
        $remainingReleasesRes = $db->queryOneRow("SELECT COUNT(*) as relcount FROM releases WHERE nzbstatus=0");
		$timing = $consoletools->convertTime(TIME() - $stage5);
		if ($echooutput && $nzbcount > 0)
        {
			echo $n.$nzbcount." NZBs created in ".number_format($totalNZBTime,4).".\n";
            echo "Average NZB creation time: ".number_format(($totalNZBTime/$nzbcount),4);
            echo "\n".number_format($remainingReleasesRes['relcount'])." releases still need NZB's.\n";
        }
        else
			if ($echooutput)
				echo "No NZBs created. Stage completed in ". $timing.".\n".number_format($remainingReleasesRes['relcount'])." releases still need NZB's.\n";
		return $nzbcount;
	}

	public function processReleasesStage5_loop($groupID, $echooutput=false)
	{
		$tot_nzbcount = 0;
		do
		{
			$nzbcount = $this->processReleasesStage5($groupID);
			$tot_nzbcount = $tot_nzbcount + $nzbcount;
		} while ($nzbcount > 0);

		return $tot_nzbcount;
	}

	public function processReleasesStage5b($groupID, $echooutput=true)
	{
		$db = new DB();
		$page = new Page();
		$n = "\n";
		$consoletools = new consoleTools();
		$iFoundcnt = 0;

		$where = (!empty($groupID)) ? " AND groupID = ".$groupID : "";

		if ($page->site->lookup_reqids == 1)
		{
			$stage8 = TIME();
			if ($echooutput)
				echo $n."\033[1;33m[".date("H:i:s A")."] Stage 5b -> Request ID lookup.\033[0m\n";

			// Mark records that don't have regex titles
			$db->query( "UPDATE releases SET reqidstatus = -1 WHERE reqidstatus = 0 AND nzbstatus = 1 AND relnamestatus = 1 AND name REGEXP '^\\[[[:digit:]]+\\]' = 0 " . $where);

			// look for records that potentially have regex titles
			$resrel = $db->queryDirect( "SELECT r.ID, r.name, g.name groupName " .
										"FROM releases r LEFT JOIN groups g ON r.groupID = g.ID " .
										"WHERE relnamestatus = 1 AND nzbstatus = 1 AND reqidstatus = 0 AND r.name REGEXP '^\\[[[:digit:]]+\\]' = 1 " . $where);

			while ($rowrel = $db->fetchAssoc($resrel))
			{
				// Try to get reqid
				$requestIDtmp = explode("]", substr($rowrel['name'], 1));
				$bFound = false;
				$newTitle = "";

				if (count($requestIDtmp) >= 1)
				{
					$requestID = (int) $requestIDtmp[0];
					if ($requestID != 0)
					{
						$newTitle = $this->getReleaseNameFromRequestID($page->site, $requestID, $rowrel['groupName']);
						if ($newTitle != false && $newTitle != "")
						{
							$bFound = true;
							$iFoundcnt++;
						}
					}
				}

				if ($bFound)
				{
					$db->query("UPDATE releases SET reqidstatus = 1, searchname = " . $db->escapeString($newTitle) . " WHERE ID = " . $rowrel['ID']);

					if ($echooutput)
						echo $n."Updated requestID " . $requestID . " to release name: ".$newTitle.$n;
				}
				else
				{
					$db->query("UPDATE releases SET reqidstatus = -2 WHERE ID = " . $rowrel['ID']);
					//if ($echooutput)
						echo ".";
				}
			}

			$timing = $consoletools->convertTime(TIME() - $stage8);
			if ($echooutput)
				echo $iFoundcnt . " Releases updated in " . $timing . ".\n";
		}
	}

	public function processReleasesStage6($categorize, $postproc, $groupID, $echooutput=true)
	{
		$db = new DB();
		$consoletools = new ConsoleTools();
		$n = "\n";

		$where = (!empty($groupID)) ? "WHERE relnamestatus = 0 AND groupID = " . $groupID : "WHERE relnamestatus = 0";

		// Categorize releases.
		if ($echooutput)
			echo $n."\033[1;33m[".date("H:i:s A")."] Stage 6 -> Categorize and post process releases.\033[0m".$n;
		$stage6 = TIME();
		if ($categorize == 1)
		{
			$this->categorizeRelease("name", $where);
		}
		if ($postproc == 1)
		{
			$postprocess = new PostProcess(true);
			$postprocess->processAll();
		}
		else
		{
			if ($echooutput)
				echo "\nPost-processing is done in post window.".$n;
		}

        if ($echooutput)
			echo $consoletools->convertTime(TIME() - $stage6).".\n\n";

	}

	public function processReleasesStage7a($groupID, $echooutput=true, $maxcollections=1000)
	{
		$db = new DB();

		$consoletools = new ConsoleTools();
		$n = "\n";
		$remcount = $passcount = $passcount = $dupecount = $relsizecount = $completioncount = $disabledcount = $disabledgenrecount = $miscothercount = 0;

		$where = (!empty($groupID)) ? " AND collections.groupID = " . $groupID : "";
        $furiousPurge = $db->queryOneRow("SELECT VALUE as furious FROM tmux WHERE setting='FURIOUS_PURGE'");
        $fastAndFurious = $furiousPurge['furious'];

		// Delete old releases and finished collections.
		if ($echooutput)
			echo $n."\033[1;33m[".date("H:i:s A")."] Stage 7a -> Delete finished collections.\033[0m".$n;
		$stage7 = TIME();
        if ($fastAndFurious == 'TRUE' && $echooutput)
            echo "\n\033[00;33mFurious purging enabled.\n\n\033[00;37m";
		// Completed releases and old collections that were missed somehow.
		// $db->queryDirect(sprintf("DELETE collections, binaries, parts
		//				  FROM collections INNER JOIN binaries ON collections.ID = binaries.collectionID INNER JOIN parts on binaries.ID = parts.binaryID
		//				  WHERE collections.filecheck = 5 " . $where));
		// $reccount = $db->getAffectedRows();

        do
        {
            $loopTime = microtime(true);
            $colsDeleted = 0;
            $binsDeleted = 0;
            $partsDeleted = 0;
            // Using thread ID to mark the collections to delete will allow us to multi-thread this function in the future if desired.
            $threadID = $db->queryOneRow("SELECT connection_ID() as thread_ID");
            if($threadID['thread_ID']<1000)
                $threadID['thread_ID'] += 1000;
            // I suppose it's possible for the thread ID to grow to above 11 digits if
            // the system was up long enough.  Not sure how big Percona/MySql allows
            // the thread counter to grow.
            // TODO: Fix opp error below
            // if($threadID>99999999999)
            //    $threadID -= 9999999999;
            if ($echooutput)
                echo "Using thread ID ".$threadID['thread_ID']." to mark collections.\n";
            $db->query("UPDATE collections SET filecheck=".$threadID['thread_ID']." WHERE filecheck = 5 ".$where." ORDER BY dateadded ASC LIMIT ".$maxcollections);
            $completeCols = $db->queryDirect("SELECT ID, groupID FROM collections WHERE filecheck=".$threadID['thread_ID']);
            $colsToDelete = $db->getNumRows($completeCols);
            if($colsToDelete == 0 || $colsToDelete == false)
            {
                echo "\n No collections to purge right now.  Exiting stage.\n";
                break;
            }

            $db->setAutoCommit(false);
            while ($currentCol=$db->fetchAssoc($completeCols))
            {
                $colsDeleted++;
                if ($echooutput)
                    $consoletools->overWrite("Processing collection ".$consoletools->percentString($colsDeleted,$colsToDelete));

                $db->queryDirect("DELETE parts FROM parts WHERE collectionID=".$currentCol['ID']);
                $partsDeleted += $db->getAffectedRows();
                // $db->query("UPDATE groups SET partsInDB=partsInDB-".$partsDeleted." WHERE ID=".$currentCol['groupID']);

                $db->queryDirect("DELETE binaries FROM binaries WHERE collectionID=".$currentCol['ID']);
                $binsDeleted += $db->getAffectedRows();

            }
            $db->Commit();
            $db->queryDirect("DELETE collections FROM collections WHERE filecheck=".$threadID['thread_ID']);
            // $colsDeletedbyAffected = $db->getAffectedRows();
            $db->Commit();
            $db->setAutoCommit(true);
            $loopTime = microtime(true) - $loopTime;
            if ($colsDeleted>0)
                $avgDeleteTime = $loopTime/$colsDeleted;
            if ($echooutput)
            {
                echo "\n\033[00;36mTotal Objects Removed:\n";
                echo "Collections:  ".number_format($colsDeleted)."\n";
                // echo "Affected rows: ".number_format($colsDeletedbyAffected)."\n";
                echo "Binaries:     ".number_format($binsDeleted)."\n";
                echo "Parts:        ".number_format($partsDeleted)."\n\n";
                echo "Processing completed in ".number_format($loopTime, 2)." seconds.\n";
                echo "Average per collection: ".number_format($avgDeleteTime,4)." seconds\n\033[00;37m";
                // Adding sleep here to give MySQL time to purge changes
                sleep(5);
            }
        } while ($colsDeleted>0 && $fastAndFurious == 'TRUE');

        if(((time()>=$this->nextCrosspostCheck) || $this->nextCrosspostCheck==0)&& ($resrel = $db->query(sprintf("SELECT ID, guid FROM releases WHERE adddate > (now() - interval %d hour) GROUP BY name HAVING count(name) > 1", $this->crosspostt))))
        {
            if ($echooutput)
                echo "\nDeleting cross-posted releases...\n";
            $db->query("UPDATE site SET value=".(time()+3600)." WHERE setting='nextCrosspostCheck'");
            $dupecount = 0;
            foreach ($resrel as $rowrel)
            {
                $this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
                $dupecount ++;
            }
            if($echooutput)
                echo "\n".$dupecount." cross-posted releases deleted.\n";
        }
        // Following added to handle releases that need to be deleted due to purging groups
        // in the Admin section of the website.  See comments in groups.php->purge function.
        if ($echooutput)
            echo "\nDeleting releases marked for purge by Admin...\n";
        $rels = $db->query("SELECT ID FROM releases WHERE groupID=999999");
        foreach ($rels as $rel)
            $this->delete($rel["ID"]);

        If ($echooutput)
            echo "\nStage completed in ".$consoletools->convertTime(TIME() - $stage7).".\n";
	}

	public function processReleasesStage7b($groupID, $echooutput=true)
	{
		$db = new DB();
		$page = new Page();
        $site = new Sites();
		$category = new Category();
		$genres = new Genres();
		$consoletools = new ConsoleTools();
		$n = "\n";
		$remcount = $passcount = $passcount = $dupecount = $relsizecount = $completioncount = $disabledcount = $disabledgenrecount = $miscothercount = 0;
        $site->get();

		$where = (!empty($groupID)) ? " AND collections.groupID = " . $groupID : "";

		// Delete old releases and finished collections.
		if ($echooutput)
			echo $n."\033[1;33m[".date("H:i:s A")."] Beginning full purge process.\033[0m".$n;
		$stage7 = time();


        if($this->site->partretentionhours > 0)
        {
            if ($echooutput)
                echo "\nDeleting collections/binaries/parts that are past retention...\n";
            $colsDeleted = 0;
            $binsDeleted = 0;
            $partsDeleted = 0;

            // Using thread ID to mark the collections to delete will allow us to multi-thread this function in the future if desired.
            $threadID = $db->queryOneRow("SELECT connection_ID() as thread_ID");
            if($threadID['thread_ID']<1000)
                $threadID['thread_ID'] += 1000;
            if ($echooutput)
                echo "Using thread ID ".$threadID['thread_ID']." to mark collections.";
            $db->query("UPDATE collections SET filecheck=".$threadID['thread_ID']." WHERE filecheck IN (0,1) AND dateadded < (now() - interval ".$this->site->partretentionhours." hour)");
            $completeCols = $db->queryDirect("SELECT ID, groupID FROM collections WHERE filecheck=".$threadID['thread_ID']);
            $colsToDelete = $db->getNumRows($completeCols);
            if($colsToDelete != 0 && $colsToDelete != false)
            {
                $db->setAutoCommit(false);
                while ($currentCol=$db->fetchAssoc($completeCols))
                {
                    $colsDeleted++;
                    if ($echooutput)
                        $consoletools->overWrite("Processing collection ".$consoletools->percentString($colsDeleted,$colsToDelete));

                    $db->queryDirect("DELETE parts FROM parts WHERE collectionID=".$currentCol['ID']);
                    $partsDeleted += $db->getAffectedRows();
                    // $db->query("UPDATE groups SET partsInDB=partsInDB-".$partsDeleted." WHERE ID=".$currentCol['groupID']);

                    $db->queryDirect("DELETE binaries FROM binaries WHERE collectionID=".$currentCol['ID']);
                    $binsDeleted += $db->getAffectedRows();

                }
                $db->Commit();
                $db->queryDirect("DELETE collections FROM collections WHERE filecheck=".$threadID['thread_ID']);

                $db->Commit();
                $db->setAutoCommit(true);

                if ($echooutput)
                {
                    echo "\nTotal Objects Removed:\n";
                    echo "Collections:  ".number_format($colsDeleted)."\n";
                    // echo "Affected rows: ".number_format($colsDeletedbyAffected)."\n";
                    echo "Binaries:     ".number_format($binsDeleted)."\n";
                    echo "Parts:        ".number_format($partsDeleted)."\n\n";
                    echo "Stage completed in ".$consoletools->convertTime(time() - $stage7).".\n";
                }
            }
            else
                echo "\n No collections to purge right now.  Exiting stage.";

        }
		// Binaries/parts that somehow have no collection.
        if ($echooutput)
            echo "\nDeleting binaries and parts that have no collection...\n";
		$db->queryDirect("DELETE binaries, parts FROM binaries LEFT JOIN parts ON binaries.ID = parts.binaryID WHERE binaries.collectionID = 0 " . $where);

		// Parts that somehow have no binaries.
        if ($echooutput)
            echo "\nDeleting parts that have no binary...\n";
		$db->queryDirect("DELETE parts FROM parts LEFT OUTER JOIN binaries on parts.binaryID=binaries.ID WHERE binaries.ID IS NULL " . $where);

		// Binaries that somehow have no collection.
        if ($echooutput)
            echo "\nDeleting binaries that have no collection...\n";
		$db->queryDirect("DELETE binaries FROM `binaries` LEFT OUTER JOIN `collections` ON binaries.collectionID=collections.ID WHERE collections.ID IS NULL " . $where);

		// Collections that somehow have no binaries.
        if ($echooutput)
            echo "\nDeleting collections that have no binaries...\n";
		$db->queryDirect("DELETE collections FROM collections LEFT OUTER JOIN binaries ON collections.ID=binaries.collectionID WHERE binaries.collectionID IS NULL " . $where);

		$where = (!empty($groupID)) ? " AND groupID = " . $groupID : "";

		// Releases past retention.
        if ($echooutput)
            echo "\nDeleting releases past retention...\n";
		if($this->site->releaseretentiondays != 0)
		{
			$result = $db->query(sprintf("SELECT ID, guid FROM releases WHERE postdate < (now() - interval %d day)", $page->site->releaseretentiondays));
			foreach ($result as $rowrel)
			{
				$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
				$remcount ++;
			}
		}

        // Misc other past retention
        if ($this->site->miscotherretentionhours > 0)
        {
            if ($echooutput)
                echo "\nDeleting releases from Misc->Other that are past retention...\n";
            if ($this->noMiscPurgeBeforeFix == 'FALSE')
                $result = $db->queryDirect(sprintf("select ID, guid from releases where categoryID = %d AND adddate <= NOW() - INTERVAL %d HOUR", CATEGORY::CAT_MISC, $page->site->miscotherretentionhours));
            else
                $result = $db->queryDirect(sprintf("select ID, guid from releases where categoryID = %d AND adddate <= NOW() - INTERVAL %d HOUR AND (relstatus >= 4 OR passwordstatus > 2)", CATEGORY::CAT_MISC, $page->site->miscotherretentionhours));
            $resultCount = $db->getNumRows($result);
            if ($resultCount > 0) {
                while ($rowrel = $db->fetchAssoc($result)) {
                    $this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
                    $miscothercount++;
                }
            }

        }

        // Hashed releases past retention
        if($this->site->hashedRetentionHours != 0)
        {
            if ($echooutput)
                echo "\nDeleting hashed releases past retention...\n";
            if($this->noMiscPurgeBeforeFix == 'FALSE')
                $result = $db->queryDirect(sprintf("SELECT ID, guid FROM releases WHERE categoryID = %d AND adddate < (now() - interval %d hour)", CATEGORY::CAT_HASHED, $page->site->hashedRetentionHours));
            else
                $result = $db->queryDirect(sprintf("SELECT ID, guid FROM releases WHERE categoryID = %d AND adddate < (now() - interval %d hour) AND (relstatus >= 4 OR passwordstatus > 2) ", CATEGORY::CAT_HASHED, $page->site->hashedRetentionHours));
            $resultCount = $db->getNumRows($result);
            if($resultCount > 0)
            {
                while ($rowrel = $db->fetchAssoc($result))
                {
                    $this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
                    $remcount ++;
                }
            }
        }
		// Passworded releases.
        if ($echooutput)
            echo "\nDeleting passworded releases...\n";
		if($this->site->deletepasswordedrelease == 1)
		{
			$result = $db->query("SELECT ID, guid FROM releases WHERE passwordstatus = ".Releases::PASSWD_RAR);
			foreach ($result as $rowrel)
			{
				$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
				$passcount ++;
			}
		}

		// Possibly passworded releases.
        if ($echooutput)
            echo "\nDeleting possible passworded releases...\n";
		if($this->site->deletepossiblerelease == 1)
		{
			$result = $db->query("SELECT ID, guid FROM releases WHERE passwordstatus = ".Releases::PASSWD_POTENTIAL);
			foreach ($result as $rowrel)
			{
				$this->fastDelete($rowrel['ID'], $rowrel['guid'], $this->site);
				$passcount ++;
			}
		}


        // $completioncount = $this->removePartialReleases($db, $completioncount);

        // Disabled categories.
		if ($catlist = $category->getDisabledIDs())
		{
			while ($cat = mysqli_fetch_assoc($catlist))
			{
				if ($rels = $db->query(sprintf("select ID, guid from releases where categoryID = %d", $cat['ID'])))
				{
					foreach ($rels as $rel)
					{
						$disabledcount++;
						$this->fastDelete($rel['ID'], $rel['guid'], $this->site);
					}
				}
			}
		}

		// Disabled music genres.
        if ($echooutput)
            echo "\nDeleting music from disabled genres...\n";
        $genrelist = $genres->getDisabledIDs();
		if ($genrelist)
		{

            foreach ($genrelist as $genre)
			{
				$rels = $db->query(sprintf("select ID, guid from releases inner join (select ID as mid from musicinfo where musicinfo.genreID = %d) mi on releases.musicinfoID = mid", $genre['ID']));
				foreach ($rels as $rel)
				{
					$disabledgenrecount++;
					$this->fastDelete($rel['ID'], $rel['guid'], $this->site);
				}
			}
		}




		// $db->queryDirect(sprintf("DELETE nzbs WHERE dateadded < (now() - interval %d hour)", $page->site->partretentionhours));

		echo "Releases removed: \n"."Past retention: ".number_format($remcount)."\nPassworded: ".number_format($passcount)."\nFrom disabled categoteries: ".number_format($disabledcount);
        echo "\nFrom disabled music genres: ".number_format($disabledgenrecount)."\nFrom Misc->Other".number_format($miscothercount);
		if ($echooutput && $this->completion > 0)
			echo "\nUnder ".$this->completion."% completion: ".number_format($completioncount)."\n";
		/*else
			if ($echooutput)
				echo ". \nRemoved ".number_format($reccount)." parts/binaries/collection rows.".$n;*/

		if ($echooutput)
			echo $consoletools->convertTime(TIME() - $stage7).".".$n;
	}

	public function processReleasesStage4567_loop($categorize, $postproc, $groupID, $echooutput=true)
	{
		$DIR = MISC_DIR;
		if ($this->command_exist("python3"))
			$PYTHON = "python3 -OO";
		else
			$PYTHON = "python -OO";
        $consoletools = new ConsoleTools();

		$tot_retcount = 0;
		$tot_nzbcount = 0;
		$loops = 0;
		do
		{
			$retcount = $this->processReleasesStage4($groupID);
			$tot_retcount = $tot_retcount + $retcount;
			// Removing stage 4.5 from the loop as I don't think it's necessary since
            // anything too big or too small should have been caught at stage 3
			// $this->processReleasesStage4dot5($groupID, $echooutput=false);
			$nzbcount = $this->processReleasesStage5($groupID);
			if ($this->requestids == "1")
			{
				$this->processReleasesStage5b($groupID, $echooutput);
			}
			elseif ($this->requestids == "2")
			{
				$consoletools = new ConsoleTools();
				$stage8 = TIME();
				if ($echooutput)
					echo "\n\033[1;33mStage 5b -> Request ID Threaded lookup.\033[0m\n";
				passthru("$PYTHON ${DIR}update_scripts/threaded_scripts/requestid_threaded.py");
				$timing = $consoletools->convertTime(TIME() - $stage8);
				if ($echooutput)
					echo "\nReleases updated in " . $timing . ".";

			}
			$tot_nzbcount = $tot_nzbcount + $nzbcount;
			$this->processReleasesStage6($categorize, $postproc, $groupID, $echooutput=true);
			// Stage 7a and b now get called by the purge_thread script

			$loops++;

            // Putting in a countdown delay here so that it's possible to shut down the thread before it loops
            if ($nzbcount>0)
            {
                $sleepCounter = 5;
                while($sleepCounter > 0)
                {

                    $consoletools->overWrite("Sleeping for ".$sleepCounter." more seconds...");
                    sleep(1);
                    $sleepCounter --;

                }
            }
		//this loops as long as there were releases created or 3 loops, otherwise, you could loop indefinately
        // Well... the above comment was not from me.  Now that I'm looking at this, I'm either reading the
        // above comment incorrectly, and misunderstanding the intention, or the original logic in the statement
        // below was wrong.  Basically, it will loop 'indefinitely' as long as releases or NZBs are being created.
        // I kind of don't like that logic, so I changed it.  Theorhetically, if there were enough collections that
        // need to be processed, you could end up backlogging collections waiting to be moved to stage 2 and 3.
        // Eventually, those collections could get purged due to part retention time.
		} while (($nzbcount > 0 || $retcount > 0) && $loops < 3);

		return $tot_retcount;
	}

	public function processReleases($categorize, $postproc, $groupName, $echooutput=true)
	{
		// $echooutput = $echooutput;
		if ($this->hashcheck == 0)
			exit("You must run update_binaries.php to update your collectionhash.\n");
		$db = new DB();
		$groups = new Groups();
		$page = new Page();
		$consoletools = new ConsoleTools();
		$n = "\n";
		$groupID = "";

		if (!empty($groupName))
		{
			$groupInfo = $groups->getByName($groupName);
			$groupID = $groupInfo['ID'];
		}

		$this->processReleases = microtime(true);
		if ($echooutput)
			echo $n."Starting release update process (".date("Y-m-d H:i:s").")".$n;

		if (!file_exists($page->site->nzbpath))
		{
			if ($echooutput)
				echo "Bad or missing nzb directory - ".$page->site->nzbpath;
			return FALSE;
		}

		$this->processReleasesStage1($groupID, $echooutput=true);
		$anyCollections=$this->processReleasesStage2($groupID, $echooutput=true);
        if(!$anyCollections)
        {
            Echo "\nNo collections to process currently.\n";
           $releasesAdded=0;
        }
        Else
        {
		$this->processReleasesStage3($groupID, $echooutput=true);
		$releasesAdded = $this->processReleasesStage4567_loop($categorize, $postproc, $groupID, $echooutput=true);
        }
        // Stages 7a and 7b now get called from the purge_thread script

		//Print amount of added releases and time it took.

		$timeUpdate = $consoletools->convertTime(number_format(microtime(true) - $this->processReleases, 2));

		$where = (!empty($groupID)) ? " AND groupID = " . $groupID : "";

		$cremain = $db->queryOneRow("select count(ID) from collections WHERE filecheck != 5" . $where);
		if ($echooutput)
			echo "\nCompleted adding ".number_format($releasesAdded)." releases in ".$timeUpdate.".\n".number_format(array_shift($cremain))." collections waiting to be created\n (still incomplete or in queue for creation).".$n;
		return $releasesAdded;
	}

    public function checkDeadCollections($maxAge = 6, $echooutput = true)
    {
        $db = new DB();

        $consoletools = new ConsoleTools();
        $n = "\n";

        // Delete old releases and finished collections.
        if ($echooutput)
            echo $n."\033[1;33m[".date("H:i:s A")."] Checking For Stale Collections.\033[0m".$n;
        $stageStart = time();
        if($maxAge<1)
            $maxAge=6;
        $loopTime = microtime(true);
        $colsDeleted = 0;
        $binsDeleted = 0;
        $partsDeleted = 0;
        $colsQueued = 0;
        // Using thread ID to mark the collections to delete will allow us to multi-thread this function in the future if desired.
        $threadID = $db->queryOneRow("SELECT connection_ID() as thread_ID");
        if($threadID['thread_ID']<1000)
            $threadID['thread_ID'] += 1000;
        // I suppose it's possible for the thread ID to grow to above 11 digits if
        // the system was up long enough.  Not sure how big Percona/MySql allows
        // the thread counter to grow.
        // TODO: Fix opp error below
        // if($threadID>99999999999)
        //    $threadID -= 9999999999;
        if ($echooutput)
            echo "Using thread ID ".$threadID['thread_ID']." to mark collections.\n";
        $db->query("UPDATE collections AS c LEFT JOIN groups AS g ON c.groupID = g.ID SET c.filecheck=".$threadID['thread_ID']." WHERE (c.oldestBinary > g.first_record_postdate + INTERVAL ".$maxAge." HOUR) AND (c.newestBinary < g.last_record_postdate - INTERVAL ".$maxAge." HOUR)");
        $completeCols = $db->queryDirect("SELECT ID, groupID, totalFiles FROM collections WHERE filecheck=".$threadID['thread_ID']);
        $colsTotal = $db->getNumRows($completeCols);
        if($colsTotal == 0 || $colsTotal == false)
        {
            echo "\n No stale collections to remove right now.  Exiting stage.\n";
            return false;
        }
        $colsChecked = 0;
        $db->setAutoCommit(false);
        while ($currentCol=$db->fetchAssoc($completeCols))
        {
            $colsChecked++;
            if ($echooutput)
                $consoletools->overWrite("Processing collection ".$consoletools->percentString($colsChecked,$colsTotal));
            $compQuery = $db->queryOneRow("SELECT SUM(totalParts) AS total, SUM(partsInDB) AS partsDownloaded, COUNT(*) AS totalBinaries FROM binaries WHERE collectionID=".$currentCol['ID']);
            if(($compQuery['totalBinaries']/$currentCol['totalFiles'])*100 > $this->completion && ($compQuery['partsDownloaded']/$compQuery['total'])*100 > $this->completion)
            {
                $db->query("UPDATE collections SET filecheck=25 WHERE ID=".$currentCol['ID']);
                $colsQueued++;
            }
            else
            {
                $colsDeleted++;
                $db->queryDirect("DELETE parts FROM parts WHERE collectionID=".$currentCol['ID']);
                $partsDeleted += $db->getAffectedRows();
                // $db->query("UPDATE groups SET partsInDB=partsInDB-".$partsDeleted." WHERE ID=".$currentCol['groupID']);

                $db->queryDirect("DELETE binaries FROM binaries WHERE collectionID=".$currentCol['ID']);
                $binsDeleted += $db->getAffectedRows();
            }
        }
        $db->Commit();

        $db->queryDirect("DELETE collections FROM collections WHERE filecheck=".$threadID['thread_ID']);

        $db->Commit();
        $db->setAutoCommit(true);
        $loopTime = microtime(true) - $loopTime;
        if ($colsDeleted>0)
            $avgDeleteTime = $loopTime/$colsDeleted;
        else
            $avgDeleteTime = 0;
        if ($echooutput)
        {
            echo "\n\n\033[00;36mCollections queued to be released: ".$colsQueued."\n";
            echo "\nTotal Objects Removed:\n";
            echo "Collections:  ".number_format($colsDeleted)."\n";
            // echo "Affected rows: ".number_format($colsDeletedbyAffected)."\n";
            echo "Binaries:     ".number_format($binsDeleted)."\n";
            echo "Parts:        ".number_format($partsDeleted)."\n\n";
            echo "Processing completed in ".number_format($loopTime, 2)." seconds.\n";
            echo "Average per collection: ".number_format($avgDeleteTime,4)." seconds\n\033[00;37m";
            // Adding sleep here to give MySQL time to purge changes to binlogs
            sleep(5);
        }

        If ($echooutput)
            echo "\nStage completed in ".$consoletools->convertTime(time() - $stageStart).".\n";
        return true;
    }

    // This resets collections, useful when the namecleaning class's collectioncleaner function changes.
	public function resetCollections()
	{
		$db = new DB();
		$namecleaner = new nameCleaning();
		$consoletools = new ConsoleTools();
		if($res = $db->queryDirect("SELECT b.ID as bID, b.name as bname, c.* FROM binaries b LEFT JOIN collections c ON b.collectionID = c.ID"))
		{
			if (mysqli_num_rows($res) > 0)
			{
				$timestart = TIME();
				if ($echooutput)
					echo "Going to remake all the collections. This can be a long process, be patient. DO NOT STOP THIS SCRIPT!\n";
				// Reset the collectionhash.
				$db->query("UPDATE collections SET collectionhash = 0");
				$delcount = 0;
				$cIDS = array();
				while ($row = mysqli_fetch_assoc($res))
				{
					$nofiles = true;
					if ($row['totalFiles'] > 0)
						$nofiles = false;
					$newSHA1 = sha1($namecleaner->collectionsCleaner($row['bname'], $row['groupID'], $nofiles).$row['fromname'].$row['groupID'].$row['totalFiles']);
					$cres = $db->queryOneRow(sprintf("SELECT ID FROM collections WHERE collectionhash = %s", $db->escapeString($newSHA1)));
					if(!$cres)
					{
						$cIDS[] = $row['ID'];
						$csql = sprintf("INSERT IGNORE INTO collections (name, subject, fromname, date, xref, groupID, totalFiles, collectionhash, filecheck, dateadded) VALUES (%s, %s, %s, %s, %s, %d, %s, %s, 0, now())", $db->escapeString($namecleaner->releaseCleaner($row['bname'], $row['groupID'])), $db->escapeString($row['bname']), $db->escapeString($row['fromname']), $db->escapeString($row['date']), $db->escapeString($row['xref']), $row['groupID'], $db->escapeString($row['totalFiles']), $db->escapeString($newSHA1));
						$collectionID = $db->queryInsert($csql);
						$consoletools->overWrite("Recreated: ".count($cIDS)." collections. Time:".$consoletools->convertTimer(TIME() - $timestart));
					}
					else
						$collectionID = $cres['ID'];
					//Update the binaries with the new info.
					$db->query(sprintf("UPDATE binaries SET collectionID = %d where ID = %d", $collectionID, $row['bID']));
				}
				//Remove the old collections.
				$delstart = TIME();
				if ($echooutput)
					echo "\n";
				foreach ($cIDS as $cID)
				{
					$db->query(sprintf("DELETE FROM collections WHERE ID = %d", $cID));
					$delcount++;
					$consoletools->overWrite("Deleting old collections:".$consoletools->percentString($delcount,sizeof($cIDS))." Time:".$consoletools->convertTimer(TIME() - $delstart));
				}
				// Delete previous failed attempts.
				$db->query('DELETE FROM collections where collectionhash = "0"');

				if ($this->hashcheck == 0)
					$db->query('UPDATE site SET value = "1" where setting = "hashcheck"');
				if ($echooutput)
					echo "\nRemade ".count($cIDS)." collections in ".$consoletools->convertTime(TIME() - $timestart);
			}
			else
				$db->query('UPDATE site SET value = "1" where setting = "hashcheck"');
		}
	}

	public function getTopDownloads()
	{
		$db = new DB();
		return $db->query("SELECT ID, searchname, guid, adddate, SUM(grabs) as grabs FROM releases
							GROUP BY ID, searchname, adddate
							HAVING SUM(grabs) > 0
							ORDER BY grabs DESC
							LIMIT 10");
	}

	public function getTopComments()
	{
		$db = new DB();
		return $db->query("SELECT ID, guid, searchname, adddate, SUM(comments) as comments FROM releases
							GROUP BY ID, searchname, adddate
							HAVING SUM(comments) > 0
							ORDER BY comments DESC
							LIMIT 10");
	}

	public function getRecentlyAdded()
	{
		$db = new DB();
		return $db->query("SELECT concat(cp.title, ' > ', category.title) as title, COUNT(*) AS count
							FROM category
							left outer join category cp on cp.ID = category.parentID
							INNER JOIN releases ON releases.categoryID = category.ID
							WHERE releases.adddate > NOW() - INTERVAL 1 WEEK
							GROUP BY concat(cp.title, ' > ', category.title)
							ORDER BY COUNT(*) DESC");
	}

	public function getReleaseNameFromRequestID($site, $requestID, $groupName)
	{
		if ($site->request_url == "")
			return "";

		// Build Request URL
		$req_url = str_ireplace("[GROUP_NM]", urlencode($groupName), $site->request_url);
		$req_url = str_ireplace("[REQUEST_ID]", urlencode($requestID), $req_url);

		$xml = simplexml_load_file($req_url);

		if (($xml == false) || (count($xml) == 0))
			return "";

		$request = $xml->request[0];

		return (!isset($request) || !isset($request["name"])) ? "" : $request['name'];
	}

	public function command_exist($cmd)
	{
		$returnVal = shell_exec("which $cmd 2>/dev/null");
		return (empty($returnVal) ? false : true);
	}

    /**
     * @param string $echoOutput
     *
     * @return void
     */
    public function removeIncompleteReleases($echoOutput)
    {
        // Releases below completion %.
        $completionCount = 1;
        $db = new DB();
        $consoleTools = new ConsoleTools();
        if ($this->completion > 1) {
            if ($echoOutput)
                echo "\nDeleting releases less than " . $this->completion . "% completed...\n";
            $resReleases = $db->queryDirect(sprintf("SELECT ID, guid FROM releases WHERE completion < %d and completion > 0", $this->completion));
            $releaseCount = $db->getNumRows($resReleases);
            if ($releaseCount>0)
            {
                while($releaseRow=$db->fetchAssoc($resReleases))
                {
                    $consoleTools->overWrite("Deleting releases ".$consoleTools->percentString($completionCount,$releaseCount));
                    $this->fastDelete($releaseRow['ID'], $releaseRow['guid'], $this->site);
                    $completionCount++;
                }

            if($echoOutput)
                echo "\nDeleted ".$completionCount." incomplete releases.";
            }
        }
        return;
    }

}
