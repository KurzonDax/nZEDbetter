<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 9/7/13
 * Time: 11:26 PM
 * File: musicBrainz_test.php
 *
 */
require(dirname(__FILE__) . "/config.php");
require_once(WWW_DIR . "/lib/framework/db.php");
require_once(WWW_DIR . "/lib/consoletools.php");
require_once(WWW_DIR . "/lib/namecleaning.php");
require_once(WWW_DIR . "/lib/MusicBrainz.php");
require_once(WWW_DIR . "/lib/music.php");
require_once(WWW_DIR . "lib/category.php");
/**
 *
 */
define('DEBUG_ECHO', false);

$consoleTools = new ConsoleTools();
$db = new DB();
$category = new Category();
$matchedReleases = 0;
$singleReleases = 0;
echo "\nWelcome to the MusicBrainz test script.\n";

$catToProcess = 3000;
$offset = '';
$relID = $consoleTools->getUserInput("Enter a release ID, or press enter to search category 3000: ");
if(!is_numeric($relID))
    $offset = $consoleTools->getUserInput("\nPlease enter the offset to begin at: ");
if($offset == '' || !is_numeric($offset))
    $offset = 0;
if(!is_numeric($relID))
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE categoryID BETWEEN ".$catToProcess." AND ".($catToProcess+999)." LIMIT ".$offset.",100";
else
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE ID=".$relID;

$musicReleases = $db->queryDirect($sql);
$totalReleases = $db->getNumRows($musicReleases);
echo "\nWe found ".number_format($totalReleases)." music releases to process.\n";
$nameCleaning = new nameCleaning();
while($musicRow = $db->fetchAssoc($musicReleases))
{
    if(preg_match('/bootleg/i', $musicRow['name']) === 1)
    {
        echo "Skipping bootleg release: " . $musicRow['name'] . "\n";
        $consoleTools->getUserInput('Press enter to continue...');
        continue;
    }
    $cleanSearchName = $nameCleaning->musicCleaner($musicRow['searchname']);
    $query = cleanQuery($cleanSearchName);
    $artist = false;
    $recording = false;
    preg_match('/\(?(19|20)\d\d\)?(?!.+(19|20)\d\d)/', $musicRow['searchname'], $year);

    $singleTitle = isSingle($musicRow['name']);

    if(!is_array($singleTitle))
    {
        $result = getReleaseName($query, $artist, $cleanSearchName, (isset($year[0]) ? $year[0] : null));
        if($result)
        {
            echo "\n\nRelease name match: ".$result['title']."\nMB ID: ".$result['mbID']."\n";
            echo "Artist: ".$result['artist']."\n";
            echo "Artist ID: ".$result['artistID']."\n";
            $matchedReleases ++;
        }
        else
            echo "\n\nNo release name matches found\n";

    }
    else
    {
        echo "Song Track: ".$singleTitle['track']." ".$singleTitle['title']."\n";
        $singleReleases ++;
        $prefix = isset($singleTitle['disc']) ? (string)$singleTitle['disc'] . (string)$singleTitle['track'] : $singleTitle['track'];
        $query = preg_replace('/^' . $prefix . '/', '', $query);
        $result = getArtist((isset($singleTitle['artist']) ? $singleTitle['artist'] : $query), $musicRow['searchname'], $musicRow['name']);
        if($result)
        {
            // $recording = getRecording($singleTitle[2], $result['name'], false, $musicRow['name']);
            echo "\nArtist Name: ".$result['name']."\n";
            echo "Artist ID:    ".$result['id']."\n";
            echo "Percent Match: ".$result['percentMatch']."\n";
        }
        else
            echo "\n\nUnable to match an artist for this single.\n\n";

        echo "Song " . (isset($singleTitle['disc']) ? "Disc: " . $singleTitle['disc'] . " " : '') . "Track: " . (isset($singleTitle['track']) ? $singleTitle['track'] : "Not Found") . "\n";
        echo "Song Title:   " . $singleTitle['title'] . "\n";
        echo "Song Release: " . (isset($singleTitle['release']) ? $singleTitle['release'] : "Not Found") . "\n";
        echo "Song Artist:  " . (isset($singleTitle['artist']) ? $singleTitle['artist'] : 'Not Found') . "\n";
    }
    echo "\nRelease ID: " . $musicRow['ID'] . "\n";
    echo "Release name: " . $musicRow['name'] . "\n\n";
    echo "Search Name:  " . $musicRow['searchname'] . "\n";
    echo "Release Year: " . (isset($year[0]) ? $year[0] : "Not Found") . "\n";
    echo "Clean Name:   " . $cleanSearchName . "\n";
    echo "Query String: " . $query . "\n";

    // if(DEBUG_ECHO)
        $consoleTools->getUserInput("\nPress enter to continue: ");
}
echo "\nSuspected Singles Found: ".$singleReleases;
echo "\nTotal Releases Matched: ".$matchedReleases."\n";
echo "Match Percentage: ".($totalReleases > 0 ? (($matchedReleases/($totalReleases-$singleReleases)*100)) : '0%')."\n";
exit ("\nThanks for playing...\n");

/**
 * @param string $query
 * @param string $orgSearchName
 * @param string $releaseName
 *
 * @return array|bool
 */
function getArtist($query, $orgSearchName, $releaseName = '')
{
    $musicBrainz = new MusicBrainz();
    $orgSearchName = normalizeString($orgSearchName);
    $return = false;
    $results = $musicBrainz->searchArtist($query, '', 50);

    $wordCount = count(explode(' ', $query));

    //if($results['artist-list']['@attributes']['count'] == '0')
    $resultsAttr = isset($results->{'artist-list'}) ? $results->{'artist-list'}->attributes() : array();
    if(isset($resultsAttr['count']) && $resultsAttr['count'] == '0')
    {
        if(DEBUG_ECHO)
            echo "Artist name search returned no results\n";
        return $return;
    }
    elseif(!isset($resultsAttr['count']))
    {
        print_r($results);
        return $return;
    }
    elseif(DEBUG_ECHO)
            echo "Artists Found: ". $resultsAttr['count']."\n";

    $percentMatch = -1000;

    $i = 0;
    foreach($results->{'artist-list'}->artist as $artist)
    {

        $artistCheck = checkArtistName($artist, $orgSearchName, false, (((30-$i) / 30) * 10));
        if($artistCheck && $artistCheck['percentMatch'] > $percentMatch)
        {
            // The following helps to prevent single-word artists from matching an artist
            // with a similar full name (i.e Pink should not match Pink Floyd)
            // Obviously only works if the query string is two words or less
            if($wordCount < 3 && count(explode(' ' , $artistCheck['name'])) != $wordCount)
                continue;
            $return = $artistCheck;
            $percentMatch = $artistCheck['percentMatch'];
        }
        $i++;
    }

    return $return;
}

/**
 * @param string            $query          searchname after musicCleaner and cleanQuery
 * @param string            $artist         Name of artist
 * @param string            $orgSearchName  nZEDbetter searchname after musicCleaner
 * @param integer|null      $year           Year of release
 *
 * @return array|bool
 *
 * NOTE: If an artist is provided, better results will be obtained if the artist
 * name is removed from the $query string
 */
function getReleaseName($query, $artist, $orgSearchName, $year=null)
{
    $orgSearchName = normalizeString($orgSearchName);
    $searchNameArr = array();
    $searchNameArr[] = $orgSearchName;
    if(substr_count($orgSearchName, ' Volume '))
        $searchNameArr[] = str_ireplace(' Volume ',' ',$orgSearchName);
    preg_match('/Volume (\d)\b/i', $orgSearchName, $matches);
    switch ($matches[1])
    {
        case '1':
            $searchNameArr[] = str_ireplace(' Volume 1',' Volume I',$orgSearchName);
            $searchNameArr[] = str_ireplace(' Volume 1',' I',$orgSearchName);
            break;
        case '2':
            $searchNameArr[] = str_ireplace(' Volume 2',' Volume II',$orgSearchName);
            $searchNameArr[] = str_ireplace(' Volume 2',' II',$orgSearchName);
            break;
        case '3':
            $searchNameArr[] = str_ireplace(' Volume 3',' Volume III',$orgSearchName);
            $searchNameArr[] = str_ireplace(' Volume 3',' III',$orgSearchName);
            break;
        case '4':
            $searchNameArr[] = str_ireplace(' Volume 4',' Volume IV',$orgSearchName);
            $searchNameArr[] = str_ireplace(' Volume 4',' IV',$orgSearchName);
            break;
        case '5':
            $searchNameArr[] = str_ireplace(' Volume 5',' Volume V',$orgSearchName);
            $searchNameArr[] = str_ireplace(' Volume 5',' V',$orgSearchName);
            break;
    }

    $query = normalizeString($query);
    $musicBrainz = new MusicBrainz();
    $return = false;
    $percentMatch = 0;
    $artist = $artist=='' ? false : $artist;
    $artistArr = false;

    if($artist === false)
    {
        $results = $musicBrainz->searchRelease($query, 'release', '', '', 30);
    }
    else
    {
        $artistArr['name'] = $artist;
        $results = $musicBrainz->searchRelease($query, 'release', normalizeString($artist), 'artistname');
    }
    if(!isset($results->{'release-list'}->attributes()->count))
        print_r($results);
    if($results->{'release-list'}->attributes()->count == '0')
    {
        if(DEBUG_ECHO)
            echo "Release name search returned no results\n";
        return $return;
    }
    else
        if(DEBUG_ECHO)
            echo "Releases Found: ".$results->{'release-list'}->attributes()->count."\n";
    // print_r($results);
    if($results->{'release-list'}->attributes()->count == '1')
    {
        $matchFound = false;
        foreach($searchNameArr as $searchName)
        {
            if(stripos($searchName, normalizeString($results->{'release-list'}->release->title)) === false &&
                stripos(normalizeString($searchName, true), normalizeString($results->{'release-list'}->release->title, true)) === false)
                continue;
            else
            {
                $matchFound = true;
                break;
            }
        }
        if(!$matchFound)
        {
            if(DEBUG_ECHO)
                echo "Non-matching release: ".$results->{'release-list'}->release->title."\n";
            return false;
        }
        else
        {
            if(!is_array($return)){ $return = array();}
            $return['title'] = $results->{'release-list'}->release->title;
            $return['mbID'] = $results->{'release-list'}->release->attributes()->id;
        }
    }
    else
    {
        foreach($results->{'release-list'}->release as $release)
        {
            $matchFound = false;
            $matchedSearchName = '';
            foreach($searchNameArr as $searchName)
            {
                if(stripos($searchName, normalizeString($release->title)) === false &&
                    stripos(normalizeString($searchName, true), normalizeString($release->title, true)) === false)
                    continue;
                else
                {
                    $matchedSearchName = $searchName;
                    $matchFound = true;
                    break;
                }
            }
            if(!$matchFound)
            {
                if(DEBUG_ECHO)
                    echo "Non-matching release: ".$release->title."\n";
                continue;
            }
            else
            {
                similar_text(normalizeString($release->title), $orgSearchName, $tempMatch);
                if(DEBUG_ECHO)
                    echo "Checking release: ".$release->title."\n";
                if(!$artist && isset($release->{'artist-credit'}->{'name-credit'}))
                {
                    // print_r($release['artist-credit']['name-credit']);
                    $i = 0;
                    foreach($release->{'artist-credit'}->{'name-credit'} as $relArtist)
                    {
                        if(isset($relArtist->name))
                            $artistArr = checkArtistName($relArtist, $orgSearchName, false, (((30 - $i) / 30) * 10));
                        else
                            $artistArr = checkArtistName($relArtist->artist, $orgSearchName, false, (((30 - $i) / 30) * 10));
                        if($artistArr && stripos($query, normalizeString($artistArr['name'])) !== false)
                        {
                            $tempMatch += 25;
                            break;
                        }
                        else
                            $artistArr = false;
                        $i ++;
                    }
                    if(!$artistArr)
                    {
                        if(DEBUG_ECHO)
                            echo "No matching artist was found in the release.\n";
                        continue;
                    }
                    elseif($artistArr['name'] == 'Various Artists')
                        $tempMatch -= 15;
                }
                if(normalizeString($release->title, true) == normalizeString($artistArr['name'], true) && substr_count($query, normalizeString($artistArr['name'], true)) == 1)
                {
                    if(DEBUG_ECHO)
                        echo "Artist name and release title are the same, but not looking for self-titled release\n";
                    continue;
                }
                elseif(stripos(trim(preg_replace('/'.normalizeString($artistArr['name'], true).'/', '', normalizeString($matchedSearchName, true), 1)), trim(normalizeString($release->title, true))) === false)
                {
                    if(DEBUG_ECHO)
                        echo "Title no longer matched after extracting artist's name.\n";
                    continue;
                }
                if(isset($release->date) && !is_null($year) && preg_match('/'.$year.'/',$release->date))
                    $tempMatch += 25;
                elseif(isset($release->date) && !is_null($year))
                {
                    preg_match('/(19|20)\d\d', $release->date, $relYear);
                    if(isset($relYear[0]) && ($relYear[0] == ($year - 1) || $relYear[0] == ($year+1)))
                        $tempMatch += 20;
                }
                elseif (isset($release->{'release-event-list'}->{'release-event'}->date) && !is_null($year) && $release->{'release-event-list'}->{'release-event'}->date == $year)
                    $tempMatch += 20;
                elseif(isset($release->{'release-event-list'}->{'release-event'}->date) && !is_null($year))
                {
                    preg_match('/(19|20)\d\d', $release->{'release-event-list'}->{'release-event'}->date, $relYear);
                    if($relYear[0] == ($year - 1) || $relYear[0] == ($year+1))
                        $tempMatch += 15;
                }
                if(isset($release->{'medium-list'}->medium->format) && $release->{'medium-list'}->medium->format == 'CD')
                    $tempMatch += 15;
                if(DEBUG_ECHO)
                    echo "Matching release: ".$release->title." tempMatch: ".$tempMatch."\n";
                if($tempMatch > $percentMatch)
                {
                    if(!is_array($return)){ $return = array();}
                    $return['title'] = $release->title;
                    $return['mbID'] = $release->attributes()->id;
                    if(!$artist)
                    {
                        $return['artist'] = $artistArr['name'];
                        $return['artistID'] = $artistArr['id'];
                    }
                    $percentMatch = $tempMatch;
                }
            }
        }
    }

    if(isset($return['mbID']))
        return $return;
    else
        return false;
}

/**
 * @param string        $query          query string
 * @param bool|array    $artistArr      false or name=>Artist Name or null, id=>MB Artist ID or null
 * @param string        $orgSearchName  nZEDbetter release name
 * @param string|int    $trackNumber    Optional, Track Number of Song
 *
 * @return bool|array
 */
function getRecording($query, $artistArr, $orgSearchName, $trackNumber='')
{
    /*      searchRecording Possible Fields
     *      arid 			artist id
            artist 			artist name is name(s) as it appears on the recording
            artistname 		an artist on the recording, each artist added as a separate field
            creditname 		name credit on the recording, each artist added as a separate field

     */

    $musicBrainz = new MusicBrainz();
    $return = false;
    $results = array();

    if(!$artistArr)
    {
        $results = $musicBrainz->searchRecording($query);
    }
    elseif(!is_null($artistArr['id']))
    {
        $results = $musicBrainz->searchRecording($query, 'recording', $artistArr['id'], 'arid');
    }
    elseif(!is_null($artistArr['name']))
    {
        $results = $musicBrainz->searchRecording($query, 'recording', $artistArr['name'], 'artist');
    }
    print_r($results);

    return $return;
}

/**
 * @param $text
 *
 * @return mixed
 */
function cleanQuery($text, $debug=false)
{
    // Remove year
    if($debug)
        echo "\nStrip Search Name - " . $text . "\n";
    $text = preg_replace('/\((19|20)\d\d\)|(?<!top|part|vol|volume)[ \-_]\d{1,3}[ \-_]|\d{3,4} ?kbps| cd ?\d{1,2} /i', ' ', $text);
    if($debug)
        echo "1 - " . $text . "\n";
    // Remove extraneous format identifiers
    $text = str_replace(array('MP3','FLAC','WMA','WEB', "cd's", ' cd ',' FM '), ' ', $text);
    if ($debug)
        echo "2 - " . $text . "\n";
    $text = str_ireplace(' vol ', ' Volume ', $text);
    if ($debug)
        echo "3 - " . $text . "\n";
    // Remove extra punctuation and non alphanumeric
    $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", "~", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+", "!"), " ", $text);
    if ($debug)
        echo "4 - " . $text . "\n";
    $text = preg_replace('/\s{2,}/',' ', $text);
    if ($debug)
        echo "5 - " . $text . "\n";
    return $text;
}

/**
 * @param string    $text
 * @param bool      $includeArticles
 *
 * @return mixed|string
 */
function normalizeString($text, $includeArticles=false)
{
    $text = strtolower($text);
    if($includeArticles)
        $text = preg_replace('/\b(a|an|the)\b/i', ' ', $text);
    $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", "~", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+", "'s "), " ", $text);
    $text = str_ireplace(' vol ', ' Volume ', $text);
    $text = str_ireplace('&', 'and', $text);
    $text = preg_replace('/\s{2,}/',' ', $text);
    $text = trim($text);

    return $text;
}

/**
 * @param array     $relArtist          array containing artist results from MB
 * @param string    $orgSearchName      normalized nZEDbetter release name
 * @param bool      $skipVariousCheck   Defaults to false, skip check for Various Artists
 * @param int|float $weight             Precalcualted weight to add to percentmatch
 *
 * @return array|bool
 */
function checkArtistName($relArtist, $orgSearchName, $skipVariousCheck=false, $weight=0)
{
    echo "Checking artist: " . $relArtist->name . "\n";
    $percentMatch = 0;
    $artistArr = array();
    $artistFound = false;
    if($relArtist->name === '[unknown]')
        return false;
    elseif($relArtist->name == 'Various Artists' && !$skipVariousCheck)
    {
        $artistArr['name'] = 'Various Artists';
        $artistArr['id'] = '89ad4ac3-39f7-470e-963a-56509c546377';
        $artistFound = true;
    }
    elseif(preg_match('/\b'.normalizeString($relArtist->name).'\b/', $orgSearchName)=== 0)
    {
        if( preg_match('/\b'.trim(str_ireplace('Group', '', normalizeString($relArtist->name, true))).'\b/', $orgSearchName) === 1)
        {
            if(DEBUG_ECHO)
                echo "Artist name matched: ".$relArtist->name." (weight = $weight)\n";
            $artistArr['name'] = $relArtist->name;
            $artistArr['id'] = $relArtist->attributes()->id;
            similar_text($orgSearchName, normalizeString($relArtist->name), $percentMatch);
            $artistArr['percentMatch'] = $percentMatch + $weight;
            $artistFound = true;
        }
        elseif(isset($relArtist->{'sort-name'}) && preg_match('/\b'.normalizeString($relArtist->{'sort-name'}).'\b/', $orgSearchName) === 1)
        {
            if (DEBUG_ECHO)
                echo "Artist sort name matched: " . $relArtist->name . " (weight = $weight)\n";
            $artistArr['name'] = $relArtist->name;
            $artistArr['id'] = $relArtist->attributes()->id;
            similar_text($orgSearchName, normalizeString($relArtist->{'sort-name'}), $percentMatch);
            $artistArr['percentMatch'] = $percentMatch + $weight;
            $artistFound = true;
        }
        else
        {
            if(DEBUG_ECHO)
                echo "Artist name not matched: ".$relArtist->name." (weight = $weight)\n";
            if(isset($relArtist->{'alias-list'}))
            {
                if(DEBUG_ECHO)
                    echo "Checking aliases...\n";
                foreach($relArtist->{'alias-list'}->alias as $alias)
                {
                    if(is_array($alias))
                    {
                        if (DEBUG_ECHO)
                            echo "\nAlias is an array\n";
                        foreach($alias as $aliasName)
                        {
                            if(isset($aliasName['locale']) && $aliasName->attributes()->locale == 'ja')
                                continue;
                            if(preg_match('/\b'.normalizeString($aliasName).'\b/', $orgSearchName) === 0)
                            {
                                if(DEBUG_ECHO)
                                    echo "Alias did not match: ".$aliasName." (weight = $weight)\n";
                                continue;
                            }
                            else
                            {
                                // if(DEBUG_ECHO)
                                echo "Alias matched: ".$aliasName->alias."\n";
                                $artistArr['name'] = $relArtist->name;
                                $artistArr['id'] = $relArtist->attributes()->id;
                                similar_text($orgSearchName, normalizeString($aliasName), $percentMatch);
                                $artistArr['percentMatch'] = $percentMatch + $weight;
                                $artistFound = true;
                                break;
                            }
                        }
                        if($artistFound) {break;}
                    }
                    else
                    {
                        if(isset($alias['locale']) && $alias->attributes()->locale == 'ja')
                            continue;
                        if(preg_match('/\b'.normalizeString($alias).'\b/', $orgSearchName)===0)
                        {
                            if(DEBUG_ECHO)
                                echo "Alias did not match: ".$alias." (weight = $weight)\n";
                            continue;
                        }
                        else
                        {
                            if(DEBUG_ECHO)
                                echo "Alias matched: ".$alias." (weight = $weight)\n";
                            $artistArr['name'] = $relArtist->name;
                            $artistArr['id'] = $relArtist->attributes()->id;
                            similar_text($orgSearchName, normalizeString($alias), $percentMatch);
                            $artistArr['percentMatch'] = $percentMatch + $weight;
                            $artistFound = true;
                            break;
                        }
                    }
                }
            }
        }
    }
    else
    {
        if(DEBUG_ECHO)
            echo "Artist name matched: ".$relArtist->name." (weight = $weight)\n";
        $artistArr['name'] = $relArtist->name;
        $artistArr['id'] = $relArtist->attributes()->id;
        similar_text($orgSearchName, normalizeString($relArtist->name), $percentMatch);
        $artistArr['percentMatch'] = $percentMatch + $weight;
        $artistFound = true;
    }

    if($artistFound && $artistArr['percentMatch'] > 15)
        return $artistArr;
    elseif($artistFound && $artistArr['percentMatch'] > 0 && $artistArr['percentMatch'] <= 15 && DEBUG_ECHO)
        echo "Artist percent match not acceptable: " . $artistArr['percentMatch'] . "\n";

    return false;
}

/**
 * @param $releaseName
 *
 * @return mixed
 */
function isSingle($releaseName)
{
    if(empty($releaseName) || $releaseName == null)
        return false;

    // Perform some very basic cleaning on the release name before matching
    $releaseName = trim(preg_replace('/by req:? |attn:?| 320 | EAC/i', '', $releaseName));
    $releaseName = trim(preg_replace('/\s{2,}/', ' ', $releaseName));

    // The 'track' group will not match tracks numbered above 19 to prevent matching a year
    // Probably won't be much of an issue because track numbers that high are rare.
    // The alternative is the regex would be much more strict in what would be identified as a track number.
    preg_match('/(^|["\- ])(?P<artist>[\w\s\']+?) ?(19\d\d|20\d\d)? ?-?(?P<release>[\w\s\']+)-?([\(\[ ](19\d\d|20\d\d)[\)\] ])?(?![\(\[ ](19\d\d|20\d\d))(?P<track> ?(?!\(|\[|19\d\d|20\d\d)[0-2][0-9]\d?\d?).+?(?!-)(?P<title>[\(\)\w _\']+)\.(?:mp3|wav|ogg|wma|mpa|rar|par|aac|m4a|flac)/i', $releaseName, $matches);

    if(!isset($matches[0]) || (!isset($matches['artist']) && !isset($matches['release']) && !isset($matches['track']) && !isset($matches['title'])))
        preg_match('/(?P<track>["\- ](?!\(|\[|19\d\d|20\d\d)[0-2][0-9]\d?\d?)(?<!\(|\[|19\d\d|20\d\d)(?P<artist>( |-).+-)* ?-? ?(?P<title>[\(\)\w \-_\']+)\.(?:mp3|wav|ogg|wma|mpa|rar|par|aac|m4a|flac)/i', $releaseName, $matches);

    if (!isset($matches[0]))
        preg_match('/("|-) ?"?(?P<artist> ?.+-)* ?-? ?(?P<title>[\(\)\w \-_\']+)\.(?:mp3|wav|ogg|wma|mpa|rar|par|aac|m4a|flac)/i', $releaseName, $matches);

    if(!isset($matches[0]))
        return false;

    if(isset($matches['artist']))
    {
        $matches['artist'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['artist']));
        $matches['artist'] = preg_replace('/\s{2,}/', ' ', $matches['artist']);
    }

    if(isset($matches['release']))
    {
        $matches['release'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['release']));
        $matches['release'] = preg_replace('/\s{2,}/', ' ', $matches['release']);
    }

    if(isset($matches['title']))
    {
        $matches['title'] = trim(str_ireplace(array('-', '_', '"'), ' ', $matches['title']));
        $matches['title'] = preg_replace('/\s{2,}/', ' ', $matches['title']);
    }
    if(isset($matches['track']))
        $matches['track'] = trim(str_ireplace(array('"', ' ', '-'), '', $matches['track']));

    if(isset($matches['track']) && strlen($matches['track']) > 2)
    {
        $matches['disc'] = strlen($matches['track']) > 3 ? substr($matches['track'], 0, 2) : substr($matches['track'], 0, 1);
        $matches['track'] = strlen($matches['track']) > 3 ? substr($matches['track'], 2, 2) : substr($matches['track'], 1, 2);
    }


    return ((isset($matches['track']) && isset($matches['title'])) || (isset($matches['artist']) && isset($matches['title'])) ) ? $matches : false;
}