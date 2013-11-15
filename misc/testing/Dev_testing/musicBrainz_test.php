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

$consoletools = new ConsoleTools();
$db = new DB();
$category = new Category();

echo "\nWelcome to the MusicBrainz test script.\n";
$gotIt = false;
/* do {

    $catToProcess = $consoletools->getUserInput("\nPlease enter the category ID or Parent ID that you want to reprocess, or type quit to exit: ");
    if(is_numeric($catToProcess) && $category->getById($catToProcess) != false)
        $gotIt = true;
    elseif($catToProcess=='quit')
        exit ("\nThanks for playing.  We'll see you next time.\n");
    else
        echo "\n\nYou specified an invalid category ID.  Please try again.\n";

} while ($gotIt==false); */
$catToProcess = 3000;
$offset = '';
$relID = $consoletools->getUserInput("Enter a release ID, or press enter to search category 3000: ");
if(!is_numeric($relID))
    $offset = $consoletools->getUserInput("\nPlease enter the offset to begin at: ");
if($offset == '' || !is_numeric($offset))
    $offset = 0;
if(!is_numeric($relID))
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE categoryID BETWEEN ".$catToProcess." AND ".($catToProcess+999)." LIMIT ".$offset.",500";
else
    $sql = "SELECT ID, name, searchname, groupID, categoryID, musicinfoID FROM releases WHERE ID=".$relID;

$musicReleases = $db->queryDirect($sql);
$totalReleases = $db->getNumRows($musicReleases);
echo "\nWe found ".number_format($totalReleases)." music releases to process.\n";
$namecleaning = new nameCleaning();
while($musicRow = $db->fetchAssoc($musicReleases))
{
    $cleanSearchName = $namecleaning->musicCleaner($musicRow['searchname']);
    $searchString = stripSearchName($cleanSearchName);
    echo "\nRelease ID: ".$musicRow['ID']."\n";
    echo "Release name: ".$musicRow['name']."\n\n";
    echo "Search Name:  ".$musicRow['searchname']."\n";
    echo "Clean Name:   ".$cleanSearchName."\n";
    echo "Query String: ".$searchString."\n";
    $artist = false;
    $consoletools->getUserInput("\nPress enter to continue: ");
    /*$result = getArtist($searchString, $musicRow['searchname'], $musicRow['name']);
    if ($result)
    {
        echo "Artist name match: ".$result['name']."\nMB ID: ".$result['mbID']."\n";
        $artist = $result['name'];
        $searchString = str_replace($artist, ' ', $searchString);
    }
    else
        echo "No artist match was found.\n";

    unset($result);*/

    $result = getReleaseName($searchString, $artist, $cleanSearchName);
    if($result)
    {
        echo "\n\nRelease name match: ".$result['title']."\nMB ID: ".$result['mbID']."\n";
        echo "Artist: ".$result['artist']."\n";
        echo "Artist ID: ".$result['artistID']."\n";
    }
    else
        echo "\n\nNo release name matches found\n";
    /*echo "\nHere's what we got for a Release lookup:\n";
    $result = getReleaseName($searchString);
    print_r($result);
    $consoletools->getUserInput("\nPress enter to continue: ");
    echo "\nHere's what we got for a Recording lookup:\n";
    $result = getRecording($searchString);
    print_r($result);*/
    $consoletools->getUserInput("\nPress enter to continue: ");
}

exit ("\nThanks for playing...\n");

function getArtist($query, $orgSearchName, $releaseName = '')
{
    $mb = new MusicBrainz();
    $orgSearchName = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "&", "+"), " ", $orgSearchName);
    $return = false;
    $results = $mb->searchArtist($query);
    if($results['artist-list']['@attributes']['count'] == '0')
    {
        echo "Artist name search returned no results\n";
        return $return;
    }
    else
        echo "Artists Found: ".$results['artist-list']['@attributes']['count']."\n";

    $percentMatch = -1000;

    foreach($results['artist-list']['artist'] as $artist)
    {
        if(stripos($orgSearchName, $artist['name']) === false)
        {
            // echo "Non-matching name: ".$artist['name']."\n";
            continue;
        }
        else
        {
            // print_r($artist);
            similar_text(strtolower($artist['name']), strtolower($orgSearchName), $tempMatch);
            if(stripos($orgSearchName, $artist['name']) == 0)
                $tempMatch += 15;
            $tempMatch -= levenshtein(strtolower($orgSearchName), strtolower($artist['name']));
            if($tempMatch > $percentMatch)
            {
                echo "Matching Artist: ".$artist['name']."      ".$tempMatch."\n";
                if(!is_array($return)) {$return = array();}
                $return['name'] = $artist['name'];
                $return['mbID'] = $artist['@attributes']['id'];
                $percentMatch = $tempMatch;
            }

        }
    }

    return $return;
}

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
    $mb = new MusicBrainz();
    $return = false;
    $percentMatch = 0;
    $artist = $artist=='' ? false : $artist;
    $artistArr = false;

    if($artist === false)
    {
        $results = $mb->searchRelease($query, 'release', '', '', 30);
    }
    else
    {
        $artistArr['name'] = $artist;
        $results = $mb->searchRelease($query, 'release', normalizeString($artist), 'artistname');
    }
    if(!isset($results['release-list']['@attributes']['count']))
        print_r($results);
    if($results['release-list']['@attributes']['count'] == '0')
    {
        echo "Release name search returned no results\n";
        return $return;
    }
    else
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
                echo "Non-matching release: ".$release['title']."\n";
                continue;
            }
            else
            {
                similar_text(normalizeString($release['title']), $orgSearchName, $tempMatch);
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
                        echo "A matching artist was not found in the release.\n";
                        continue;
                    }
                    elseif($artistArr['name'] == 'Various Artists')
                        $tempMatch -= 15;
                }
                if(normalizeString($release['title'], true) == normalizeString($artistArr['name'], true) && substr_count($query, normalizeString($artistArr['name'], true)) == 1)
                {
                    echo "Artist name and release title are the same, but not looking for self-titled release\n";
                    continue;
                }
                elseif(stripos(trim(preg_replace('/'.normalizeString($artistArr['name'], true).'/', '', normalizeString($matchedSearchName, true), 1)), trim(normalizeString($release['title'], true))) === false)
                {
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
function getRecording($query, $artist=false, $orgSearchName)
{
    $mb = new MusicBrainz();
    return $mb->searchRecording($query);
}
function stripSearchName($text)
{
    // Remove year
    $text = preg_replace('/\((19|20)\d\d\)|(?<!top)[ \-_]\d{1,3}[ \-_]|\d{3,4} ?kbps| cd ?\d{1,2} /i', ' ', $text);
    // Remove extraneous format identifiers
    $text = str_replace(array('MP3','FLAC','WMA','WEB', "cd's", ' cd ',' FM '), ' ', $text);
    $text = str_ireplace(' vol ', ' Volume ', $text);
    // Remove extra punctuation and non alphanumeric
    $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+"), " ", $text);
    $text = preg_replace('/\s{2,}/',' ', $text);

    return $text;
}
function normalizeString($text, $includeArticles=false)
{
    $text = strtolower($text);
    if($includeArticles)
        $text = preg_replace('/\b(a|an|the)\b/i', ' ', $text);
    $text = str_replace(array(".", "_", '-', "|", "<", ">", '"', "=", '[', "]", "(", ")", "{", "}", "*", ";", ":", ",", "~", "/", "+", "'s "), " ", $text);
    $text = str_ireplace(' vol ', ' Volume ', $text);
    $text = preg_replace('/\s{2,}/',' ', $text);
    $text = trim($text);

    return $text;
}

function checkArtistName($relArtist, $orgSearchName)
{
    $artistArr = array();
    $artistFound = false;
    if($relArtist['name'] == 'Various Artists')
    {
        $artistArr['name'] = 'Various Artists';
        $artistArr['id'] = '89ad4ac3-39f7-470e-963a-56509c546377';
        $artistFound = true;
    }
    elseif(preg_match('/\b'.normalizeString($relArtist['name']).'\b/', $orgSearchName)=== 0)
    {
        if( preg_match('/\b'.trim(str_ireplace('Group', '', normalizeString($relArtist['name'], true))).'\b/', $orgSearchName) === 1)
        {
            echo "Artist name matched: ".$relArtist['name']."\n";
            $artistArr['name'] = $relArtist['name'];
            $artistArr['id'] = $relArtist['@attributes']['id'];
            $artistFound = true;
        }
        else
        {
            echo "Artist name not matched: ".$relArtist['name']."\n";
            if(isset($relArtist['alias-list']))
            {
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
                                echo "Alias did not match: ".$aliasName."\n";
                                continue;
                            }
                            else
                            {
                                echo "Alias matched: ".$aliasName."\n";
                                $artistArr['name'] = $relArtist['name'];
                                $artistArr['id'] = $relArtist['@attributes']['id'];
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
                            echo "Alias did not match: ".$alias."\n";
                            continue;
                        }
                        else
                        {
                            echo "Alias matched: ".$alias."\n";
                            $artistArr['name'] = $relArtist['name'];
                            $artistArr['id'] = $relArtist['@attributes']['id'];
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
        echo "Artist name matched: ".$relArtist['name']."\n";
        $artistArr['name'] = $relArtist['name'];
        $artistArr['id'] = $relArtist['@attributes']['id'];
        $artistFound = true;
    }

    if($artistFound)
        return $artistArr;
    else
        return false;
}