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
    $cleanSearchName = $nameCleaning->musicCleaner($musicRow['searchname']);
    $searchString = stripSearchName($cleanSearchName);
    $artist = false;
    $recording = false;

    $singleTitle = isSingle($musicRow['name']);
    if(!$singleTitle)
    {
        $result = getReleaseName($searchString, $artist, $cleanSearchName);
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
        echo "Song Track: ".$singleTitle[1]." ".$singleTitle[2]."\n";
        $singleReleases ++;
        $result = getArtist($searchString, $musicRow['searchname'], $musicRow['name']);
        if($result)
        {
            $recording = getRecording($singleTitle[2], $result['name'], false, $musicRow['name']);
            echo "\nArtist Name: ".$result['name']."\n";
            echo "Artist ID:    ".$result['id']."\n";
            echo "Percent Match: ".$result['percentMatch']."\n";
        }
        else
            echo "\n\nUnable to match an artist for this single.\n\n";

        echo "Song Track: " . $singleTitle[1] . " " . $singleTitle[2] . "\n";
    }
    echo "\nRelease ID: " . $musicRow['ID'] . "\n";
    echo "Release name: " . $musicRow['name'] . "\n\n";
    echo "Search Name:  " . $musicRow['searchname'] . "\n";
    echo "Clean Name:   " . $cleanSearchName . "\n";
    echo "Query String: " . $searchString . "\n";

    if(DEBUG_ECHO)
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
    $results = $musicBrainz->searchArtist($query);

    if($results['artist-list']['@attributes']['count'] == '0')
    {
        if(DEBUG_ECHO)
            echo "Artist name search returned no results\n";
        return $return;
    }
    else
        if(DEBUG_ECHO)
            echo "Artists Found: ".$results['artist-list']['@attributes']['count']."\n";

    $percentMatch = -1000;

    foreach($results['artist-list']['artist'] as $artist)
    {
        $artistCheck = checkArtistName($artist, $orgSearchName, true);
        if($artistCheck && $artistCheck['percentMatch'] > $percentMatch)
        {
            $return = $artistCheck;
            $percentMatch = $artistCheck['percentMatch'];
        }
    }

    return $return;
}

/**
 * @param $query
 * @param $artist
 * @param $orgSearchName
 *
 * @return array|bool
 */
function getReleaseName($query, $artist, $orgSearchName)
{
    preg_match('/\(?(19|20)\d\d\)?', $orgSearchName, $year);
    if(isset($year['1'])){$year = $year['1'];}

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
    if(!isset($results['release-list']['@attributes']['count']))
        print_r($results);
    if($results['release-list']['@attributes']['count'] == '0')
    {
        if(DEBUG_ECHO)
            echo "Release name search returned no results\n";
        return $return;
    }
    else
        if(DEBUG_ECHO)
            echo "Releases Found: ".$results['release-list']['@attributes']['count']."\n";
    // print_r($results);
    if($results['release-list']['@attributes']['count'] == '1')
    {
        $matchFound = false;
        foreach($searchNameArr as $searchName)
        {
            if(stripos($searchName, normalizeString($results['release-list']['release']['title'])) === false &&
                stripos(normalizeString($searchName, true), normalizeString($results['release-list']['release']['title'], true)) === false)
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
                echo "Non-matching release: ".$results['release-list']['release']['title']."\n";
            return false;
        }
        else
        {
            if(!is_array($return)){ $return = array();}
            $return['title'] = $results['release-list']['release']['title'];
            $return['mbID'] = $results['release-list']['release']['@attributes']['id'];
        }
    }
    else
    {
        foreach($results['release-list']['release'] as $release)
        {
            $matchFound = false;
            $matchedSearchName = '';
            foreach($searchNameArr as $searchName)
            {
                if(stripos($searchName, normalizeString($release['title'])) === false &&
                    stripos(normalizeString($searchName, true), normalizeString($release['title'], true)) === false)
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
                    echo "Non-matching release: ".$release['title']."\n";
                continue;
            }
            else
            {
                similar_text(normalizeString($release['title']), $orgSearchName, $tempMatch);
                if(DEBUG_ECHO)
                    echo "Checking release: ".$release['title']."\n";
                if(!$artist && isset($release['artist-credit']['name-credit']))
                {
                    // print_r($release['artist-credit']['name-credit']);
                    foreach($release['artist-credit']['name-credit'] as $relArtist)
                    {
                        if(isset($relArtist['name']))
                            $artistArr = checkArtistName($relArtist, $orgSearchName);
                        else
                            $artistArr = checkArtistName($relArtist['artist'], $orgSearchName);
                        if($artistArr)
                        {
                            $tempMatch += 25;
                            break;
                        }
                    }
                    if(!$artistArr)
                    {
                        if(DEBUG_ECHO)
                            echo "A matching artist was not found in the release.\n";
                        continue;
                    }
                    elseif($artistArr['name'] == 'Various Artists')
                        $tempMatch -= 15;
                }
                if(normalizeString($release['title'], true) == normalizeString($artistArr['name'], true) && substr_count($query, normalizeString($artistArr['name'], true)) == 1)
                {
                    if(DEBUG_ECHO)
                        echo "Artist name and release title are the same, but not looking for self-titled release\n";
                    continue;
                }
                elseif(stripos(trim(preg_replace('/'.normalizeString($artistArr['name'], true).'/', '', normalizeString($matchedSearchName, true), 1)), trim(normalizeString($release['title'], true))) === false)
                {
                    if(DEBUG_ECHO)
                        echo "Title no longer matched after extracting artist's name.\n";
                    continue;
                }
                if(isset($release['date']) && preg_match('/'.$year.'/',$release['date']))
                    $tempMatch += 25;
                elseif(isset($release['date']))
                {
                    preg_match('/(19|20)\d\d', $release['date'], $relYear);
                    if($relYear[0] == ($year - 1) || $relYear[0] == ($year+1))
                        $tempMatch += 20;
                }
                elseif (isset($release['release-event-list']['release-event']['date']) && $release['release-event-list']['release-event']['date'] == $year)
                    $tempMatch += 20;
                elseif(isset($release['release-event-list']['release-event']['date']))
                {
                    preg_match('/(19|20)\d\d', $release['release-event-list']['release-event']['date'], $relYear);
                    if($relYear[0] == ($year - 1) || $relYear[0] == ($year+1))
                        $tempMatch += 15;
                }
                if(isset($release['medium-list']['medium']['format']) && $release['medium-list']['medium']['format'] == 'CD')
                    $tempMatch += 15;
                if(DEBUG_ECHO)
                    echo "Matching release: ".$release['title']." tempMatch: ".$tempMatch."\n";
                if($tempMatch > $percentMatch)
                {
                    if(!is_array($return)){ $return = array();}
                    $return['title'] = $release['title'];
                    $return['mbID'] = $release['@attributes']['id'];
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
 * @param bool|string   $artist         artist name or false
 * @param bool|string   $artistID       MusicBrainz artistID or false
 * @param string        $orgSearchName  nZEDbetter release name
 * @param string|int    $trackNumber    Optional, Track Number of Song
 *
 * @return bool|array
 */
function getRecording($query, $artist, $artistID, $orgSearchName, $trackNumber='')
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

    if(!$artist && !$artistID)
    {
        $results = $musicBrainz->searchRecording($query);
    }
    elseif($artistID)
    {
        $results = $musicBrainz->searchRecording($query, 'recording', $artistID, 'arid');
    }
    elseif($artist)
    {
        $results = $musicBrainz->searchRecording($query, 'recording', $artist, 'artist');
    }
    print_r($results);

    return $return;
}

/**
 * @param $text
 *
 * @return mixed
 */
function stripSearchName($text)
{
    // Remove year
    $text = preg_replace('/\((19|20)\d\d\)|(?<!top)[ \-_]\d{1,3}[ \-_]|\d{3,4} ?kbps| cd ?\d{1,2} /i', ' ', $text);
    // Remove extraneous format identifiers
    $text = str_replace(array('MP3','FLAC','WMA','WEB', "cd's", ' cd ',' FM '), ' ', $text);
    $text = str_ireplace(' vol ', ' Volume ', $text);
    // Remove extra punctuation and non alphanumeric
    $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", "~", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+"), " ", $text);
    $text = preg_replace('/\s{2,}/',' ', $text);

    return $text;
}

/**
 * @param      $text
 * @param bool $includeArticles
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
    $text = preg_replace('/\s{2,}/',' ', $text);
    $text = trim($text);

    return $text;
}

/**
 * @param array     $relArtist          array containing artist results from MB
 * @param string    $orgSearchName      normalized nZEDbetter release name
 * @param bool      $skipVariousCheck   Defaults to false, skip check for Various Artists
 *
 * @return array|bool
 */
function checkArtistName($relArtist, $orgSearchName, $skipVariousCheck=false)
{
    $artistArr = array();
    $artistFound = false;
    if($relArtist['name'] === '[unknown]')
        return false;
    elseif($relArtist['name'] == 'Various Artists' && !$skipVariousCheck)
    {
        $artistArr['name'] = 'Various Artists';
        $artistArr['id'] = '89ad4ac3-39f7-470e-963a-56509c546377';
        $artistFound = true;
    }
    elseif(preg_match('/\b'.normalizeString($relArtist['name']).'\b/', $orgSearchName)=== 0)
    {
        if( preg_match('/\b'.trim(str_ireplace('Group', '', normalizeString($relArtist['name'], true))).'\b/', $orgSearchName) === 1)
        {
            if(DEBUG_ECHO)
                echo "Artist name matched: ".$relArtist['name']."\n";
            $artistArr['name'] = $relArtist['name'];
            $artistArr['id'] = $relArtist['@attributes']['id'];
            similar_text($orgSearchName, normalizeString($relArtist['name']), $artistArr['percentMatch']);
            $artistFound = true;
        }
        elseif(isset($relArtist['sort-name']) && preg_match('/\b'.normalizeString($relArtist['sort-name']).'\b/', $orgSearchName) === 1)
        {
            if (DEBUG_ECHO)
                echo "Artist name matched: " . $relArtist['name'] . "\n";
            $artistArr['name'] = $relArtist['name'];
            $artistArr['id'] = $relArtist['@attributes']['id'];
            similar_text($orgSearchName, normalizeString($relArtist['sort-name']), $artistArr['percentMatch']);
            $artistFound = true;
        }
        else
        {
            if(DEBUG_ECHO)
                echo "Artist name not matched: ".$relArtist['name']."\n";
            if(isset($relArtist['alias-list']))
            {
                if(DEBUG_ECHO)
                    echo "Checking aliases...\n";
                foreach($relArtist['alias-list'] as $alias)
                {
                    // print_r($alias);
                    if(is_array($alias))
                    {
                        foreach($alias as $aliasName)
                        {
                            if(isset($aliasName['@attributes']) && $aliasName['@attributes']['locale'] == 'ja')
                                continue;
                            if(preg_match('/\b'.normalizeString($aliasName).'\b/', $orgSearchName) === 0)
                            {
                                if(DEBUG_ECHO)
                                    echo "Alias did not match: ".$aliasName."\n";
                                continue;
                            }
                            else
                            {
                                // if(DEBUG_ECHO)
                                echo "Alias matched: ".$aliasName."\n";
                                $artistArr['name'] = $relArtist['name'];
                                $artistArr['id'] = $relArtist['@attributes']['id'];
                                similar_text($orgSearchName, normalizeString($aliasName), $artistArr['percentMatch']);
                                $artistFound = true;
                                break;
                            }
                        }
                        if($artistFound) {break;}
                    }
                    else
                    {
                        if(isset($alias['@attributes']) && $alias['@attributes']['locale'] == 'ja')
                            continue;
                        if(preg_match('/\b'.normalizeString($alias).'\b/', $orgSearchName)===0)
                        {
                            if(DEBUG_ECHO)
                                echo "Alias did not match: ".$alias."\n";
                            continue;
                        }
                        else
                        {
                            if(DEBUG_ECHO)
                                echo "Alias matched: ".$alias."\n";
                            $artistArr['name'] = $relArtist['name'];
                            $artistArr['id'] = $relArtist['@attributes']['id'];
                            similar_text($orgSearchName, normalizeString($alias), $artistArr['percentMatch']);
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
            echo "Artist name matched: ".$relArtist['name']."\n";
        $artistArr['name'] = $relArtist['name'];
        $artistArr['id'] = $relArtist['@attributes']['id'];
        similar_text($orgSearchName, normalizeString($relArtist['name']), $artistArr['percentMatch']);
        $artistFound = true;
    }

    if($artistFound)
        return $artistArr;
    else
        return false;
}

/**
 * @param $releaseName
 *
 * @return mixed
 */
function isSingle($releaseName)
{
    preg_match('/".*([012]\d) ?-? ?([\w -_]+)\.(?:mp3|wav|ogg|wma|mpa|rar|par|aac|m4a|flac)/i', $releaseName, $matches);
    return $matches;

}