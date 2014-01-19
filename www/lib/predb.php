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
		if (strtotime($newestrel["adddate"]) < time()-600)
		{
			if ($this->echooutput)
				echo "\033[01;37mRetrieving titles from preDB sources.\n";

            $newprelist = $this->retrievePrelist();
			$newzenet = $this->retrieveZenet();
            // $neworly = $this->retrieveOrlydb();  //Orlydb seems to be down - 1/16/2014
            $newpdme = $this->retrievePredbme();
            $newsrr = $this->retrieveSrr();
			$newwomble = $this->retrieveWomble();
            $newomgwtf = $this->retrieveOmgwtfnzbs();
			$newnames = $newwomble+$newomgwtf+$newzenet+$newprelist+$newsrr+$newpdme;
			if ($newnames == 0)
				$db->query(sprintf("UPDATE predb SET adddate = now() where ID = %d", $newestrel["ID"]));
		}
		$matched = $this->matchPredb();
		$matched += $this->matchHashed();
        $matched += $this->matchFileNames();
        $matched += $this->matchHashedFileNames();
        if ($matched > 0 && $this->echooutput)
            echo "\n\n\033[01;32mMatched a total of " . $matched . " releases to preDB titles.\033[01;37m\n";
        else
            echo "\n\n\033[01;33mNo releases were matched to preDB titles.\033[01;37m\n";

        // $nfos = $this->matchReleaseFiles();
		// if ($nfos > 0 && $this->echooutput)
		//      echo "\nAdded ".$nfos." missing NFOs from preDB sources.\n";
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
            $nfo = '';
            foreach($a as $link)
            {
                if(preg_match('/nfo/i', $link->href) === 1)
                {
                    $nfo = "nfo=" . $db->escapeString('http://www.newshost.co.za/' . $link->href) . ", ";
                    //echo $nfo . "\n";
                }
            }
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
                    $db->query("UPDATE predb SET " . $nfo . " size = " . $db->escapeString($size) .
                        ", category = " . $db->escapeString($categoryPrime) . ", adddate = NOW(), source = 'womble' " .
                        " where ID = " . $oldname['ID']);
                    //echo "DB: " . $db->Error() . "\n";
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
        $consoleTools = new ConsoleTools();
        $html = str_get_html($this->getWebPage("http://www.prelist.ws/?do=list"));
        $releases = $html->find('div[class="PreEntry Pred"]');

        foreach ($releases as $post) {

            $data = $post->find('div[class=PreData]');

            $e = $data[0]->find('div[class=PreName]');
            $e2 = $e[0]->find('a');
            $title = $e2[0]->innertext;
            $e = $data[0]->find('div[class=Time]');
            $preDate = strtotime($e[0]->innertext);
            $e = $data[0]->find('div[class=Section]');
            $e2 = $e[0]->find('a');
            $categoryPrime = $e2[0]->innertext;
            $e = $data[0]->find('div[class=FilesSize]');
            preg_match('/([\d\.]+MB)/', $e[0]->innertext, $match);
            $size = isset($match[1]) ? $match[1] : 'NULL';


            if ($categoryPrime != 'NUKE') {
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
	public function matchPre($releaseName, $releaseID)
	{
	    preg_match('/([A-Za-z0-9\.\(\)_\-]{12,}-([A-Za-z0-9]+))[ \[\(\-]/i', $releaseName, $nameMatches);
	    if(isset($nameMatches[1]) && isset($nameMatches[2]))
        {
            $db = new DB();
            $matchSql = "SELECT ID, nfo, title FROM predb WHERE releaseGroup = " . $db->escapeString($nameMatches[2]) . " AND title LIKE '%" . $nameMatches[1] . "%'";
            $preDbMatch = $db->queryOneRow($matchSql);
            if (isset($preDbMatch['ID']))
            {
                //echo "Match found: " . $releaseRow['name'] . "\n";
                $nfo = '';
                if (isset($preDbMatch['nfo']) && $preDbMatch['nfo'] != 'NULL' & !is_null($preDbMatch['nfo']))
                {
                    $nfoFile = $this->retrieveNfo($preDbMatch['nfo']);
                    if ($nfoFile !== false)
                    {
                        $db->queryInsert("INSERT INTO releasenfo (releaseID, nfo) VALUES (" . $releaseID . ", COMPRESS(" . $nfoFile . "))");
                        $nfo = " nfostatus=1, ";
                    }

                }
                $db->query("UPDATE releases SET " . $nfo . " preDbID=" . $preDbMatch['ID'] . ", searchname=" . $db->escapeString($preDbMatch['title']) . ", relnamestatus=6 WHERE ID=" . $releaseID);
                return true;
            }
        }
		return false;
    }


    /**
     * @param int   $interval   Number of hours to scan back from most recent release adddate
     *                          defaults to 3. Set to zero (0) to check all releases.
     *
     * @return int  $updated    Number of releases that were matched to a preDB entry
     *
     *  This function checks releases added to the database over the last 3 hours
     *  for a name that contains a pattern similar to a typical preDB entry.  Next,
     *  it checks for release names that may be md2, md4, md5, or sha1 encoded and
     *  compares them to precalculated hashes in the preDB database.  If a match
     *  is found, it will update the searchname, and recategorize the release.
     */
    public function matchPredb($interval = 3)
	{
		$db = new DB();
		$updated = 0;

        if($interval < 1)
            $intervalClause = ' ';
        elseif(is_numeric($interval))
            $intervalClause = " AND adddate > NOW() - INTERVAL " . $interval . " HOUR";
        else
            return false;

		if($this->echooutput)
			echo "\n\033[01;36mMatching up predb titles with release names.\033[01;37m\n";
        $releaseResults = $db->queryDirect("SELECT ID, name FROM releases WHERE relnamestatus != 6 AND preDbID IS NULL " . $intervalClause);
        $totalReleases = $db->getNumRows($releaseResults);

        if($totalReleases > 0)
        {
            $releasesProcessed = 0;
            $consoleTools = new ConsoleTools();
            while($releaseRow = $db->fetchAssoc($releaseResults))
            {
                $releasesProcessed ++;
                $consoleTools->overWrite("Processing release names " . $consoleTools->percentString($releasesProcessed, $totalReleases));
                preg_match('/([A-Za-z0-9\.\(\)_\-]{12,}-([A-Za-z0-9]+))[ \[\(\-]/i', $releaseRow['name'], $nameMatches);
                if(isset($nameMatches[1]) && isset($nameMatches[2]))
                {
                    $matchSql = "SELECT ID, nfo, title FROM predb WHERE releaseGroup = " . $db->escapeString($nameMatches[2]) . " AND title LIKE '%" . $nameMatches[1] . "%'";
                    $preDbMatch = $db->queryOneRow($matchSql);
                    if(isset($preDbMatch['ID']))
                    {
                        //echo "Match found: " . $releaseRow['name'] . "\n";
                        $nfo = '';
                        if(isset($preDbMatch['nfo']) && $preDbMatch['nfo'] != 'NULL' & !is_null($preDbMatch['nfo']))
                        {
                            $nfoFile = $this->retrieveNfo($preDbMatch['nfo']);
                            if($nfoFile !== false)
                            {
                                $db->queryInsert("INSERT INTO releasenfo (releaseID, nfo) VALUES (" . $releaseRow['ID'] . ", COMPRESS(" . $nfoFile . "))");
                                $nfo = " nfostatus=1, ";
                            }

                        }
                        $db->query("UPDATE releases SET " . $nfo . " preDbID=" . $preDbMatch['ID'] . ", searchname=" . $db->escapeString($preDbMatch['title']) . ", relnamestatus=6 WHERE ID=" . $releaseRow['ID']);
                        $updated ++;
                    }
                }
            }
            echo "\nRelease names matched: " . $updated . "\n";
        }
        else
            echo "No new releases to match.\n";

        return $updated;

	}

    public function matchHashed($interval = 3)
    {
        $db = new DB();
        $updated = 0;

        if ($interval < 1)
            $intervalClause = ' ';
        elseif (is_numeric($interval))
            $intervalClause = " AND adddate > NOW() - INTERVAL " . $interval . " HOUR";
        else
            return false;
        if ($this->echooutput)
            echo "\n\033[01;36mMatching up predb titles with hashed releases.\033[01;37m\n";

        $releaseResults = $db->queryDirect("SELECT ID, name, groupID FROM releases WHERE relnamestatus !=6 AND preDbId IS NULL AND " .
                                            "categoryID = 7020 AND name REGEXP '[a-fA-F0-9]{40}|[a-fA-F0-9]{32}' " . $intervalClause);
        $totalReleases = $db->getNumRows($releaseResults);
        if ($totalReleases > 0)
        {
            if (!isset($consoleTools))
                $consoleTools = new ConsoleTools();
            $category = new Category();
            $releasesProcessed = 0;
            $categoriesUpdated = array();
            while ($releaseRow = $db->fetchAssoc($releaseResults))
            {
                $releasesProcessed++;
                $consoleTools->overWrite("Processing hashed releases " . $consoleTools->percentString($releasesProcessed, $totalReleases));
                if (preg_match("/[a-f0-9]{40}|[a-f0-9]{32}/i", $releaseRow["name"], $matches))
                {
                    $hash = $db->escapeString($matches[0]);
                    $hashResults = $db->queryOneRow("SELECT ID, title, source FROM predb WHERE md5 =" . $hash . " OR md2=" . $hash . " OR md4=" . $hash . " OR sha1=" . $hash);
                    if (isset($hashResults['ID']))
                    {
                        $releaseCategory = $category->determineCategory($hashResults['title'], $releaseRow['groupID']);
                        if (array_key_exists($releaseCategory, $categoriesUpdated))
                            $categoriesUpdated[$releaseCategory] += 1;
                        else
                        {
                            $categoriesUpdated[$releaseCategory] = 1;
                        }
                        $db->query("UPDATE releases SET searchname=" . $db->escapeString($hashResults['title']) . ", categoryID=" . $releaseCategory .
                            ", preDbID=" . $hashResults['ID'] . ", relnamestatus = 6 WHERE ID=" . $releaseRow['ID']);
                        $updated++;
                    }
                }
            }
            echo "\nHashed releases matched: " . $updated . "\n";
            if ($updated > 0)
            {
                print_r($categoriesUpdated);
                $mask = "%-30.30s %22.22s\n";
                printf($mask, "Category", "Releases Added");
                printf($mask, "====================", "=================");
                foreach ($categoriesUpdated as $cat => $catCount)
                {
                    printf($category->getQualifiedName($cat), (string)number_format($catCount,0));
                }
            }
        }
        else
            echo "No new releases to match.\n";

        return $updated;
    }

    public function matchFileNames($interval=3)
    {
        $db = new DB();
        $updated = 0;

        if ($interval < 1)
            $intervalClause = ' ';
        elseif (is_numeric($interval))
            $intervalClause = " AND rf.createdate > NOW() - INTERVAL " . $interval . " HOUR";
        else
            return false;

        if ($this->echooutput)
            echo "\n\033[01;36mMatching up predb titles with release file names.\n\033[01;37m";

        $fileNameResults = $db->queryDirect("SELECT r.ID, r.searchname, r.groupID, rf.name FROM `releases` AS r LEFT JOIN `releasefiles` AS rf ON r.ID=rf.releaseID " .
                                            "WHERE rf.name REGEXP '([a-fA-F0-9]{32}|[a-fA-F0-9]{40})[[...][.backslash.]]' AND rf.ID IS NOT NULL " .
                                            "AND r.relnamestatus != -6 " . $intervalClause);
        $totalFiles = $db->getNumRows($fileNameResults);
        if ($totalFiles > 0)
        {
            if (!isset($consoleTools))
                $consoleTools = new ConsoleTools();
            $category = new Category();
            $fileNamesProcessed = 0;
            while($fileNameRow = $db->fetchAssoc($fileNameResults))
            {
                $fileNamesProcessed ++;
                $consoleTools->overWrite("Processing file names " . $consoleTools->percentString($fileNamesProcessed, $totalFiles));
                preg_match('/([a-fA-F0-9]{32}|[a-fA-F0-9]{40})[\.\\]/', $fileNameRow['name'], $nameMatches);
                if(isset($nameMatches[1]))
                {
                    $preDbMatch = $db->queryOneRow("SELECT ID, nfo, title FROM predb WHERE releaseGroup = " . $db->escapeString($nameMatches[2]) . " AND title LIKE '%" . $nameMatches[1] . "%'");
                    if (isset($preDbMatch['ID']))
                    {
                        //echo "Match found: " . $releaseRow['name'] . "\n";
                        $nfo = '';
                        if (isset($preDbMatch['nfo']) && $preDbMatch['nfo'] != 'NULL' & !is_null($preDbMatch['nfo']))
                        {
                            $nfoFile = $this->retrieveNfo($preDbMatch['nfo']);
                            if ($nfoFile !== false)
                            {
                                $db->queryInsert("INSERT INTO releasenfo (releaseID, nfo) VALUES (" . $fileNameRow['ID'] . ", COMPRESS(" . $nfoFile . "))");
                                $nfo = " nfostatus=1, ";
                            }

                        }
                        $newCategory = $category->determineCategory($preDbMatch['title'], $fileNameRow['groupID']);
                        $db->query("UPDATE releases SET " . $nfo . " preDbID=" . $preDbMatch['ID'] . ", searchname=" . $db->escapeString($preDbMatch['title']) .
                                    ", relnamestatus=6, categoryID=" . $newCategory . " WHERE ID=" . $fileNameRow['ID']);
                        $updated++;
                    }
                }
            }
            echo "\nRelease file names matched: " . $updated . "\n";
        }
        else
            echo "No new file names to process.\n";

        return $updated;
    }

    public function matchHashedFileNames($interval = 3)
    {
        $db = new DB();
        $updated = 0;

        if ($interval < 1)
            $intervalClause = ' ';
        elseif (is_numeric($interval))
            $intervalClause = " AND rf.createdate > NOW() - INTERVAL " . $interval . " HOUR";
        else
            return false;

        if ($this->echooutput)
            echo "\n\033[01;36mMatching up predb titles with hashed file names.\033[01;37m\n";

        $fileNameResults = $db->queryDirect("SELECT r.ID, r.searchname, r.groupID, rf.name FROM `releases` AS r LEFT JOIN `releasefiles` AS rf ON r.ID=rf.releaseID " .
            "WHERE rf.name REGEXP '([a-fA-F0-9]{40}|[a-fA-F0-9]{32})[[...][.backslash.]]' AND rf.ID IS NOT NULL " .
            "AND r.relnamestatus != -6 " . $intervalClause);
        $totalFiles = $db->getNumRows($fileNameResults);
        if ($totalFiles > 0)
        {
            if (!isset($consoleTools))
                $consoleTools = new ConsoleTools();
            $category = new Category();
            $fileNamesProcessed = 0;
            while ($fileNameRow = $db->fetchAssoc($fileNameResults))
            {
                $fileNamesProcessed++;
                $consoleTools->overWrite("Processing file names " . $consoleTools->percentString($fileNamesProcessed, $totalFiles));
                preg_match('/([a-fA-F0-9]{40}|[a-fA-F0-9]{32})[\.\\]/', $fileNameRow['name'], $nameMatches);
                if (isset($nameMatches[1]))
                {
                    $hash = $db->escapeString($nameMatches[1]);
                    $hashResults = $db->queryOneRow("SELECT ID, title, source, nfo FROM predb WHERE md5 =" . $hash . " OR md2=" . $hash . " OR md4=" . $hash . " OR sha1=" . $hash);
                    if (isset($hashResults['ID']))
                    {
                        $nfo = '';
                        if (isset($hashResults['nfo']) && $hashResults['nfo'] != 'NULL' & !is_null($hashResults['nfo']))
                        {
                            $nfoFile = $this->retrieveNfo($hashResults['nfo']);
                            if ($nfoFile !== false)
                            {
                                $db->queryInsert("INSERT INTO releasenfo (releaseID, nfo) VALUES (" . $fileNameRow['ID'] . ", COMPRESS(" . $nfoFile . "))");
                                $nfo = " nfostatus=1, ";
                            }

                        }
                        $releaseCategory = $category->determineCategory($hashResults['title'], $fileNameRow['groupID']);

                        $db->query("UPDATE releases SET " . $nfo . " searchname=" . $db->escapeString($hashResults['title']) . ", categoryID=" . $releaseCategory .
                            ", preDbID=" . $hashResults['ID'] . ", relnamestatus = 6 WHERE ID=" . $fileNameRow['ID']);
                        $updated++;
                    }


                }
            }
            echo "\nHashed file names matched: " . $updated . "\n";
        } else
            echo "No new file names to process.\n";

        return $updated;
    }


    public function retrieveNfo($url)
    {
        if (extension_loaded('curl'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);


            $nfo_response = curl_exec($ch);
            curl_close($ch);
            return $nfo_response !== false ? $nfo_response : false;
        }
        else
            echo "Error - php-curl extension not loaded.\n";

        return false;

    }

	// Look if the release is missing an nfo.
	public function matchReleaseFiles()
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
			echo "Fixing search names".$te." using preDB hashes.\n";
		}
		// if ($res = $db->queryDirect("select r.ID, r.name, r.searchname, r.categoryID, r.groupID, rf.name as filename from releases r left join releasefiles rf on r.ID = rf.releaseID  where (r.name REGEXP'[a-fA-F0-9]{32}' or rf.name REGEXP'[a-fA-F0-9]{32}') and r.relnamestatus > 0 and r.categoryID IN (7010, 7020) and passwordstatus >= 0 ORDER BY rf.releaseID, rf.size DESC ")); //.$tq))
        if ($res = $db->queryDirect("select r.ID as relID, r.name, r.searchname, r.categoryID, r.groupID from releases r where r.name REGEXP'[a-fA-F0-9]{32}|[a-fA-F0-9]{40}'  AND r.relnamestatus != 3 AND r.relnamestatus !=6 and r.categoryID IN (7010, 7020) AND r.preDbId IS NULL")) ; //.$tq))
        {
            echo "Checking ".$db->getNumRows($res)." hashed releases.\n";
            while($row = mysqli_fetch_assoc($res))
			{
				if (preg_match("/[a-f0-9]{32}|[a-f0-9]{40}/i", $row["name"], $matches))
				{
					$hash = $db->escapeString($matches[0]);
                    $a = $db->query("select ID, title, source from predb where md5 =".$hash." OR md2=".$hash." OR md4=" . $hash . " OR sha1=" . $hash);

					foreach ($a as $b)
					{
						if ($b["title"] !== $row["searchname"])
						{
							$category = new Category();
							$determinedcat = $category->determineCategory($b["title"], $row["groupID"]);
                            $db->query(sprintf("UPDATE releases SET searchname = %s, categoryID = %d, preDbId=%s, relnamestatus = 3 where ID = %d", $db->escapeString($b["title"]), $determinedcat, $b['ID'], $row["relID"]));

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
                "WHERE rf.name REGEXP'[a-fA-F0-9]{32}|[a-fA-F0-9]{40}' AND r.relnamestatus != 3 AND r.relnamestatus !=6 AND r.preDbID IS NULL"))
            {
                echo "Checking " . $db->getNumRows($res) . " hashed filenames.\n";
                while ($row = $db->fetchAssoc($res))
                {
                    if (preg_match("/[a-f0-9]{32}|[a-f0-9]{40}/i", $row["name"], $matches))
                    {
                        $hash = $db->escapeString($matches[0]);
                        $a = $db->queryDirect("select ID, title, source from predb where md5 =" . $hash . " OR md2=" . $hash . " OR md4=" . $hash . " OR sha1=" . $hash);

                        while ($b = $db->fetchAssoc($a))
                        {
                            if ($b["title"] !== $row["releaseSearchName"])
                            {
                                $category = new Category();
                                $determinedcat = $category->determineCategory($b["title"], $row["releaseGroupID"]);

                                $db->query(sprintf("UPDATE releases SET searchname = %s, categoryID = %d, preDbId=%s, relnamestatus = 3 where ID = %d", $db->escapeString($b["title"]), $determinedcat, $b['ID'], $row["releaseID"]));

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
            `md5`, `md2`, `md4`, `sha1`, `releaseGroup`) VALUES (' .
            $db->escapeString($title) . ', ' . $db->escapeString($nfo) . ', ' . $db->escapeString($size) .
            ', ' . $db->escapeString($category) . ', FROM_UNIXTIME(' . $predate . '), now(), ' . $db->escapeString($source) .
            ', ' . $db->escapeString(hash('md5', $title, false)) . ', ' . $db->escapeString(hash('md2', $title, false)) .
            ', ' . $db->escapeString(hash('md4', $title, false)) . ', ' . $db->escapeString(hash('sha1', $title, false)) .
            ', ' . $db->escapeString($releaseGroup[1]) . ')';
        // file_put_contents(WWW_DIR . "lib/logging/predb.log", $sql . "\n------------------------------------\n", FILE_APPEND);
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
