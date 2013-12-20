<?php
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/TMDb.php");
require_once(WWW_DIR."/lib/category.php");
require_once(WWW_DIR."/lib/nfo.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/util.php");
require_once(WWW_DIR."/lib/releaseimage.php");
require_once(WWW_DIR."/lib/rottentomato.php");
require_once(WWW_DIR."/lib/trakttv.php");
require_once(WWW_DIR."/lib/namecleaning.php");
require_once(WWW_DIR."/lib/consoletools.php");
require_once(WWW_DIR . "/lib/imdb.php");
class Movie
{
    const SRC_BOXOFFICE = 1;
    const SRC_INTHEATRE = 2;
    const SRC_OPENING = 3;
    const SRC_UPCOMING = 4;
    const SRC_DVD = 5;

    function Movie($echooutput=false)
    {
        $this->echooutput = true;
        $s = new Sites();
        $site = $s->get();
        $this->apikey = $site->tmdbkey;
        $this->movieqty = (!empty($site->maximdbprocessed)) ? $site->maximdbprocessed : 100;
        $this->service = "";
        $this->imdburl = ($site->imdburl == "0") ? false : true;
        $this->imdblanguage = (!empty($site->imdblanguage)) ? $site->imdblanguage : "en";

        $this->tmdbSearch = (!empty($site->movie_search_tmdb)) ? $site->movie_search_tmdb : 'FALSE';
        $this->imdbSearch = (!empty($site->movie_search_imdb)) ? $site->movie_search_imdb : 'FALSE';

        $this->movieNoYearMatchPercent = (!empty($site->movieNoYearMatchPercent) ? $site->movieNoYearMatchPercent : 91);
        $this->movieWithYearMatchPercent = (!empty($site->movieWithYearMatchPercent) ? $site->movieWithYearMatchPercent : 80);

        $this->matchMoviesWithoutYear = (!empty($site->matchMoviesWithoutYear)) ? $site->matchMoviesWithoutYear : 'FALSE';
        $this->processForeignMovies = (!empty($site->processForeignMovies)) ? $site->processForeignMovies : 'FALSE';

        $this->imgSavePath = WWW_DIR.'covers/movies/';
        $this->binglimit = 0;
        $this->yahoolimit = 0;
    }

    public function getMovieInfo($imdbId)
    {
        $db = new DB();
        return $db->queryOneRow(sprintf("SELECT * FROM movieinfo where imdbID = %d", $imdbId));
    }

    public function getMovieInfoMultiImdb($imdbIds)
    {
        $db = new DB();
        $allids = implode(",", $imdbIds);
        $sql = sprintf("SELECT DISTINCT movieinfo.*, releases.imdbID AS relimdb FROM movieinfo LEFT OUTER JOIN releases ON releases.imdbID = movieinfo.imdbID WHERE movieinfo.imdbID IN (%s)", $allids);
        return $db->query($sql);
    }

    public function getRange($start, $num)
    {
        $db = new DB();

        if ($start === false)
            $limit = "";
        else
            $limit = " LIMIT ".$start.",".$num;

        return $db->query(" SELECT * FROM movieinfo ORDER BY createddate DESC".$limit);
    }

    public function getCount()
    {
        $db = new DB();
        $res = $db->queryOneRow("select count(ID) as num from movieinfo");
        return $res["num"];
    }

    public function getMovieCount($cat, $maxage=-1, $excludedcats=array())
    {
        $db = new DB();

        $browseby = $this->getBrowseBy();

        $catsrch = "";
        if (count($cat) > 0 && $cat[0] != -1)
        {
            $catsrch = " (";
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
                            $chlist.=", ".$child["ID"];

                        if ($chlist != "-99")
                            $catsrch .= " r.categoryID in (".$chlist.") OR ";
                    }
                    else
                    {
                        $catsrch .= sprintf(" r.categoryID = %d OR ", $category);
                    }
                }
            }
            $catsrch.= "1=2 )";
        }

        if ($maxage > 0)
            $maxage = sprintf(" AND r.postdate > NOW() - INTERVAL %d DAY ", $maxage);
        else
            $maxage = "";

        $exccatlist = "";
        if (count($excludedcats) > 0)
            $exccatlist = " AND r.categoryID NOT IN (".implode(",", $excludedcats).")";

        $sql = sprintf("SELECT COUNT(DISTINCT r.movieID) AS num FROM releases AS r
                            INNER JOIN movieinfo AS m ON m.ID = r.movieID AND m.title != ''
                            WHERE r.passwordstatus <= (SELECT value FROM site WHERE setting='showpasswordedrelease') AND %s %s %s %s ",
                            $browseby, $catsrch, $maxage, $exccatlist);
        $res = $db->queryOneRow($sql);
        return $res["num"];
    }

    public function getMovieRange($cat, $start, $num, $orderby, $maxage=-1, $excludedcats=array())
    {
        $db = new DB();

        $browseby = $this->getBrowseBy();

        if ($start === false)
            $limit = "";
        else
            $limit = " LIMIT ".$start.",".$num;

        $catsrch = "";
        if (count($cat) > 0 && $cat[0] != -1)
        {
            $catsrch = " (";
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
                            $chlist.=", ".$child["ID"];

                        if ($chlist != "-99")
                            $catsrch .= " r.categoryID in (".$chlist.") or ";
                    }
                    else
                    {
                        $catsrch .= sprintf(" r.categoryID = %d or ", $category);
                    }
                }
            }
            $catsrch.= "1=2 )";
        }

        $maxage = "";
        if ($maxage > 0)
            $maxage = sprintf(" and r.postdate > now() - interval %d day ", $maxage);

        $exccatlist = "";
        if (count($excludedcats) > 0)
            $exccatlist = " and r.categoryID not in (".implode(",", $excludedcats).")";

        $order = $this->getMovieOrder($orderby);
        $sql = sprintf(" SELECT GROUP_CONCAT(r.ID ORDER BY r.postdate DESC SEPARATOR ',') AS grp_release_id,
                            GROUP_CONCAT(r.rarinnerfilecount ORDER BY r.postdate DESC SEPARATOR ',') AS grp_rarinnerfilecount,
                            GROUP_CONCAT(r.haspreview ORDER BY r.postdate DESC SEPARATOR ',') as grp_haspreview,
                            GROUP_CONCAT(r.passwordstatus ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_password,
                            GROUP_CONCAT(r.guid ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_guid,
                            GROUP_CONCAT(rn.ID ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_nfoID,
                            GROUP_CONCAT(groups.name ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_grpname,
                            GROUP_CONCAT(r.searchname ORDER BY r.postdate DESC SEPARATOR '#') as grp_release_name,
                            GROUP_CONCAT(r.postdate ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_postdate,
                            GROUP_CONCAT(r.size ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_size,
                            GROUP_CONCAT(r.totalpart ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_totalparts,
                            GROUP_CONCAT(r.comments ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_comments,
                            GROUP_CONCAT(r.grabs ORDER BY r.postdate DESC SEPARATOR ',') as grp_release_grabs,
                            m.*, groups.name AS group_name, rn.ID AS nfoID
                            FROM releases AS r LEFT OUTER JOIN groups ON groups.ID = r.groupID
                                INNER JOIN movieinfo AS m on m.ID = r.movieID AND m.title != ''
                                LEFT OUTER JOIN releasenfo AS rn ON rn.releaseID = r.ID AND rn.nfo IS NOT NULL
                            WHERE r.passwordstatus <= (SELECT value FROM site WHERE setting='showpasswordedrelease')
                            AND %s %s %s %s GROUP BY m.ID ORDER BY %s %s " . $limit,
                        $browseby, $catsrch, $maxage, $exccatlist, $order[0], $order[1]);
        return $db->query($sql);
    }

    public function getMovieOrder($orderby)
    {
        $order = ($orderby == '') ? 'max(r.postdate)' : $orderby;
        $orderArr = explode("_", $order);
        switch($orderArr[0]) {
            case 'title':
                $orderfield = 'm.title';
                break;
            case 'year':
                $orderfield = 'm.year';
                break;
            case 'rating':
                $orderfield = 'm.rating';
                break;
            case 'posted':
            default:
                $orderfield = 'max(r.postdate)';
                break;
        }
        $ordersort = (isset($orderArr[1]) && preg_match('/^asc|desc$/i', $orderArr[1])) ? $orderArr[1] : 'desc';
        return array($orderfield, $ordersort);
    }

    public function getMovieOrdering()
    {
        return array('title_asc', 'title_desc', 'year_asc', 'year_desc', 'rating_asc', 'rating_desc');
    }

    public function getBrowseByOptions()
    {
        return array('title', 'director', 'actors', 'genre', 'rating', 'year', 'imdb');
    }

    public function getBrowseBy()
    {
        $db = new Db();

        $browseby = ' ';
        $browsebyArr = $this->getBrowseByOptions();
        foreach ($browsebyArr as $bb) {
            if (isset($_REQUEST[$bb]) && !empty($_REQUEST[$bb])) {
                $bbv = stripslashes($_REQUEST[$bb]);
                if ($bb == 'rating') { $bbv .= '.'; }
                if ($bb == 'imdb') {
                    $browseby .= "m.{$bb}ID = $bbv AND ";
                } else {
                    $browseby .= "m.$bb LIKE(".$db->escapeString('%'.$bbv.'%').") AND ";
                }
            }
        }
        return $browseby;
    }

    public function makeFieldLinks($data, $field)
    {
        if ($data[$field] == "")
            return "";

        $tmpArr = explode(',',$data[$field]);
        $newArr = array();
        $i = 0;
        foreach($tmpArr as $ta) {
            if ($i > 5) { break; } //only use first 6
            $newArr[] = '<a href="'.WWW_TOP.'/movies?'.$field.'='.urlencode($ta).'" title="'.$ta.'">'.$ta.'</a>';
            $i++;
        }
        return implode(', ', $newArr);
    }

    public function update($id, $title, $tagline, $plot, $year, $rating, $genre, $director, $actors, $language, $cover, $backdrop)
    {
        $db = new DB();

        $db->query(sprintf("UPDATE movieinfo SET title=%s, tagline=%s, plot=%s, year=%s, rating=%s, genre=%s, director=%s, actors=%s, language=%s, cover=%d, backdrop=%d, updateddate=NOW() WHERE imdbID = %d",
            $db->escapeString($title), $db->escapeString($tagline), $db->escapeString($plot), $db->escapeString($year), $db->escapeString($rating), $db->escapeString($genre), $db->escapeString($director), $db->escapeString($actors), $db->escapeString($language), $cover, $backdrop, $id));
    }

    public function updateMovieInfo($movieData)
    {
        $db = new DB();

        $ri = new ReleaseImage();


        // Get the poster and backdrop

        if (isset($movieData['cover']) && $movieData['cover'] != '')
        {
            preg_match('/\.jpg$|\.png$/im', $movieData['cover'], $ext);
            $imageResult = $ri->saveImage('imdb'.$movieData['imdbID'].'tmdb'.$movieData['tmdbID'].'-cover', $movieData['cover'], $this->imgSavePath);
            $movieData['cover'] = $imageResult ? 'imdb' . $movieData['imdbID'] . 'tmdb' . $movieData['tmdbID'] . '-cover' . $ext[0]: 'NULL';
        }

        if (isset($movieData['backdrop']) && $movieData['backdrop'] != '')
        {
            preg_match('/\.jpg$|\.png$/im', $movieData['backdrop'], $ext);
            $imageResult = $ri->saveImage('imdb' . $movieData['imdbID'] . 'tmdb' . $movieData['tmdbID'] . '-backdrop', $movieData['backdrop'], $this->imgSavePath, 1024, 768);
            $movieData['backdrop'] = $imageResult ? 'imdb' . $movieData['imdbID'] . 'tmdb' . $movieData['tmdbID'] . '-backdrop' . $ext[0]: 'NULL';
        }
        $query = "SELECT ID FROM movieinfo WHERE imdbID=" . $movieData['imdbID'] . " AND tmdbID=" . $movieData['tmdbID'];
        if ($existingID = $db->queryOneRow($query))
        {
            $query = sprintf("UPDATE movieinfo SET
				imdbID=%d, tmdbID=%d, title=%s, tagline=%s, rating=%s, MPAArating=%s, MPAAtext=%s, plot=%s, year=%s, genre=%s, type=%s, director=%s,
				actors=%s, language=%s, cover=%s, backdrop=%s, duration=%d, updateddate=NOW() WHERE ID=%d",
                $movieData['imdbID'], $movieData['tmdbID'], $db->escapeString($movieData['title']), $db->escapeString($movieData['tagline']), $db->escapeString($movieData['rating']),
                $db->escapeString($movieData['MPAArating']), $db->escapeString($movieData['MPAAtext']), $db->escapeString($movieData['plot']), $db->escapeString($movieData['year']),
                $db->escapeString(is_array($movieData['genres']) && !is_null($movieData['genres']) ? implode(",", $movieData['genres']) : ''), $db->escapeString($movieData['type']), $db->escapeString($movieData['director']),
                $db->escapeString(implode(",", $movieData['actors'])), $db->escapeString($movieData['language']), $db->escapeString($movieData['cover']),
                $db->escapeString($movieData['backdrop']), $movieData['duration'], $existingID['ID']);
            $db->query($query);
            if($db->getAffectedRows() > 0)
                $movieId = $existingID['ID'];
            else
                $movieId = false;
        }
        else
        {
            $query = sprintf("
                INSERT INTO movieinfo
                    (imdbID, tmdbID, title, tagline, rating, MPAArating, MPAAtext, plot, year, genre, type, director, actors, language, cover, backdrop, duration, createddate, updateddate)
                VALUES
                    (%d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, NOW(), NOW())",
                $movieData['imdbID'], $movieData['tmdbID'], $db->escapeString($movieData['title']), $db->escapeString($movieData['tagline']), $db->escapeString($movieData['rating']),
                $db->escapeString($movieData['MPAArating']), $db->escapeString($movieData['MPAAtext']), $db->escapeString($movieData['plot']), $db->escapeString($movieData['year']),
                $db->escapeString(implode(",", $movieData['genres'])), $db->escapeString($movieData['type']), $db->escapeString($movieData['director']),
                $db->escapeString(implode(",", $movieData['actors'])), $db->escapeString($movieData['language']), $db->escapeString($movieData['cover']),
                $db->escapeString($movieData['backdrop']), $movieData['duration']);

            $movieId = $db->queryInsert($query);
        }

        if ($movieId)
        {
            if (!is_null($movieData['genres']) && array_count_values($movieData['genres']) > 0)
            {
                // Update genres and genres mapping
                foreach ($movieData['genres'] as $genre)
                {
                    $genre = trim($genre);
                    if ($genreExists = $db->queryOneRow("SELECT ID FROM movieGenres WHERE name = '" . $genre . "' OR name LIKE '" . $genre . "%'"))
                    {
                        $db->query("INSERT IGNORE INTO movieIDtoGenre (movieID, genreID) VALUES (" . $movieId . ", " . $genreExists['ID'] . ")");
                    } else
                    {
                        $newGenre = $db->queryInsert("INSERT INTO movieGenres (name) VALUES ('" . $genre . "')");
                        $db->query("INSERT IGNORE INTO movieIDtoGENRE (movieID, genreID) VALUES (" . $movieId . ", " . $newGenre . ")");
                    }
                }
            }
            if ($this->echooutput && $this->service == "")
                echo "\033[01;32mAdded/updated movie: ".$movieData['title']." (".$movieData['year'].") - ".$movieData['imdbID']. "  " . $movieData['tmdbID'] . "\n";
        }
        else
        {
            $lastDBerror = $db->Error();
            if ($this->echooutput && $this->service == "")
                echo "\033[01;35mNothing to update for movie: ".$movieData['title']." (".$movieData['year'].") - ".$movieData['imdbID']. "  " . $movieData['tmdbID'] . "\n";
            echo "Last DB Error: " . $lastDBerror . "\033[01;37m\n";
            file_put_contents(WWW_DIR."lib/logging/movie_update_sql.log", $query . "\nDB Error: " . $lastDBerror . "\n-----------------------------------------\n", FILE_APPEND);
        }

        return $movieId;
    }
    public function fetchTmdbInfoByName($searchstring, $year=false)
    {
        $tmdb = new TMDb($this->apikey, $this->imdblanguage);
        $results = $tmdb->searchMovie($searchstring, true, ($year===false ? null : $year));
        return $results;

    }


    public function fetchTmdbProperties($tmdbID, $isImdbID=false)
    {
        $tmdb = new TMDb($this->apikey, $this->imdblanguage);

        if ($isImdbID !== false)
            $lookupId = 'tt'.$tmdbID;
        else
            $lookupId = $tmdbID;

        try {$tmdbLookup = $tmdb->getMovie($lookupId);}
        catch (exception $e) {return false;}

        if (!$tmdbLookup) {return false;};
        if (isset($tmdbLookup['status_code']) && $tmdbLookup['status_code'] !== 1) { return false;}

        $ret = array();
        $ret['title'] = $tmdbLookup['title'];
        $ret['tmdb_id'] = $tmdbLookup['id'];
        $imdbID = str_replace('tt','',$tmdbLookup['imdb_id']);

        if($imdbID=='0000000' || $imdbID==0 || $imdbID == false || empty($imdbID))
            $imdbID=-1;
        $ret['imdb_id'] = $imdbID;

        if (isset($tmdbLookup['vote_average'])) {$ret['rating'] = ($tmdbLookup['vote_average'] == 0) ? '' : $tmdbLookup['vote_average'];}
        // A "plot" and a "tagline" are two entirely different things. wtf?
        // if (isset($tmdbLookup['tagline']))		{$ret['plot'] = $tmdbLookup['tagline'];}
        // How about we actually set the tagline to the tagline field??
        $ret['tagline'] = (isset($tmdbLookup['tagline']) ? $tmdbLookup['tagline'] : '');

        // And maybe we can use the overview for the plot </sarcasm>
        if (isset($tmdbLookup['overview'])) {$ret['plot'] = $tmdbLookup['overview'];}
        if (isset($tmdbLookup['release_date'])) {$ret['year'] = date("Y", strtotime($tmdbLookup['release_date']));}
        if (isset($tmdbLookup['imdb_id'])) {$ret['imdbID'] = str_replace('tt','', $tmdbLookup['imdb_id']);}
        if (isset($tmdbLookup['genres']) && sizeof($tmdbLookup['genres']) > 0)
        {
            $genres = array();
            foreach($tmdbLookup['genres'] as $genre)
            {
                $genres[] = $genre['name'];
            }
            $ret['genre'] = $genres;
        }
        if (isset($tmdbLookup['poster_path']) && sizeof($tmdbLookup['poster_path']) > 0)
        {
            $ret['cover'] = $tmdb->getImageUrl($tmdbLookup['poster_path'], "poster", "w185");
        }
        if (isset($this->backdrops) && sizeof($this->backdrops) > 0)
        {
            $ret['backdrop'] = $tmdb->getImageUrl($tmdbLookup['backdrop_path'], "backdrop", "original");  //"http://image.tmdb.org/t/p/original".$tmdbLookup['backdrop_path'];
        }
        return $ret;
    }

    public function processMovieReleases($releaseToWork = '')
    {
        /*
         * IMDB ID AND TMDB ID RESULT CODES:
         *
         * If match is found for either, then the appropriate field will be set to the
         * corresponding ID from the service.
         *
         * -1 = No match from the service
         * -2 = Search without year is false, and movie did not have a year in the searchname
         * -3 = Failure to find any matches from either service
         * -4 = Unable to parse the searchname
         * -5 = An error occurred inserting/updating movieinfo table
         *
         */

        echo "\033[01;37m";
        if($this->tmdbSearch == 'FALSE' && $this->imdbSearch == 'FALSE')
        {
            echo "\nERROR: You have not set any lookups for movies. Please check the\n";
            echo "Site Settings section of the admin site for your server.\n\n ";
            return false;
        }
        $db = new DB();

        if ($releaseToWork == '')
        {
            if($this->processForeignMovies == 'TRUE')
            {
                $res = $db->queryDirect(sprintf("SELECT * from releases where imdbID IS NULL and nzbstatus = 1 and categoryID in ( select ID from category where parentID = %d ) order by postdate desc limit %d", Category::CAT_PARENT_MOVIE, $this->movieqty));
                $moviecount = $db->getNumRows($res);
            }
            else
            {
                $res = $db->queryDirect(sprintf("SELECT * from releases where imdbID IS NULL and nzbstatus = 1 and categoryID in ( select ID from category where parentID = %d ) AND categoryID != %d order by postdate desc limit %d", Category::CAT_PARENT_MOVIE, Category::CAT_MOVIE_FOREIGN, $this->movieqty));
                $moviecount = $db->getNumRows($res);
            }
        }
        else
        {
            // name, searchname, ID, categoryID, groupID
            $pieces = explode("           =+=            ", $releaseToWork);
            $res = array(array('name' => $pieces[0], 'searchname' => $pieces[1], 'ID' => $pieces[2], 'categoryID' => $pieces[3], 'groupID' => $pieces[4]));
            $moviecount = 1;
        }
        if($this->tmdbSearch == 'TRUE')
            $tmdb = new TMDb($this->apikey);
        if($this->imdbSearch == 'TRUE')
            $imdb = new IMDB();

        if ($moviecount > 0)
        {
            if ($this->echooutput && $moviecount > 1)
                echo "\033[01;37mProcessing ".$moviecount." movie release(s)."."\n";

            $nameCleaning = new nameCleaning();

            foreach ($res as $arr)
            {
                if($this->processForeignMovies == 'FALSE' && $arr['categoryID'] == Category::CAT_MOVIE_FOREIGN)
                    continue;

                if($cleanName = $this->parseMovieSearchName($nameCleaning->movieCleaner($arr['searchname'])))
                {
                    if(isset($cleanName['TVSeries']) && $cleanName['TVSeries'] == 'TRUE')
                    {
                        $db->query("UPDATE releases SET categoryID=" . Category::CAT_TV_SD . " WHERE ID=" . $arr['ID']);
                        continue;
                    }
                    if(is_null($cleanName['year']) && $this->matchMoviesWithoutYear == 'FALSE')
                    {
                        echo "\033[01;37mMovie does not have a year in the searchname: " . $arr['searchname'] . "\n";
                        $db->query("UPDATE releases SET imdbID = -2 WHERE ID = " . $arr['ID']);
                        continue;
                    }

                    echo "Looking up: " . $cleanName['name'] . " (" . (!is_null($cleanName['year']) ? $cleanName['year'] : "N/A") . ")\n";

                    // Search The Movie DB, if enabled
                    if($this->tmdbSearch == 'TRUE')
                    {
                        if ($tmdbResult = $this->searchTMDb($cleanName['name'], $cleanName['year']))
                        {
                            $tmdbProps = $tmdb->lookupMovie($tmdbResult);
                            if(!$tmdbProps)
                            {
                                // We did not get data back from tmdb
                                echo "\033[01;31mERROR: The Movie DB returned no data for ID: " . $tmdbResult . "\033[01;37m\n";
                                unset($tmdbProps);
                            }
                        }
                        else
                        {
                            $msg = $arr['ID'].",".$db->escapeString($arr['searchname']).",".$db->escapeString($cleanName['name'])."\n";
                            file_put_contents(WWW_DIR."lib/logging/tmdb-fail.log", $msg, FILE_APPEND);
                        }

                        if ($this->imdbSearch == 'FALSE' && (!isset($tmdbProps) || $tmdbProps === false))
                        {
                            // No result found on The Movie DB and IMDB searching is FALSE in site settings
                            // Update the imdbID and tmdbID for the release to -3
                            echo "\033[01;35mNo results found from The Movie DB for " . $arr['searchname'] . "\033[01;37m\n";
                            file_put_contents(WWW_DIR . "/lib/logging/tmdb-nomatch.log", $arr['ID'] . "," . $db->escapeString($arr['searchname']) .
                                "," . $db->escapeString($cleanName['name']) . "," . (is_null($cleanName['year']) ? "NULL" : $cleanName['year']) . "\n", FILE_APPEND);
                            $db->query("UPDATE releases SET imdbID = -3, tmdbID = -3 WHERE ID = " . $arr['ID']);
                            continue;
                        }
                    }

                    // Search IMDB, if enabled
                    if($this->imdbSearch == 'TRUE')
                    {
                        // Check if we already have an IMDB ID from The Movie DB
                        if(isset($tmdbProps) && $tmdbProps['imdb_id'] != -1)
                            $imdbResults = $tmdbProps['imdb_id'];
                        elseif (isset($tmdbProps) && $tmdbProps['imdb_id'] == -1)
                            $imdbResults = $this->searchIMDB($tmdbProps['title'], (isset($tmdbProps['year']) ? $tmdbProps['year'] :
                                (isset($cleanName['year']) ? $cleanName['year'] : null)));
                        else
                            $imdbResults = $this->searchIMDB($cleanName['name'], $cleanName['year']);

                        if($imdbResults !== false && preg_replace('/tt|0+/', '', $imdbResults['id']) != 1)
                        {
                            $imdbProps = $imdb->lookupMovie($imdbResults);
                            // Check if no data returned from IMDB lookup
                            if(!$imdbProps)
                            {
                                echo "\033[01;31mERROR: IMDB returned no data for ID: " . $imdbResults . "\033[01;37m\n";
                                unset($imdbProps);
                            }
                            else
                                echo "Received IMDB data for " . $imdbProps['title'] . "\033[01;37m\n";
                        }
                        else
                            unset($imdbProps);
                    } // IMDB search

                } // If $cleanName = true
                else
                {
                    // Something went wrong parsing the searchname, update imdbID and tmdbID to -4 for the release
                    echo "\033[01;31mERROR: Unable to parse searchname for ID: " . $arr['ID'] . "  " . $arr['searchname'] . "\033[01;37m\n";
                    $db->query("UPDATE releases SET imdbID = -4, tmdbID = -4 WHERE ID = " . $arr['ID']);
                    continue;
                }

                // Check if we have complete and utter failure
                if (!isset($imdbProps) && !isset($tmdbProps))
                {
                    echo "\033[01;33mNo results found for ID: " . $arr['ID'] . "   " . $cleanName['name'] . "\033[01;37m\n";
                    $db->query("UPDATE releases SET imdbID = -2, tmdbID = -2 WHERE ID = " . $arr['ID']);
                    $msg = $arr['ID'] . "," . $db->escapeString($arr['searchname']) . "," . $db->escapeString($cleanName['name']) . "\n";
                    file_put_contents(WWW_DIR."lib/logging/movie-no-match.log",$msg, FILE_APPEND);
                    continue;
                }

                $movieData = $this->normalizeMovieData( (isset($tmdbProps) ? $tmdbProps : null), (isset($imdbProps) ? $imdbProps : null) );

                $movieID = $this->updateMovieInfo($movieData);

                if($movieID)
                    $db->query("UPDATE releases SET movieID=". $movieID . ", imdbID=" . $movieData['imdbID'] . ", tmdbID=" . $movieData['tmdbID'] .
                                " WHERE ID=" . $arr['ID']);
                else
                    $db->query("UPDATE releases SET movieID=-5, imdbID=-5, tmdbID=-5 WHERE ID=" . $arr['ID']);

            } // foreach
        } // if moviecount > 0
        else
            return false;

        return true;
    }

    public function parseMovieSearchName($releasename)
    {
        $cat = new Category();
        if (!$cat->isMovieForeign($releasename))
        {
            if(preg_match('/S\d{1,2}E\d{1,2}| S\d{1,2} | D\d{1,2} |Episode \d{1,2} /i', $releasename))
            {
                echo "\033[01;36mAppears to be TV Series, changing category: " . $releasename . "\n";
                return array('TVSeries' => 'TRUE');
            }
            preg_match('/^(?P<name>.*)[\.\-_\( ](?P<year>19\d{2}|20\d{2})/i', $releasename, $matches);
            if (!isset($matches['year']))
            {
                preg_match('/^(?P<name>.*)[\.\-_ ](?:dvdrip|ntsc|dvdr|bdrip|brrip|bluray|hdtv|divx|xvid|hdrip|proper|repack|real\.proper|sub\.?fix|sub\.?pack|ac3d|unrated|1080i|1080p|720p)/i', $releasename, $matches);
            }

            if (isset($matches['name']))
            {
                $name = preg_replace('/\(.*?\)|\.|_/i', ' ', $matches['name']);
                $year = (isset($matches['year'])) ? $matches['year'] : null;
                return array('name' => $name, 'year' => $year);
            }
        }
        return false;
    }

    public function normalizeMovieData ($tmdbProps, $imdbProps)
    {
        $movieData = array();

        $movieData['imdbID'] = (!is_null($tmdbProps) && $tmdbProps['imdb_id'] != -1 ? $tmdbProps['imdb_id'] : (!is_null($imdbProps) ? $imdbProps['imdb_id'] : -1));
        $movieData['tmdbID'] = (!is_null($tmdbProps) ? $tmdbProps['tmdb_id'] : -1);
        $movieData['title'] = (!is_null($tmdbProps) ? $tmdbProps['title'] : $imdbProps['title']);
        $movieData['rating'] = (!is_null($tmdbProps) ? $tmdbProps['rating'] : $imdbProps['rating']);
        $movieData['tagline'] = (!is_null($tmdbProps) && (strlen($tmdbProps['tagline']) >= (!is_null($imdbProps) && isset($imdbProps['tagline']) ? strlen($imdbProps['tagline']) : 0))) ?
                                    $tmdbProps['tagline'] : (!is_null($imdbProps) ? $imdbProps['tagline'] : '');
        $tmdbPlot = (!is_null($tmdbProps) ? $tmdbProps['plot'] : '');
        if(!is_null($imdbProps))
        {
            $imdbDescLength = isset($imdbProps['description']) && !is_null($imdbProps['description']) ? strlen($imdbProps['description']) : 0;
            $imdbShortDescLength = isset($imdbProps['shortDescription']) && !is_null($imdbProps['shortDescription']) ? strlen($imdbProps['shortDescription']) : 0;
            if($imdbDescLength > 0 && $imdbShortDescLength > 0)
                $imdbPlot = $imdbDescLength > $imdbShortDescLength ? $imdbProps['description'] : $imdbProps['shortDescription'];
            else
                $imdbPlot = '';
        }

        // $imdbPlot = (!is_null($imdbProps) ? (isset($imdbProps['description']) ? $imdbProps['description'] :
        //                (isset($imdbProps['shortDescription']) ? $imdbProps['shortDescription'] : '')) : '');
        $movieData['plot'] = (strlen($tmdbPlot) >= strlen($imdbPlot) ? $tmdbPlot : $imdbPlot);
        $movieData['year'] = (!is_null($tmdbProps) && $tmdbProps['year'] != -1 ? $tmdbProps['year'] : (!is_null($imdbProps) && $imdbProps['year'] != -1 ?
                                $imdbProps['year'] : 'N/A'));
        // $movieData['genres'] = array();
        $movieData['genres'] = (!is_null($tmdbProps) && isset($tmdbProps['genres']) && array_count_values($tmdbProps['genres']) > 0 ? $tmdbProps['genres'] : null);
        $movieData['genres'] = (!is_null($imdbProps) && isset($imdbProps['genres']) && array_count_values($imdbProps['genres']) > 0 ?
                                    (!is_null($movieData['genres']) ? array_unique(array_merge((array)$imdbProps['genres'], $movieData['genres'])) :
                                    $imdbProps['genres']) : array('N/A'));
        if(is_null($movieData['genres']))
            $movieData['genres'] = array('N/A');
        $movieData['type'] = 'Movie';
        $movieData['director'] = (!is_null($tmdbProps) && isset($tmdbProps['director']) ? $tmdbProps['director'] :
                                    (!is_null($imdbProps) && isset($imdbProps['director']) ? $imdbProps['director'] : ''));
        $movieData['actors'] = (!is_null($tmdbProps) && isset($tmdbProps['actors']) && array_count_values($tmdbProps['actors']) > 0 ? $tmdbProps['actors'] : null);
        $movieData['actors'] = (!is_null($imdbProps) && isset($imdbProps['actors']) && array_count_values($imdbProps['actors']) > 0 ?
                                    (!is_null($movieData['actors']) ? array_unique(array_merge((array)$imdbProps['actors'], $movieData['actors'])) :
                                    $imdbProps['actors']) : array('N/A'));
        $movieData['language'] = (!is_null($tmdbProps) && isset($tmdbProps['language']) ? $tmdbProps['language'] :
                                    (!is_null($imdbProps) && isset($imdbProps['language']) ? $imdbProps['language'] : 'English'));
        $movieData['cover'] = (!is_null($tmdbProps) && isset($tmdbProps['cover']) && !empty($tmdbProps['cover']) ? $tmdbProps['cover'] :
                                    (!is_null($imdbProps) && isset($imdbProps['cover']) && !is_null($imdbProps['cover']) ? $imdbProps['cover'] : 0));
        $movieData['backdrop'] = (!is_null($tmdbProps) && isset($tmdbProps['backdrop']) && !empty($tmdbProps['backdrop']) ? $tmdbProps['backdrop'] : 0);
        $movieData['duration'] = (!is_null($tmdbProps) && isset($tmdbProps['duration']) ? $tmdbProps['duration'] :
                                    (!is_null($imdbProps) && isset($imdbProps['duration']) ? $imdbProps['duration'] : 'NULL'));
        $movieData['MPAArating'] = (!is_null($imdbProps && isset($imdbProps['MPAArating'])) ? $imdbProps['MPAArating'] : 'NULL');
        $movieData['MPAAtext'] = (!is_null($imdbProps && isset($imdbProps['MPAAtext'])) ? $imdbProps['MPAAtext'] : 'NULL');

        return $movieData;
    }

    public function getUpcoming($type, $source="rottentomato")
    {
        $db = new DB();
        $sql = sprintf("select * from upcoming where source = %s and typeid = %d", $db->escapeString($source), $type);
        return $db->queryOneRow($sql);
    }

    public function updateUpcoming()
    {
        $s = new Sites();
        $site = $s->get();
        if ($this->echooutput)
            echo "Updating movie schedule using rotten tomatoes.\n";
        if (isset($site->rottentomatokey))
        {
            $rt = new RottenTomato($site->rottentomatokey);

            $ret = $rt->getBoxOffice();
            if ($ret != "")
                $this->updateInsUpcoming('rottentomato', Movie::SRC_BOXOFFICE, $ret);

            $ret = $rt->getInTheaters();
            if ($ret != "")
                $this->updateInsUpcoming('rottentomato', Movie::SRC_INTHEATRE, $ret);

            $ret = $rt->getOpening();
            if ($ret != "")
                $this->updateInsUpcoming('rottentomato', Movie::SRC_OPENING, $ret);

            $ret = $rt->getUpcoming();
            if ($ret != "")
                $this->updateInsUpcoming('rottentomato', Movie::SRC_UPCOMING, $ret);

            $ret = $rt->getDVDReleases();
            if ($ret != "")
                $this->updateInsUpcoming('rottentomato', Movie::SRC_DVD, $ret);
            if ($this->echooutput)
                echo "Updated successfully.\n";
        }
    }
    public function searchTMDb($cleanName, $cleanYear = null)
    {
        $nameCleaning = new nameCleaning();

        $results = $this->fetchTmdbInfoByName($cleanName, (is_null($cleanYear) ? false : $cleanYear));

        $matchFound = false;
        if(count($results['results'])>0)
        {
            $matchCount = 0;
            foreach ($results['results'] as $possibleMatch)
            {
                $ourName = $nameCleaning->normalizeText(strtolower($cleanName));
                $tmdbName = $nameCleaning->normalizeText(strtolower($possibleMatch['title']));
                similar_text($ourName, $tmdbName, $percentSimilar);
                if(isset($possibleMatch['release_date']) &&  preg_match('/((20|19)\d\d)/',$possibleMatch['release_date'], $matches))
                    $tmdbYear = $matches['1'];
                if(!is_null($cleanYear) && isset($tmdbYear))
                    $matchedYear = ($tmdbYear > $cleanYear - 2 && $tmdbYear < $cleanYear + 2) ? true : false;
                else
                    $matchedYear = true;
                if (!is_null($cleanYear) && $percentSimilar > $this->movieWithYearMatchPercent && $matchedYear)
                {
                    echo "TMDb Match found:   ".$possibleMatch['title']." (".$tmdbYear.") Match Number: ".$matchCount."  ID: ".$possibleMatch['id']."\n\033[00;37m";
                    $matchFound = $possibleMatch['id'];
                    break;
                }
                elseif (is_null($cleanYear) && $this->matchMoviesWithoutYear == 'TRUE' && $percentSimilar > $this->movieNoYearMatchPercent)
                {
                    echo "TMDb Match found \033[01;31m(no year)\033[01;31m:   " . $possibleMatch['title'] . "  Match Number: " . $matchCount . "  ID: " . $possibleMatch['id'] . "\n\033[00;37m";
                    $matchFound = $possibleMatch['id'];
                    break;
                }
                unset($tmdbYear, $tmdbName);
                $matchCount ++;
            }
        }

        return $matchFound;
    }

    public function searchIMDB($cleanName, $cleanYear=null)
    {
        $nameCleaning = new nameCleaning();
        $imdb = new IMDB();

        $results = $imdb->searchIMDB($cleanName, IMDB::MOVIES);

        $matchFound = false;
        if (count($results) > 0)
        {
            $matchCount = 0;
            foreach ($results as $possibleMatch)
            {
                $ourName = $nameCleaning->normalizeText(strtolower($cleanName));
                $imdbName = $nameCleaning->normalizeText(strtolower($possibleMatch['title']));
                similar_text($ourName, $imdbName, $percentSimilar);
                if (isset($possibleMatch['year']) && $possibleMatch['year'] !== -1)
                    $imdbYear = $possibleMatch['year'];
                if (!is_null($cleanYear) && isset($imdbYear))
                    $matchedYear = ($imdbYear > $cleanYear - 2 && $imdbYear < $cleanYear + 2) ? true : false;
                else
                    $matchedYear = true;
                if (!is_null($cleanYear) && $percentSimilar > $this->movieWithYearMatchPercent && $matchedYear)
                {
                    echo "IMDB Match found:   " . $possibleMatch['title'] . " (" . (isset($imdbYear) ? $imdbYear : "N/A") . ") Match Number: " . $matchCount . "  ID: " . $possibleMatch['id'] . "\n\033[00;37m";
                    $matchFound = $possibleMatch['id'];
                    break;
                } elseif (is_null($cleanYear) && $this->matchMoviesWithoutYear == 'TRUE' && $percentSimilar > $this->movieNoYearMatchPercent)
                {
                    echo "IMDB Match found \033[01;31m(no year)\033[01;31m:   " . $possibleMatch['title'] . "  Match Number: " . $matchCount . "  ID: " . $possibleMatch['id'] . "\n\033[00;37m";
                    $matchFound = $possibleMatch['id'];
                    break;
                }
                unset($imdbYear, $imdbName);
                $matchCount++;
            }
        }

        return $matchFound;
    }
    public function updateInsUpcoming($source, $type, $info)
    {
        $db = new DB();
        $sql = sprintf("INSERT IGNORE INTO upcoming (source,typeID,info,updateddate) VALUES (%s, %d, %s, null)
				ON DUPLICATE KEY UPDATE info = %s", $db->escapeString($source), $type, $db->escapeString($info), $db->escapeString($info));
        $db->query($sql);
    }


    public function getGenres()
    {
        $db = new DB();
        $query = $db->queryDirect("SELECT DISTINCT name FROM movieGenres WHERE active=1");
        $allGenres = array();
        while($genre = $db->fetchAssoc($query))
            $allGenres[] = $genre['name'];
        sort($allGenres, SORT_STRING);
        return $allGenres;
    }
}
