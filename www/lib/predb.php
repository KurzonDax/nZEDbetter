<?php
require_once(WWW_DIR."lib/framework/db.php");
require_once(WWW_DIR."lib/category.php");
require_once(WWW_DIR."lib/groups.php");
require_once(WWW_DIR."lib/nfo.php");
require_once(WWW_DIR."lib/site.php");
require_once(WWW_DIR."lib/simple_html_dom.php");

/*
 * Class for inserting names/categories/md5 etc from predb sources into the DB, also for matching names on files / subjects.
 */

Class Predb
{
	function Predb($echooutput=false)
	{
		$s = new Sites();
		$this->site = $s->get();
		$this->echooutput = $echooutput;
	}

	// Retrieve pre info from predb sources and store them in the DB.
	// Returns the quantity of new titles retrieved.
	public function combinePre()
	{
		$db = new DB();
		$newnames = 0;
		$newestrel = $db->queryOneRow("SELECT adddate, ID FROM predb ORDER BY adddate DESC LIMIT 1");
		if (strtotime($newestrel["adddate"]) < time()-300)
		{
			if ($this->echooutput)
				echo "Retrieving titles from preDB sources.\n";

            $newprelist = $this->retrievePrelist();
			$newzenet = $this->retrieveZenet();
            $neworly = $this->retrieveOrlydb();
            $newpdme = $this->retrievePredbme();
            $newsrr = $this->retrieveSrr();
			$newwomble = $this->retrieveWomble();
            $newomgwtf = $this->retrieveOmgwtfnzbs();
			$newnames = $newwomble+$newomgwtf+$newzenet+$newprelist+$neworly+$newsrr+$newpdme;
			if ($newnames == 0)
				$db->query(sprintf("UPDATE predb SET adddate = now() where ID = %d", $newestrel["ID"]));
		}
		$matched = $this->matchPredb();
		if ($matched > 0 && $this->echooutput)
			echo "\nMatched ".$matched." predDB titles to release search names.\n";
		$nfos = $this->matchNfo();
		if ($nfos > 0 && $this->echooutput)
			echo "\nAdded ".$nfos." missing NFOs from preDB sources.\n";
		return $newnames;
	}

	public function retrieveWomble()
	{
		$db = new DB();
		$newnames = 0;
        $skipped = 0;
        $updated = 0;

        $html = str_get_html($this->getWebPage("http://www.newshost.co.za"));
        $releases = $html->find("tr[bgcolor=#ffffff]");

        foreach ($releases as $post)
        {
            $pieces = $post->find('td');
            $preDate = strtotime(trim($pieces[0]->innertext));
            $size = preg_replace('/&nbsp;/', ' ', $pieces[1]->innertext);
            $categoryPrime = $pieces[2]->innertext;
            $a = $pieces[3]->find('a');
            $nfo = isset($a[1]) ? "http://nzb.isasecret.com/" . $a[1]->href : '';
            $title = trim($pieces[5]->innertext);

            $oldname = $db->queryOneRow(sprintf("SELECT title, source, ID FROM predb WHERE title = %s", $db->escapeString($title)));
            if ($oldname["title"] == $title) {
                if ($oldname["source"] == "womble")
                {
                    $skipped++;
                    continue;
                }
                else
                {
                    // $this->_insertPreDB($db, $title, $preDate, 'womble', $size, $categoryPrime, $nfo);
                    $updated ++;
                    $db->query("UPDATE predb SET nfo = " . $db->escapeString($nfo) . ", size = " . $db->escapeString($size) .
                        ", category = " . $categoryPrime . ", predate = " . $preDate . ", adddate = now(), source = 'womble' " .
                        " where ID = " . $oldname['ID']);
                }
            }
            else
            {
                $this->_insertPreDB($db, $title, $preDate, 'womble', $size, $categoryPrime, $nfo);

                // $db->query(sprintf("INSERT IGNORE INTO predb (title, nfo, size, category, predate, adddate, source, md5) VALUES (%s, %s, %s, %s, FROM_UNIXTIME(" . strtotime($matches2["date"]) . "), now(), %s, %s)", $db->escapeString($matches2["title"]), $nfo, $size, $db->escapeString($matches2["category"]), $db->escapeString("womble"), $db->escapeString(md5($matches2["title"]))));
                $newnames++;
            }
        }
        echo "Womble: " . $newnames . " Added, " . $updated . " Updated, " . $skipped . " Skipped\n";
		return $newnames;
	}

	public function retrieveOmgwtfnzbs()
	{
		$db = new DB();
		$newnames = 0;
        $skipped = 0;
        $xml = simplexml_load_file("http://rss.omgwtfnzbs.org/rss-info.php");

        foreach ($xml->channel->item as $item)
        {
            $title = trim(str_replace(' - omgwtfnzbs.org', '', $item->title));
            $preDate = strtotime(trim($item->pubDate));
            preg_match('/<b>Category:<\/b> *([\w\:\s\-]+)<br \/><b>Size:<\/b> *([\d\. ]+[GM]B)<br \/>/', $item->description, $match);
            $categoryPrime = isset($match[1]) ? $match[1] : 'NULL';
            $size = isset($match[2]) ? $match[2] : 'NULL';
            $oldname = $db->queryOneRow(sprintf("SELECT title FROM predb WHERE title = %s", $db->escapeString($title)));
            if ($oldname["title"] == $title)
            {
                $skipped ++;
                continue;
            }
            else
            {
                $this->_insertPreDB($db, $title, $preDate, 'omgwtfnzbs', $size, $categoryPrime);
                $newnames++;
            }
        }
        echo "Omgwtfnzbs: ".$newnames." Added, ".$skipped." Skipped\n";
        return $newnames;
	}

	public function retrieveZenet()
	{
		$db = new DB();
		$newnames = 0;
        $skipped = 0;
        $html = str_get_html($this->getWebPage("http://pre.zenet.org/live.php"));

        foreach ($html->find('div[class="mini-layout"]') as $release)
        {
            $divs = $release->find('div');
            preg_match('/20\d\d-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}/', $divs[0]->innertext, $match);
            $preDate = strtotime($match[0]);
            preg_match('/<b><font color="#[A-Fa-f0-9]+">(.+)<\/font><\/b>/', $divs[1]->innertext, $match);
            $title = trim($match[1]);
            preg_match('/<a href="\?cats=(.+?)"><font color/', $divs[1]->innertext, $match);
            $categoryPrime = isset($match[1]) ? $match[1] : 'NULL';
            preg_match('/\|\s*([\d\.]+[GM]B)\s*\/\s*(\d+ Files)\s*\|/', $divs[1]->innertext, $match);
            $size = isset($match[1]) ? $match[1] : 'NULL';
            $oldname = $db->queryOneRow(sprintf("SELECT title FROM predb WHERE title = %s", $db->escapeString($title)));
            if ($oldname["title"] == $title)
            {
                $skipped++;
                continue;
            }
            else
            {
                $this->_insertPreDB($db, $title, $preDate, 'zenet', $size, $categoryPrime);
                $newnames++;
            }
        }
        echo "Zenet: " . $newnames . " Added, " . $skipped . " Skipped\n";
		return $newnames;
	}

	public function retrievePrelist()
	{
		$db = new DB();
		$newnames = 0;
        $skipped = 0;

        $html = str_get_html($this->getWebPage("http://www.prelist.ws/"));
        $releases = $html->find('span[class="nobreak"]');

        foreach ($releases as $post) {

            preg_match('/\[ (.+) UTC \]/', $post->innertext, $match);
            $preDate = strtotime(trim($match[1]));
            $e = $post->find('a');
            $categoryPrime = $e[0]->innertext;
            $title = trim($e[1]->innertext);
            $e = $post->find('b');
            preg_match('/\[ *([\d\.]+MB) *\]/', $e[0]->innertext, $match);
            $size = isset($match[1]) ? $match[1] : 'NULL';
            if ($categoryPrime != 'NUKED') {
                $oldname = $db->queryOneRow(sprintf("SELECT title FROM predb WHERE title = %s", $db->escapeString($title)));
                if ($oldname["title"] == $title)
                {
                    $skipped++;
                    continue;
                }
                else
                {
                    $this->_insertPreDB($db, $title, $preDate, 'prelist', $size, $categoryPrime);
                    $newnames++;
                }
            }
        }
        echo "Prelist: " . $newnames . " Added, " . $skipped . " Skipped\n";
		return $newnames;
	}

	public function retrieveOrlydb()
	{
		$db = new DB();
		$newnames = 0;
        $skipped = 0;
        $html = str_get_html($this->getWebPage("http://www.orlydb.com/"));
        $releases = $html->find('div[id="releases"]', 0);

        foreach ($releases->find('div[!id]') as $post)
        {
            $e = $post->find('span[class="timestamp"]');
            $preDate = strtotime(trim($e[0]->innertext));
            $e = $post->find('span[class="section"] a');
            $categoryPrime = trim($e[0]->innertext);
            $e = $post->find('span[class="release"]');
            $title = trim($e[0]->innertext);
            $e = $post->find('span[class="info"]');
            preg_match('/[\d\.]+ ?MB/', $e[0]->innertext, $sizeMatch);

            $oldname = $db->queryOneRow(sprintf("SELECT title FROM predb WHERE title = %s", $db->escapeString($title)));
            if ($oldname["title"] == $title)
            {
                $skipped++;
                continue;
            }
            else
            {
                $this->_insertPreDB($db, $title, $preDate, 'orlydb', 'NULL', $categoryPrime);
                $newnames++;
            }
        }
        echo "Orlydb: " . $newnames . " Added, " . $skipped . " Skipped\n";
		return $newnames;
	}

	public function retrieveSrr()
	{
		$db = new DB();
		$newnames = 0;
        $skipped = 0;

		$releases = @simplexml_load_file('http://www.srrdb.com/feed/srrs');
		if ($releases !== false)
		{
			foreach ($releases->channel->item as $release)
			{
				$oldname = $db->queryOneRow(sprintf("SELECT title FROM predb WHERE title = %s", $db->escapeString($release->title)));
				if ($oldname["title"] == $release->title)
                {
                    $skipped++;
                    continue;
                }
				else
				{
                    $this->_insertPreDB($db, trim($release->title), strtotime($release->pubDate), 'srrdb');
					$newnames++;
				}
			}
		}
        echo "SrrDB: " . $newnames . " Added, " . $skipped . " Skipped\n";
		return $newnames;
	}

	public function retrievePredbme()
	{
		$db = new DB();
		$newnames = 0;
        $skipped = 0;

        $html = str_get_html($this->getWebPage("http://predb.me"));
        // $html = file_get_html("http://predb.me/");
        foreach ($html->find('div[class="post"]') as $post)
        {
            $e = $post->find('span[class="p-time"]');
            $preDate = strtotime(trim($e[0]->data));
            $e = $post->find('a[class="c-adult"]');
            $categoryPrime = $e[0]->innertext;
            $e = $post->find('a[class="c-child"]');
            $categorySub = $e[0]->innertext;
            $e = $post->find('a[class="p-title"]');
            $title = trim($e[0]->innertext);
            $oldname = $db->queryOneRow(sprintf("SELECT title FROM predb WHERE title = %s", $db->escapeString($title)));
            if ($oldname["title"] == $title)
            {
                $skipped++;
                continue;
            }
            else
            {
                $this->_insertPreDB($db, $title, $preDate, 'predbme', 'NULL', $categoryPrime . "-" . $categorySub);
                $newnames++;
            }

        }
        echo "Predbme: " . $newnames . " Added, " . $skipped . " Skipped\n";
		return $newnames;
	}

	//update a single release as its created
	public function matchPre($cleanerName, $releaseID)
	{
		$db = new DB();
		if($db->query(sprintf("update releaseID = %d from predb where name = %s and releaseID = null", $releaseID, $db->escapeString($cleanerName))))
			$db->query(sprintf("update releases set relnamestatus = 6 ID = %d", $releaseID));
	}

	// When a searchname is the same as the title, tie it to the predb.
	public function matchPredb()
	{
		$db = new DB();
		$updated = 0;
		if($this->echooutput)
			echo "Matching up predb titles with release search names.\n";

		//do womble first
		if($res = $db->queryDirect("SELECT p.ID, p.category, r.ID as releaseID from predb p inner join releases r on p.title = r.searchname where p.releaseID is null and p.source = 'womble'"))
		{
			while ($row = mysqli_fetch_assoc($res))
			{
				$db->query(sprintf("UPDATE predb SET releaseID = %d where ID = %d", $row["releaseID"], $row["ID"]));
				$catName=str_replace("TV-", '', $row["category"]);
				$catName=str_replace("TV: ", '', $catName);
				if($catID = $db->queryOneRow(sprintf("select ID from category where title = %s", $db->escapeString($catName))))
				{
					//print($row["category"]." - ".$catID["ID"]."\n");
					$db->query(sprintf("UPDATE releases set categoryID = %d where ID = %d", $db->escapeString($catID["ID"]), $db->escapeString($row["ID"])));
				}
				echo ".";
				$updated++;
			}
			return $updated;
		}
		elseif($res = $db->queryDirect("SELECT p.ID, p.category, r.ID as releaseID from predb p inner join releases r on p.title = r.searchname where p.releaseID is null"))
		{
			while ($row = mysqli_fetch_assoc($res))
			{
				$db->query(sprintf("UPDATE predb SET releaseID = %d where ID = %d", $row["releaseID"], $row["ID"]));
				$catName=str_replace("TV-", '', $row["category"]);
				$catName=str_replace("TV: ", '', $catName);
				if($catID = $db->queryOneRow(sprintf("select ID from category where title = %s", $db->escapeString($catName))))
				{
					//print($row["category"]." - ".$catID["ID"]."\n");
					$db->query(sprintf("UPDATE releases set categoryID = %d where ID = %d", $db->escapeString($catID["ID"]), $db->escapeString($row["ID"])));
				}
				echo ".";
				$updated++;
			}
			return $updated;
		}
		elseif($res = $db->queryDirect("SELECT p.ID, r.ID as releaseID from predb p inner join releases r on p.title = r.name where p.releaseID is null"))
		{
			while ($row = mysqli_fetch_assoc($res))
			{
				$db->query(sprintf("UPDATE predb SET releaseID = %d where ID = %d", $row["releaseID"], $row["ID"]));
				$db->query(sprintf("UPDATE releases SET relnamestatus = 6 where ID = %d", $row["releaseID"]));
				echo ".";
				$updated++;
			}
			return $updated;
		}

	}

	// Look if the release is missing an nfo.
	public function matchNfo()
	{
		$db = new DB();
		$nfos = 0;
		if($this->echooutput)
			echo "Matching up predb NFOs with releases missing an NFO.\n";

		if($res = $db->queryDirect("SELECT r.ID, p.nfo from releases r inner join predb p on r.ID = p.releaseID where p.nfo is not null and r.nfostatus != 1 limit 100"))
		{
			$nfo = new Nfo($this->echooutput);
			while ($row = mysqli_fetch_assoc($res))
			{
				$buffer = getUrl($row["nfo"]);
				if ($buffer !== false && strlen($buffer))
				{
					$nfo->addReleaseNfo($row["ID"]);
					$db->query(sprintf("UPDATE releasenfo SET nfo = compress(%s) WHERE releaseID = %d", $db->escapeString($buffer), $row["ID"]));
					$db->query(sprintf("UPDATE releases SET nfostatus = 1 WHERE ID = %d", $row["ID"]));
					echo ".";
					$nfos++;
				}
			}
			return $nfos;
		}
	}

	// Matches the names within the predb table to release files and subjects (names). In the future, use the MD5.
	public function parseTitles($time, $echo, $cats, $namestatus, $md5="")
	{
		$db = new DB();
		$updated = 0;

		/*if($backfill = "" && $this->echooutput)
		{
			$te = "";
			if ($time == 1)
				$te = " in the past 3 hours";
			echo "Fixing search names".$te." using the predb titles.\n";
		}*/

		$tq = "";
		if ($time == 1)
			$tq = " and r.adddate > (now() - interval 3 hour)";
		$ct = "";
		if ($cats == 1)
			$ct = " and r.categoryID in (1090, 2020, 3050, 6050, 5050, 7010, 7020, 8050)";

		/*if($backfill = "" && $res = $db->queryDirect("SELECT r.searchname, r.categoryID, r.groupID, p.source, p.title, r.ID from releases r left join releasefiles rf on rf.releaseID = r.ID, predb p where (r.name like concat('%', p.title, '%') or rf.name like concat('%', p.title, '%')) and r.relnamestatus = 1".$tq.$ct))
		{
			while ($row = mysqli_fetch_assoc($res))
			{
				if ($row["title"] !== $row["searchname"])
				{
					$category = new Category();
					$determinedcat = $category->determineCategory($row["title"], $row["groupID"]);

					if ($echo == 1)
					{
						if ($namestatus == 1)
							$db->query(sprintf("UPDATE releases SET searchname = %s, categoryID = %d, relnamestatus = 3 where ID = %d", $db->escapeString($row["title"]), $determinedcat, $row["ID"]));
						else
							$db->query(sprintf("UPDATE releases SET searchname = %s, categoryID = %d where ID = %d", $db->escapeString($row["title"]), $determinedcat, $row["ID"]));
					}
					if ($this->echooutput)
					{
						$groups = new Groups();

						echo"New name: ".$row["title"]."\n".
							"Old name: ".$row["searchname"]."\n".
							"New cat:  ".$category->getNameByID($determinedcat)."\n".
							"Old cat:  ".$category->getNameByID($row["categoryID"])."\n".
							"Group:    ".$groups->getByNameByID($row["groupID"])."\n".
							"Method:   "."predb titles: ".$row["source"]."\n"."\n";
					}
					$updated++;
				}
			}
		}*/
		if($this->echooutput)
		{
			$te = "";
			if ($time == 1)
				$te = " in the past 3 hours";
			echo "Fixing search names".$te." using the predb md5.\n";
		}
		// if ($res = $db->queryDirect("select r.ID, r.name, r.searchname, r.categoryID, r.groupID, rf.name as filename from releases r left join releasefiles rf on r.ID = rf.releaseID  where (r.name REGEXP'[a-fA-F0-9]{32}' or rf.name REGEXP'[a-fA-F0-9]{32}') and r.relnamestatus > 0 and r.categoryID IN (7010, 7020) and passwordstatus >= 0 ORDER BY rf.releaseID, rf.size DESC ")); //.$tq))
        if ($res = $db->queryDirect("select r.ID as relID, r.name, r.searchname, r.categoryID, r.groupID from releases r where r.name REGEXP'[a-fA-F0-9]{32}|[a-fA-F0-9]{40}'  AND r.relnamestatus != 3 and r.categoryID IN (7010, 7020)")) ; //.$tq))
        {
            echo "Checking ".$db->getNumRows($res)." hashed releases.\n";
            while($row = mysqli_fetch_assoc($res))
			{
				/*
				 * $db->query('INSERT INTO `predb`(`title`, `nfo`, `size`, `category`, `predate`, `adddate`, `source`,
                    `md5`, `md2`, `md4`, `sha1`, `ripemd128`, `ripemd160`, `tiger128_3`, `tiger160_3`, `tiger128_4`,
                    `tiger160_4`, `haval128_3`, `haval160_3`, `haval128_4`, `haval160_4`, `haval128_5`, `haval160_5`, `releaseGroup`)
				 */
                if (preg_match("/[a-f0-9]{32}|[a-f0-9]{40}/i", $row["name"], $matches))
				{
					$hash = $db->escapeString($matches[0]);
                    $a = $db->query("select ID, title, source from predb where md5 =".$hash." OR md2=".$hash." OR md4=" . $hash . " OR ripemd128=" . $hash .
                        " OR tiger128_3=" . $hash . " OR tiger128_4=" . $hash . " OR haval128_3=" . $hash . " OR haval128_4=" . $hash . " OR haval128_5=" . $hash .
                        " OR ripemd160=" . $hash . " OR tiger160_3=" . $hash . " OR tiger160_4=" . $hash . " OR haval160_3=" . $hash . " OR haval160_4=" . $hash .
                        " OR haval160_5=" . $hash);

					foreach ($a as $b)
					{
						if ($b["title"] !== $row["searchname"])
						{
							$category = new Category();
							$determinedcat = $category->determineCategory($b["title"], $row["groupID"]);
                            $db->query(sprintf("UPDATE releases SET searchname = %s, categoryID = %d, relnamestatus = 3 where ID = %d", $db->escapeString($b["title"]), $determinedcat, $row["ID"]));

							if ($this->echooutput)
							{
								$groups = new Groups();

								echo"New name: ".$b["title"]."\n".
									"Old name: ".$row["searchname"]."\n".
									"New cat:  ".$category->getNameByID($determinedcat)."\n".
									"Old cat:  ".$category->getNameByID($row["categoryID"])."\n".
									"Group:    ".$groups->getByNameByID($row["groupID"])."\n".
									"Method:   "."predb md5 release name: ".$b["source"]."\n"."\n";
							}
							$updated++;
                            $db->query("UPDATE predb SET releaseID=" . $row['relID'] . " WHERE ID=" . $b['ID']);
						}
					}
				}
			}
            if($res=$db->queryDirect("SELECT rf.*, r.name AS releaseName, r.searchname AS releaseSearchName, r.relnamestatus, r.groupID AS releaseGroupID, r.categoryID AS releaseCat " .
                "FROM `releasefiles` AS rf LEFT JOIN `releases` AS r ON rf.releaseID=r.ID " .
                "WHERE rf.name REGEXP'[a-fA-F0-9]{32}|[a-fA-F0-9]{40}' AND r.relnamestatus != 3"))
            {
                echo "Checking " . $db->getNumRows($res) . " hashed filenames.\n";
                while ($row = $db->fetchAssoc($res))
                {
                    if (preg_match("/[a-f0-9]{32}|[a-f0-9]{40}/i", $row["name"], $matches))
                    {
                        $hash = $db->escapeString($matches[0]);
                        $a = $db->queryDirect("select ID, title, source from predb where md5 =" . $hash . " OR md2=" . $hash . " OR md4=" . $hash . " OR ripemd128=" . $hash .
                            " OR tiger128_3=" . $hash . " OR tiger128_4=" . $hash . " OR haval128_3=" . $hash . " OR haval128_4=" . $hash . " OR haval128_5=" . $hash .
                            " OR ripemd160=" . $hash . " OR tiger160_3=" . $hash . " OR tiger160_4=" . $hash . " OR haval160_3=" . $hash . " OR haval160_4=" . $hash .
                            " OR haval160_5=" . $hash);

                        while ($b = $db->fetchAssoc($a))
                        {
                            if ($b["title"] !== $row["releaseSearchName"])
                            {
                                $category = new Category();
                                $determinedcat = $category->determineCategory($b["title"], $row["releaseGroupID"]);

                                $db->query(sprintf("UPDATE releases SET searchname = %s, categoryID = %d, relnamestatus = 3 where ID = %d", $db->escapeString($b["title"]), $determinedcat, $row["releaseID"]));

                                if ($this->echooutput)
                                {
                                    $groups = new Groups();

                                    echo "New name: " . $b["title"] . "\n" .
                                        "Old name: " . $row["releaseSearchName"] . "\n" .
                                        "New cat:  " . $category->getNameByID($determinedcat) . "\n" .
                                        "Old cat:  " . $category->getNameByID($row["releaseCat"]) . "\n" .
                                        "Group:    " . $groups->getByNameByID($row["releaseGroupID"]) . "\n" .
                                        "Method:   " . "predb md5 release name: " . $b["source"] . "\n" . "\n";
                                }
                                $updated++;
                                $db->query("UPDATE predb SET releaseID=" . $row['releaseID'] . " WHERE ID=" . $b['ID']);
                            }
                        }
                    }
                }
            }
		}
        echo "Total releases updated based on hashes: " . $updated . "\n";
		return $updated;
	}

	public function getAll($offset, $offset2)
	{
		$db = new DB();
		return $db->query(sprintf("SELECT p.*, r.guid FROM predb p left join releases r on p.releaseID = r.ID ORDER BY p.adddate DESC limit %d,%d", $offset, $offset2));
	}

	public function getCount()
	{
		$db = new DB();
		$count = $db->queryOneRow("SELECT count(*) as cnt from predb");
		return $count["cnt"];
	}
    
    public function _insertPreDB($db, $title,  $predate, $source, $size = '', $category = '', $nfo='')
    {
        preg_match('/[- ](?!.+[- ])(.+)/', $title, $releaseGroup);
        $sql = 'INSERT INTO `predb`(`title`, `nfo`, `size`, `category`, `predate`, `adddate`, `source`,
            `md5`, `md2`, `md4`, `sha1`, `ripemd128`, `ripemd160`, `tiger128_3`, `tiger160_3`, `tiger128_4`,
            `tiger160_4`, `haval128_3`, `haval160_3`, `haval128_4`, `haval160_4`, `haval128_5`, `haval160_5`, `releaseGroup`) VALUES (' .
            $db->escapeString($title) . ', ' . $db->escapeString($nfo) . ', ' . $db->escapeString($size) .
            ', ' . $db->escapeString($category) . ', FROM_UNIXTIME(' . $predate . '), now(), ' . $db->escapeString($source) .
            ', ' . $db->escapeString(hash('md5', $title, false)) . ', ' . $db->escapeString(hash('md2', $title, false)) .
            ', ' . $db->escapeString(hash('md4', $title, false)) . ', ' . $db->escapeString(hash('sha1', $title, false)) .
            ', ' . $db->escapeString(hash('ripemd128', $title, false)) . ', ' . $db->escapeString(hash('ripemd160', $title, false)) .
            ', ' . $db->escapeString(hash('tiger128,3', $title, false)) . ', ' . $db->escapeString(hash('tiger160,3', $title, false)) .
            ', ' . $db->escapeString(hash('tiger128,4', $title, false)) . ', ' . $db->escapeString(hash('tiger160,4', $title, false)) .
            ', ' . $db->escapeString(hash('haval128,3', $title, false)) . ', ' . $db->escapeString(hash('haval160,3', $title, false)) .
            ', ' . $db->escapeString(hash('haval128,4', $title, false)) . ', ' . $db->escapeString(hash('haval160,4', $title, false)) .
            ', ' . $db->escapeString(hash('haval128,5', $title, false)) . ', ' . $db->escapeString(hash('haval160,5', $title, false)) .
            ', ' . $db->escapeString($releaseGroup[1]) . ')';
        file_put_contents(WWW_DIR . "lib/logging/predb.log", $sql . "\n------------------------------------\n", FILE_APPEND);
        $db->query($sql);

        // echo "\n".$db->Error;

    }

    public function getWebPage($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
